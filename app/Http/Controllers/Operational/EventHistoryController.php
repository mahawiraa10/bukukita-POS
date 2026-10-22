<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PurchaseOrder;
use App\Models\SaleItem;
use App\Models\VendorReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Histori & ringkasan event.
 * Akses: supervisor + owner (lihat routes/api.php -> permission:view_event_history).
 *
 * Catatan HPP: sale_items.subtotal sudah net (setelah diskon item), jadi profit
 * dihitung dari harga_pokok produk saat ini. Cukup akurat untuk ringkasan event.
 */
class EventHistoryController extends Controller
{
    private const EVENT_COLS = "e.id_acara, e.nama_acara, e.tanggal_mulai, e.tanggal_selesai, e.lokasi, e.status_acara";

    /** Daftar histori event + omzet/profit ringkas per event. */
    public function index(Request $request)
    {
        // Penjualan lunas per event
        $sales = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id_produk', '=', 'sale_items.id_produk')
            ->where('sales.payment_status', 'paid')
            ->whereNotNull('sales.id_acara')
            ->groupBy('sales.id_acara')
            ->selectRaw("sales.id_acara as _id,
                COALESCE(SUM(sale_items.subtotal),0) as omzet,
                COALESCE(SUM(sale_items.qty),0) as qty_terjual,
                COALESCE(SUM(COALESCE(sale_items.hpp_snapshot, products.harga_pokok) * sale_items.qty),0) as hpp");

        // Retur titipan per event
        $retur = VendorReturn::whereNotNull('id_acara')
            ->join('products', 'products.id_produk', '=', 'vendor_returns.id_produk')
            ->groupBy('id_acara')
            ->selectRaw("id_acara as _id,
                COALESCE(SUM(jumlah_dikembalikan),0) as qty_retur,
                COALESCE(SUM(jumlah_dikembalikan * products.harga_pokok),0) as nilai_retur");

        $rows = DB::query()
            ->from('events as e')
            ->leftJoinSub($sales, 's', 's._id', '=', 'e.id_acara')
            ->leftJoinSub($retur, 'r', 'r._id', '=', 'e.id_acara')
            ->selectRaw(self::EVENT_COLS . ",
                COALESCE(s.omzet,0) as omzet,
                COALESCE(s.qty_terjual,0) as qty_terjual,
                COALESCE(s.hpp,0) as hpp,
                COALESCE(r.qty_retur,0) as qty_retur,
                COALESCE(r.nilai_retur,0) as nilai_retur")
            ->when($request->search, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('e.nama_acara', 'like', "%$s%")
                ->orWhere('e.id_acara', 'like', "%$s%")
                ->orWhere('e.lokasi', 'like', "%$s%")))
            ->when($request->status, fn ($q, $s) => $q->where('e.status_acara', $s))
            ->orderByDesc('e.tanggal_mulai')
            ->get()
            ->map(function ($e) {
                $e->omzet      = (float) $e->omzet;
                $e->hpp        = (float) $e->hpp;
                $e->profit     = round($e->omzet - $e->hpp, 2);
                $e->margin     = $e->omzet > 0 ? round($e->profit / $e->omzet * 100, 1) : 0;
                $e->qty_retur  = (int) $e->qty_retur;
                $e->nilai_retur = (float) $e->nilai_retur;
                return $e;
            });

        return response()->json(['data' => $rows]);
    }

    /** Detail satu event: KPI + rincian per produk. */
    public function show(string $id)
    {
        $event = Event::findOrFail($id);

        $perProduk = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id_produk', '=', 'sale_items.id_produk')
            ->where('sales.id_acara', $id)
            ->where('sales.payment_status', 'paid')
            ->groupBy('sale_items.id_produk', 'products.nama_produk')
            ->selectRaw('sale_items.id_produk, products.nama_produk,
                SUM(sale_items.qty) as qty,
                SUM(sale_items.subtotal) as omzet,
                SUM(sale_items.subtotal - COALESCE(sale_items.hpp_snapshot, products.harga_pokok) * sale_items.qty) as profit');

        $returMap = VendorReturn::where('id_acara', $id)
            ->groupBy('id_produk')
            ->selectRaw('id_produk, SUM(jumlah_dikembalikan) as qty')
            ->pluck('qty', 'id_produk')
            ->all();

        $items = $perProduk->get()->map(function ($r) use ($returMap) {
            $r->qty_retur = (int) ($returMap[$r->id_produk] ?? 0);
            $r->omzet     = (float) $r->omzet;
            $r->profit    = (float) $r->profit;
            return $r;
        });

        $agg = DB::query()->fromSub($perProduk, 'x')->selectRaw(
            "COALESCE(SUM(x.omzet),0) omzet,
             COALESCE(SUM(x.profit),0) profit,
             COALESCE(SUM(x.qty),0) qty"
        )->first();

        $nilaiRetur = (float) VendorReturn::where('id_acara', $id)
            ->join('products', 'products.id_produk', '=', 'vendor_returns.id_produk')
            ->selectRaw('COALESCE(SUM(jumlah_dikembalikan * products.harga_pokok),0) v')
            ->value('v');
        $qtyRetur = (int) array_sum($returMap);

        $poTerkait = PurchaseOrder::where('id_acara', $id)
            ->get(['id', 'tipe_po', 'status']);

        return response()->json([
            'event' => $event,
            'kpi'   => [
                'omzet'      => (float) $agg->omzet,
                'hpp'        => round((float) $agg->omzet - (float) $agg->profit, 2),
                'profit'     => (float) $agg->profit,
                'margin'     => $agg->omzet > 0 ? round($agg->profit / $agg->omzet * 100, 1) : 0,
                'qty_terjual' => (int) $agg->qty,
                'qty_retur'  => $qtyRetur,
                'nilai_retur' => $nilaiRetur,
            ],
            'produk'  => $items,
            'po_terkait' => $poTerkait,
        ]);
    }
}
