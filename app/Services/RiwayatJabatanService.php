<?php

namespace App\Services;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Contracts\RiwayatJabatanRepositoryInterface;

class RiwayatJabatanService
{
    public function __construct(
        protected RiwayatJabatanRepositoryInterface $repository
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Listing
    |--------------------------------------------------------------------------
    */

    public function paginate(int $perPage = 10)
    {
        return $this->repository->paginate($perPage);
    }

    public function search(
        ?string $search,
        int $perPage = 10
    ) {
        return $this->repository->search(
            $search,
            $perPage
        );
    }

    public function filter(
        ?string $search,
        ?string $status,
        int $perPage = 10
    ) {
        return $this->repository->filter(
            $search,
            $status,
            $perPage
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Master Data
    |--------------------------------------------------------------------------
    */

    public function pegawai()
    {
        return Pegawai::orderBy('nama')->get();
    }

    public function jabatan()
    {
        return Jabatan::orderBy('nama_jabatan')->get();
    }

    public function unitKerja()
    {
        return UnitKerja::orderBy('nama_unit')->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Detail
    |--------------------------------------------------------------------------
    */

    public function find(int $id)
    {
        return $this->repository->findOrFail($id);
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(
        array $data,
        $file = null
    ) {

        return DB::transaction(function () use ($data, $file) {

            if ($file) {
                $data['file_sk'] = $file->store(
                    'sk-jabatan',
                    'local'
                );
            }

            /*
             * sementara
             * sampai migration status dibuat
             */

            if (isset($data['status'])) {

                $this->repository
                    ->nonaktifkanRiwayatAktif(
                        $data['pegawai_id']
                    );

                $data['status'] = 'Aktif';
            }

            $riwayat = $this->repository
                ->createRiwayat($data);

            /*
             * sinkronisasi pegawai
             * dilakukan nanti
             */

            return $riwayat;

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        int $id,
        array $data,
        $file = null
    ) {

        return DB::transaction(function () use ($id, $data, $file) {

            $riwayat = $this->repository
                ->findOrFail($id);

            if ($file) {

                if (!empty($riwayat->file_sk)) {

                    if (Storage::disk('local')->exists($riwayat->file_sk)) {
                        Storage::disk('local')->delete($riwayat->file_sk);
                    } else {
                        Storage::disk('public')->delete($riwayat->file_sk);
                    }

                }

                $data['file_sk'] = $file->store(
                    'sk-jabatan',
                    'local'
                );

            }

            return $this->repository
                ->updateRiwayat(
                    $id,
                    $data
                );

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function delete(
        int $id
    ) {

        return DB::transaction(function () use ($id) {

            $riwayat = $this->repository
                ->findOrFail($id);

            if (!empty($riwayat->file_sk)) {

                Storage::disk('public')
                    ->delete($riwayat->file_sk);

            }

            return $this->repository
                ->deleteRiwayat($id);

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Pegawai
    |--------------------------------------------------------------------------
    */

    public function getByPegawai(
        int $pegawaiId
    ) {
        return $this->repository
            ->getByPegawai($pegawaiId);
    }

    public function getAktif(
        int $pegawaiId
    ) {
        return $this->repository
            ->getAktif($pegawaiId);
    }
}