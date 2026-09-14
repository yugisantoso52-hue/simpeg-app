<?php

namespace App\Repositories\Contracts;

use App\Models\TugasBelajar;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface TugasBelajarRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(?string $search, ?string $jenjang, ?string $status, ?int $pegawaiId = null, int $perPage = 10): LengthAwarePaginator;

    public function getStatistics(?int $pegawaiId = null): array;

    public function getByPegawai(int $pegawaiId): Collection;

    public function getActiveByPegawai(int $pegawaiId): ?TugasBelajar;

    public function getExpiring(int $months = 6): Collection;
}
