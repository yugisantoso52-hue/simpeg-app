<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    public function all(): Collection;

    public function paginate(int $perPage = 10): LengthAwarePaginator;

    public function search(?string $search, int $perPage = 10): LengthAwarePaginator;

    public function find(int|string $id);

    public function findOrFail(int|string $id);

    public function first();

    public function firstWhere(string $column, mixed $value);

    public function findBy(string $column, mixed $value);

    public function create(array $data);

    public function createMany(array $data);

    public function update(int|string $id, array $data);

    public function updateWhere(array $conditions, array $data): int;

    public function delete(int|string $id): bool;

    public function deleteWhere(array $conditions): int;

    public function count(): int;

    public function exists(string $column, mixed $value): bool;

    public function transaction(callable $callback);
}