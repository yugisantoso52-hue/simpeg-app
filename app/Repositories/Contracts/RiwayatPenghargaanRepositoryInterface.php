<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatPenghargaan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RiwayatPenghargaanRepositoryInterface extends BaseRepositoryInterface
{
    public function search(?string $search, int $perPage = 10): LengthAwarePaginator;
    public function getByPegawai(int $pegawaiId);
    public function createRiwayat(array $data): RiwayatPenghargaan;
    public function updateRiwayat(int $id, array $data): RiwayatPenghargaan;
    public function deleteRiwayat(int $id): bool;
}
