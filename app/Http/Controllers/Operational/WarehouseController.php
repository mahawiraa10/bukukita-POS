<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMutation;
use Illuminate\Http\Request;

/**
 * Modul Gudang: master stok + kartu stok per produk.
 * Stok gudang = products.stok (satu gudang pusat). Ledger = stock_mutations (append-only).
 */
class WarehouseController extends Controller
{
    /** Master stok semua produk, dengan flag rendah (stok < min_stock). */
    public function index(Request $request)
    {
        $q = Product::query()
            ->when($request->search, function ($w, $s) {
                $w->where(fn ($x) => $x
                    ->where('nama_produk', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%"));
            })
            ->when($request->low === '1', fn ($w) => $w->whereColumn('stok', '<', 'min_stock'))
            ->orderBy('nama_produk');

        return response()->json($q->paginate($request->input('per_page', 20)));
    }

    /** Kartu stok satu produk: seluruh mutasinya (append-only ledger). */
    public function kartuStok(string $idProduk)
    {
        $produk = Product::findOrFail($idProduk);

        $mutasi = StockMutation::where('id_produk', $idProduk)
            ->orderBy('tanggal_perubahan')
            ->orderBy('id_perubahan_stok')
            ->limit(500)
            ->get();

        return response()->json([
            'produk' => $produk->only(['id_produk', 'sku', 'nama_produk', 'stok', 'min_stock', 'harga_beli', 'harga_jual']),
            'mutasi' => $mutasi,
        ]);
    }

    /** Stock opname: PENYESUAIAN delta = stok_fisik - stok_sistem. */
    public function adjust(Request $request)
    {
        $data = $request->validate([
            'id_produk'   => 'required|exists:products,id_produk',
            'stok_fisik'  => 'required|integer|min:0',
            'catatan'     => 'nullable|string|max:255',
        ]);

        $produk = Product::findOrFail($data['id_produk']);
        $delta = $data['stok_fisik'] - $produk->stok;

        if ($delta === 0) {
            return response()->json(['message' => 'Stok sudah sesuai fisik', 'delta' => 0]);
        }

        $mutation = \App\Services\StockService::record(
            $produk,
            'PENYESUAIAN',
            $delta,
            'opname',
            $data['catatan'] ?: 'Stock opname',
            (string) auth()->id()
        );

        return response()->json([
            'message'  => 'Stok disesuaikan',
            'delta'    => $delta,
            'stok_baru' => $produk->fresh()->stok,
            'mutasi'   => $mutation,
        ], 201);
    }
}
