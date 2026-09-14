<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatOrganisasi;
use App\Repositories\Contracts\RiwayatOrganisasiRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiwayatOrganisasiRepository extends BaseRepository implements RiwayatOrganisasiRepositoryInterface
{
    public function __construct(RiwayatOrganisasi $model)
    {
        parent::__construct($model);
    }

    protected function query()
    {
        return $this->model->with(['pegawai']);
    }

    public function search(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->when($search, function ($q) use ($search) {
                $q->where('nama_organisasi', 'like', "%{$search}%")
                  ->orWhere('jabatan_organisasi', 'like', "%{$search}%")
                  ->orWhereHas('pegawai', function ($pegawai) use ($search) {
                      $pegawai->where('nama', 'like', "%{$search}%")
                              ->orWhere('nip', 'like', "%{$search}%");
                  });
            })
            ->orderByDesc('tahun_mulai')
            ->paginate($perPage);
    }

    public function getByPegawai(int $pegawaiId)
    {
        return $this->query()
            ->where('pegawai_id', $pegawaiId)
            ->orderByDesc('tahun_mulai')
            ->get();
    }

    public function createRiwayat(array $data): RiwayatOrganisasi
    {
        return $this->create($data);
    }

    public function updateRiwayat(int $id, array $data): RiwayatOrganisasi
    {
        return $this->update($id, $data);
    }

    public function deleteRiwayat(int $id): bool
    {
        return $this->delete($id);
    }
}
