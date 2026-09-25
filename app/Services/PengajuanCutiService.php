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

    public function filter(?string $search, ?string $jenis, ?string $status, ?int $pegawaiId = null, int $perPage = 10, ?array $bawahanIds = null)
    {
        return $this->repository->filter($search, $jenis, $status, $pegawaiId, $perPage, $bawahanIds);
    }

    public function statistics(?int $pegawaiId = null, ?array $bawahanIds = null): array
    {
        return $this->repository->getStatistics($pegawaiId, $bawahanIds);
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
                $jenisTitle = $data['jenis_cuti'] ?? 'cuti';
                $data['file_lampiran'] = PegawaiStorageService::store(
                    $file,
                    $pegawaiId,
                    'cuti',
                    'lampiran_' . $jenisTitle
                );
            }

            // Tentukan Atasan Langsung & PYBMC via ApprovalHierarchyService
            $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
            $atasanPegawai = $hierarchyService->getAtasanLangsung($pegawai);
            $pybmcPegawai    = $hierarchyService->getPybmc($pegawai, $data['jenis_cuti']);

            $atasanUser = $atasanPegawai ? \App\Models\User::where('pegawai_id', $atasanPegawai->id)->first() : null;
            $pybmcUser  = $pybmcPegawai ? \App\Models\User::where('pegawai_id', $pybmcPegawai->id)->first() : null;

            $data['pegawai_id']          = $pegawaiId;
            $data['jumlah_hari']         = $jumlahHari;
            $data['atasan_langsung_id']  = $atasanUser?->id;
            $data['pybmc_id']            = $pybmcUser?->id;
            $data['status']              = 'Menunggu Persetujuan';

            $cuti = $this->repository->create($data);

            if ($file && $pegawai) {
                $jenisCuti = strtoupper(str_replace(' ', '_', $data['jenis_cuti'] ?? 'CUTI'));
                app(GoogleDriveGasService::class)->uploadDokumen($pegawai, $file, "SURAT_LAMPIRAN_{$jenisCuti}", '05_DOKUMEN_LAINNYA', "Permohonan {$data['jenis_cuti']}");
            }

            // Kirim notifikasi ke Atasan Langsung & Pimpinan
            try {
                $targets = collect();
                if ($atasanUser) {
                    $targets->push($atasanUser);
                }
                if ($pybmcUser && !$targets->contains('id', $pybmcUser->id)) {
                    $targets->push($pybmcUser);
                }

                if ($targets->isEmpty()) {
                    $targets = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'pimpinan']))->get();
                }

                \Illuminate\Support\Facades\Notification::send($targets, new \App\Notifications\CutiSubmittedNotification($cuti, $pegawai));
            } catch (\Throwable $e) {
                // Ignore notification failure to prevent transaction abort
            }

            return $cuti;
        });
    }

    /**
     * Verifikasi & Approval Pengajuan Cuti oleh Pimpinan (Atasan Langsung & PYBMC)
     */
    public function approve(int $id, array $data, int $approverUserId): PengajuanCuti
    {
        return DB::transaction(function () use ($id, $data, $approverUserId) {
            $cuti = $this->repository->findOrFail($id);
            $approver = \App\Models\User::find($approverUserId);

            $updateData = [];

            // Jika role admin atau PYBMC langsung menyetujui/menolak final
            if ($approver->hasRole('admin') || ($cuti->pybmc_id && (int)$cuti->pybmc_id === $approverUserId)) {
                $updateData['status']           = $data['status'];
                $updateData['approved_by']      = $approverUserId;
                $updateData['approved_at']      = now();
                $updateData['catatan_pimpinan'] = $data['catatan_pimpinan'] ?? $data['catatan_atasan_langsung'] ?? null;
            } else {
                // Pertimbangan Atasan Langsung (Tahap 1 PerBKN 7/2022)
                $statusAtasan = $data['status'] === 'Disetujui' ? 'Disetujui' : ($data['status'] === 'Ditolak' ? 'Ditolak' : $data['status']);
                $updateData['pertimbangan_atasan']     = $statusAtasan;
                $updateData['catatan_atasan_langsung'] = $data['catatan_pimpinan'] ?? $data['catatan_atasan_langsung'] ?? null;
                $updateData['pertimbangan_atasan_at'] = now();
                $updateData['atasan_langsung_id']      = $approverUserId;

                if ($statusAtasan === 'Disetujui') {
                    $updateData['status'] = 'Disetujui Atasan (Menunggu PYBMC)';
                } else {
                    $updateData['status']           = $data['status'];
                    $updateData['approved_by']      = $approverUserId;
                    $updateData['approved_at']      = now();
                    $updateData['catatan_pimpinan'] = $data['catatan_pimpinan'] ?? null;
                }
            }

            if (!empty($data['nomor_surat'])) {
                $updateData['nomor_surat'] = $data['nomor_surat'];
            }

            $updatedCuti = $this->repository->update($id, $updateData);

            // Kirim notifikasi lonceng ke Pegawai pemohon cuti
            try {
                $employeeUser = \App\Models\User::where('pegawai_id', $cuti->pegawai_id)->first();
                if ($employeeUser && $approver) {
                    $employeeUser->notify(new \App\Notifications\CutiStatusNotification($cuti, $approver, $updateData['status']));
                }
            } catch (\Throwable $e) {
                // Ignore notification failure
            }

            return $updatedCuti;
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

            if ($cuti->file_lampiran) {
                PegawaiStorageService::delete($cuti->file_lampiran);
            }

            return $this->repository->delete($id);
        });
    }
}
