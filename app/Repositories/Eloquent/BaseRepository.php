<?php

namespace App\Repositories\Eloquent;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->latest()
            ->paginate($perPage);
    }

    public function search(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->paginate($perPage);
    }

    public function find(int|string $id)
    {
        return $this->model->find($id);
    }

    public function findOrFail(int|string $id)
    {
        return $this->model->findOrFail($id);
    }

    public function first()
    {
        return $this->model->first();
    }

    public function firstWhere(string $column, mixed $value)
    {
        return $this->model
            ->where($column, $value)
            ->first();
    }

    public function findBy(string $column, mixed $value)
    {
        return $this->model
            ->where($column, $value)
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function createMany(array $data)
    {
        return $this->model->insert($data);
    }

    public function update(int|string $id, array $data)
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model->fresh();
    }

    public function updateWhere(array $conditions, array $data): int
    {
        return $this->model
            ->where($conditions)
            ->update($data);
    }

    public function delete(int|string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function deleteWhere(array $conditions): int
    {
        return $this->model
            ->where($conditions)
            ->delete();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function exists(string $column, mixed $value): bool
    {
        return $this->model
            ->where($column, $value)
            ->exists();
    }

    public function transaction(callable $callback)
    {
        return DB::transaction($callback);
    }
}