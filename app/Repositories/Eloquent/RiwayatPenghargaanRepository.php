<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatPenghargaan;
use App\Repositories\Contracts\RiwayatPenghargaanRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiwayatPenghargaanRepository extends BaseRepository implements RiwayatPenghargaanRepositoryInterface
{
    public function __construct(RiwayatPenghargaan $model)
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
                $q->where('nama_penghargaan', 'like', "%{$search}%")
                  ->orWhere('jenis_penghargaan', 'like', "%{$search}%")
                  ->orWhereHas('pegawai', function ($pegawai) use ($search) {
                      $pegawai->where('nama', 'like', "%{$search}%")
                              ->orWhere('nip', 'like', "%{$search}%");
                  });
            })
            ->latest('tanggal_terima')
            ->paginate($perPage);
    }

    public function getByPegawai(int $pegawaiId)
    {
        return $this->query()
            ->where('pegawai_id', $pegawaiId)
            ->orderByDesc('tanggal_terima')
            ->get();
    }

    public function createRiwayat(array $data): RiwayatPenghargaan
    {
        return $this->create($data);
    }

    public function updateRiwayat(int $id, array $data): RiwayatPenghargaan
    {
        return $this->update($id, $data);
    }

    public function deleteRiwayat(int $id): bool
    {
        return $this->delete($id);
    }
}
