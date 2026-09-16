<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\RiwayatPublikasi;
use App\Repositories\Contracts\RiwayatPublikasiRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RiwayatPublikasiService
{
    public function __construct(
        protected RiwayatPublikasiRepositoryInterface $repository
    ) {}

    private function uploadFile(?UploadedFile $file, $pegawai = null, ?string $title = null): ?string
    {
        if (!$file) return null;
        return PegawaiStorageService::store($file, $pegawai, 'publikasi', $title);
    }

    private function deleteFile(?string $path): void
    {
        PegawaiStorageService::delete($path);
    }

    public function search(?string $search, int $perPage = 15)
    {
        return $this->repository->search($search, $perPage);
    }

    public function find(int $id): RiwayatPublikasi
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

    public function create(array $data, ?UploadedFile $file = null): RiwayatPublikasi
    {
        if ($file) {
            $data['file_publikasi'] = $this->uploadFile($file, $data['pegawai_id'] ?? null, $data['judul'] ?? null);
        }
        $publikasi = $this->repository->createRiwayat($data);

        if ($file && !empty($data['pegawai_id'])) {
            $pegawai = Pegawai::find($data['pegawai_id']);
            if ($pegawai) {
                app(GoogleDriveGasService::class)->uploadDokumen($pegawai, $file, 'PUBLIKASI_ILMIAH', '05_DOKUMEN_LAINNYA', 'Dokumen Publikasi Ilmiah');
            }
        }

        return $publikasi;
    }

    public function update(int $id, array $data, ?UploadedFile $file = null): RiwayatPublikasi
    {
        $existing = $this->find($id);

        if ($file) {
            $this->deleteFile($existing->file_publikasi);
            $pegawaiTarget = $data['pegawai_id'] ?? $existing->pegawai_id;
            $judulTarget = $data['judul'] ?? $existing->judul;
            $data['file_publikasi'] = $this->uploadFile($file, $pegawaiTarget, $judulTarget);
        } else {
            unset($data['file_publikasi']);
        }

        $updated = $this->repository->updateRiwayat($id, $data);

        if ($file) {
            $pegawaiId = $data['pegawai_id'] ?? $updated->pegawai_id;
            $pegawai = Pegawai::find($pegawaiId);
            if ($pegawai) {
                app(GoogleDriveGasService::class)->uploadDokumen($pegawai, $file, 'PUBLIKASI_ILMIAH', '05_DOKUMEN_LAINNYA', 'Update Dokumen Publikasi Ilmiah');
            }
        }

        return $updated;
    }

    public function delete(int $id): bool
    {
        $existing = $this->find($id);
        $this->deleteFile($existing->file_publikasi);
        return $this->repository->deleteRiwayat($id);
    }
}
