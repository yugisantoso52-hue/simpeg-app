<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatOrganisasi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RiwayatOrganisasiRepositoryInterface extends BaseRepositoryInterface
{
    public function search(?string $search, int $perPage = 10): LengthAwarePaginator;
    public function getByPegawai(int $pegawaiId);
    public function createRiwayat(array $data): RiwayatOrganisasi;
    public function updateRiwayat(int $id, array $data): RiwayatOrganisasi;
    public function deleteRiwayat(int $id): bool;
}
