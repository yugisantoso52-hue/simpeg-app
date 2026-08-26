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
            if ($fileRencana) {
                $data['file_rencana_skp'] = $fileRencana->store('pegawai/skp', 'local');
            }

            if ($fileEvaluasi) {
                $data['file_evaluasi_skp'] = $fileEvaluasi->store('pegawai/skp', 'local');
            }

            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data, ?UploadedFile $fileRencana = null, ?UploadedFile $fileEvaluasi = null): RiwayatSkp
    {
        return DB::transaction(function () use ($id, $data, $fileRencana, $fileEvaluasi) {
            $existing = $this->repository->findOrFail($id);

            if ($fileRencana) {
                if ($existing->file_rencana_skp && Storage::disk('local')->exists($existing->file_rencana_skp)) {
                    Storage::disk('local')->delete($existing->file_rencana_skp);
                }
                $data['file_rencana_skp'] = $fileRencana->store('pegawai/skp', 'local');
            }

            if ($fileEvaluasi) {
                if ($existing->file_evaluasi_skp && Storage::disk('local')->exists($existing->file_evaluasi_skp)) {
                    Storage::disk('local')->delete($existing->file_evaluasi_skp);
                }
                $data['file_evaluasi_skp'] = $fileEvaluasi->store('pegawai/skp', 'local');
            }

            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $existing = $this->repository->findOrFail($id);

            if ($existing->file_rencana_skp && Storage::disk('local')->exists($existing->file_rencana_skp)) {
                Storage::disk('local')->delete($existing->file_rencana_skp);
            }
            if ($existing->file_evaluasi_skp && Storage::disk('local')->exists($existing->file_evaluasi_skp)) {
                Storage::disk('local')->delete($existing->file_evaluasi_skp);
            }

            return $this->repository->delete($id);
        });
    }
}
