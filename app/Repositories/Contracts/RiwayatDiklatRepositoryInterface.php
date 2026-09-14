<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatDiklat;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RiwayatDiklatRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Pagination
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /**
     * Search
     */
    public function search(
        ?string $search,
        int $perPage = 10
    ): LengthAwarePaginator;

    /**
     * Filter
     */
    public function filter(
        ?string $search,
        ?string $status,
        int $perPage = 10
    ): LengthAwarePaginator;

    /**
     * Statistik Dashboard
     */
    public function getStatistics(): array;

    /**
     * Riwayat berdasarkan Pegawai
     */
    public function getByPegawai(
        int $pegawaiId
    );

    /**
     * Diklat Aktif
     */
    public function getAktif(
        int $pegawaiId
    ): ?RiwayatDiklat;

    /**
     * Nonaktifkan Diklat Aktif
     */
    public function nonaktifkanRiwayatAktif(
        int $pegawaiId
    ): void;

    /**
     * Simpan
     */
    public function createRiwayat(
        array $data
    ): RiwayatDiklat;

    /**
     * Update
     */
    public function updateRiwayat(
        int $id,
        array $data
    ): RiwayatDiklat;

    /**
     * Delete
     */
    public function deleteRiwayat(
        int $id
    ): bool;
}