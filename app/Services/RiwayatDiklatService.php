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
        int $perPage = 10,
        ?int $pegawaiId = null
    ) {
        return $this->repository->filter(
            $search,
            $status,
            $perPage,
            $pegawaiId
        );
    }

    /**
     * Dashboard Statistic
     */
    public function statistics(?int $pegawaiId = null): array
    {
        return $this->repository->getStatistics($pegawaiId);
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
    public function pegawai(?int $pegawaiId = null)
    {
        return Pegawai::query()
            ->when($pegawaiId, fn($q) => $q->where('id', $pegawaiId))
            ->orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
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

                $data['file_sertifikat'] = PegawaiStorageService::store(
                    $file,
                    $data['pegawai_id'] ?? null,
                    'diklat',
                    $data['nama_diklat'] ?? 'diklat'
                );

            }

            if (!isset($data['status'])) {

                $data['status'] = 'Aktif';

            }

            $created = $this->repository
                ->createRiwayat($data);

            if (!empty($data['pegawai_id'])) {
                try {
                    $pegawai = Pegawai::find($data['pegawai_id']);
                    if ($pegawai) {
                        app(TalentScoringService::class)->calculatePegawaiTalent($pegawai);
                    }
                } catch (\Throwable $e) {
                    // Ignore talent scoring sync errors
                }
            }

            return $created;

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

                    PegawaiStorageService::delete($riwayat->file_sertifikat);

                }

                $pegawaiTarget = $data['pegawai_id'] ?? $riwayat->pegawai_id;
                $titleTarget   = $data['nama_diklat'] ?? $riwayat->nama_diklat ?? 'diklat';
                $data['file_sertifikat'] = PegawaiStorageService::store(
                    $file,
                    $pegawaiTarget,
                    'diklat',
                    $titleTarget
                );

            }

            $updated = $this->repository
                ->updateRiwayat(
                    $id,
                    $data
                );

            $pegawaiId = $data['pegawai_id'] ?? $riwayat->pegawai_id;
            if ($pegawaiId) {
                try {
                    $pegawai = Pegawai::find($pegawaiId);
                    if ($pegawai) {
                        app(TalentScoringService::class)->calculatePegawaiTalent($pegawai);
                    }
                } catch (\Throwable $e) {
                    // Ignore talent scoring sync errors
                }
            }

            return $updated;

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
            $pegawaiId = $riwayat->pegawai_id;

            if ($riwayat->file_sertifikat) {

                PegawaiStorageService::delete($riwayat->file_sertifikat);

            }

            $deleted = $this->repository
                ->deleteRiwayat($id);

            if ($deleted && $pegawaiId) {
                try {
                    $pegawai = Pegawai::find($pegawaiId);
                    if ($pegawai) {
                        app(TalentScoringService::class)->calculatePegawaiTalent($pegawai);
                    }
                } catch (\Throwable $e) {
                    // Ignore talent scoring sync errors
                }
            }

            return $deleted;

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