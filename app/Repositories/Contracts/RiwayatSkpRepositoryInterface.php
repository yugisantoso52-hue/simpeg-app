<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatSkp;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RiwayatSkpRepositoryInterface extends BaseRepositoryInterface
{
    public function filter(?string $search, ?int $tahun, ?string $predikat, ?int $pegawaiId = null, int $perPage = 10): LengthAwarePaginator;

    public function getStatistics(?int $pegawaiId = null): array;

    public function getByPegawai(int $pegawaiId): Collection;

    public function findByPegawaiAndTahun(int $pegawaiId, int $tahun): ?RiwayatSkp;
}
