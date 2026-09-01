<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PegawaiRepositoryInterface extends BaseRepositoryInterface
{
    public function getAktif(): int;

    public function getPensiun(): int;

    public function getKGBBulanIni(): int;

    public function getKPBulanIni(): int;

    public function searchFiltered(?string $search, ?string $filter = null, int $perPage = 10): LengthAwarePaginator;

    /**
     * Statistik Pegawai
     */
    public function getStatistics(): array;
}