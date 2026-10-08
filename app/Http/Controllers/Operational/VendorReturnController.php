<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\VendorReturn;
use App\Services\StockService;
use App\Support\IdGen;
use Illuminate\Http\Request;

class VendorReturnController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_pemasok' => 'required|string|exists:vendors,id_pemasok',
            'id_penerimaan' => 'nullable|string|exists:receiving_goods,id_penerimaan',
            'id_produk' => 'required|string|exists:products,id_produk',
            'alasan' => 'required|string|max:255',
            'jumlah_dikembalikan' => 'required|integer|min:1',
        ]);
        $p = Product::findOrFail($data['id_produk']);
        $vr = VendorReturn::create($data + [
            'id_pengembalian' => IdGen::next(VendorReturn::class, 'id_pengembalian', 'RTV'),
            'nama_produk' => $p->nama_produk,
            'tanggal_pengembalian' => today(),
        ]);
        StockService::record($p, 'PENGEMBALIAN_PEMASOK', -$data['jumlah_dikembalikan'], 'vendor_return', $vr->id_pengembalian, (string) auth()->id());
        return response()->json($vr, 201);
    }

    public function index(Request $request)
    {
        return VendorReturn::latest()->paginate($request->input('per_page', 20));
    }
}
