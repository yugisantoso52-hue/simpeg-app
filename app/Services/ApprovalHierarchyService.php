<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ApprovalHierarchyService
{
    /**
     * Daftar 15+ Jabatan Pimpinan FKP UNRI
     */
    public const JABATAN_PIMPINAN = [
        'Dekan',
        'Wakil Dekan I (Bid. Akademik)',
        'Wakil Dekan II (Bid. Keuangan dan Umum)',
        'Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)',
        'Ketua Jurusan (Kajur)',
        'Ketua Jurusan Klinik dan Komunitas',
        'Ketua Jurusan Preklinik Keperawatan',
        'Koordinator Prodi S1 Keperawatan',
        'Koordinator Prodi S2 Keperawatan',
        'Koordinator Prodi Ners',
        'Kepala Bagian Umum',
        'Ka Pokja Akademik',
        'Ka Pokja Keu-Kepeg',
        'Ka Pokja Umum Sarana Akademik',
        'Kepala UPT Laboratorium Keperawatan',
        'Kepala Unit Fungsional',
    ];

    /**
     * Dapatkan Evaluasi Matriks 20 Aturan Persetujuan Logbook (Presisi Sesuai SK / Ketetapan FKP UNRI)
     *
     * Matriks Alur Persetujuan Resmi:
     * 1. Dosen S2 Keperawatan                  -> Approved By: Koordinator Prodi S2 Keperawatan
     * 2. Dosen S1 Keperawatan                  -> Approved By: Koordinator Prodi S1 Keperawatan
     * 3. Dosen Profesi Ners                    -> Approved By: Koordinator Prodi Ners
     * 4. Koordinator Prodi S1, S2, dan Ners    -> Approved By: Ketua Jurusan
     * 5. Ketua Jurusan (Kajur)                 -> Approved By: Wakil Dekan I (Bid. Akademik)
     * 6. Ketua Jurusan Klinik dan Komunitas    -> Approved By: Ketua Jurusan (Kajur)
     * 7. Ketua Jurusan Preklinik Keperawatan   -> Approved By: Ketua Jurusan (Kajur)
     * 8. Dosen Jurusan Klinik dan Komunitas   -> Approved By: Ketua Jurusan Klinik dan Komunitas
     * 9. Dosen Jurusan Preklinik Keperawatan  -> Approved By: Ketua Jurusan Preklinik Keperawatan
     * 10. Staff Pokja Akademik dan Kemahasiswaan -> Approved By: Ka Pokja Akademik
     * 11. Staff Pokja Keu & Kepeg              -> Approved By: Ka Pokja Keu-Kepeg
     * 12. Staff Pokja Umum Sarana Akademik     -> Approved By: Ka Pokja Umum Sarana Akademik
     * 13. Ka Pokja Akademik                    -> Approved By: Kabag Umum
     * 14. Ka Pokja Keu-Kepeg                   -> Approved By: Kabag Umum
     * 15. Ka Pokja Umum Sarana Akademik        -> Approved By: Kabag Umum
     * 16. Kabag Umum                           -> Approved By: Dekan Fakultas Keperawatan / Wadek II (Bid. Keuangan dan Umum)
     * 17. PLP / Laboran Lab                    -> Approved By: Kabag Umum / Kepala UPT Laboratorium Keperawatan
     * 18. Kepala Lab & Kepala Unit             -> Approved By: Wakil Dekan I (Bid. Akademik) / Wadek II (Bid. Keuangan dan Umum)
     * 19. Para Wadek (WD I, WD II, WD III)     -> Approved By: Dekan Fakultas Keperawatan
     * 20. Dekan Fakultas Keperawatan           -> Approved By: Rektor Universitas Riau
     */
    public function getApprovalRuleInfo(Pegawai $pegawai): array
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));
        $unitNama    = strtoupper(trim((string)($pegawai->unitKerja->nama_unit ?? '')));
        $isDosen     = $pegawai->isDosen();
        $isPlp       = $pegawai->isPlp();

        // -------------------------------------------------------------------------------------------------
        // RULE 20: Dekan Fakultas Keperawatan ──► Approved By ──► Rektor Universitas Riau
        // -------------------------------------------------------------------------------------------------
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
            return [
                'rule_number'            => 20,
                'rule_label'             => 'Rule 20: Dekan Fakultas Keperawatan ──► Disahkan oleh Rektor Universitas Riau',
                'subject_role'           => 'Dekan Fakultas Keperawatan',
                'approver_title'         => 'Rektor Universitas Riau',
                'approver_pegawai'       => null,
                'approver_name'          => 'Rektor Universitas Riau',
                'approver_nip'           => '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 19: Para Wadek (WD I, WD II, WD III) ──► Approved By ──► Dekan Fakultas Keperawatan
        // -------------------------------------------------------------------------------------------------
        if (str_contains($jabatanNama, 'WAKIL DEKAN') || str_contains($jabatanNama, 'WADEK')) {
            $dekan = $this->findPegawaiByJabatan(['Dekan']);
            return [
                'rule_number'            => 19,
                'rule_label'             => 'Rule 19: Para Wadek (WD I, WD II, WD III) ──► Disetujui Dekan Fakultas Keperawatan',
                'subject_role'           => $pegawai->jabatan?->nama_jabatan ?? 'Wakil Dekan',
                'approver_title'         => 'Dekan Fakultas Keperawatan',
                'approver_pegawai'       => $dekan,
                'approver_name'          => $dekan ? ($dekan->nama_lengkap ?? $dekan->nama) : 'Dekan Fakultas Keperawatan',
                'approver_nip'           => $dekan?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 18: Kepala Lab & Kepala Unit ──► Approved By ──► Wakil Dekan I (Bid. Akademik) / Wadek II (Bid. Keuangan dan Umum)
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
                'rule_number'            => 18,
                'rule_label'             => 'Rule 18: Kepala Lab & Kepala Unit ──► Disetujui Wakil Dekan I / Wadek II',
                'subject_role'           => $pegawai->jabatan?->nama_jabatan ?? 'Kepala Lab / Unit',
                'approver_title'         => 'Wakil Dekan I (Bid. Akademik) / Wadek II (Bid. Keuangan dan Umum)',
                'approver_pegawai'       => $approver,
                'approver_name'          => $approver ? ($approver->nama_lengkap ?? $approver->nama) : 'Wakil Dekan I (Bid. Akademik)',
                'approver_nip'           => $approver?->nip ?? '-',
                'secondary_approver_title' => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'secondary_approver_pegawai' => $wadek2,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 16: Kabag Umum ──► Approved By ──► Dekan Fakultas Keperawatan / Wadek II (Bid. Keuangan dan Umum)
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
                'rule_number'            => 16,
                'rule_label'             => 'Rule 16: Kabag Umum ──► Disetujui Dekan / Wadek II (Bid. Keuangan dan Umum)',
                'subject_role'           => 'Kepala Bagian Umum',
                'approver_title'         => 'Dekan Fakultas Keperawatan / Wadek II (Bid. Keuangan dan Umum)',
                'approver_pegawai'       => $approver,
                'approver_name'          => $approver ? ($approver->nama_lengkap ?? $approver->nama) : 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'approver_nip'           => $approver?->nip ?? '-',
                'secondary_approver_title' => 'Dekan Fakultas Keperawatan',
                'secondary_approver_pegawai' => $dekan,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 7: Ketua Jurusan Preklinik Keperawatan ──► Approved By ──► Ketua Jurusan (Kajur)
        // (Harus dicek SEBELUM Klinik karena Preklinik mengandung kata Klinik)
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) && str_contains($jabatanNama, 'PREKLINIK')) {
            $kajur = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan Keperawatan',
                'Ketua Jurusan',
                'Wakil Dekan I (Bid. Akademik)',
                'Dekan'
            ]);
            return [
                'rule_number'            => 7,
                'rule_label'             => 'Rule 7: Ketua Jurusan Preklinik Keperawatan ──► Disetujui Ketua Jurusan (Kajur)',
                'subject_role'           => 'Ketua Jurusan Preklinik Keperawatan',
                'approver_title'         => 'Ketua Jurusan (Kajur)',
                'approver_pegawai'       => $kajur,
                'approver_name'          => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan (Kajur)',
                'approver_nip'           => $kajur?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 6: Ketua Jurusan Klinik dan Komunitas ──► Approved By ──► Ketua Jurusan (Kajur)
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) && str_contains($jabatanNama, 'KLINIK') && !str_contains($jabatanNama, 'PREKLINIK')) {
            $kajur = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan Keperawatan',
                'Ketua Jurusan',
                'Wakil Dekan I (Bid. Akademik)',
                'Dekan'
            ]);
            return [
                'rule_number'            => 6,
                'rule_label'             => 'Rule 6: Ketua Jurusan Klinik dan Komunitas ──► Disetujui Ketua Jurusan (Kajur)',
                'subject_role'           => 'Ketua Jurusan Klinik dan Komunitas',
                'approver_title'         => 'Ketua Jurusan (Kajur)',
                'approver_pegawai'       => $kajur,
                'approver_name'          => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan (Kajur)',
                'approver_nip'           => $kajur?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 5: Ketua Jurusan (Kajur) ──► Approved By ──► Wakil Dekan I (Bid. Akademik)
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) 
            && !str_contains($jabatanNama, 'PREKLINIK') && !str_contains($jabatanNama, 'KLINIK')) {
            $wadek1 = $this->findPegawaiByJabatan([
                'Wakil Dekan I (Bid. Akademik)',
                'Wakil Dekan I',
                'Wakil Dekan Akedemik',
                'Wakil Dekan Bidang Akademik',
                'Dekan'
            ]);
            return [
                'rule_number'            => 5,
                'rule_label'             => 'Rule 5: Ketua Jurusan (Kajur) ──► Disetujui Wakil Dekan I (Bid. Akademik)',
                'subject_role'           => 'Ketua Jurusan (Kajur)',
                'approver_title'         => 'Wakil Dekan I (Bid. Akademik)',
                'approver_pegawai'       => $wadek1,
                'approver_name'          => $wadek1 ? ($wadek1->nama_lengkap ?? $wadek1->nama) : 'Wakil Dekan I (Bid. Akademik)',
                'approver_nip'           => $wadek1?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 4: Koordinator Prodi S1, S2, dan Ners ──► Approved By ──► Ketua Jurusan
        // -------------------------------------------------------------------------------------------------
        if (str_contains($jabatanNama, 'KOORDINATOR PRODI') || str_contains($jabatanNama, 'KOORPRODI') || str_contains($jabatanNama, 'KOORDINATOR PROGRAM STUDI')) {
            $kajur = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan Keperawatan',
                'Ketua Jurusan',
                'Wakil Dekan I (Bid. Akademik)',
                'Dekan'
            ]);
            return [
                'rule_number'            => 4,
                'rule_label'             => 'Rule 4: Koordinator Prodi S1, S2, dan Ners ──► Disetujui Ketua Jurusan',
                'subject_role'           => $pegawai->jabatan?->nama_jabatan ?? 'Koordinator Prodi',
                'approver_title'         => 'Ketua Jurusan (Kajur)',
                'approver_pegawai'       => $kajur,
                'approver_name'          => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan (Kajur)',
                'approver_nip'           => $kajur?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 15: Ka Pokja Umum Sarana Akademik ──► Approved By ──► Kabag Umum
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA'))
            && (str_contains($jabatanNama, 'UMUM') || str_contains($jabatanNama, 'SARANA'))) {
            $kabag = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
            return [
                'rule_number'            => 15,
                'rule_label'             => 'Rule 15: Ka Pokja Umum Sarana Akademik ──► Disetujui Kabag Umum',
                'subject_role'           => 'Ka Pokja Umum Sarana Akademik',
                'approver_title'         => 'Kepala Bagian Umum',
                'approver_pegawai'       => $kabag,
                'approver_name'          => $kabag ? ($kabag->nama_lengkap ?? $kabag->nama) : 'Kepala Bagian Umum',
                'approver_nip'           => $kabag?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 14: Ka Pokja Keu-Kepeg ──► Approved By ──► Kabag Umum
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA'))
            && (str_contains($jabatanNama, 'KEU') || str_contains($jabatanNama, 'KEPEG'))) {
            $kabag = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
            return [
                'rule_number'            => 14,
                'rule_label'             => 'Rule 14: Ka Pokja Keu-Kepeg ──► Disetujui Kabag Umum',
                'subject_role'           => 'Ka Pokja Keu-Kepeg',
                'approver_title'         => 'Kepala Bagian Umum',
                'approver_pegawai'       => $kabag,
                'approver_name'          => $kabag ? ($kabag->nama_lengkap ?? $kabag->nama) : 'Kepala Bagian Umum',
                'approver_nip'           => $kabag?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 13: Ka Pokja Akademik ──► Approved By ──► Kabag Umum
        // (Pastikan tidak mengandung Sarana/Umum)
        // -------------------------------------------------------------------------------------------------
        if ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA'))
            && str_contains($jabatanNama, 'AKADEMIK') && !str_contains($jabatanNama, 'SARANA') && !str_contains($jabatanNama, 'UMUM')) {
            $kabag = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag TU', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
            return [
                'rule_number'            => 13,
                'rule_label'             => 'Rule 13: Ka Pokja Akademik ──► Disetujui Kabag Umum',
                'subject_role'           => 'Ka Pokja Akademik',
                'approver_title'         => 'Kepala Bagian Umum',
                'approver_pegawai'       => $kabag,
                'approver_name'          => $kabag ? ($kabag->nama_lengkap ?? $kabag->nama) : 'Kepala Bagian Umum',
                'approver_nip'           => $kabag?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 17: PLP / Laboran Lab ──► Approved By ──► Kabag Umum / Kepala UPT Laboratorium Keperawatan
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
                'rule_number'            => 17,
                'rule_label'             => 'Rule 17: PLP / Laboran Lab ──► Disetujui Kabag Umum / Ka UPT Lab',
                'subject_role'           => 'PLP / Laboran Lab',
                'approver_title'         => 'Kabag Umum / Kepala UPT Laboratorium Keperawatan',
                'approver_pegawai'       => $approver,
                'approver_name'          => $approver ? ($approver->nama_lengkap ?? $approver->nama) : 'Kepala UPT Laboratorium Keperawatan',
                'approver_nip'           => $approver?->nip ?? '-',
                'secondary_approver_title' => 'Kepala Bagian Umum',
                'secondary_approver_pegawai' => $kabag,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 12: Staff Pokja Umum Sarana Akademik ──► Approved By ──► Ka Pokja Umum Sarana Akademik
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
                'rule_number'            => 12,
                'rule_label'             => 'Rule 12: Staff Pokja Umum Sarana Akademik ──► Disetujui Ka Pokja Umum Sarana Akademik',
                'subject_role'           => 'Staff Pokja Umum Sarana Akademik',
                'approver_title'         => 'Ka Pokja Umum Sarana Akademik',
                'approver_pegawai'       => $kaPokja,
                'approver_name'          => $kaPokja ? ($kaPokja->nama_lengkap ?? $kaPokja->nama) : 'Ka Pokja Umum Sarana Akademik',
                'approver_nip'           => $kaPokja?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 11: Staff Pokja Keu & Kepeg ──► Approved By ──► Ka Pokja Keu-Kepeg
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
                'rule_number'            => 11,
                'rule_label'             => 'Rule 11: Staff Pokja Keu & Kepeg ──► Disetujui Ka Pokja Keu-Kepeg',
                'subject_role'           => 'Staff Pokja Keu & Kepeg',
                'approver_title'         => 'Ka Pokja Keu-Kepeg',
                'approver_pegawai'       => $kaPokja,
                'approver_name'          => $kaPokja ? ($kaPokja->nama_lengkap ?? $kaPokja->nama) : 'Ka Pokja Keu-Kepeg',
                'approver_nip'           => $kaPokja?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 10: Staff Pokja Akademik dan Kemahasiswaan ──► Approved By ──► Ka Pokja Akademik
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
                'rule_number'            => 10,
                'rule_label'             => 'Rule 10: Staff Pokja Akademik dan Kemahasiswaan ──► Disetujui Ka Pokja Akademik',
                'subject_role'           => 'Staff Pokja Akademik dan Kemahasiswaan',
                'approver_title'         => 'Ka Pokja Akademik',
                'approver_pegawai'       => $kaPokja,
                'approver_name'          => $kaPokja ? ($kaPokja->nama_lengkap ?? $kaPokja->nama) : 'Ka Pokja Akademik',
                'approver_nip'           => $kaPokja?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // =================================================================================================
        // HIERARKI DOSEN (RULES 1, 2, 3, 8, 9, DAN FALLBACK)
        // =================================================================================================
        if ($isDosen) {
            // RULE 1: Dosen S2 Keperawatan ──► Approved By ──► Koordinator Prodi S2 Keperawatan
            if (str_contains($jabatanNama, 'S2') || str_contains($unitNama, 'S2')) {
                $koor = $this->findPegawaiByJabatan([
                    'Koordinator Prodi S2 Keperawatan',
                    'Koordinator Program Studi S2 Keperawatan',
                    'Koorprodi S2',
                    'Program Studi S2 Keperawatan',
                    'Ketua Jurusan Preklinik Keperawatan',
                    'Ketua Jurusan (Kajur)'
                ]);
                return [
                    'rule_number'            => 1,
                    'rule_label'             => 'Rule 1: Dosen S2 Keperawatan ──► Disetujui Koordinator Prodi S2 Keperawatan',
                    'subject_role'           => 'Dosen S2 Keperawatan',
                    'approver_title'         => 'Koordinator Prodi S2 Keperawatan',
                    'approver_pegawai'       => $koor,
                    'approver_name'          => $koor ? ($koor->nama_lengkap ?? $koor->nama) : 'Koordinator Prodi S2 Keperawatan',
                    'approver_nip'           => $koor?->nip ?? '-',
                    'secondary_approver_title' => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // RULE 3: Dosen Profesi Ners ──► Approved By ──► Koordinator Prodi Ners
            if (str_contains($jabatanNama, 'NERS') || str_contains($unitNama, 'NERS')) {
                $koor = $this->findPegawaiByJabatan([
                    'Koordinator Prodi Ners',
                    'Koordinator Program Studi Profesi Ners',
                    'Koorprodi Ners',
                    'Ketua Jurusan Klinik dan Komunitas',
                    'Ketua Jurusan (Kajur)'
                ]);
                return [
                    'rule_number'            => 3,
                    'rule_label'             => 'Rule 3: Dosen Profesi Ners ──► Disetujui Koordinator Prodi Ners',
                    'subject_role'           => 'Dosen Profesi Ners',
                    'approver_title'         => 'Koordinator Prodi Ners',
                    'approver_pegawai'       => $koor,
                    'approver_name'          => $koor ? ($koor->nama_lengkap ?? $koor->nama) : 'Koordinator Prodi Ners',
                    'approver_nip'           => $koor?->nip ?? '-',
                    'secondary_approver_title' => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // RULE 2: Dosen S1 Keperawatan ──► Approved By ──► Koordinator Prodi S1 Keperawatan
            if (str_contains($jabatanNama, 'S1') || (str_contains($unitNama, 'S1') && !str_contains($unitNama, 'S2'))) {
                $koor = $this->findPegawaiByJabatan([
                    'Koordinator Prodi S1 Keperawatan',
                    'Koordinator Program Studi S1 Keperawatan',
                    'Koorprodi S1',
                    'Ketua Jurusan Preklinik Keperawatan',
                    'Ketua Jurusan (Kajur)'
                ]);
                return [
                    'rule_number'            => 2,
                    'rule_label'             => 'Rule 2: Dosen S1 Keperawatan ──► Disetujui Koordinator Prodi S1 Keperawatan',
                    'subject_role'           => 'Dosen S1 Keperawatan',
                    'approver_title'         => 'Koordinator Prodi S1 Keperawatan',
                    'approver_pegawai'       => $koor,
                    'approver_name'          => $koor ? ($koor->nama_lengkap ?? $koor->nama) : 'Koordinator Prodi S1 Keperawatan',
                    'approver_nip'           => $koor?->nip ?? '-',
                    'secondary_approver_title' => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // RULE 9: Dosen Jurusan Preklinik Keperawatan ──► Approved By ──► Ketua Jurusan Preklinik Keperawatan
            if (str_contains($jabatanNama, 'PREKLINIK') || str_contains($unitNama, 'PREKLINIK')) {
                $kajurPre = $this->findPegawaiByJabatan([
                    'Ketua Jurusan Preklinik Keperawatan',
                    'Kajur Preklinik',
                    'Ketua Jurusan (Kajur)',
                    'Wakil Dekan I (Bid. Akademik)'
                ]);
                return [
                    'rule_number'            => 9,
                    'rule_label'             => 'Rule 9: Dosen Jurusan Preklinik Keperawatan ──► Disetujui Ketua Jurusan Preklinik Keperawatan',
                    'subject_role'           => 'Dosen Jurusan Preklinik Keperawatan',
                    'approver_title'         => 'Ketua Jurusan Preklinik Keperawatan',
                    'approver_pegawai'       => $kajurPre,
                    'approver_name'          => $kajurPre ? ($kajurPre->nama_lengkap ?? $kajurPre->nama) : 'Ketua Jurusan Preklinik Keperawatan',
                    'approver_nip'           => $kajurPre?->nip ?? '-',
                    'secondary_approver_title' => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // RULE 8: Dosen Jurusan Klinik dan Komunitas ──► Approved By ──► Ketua Jurusan Klinik dan Komunitas
            if (str_contains($jabatanNama, 'KLINIK') || str_contains($unitNama, 'KLINIK')) {
                $kajurKlinik = $this->findPegawaiByJabatan([
                    'Ketua Jurusan Klinik dan Komunitas',
                    'Kajur Klinik',
                    'Ketua Jurusan (Kajur)',
                    'Wakil Dekan I (Bid. Akademik)'
                ]);
                return [
                    'rule_number'            => 8,
                    'rule_label'             => 'Rule 8: Dosen Jurusan Klinik dan Komunitas ──► Disetujui Ketua Jurusan Klinik dan Komunitas',
                    'subject_role'           => 'Dosen Jurusan Klinik dan Komunitas',
                    'approver_title'         => 'Ketua Jurusan Klinik dan Komunitas',
                    'approver_pegawai'       => $kajurKlinik,
                    'approver_name'          => $kajurKlinik ? ($kajurKlinik->nama_lengkap ?? $kajurKlinik->nama) : 'Ketua Jurusan Klinik dan Komunitas',
                    'approver_nip'           => $kajurKlinik?->nip ?? '-',
                    'secondary_approver_title' => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // Fallback Dosen Umum ──► Approved By ──► Ketua Jurusan (Kajur)
            $kajur = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan Keperawatan',
                'Ketua Jurusan',
                'Koordinator Prodi S1 Keperawatan',
                'Wakil Dekan I (Bid. Akademik)',
                'Dekan'
            ]);
            return [
                'rule_number'            => 4,
                'rule_label'             => 'Rule 4: Dosen Fakultas Keperawatan ──► Disetujui Ketua Jurusan',
                'subject_role'           => 'Dosen Fakultas Keperawatan',
                'approver_title'         => 'Ketua Jurusan (Kajur)',
                'approver_pegawai'       => $kajur,
                'approver_name'          => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan (Kajur)',
                'approver_nip'           => $kajur?->nip ?? '-',
                'secondary_approver_title' => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // =================================================================================================
        // FALLBACK STAFF / TENDIK / UMUM ──► Approved By ──► Kabag Umum
        // (Contoh: Pengadministrasi, Pengolah Data, Penata Layanan, Operator, PHL, Bagian Umum)
        // =================================================================================================
        $kabag = $this->findPegawaiByJabatan([
            'Kepala Bagian Umum',
            'Kabag Umum',
            'Kabag TU',
            'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'Dekan'
        ]);
        return [
            'rule_number'            => 16,
            'rule_label'             => 'Rule 16: Tenaga Kependidikan / Staff Bagian Umum ──► Disetujui Kabag Umum',
            'subject_role'           => $pegawai->jabatan?->nama_jabatan ?? 'Tenaga Kependidikan',
            'approver_title'         => 'Kepala Bagian Umum',
            'approver_pegawai'       => $kabag,
            'approver_name'          => $kabag ? ($kabag->nama_lengkap ?? $kabag->nama) : 'Kepala Bagian Umum',
            'approver_nip'           => $kabag?->nip ?? '-',
            'secondary_approver_title' => null,
            'secondary_approver_pegawai' => null,
        ];
    }

    /**
     * Dapatkan Pegawai Atasan Langsung untuk Persetujuan Logbook Kinerja
     * Mengembalikan Pegawai model atau null (misal Dekan yang atasannya adalah Rektor di luar sistem)
     */
    public function getAtasanLangsung(Pegawai $pegawai): ?Pegawai
    {
        // Evaluasi hierarki presisi 20 aturan
        $info = $this->getApprovalRuleInfo($pegawai);

        // Jika Dekan, atasan adalah Rektor (tidak ada di tabel Pegawai internal)
        if ($info['rule_number'] === 20) {
            return null;
        }

        if (!empty($info['approver_pegawai']) && $info['approver_pegawai']->id !== $pegawai->id) {
            return $info['approver_pegawai'];
        }

        // Cek manual atasan_id di DB sebagai pendukung jika pejabat belum terdaftar di jabatan struktural
        if (!empty($pegawai->atasan_id)) {
            $atasanManual = Pegawai::with(['jabatan', 'unitKerja'])->find($pegawai->atasan_id);
            if ($atasanManual && $atasanManual->id !== $pegawai->id) {
                return $atasanManual;
            }
        }

        return null;
    }

    /**
     * Dapatkan ID seluruh pegawai bawahan langsung & hirarki persetujuan dari seorang pimpinan
     * Diterapkan secara simetris terhadap matriks 20 aturan persetujuan
     */
    public function getBawahanIdsForPegawai(Pegawai $pimpinan): array
    {
        $cacheKey = "bawahan_ids_pegawai_" . $pimpinan->id;
        
        return Cache::remember($cacheKey, 60, function () use ($pimpinan) {
            // 1. Bawahan eksplisit via atasan_id manual di DB
            $directIds = Pegawai::where('atasan_id', $pimpinan->id)->pluck('id')->toArray();

            $jabatanNama = strtoupper(trim((string)($pimpinan->jabatan->nama_jabatan ?? '')));
            $implicitIds = [];

            // ---------------------------------------------------------------------------------------------
            // 1. Dekan Fakultas Keperawatan
            // Menyetujui:
            // - Rule 19: Para Wadek (WD I, WD II, WD III)
            // - Rule 16: Kabag Umum
            // ---------------------------------------------------------------------------------------------
            if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL') && !str_contains($jabatanNama, 'WADEK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Wakil Dekan%')
                           ->orWhere('nama_jabatan', 'like', '%Wadek%')
                           ->orWhere('nama_jabatan', 'like', '%Kepala Bagian Umum%')
                           ->orWhere('nama_jabatan', 'like', '%Kabag Umum%');
                    });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 2. Wakil Dekan I (Bid. Akademik)
            // Menyetujui:
            // - Rule 5: Ketua Jurusan (Kajur)
            // - Rule 18: Kepala Lab & Kepala Unit
            // ---------------------------------------------------------------------------------------------
            elseif (str_contains($jabatanNama, 'WAKIL DEKAN I') || str_contains($jabatanNama, 'WD I') 
                || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'AKADEMIK'))) {
                $implicitIds = Pegawai::where(function ($q) {
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

            // ---------------------------------------------------------------------------------------------
            // 3. Wakil Dekan II (Bid. Keuangan dan Umum)
            // Menyetujui:
            // - Rule 16: Kabag Umum
            // - Rule 18: Kepala Lab & Kepala Unit (opsional / co-approval)
            // ---------------------------------------------------------------------------------------------
            elseif (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II') 
                || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'KEUANGAN'))) {
                $implicitIds = Pegawai::where(function ($q) {
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

            // ---------------------------------------------------------------------------------------------
            // 4. Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)
            // Menyetujui: Unit Kemahasiswaan & Bimbingan Khusus
            // ---------------------------------------------------------------------------------------------
            elseif (str_contains($jabatanNama, 'WAKIL DEKAN III') || str_contains($jabatanNama, 'WD III') 
                || (str_contains($jabatanNama, 'WAKIL DEKAN') && str_contains($jabatanNama, 'KEMAHASISWAAN'))) {
                $implicitIds = Pegawai::whereHas('unitKerja', function ($uq) {
                    $uq->where('nama_unit', 'like', '%Kemahasiswaan%');
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 5. Ketua Jurusan (Kajur) Keperawatan
            // Menyetujui:
            // - Rule 4: Koordinator Prodi S1, S2, dan Ners
            // - Rule 6: Ketua Jurusan Klinik dan Komunitas
            // - Rule 7: Ketua Jurusan Preklinik Keperawatan
            // - Serta Dosen Umum yang belum terpetakan ke prodi khusus
            // ---------------------------------------------------------------------------------------------
            elseif ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) 
                    && !str_contains($jabatanNama, 'PREKLINIK') && !str_contains($jabatanNama, 'KLINIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    // Koordinator Prodi S1, S2, Ners
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Koordinator Prodi%')
                           ->orWhere('nama_jabatan', 'like', '%Koordinator Program Studi%')
                           ->orWhere('nama_jabatan', 'like', '%Koorprodi%');
                    })
                    // Kajur Klinik dan Preklinik
                    ->orWhereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Ketua Jurusan Klinik%')
                           ->orWhere('nama_jabatan', 'like', '%Ketua Jurusan Preklinik%');
                    })
                    // Dosen umum
                    ->orWhere(function ($dq) {
                        $dq->where('jenis_pegawai', 'Dosen')
                           ->where(function ($sub) {
                               $sub->whereNull('unit_kerja_id')
                                   ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Jurusan Keperawatan%'));
                           });
                    });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 6. Ketua Jurusan Klinik dan Komunitas
            // Menyetujui:
            // - Rule 8: Dosen Jurusan Klinik dan Komunitas
            // ---------------------------------------------------------------------------------------------
            elseif ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) && str_contains($jabatanNama, 'KLINIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen Jurusan Klinik%'))
                      ->orWhere(function ($uq) {
                          $uq->whereHas('unitKerja', fn($u) => $u->where('nama_unit', 'like', '%Klinik%'))
                             ->where(function ($sq) {
                                 $sq->where('jenis_pegawai', 'Dosen')
                                    ->orWhereNotNull('nidn_nuptk');
                             })
                             ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Ketua Jurusan%'));
                      });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 7. Ketua Jurusan Preklinik Keperawatan
            // Menyetujui:
            // - Rule 9: Dosen Jurusan Preklinik Keperawatan
            // ---------------------------------------------------------------------------------------------
            elseif ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) && str_contains($jabatanNama, 'PREKLINIK')) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen Jurusan Preklinik%'))
                      ->orWhere(function ($uq) {
                          $uq->whereHas('unitKerja', fn($u) => $u->where('nama_unit', 'like', '%Preklinik%'))
                             ->where(function ($sq) {
                                 $sq->where('jenis_pegawai', 'Dosen')
                                    ->orWhereNotNull('nidn_nuptk');
                             })
                             ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Ketua Jurusan%'));
                      });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 8. Koordinator Prodi S1 Keperawatan
            // Menyetujui:
            // - Rule 2: Dosen S1 Keperawatan
            // ---------------------------------------------------------------------------------------------
            elseif (str_contains($jabatanNama, 'KOORDINATOR PRODI S1') || str_contains($jabatanNama, 'KOORPRODI S1') 
                || (str_contains($jabatanNama, 'KOORDINATOR') && str_contains($jabatanNama, 'S1'))) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen S1%'))
                      ->orWhere(function ($sq) {
                          $sq->whereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%S1%')->where('nama_unit', 'not like', '%S2%'))
                             ->where(function ($dq) {
                                 $dq->where('jenis_pegawai', 'Dosen')
                                    ->orWhereNotNull('nidn_nuptk');
                             })
                             ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Koordinator%'));
                      });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 9. Koordinator Prodi S2 Keperawatan
            // Menyetujui:
            // - Rule 1: Dosen S2 Keperawatan
            // ---------------------------------------------------------------------------------------------
            elseif (str_contains($jabatanNama, 'KOORDINATOR PRODI S2') || str_contains($jabatanNama, 'KOORPRODI S2') 
                || (str_contains($jabatanNama, 'KOORDINATOR') && str_contains($jabatanNama, 'S2'))) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen S2%'))
                      ->orWhere(function ($sq) {
                          $sq->whereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%S2%'))
                             ->where(function ($dq) {
                                 $dq->where('jenis_pegawai', 'Dosen')
                                    ->orWhereNotNull('nidn_nuptk');
                             })
                             ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Koordinator%'));
                      });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 10. Koordinator Prodi Ners
            // Menyetujui:
            // - Rule 3: Dosen Profesi Ners
            // ---------------------------------------------------------------------------------------------
            elseif (str_contains($jabatanNama, 'KOORDINATOR PRODI NERS') || str_contains($jabatanNama, 'KOORPRODI NERS') 
                || (str_contains($jabatanNama, 'KOORDINATOR') && str_contains($jabatanNama, 'NERS'))) {
                $implicitIds = Pegawai::where(function ($q) {
                    $q->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Dosen Profesi Ners%')->orWhere('nama_jabatan', 'like', '%Dosen Ners%'))
                      ->orWhere(function ($sq) {
                          $sq->whereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Ners%'))
                             ->where(function ($dq) {
                                 $dq->where('jenis_pegawai', 'Dosen')
                                    ->orWhereNotNull('nidn_nuptk');
                             })
                             ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Koordinator%'));
                      });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 11. Kepala Bagian Umum (Kabag Umum)
            // Menyetujui:
            // - Rule 13: Ka Pokja Akademik
            // - Rule 14: Ka Pokja Keu-Kepeg
            // - Rule 15: Ka Pokja Umum Sarana Akademik
            // - Rule 17: PLP / Laboran Lab (co-approval dengan Ka UPT Lab)
            // - Serta Tendik/Staff umum Bagian Umum / PHL
            // ---------------------------------------------------------------------------------------------
            elseif (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM') || str_contains($jabatanNama, 'KABAG TU')) {
                $implicitIds = Pegawai::where(function ($q) {
                    // Ka Pokja
                    $q->whereHas('jabatan', function ($jq) {
                        $jq->where('nama_jabatan', 'like', '%Ka Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%Kepala Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%Ketua Pokja%')
                           ->orWhere('nama_jabatan', 'like', '%PLP%')
                           ->orWhere('nama_jabatan', 'like', '%Laboran%');
                    })
                    // Staff Bagian Umum / Tendik umum
                    ->orWhere(function ($sq) {
                        $sq->where('jenis_pegawai', '!=', 'Dosen')
                           ->whereNull('nidn_nuptk')
                           ->whereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Bagian Umum%')->orWhere('nama_unit', 'like', '%Dekanat%'));
                    });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 12. Ka Pokja Akademik
            // Menyetujui:
            // - Rule 10: Staff Pokja Akademik dan Kemahasiswaan
            // ---------------------------------------------------------------------------------------------
            elseif ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA')) 
                && str_contains($jabatanNama, 'AKADEMIK') && !str_contains($jabatanNama, 'SARANA') && !str_contains($jabatanNama, 'UMUM')) {
                $implicitIds = Pegawai::where(function ($q) use ($pimpinan) {
                    $q->where('id', '!=', $pimpinan->id)
                      ->where(function ($sq) {
                          $sq->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Akademik%'))
                             ->orWhere(function ($uq) {
                                 $uq->whereHas('unitKerja', fn($u) => $u->where('nama_unit', 'like', '%Akademik%'))
                                    ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Ka Pokja%')->orWhere('nama_jabatan', 'like', '%Dosen%'));
                             });
                      });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 13. Ka Pokja Keu-Kepeg
            // Menyetujui:
            // - Rule 11: Staff Pokja Keu & Kepeg
            // ---------------------------------------------------------------------------------------------
            elseif ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA')) 
                && (str_contains($jabatanNama, 'KEU') || str_contains($jabatanNama, 'KEPEG'))) {
                $implicitIds = Pegawai::where(function ($q) use ($pimpinan) {
                    $q->where('id', '!=', $pimpinan->id)
                      ->where(function ($sq) {
                          $sq->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Keu%'))
                             ->orWhere(function ($uq) {
                                 $uq->whereHas('unitKerja', fn($u) => $u->where('nama_unit', 'like', '%Keuangan%'))
                                    ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Ka Pokja%')->orWhere('nama_jabatan', 'like', '%Dosen%'));
                             });
                      });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 14. Ka Pokja Umum Sarana Akademik
            // Menyetujui:
            // - Rule 12: Staff Pokja Umum Sarana Akademik
            // ---------------------------------------------------------------------------------------------
            elseif ((str_contains($jabatanNama, 'KA POKJA') || str_contains($jabatanNama, 'KEPALA POKJA') || str_contains($jabatanNama, 'KETUA POKJA')) 
                && (str_contains($jabatanNama, 'UMUM') || str_contains($jabatanNama, 'SARANA'))) {
                $implicitIds = Pegawai::where(function ($q) use ($pimpinan) {
                    $q->where('id', '!=', $pimpinan->id)
                      ->where(function ($sq) {
                          $sq->whereHas('jabatan', fn($jq) => $jq->where('nama_jabatan', 'like', '%Staff Pokja Umum%'))
                             ->orWhere(function ($uq) {
                                 $uq->whereHas('unitKerja', fn($u) => $u->where('nama_unit', 'like', '%Sarana%')->orWhere('nama_unit', 'like', '%Umum%'))
                                    ->whereDoesntHave('jabatan', fn($j) => $j->where('nama_jabatan', 'like', '%Ka Pokja%')->orWhere('nama_jabatan', 'like', '%Kabag%')->orWhere('nama_jabatan', 'like', '%Dosen%'));
                             });
                      });
                })->pluck('id')->toArray();
            }

            // ---------------------------------------------------------------------------------------------
            // 15. Kepala UPT Laboratorium Keperawatan
            // Menyetujui:
            // - Rule 17: PLP / Laboran Lab
            // ---------------------------------------------------------------------------------------------
            elseif (str_contains($jabatanNama, 'KEPALA UPT LAB') || str_contains($jabatanNama, 'KEPALA LABORATORIUM') || str_contains($jabatanNama, 'KEPALA LAB')) {
                $implicitIds = Pegawai::where(function ($q) use ($pimpinan) {
                    $q->where('id', '!=', $pimpinan->id)
                      ->where(function ($sq) {
                          $sq->whereHas('jabatan', function ($jq) {
                              $jq->where('nama_jabatan', 'like', '%PLP%')
                                 ->orWhere('nama_jabatan', 'like', '%Laboran%')
                                 ->orWhere('nama_jabatan', 'like', '%Pranata Laboratorium%');
                          })->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Laboratorium%'));
                      });
                })->pluck('id')->toArray();
            }

            $allIds = array_unique(array_merge($directIds, $implicitIds));
            
            // Kecualikan diri sendiri dari daftar bawahan
            return array_values(array_filter($allIds, fn($id) => (int)$id !== (int)$pimpinan->id));
        });
    }

    /**
     * Dapatkan Atasan Langsung untuk Permohonan Cuti (Bagian VI)
     * Aturan Cuti PNS / PPPK FKP UNRI:
     * - Dosen ──► Ketua Jurusan (Kajur)
     * - Tendik ──► Kepala Bagian Umum (Kabag Umum)
     */
    public function getAtasanLangsungCuti(Pegawai $pegawai): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // Jika pemohon adalah Dekan, Atasan Langsung adalah Rektor (fallback Dekan)
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
            return $pegawai;
        }

        // Jika pemohon adalah Wadek, Atasan Langsung adalah Dekan
        if (str_contains($jabatanNama, 'WAKIL DEKAN') || str_contains($jabatanNama, 'WADEK')) {
            return $this->findPegawaiByJabatan(['Dekan']);
        }

        // Jika pemohon adalah Ketua Jurusan, Atasan Langsung adalah Wakil Dekan I / Wadek II
        if (str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) {
            return $this->findPegawaiByJabatan(['Wakil Dekan I (Bid. Akademik)', 'Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
        }

        // Jika pemohon adalah Kepala Bagian Umum, Atasan Langsung adalah Wakil Dekan II
        if (str_contains($jabatanNama, 'KEPALA BAGIAN UMUM') || str_contains($jabatanNama, 'KABAG UMUM')) {
            return $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Dekan']);
        }

        // Jika Dosen: VI. Pertimbangan Atasan Langsung = Ketua Jurusan
        if ($pegawai->isDosen()) {
            return $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan Preklinik Keperawatan',
                'Ketua Jurusan Klinik dan Komunitas',
            ]);
        }

        // Jika Tendik (dan PHL/pegawai lainnya): VI. Pertimbangan Atasan Langsung = Kepala Bagian Umum
        return $this->findPegawaiByJabatan([
            'Kepala Bagian Umum',
            'Kabag Umum',
        ]);
    }

    /**
     * Dapatkan Pejabat Yang Berwenang Memberikan Cuti (PYBMC) - Bagian VII
     * Aturan Resmi SIKAP FKP UNRI:
     * Baik Dosen maupun Tendik ──► Wakil Dekan II (Bid. Keuangan dan Umum)
     */
    public function getPybmcCuti(Pegawai $pegawai, ?string $jenisCuti = null): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // 1. Jika pemohon adalah Dekan, PYBMC adalah Rektor (fallback ke diri sendiri / admin)
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
            return $pegawai;
        }

        // 2. Jika pemohon adalah Wadek II, PYBMC dialihkan ke Dekan
        if (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')) {
            return $this->findPegawaiByJabatan(['Dekan']);
        }

        // 3. Ketetapan Resmi: Wakil Dekan II (Bid. Keuangan dan Umum)
        return $this->findPegawaiByJabatan([
            'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'Wakil Dekan II',
        ]);
    }

    /**
     * Helper untuk mendapatkan teks Pejabat Cuti (Nama, Jabatan, NIP) untuk formulir PDF & cetak
     */
    public function getPejabatCutiInfo(?Pegawai $pegawai): array
    {
        $isDosen = $pegawai ? $pegawai->isDosen() : false;
        $jabatanNama = strtoupper(trim((string)($pegawai?->jabatan?->nama_jabatan ?? '')));

        $pybmcPegawai  = $pegawai ? $this->getPybmcCuti($pegawai) : null;

        // Label & Nama Atasan Langsung (Bagian VI)
        // Untuk Dosen: Ketua Jurusan (dari getAtasanLangsungCuti)
        // Untuk Tendik: SELALU Kabag Umum (lookup by jabatan, bukan atasan personal)
        if ($isDosen) {
            $atasanPegawai = $pegawai ? $this->getAtasanLangsungCuti($pegawai) : null;
            $atasanJabatan = 'Ketua Jurusan';
            $atasanNama    = $atasanPegawai ? ($atasanPegawai->nama_lengkap ?? $atasanPegawai->nama) : 'Ketua Jurusan';
            $atasanNip     = $atasanPegawai?->nip ?? '.....................................................';
        } else {
            // Tendik: VI selalu ditandatangani Kabag Umum, apapun jabatan pemohon
            $kabagUmum     = $this->findPegawaiByJabatan(['Kepala Bagian Umum', 'Kabag Umum', 'Kabag. Umum']);
            $atasanJabatan = 'Kepala Bagian Umum';
            $atasanNama    = $kabagUmum ? ($kabagUmum->nama_lengkap ?? $kabagUmum->nama) : 'Kepala Bagian Umum';
            $atasanNip     = $kabagUmum?->nip ?? '.....................................................';
        }

        // Label & Nama PYBMC (Bagian VII)
        $pybmcJabatan = 'Wakil Dekan II (Bid. Keuangan dan Umum)';
        $pybmcNama    = $pybmcPegawai ? ($pybmcPegawai->nama_lengkap ?? $pybmcPegawai->nama) : 'Wakil Dekan II';
        $pybmcNip     = $pybmcPegawai?->nip ?? '.....................................................';

        // Pengecualian khusus Pejabat Pimpinan Tertinggi:
        // 1. Jika pemohon adalah Dekan: Atasan & PYBMC adalah Rektor
        if (str_contains($jabatanNama, 'DEKAN') && !str_contains($jabatanNama, 'WAKIL')) {
            $atasanJabatan = 'Rektor Universitas Riau';
            $atasanNama    = 'Rektor Universitas Riau';
            $atasanNip     = '';
            $pybmcJabatan  = 'Rektor Universitas Riau';
            $pybmcNama     = 'Rektor Universitas Riau';
            $pybmcNip      = '';
        }
        // 2. Jika pemohon adalah Wadek II: PYBMC adalah Dekan
        elseif (str_contains($jabatanNama, 'WAKIL DEKAN II') || str_contains($jabatanNama, 'WD II')) {
            $pybmcJabatan = 'Dekan Fakultas Keperawatan';
            $dekan = $this->findPegawaiByJabatan(['Dekan']);
            if ($dekan) {
                $pybmcNama = $dekan->nama_lengkap ?? $dekan->nama;
                $pybmcNip  = $dekan->nip ?? '';
            }
        }

        return [
            'is_dosen'       => $isDosen,
            'atasan_jabatan' => $atasanJabatan,
            'atasan_nama'    => $atasanNama,
            'atasan_nip'     => $atasanNip,
            'pybmc_jabatan'  => $pybmcJabatan,
            'pybmc_nama'     => $pybmcNama,
            'pybmc_nip'      => $pybmcNip,
        ];
    }

    /**
     * Helper cari Pegawai berdasarkan list nama jabatan
     */
    public function findPegawaiByJabatan(array $jabatanNames): ?Pegawai
    {
        foreach ($jabatanNames as $name) {
            $pegawai = Pegawai::whereHas('jabatan', function ($q) use ($name) {
                $q->where('nama_jabatan', 'like', "%{$name}%");
            })->first();

            if ($pegawai) {
                return $pegawai;
            }
        }

        return null;
    }
}
