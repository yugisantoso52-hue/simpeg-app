<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatPendidikan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RiwayatPendidikanRepositoryInterface
{
    public function paginate(?string $search = null): LengthAwarePaginator;

    public function find(int $id): ?RiwayatPendidikan;

    public function create(array $data): RiwayatPendidikan;

    public function update(int $id, array $data): RiwayatPendidikan;

    public function delete(int $id): bool;
}