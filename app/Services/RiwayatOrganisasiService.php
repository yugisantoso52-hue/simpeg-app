<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\RiwayatOrganisasi;
use App\Repositories\Contracts\RiwayatOrganisasiRepositoryInterface;

class RiwayatOrganisasiService
{
    public function __construct(
        protected RiwayatOrganisasiRepositoryInterface $repository
    ) {}

    public function search(?string $search, int $perPage = 15)
    {
        return $this->repository->search($search, $perPage);
    }

    public function find(int $id): RiwayatOrganisasi
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

    public function create(array $data): RiwayatOrganisasi
    {
        // Normalize masih_aktif checkbox (might not be present if unchecked)
        $data['masih_aktif'] = isset($data['masih_aktif']) ? (bool)$data['masih_aktif'] : false;

        // Jika masih aktif, kosongkan tahun_selesai
        if ($data['masih_aktif']) {
            $data['tahun_selesai'] = null;
        }

        return $this->repository->createRiwayat($data);
    }

    public function update(int $id, array $data): RiwayatOrganisasi
    {
        $data['masih_aktif'] = isset($data['masih_aktif']) ? (bool)$data['masih_aktif'] : false;

        if ($data['masih_aktif']) {
            $data['tahun_selesai'] = null;
        }

        return $this->repository->updateRiwayat($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->deleteRiwayat($id);
    }
}
