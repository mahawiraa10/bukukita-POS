<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductLoad;
use App\Services\StockService;
use App\Support\IdGen;
use Illuminate\Http\Request;

class ProductLoadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'id_produk' => 'required|string|exists:products,id_produk',
            'jumlah_produk' => 'required|integer|min:1',
            'sumber_data' => 'required|string|max:100',
            'id_acara' => 'nullable|string|exists:events,id_acara',
        ]);
        $pl = ProductLoad::create($data + [
            'id_load' => IdGen::next(ProductLoad::class, 'id_load', 'LOD'),
            'tanggal_load' => today(),
            'status_load' => 'SELESAI',
        ]);
        StockService::record(Product::findOrFail($data['id_produk']), 'LOAD_EVENT', $data['jumlah_produk'], 'load', $pl->id_load, (string) auth()->id());
        return response()->json($pl, 201);
    }

    public function index(Request $request)
    {
        return ProductLoad::with('product:id_produk,nama_produk')
            ->when($request->id_acara, fn($q, $v) => $q->where('id_acara', $v))
            ->paginate($request->input('per_page', 20));
    }
}
