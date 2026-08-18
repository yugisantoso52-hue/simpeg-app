<?php

namespace App\Repositories\Contracts;

use App\Models\RiwayatPangkat;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RiwayatPangkatRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Pagination
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /**
     * Pencarian
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
     * Riwayat Pangkat berdasarkan Pegawai
     */
    public function getByPegawai(
        int $pegawaiId
    );

    /**
     * Pangkat aktif
     */
    public function getAktif(
        int $pegawaiId
    ): ?RiwayatPangkat;

    /**
     * Nonaktifkan pangkat aktif
     */
    public function nonaktifkanRiwayatAktif(
        int $pegawaiId
    ): void;

    /**
     * Simpan Riwayat
     */
    public function createRiwayat(
        array $data
    ): RiwayatPangkat;

    /**
     * Update Riwayat
     */
    public function updateRiwayat(
        int $id,
        array $data
    ): RiwayatPangkat;

    /**
     * Hapus Riwayat
     */
    public function deleteRiwayat(
        int $id
    ): bool;

    /**
     * Statistik Riwayat Pangkat
     *
     * return:
     * [
     *     'total' => int,
     *     'aktif' => int,
     *     'nonaktif' => int
     * ]
     */
    public function getStatistics(): array;
}