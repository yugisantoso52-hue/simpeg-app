<?php

namespace App\Services\Approval\Resolvers;

use App\Models\Pegawai;

class AdministrasiApprovalResolver extends AbstractApprovalResolver
{
    /**
     * Memeriksa apakah pegawai adalah Tenaga Kependidikan, Bagian Umum, Pokja, PLP/Laboran, atau Kepala Lab/Unit
     */
    public function supports(Pegawai $pegawai): bool
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        return !$pegawai->isDosen()
            || str_contains($jabatanNama, 'BAGIAN UMUM')
            || str_contains($jabatanNama, 'KABAG')
            || str_contains($jabatanNama, 'POKJA')
            || str_contains($jabatanNama, 'PLP')
            || str_contains($jabatanNama, 'LABORAN')
            || str_contains($jabatanNama, 'LABORATORIUM')
            || str_contains($jabatanNama, 'KEPALA UPT')
            || str_contains($jabatanNama, 'KEPALA UNIT')
            || str_contains($jabatanNama, 'KEPALA SPMF');
    }

    /**
     * Selesaikan aturan persetujuan kinerja logbook tingkat Administrasi, Pokja, PLP, dan Tendik (Rule 10, 11, 12, 13, 14, 15, 16, 17, 18, dan Fallback Tendik)
     */
    public function resolveApprovalRule(Pegawai $pegawai): ?array
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));
        $unitNama    = strtoupper(trim((string)($pegawai->unitKerja->nama_unit ?? '')));
        $isDosen     = $pegawai->isDosen();
        $isPlp       = $pegawai->isPlp();

        // -------------------------------------------------------------------------------------------------
        // RULE 18: Kepala Lab & Kepala Unit ──► Disetujui Wakil Dekan I / Wadek II
        // -------------------------------------------------------------------------------------------------
        if (str_contains($jabatanNama, 'KEPALA UPT') || str_contains($jabatanNama, 'KEPALA LABORATORIUM') 
            || str_contains($jabatanNama, 'KEPALA LAB') || str_contains($jabatanNama, 'KEPALA UNIT') 
            || str_contains($jabatanNama, 'KEPALA SPMF') || str_contains($jabatanNama, 'KEPALA RUANG LABORATORIUM')) {
            
            $wadek1 = $this->findPegawaiByJabatan([
                'Wakil Dekan I (Bid. Akademik)',
                'Wakil Dekan I',
                'Wakil Dekan Akedemik',
                'Wakil Dekan Bidang Akademik',
            ]);
            $wadek2 = $this->findPegawaiByJabatan([
                'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'Wakil Dekan Keuangan dan Kepegawaian',
                'Wakil Dekan II',
            ]);
            $dekan = $this->findPegawaiByJabatan(['Dekan']);
            $approver = $wadek1 ?: ($wadek2 ?: $dekan);

            return [
                'rule_number'                => 18,
                'rule_label'                 => 'Rule 18: Kepala Lab & Kepala Unit ──► Disetujui Wakil Dekan I / Wadek II',
                'subject_role'               => $pegawai->jabatan?->nama_jabatan ?? 'Kepala Lab / Unit',
                'approver_title'             => 'Wakil Dekan I (Bid. Akademik) / Wadek II (Bid. Keuangan dan Umum)',
                'approver_pegawai'           => $approver,
                'approver_name'              => $approver ? ($approver->nama_lengkap ?? $approver->nama) : 'Wakil Dekan I (Bid. Akademik)',
                'approver_nip'               => $approver?->nip ?? '-',
                'secondary_approver_title'   => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'secondary_approver_pegawai' => $wadek2,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 16: Kabag Umum ──► Disetujui Dekan / Wadek II (Bid. Keuangan dan Umum)
        // -------------------------------------------------------------------------------------------------
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM') || str_contains($jabatanNama, 'KABAG TU')) {
            $wadek2 = $this->findPegawaiByJabatan([
                'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'Wakil Dekan Keuangan dan Kepegawaian',
                'Wakil Dekan II',
            ]);
            $dekan = $this->findPegawaiByJabatan(['Dekan']);
            $approver = $wadek2 ?: $dekan;

            return [
                'rule_number'                => 16,
                'rule_label'                 => 'Rule 16: Kabag Umum ──► Disetujui Dekan / Wadek II (Bid. Keuangan dan Umum)',
                'subject_role'               => 'Kepala Bagian Umum',
                'approver_title'             => 'Dekan Fakultas Keperawatan / Wadek II (Bid. Keuangan dan Umum)',
                'approver_pegawai'           => $approver,
                'approver_name'              => $approver ? ($approver->nama_lengkap ?? $approver->nama) : 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'approver_nip'               => $approver?->nip ?? '-',
                'secondary_approver_title'   => 'Dekan Fakultas Keperawatan',
                'secondary_approver_pegawai' => $dekan,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 15: Ka Pokja Umum Sarana Akademik ──► Disetujui Kabag Umum
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA'))
            && (str_contains($jabatanNama, 'UMUM') || str_contains($jabatanNama, 'SARANA'))) {
            $kabag = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
            return [
                'rule_number'                => 15,
                'rule_label'                 => 'Rule 15: Ka Pokja Umum Sarana Akademik ──► Disetujui Kabag Umum',
                'subject_role'               => 'Ka Pokja Umum Sarana Akademik',
                'approver_title'             => 'Kepala Bagian Umum',
                'approver_pegawai'           => $kabag,
                'approver_name'              => $kabag ? ($kabag->nama_lengkap ?? $kabag->nama) : 'Kepala Bagian Umum',
                'approver_nip'               => $kabag?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 14: Ka Pokja Keu-Kepeg ──► Disetujui Kabag Umum
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA'))
            && (str_contains($jabatanNama, 'KEU') || str_contains($jabatanNama, 'KEPEG'))) {
            $kabag = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
            return [
                'rule_number'                => 14,
                'rule_label'                 => 'Rule 14: Ka Pokja Keu-Kepeg ──► Disetujui Kabag Umum',
                'subject_role'               => 'Ka Pokja Keu-Kepeg',
                'approver_title'         => 'Kepala Bagian Umum',
                'approver_pegawai'           => $kabag,
                'approver_name'              => $kabag ? ($kabag->nama_lengkap ?? $kabag->nama) : 'Kepala Bagian Umum',
                'approver_nip'               => $kabag?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 13: Ka Pokja Akademik ──► Disetujui Kabag Umum
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA'))
            && str_contains($jabatanNama, 'AKADEMIK') && !str_contains($jabatanNama, 'SARANA') && !str_contains($jabatanNama, 'UMUM')) {
            $kabag = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
            return [
                'rule_number'                => 13,
                'rule_label'                 => 'Rule 13: Ka Pokja Akademik ──► Disetujui Kabag Umum',
                'subject_role'               => 'Ka Pokja Akademik',
                'approver_title'             => 'Kepala Bagian Umum',
                'approver_pegawai'           => $kabag,
                'approver_name'              => $kabag ? ($kabag->nama_lengkap ?? $kabag->nama) : 'Kepala Bagian Umum',
                'approver_nip'               => $kabag?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 17: PLP / Laboran Lab ──► Disetujui Kabag Umum / Ka UPT Lab
        // -------------------------------------------------------------------------------------------------
        if ($isPlp || str_contains($jabatanNama, 'PLP') || str_contains($jabatanNama, 'LABORAN') 
            || str_contains($jabatanNama, 'PRANATA LABORATORIUM') || (str_contains($unitNama, 'LABORATORIUM') && !str_contains($jabatanNama, 'KEPALA'))) {
            $kaLab = $this->findPegawaiByJabatan([
                'Kepala UPT Laboratorium Keperawatan',
                'Kepala Laboratorium',
                'Kepala Lab',
            ]);
            $kabag = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU']);
            $approver = $kaLab ?: ($kabag ?: $this->findPegawaiByJabatan(['Wakil Dekan II', 'Dekan']));

            return [
                'rule_number'                => 17,
                'rule_label'                 => 'Rule 17: PLP / Laboran Lab ──► Disetujui Kabag Umum / Ka UPT Lab',
                'subject_role'               => 'PLP / Laboran Lab',
                'approver_title'             => 'Kabag Umum / Kepala UPT Laboratorium Keperawatan',
                'approver_pegawai'           => $approver,
                'approver_name'              => $approver ? ($approver->nama_lengkap ?? $approver->nama) : 'Kepala UPT Laboratorium Keperawatan',
                'approver_nip'               => $approver?->nip ?? '-',
                'secondary_approver_title'   => 'Kepala Bagian Umum',
                'secondary_approver_pegawai' => $kabag,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 12: Staff Pokja Umum Sarana Akademik ──► Disetujui Ka Pokja Umum Sarana Akademik
        // -------------------------------------------------------------------------------------------------
        if (str_contains($jabatanNama, 'STAFF POKJA UMUM') || str_contains($jabatanNama, 'STAFF POKJA SARANA') 
            || ((str_contains($unitNama, 'SARANA') || (str_contains($unitNama, 'UMUM') && str_contains($unitNama, 'POKJA'))) && !$isDosen && !str_contains($jabatanNama, 'KA POKJA'))) {
            $kaPokja = $this->findPegawaiByJabatan([
                'Ka Pokja Umum Sarana Akademik',
                'Ketua Pokja Umum dan Sarana Akademik',
                'Kepala Pokja Umum',
                'Kepala Bagian Umum',
                'Kabag Umum'
            ]);
            return [
                'rule_number'                => 12,
                'rule_label'                 => 'Rule 12: Staff Pokja Umum Sarana Akademik ──► Disetujui Ka Pokja Umum Sarana Akademik',
                'subject_role'               => 'Staff Pokja Umum Sarana Akademik',
                'approver_title'             => 'Ka Pokja Umum Sarana Akademik',
                'approver_pegawai'           => $kaPokja,
                'approver_name'              => $kaPokja ? ($kaPokja->nama_lengkap ?? $kaPokja->nama) : 'Ka Pokja Umum Sarana Akademik',
                'approver_nip'               => $kaPokja?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 11: Staff Pokja Keu & Kepeg ──► Disetujui Ka Pokja Keu-Kepeg
        // -------------------------------------------------------------------------------------------------
        if (str_contains($jabatanNama, 'STAFF POKJA KEU') || str_contains($jabatanNama, 'STAFF POKJA KEPEG') 
            || (str_contains($unitNama, 'KEUANGAN') && !$isDosen && !str_contains($jabatanNama, 'KA POKJA'))) {
            $kaPokja = $this->findPegawaiByJabatan([
                'Ka Pokja Keu-Kepeg',
                'Ketua Pokja Keuangan dan Kepegawaian',
                'Kepala Pokja Keu',
                'Kepala Bagian Umum',
                'Kabag Umum'
            ]);
            return [
                'rule_number'                => 11,
                'rule_label'                 => 'Rule 11: Staff Pokja Keu & Kepeg ──► Disetujui Ka Pokja Keu-Kepeg',
                'subject_role'               => 'Staff Pokja Keu & Kepeg',
                'approver_title'             => 'Ka Pokja Keu-Kepeg',
                'approver_pegawai'           => $kaPokja,
                'approver_name'              => $kaPokja ? ($kaPokja->nama_lengkap ?? $kaPokja->nama) : 'Ka Pokja Keu-Kepeg',
                'approver_nip'               => $kaPokja?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 10: Staff Pokja Akademik dan Kemahasiswaan ──► Disetujui Ka Pokja Akademik
        // -------------------------------------------------------------------------------------------------
        if (str_contains($jabatanNama, 'STAFF POKJA AKADEMIK') 
            || (str_contains($unitNama, 'AKADEMIK') && !str_contains($unitNama, 'SARANA') && !$isDosen && !str_contains($jabatanNama, 'KA POKJA'))) {
            $kaPokja = $this->findPegawaiByJabatan([
                'Ka Pokja Akademik',
                'Ketua Pokja Akademik dan Kemahasiswaan',
                'Kepala Pokja Akademik',
                'Kepala Bagian Umum',
                'Kabag Umum'
            ]);
            return [
                'rule_number'                => 10,
                'rule_label'                 => 'Rule 10: Staff Pokja Akademik dan Kemahasiswaan ──► Disetujui Ka Pokja Akademik',
                'subject_role'               => 'Staff Pokja Akademik dan Kemahasiswaan',
                'approver_title'             => 'Ka Pokja Akademik',
                'approver_pegawai'           => $kaPokja,
                'approver_name'              => $kaPokja ? ($kaPokja->nama_lengkap ?? $kaPokja->nama) : 'Ka Pokja Akademik',
                'approver_nip'               => $kaPokja?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // FALLBACK STAFF / TENDIK / UMUM ──► Disetujui Kabag Umum
        // -------------------------------------------------------------------------------------------------
        if (!$isDosen) {
            $kabag = $this->findPegawaiByJabatan([
                'Kepala Bagian Umum',
                'Kabag Umum',
                'Kabag TU',
                'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'Dekan'
            ]);
            return [
                'rule_number'                => 16,
                'rule_label'                 => 'Rule 16 (Fallback): Tendik / Staff Umum ──► Disetujui Kabag Umum',
                'subject_role'               => $pegawai->jabatan?->nama_jabatan ?? 'Tenaga Kependidikan',
                'approver_title'             => 'Kepala Bagian Umum',
                'approver_pegawai'           => $kabag,
                'approver_name'              => $kabag ? ($kabag->nama_lengkap ?? $kabag->nama) : 'Kepala Bagian Umum',
                'approver_nip'               => $kabag?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        return null;
    }

    /**
     * Dapatkan daftar ID bawahan untuk Kabag Umum, Ka Pokja, dan Ka UPT Lab
     */
    public function getBawahanIds(Pegawai $pimpinan): ?array
    {
        $jabatanNama = strtoupper(trim((string)($pimpinan->jabatan->nama_jabatan ?? '')));

        // 1. Kepala Bagian Umum (Kabag Umum)
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM') || str_contains($jabatanNama, 'KABAG TU')) {
            return Pegawai::where(function ($q) {
                $q->whereHas('jabatan', function ($jq) {
                    $jq->where('nama_jabatan', 'like', '%Ka Pokja%')
                       ->orWhere('nama_jabatan', 'like', '%Kepala Pokja%')
                       ->orWhere('nama_jabatan', 'like', '%Ketua Pokja%')
                       ->orWhere('nama_jabatan', 'like', '%PLP%')
                       ->orWhere('nama_jabatan', 'like', '%Laboran%');
                })
                ->orWhere(function ($sq) {
                    $sq->where('jenis_pegawai', '!=', 'Dosen')
                       ->whereNull('nidn_nuptk')
                       ->whereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Bagian Umum%')->orWhere('nama_unit', 'like', '%Dekanat%'));
                });
            })->pluck('id')->toArray();
        }

        // 2. Ka Pokja Akademik
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA')) 
            && str_contains($jabatanNama, 'AKADEMIK') && !str_contains($jabatanNama, 'SARANA') && !str_contains($jabatanNama, 'UMUM')) {
            return Pegawai::where('id', '!=', $pimpinan->id)
                ->where(function ($sq) {
                    $sq->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Akademik%'))
                       ->orWhere(function ($uq) {
                           $uq->whereHas('unitKerja', fn($u) => $u->where('nama_unit', 'like', '%Akademik%'))
                              ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Ka Pokja%')->orWhere('nama_jabatan', 'like', '%Dosen%'));
                       });
                })->pluck('id')->toArray();
        }

        // 3. Ka Pokja Keu-Kepeg
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA')) 
            && (str_contains($jabatanNama, 'KEU') || str_contains($jabatanNama, 'KEPEG'))) {
            return Pegawai::where('id', '!=', $pimpinan->id)
                ->where(function ($sq) {
                    $sq->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Keu%'))
                       ->orWhere(function ($uq) {
                           $uq->whereHas('unitKerja', fn($u) => $u->where('nama_unit', 'like', '%Keuangan%'))
                              ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Ka Pokja%')->orWhere('nama_jabatan', 'like', '%Dosen%'));
                       });
                })->pluck('id')->toArray();
        }

        // 4. Ka Pokja Umum Sarana Akademik
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA')) 
            && (str_contains($jabatanNama, 'UMUM') || str_contains($jabatanNama, 'SARANA'))) {
            return Pegawai::where('id', '!=', $pimpinan->id)
                ->where(function ($sq) {
                    $sq->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Umum%'))
                       ->orWhere(function ($uq) {
                           $uq->whereHas('unitKerja', fn($u) => $u->where('nama_unit', 'like', '%Sarana%')->orWhere('nama_unit', 'like', '%Umum%'))
                              ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Ka Pokja%')->orWhere('nama_jabatan', 'like', '%Kabag%')->orWhere('nama_jabatan', 'like', '%Dosen%'));
                       });
                })->pluck('id')->toArray();
        }

        // 5. Kepala UPT Laboratorium Keperawatan
        if (str_contains($jabatanNama, 'KEPALA UPT LAB') || str_contains($jabatanNama, 'KEPALA LABORATORIUM') || str_contains($jabatanNama, 'KEPALA LAB')) {
            return Pegawai::where('id', '!=', $pimpinan->id)
                ->where(function ($sq) {
                    $sq->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%PLP%')
                           ->orWhere('nama_jabatan', 'like', '%Laboran%')
                           ->orWhere('nama_jabatan', 'like', '%Pranata Laboratorium%');
                    })->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Laboratorium%'));
                })->pluck('id')->toArray();
        }

        return null;
    }

    /**
     * Atasan Langsung Cuti untuk Tendik, Staff, Laboran, Ka Pokja, dan Kabag Umum
     */
    public function resolveAtasanLangsungCuti(Pegawai $pegawai): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // Kabag Umum -> Wadek II memberikan pertimbangan
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM')) {
            return $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Wakil Dekan II']);
        }

        // Tendik / Staff / Laboran / Ka Pokja -> Kabag Umum memberikan pertimbangan
        return $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum']);
    }

    /**
     * PYBMC Cuti untuk Administrasi & Tendik
     */
    public function resolvePybmcCuti(Pegawai $pegawai, ?string $jenisCuti = null): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // Kabag Umum (Struktural) -> PYBMC Dekan
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM')) {
            return $this->findPegawaiByJabatan(['Dekan']);
        }

        // Tendik / Staff / Ka Pokja / PLP -> PYBMC Wadek II
        return $this->findPegawaiByJabatan([
            'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'Wakil Dekan Keuangan dan Kepegawaian',
            'Wakil Dekan II',
            'Wakil Dekan Bidang Keuangan dan Umum',
        ]);
    }
}
