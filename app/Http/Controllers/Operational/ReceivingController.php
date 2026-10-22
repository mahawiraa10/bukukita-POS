<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\ReceivingGood;
use App\Models\ReceivingItem;
use App\Services\StockService;
use App\Support\IdGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceivingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pesanan' => 'nullable|string|exists:purchase_orders,po_code',
            'nomor_surat_jalan' => 'required|string|max:30',
            'nomor_faktur' => 'required|string|max:30',
            'id_pemasok' => 'required|string|exists:vendors,id_pemasok',
            'penerima' => 'required|string|max:150',
            'items' => 'required|array|min:1',
            'items.*.id_produk' => 'required|string|exists:products,id_produk',
            'items.*.jumlah_diterima' => 'required|integer|min:1',
            'items.*.harga_beli' => 'required|numeric|min:0',
            'items.*.diskon_pemasok' => 'nullable|numeric|min:0|max:100',
        ]);

        // Produk dobel dalam satu surat jalan bikin received_qty ambigu.
        $ids = array_column($data['items'], 'id_produk');
        if (count($ids) !== count(array_unique($ids))) {
            throw new \DomainException('Produk yang sama muncul lebih dari sekali dalam satu penerimaan.');
        }

        return DB::transaction(function () use ($data) {
            $po = null;
            if (!empty($data['id_pesanan'])) {
                $po = PurchaseOrder::with('items')->where('po_code', $data['id_pesanan'])->firstOrFail();

                if (!in_array($po->status, ['approved', 'partial'])) {
                    throw new \DomainException("PO {$po->po_code} berstatus {$po->status}, tidak bisa diterima.");
                }
            }

            $rg = ReceivingGood::create([
                'id_penerimaan' => IdGen::next(ReceivingGood::class, 'id_penerimaan', 'RCV'),
                'id_pesanan' => $data['id_pesanan'] ?? null,
                'tanggal_penerimaan' => today(),
                'nomor_faktur' => $data['nomor_faktur'],
                'nomor_surat_jalan' => $data['nomor_surat_jalan'],
                'id_pemasok' => $data['id_pemasok'],
                'penerima' => $data['penerima'],
                'status' => 'SELESAI',
            ]);

            foreach ($data['items'] as $it) {
                $qty = (int) $it['jumlah_diterima'];

                // Kalau terikat PO: update received_qty (partial-aware), bukan paksa 'received'.
                if ($po) {
                    $poItem = $po->items->firstWhere('id_produk', $it['id_produk']);
                    if (!$poItem) {
                        throw new \DomainException("Produk {$it['id_produk']} tidak ada di PO {$po->po_code}.");
                    }
                    $newTotal = (int) $poItem->received_qty + $qty;
                    if ($newTotal > (int) $poItem->qty) {
                        throw new \DomainException("Qty diterima ({$newTotal}) melebihi pesanan {$poItem->qty} untuk produk {$it['id_produk']}.");
                    }
                    $poItem->update(['received_qty' => $newTotal]);
                }

                ReceivingItem::create([
                    'id_penerimaan' => $rg->id_penerimaan,
                    'id_produk' => $it['id_produk'],
                    'jumlah_diterima' => $qty,
                    'harga_beli' => $it['harga_beli'],
                    'diskon_pemasok' => $it['diskon_pemasok'] ?? null,
                ]);

                $p = Product::findOrFail($it['id_produk']);
                $disc = $it['diskon_pemasok'] ?? 0;
                $p->update([
                    'harga_beli' => $it['harga_beli'],
                    'harga_pokok' => round($it['harga_beli'] * (1 - $disc / 100), 2), // HPP dari invoice terbaru
                ]);
                StockService::record($p, 'PENERIMAAN', $qty, 'receiving', $rg->id_penerimaan, (string) auth()->id());
            }

            if ($po) {
                $allDone = $po->items()->whereColumn('received_qty', '<', 'qty')->doesntExist();
                $po->update([
                    'status' => $allDone ? 'received' : 'partial',
                    'received_date' => today(),
                ]);
            }

            return response()->json($rg->load('items'), 201);
        });
    }

    public function index(Request $request)
    {
        return ReceivingGood::with('items')->when($request->id_pemasok, fn($q, $v) => $q->where('id_pemasok', $v))
            ->when($request->from, fn($q, $f) => $q->whereDate('tanggal_penerimaan', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->whereDate('tanggal_penerimaan', '<=', $t))
            ->paginate($request->input('per_page', 20));
    }
}
