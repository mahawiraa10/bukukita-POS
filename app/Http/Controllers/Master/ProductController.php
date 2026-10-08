<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\StockService;
use App\Support\IdGen;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return Product::query()
            ->when($request->search, fn($q, $s) => $q->where(fn($w) => $w
                ->where('nama_produk', 'like', "%$s%")->orWhere('sku', 'like', "%$s%")->orWhere('isbn', 'like', "%$s%")))
            ->when($request->id_departemen, fn($q, $d) => $q->where('id_departemen', $d))
            ->when($request->id_subkelas, fn($q, $s) => $q->where('id_subkelas', $s))
            ->when($request->low_stock, fn($q) => $q->whereColumn('stok', '<=', 'min_stock'))
            ->paginate($request->input('per_page', 20));
    }

    public function show(string $id)
    {
        return Product::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku',   // tolak duplikat (Function 1)
            'nama_produk' => 'required|string|max:100',
            'deskripsi_produk' => 'nullable|string|max:75',
            'penerbit' => 'nullable|string|max:150',
            'isbn' => 'nullable|string|max:20|unique:products,isbn',
            'harga_jual' => 'required|numeric|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'diskon_base' => 'nullable|numeric|min:0|max:100',
            'id_pemasok' => 'nullable|exists:vendors,id_pemasok',
            'id_departemen' => 'required|exists:departments,id_departemen',
            'id_subkelas' => 'nullable|exists:subclasses,id_subkelas',
            'stok' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ]);
        $vendor = isset($data['id_pemasok']) && $data['id_pemasok'] ? \App\Models\Vendor::find($data['id_pemasok']) : null;
        $disc = $vendor?->diskon_pemasok ?? 0;
        $data['harga_pokok'] = round($data['harga_beli'] * (1 - $disc / 100), 2); // HPP auto
        $data['id_produk'] = IdGen::next(Product::class, 'id_produk', 'PRD');
        $data['stok'] = 0; // stok awal HANYA lewat kartu stok, biar ledger & produk tidak double count
        $p = Product::create($data);
        if ($request->input('stok', 0) > 0) {
            StockService::record($p, 'PENYESUAIAN', (int) $request->input('stok'), 'master', $p->id_produk, (string) auth()->id());
            $p->refresh();
        }
        return response()->json($p, 201);
    }

    public function update(Request $request, string $id)
    {
        $p = Product::findOrFail($id);
        $p->update($request->except(['id_produk', 'stok'])); // stok hanya lewat kartu stok / receiving
        return response()->json($p);
    }

    public function destroy(string $id)
    {
        Product::findOrFail($id)->update(['is_active' => false]);
        return response()->json(['message' => 'produk dinonaktifkan (soft)']);
    }
}
