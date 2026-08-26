<?php

namespace App\Repositories\Contracts;

use App\Models\PengajuanCuti;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PengajuanCutiRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(?string $search, ?string $jenis, ?string $status, ?int $pegawaiId = null, int $perPage = 10): LengthAwarePaginator;

    public function getStatistics(?int $pegawaiId = null): array;

    public function getByPegawai(int $pegawaiId): Collection;

    public function getPendingCount(): int;
}
