<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatPangkat;
use App\Repositories\Contracts\RiwayatPangkatRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RiwayatPangkatRepository extends BaseRepository implements RiwayatPangkatRepositoryInterface
{
    public function __construct(RiwayatPangkat $model)
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
            'golongan',
        ]);
    }

    /**
     * Pagination
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->query()
            ->latest('tmt')
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

                $q->whereHas('pegawai', function ($pegawai) use ($search) {

                    $pegawai->where('nama', 'like', "%{$search}%");

                });

            })

            ->latest('tmt')

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

                $q->whereHas('pegawai', function ($pegawai) use ($search) {

                    $pegawai->where('nama', 'like', "%{$search}%");

                });

            })

            ->when($status, function ($q) use ($status) {

                $q->where('status', $status);

            })

            ->latest('tmt')

            ->paginate($perPage);

    }

    /**
     * Semua Riwayat Pegawai
     */
    public function getByPegawai(int $pegawaiId)
    {
        return $this->query()

            ->where('pegawai_id', $pegawaiId)

            ->orderByDesc('tmt')

            ->get();
    }

    /**
     * Riwayat Aktif
     */
    public function getAktif(
        int $pegawaiId
    ): ?RiwayatPangkat {

        return $this->query()

            ->where('pegawai_id', $pegawaiId)

            ->where('status', 'aktif')

            ->latest('tmt')

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

            ->where('status', 'aktif')

            ->update([
                'status' => 'nonaktif'
            ]);

    }

    /**
     * Simpan Riwayat
     */
    public function createRiwayat(
        array $data
    ): RiwayatPangkat {

        return $this->create($data);

    }

    /**
     * Update Riwayat
     */
    public function updateRiwayat(
        int $id,
        array $data
    ): RiwayatPangkat {

        return $this->update($id, $data);

    }

    /**
     * Hapus Riwayat
     */
    public function deleteRiwayat(
        int $id
    ): bool {

        return $this->delete($id);

    }

    /**
     * Statistik Riwayat Pangkat
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->model->count(),

            'aktif' => $this->model
                ->where('status', 'aktif')
                ->count(),

            'nonaktif' => $this->model
                ->where('status', 'nonaktif')
                ->count(),
        ];
    }
}