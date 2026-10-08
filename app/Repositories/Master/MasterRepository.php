<?php

namespace App\Repositories\Master;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class MasterRepository
{
    public function __construct(
        protected Model $model,
        protected string $pk,
        protected string $prefix,
        protected ?string $hasChildren = null
    ) {}

    public function all(int $perPage = 50): LengthAwarePaginator
    {
        $counts = [];
        if ($this->hasChildren) {
            $counts = ['children_count'];
        }
        return $this->model->paginate($perPage);
    }

    public function find(string $id): ?Model
    {
        return $this->model::find($id);
    }

    public function create(array $data): Model
    {
        $data[$this->pk] = $data[$this->pk] ?? \App\Support\IdGen::next($this->model::class, $this->pk, $this->prefix);
        return $this->model::create($data);
    }

    public function update(string $id, array $data): Model
    {
        $model = $this->model::findOrFail($id);
        $model->update($data);
        return $model;
    }

    /**
     * @throws \Illuminate\Database\QueryException
     */
    public function delete(string $id): void
    {
        $model = $this->model::findOrFail($id);
        $model->delete();
    }
}
