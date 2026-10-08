<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Support\IdGen;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    // peta route => [model, pk, prefix, fields wajib]
    private array $map = [
        'divisions'   => [\App\Models\Division::class,   'id_divisi',       'DIV', ['nama_divisi']],
        'departments' => [\App\Models\Department::class,  'id_departemen',   'DEP', ['nama_departemen', 'id_divisi']],
        'classes'     => [\App\Models\ClassLevel::class,  'id_kelas',        'KLS', ['nama_kelas', 'id_departemen']],
        'subclasses'  => [\App\Models\Subclass::class,    'id_subkelas',     'SUB', ['nama_subkelas', 'id_kelas']],
    ];

    private function cfg(string $key): array
    {
        abort_unless(isset($this->map[$key]), 404);
        return $this->map[$key];
    }

    public function index(Request $request, string $key)
    {
        [$m] = $this->cfg($key);
        return $m::withCount(in_array($key, ['divisions']) ? ['departments'] : (in_array($key, ['departments']) ? ['classes'] : (in_array($key, ['classes']) ? ['subclasses'] : [])))
            ->paginate($request->input('per_page', 50));
    }

    public function store(Request $request, string $key)
    {
        [$m, $pk, $prefix, $required] = $this->cfg($key);
        $data = $request->validate(array_fill_keys($required, 'required'));
        $data[$pk] = $request->input($pk) ?: IdGen::next($m, $pk, $prefix);
        return response()->json($m::create($data), 201);
    }

    public function update(Request $request, string $id)
    {
        $key = $request->route('key');
        [$m, $pk] = $this->cfg($key);
        $row = $m::findOrFail($id);
        $row->update($request->all());
        return response()->json($row);
    }

    public function destroy(Request $request, string $id)
    {
        $key = $request->route('key');
        [$m, $pk] = $this->cfg($key);
        $row = $m::findOrFail($id);
        try {
            $row->delete(); // FK restrictOnDelete menolak kalau masih ada anak (cascade protection)
        } catch (\Illuminate\Database\Exception\QueryException|\PDOException $e) {
            return response()->json(['message' => 'Ditolak: masih punya data di bawahnya (cascade protection)'], 422);
        }
        return response()->json(['message' => 'terhapus']);
    }
}
