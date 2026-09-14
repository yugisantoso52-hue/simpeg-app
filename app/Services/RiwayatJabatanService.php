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

            // 1. Nonaktifkan jabatan aktif sebelumnya
            $this->repository->nonaktifkanRiwayatAktif(
                $data['pegawai_id']
            );

            $data['status'] = 'Aktif';

            $riwayat = $this->repository->createRiwayat($data);

            // 2. Sinkronisasi data ke tabel pegawai
            $updatePegawai = [
                'jabatan_id'         => $data['jabatan_id'],
                'unit_kerja_id'      => $data['unit_kerja_id'],
                'nomor_sk_pertama'   => $data['nomor_sk'] ?? null,
                'tanggal_sk_pertama' => $data['tanggal_sk'] ?? null,
            ];
            if (!empty($data['tmt_jabatan'])) {
                $updatePegawai['tmt_sk_pertama'] = $data['tmt_jabatan'];
            }
            if (!empty($riwayat->file_sk)) {
                $updatePegawai['file_sk_pertama'] = $riwayat->file_sk;
            }
            Pegawai::where('id', $data['pegawai_id'])->update($updatePegawai);

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

            // 3. Sinkronisasi data ke tabel pegawai
            $updatePegawai = [
                'jabatan_id'         => $riwayatUpdated->jabatan_id,
                'unit_kerja_id'      => $riwayatUpdated->unit_kerja_id,
                'nomor_sk_pertama'   => $riwayatUpdated->nomor_sk ?? null,
                'tanggal_sk_pertama' => $riwayatUpdated->tanggal_sk ?? null,
            ];
            if (!empty($riwayatUpdated->tmt_jabatan)) {
                $updatePegawai['tmt_sk_pertama'] = $riwayatUpdated->tmt_jabatan;
            }
            if (!empty($riwayatUpdated->file_sk)) {
                $updatePegawai['file_sk_pertama'] = $riwayatUpdated->file_sk;
            }
            Pegawai::where('id', $riwayatUpdated->pegawai_id)->update($updatePegawai);

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

                if (Storage::disk('local')->exists($riwayat->file_sk)) {
                    Storage::disk('local')->delete($riwayat->file_sk);
                } else {
                    Storage::disk('public')->delete($riwayat->file_sk);
                }

            }

            $pegawaiId = $riwayat->pegawai_id;

            $deleted = $this->repository->deleteRiwayat($id);

            // Ambil riwayat jabatan terbaru yang masih ada
            $jabatanTerbaru = \App\Models\RiwayatJabatan::where('pegawai_id', $pegawaiId)
                ->orderBy('tmt_jabatan', 'desc')
                ->first();

            if ($jabatanTerbaru) {
                $jabatanTerbaru->update(['status' => 'Aktif']);

                $updatePegawai = [
                    'jabatan_id'         => $jabatanTerbaru->jabatan_id,
                    'unit_kerja_id'      => $jabatanTerbaru->unit_kerja_id,
                    'nomor_sk_pertama'   => $jabatanTerbaru->nomor_sk ?? null,
                    'tanggal_sk_pertama' => $jabatanTerbaru->tanggal_sk ?? null,
                    'file_sk_pertama'    => $jabatanTerbaru->file_sk ?? null,
                ];
                if (!empty($jabatanTerbaru->tmt_jabatan)) {
                    $updatePegawai['tmt_sk_pertama'] = $jabatanTerbaru->tmt_jabatan;
                }
                Pegawai::where('id', $pegawaiId)->update($updatePegawai);
            } else {
                Pegawai::where('id', $pegawaiId)->update([
                    'jabatan_id'         => null,
                    'unit_kerja_id'      => null,
                    'nomor_sk_pertama'   => null,
                    'tanggal_sk_pertama' => null,
                    'file_sk_pertama'    => null,
                ]);
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