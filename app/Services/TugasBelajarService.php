<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\TugasBelajar;
use App\Repositories\Contracts\TugasBelajarRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TugasBelajarService
{
    public function __construct(
        protected TugasBelajarRepositoryInterface $repository
    ) {}

    public function filter(?string $search, ?string $jenjang, ?string $status, ?int $pegawaiId = null, int $perPage = 10)
    {
        return $this->repository->filter($search, $jenjang, $status, $pegawaiId, $perPage);
    }

    public function statistics(?int $pegawaiId = null): array
    {
        return $this->repository->getStatistics($pegawaiId);
    }

    public function find(int $id): TugasBelajar
    {
        return $this->repository->findOrFail($id);
    }

    public function pegawaiList()
    {
        return Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->get();
    }

    public function create(array $data, ?UploadedFile $fileSk = null, ?UploadedFile $fileProgress = null): TugasBelajar
    {
        return DB::transaction(function () use ($data, $fileSk, $fileProgress) {
            if ($fileSk) {
                $data['file_sk'] = $fileSk->store('pegawai/tugas_belajar', 'local');
            }

            if ($fileProgress) {
                $data['file_laporan_progress'] = $fileProgress->store('pegawai/tugas_belajar', 'local');
            }

            $tugasBelajar = $this->repository->create($data);

            // Sinkronisasi status_pegawai
            $this->syncPegawaiStatus((int)$data['pegawai_id']);

            if (!empty($data['pegawai_id'])) {
                $pegawai = Pegawai::find($data['pegawai_id']);
                if ($pegawai) {
                    $jenisPengembangan = strtoupper(str_replace(' ', '_', $data['jenis_pengembangan'] ?? 'TUBEL_IBEL'));
                    $driveService = app(GoogleDriveGasService::class);
                    if ($fileSk) {
                        $driveService->uploadDokumen($pegawai, $fileSk, "SK_{$jenisPengembangan}", '03_PENDIDIKAN_DIKLAT', "SK {$data['jenis_pengembangan']}");
                    }
                    if ($fileProgress) {
                        $driveService->uploadDokumen($pegawai, $fileProgress, "KHS_LAPORAN_PROGRESS", '03_PENDIDIKAN_DIKLAT', "Laporan Progress KHS {$data['jenis_pengembangan']}");
                    }
                }
            }

            return $tugasBelajar;
        });
    }

    public function update(int $id, array $data, ?UploadedFile $fileSk = null, ?UploadedFile $fileProgress = null): TugasBelajar
    {
        return DB::transaction(function () use ($id, $data, $fileSk, $fileProgress) {
            $existing = $this->repository->findOrFail($id);

            if ($fileSk) {
                if ($existing->file_sk && Storage::disk('local')->exists($existing->file_sk)) {
                    Storage::disk('local')->delete($existing->file_sk);
                }
                $data['file_sk'] = $fileSk->store('pegawai/tugas_belajar', 'local');
            }

            if ($fileProgress) {
                if ($existing->file_laporan_progress && Storage::disk('local')->exists($existing->file_laporan_progress)) {
                    Storage::disk('local')->delete($existing->file_laporan_progress);
                }
                $data['file_laporan_progress'] = $fileProgress->store('pegawai/tugas_belajar', 'local');
            }

            $tugasBelajar = $this->repository->update($id, $data);

            // Sinkronisasi status_pegawai
            $this->syncPegawaiStatus((int)$data['pegawai_id']);

            $pegawaiId = $data['pegawai_id'] ?? $tugasBelajar->pegawai_id;
            $pegawai = Pegawai::find($pegawaiId);
            if ($pegawai) {
                $jenis = strtoupper(str_replace(' ', '_', $data['jenis_pengembangan'] ?? $tugasBelajar->jenis_pengembangan ?? 'TUBEL_IBEL'));
                $driveService = app(GoogleDriveGasService::class);
                if ($fileSk) {
                    $driveService->uploadDokumen($pegawai, $fileSk, "SK_{$jenis}", '03_PENDIDIKAN_DIKLAT', "Update SK {$jenis}");
                }
                if ($fileProgress) {
                    $driveService->uploadDokumen($pegawai, $fileProgress, "KHS_LAPORAN_PROGRESS", '03_PENDIDIKAN_DIKLAT', "Update Laporan Progress KHS");
                }
            }

            return $tugasBelajar;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $existing = $this->repository->findOrFail($id);
            $pegawaiId = $existing->pegawai_id;

            if ($existing->file_sk && Storage::disk('local')->exists($existing->file_sk)) {
                Storage::disk('local')->delete($existing->file_sk);
            }
            if ($existing->file_laporan_progress && Storage::disk('local')->exists($existing->file_laporan_progress)) {
                Storage::disk('local')->delete($existing->file_laporan_progress);
            }

            $deleted = $this->repository->delete($id);

            // Sinkronisasi status_pegawai setelah penghapusan
            $this->syncPegawaiStatus($pegawaiId);

            return $deleted;
        });
    }

    /**
     * Sinkronisasi Status Pegawai di Tabel Master Pegawai
     */
    protected function syncPegawaiStatus(int $pegawaiId): void
    {
        $pegawai = Pegawai::find($pegawaiId);
        if (!$pegawai) {
            return;
        }

        // Cek apakah ada tugas belajar aktif (Sedang Studi / Perpanjangan)
        $hasActiveTubel = TugasBelajar::where('pegawai_id', $pegawaiId)
            ->where('jenis_pengembangan', 'Tugas Belajar')
            ->whereIn('status_studi', ['Sedang Studi', 'Perpanjangan'])
            ->exists();

        if ($hasActiveTubel) {
            $pegawai->update(['status_pegawai' => Pegawai::STATUS_TUGAS_BELAJAR]);
        } else {
            // Jika sebelumnya Tugas Belajar dan sekarang sudah tidak ada tubel aktif, kembalikan ke Aktif
            if ($pegawai->status_pegawai === Pegawai::STATUS_TUGAS_BELAJAR) {
                $pegawai->update(['status_pegawai' => Pegawai::STATUS_AKTIF]);
            }
        }
    }
}
