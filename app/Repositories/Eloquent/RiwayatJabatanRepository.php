<?php

namespace App\Repositories\Eloquent;

use App\Models\RiwayatJabatan;
use App\Repositories\Contracts\RiwayatJabatanRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RiwayatJabatanRepository extends BaseRepository implements RiwayatJabatanRepositoryInterface
{
    public function __construct(RiwayatJabatan $model)
    {
        parent::__construct($model);
    }

    /**
     * Pagination dengan eager loading
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with([
                'pegawai',
                'jabatan',
                'unitKerja',
            ])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Pencarian Riwayat Jabatan
     */
    public function search(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->with([
                'pegawai',
                'jabatan',
                'unitKerja',
            ])
            ->when($search, function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    $q->whereHas('pegawai', function ($pegawai) use ($search) {
                        $pegawai->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                    })

                    ->orWhereHas('jabatan', function ($jabatan) use ($search) {
                        $jabatan->where('nama_jabatan', 'like', "%{$search}%");
                    })

                    ->orWhereHas('unitKerja', function ($unit) use ($search) {
                        $unit->where('nama_unit', 'like', "%{$search}%");
                    })

                    ->orWhere('nomor_sk', 'like', "%{$search}%");

                });

            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Filter Riwayat Jabatan
     */
    public function filter(
        ?string $search,
        ?string $status,
        int $perPage = 10
    ): LengthAwarePaginator {

        return $this->model
            ->with([
                'pegawai',
                'jabatan',
                'unitKerja',
            ])

            ->when($search, function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    $q->whereHas('pegawai', function ($pegawai) use ($search) {
                        $pegawai->where('nama', 'like', "%{$search}%")
                                ->orWhere('nip', 'like', "%{$search}%");
                    })

                    ->orWhereHas('jabatan', function ($jabatan) use ($search) {
                        $jabatan->where('nama_jabatan', 'like', "%{$search}%");
                    })

                    ->orWhereHas('unitKerja', function ($unit) use ($search) {
                        $unit->where('nama_unit', 'like', "%{$search}%");
                    })

                    ->orWhere('nomor_sk', 'like', "%{$search}%");

                });

            })

            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })

            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Riwayat Jabatan berdasarkan Pegawai
     */
    public function getByPegawai(int $pegawaiId): Collection
    {
        return $this->model
            ->with([
                'pegawai',
                'jabatan',
                'unitKerja',
            ])
            ->where('pegawai_id', $pegawaiId)
            ->orderByDesc('tmt_jabatan')
            ->get();
    }

    /**
     * Jabatan aktif pegawai
     */
    public function getAktif(int $pegawaiId): ?RiwayatJabatan
    {
        return $this->model
            ->with([
                'pegawai',
                'jabatan',
                'unitKerja',
            ])
            ->where('pegawai_id', $pegawaiId)
            ->whereIn('status', [
                'Aktif',
                'aktif',
            ])
            ->latest('tmt_jabatan')
            ->first();
    }

    /**
     * Nonaktifkan seluruh jabatan aktif
     */
    public function nonaktifkanRiwayatAktif(int $pegawaiId): void
    {
        $this->model
            ->where('pegawai_id', $pegawaiId)
            ->whereIn('status', [
                'Aktif',
                'aktif',
            ])
            ->update([
                'status' => 'Tidak Aktif',
            ]);
    }

    /**
     * Membuat Riwayat Jabatan
     */
    public function createRiwayat(array $data): RiwayatJabatan
    {
        return $this->create($data);
    }

    /**
     * Update Riwayat Jabatan
     */
    public function updateRiwayat(int $id, array $data): RiwayatJabatan
    {
        return $this->update($id, $data);
    }

    /**
     * Hapus Riwayat Jabatan
     */
    public function deleteRiwayat(int $id): bool
    {
        return $this->delete($id);
    }
}