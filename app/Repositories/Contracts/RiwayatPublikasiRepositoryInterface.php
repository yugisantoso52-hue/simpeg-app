<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatPublikasi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RiwayatPublikasiRepositoryInterface extends BaseRepositoryInterface
{
    public function search(?string $search, int $perPage = 10): LengthAwarePaginator;
    public function getByPegawai(int $pegawaiId);
    public function createRiwayat(array $data): RiwayatPublikasi;
    public function updateRiwayat(int $id, array $data): RiwayatPublikasi;
    public function deleteRiwayat(int $id): bool;
}
