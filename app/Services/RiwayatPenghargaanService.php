<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\RiwayatPenghargaan;
use App\Repositories\Contracts\RiwayatPenghargaanRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RiwayatPenghargaanService
{
    public function __construct(
        protected RiwayatPenghargaanRepositoryInterface $repository
    ) {}

    private function uploadFile(?UploadedFile $file, $pegawai = null, ?string $title = null): ?string
    {
        if (!$file) return null;
        return PegawaiStorageService::store($file, $pegawai, 'penghargaan', $title);
    }

    private function deleteFile(?string $path): void
    {
        PegawaiStorageService::delete($path);
    }

    public function search(?string $search, int $perPage = 15)
    {
        return $this->repository->search($search, $perPage);
    }

    public function find(int $id): RiwayatPenghargaan
    {
        return $this->repository->findOrFail($id);
    }

    public function getByPegawai(int $pegawaiId)
    {
        return $this->repository->getByPegawai($pegawaiId);
    }

    public function pegawai()
    {
        return Pegawai::orderBy('nama')->get(['id', 'nama', 'nip']);
    }

    public function create(array $data, ?UploadedFile $file = null): RiwayatPenghargaan
    {
        if ($file) {
            $data['file_sk'] = $this->uploadFile($file, $data['pegawai_id'] ?? null, $data['nama_penghargaan'] ?? null);
        }
        $penghargaan = $this->repository->createRiwayat($data);

        if ($file && !empty($data['pegawai_id'])) {
            $pegawai = Pegawai::find($data['pegawai_id']);
            if ($pegawai) {
                $namaPenghargaan = strtoupper(str_replace(' ', '_', $data['nama_penghargaan'] ?? 'PENGHARGAAN'));
                app(GoogleDriveGasService::class)->uploadDokumen($pegawai, $file, "SK_PENGHARGAAN_{$namaPenghargaan}", '05_DOKUMEN_LAINNYA', 'Riwayat Penghargaan');
            }
        }

        return $penghargaan;
    }

    public function update(int $id, array $data, ?UploadedFile $file = null): RiwayatPenghargaan
    {
        $existing = $this->find($id);

        if ($file) {
            $this->deleteFile($existing->file_sk);
            $pegawaiTarget = $data['pegawai_id'] ?? $existing->pegawai_id;
            $namaPenghargaan = $data['nama_penghargaan'] ?? $existing->nama_penghargaan;
            $data['file_sk'] = $this->uploadFile($file, $pegawaiTarget, $namaPenghargaan);
        } else {
            unset($data['file_sk']);
        }

        $updated = $this->repository->updateRiwayat($id, $data);

        if ($file) {
            $pegawaiId = $data['pegawai_id'] ?? $updated->pegawai_id;
            $pegawai = Pegawai::find($pegawaiId);
            if ($pegawai) {
                $namaPenghargaan = strtoupper(str_replace(' ', '_', $data['nama_penghargaan'] ?? $updated->nama_penghargaan ?? 'PENGHARGAAN'));
                app(GoogleDriveGasService::class)->uploadDokumen($pegawai, $file, "SK_PENGHARGAAN_{$namaPenghargaan}", '05_DOKUMEN_LAINNYA', 'Update Riwayat Penghargaan');
            }
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $existing = $this->find($id);
        $this->deleteFile($existing->file_sk);
        return $this->repository->deleteRiwayat($id);
    }
}
