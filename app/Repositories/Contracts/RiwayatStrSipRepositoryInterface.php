<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatStrSip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RiwayatStrSipRepositoryInterface extends BaseRepositoryInterface
{
    public function search(?string $search, int $perPage = 10): LengthAwarePaginator;

    public function filter(?string $search, ?string $jenis, ?string $status, int $perPage = 10): LengthAwarePaginator;

    public function getStatistics(): array;

    public function getByPegawai(int $pegawaiId): Collection;

    public function getExpiring(int $months = 6): Collection;
}
