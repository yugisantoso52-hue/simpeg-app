<?php

namespace App\Repositories\Contracts;

use App\Models\UnitKerja;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UnitKerjaRepositoryInterface
{
    public function paginate(?string $search = null, int $perPage = 10): LengthAwarePaginator;

    public function all(): Collection; // Tambahan untuk mengambil semua data (untuk dropdown)

    public function findOrFail(int|string $id): UnitKerja;

    public function create(array $data): UnitKerja;

    public function update(int|string $id, array $data): UnitKerja;

    public function delete(int|string $id): bool;
}