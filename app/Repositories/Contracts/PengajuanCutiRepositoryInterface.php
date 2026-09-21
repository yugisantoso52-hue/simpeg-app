<?php

namespace App\Repositories\Contracts;

use App\Models\PengajuanCuti;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PengajuanCutiRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(?string $search, ?string $jenis, ?string $status, ?int $pegawaiId = null, int $perPage = 10, ?array $bawahanIds = null): LengthAwarePaginator;

    public function getStatistics(?int $pegawaiId = null, ?array $bawahanIds = null): array;

    public function getByPegawai(int $pegawaiId): Collection;

    public function getPendingCount(?array $bawahanIds = null): int;
}
