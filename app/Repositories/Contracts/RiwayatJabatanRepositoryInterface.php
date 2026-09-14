<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatJabatan;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RiwayatJabatanRepositoryInterface extends BaseRepositoryInterface
{
    public function search(
        ?string $search,
        int $perPage = 10
    ): LengthAwarePaginator;

    public function filter(
        ?string $search,
        ?string $status,
        int $perPage = 10
    ): LengthAwarePaginator;

    public function getByPegawai(
        int $pegawaiId
    );

    public function getAktif(
        int $pegawaiId
    ): ?RiwayatJabatan;

    public function nonaktifkanRiwayatAktif(
        int $pegawaiId
    ): void;

    public function createRiwayat(
        array $data
    ): RiwayatJabatan;

    public function updateRiwayat(
        int $id,
        array $data
    ): RiwayatJabatan;

    public function deleteRiwayat(
        int $id
    ): bool;
}