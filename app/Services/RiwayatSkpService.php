<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\RiwayatSkp;
use App\Repositories\Contracts\RiwayatSkpRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RiwayatSkpService
{
    public function __construct(
        protected RiwayatSkpRepositoryInterface $repository
    ) {}

    public function filter(?string $search, ?int $tahun, ?string $predikat, ?int $pegawaiId = null, int $perPage = 10)
    {
        return $this->repository->filter($search, $tahun, $predikat, $pegawaiId, $perPage);
    }

    public function statistics(?int $pegawaiId = null): array
    {
        return $this->repository->getStatistics($pegawaiId);
    }

    public function find(int $id): RiwayatSkp
    {
        return $this->repository->findOrFail($id);
    }

    public function pegawaiList()
    {
        return Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->get();
    }

    public function create(array $data, ?UploadedFile $fileRencana = null, ?UploadedFile $fileEvaluasi = null): RiwayatSkp
    {
        return DB::transaction(function () use ($data, $fileRencana, $fileEvaluasi) {
            $tahun = $data['tahun'] ?? date('Y');

            if ($fileRencana) {
                $data['file_rencana_skp'] = PegawaiStorageService::store($fileRencana, $data['pegawai_id'] ?? null, 'skp', "rencana_{$tahun}");
            }

            if ($fileEvaluasi) {
                $data['file_evaluasi_skp'] = PegawaiStorageService::store($fileEvaluasi, $data['pegawai_id'] ?? null, 'skp', "evaluasi_{$tahun}");
            }

            $skp = $this->repository->create($data);

            if (!empty($data['pegawai_id'])) {
                $pegawai = Pegawai::find($data['pegawai_id']);
                if ($pegawai) {
                    $driveService = app(GoogleDriveGasService::class);
                    if ($fileRencana) {
                        $driveService->uploadDokumen($pegawai, $fileRencana, "SKP_RENCANA_{$tahun}", '04_KINERJA_PENILAIAN', "Form SKP Rencana Tahun {$tahun}");
                    }
                    if ($fileEvaluasi) {
                        $driveService->uploadDokumen($pegawai, $fileEvaluasi, "SKP_EVALUASI_{$tahun}", '04_KINERJA_PENILAIAN', "Form SKP Evaluasi Tahun {$tahun}");
                    }
                }
            }

            return $skp;
        });
    }

    public function update(int $id, array $data, ?UploadedFile $fileRencana = null, ?UploadedFile $fileEvaluasi = null): RiwayatSkp
    {
        return DB::transaction(function () use ($id, $data, $fileRencana, $fileEvaluasi) {
            $existing = $this->repository->findOrFail($id);
            $pegawaiId = $data['pegawai_id'] ?? $existing->pegawai_id;
            $tahun = $data['tahun'] ?? $existing->tahun ?? date('Y');

            if ($fileRencana) {
                if ($existing->file_rencana_skp) {
                    PegawaiStorageService::delete($existing->file_rencana_skp);
                }
                $data['file_rencana_skp'] = PegawaiStorageService::store($fileRencana, $pegawaiId, 'skp', "rencana_{$tahun}");
            }

            if ($fileEvaluasi) {
                if ($existing->file_evaluasi_skp) {
                    PegawaiStorageService::delete($existing->file_evaluasi_skp);
                }
                $data['file_evaluasi_skp'] = PegawaiStorageService::store($fileEvaluasi, $pegawaiId, 'skp', "evaluasi_{$tahun}");
            }

            $updated = $this->repository->update($id, $data);

            $pegawai = Pegawai::find($pegawaiId);
            if ($pegawai) {
                $driveService = app(GoogleDriveGasService::class);
                if ($fileRencana) {
                    $driveService->uploadDokumen($pegawai, $fileRencana, "SKP_RENCANA_{$tahun}", '04_KINERJA_PENILAIAN', "Update SKP Rencana Tahun {$tahun}");
                }
                if ($fileEvaluasi) {
                    $driveService->uploadDokumen($pegawai, $fileEvaluasi, "SKP_EVALUASI_{$tahun}", '04_KINERJA_PENILAIAN', "Update SKP Evaluasi Tahun {$tahun}");
                }
            }

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $existing = $this->repository->findOrFail($id);

            if ($existing->file_rencana_skp) {
                PegawaiStorageService::delete($existing->file_rencana_skp);
            }
            if ($existing->file_evaluasi_skp) {
                PegawaiStorageService::delete($existing->file_evaluasi_skp);
            }

            return $this->repository->delete($id);
        });
    }
}
