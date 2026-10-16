<?php

namespace App\Services\Approval\Resolvers;

use App\Models\Pegawai;

class FakultasApprovalResolver extends AbstractApprovalResolver
{
    /**
     * Memeriksa apakah pegawai memegang jabatan di tingkat pimpinan fakultas (Dekan / Wakil Dekan)
     */
    public function supports(Pegawai $pegawai): bool
    {
        $jabatan = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));
        return str_contains($jabatan, 'DEKAN') || str_contains($jabatan, 'WADEK');
    }

    /**
     * Selesaikan Rule 19 & Rule 20 persetujuan kinerja logbook
     */
    public function resolveApprovalRule(Pegawai $pegawai): ?array
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // Rule 20: Dekan Fakultas Keperawatan ──► Disahkan oleh Rektor Universitas Riau
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
            return [
                'rule_number'                => 20,
                'rule_label'                 => 'Rule 20: Dekan Fakultas Keperawatan ──► Disahkan oleh Rektor Universitas Riau',
                'subject_role'               => 'Dekan Fakultas Keperawatan',
                'approver_title'             => 'Rektor Universitas Riau',
                'approver_pegawai'           => null,
                'approver_name'              => 'Rektor Universitas Riau',
                'approver_nip'               => '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // Rule 19: Para Wadek (WD I, WD II, WD III) ──► Disetujui Dekan Fakultas Keperawatan
        if (str_contains($jabatanNama, 'WAKIL DEKAN') || str_contains($jabatanNama, 'WADEK')) {
            $dekan = $this->findPegawaiByJabatan(['Dekan']);
            return [
                'rule_number'                => 19,
                'rule_label'                 => 'Rule 19: Para Wadek (WD I, WD II, WD III) ──► Disetujui Dekan Fakultas Keperawatan',
                'subject_role'               => $pegawai->jabatan?->nama_jabatan ?? 'Wakil Dekan',
                'approver_title'             => 'Dekan Fakultas Keperawatan',
                'approver_pegawai'           => $dekan,
                'approver_name'              => $dekan ? ($dekan->nama_lengkap ?? $dekan->nama) : 'Dekan Fakultas Keperawatan',
                'approver_nip'               => $dekan?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        return null;
    }

    /**
     * Dapatkan daftar ID bawahan pimpinan fakultas
     */
    public function getBawahanIds(Pegawai $pimpinan): ?array
    {
        $jabatanNama = strtoupper(trim((string)($pimpinan->jabatan->nama_jabatan ?? '')));

        // 1. Dekan Fakultas Keperawatan
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
            return Pegawai::where(function ($q) {
                $q->whereHas('jabatan', function ($jq) {
                    $jq->where('nama_jabatan', 'like', '%Wakil Dekan%')
                       ->orWhere('nama_jabatan', 'like', '%Wadek%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala Bagian Umum%')
                       ->orWhere('nama_jabatan', 'like', '%Kabag Umum%');
                });
            })->pluck('id')->toArray();
        }

        // 2. Wakil Dekan I (Bid. Akademik)
        if (str_contains($jabatanNama, 'WAKIL DEKAN I') || str_contains($jabatanNama, 'WD I') 
            || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'AKADEMIK'))) {
            return Pegawai::where(function ($q) {
                $q->whereHas('jabatan', function ($jq) {
                    $jq->where(function ($kj) {
                        $kj->where('nama_jabatan', 'like', '%Ketua Jurusan%')
                           ->orWhere('nama_jabatan', 'like', '%Kajur%');
                    })->where('nama_jabatan', 'not like', '%Preklinik%')
                      ->where('nama_jabatan', 'not like', '%Klinik%');
                })->orWhereHas('jabatan', function ($lj) {
                    $lj->where('nama_jabatan', 'like', '%Kepala UPT%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala Lab%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala Unit%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala SPMF%');
                });
            })->pluck('id')->toArray();
        }

        // 3. Wakil Dekan II (Bid. Keuangan dan Umum)
        if (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II') 
            || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'KEUANGAN'))) {
            return Pegawai::where(function ($q) {
                $q->whereHas('jabatan', function ($jq) {
                    $jq->where('nama_jabatan', 'like', '%Kepala Bagian Umum%')
                       ->orWhere('nama_jabatan', 'like', '%Kabag Umum%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala UPT%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala Lab%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala Unit%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala SPMF%');
                });
            })->pluck('id')->toArray();
        }

        // 4. Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)
        if (str_contains($jabatanNama, 'WAKIL DEKAN III') || str_contains($jabatanNama, 'WD III') 
            || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'KEMAHASISWAAN'))) {
            return Pegawai::whereHas('unitKerja', function ($uq) {
                $uq->where('nama_unit', 'like', '%Kemahasiswaan%');
            })->pluck('id')->toArray();
        }

        return null;
    }

    /**
     * Atasan Langsung Cuti untuk Pimpinan Fakultas
     */
    public function resolveAtasanLangsungCuti(Pegawai $pegawai): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // Dekan -> Fallback ke diri sendiri (Rektor eksternal)
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
            return $pegawai;
        }

        // Wakil Dekan II -> Kabag Umum memberikan pertimbangan tahap 1
        if (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')) {
            return $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum']);
        }

        // Wakil Dekan I, Wakil Dekan III -> Wakil Dekan II
        if (str_contains($jabatanNama, 'WAKIL DEKAN') || str_contains($jabatanNama, 'WADEK')) {
            return $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Wakil Dekan II']);
        }

        return null;
    }

    /**
     * PYBMC Cuti untuk Pimpinan Fakultas
     */
    public function resolvePybmcCuti(Pegawai $pegawai, ?string $jenisCuti = null): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
            return $pegawai;
        }

        if (str_contains($jabatanNama, 'WAKIL DEKAN') || str_contains($jabatanNama, 'WADEK')) {
            return $this->findPegawaiByJabatan(['Dekan']);
        }

        return null;
    }
}
