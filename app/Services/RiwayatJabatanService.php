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
        return Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->get();
    }

    public function jabatan()
    {
        return Jabatan::with('unitKerja')->get()->sortByDesc('hierarchy_order')->values();
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
                $data['file_sk'] = PegawaiStorageService::store(
                    $file,
                    $data['pegawai_id'] ?? null,
                    'sk_jabatan'
                );
            }

            // 1. Nonaktifkan jabatan aktif sebelumnya
            $this->repository->nonaktifkanRiwayatAktif(
                $data['pegawai_id']
            );

            $data['status'] = 'Aktif';

            $riwayat = $this->repository->createRiwayat($data);

            // 2. Sinkronisasi data jabatan terkini ke tabel pegawai (tanpa menimpa SK Pertama CPNS)
            $pegawai = Pegawai::find($data['pegawai_id']);
            if ($pegawai) {
                app(PegawaiService::class)->syncPegawaiFromHistories($pegawai);
            }

            return $riwayat;

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    |
    */

    public function update(
        int $id,
        array $data,
        $file = null
    ) {

        return DB::transaction(function () use ($id, $data, $file) {

            $riwayat = $this->repository->findOrFail($id);

            if ($file) {

                if (!empty($riwayat->file_sk)) {
                    PegawaiStorageService::delete($riwayat->file_sk);
                }

                $pegawaiTarget = $data['pegawai_id'] ?? $riwayat->pegawai_id;
                $data['file_sk'] = PegawaiStorageService::store(
                    $file,
                    $pegawaiTarget,
                    'sk_jabatan'
                );

            }

            // 1. Nonaktifkan riwayat jabatan lainnya milik pegawai ini terlebih dahulu
            $this->repository->nonaktifkanRiwayatAktif(
                $riwayat->pegawai_id
            );

            $data['status'] = 'Aktif';

            // 2. Update record riwayat jabatan ini
            $riwayatUpdated = $this->repository->updateRiwayat(
                $id,
                $data
            );

            // 3. Sinkronisasi data jabatan terkini ke tabel pegawai (tanpa menimpa SK Pertama CPNS)
            $pegawai = Pegawai::find($riwayatUpdated->pegawai_id);
            if ($pegawai) {
                app(PegawaiService::class)->syncPegawaiFromHistories($pegawai);
            }

            return $riwayatUpdated;

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    |
    */

    public function delete(
        int $id
    ) {

        return DB::transaction(function () use ($id) {

            $riwayat = $this->repository->findOrFail($id);

            if (!empty($riwayat->file_sk)) {
                PegawaiStorageService::delete($riwayat->file_sk);
            }

            $pegawaiId = $riwayat->pegawai_id;

            $deleted = $this->repository->deleteRiwayat($id);

            // Ambil riwayat jabatan terbaru yang masih ada
            $jabatanTerbaru = \App\Models\RiwayatJabatan::where('pegawai_id', $pegawaiId)
                ->orderBy('tmt_jabatan', 'desc')
                ->first();

            if ($jabatanTerbaru) {
                $jabatanTerbaru->update(['status' => 'Aktif']);
            }

            $pegawai = Pegawai::find($pegawaiId);
            if ($pegawai) {
                app(PegawaiService::class)->syncPegawaiFromHistories($pegawai);
            }

            return $deleted;

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