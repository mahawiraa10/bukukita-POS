<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\ClassLevel;
use App\Models\Division;
use App\Models\Department;
use App\Models\Subclass;
use App\Support\IdGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class MasterDataController extends Controller
{
    // peta route => [model, pk, prefix, fields wajib]
    private array $map = [
        'divisions'   => [\App\Models\Division::class,   'id_divisi',       'DIV', ['nama_divisi']],
        'departments' => [\App\Models\Department::class,  'id_departemen',   'DEP', ['nama_departemen', 'id_divisi']],
        'classes'     => [\App\Models\ClassLevel::class,  'id_kelas',        'KLS', ['nama_kelas', 'id_departemen']],
        'subclasses'  => [\App\Models\Subclass::class, 'id_subkelas', 'SUB', ['nama_subkelas']],
    ];

    private function cfg(string $key): array
    {
        abort_unless(isset($this->map[$key]), 404);
        return $this->map[$key];
    }

    public function index(Request $request, string $key)
    {
        [$m] = $this->cfg($key);
        $query = $m::query();
        
        // Eager load relasi parent hierarki agar Master Produk bisa ambil nama Departemen/Divisi
        if ($key === 'classes') {
            $query->with('department');
        } elseif ($key === 'departments') {
            $query->with('division');
        } elseif ($key === 'subclasses') {
            $query->with('kelas.department');
        }

        // Scoping opsional per level parent — dipakai autocomplete cascading.
        // Tanpa param tetap balik semua (backward compatible buat Master Produk).
        if ($key === 'departments' && $request->filled('id_divisi')) {
            $query->where('id_divisi', $request->input('id_divisi'));
        } elseif ($key === 'classes') {
            if ($request->filled('id_departemen')) {
                $query->where('id_departemen', $request->input('id_departemen'));
            } elseif ($request->filled('id_divisi')) {
                // classes gak punya id_divisi → saring lewat departemen milik divisi itu
                $query->whereIn('id_departemen', Department::where('id_divisi', $request->input('id_divisi'))->pluck('id_departemen'));
            }
        } elseif ($key === 'subclasses' && $request->filled('id_kelas')) {
            $query->where('id_kelas', $request->input('id_kelas'));
        }

        return $query->withCount(in_array($key, ['divisions']) ? ['departments'] : (in_array($key, ['departments']) ? ['classes'] : (in_array($key, ['classes']) ? ['subclasses'] : [])))
            ->paginate($request->input('per_page', 50));
    }

    public function store(Request $request, string $key)
    {
        [$m, $pk, $prefix, $required] = $this->cfg($key);
        
        // Remove ID fields from validation required list
        $validationRules = [];
        foreach ($required as $field) {
            if (!str_starts_with($field, 'id_')) {
                $validationRules[$field] = 'required';
            }
        }
        $data = $request->validate($validationRules);

        // Resolve FK parent IDs from text-input names so the form can use plain names
        $resolved = [];
        if ($key === 'subclasses') {
            if ($request->filled('nama_divisi')) {
                $div = Division::where('nama_divisi', $request->input('nama_divisi'))->first();
                if (!$div) {
                    $div = Division::create([
                        'id_divisi' => IdGen::next(Division::class, 'id_divisi', 'DIV'),
                        'nama_divisi' => $request->input('nama_divisi')
                    ]);
                }
                $resolved['id_divisi'] = $div->id_divisi;
            }
            if ($request->filled('nama_departemen') && isset($resolved['id_divisi'])) {
                $dept = Department::where('nama_departemen', $request->input('nama_departemen'))
                                  ->where('id_divisi', $resolved['id_divisi'])
                                  ->first();
                if (!$dept) {
                    $dept = Department::create([
                        'id_departemen' => IdGen::next(Department::class, 'id_departemen', 'DEP'),
                        'nama_departemen' => $request->input('nama_departemen'),
                        'id_divisi' => $resolved['id_divisi']
                    ]);
                }
                $resolved['id_departemen'] = $dept->id_departemen;
            }
            if ($request->filled('nama_kelas') && isset($resolved['id_departemen'])) {
                $cls = ClassLevel::where('nama_kelas', $request->input('nama_kelas'))
                                 ->where('id_departemen', $resolved['id_departemen'])
                                 ->first();
                if (!$cls) {
                    $cls = ClassLevel::create([
                        'id_kelas' => IdGen::next(ClassLevel::class, 'id_kelas', 'KLS'),
                        'nama_kelas' => $request->input('nama_kelas'),
                        'id_departemen' => $resolved['id_departemen']
                    ]);
                }
                $resolved['id_kelas'] = $cls->id_kelas;
            }
        } elseif ($key === 'classes') {
            if ($request->filled('nama_departemen')) {
                $dept = Department::where('nama_departemen', $request->input('nama_departemen'))->first();
                if ($dept) $resolved['id_departemen'] = $dept->id_departemen;
            }
        } elseif ($key === 'departments') {
            if ($request->filled('nama_divisi')) {
                $div = Division::where('nama_divisi', $request->input('nama_divisi'))->first();
                if ($div) $resolved['id_divisi'] = $div->id_divisi;
            }
        }

        // Masukkan id hasil resolve, tapi HANYA kolom yang ada di tabelnya:
        // departments punya id_divisi, classes punya id_departemen, subclasses cuma id_kelas.
        $cols = Schema::getColumnListing((new $m)->getTable());
        foreach ($resolved as $col => $id) {
            if (in_array($col, $cols, true)) {
                $data[$col] = $id;
            }
        }

        // Enforce uniqueness for Sub-Kelas under Kelas
        if ($key === 'subclasses') {
            $existing = Subclass::where('nama_subkelas', trim($request->input('nama_subkelas')))
                                ->where('id_kelas', $resolved['id_kelas'] ?? $request->input('id_kelas'))
                                ->first();
            if ($existing) {
                return response()->json(['message' => 'Sub-Kelas sudah ada di Kelas ini.'], 422);
            }
        }
        
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
