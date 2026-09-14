<?php

namespace App\Services;

abstract class BaseService
{
    protected $repository;

    public function all()
    {
        return $this->repository->all();
    }

    public function paginate(int $perPage = 10)
    {
        return $this->repository->paginate($perPage);
    }

    public function search(?string $search, int $perPage = 10)
    {
        return $this->repository->search($search, $perPage);
    }

    public function find(int|string $id)
    {
        return $this->repository->findOrFail($id);
    }

    public function delete(int|string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function count(): int
    {
        return $this->repository->count();
    }

    public function exists(string $column, mixed $value): bool
    {
        return $this->repository->exists($column, $value);
    }

    protected function transaction(callable $callback)
    {
        return $this->repository->transaction($callback);
    }
} // Make sure this is the absolute end of the class and no code follows it.