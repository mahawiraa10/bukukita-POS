<?php

namespace App\Services\Master;

use App\Repositories\Master\MasterRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;

class HierarchyService
{
    private const MAPPING = [
        'divisions'   => ['model' => \App\Models\Division::class,   'pk' => 'id_divisi',   'prefix' => 'DIV'],
        'departments' => ['model' => \App\Models\Department::class, 'pk' => 'id_departemen', 'prefix' => 'DEP'],
        'classes'     => ['model' => \App\Models\ClassLevel::class,  'pk' => 'id_kelas',     'prefix' => 'KLS'],
        'subclasses'  => ['model' => \App\Models\Subclass::class,    'pk' => 'id_subkelas',  'prefix' => 'SUB'],
    ];

    public function all(string $key, int $perPage = 50): LengthAwarePaginator
    {
        return $this->repo($key)->all($perPage);
    }

    public function store(string $key, array $data)
    {
        return $this->repo($key)->create($data);
    }

    public function update(string $key, string $id, array $data)
    {
        return $this->repo($key)->update($id, $data);
    }

    public function destroy(string $key, string $id): ?string
    {
        try {
            $this->repo($key)->delete($id);
            return null;
        } catch (QueryException $e) {
            return 'Ditolak: masih punya data di bawahnya';
        }
    }

    private function repo(string $key): MasterRepository
    {
        $cfg = $this->mapConfig($key);
        return new MasterRepository(
            app($cfg['model']),
            $cfg['pk'],
            $cfg['prefix'],
            null
        );
    }

    private function mapConfig(string $key): array
    {
        abort_unless(array_key_exists($key, self::MAPPING), 404);
        return self::MAPPING[$key];
    }
}
