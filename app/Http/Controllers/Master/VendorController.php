<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Support\IdGen;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        return Vendor::when($request->id_divisi, fn($q, $d) => $q->where('id_divisi', $d))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->paginate($request->input('per_page', 20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_pemasok' => 'required|string|max:150',
            'alamat_lengkap' => 'required|string|max:255',
            'nomor_telepon_pemasok' => 'required|string|max:20',
            'email_pemasok' => 'nullable|email|unique:vendors,email_pemasok',
            'diskon_pemasok' => 'nullable|numeric|min:0|max:100',
            'nomor_rekening' => 'nullable|string|max:30',
            'id_divisi' => 'required|exists:divisions,id_divisi',
        ]);
        $data['id_pemasok'] = IdGen::next(Vendor::class, 'id_pemasok', 'VDR');
        return response()->json(Vendor::create($data), 201);
    }

    public function update(Request $request, string $id)
    {
        $v = Vendor::findOrFail($id);
        $v->update($request->validate([
            'nama_pemasok' => 'sometimes|string|max:150',
            'email_pemasok' => 'nullable|email|unique:vendors,email_pemasok,' . $id . ',id_pemasok',
            'diskon_pemasok' => 'nullable|numeric',
            'is_active' => 'sometimes|boolean', // nonaktifkan, bukan hapus (Function 3)
        ]));
        return response()->json($v);
    }

    public function destroy(string $id)
    {
        Vendor::findOrFail($id)->update(['is_active' => false]);
        return response()->json(['message' => 'vendor dinonaktifkan (soft)']);
    }
}
