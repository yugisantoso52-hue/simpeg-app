<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\RiwayatStrSip;
use App\Repositories\Contracts\RiwayatStrSipRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RiwayatStrSipService
{
    public function __construct(
        protected RiwayatStrSipRepositoryInterface $repository
    ) {}

    public function search(?string $search, int $perPage = 10)
    {
        return $this->repository->search($search, $perPage);
    }

    public function filter(?string $search, ?string $jenis, ?string $status, int $perPage = 10)
    {
        return $this->repository->filter($search, $jenis, $status, $perPage);
    }

    public function statistics(): array
    {
        return $this->repository->getStatistics();
    }

    public function find(int $id): RiwayatStrSip
    {
        return $this->repository->findOrFail($id);
    }

    public function pegawai()
    {
        return Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->get();
    }

    public function create(array $data, ?UploadedFile $file = null): RiwayatStrSip
    {
        return DB::transaction(function () use ($data, $file) {
            if ($file) {
                $data['file_dokumen'] = $file->store('pegawai/str_sip', 'local');
            }

            // Normalisasi is_seumur_hidup
            $data['is_seumur_hidup'] = !empty($data['is_seumur_hidup']) && (bool)$data['is_seumur_hidup'];
            if ($data['is_seumur_hidup']) {
                $data['tanggal_berakhir'] = null;
            }

            // Status otomatis berdasarkan tanggal berakhir
            if (empty($data['status'])) {
                if ($data['is_seumur_hidup']) {
                    $data['status'] = 'Aktif';
                } elseif (!empty($data['tanggal_berakhir']) && Carbon::parse($data['tanggal_berakhir'])->isPast()) {
                    $data['status'] = 'Kedaluwarsa';
                } else {
                    $data['status'] = 'Aktif';
                }
            }

            $strSip = $this->repository->create($data);

            if ($file && !empty($data['pegawai_id'])) {
                $pegawai = Pegawai::find($data['pegawai_id']);
                if ($pegawai) {
                    $jenisDok = 'STR_SIP_' . strtoupper($data['jenis'] ?? 'PROFESI');
                    app(GoogleDriveGasService::class)->uploadDokumen($pegawai, $file, $jenisDok, '01_DOKUMEN_UTAMA', 'Auto-sync dari Legalitas STR / SIP');
                }
            }

            return $strSip;
        });
    }

    public function update(int $id, array $data, ?UploadedFile $file = null): RiwayatStrSip
    {
        return DB::transaction(function () use ($id, $data, $file) {
            $riwayat = $this->repository->findOrFail($id);

            if ($file) {
                if ($riwayat->file_dokumen && Storage::disk('local')->exists($riwayat->file_dokumen)) {
                    Storage::disk('local')->delete($riwayat->file_dokumen);
                }
                $data['file_dokumen'] = $file->store('pegawai/str_sip', 'local');
            }

            $data['is_seumur_hidup'] = !empty($data['is_seumur_hidup']) && (bool)$data['is_seumur_hidup'];
            if ($data['is_seumur_hidup']) {
                $data['tanggal_berakhir'] = null;
            }

            if (empty($data['status'])) {
                if ($data['is_seumur_hidup']) {
                    $data['status'] = 'Aktif';
                } elseif (!empty($data['tanggal_berakhir']) && Carbon::parse($data['tanggal_berakhir'])->isPast()) {
                    $data['status'] = 'Kedaluwarsa';
                } else {
                    $data['status'] = 'Aktif';
                }
            }

            $updated = $this->repository->update($id, $data);

            if ($file) {
                $pegawaiId = $data['pegawai_id'] ?? $updated->pegawai_id;
                $pegawai = Pegawai::find($pegawaiId);
                if ($pegawai) {
                    $jenisDok = 'STR_SIP_' . strtoupper($data['jenis'] ?? 'PROFESI');
                    app(GoogleDriveGasService::class)->uploadDokumen($pegawai, $file, $jenisDok, '01_DOKUMEN_UTAMA', 'Auto-sync update dari Legalitas STR / SIP');
                }
            }

            return $updated;
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $riwayat = $this->repository->findOrFail($id);

            if ($riwayat->file_dokumen && Storage::disk('local')->exists($riwayat->file_dokumen)) {
                Storage::disk('local')->delete($riwayat->file_dokumen);
            }

            return $this->repository->delete($id);
        });
    }

    public function getByPegawai(int $pegawaiId)
    {
        return $this->repository->getByPegawai($pegawaiId);
    }
}
