<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventClosing;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\VendorReturn;
use App\Support\IdGen;
use Illuminate\Support\Facades\DB;

/**
 * Tutup event: hitung sisa & arahkan sesuai tipe PO yang terikat ke event.
 *
 * Model stok: SATU pool (products.stok) = stok gudang. Pemuatan barang ke lapak
 * (product_loads) cuma catatan logistik, TIDAK mengubah stok — kalau ikut dikurangi,
 * penjualan di event akan mengurangi pool dua kali (load + sale).
 *
 * - konsinyasi: SEMUA barang PO yang belum terjual balik ke vendor (PENGEMBALIAN_PEMASOK).
 * - reguler   : sisa tetap jadi stok gudang, cukup dicatat di event_closings (tanpa mutasi).
 */
class EventClosingService
{
    /**
     * @return array<int, array{id_produk:string,nama_produk:?string,jumlah_sisa_produk:int,tipe_sumber:string,dokumen:?string}>
     */
    public function close(Event $event, ?string $userId = null): array
    {
        return DB::transaction(function () use ($event, $userId) {
            $id = $event->id_acara;

            // Terjual di event ini, per produk (hanya nota lunas).
            $terjual = Sale::where('sales.id_acara', $id)
                ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.payment_status', 'paid')
                ->selectRaw('sale_items.id_produk as idp, SUM(sale_items.qty) as total')
                ->groupBy('sale_items.id_produk')
                ->pluck('total', 'idp');

            // PO yang terikat ke event ini menentukan tujuan sisa barang.
            $pos = PurchaseOrder::with('items')
                ->where('id_acara', $id)
                ->whereIn('status', ['received', 'partial'])
                ->get();

            $bon = [];

            foreach ($pos as $order) {
                $isKonsinyasi = $order->tipe_po === 'konsinyasi';

                foreach ($order->items as $item) {
                    $sisa = (int) $item->received_qty - (int) ($terjual[$item->id_produk] ?? 0);
                    if ($sisa <= 0) {
                        continue;
                    }

                    $produk = Product::find($item->id_produk);
                    $dokumen = null;

                    if ($isKonsinyasi && $produk) {
                        // Sisa titipan wajib balik vendor -> catat retur + stok keluar.
                        $vr = VendorReturn::create([
                            'id_pengembalian'      => IdGen::next(VendorReturn::class, 'id_pengembalian', 'RTV'),
                            'id_pemasok'           => $order->id_pemasok,
                            'id_acara'             => $id,
                            'id_produk'            => $item->id_produk,
                            'nama_produk'          => $produk->nama_produk,
                            'alasan'               => 'Sisa barang konsinyasi setelah event selesai',
                            'tanggal_pengembalian' => now()->toDateString(),
                            'jumlah_dikembalikan'  => $sisa,
                        ]);
                        StockService::record($produk, 'PENGEMBALIAN_PEMASOK', -$sisa, 'vendor_return', $vr->id_pengembalian, $userId);
                        $dokumen = $vr->id_pengembalian;
                    }
                    // reguler: sisa tetap di gudang, tidak ada mutasi.

                    EventClosing::create([
                        'id_penutupan_acara'     => IdGen::next(EventClosing::class, 'id_penutupan_acara', 'ECL'),
                        'id_acara'               => $id,
                        'id_produk'              => $item->id_produk,
                        'tanggal_selesai'        => now()->toDateString(),
                        'status_acara'           => 'SELESAI',
                        'jumlah_sisa_produk'     => $sisa,
                        'nomor_bon_pengembalian' => 'BON-' . strtoupper($id) . '-' . now()->format('ymd'),
                        'tanggal_pengembalian'   => now()->toDateString(),
                    ]);

                    $bon[] = [
                        'id_produk'          => $item->id_produk,
                        'nama_produk'        => $produk?->nama_produk,
                        'jumlah_sisa_produk' => $sisa,
                        'tipe_sumber'        => $isKonsinyasi ? 'konsinyasi' : 'reguler',
                        'dokumen'            => $dokumen,
                    ];
                }
            }

            $event->update(['status_acara' => 'NONAKTIF']);

            return $bon;
        });
    }

    /**
     * Tipe sumber barang untuk sebuah event, dibaca dari PO yang terikat ke event itu.
     * Dipakai UI gudang buat nentuin sisa stok balik ke vendor atau ke gudang.
     */
    public function sourceTypeForEvent(string $idAcara): string
    {
        $tipe = PurchaseOrder::where('id_acara', $idAcara)->value('tipe_po');

        return $tipe === 'konsinyasi' ? 'konsinyasi' : 'reguler';
    }
}
