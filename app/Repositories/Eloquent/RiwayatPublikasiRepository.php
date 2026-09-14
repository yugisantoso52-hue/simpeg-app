<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatPublikasi;
use App\Repositories\Contracts\RiwayatPublikasiRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiwayatPublikasiRepository extends BaseRepository implements RiwayatPublikasiRepositoryInterface
{
    public function __construct(RiwayatPublikasi $model)
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
                $q->where('judul_publikasi', 'like', "%{$search}%")
                  ->orWhere('nama_jurnal', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%")
                  ->orWhereHas('pegawai', function ($pegawai) use ($search) {
                      $pegawai->where('nama', 'like', "%{$search}%")
                              ->orWhere('nip', 'like', "%{$search}%");
                  });
            })
            ->orderByDesc('tahun_terbit')
            ->paginate($perPage);
    }

    public function getByPegawai(int $pegawaiId)
    {
        return $this->query()
            ->where('pegawai_id', $pegawaiId)
            ->orderByDesc('tahun_terbit')
            ->get();
    }

    public function createRiwayat(array $data): RiwayatPublikasi
    {
        return $this->create($data);
    }

    public function updateRiwayat(int $id, array $data): RiwayatPublikasi
    {
        return $this->update($id, $data);
    }

    public function deleteRiwayat(int $id): bool
    {
        return $this->delete($id);
    }
}
