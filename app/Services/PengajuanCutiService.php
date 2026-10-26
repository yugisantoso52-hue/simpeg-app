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

            // Tentukan Atasan Langsung & PYBMC via ApprovalHierarchyService sesuai Ketetapan Cuti
            $atasanUser = null;
            $pybmcUser  = null;
            try {
                $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
                $atasanPegawai = $hierarchyService->getAtasanLangsungCuti($pegawai);
                $pybmcPegawai  = $hierarchyService->getPybmcCuti($pegawai, $data['jenis_cuti'] ?? null);

                $atasanUser = $atasanPegawai ? \App\Models\User::where('pegawai_id', $atasanPegawai->id)->first() : null;
                $pybmcUser  = $pybmcPegawai ? \App\Models\User::where('pegawai_id', $pybmcPegawai->id)->first() : null;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal menentukan hierarki atasan/PYBMC cuti: ' . $e->getMessage());
            }

            $data['pegawai_id']          = $pegawaiId;
            $data['jumlah_hari']         = $jumlahHari;
            $data['atasan_langsung_id']  = $atasanUser?->id;
            $data['pybmc_id']            = $pybmcUser?->id;
            $data['status']              = 'Menunggu Persetujuan';

            $cuti = $this->repository->create($data);

            if ($file && $pegawai) {
                try {
                    $jenisCuti = strtoupper(str_replace(' ', '_', $data['jenis_cuti'] ?? 'CUTI'));
                    app(GoogleDriveGasService::class)->uploadDokumen($pegawai, $file, "SURAT_LAMPIRAN_{$jenisCuti}", '05_DOKUMEN_LAINNYA', "Permohonan {$data['jenis_cuti']}");
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Gagal mencatat arsip dokumen cuti: ' . $e->getMessage());
                }
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

            if (!$approver) {
                throw ValidationException::withMessages(['error' => 'Data pengguna verifikator tidak ditemukan.']);
            }

            $updateData = [];
            $isKeputusanFinal = false;

            $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
            $isAdmin   = $approver->isScreeningAdmin();
            $isKaPokja = $approver->isKaPokjaKepeg();

            if ($isAdmin || $isKaPokja) {
                throw ValidationException::withMessages([
                    'error' => 'Otorisasi Ditolak: Wewenang Admin Kepegawaian (Screening) dan Ka Pokja Keu-Kepeg (Paraf Koordinasi) tidak mencakup penetapan TTE Pertimbangan Atasan Langsung maupun Keputusan PYBMC.',
                ]);
            }

            // User dengan role 'pimpinan' langsung di tabel roles (tanpa pegawai spesifik, misal akun pengujian)
            // tetap diizinkan, namun jika user terhubung ke Pegawai struktural, wajib mengikuti matriks hierarki Cuti
            $isGenericPimpinan = !$approver->pegawai_id && $approver->hasRole('pimpinan');

            $isPybmc = !$isKaPokja && (
                $isGenericPimpinan
                || ($cuti->pybmc_id && (int)$cuti->pybmc_id === $approverUserId)
                || ($approver->pegawai && $cuti->pegawai && $hierarchyService->isPybmcCuti($approver->pegawai, $cuti->pegawai))
            );

            $isAtasanLgs = !$isKaPokja && (
                $isGenericPimpinan
                || ($approver->pegawai && $cuti->pegawai && $hierarchyService->isAtasanLangsungCuti($approver->pegawai, $cuti->pegawai))
                || ($cuti->atasan_langsung_id && (int)$cuti->atasan_langsung_id === $approverUserId && !$approver->isKaPokjaKepeg())
            );

            // Proteksi Otorisasi Hierarki: Cegah Approval Bypass
            if (!$isAtasanLgs && !$isPybmc) {
                throw ValidationException::withMessages([
                    'error' => 'Anda tidak memiliki hak wewenang hierarkis untuk menetapkan Pertimbangan Atasan Langsung atau Keputusan PYBMC pada permohonan cuti ini.',
                ]);
            }

            // Jika pertimbangan atasan belum ada dan bukan generic pimpinan yang langsung menyertakan nomor surat,
            // maka aksi ini adalah TAHAP 3 (VI. PERTIMBANGAN ATASAN LANGSUNG)
            $isTahap1 = empty($cuti->pertimbangan_atasan) && (!$isGenericPimpinan || empty($data['nomor_surat']));

            if ($isTahap1) {
                if (!$isAtasanLgs) {
                    throw ValidationException::withMessages([
                        'error' => 'Otorisasi Ditolak: Pertimbangan Atasan Langsung (Bagian VI) hanya dapat diberikan oleh Atasan Langsung pemohon cuti.',
                    ]);
                }

                if (!$isGenericPimpinan && !$cuti->paraf_kapokja_at) {
                    throw ValidationException::withMessages([
                        'error' => 'SOP Tata Naskah: Berkas permohonan cuti harus melalui Tahap 1 (Admin Screening) dan Tahap 2 (Paraf Ka Pokja Keu-Kepeg) terlebih dahulu sebelum diberikan Pertimbangan Atasan Langsung.',
                    ]);
                }
            } else {
                if (!$isPybmc) {
                    throw ValidationException::withMessages([
                        'error' => 'Otorisasi Ditolak: Keputusan akhir cuti (Bagian VII) hanya dapat ditetapkan oleh Pejabat Yang Berwenang Memberikan Cuti (PYBMC).',
                    ]);
                }

                if (!$isGenericPimpinan && empty($cuti->pertimbangan_atasan)) {
                    throw ValidationException::withMessages([
                        'error' => 'SOP Tata Naskah: Keputusan Pejabat Berwenang (PYBMC) hanya dapat ditetapkan setelah Atasan Langsung memberikan pertimbangan.',
                    ]);
                }
            }

            if ($isTahap1) {
                // Pertimbangan Atasan Langsung (Bagian VI Form Cuti BKN)
                $statusAtasan = $data['status'] === 'Disetujui' ? 'Disetujui' : ($data['status'] === 'Ditolak' ? 'Ditolak' : $data['status']);
                $updateData['pertimbangan_atasan']     = $statusAtasan;
                $updateData['catatan_atasan_langsung'] = $data['catatan_atasan_langsung'] ?? $data['catatan_pimpinan'] ?? null;
                $updateData['pertimbangan_atasan_at']  = now();

                if ($approver->isKabagUmum()) {
                    $updateData['paraf_kabag_at'] = now();
                    $updateData['paraf_kabag_by'] = $approver->pegawai_id ?? $approver->pegawai?->id;
                }

                // Pastikan atasan_langsung_id merujuk ke Pejabat Atasan Langsung Cuti yang sah (misal Kepala Bagian Umum untuk Tendik)
                $expectedAtasanPegawai = $cuti->pegawai ? $hierarchyService->getAtasanLangsungCuti($cuti->pegawai) : null;
                $expectedAtasanUser    = $expectedAtasanPegawai ? \App\Models\User::where('pegawai_id', $expectedAtasanPegawai->id)->first() : null;
                $updateData['atasan_langsung_id'] = ($isAtasanLgs && !$isGenericPimpinan)
                    ? $approverUserId
                    : ($expectedAtasanUser?->id ?? $approverUserId);

                if ($statusAtasan === 'Disetujui') {
                    $updateData['status'] = 'Disetujui Atasan (Menunggu PYBMC)';
                } else {
                    $updateData['status']           = 'Ditolak';
                    $updateData['approved_by']      = $approverUserId;
                    $updateData['approved_at']      = now();
                    $updateData['catatan_pimpinan'] = $data['catatan_atasan_langsung'] ?? $data['catatan_pimpinan'] ?? null;
                    $isKeputusanFinal = true;
                }
            } else {
                // Keputusan Final PYBMC (Tahap 2)
                $updateData['status']           = $data['status'];
                $updateData['approved_by']      = $approverUserId;
                $updateData['approved_at']      = now();
                $updateData['catatan_pimpinan'] = $data['catatan_pimpinan'] ?? null;
                if (!empty($data['nomor_surat'])) {
                    $updateData['nomor_surat'] = $data['nomor_surat'];
                }
                $isKeputusanFinal = true;
            }

            $updatedCuti = $this->repository->update($id, $updateData);

            // ── Notifikasi ke Pegawai pemohon ────────────────────────────────
            try {
                $employeeUser = \App\Models\User::where('pegawai_id', $cuti->pegawai_id)->first();
                if ($employeeUser && $approver) {
                    $employeeUser->notify(new \App\Notifications\CutiStatusNotification($cuti, $approver, $updateData['status']));
                }
            } catch (\Throwable $e) {
                // Ignore notification failure
            }

            // ── Notifikasi otomatis ke PYBMC setelah Pertimbangan Atasan disetujui ──
            // Agar PYBMC tahu ada cuti yang menunggu keputusan mereka
            if (isset($updateData['status']) && $updateData['status'] === 'Disetujui Atasan (Menunggu PYBMC)') {
                try {
                    $pybmcUser = $cuti->pybmc_id ? \App\Models\User::find($cuti->pybmc_id) : null;
                    // Jika pybmc_id belum diset, lookup dari hierarchy service
                    if (!$pybmcUser && $cuti->pegawai) {
                        $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
                        $pybmcPegawai     = $hierarchyService->getPybmcCuti($cuti->pegawai);
                        $pybmcUser        = $pybmcPegawai ? \App\Models\User::where('pegawai_id', $pybmcPegawai->id)->first() : null;
                    }
                    if ($pybmcUser) {
                        $pybmcUser->notify(new \App\Notifications\CutiSubmittedNotification($updatedCuti, $cuti->pegawai));
                    }
                } catch (\Throwable $e) {
                    // Ignore notification failure
                }
            }

            // ── Notifikasi Monitoring/Pengarsipan ke Dekan + Admin/Operator ──
            // Dikirim setelah keputusan final (Disetujui atau Ditolak)
            if ($isKeputusanFinal && in_array($updateData['status'], ['Disetujui', 'Ditolak'])) {
                try {
                    $monitorTargets = collect();

                    // Dekan sebagai laporan/monitoring
                    $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
                    $dekanPegawai     = $hierarchyService->findPegawaiByJabatan(['Dekan']);
                    if ($dekanPegawai) {
                        $dekanUser = \App\Models\User::where('pegawai_id', $dekanPegawai->id)->first();
                        if ($dekanUser && (int)$dekanUser->id !== $approverUserId) {
                            $monitorTargets->push($dekanUser);
                        }
                    }

                    // Admin/Operator sebagai pengarsipan
                    $adminUsers = \App\Models\User::whereHas('role', fn($q) => $q->whereIn('name', ['admin']))->get();
                    foreach ($adminUsers as $adminUser) {
                        if ((int)$adminUser->id !== $approverUserId && !$monitorTargets->contains('id', $adminUser->id)) {
                            $monitorTargets->push($adminUser);
                        }
                    }

                    if ($monitorTargets->isNotEmpty() && $cuti->pegawai) {
                        \Illuminate\Support\Facades\Notification::send(
                            $monitorTargets,
                            new \App\Notifications\CutiSubmittedNotification($updatedCuti, $cuti->pegawai)
                        );
                    }
                } catch (\Throwable $e) {
                    // Ignore notification failure
                }
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
