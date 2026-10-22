<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\ProductLoad;
use App\Models\PurchaseOrder;
use App\Support\IdGen;
use Illuminate\Http\Request;

class ProductLoadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_produk'     => 'required|string|exists:products,id_produk',
            'jumlah_produk' => 'required|integer|min:1',
            'sumber_data'   => 'required|string|max:100',
            'id_acara'      => 'nullable|string|exists:events,id_acara',
            // Asal barang: terisi = dari PO tertentu (konsinyasi/reguler), kosong = stok gudang bebas.
            'id_pesanan'    => ['nullable', 'string', function ($attr, $val, $fail) {
                if ($val && !PurchaseOrder::find($val)) {
                    $fail('PO tidak ditemukan.');
                }
            }],
        ]);

        // Pemuatan ke lapak cuma catatan logistik: stok tetap dipotong saat PENJUALAN.
        // Kalau di sini juga dikurangi, satu barang kena potong dua kali.
        $pl = ProductLoad::create($data + [
            'id_load'      => IdGen::next(ProductLoad::class, 'id_load', 'LOD'),
            'tanggal_load' => today(),
            'status_load'  => 'SELESAI',
        ]);
        return response()->json($pl, 201);
    }

    public function index(Request $request)
    {
        return ProductLoad::with('product:id_produk,nama_produk')
            ->when($request->id_acara, fn($q, $v) => $q->where('id_acara', $v))
            ->paginate($request->input('per_page', 20));
    }
}
