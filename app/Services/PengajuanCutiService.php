<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Repositories\Contracts\PengajuanCutiRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PengajuanCutiService
{
    public function __construct(
        protected PengajuanCutiRepositoryInterface $repository
    ) {}

    public function filter(?string $search, ?string $jenis, ?string $status, ?int $pegawaiId = null, int $perPage = 10)
    {
        return $this->repository->filter($search, $jenis, $status, $pegawaiId, $perPage);
    }

    public function statistics(?int $pegawaiId = null): array
    {
        return $this->repository->getStatistics($pegawaiId);
    }

    public function find(int $id): PengajuanCuti
    {
        return $this->repository->findOrFail($id);
    }

    public function pegawaiList()
    {
        return Pegawai::orderByRaw("CASE WHEN status_pegawai = 'Aktif' THEN 0 ELSE 1 END")
            ->orderBy('nama')
            ->get();
    }

    /**
     * Menghitung jumlah hari kerja (Senin - Jumat) antara dua tanggal
     */
    public function hitungHariKerja(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end   = Carbon::parse($endDate);

        if ($start->gt($end)) {
            return 0;
        }

        $days = 0;
        $curr = $start->copy();
        while ($curr->lte($end)) {
            if (!$curr->isWeekend()) {
                $days++;
            }
            $curr->addDay();
        }

        return max(1, $days);
    }

    /**
     * Pengajuan Permohonan Cuti Baru
     */
    public function create(array $data, int $pegawaiId, ?UploadedFile $file = null): PengajuanCuti
    {
        return DB::transaction(function () use ($data, $pegawaiId, $file) {
            $pegawai = Pegawai::findOrFail($pegawaiId);

            // Hitung durasi hari kerja
            $jumlahHari = $this->hitungHariKerja($data['tanggal_mulai'], $data['tanggal_selesai']);

            // Validasi Kuota Cuti Tahunan
            if ($data['jenis_cuti'] === 'Cuti Tahunan') {
                $sisaKuota = $pegawai->sisa_cuti_tahunan;
                if ($jumlahHari > $sisaKuota) {
                    throw ValidationException::withMessages([
                        'tanggal_selesai' => "Jumlah hari cuti ({$jumlahHari} hari kerja) melebihi sisa kuota Cuti Tahunan Anda ({$sisaKuota} hari).",
                    ]);
                }
            }

            // Wajib lampiran jika cuti sakit > 1 hari
            if ($data['jenis_cuti'] === 'Cuti Sakit' && $jumlahHari > 1 && !$file) {
                throw ValidationException::withMessages([
                    'file_lampiran' => 'Cuti Sakit lebih dari 1 hari wajib menyertakan surat keterangan dokter.',
                ]);
            }

            if ($file) {
                $data['file_lampiran'] = $file->store('pegawai/cuti', 'local');
            }

            $data['pegawai_id']  = $pegawaiId;
            $data['jumlah_hari'] = $jumlahHari;
            $data['status']      = 'Menunggu Persetujuan';

            return $this->repository->create($data);
        });
    }

    /**
     * Verifikasi & Approval Pengajuan Cuti oleh Pimpinan
     */
    public function approve(int $id, array $data, int $approverUserId): PengajuanCuti
    {
        return DB::transaction(function () use ($id, $data, $approverUserId) {
            $cuti = $this->repository->findOrFail($id);

            $updateData = [
                'status'           => $data['status'],
                'catatan_pimpinan' => $data['catatan_pimpinan'] ?? null,
                'approved_by'      => $approverUserId,
                'approved_at'      => now(),
            ];

            if (!empty($data['nomor_surat'])) {
                $updateData['nomor_surat'] = $data['nomor_surat'];
            }

            return $this->repository->update($id, $updateData);
        });
    }

    /**
     * Pembatalan Permohonan Cuti oleh Pegawai (sebelum diapprove)
     */
    public function cancel(int $id, int $pegawaiId): PengajuanCuti
    {
        return DB::transaction(function () use ($id, $pegawaiId) {
            $cuti = $this->repository->findOrFail($id);

            if ($cuti->pegawai_id !== $pegawaiId) {
                throw ValidationException::withMessages([
                    'error' => 'Anda tidak memiliki hak untuk membatalkan pengajuan ini.',
                ]);
            }

            if ($cuti->status !== 'Menunggu Persetujuan') {
                throw ValidationException::withMessages([
                    'error' => 'Pengajuan cuti yang sudah diproses tidak dapat dibatalkan.',
                ]);
            }

            return $this->repository->update($id, [
                'status'           => 'Dibatalkan',
                'catatan_pimpinan' => 'Dibatalkan oleh pemohon.',
            ]);
        });
    }

    /**
     * Hapus Data Pengajuan Cuti (Khusus Admin)
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $cuti = $this->repository->findOrFail($id);

            if ($cuti->file_lampiran && Storage::disk('local')->exists($cuti->file_lampiran)) {
                Storage::disk('local')->delete($cuti->file_lampiran);
            }

            return $this->repository->delete($id);
        });
    }
}
