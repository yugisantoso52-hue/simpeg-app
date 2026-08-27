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

    private function uploadFile(?UploadedFile $file): ?string
    {
        if (!$file) return null;
        return $file->store('publikasi/dokumen', 'local');
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
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
            $data['file_publikasi'] = $this->uploadFile($file);
        }
        return $this->repository->createRiwayat($data);
    }

    public function update(int $id, array $data, ?UploadedFile $file = null): RiwayatPublikasi
    {
        $existing = $this->find($id);

        if ($file) {
            $this->deleteFile($existing->file_publikasi);
            $data['file_publikasi'] = $this->uploadFile($file);
        } else {
            unset($data['file_publikasi']);
        }

        return $this->repository->updateRiwayat($id, $data);
    }

    public function delete(int $id): bool
    {
        $existing = $this->find($id);
        $this->deleteFile($existing->file_publikasi);
        return $this->repository->deleteRiwayat($id);
    }
}
