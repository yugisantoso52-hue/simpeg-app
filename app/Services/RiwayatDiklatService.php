<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Repositories\Contracts\RiwayatDiklatRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RiwayatDiklatService
{
    public function __construct(
        protected RiwayatDiklatRepositoryInterface $repository
    ) {
    }

    /**
     * Search
     */
    public function search(
        ?string $search,
        int $perPage = 10
    ) {
        return $this->repository->search(
            $search,
            $perPage
        );
    }

    /**
     * Filter
     */
    public function filter(
        ?string $search,
        ?string $status,
        int $perPage = 10
    ) {
        return $this->repository->filter(
            $search,
            $status,
            $perPage
        );
    }

    /**
     * Dashboard Statistic
     */
    public function statistics(): array
    {
        return $this->repository->getStatistics();
    }

    /**
     * Detail
     */
    public function find(
        int $id
    ) {
        return $this->repository->findOrFail($id);
    }

    /**
     * Data Pegawai
     */
    public function pegawai()
    {
        return Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->get();
    }

    /**
     * Simpan Riwayat Diklat
     */
    public function create(
        array $data,
        $file = null
    ) {

        return DB::transaction(function () use ($data, $file) {

            if ($file) {

                $data['file_sertifikat'] = $file->store(
                    'sertifikat-diklat',
                    'public'
                );

            }

            if (!isset($data['status'])) {

                $data['status'] = 'Aktif';

            }

            return $this->repository
                ->createRiwayat($data);

        });

    }

    /**
     * Update
     */
    public function update(
        int $id,
        array $data,
        $file = null
    ) {

        return DB::transaction(function () use ($id, $data, $file) {

            $riwayat = $this->repository
                ->findOrFail($id);

            if ($file) {

                if ($riwayat->file_sertifikat) {

                    Storage::disk('public')
                        ->delete($riwayat->file_sertifikat);

                }

                $data['file_sertifikat'] = $file->store(
                    'sertifikat-diklat',
                    'public'
                );

            }

            return $this->repository
                ->updateRiwayat(
                    $id,
                    $data
                );

        });

    }

    /**
     * Delete
     */
    public function delete(
        int $id
    ) {

        return DB::transaction(function () use ($id) {

            $riwayat = $this->repository
                ->findOrFail($id);

            if ($riwayat->file_sertifikat) {

                Storage::disk('public')
                    ->delete($riwayat->file_sertifikat);

            }

            return $this->repository
                ->deleteRiwayat($id);

        });

    }

    /**
     * Riwayat Pegawai
     */
    public function getByPegawai(
        int $pegawaiId
    ) {

        return $this->repository
            ->getByPegawai($pegawaiId);

    }

    /**
     * Diklat Aktif
     */
    public function getAktif(
        int $pegawaiId
    ) {

        return $this->repository
            ->getAktif($pegawaiId);

    }
}