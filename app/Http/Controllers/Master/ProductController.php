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
        $user = $request->user();
        $role = $user?->role?->name;
        // Harga beli & HPP = data sensitif. Kasir/admin gak boleh lihat.
        $canSeeCost = in_array($role, ['owner', 'supervisor'], true);

        $query = Product::query()
            ->with(['division', 'department', 'kelas', 'subkelas'])
            ->when($request->search, function ($q, $s) use ($request) {
                // exact=1 → WHERE = (bukan LIKE), dipakai filter presisi katalog.
                if ($request->boolean('exact')) {
                    $q->where(fn($w) => $w
                        ->where('nama_produk', $s)
                        ->orWhere('sku', $s)
                        ->orWhere('isbn', $s));
                } else {
                    $q->where(fn($w) => $w
                        ->where('nama_produk', 'like', "%$s%")
                        ->orWhere('sku', 'like', "%$s%")
                        ->orWhere('isbn', 'like', "%$s%"));
                }
            });

        // Filter hierarki 4 level
        if ($request->id_subkelas) {
            $query->where('id_subkelas', $request->id_subkelas);
        } elseif ($request->id_kelas) {
            $query->where('id_kelas', $request->id_kelas);
        } elseif ($request->id_departemen) {
            $query->where('id_departemen', $request->id_departemen);
        } elseif ($request->id_divisi) {
            $query->where('id_divisi', $request->id_divisi);
        }

        $query->when($request->low_stock, fn($q) => $q->whereColumn('stok', '<=', 'min_stock'));
        $query->when($request->boolean('available'), fn($q) => $q->where('stok', '>', 0));

        return $query->paginate($request->input('per_page', 20))->through(function (Product $p) use ($canSeeCost) {
            $row = array_merge($p->toArray(), ['kategori' => $p->kategori]);
            if (!$canSeeCost) {
                unset($row['harga_beli'], $row['harga_pokok']);
            }
            return $row;
        });
    }

    public function show(string $id)
    {
        return Product::findOrFail($id);
    }

    /** Aturan validasi produk baru — dipakai store() & bulk() biar satu sumber. */
    private function rules(): array
    {
        return [
            'sku' => 'required|string|max:50|unique:products,sku',   // tolak duplikat (Function 1)
            'nama_produk' => 'required|string|max:100',
            'deskripsi_produk' => 'nullable|string|max:75',
            'penerbit' => 'nullable|string|max:150',
            'isbn' => 'nullable|string|max:20|unique:products,isbn',
            'foto_produk' => ['nullable', 'string', 'max:255', 'regex:/\.webp$/i'],
            'harga_jual' => 'required|numeric|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'diskon_base' => 'nullable|numeric|min:0|max:100',
            'id_pemasok' => 'nullable|exists:vendors,id_pemasok',
            'id_departemen' => 'required|exists:departments,id_departemen',
            'id_subkelas' => 'nullable|exists:subclasses,id_subkelas',
            'stok' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ];
    }

    /** Buat satu produk dari array tervalidasi. Return Product. */
    private function createProduct(array $data, int $stokAwal = 0): Product
    {
        $vendor = !empty($data['id_pemasok']) ? \App\Models\Vendor::find($data['id_pemasok']) : null;
        $disc = $vendor?->diskon_pemasok ?? 0;
        $data['harga_pokok'] = round($data['harga_beli'] * (1 - $disc / 100), 2); // HPP auto
        $data['id_produk'] = IdGen::next(Product::class, 'id_produk', 'PRD');
        $data['stok'] = 0; // stok awal HANYA lewat kartu stok, biar ledger & produk tidak double count
        // Turunkan sisa level hierarki biar KATEGORI 4 level utuh (dipakai sortir kasir).
        $data['id_kelas']  = $data['id_subkelas']
            ? optional(\App\Models\Subclass::find($data['id_subkelas']))->id_kelas
            : null;
        $data['id_divisi'] = optional(\App\Models\Department::find($data['id_departemen']))->id_divisi;

        $p = Product::create($data);
        if ($stokAwal > 0) {
            StockService::record($p, 'PENYESUAIAN', $stokAwal, 'master', $p->id_produk, (string) auth()->id());
            $p->refresh();
        }
        return $p;
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        return response()->json($this->createProduct($data, (int) $request->input('stok', 0)), 201);
    }

    /**
     * Bulk inject produk (8.1). Body: { rows: [ {...}, {...} ] }.
     * Client yang parsing file (CSV/Excel) — backend gak nyentuh storage.
     * Tiap baris divalidasi terpisah; baris gagal dilaporkan, baris valid tetap masuk.
     */
    public function bulk(Request $request)
    {
        $rows = $request->validate([
            'rows' => 'required|array|min:1|max:2000',
        ])['rows'];

        $ok = 0;
        $gagal = [];
        foreach ($rows as $i => $row) {
            $baris = $i + 1;
            try {
                $v = \Illuminate\Support\Facades\Validator::make($row, $this->rules());
                if ($v->fails()) {
                    $gagal[] = ['baris' => $baris, 'sku' => $row['sku'] ?? '-', 'alasan' => implode('; ', $v->errors()->all())];
                    continue;
                }
                $data = $v->validated();
                $this->createProduct($data, (int) ($row['stok'] ?? 0));
                $ok++;
            } catch (\Throwable $e) {
                report($e); // detail asli ke laravel.log, client cuma dapat pesan aman
                $gagal[] = [
                    'baris' => $baris,
                    'sku' => $row['sku'] ?? '-',
                    'alasan' => $e instanceof \Illuminate\Database\QueryException
                        ? 'Gagal simpan ke basis data (kemungkinan duplikat / relasi tidak valid).'
                        : 'Baris gagal diproses.',
                ];
            }
        }

        return response()->json(['sukses' => $ok, 'gagal' => $gagal, 'total' => count($rows)]);
    }

    public function update(Request $request, string $id)
    {
        // Guard: edit identitas & harga produk cuma owner/supervisor.
        $role = $request->user()?->role?->name;
        if (!in_array($role, ['owner', 'supervisor'], true)) {
            return response()->json([
                'message' => 'Forbidden: hanya Owner dan Supervisor yang boleh mengedit master produk.',
            ], 403);
        }

        $p = Product::findOrFail($id);

        // Whitelist ketat — stok & harga_pokok TIDAK boleh lewat sini
        // (stok lewat kartu stok/receiving, harga_pokok dihitung ulang di bawah).
        $data = $request->validate([
            'nama_produk' => 'required|string|max:100',
            'deskripsi_produk' => 'nullable|string|max:75',
            'penerbit' => 'nullable|string|max:150',
            'isbn' => 'nullable|string|max:20|unique:products,isbn,' . $p->id_produk . ',id_produk',
            'foto_produk' => ['nullable', 'string', 'max:255', 'regex:/\.webp$/i'],
            'harga_jual' => 'required|numeric|min:0',
            'harga_beli' => 'required|numeric|min:0',
            'diskon_base' => 'nullable|numeric|min:0|max:100',
        ]);

        // HPP ikut diskon vendor — biar konsisten sama store().
        $vendor = $p->id_pemasok ? \App\Models\Vendor::find($p->id_pemasok) : null;
        $disc = $vendor?->diskon_pemasok ?? 0;
        $data['harga_pokok'] = round($data['harga_beli'] * (1 - $disc / 100), 2);

        $p->update($data);
        return response()->json($p);
    }

    public function destroy(string $id)
    {
        Product::findOrFail($id)->update(['is_active' => false]);
        return response()->json(['message' => 'produk dinonaktifkan (soft)']);
    }
}
