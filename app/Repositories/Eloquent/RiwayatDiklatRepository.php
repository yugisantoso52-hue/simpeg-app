<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatDiklat;
use App\Repositories\Contracts\RiwayatDiklatRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiwayatDiklatRepository extends BaseRepository implements RiwayatDiklatRepositoryInterface
{
    public function __construct(RiwayatDiklat $model)
    {
        parent::__construct($model);
    }

    /**
     * Base Query
     */
    protected function query()
    {
        return $this->model->with([
            'pegawai',
        ]);
    }

    /**
     * Pagination
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->latest('tanggal_mulai')
            ->paginate($perPage);
    }

    /**
     * Search
     */
    public function search(
        ?string $search,
        int $perPage = 10
    ): LengthAwarePaginator {

        return $this->query()

            ->when($search, function ($q) use ($search) {

                $q->where('nama_diklat', 'like', "%{$search}%")

                  ->orWhere('penyelenggara', 'like', "%{$search}%")

                  ->orWhereHas('pegawai', function ($pegawai) use ($search) {

                      $pegawai->where('nama', 'like', "%{$search}%")
                              ->orWhere('nip', 'like', "%{$search}%");

                  });

            })

            ->latest('tanggal_mulai')

            ->paginate($perPage);
    }

    /**
     * Filter
     */
    public function filter(
        ?string $search,
        ?string $status,
        int $perPage = 10
    ): LengthAwarePaginator {

        return $this->query()

            ->when($search, function ($q) use ($search) {

                $q->where('nama_diklat', 'like', "%{$search}%")

                  ->orWhere('penyelenggara', 'like', "%{$search}%")

                  ->orWhereHas('pegawai', function ($pegawai) use ($search) {

                      $pegawai->where('nama', 'like', "%{$search}%")
                              ->orWhere('nip', 'like', "%{$search}%");

                  });

            })

            ->when($status, function ($q) use ($status) {

                $q->where('status', $status);

            })

            ->latest('tanggal_mulai')

            ->paginate($perPage);
    }

    /**
     * Statistik Dashboard
     */
    public function getStatistics(): array
    {
        return [

            'total' => $this->model->count(),

            'aktif' => $this->model
                ->where('status', 'Aktif')
                ->count(),

            'nonaktif' => $this->model
                ->where('status', 'Tidak Aktif')
                ->count(),

        ];
    }

    /**
     * Riwayat Pegawai
     */
    public function getByPegawai(
        int $pegawaiId
    ) {

        return $this->query()

            ->where('pegawai_id', $pegawaiId)

            ->orderByDesc('tanggal_mulai')

            ->get();

    }

    /**
     * Diklat Aktif
     */
    public function getAktif(
        int $pegawaiId
    ): ?RiwayatDiklat {

        return $this->query()

            ->where('pegawai_id', $pegawaiId)

            ->where('status', 'Aktif')

            ->latest('tanggal_mulai')

            ->first();

    }

    /**
     * Nonaktifkan Riwayat Aktif
     */
    public function nonaktifkanRiwayatAktif(
        int $pegawaiId
    ): void {

        $this->model

            ->where('pegawai_id', $pegawaiId)

            ->where('status', 'Aktif')

            ->update([
                'status' => 'Tidak Aktif'
            ]);

    }

    /**
     * Simpan
     */
    public function createRiwayat(
        array $data
    ): RiwayatDiklat {

        return $this->create($data);

    }

    /**
     * Update
     */
    public function updateRiwayat(
        int $id,
        array $data
    ): RiwayatDiklat {

        return $this->update($id, $data);

    }

    /**
     * Hapus
     */
    public function deleteRiwayat(
        int $id
    ): bool {

        return $this->delete($id);

    }
}