<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Golongan;
use App\Repositories\Contracts\RiwayatPangkatRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RiwayatPangkatService
{
    public function __construct(
        protected RiwayatPangkatRepositoryInterface $repository
    ) {
    }

    /**
     * Search
     */
    public function search(
        ?string $search,
        int $perPage = 10
    ) {
        return $this->repository->search(
            $search,
            $perPage
        );
    }

    /**
     * Filter
     */
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

    /**
     * Detail
     */
    public function find(
        int $id
    ) {
        return $this->repository->findOrFail($id);
    }

    /**
     * Data Pegawai
     */
    public function pegawai()
    {
        return Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->get();
    }

    /**
     * Data Golongan
     */
    public function golongan()
    {
        return Golongan::orderBy('nama_golongan')->get();
    }

    /**
     * Simpan Riwayat Pangkat
     */
    public function create(
        array $data,
        $file = null
    ) {
        return DB::transaction(function () use ($data, $file) {

            if ($file) {
                $data['file_sk'] = $file->store(
                    'sk-pangkat',
                    'local'
                );
            }

            $data['status'] = 'aktif';

            // 1. Nonaktifkan pangkat sebelumnya
            $this->repository->nonaktifkanRiwayatAktif(
                $data['pegawai_id']
            );

            // 2. Buat record riwayat pangkat baru
            $riwayat = $this->repository->createRiwayat($data);

            // 3. Update data pangkat aktif terbaru di tabel Pegawai (Gunakan null-check Carbon)
            $updatePegawai = [
                'golongan_id'          => $data['golongan_id'],
                'tmt_pangkat_terakhir' => $data['tmt'] ?? null,
                'kp_berikutnya'        => !empty($data['tmt']) ? Carbon::parse($data['tmt'])->addYears(4) : null,
            ];
            Pegawai::where('id', $data['pegawai_id'])->update($updatePegawai);

            return $riwayat;
        });
    }

    /**
     * Update Riwayat Pangkat
     */
    public function update(
        int $id,
        array $data,
        $file = null
    ) {
        return DB::transaction(function () use ($id, $data, $file) {

            $riwayat = $this->repository->findOrFail($id);

            if ($file) {
                if ($riwayat->file_sk) {
                    if (Storage::disk('local')->exists($riwayat->file_sk)) {
                        Storage::disk('local')->delete($riwayat->file_sk);
                    } else {
                        Storage::disk('public')->delete($riwayat->file_sk);
                    }
                }

                $data['file_sk'] = $file->store(
                    'sk-pangkat',
                    'local'
                );
            }

            // Set status menjadi aktif saat diperbarui
            $data['status'] = 'aktif';

            // 1. Nonaktifkan riwayat pangkat lainnya milik pegawai ini terlebih dahulu
            $this->repository->nonaktifkanRiwayatAktif(
                $riwayat->pegawai_id
            );

            // 2. Update record riwayat pangkat ini
            $riwayatUpdated = $this->repository->updateRiwayat(
                $id,
                $data
            );

            // 3. Guard Carbon::parse(null) agar tidak crash jika tmt null
            $kpBerikutnya = !empty($riwayatUpdated->tmt) 
                ? Carbon::parse($riwayatUpdated->tmt)->addYears(4) 
                : null;

            $updatePegawai = [
                'golongan_id'          => $riwayatUpdated->golongan_id,
                'tmt_pangkat_terakhir' => $riwayatUpdated->tmt,
                'kp_berikutnya'        => $kpBerikutnya,
            ];
            Pegawai::where('id', $riwayatUpdated->pegawai_id)->update($updatePegawai);

            return $riwayatUpdated;
        });
    }

    /**
     * Hapus Riwayat Pangkat
     */
    public function delete(
        int $id
    ) {
        return DB::transaction(function () use ($id) {

            $riwayat = $this->repository->findOrFail($id);

            if ($riwayat->file_sk) {
                Storage::disk('public')->delete($riwayat->file_sk);
            }

            // Ambil info pegawai sebelum riwayat dihapus
            $pegawaiId = $riwayat->pegawai_id;

            // Hapus riwayat pangkat
            $deleted = $this->repository->deleteRiwayat($id);

            // Ambil riwayat pangkat terbaru yang masih ada (jika ada)
            $pangkatTerbaru = \App\Models\RiwayatPangkat::where('pegawai_id', $pegawaiId)
                ->orderBy('tmt', 'desc')
                ->first();

            if ($pangkatTerbaru) {
                // Aktifkan kembali pangkat terakhir yang tersisa
                $pangkatTerbaru->update(['status' => 'aktif']);

                $kpBerikutnya = !empty($pangkatTerbaru->tmt) 
                    ? Carbon::parse($pangkatTerbaru->tmt)->addYears(4) 
                    : null;

                Pegawai::where('id', $pegawaiId)->update([
                    'golongan_id'          => $pangkatTerbaru->golongan_id,
                    'tmt_pangkat_terakhir' => $pangkatTerbaru->tmt,
                    'kp_berikutnya'        => $kpBerikutnya,
                ]);
            } else {
                // Jika tidak ada riwayat pangkat sama sekali, kosongkan di tabel pegawai
                Pegawai::where('id', $pegawaiId)->update([
                    'golongan_id'          => null,
                    'tmt_pangkat_terakhir' => null,
                    'kp_berikutnya'        => null,
                ]);
            }

            return $deleted;
        });
    }

    /**
     * Riwayat berdasarkan Pegawai
     */
    public function getByPegawai(
        int $pegawaiId
    ) {
        return $this->repository->getByPegawai($pegawaiId);
    }

    /**
     * Pangkat Aktif
     */
    public function getAktif(
        int $pegawaiId
    ) {
        return $this->repository->getAktif($pegawaiId);
    }

    /**
     * Statistik Riwayat Pangkat
     */
    public function getStatistics(): array
    {
        return $this->repository->getStatistics();
    }
}