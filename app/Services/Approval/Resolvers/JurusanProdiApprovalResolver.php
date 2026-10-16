<?php

namespace App\Services\Approval\Resolvers;

use App\Models\Pegawai;

class JurusanProdiApprovalResolver extends AbstractApprovalResolver
{
    /**
     * Memeriksa apakah pegawai adalah Dosen, Kajur, atau Koorprodi
     */
    public function supports(Pegawai $pegawai): bool
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));
        
        return $pegawai->isDosen()
            || str_contains($jabatanNama, 'KETUA JURUSAN')
            || str_contains($jabatanNama, 'KAJUR')
            || str_contains($jabatanNama, 'KOORDINATOR PRODI')
            || str_contains($jabatanNama, 'KOORPRODI')
            || str_contains($jabatanNama, 'KOORDINATOR PROGRAM STUDI');
    }

    /**
     * Selesaikan aturan persetujuan kinerja logbook tingkat Jurusan & Prodi (Rule 1, 2, 3, 4, 5, 6, 7, 8, 9)
     */
    public function resolveApprovalRule(Pegawai $pegawai): ?array
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));
        $unitNama    = strtoupper(trim((string)($pegawai->unitKerja->nama_unit ?? '')));
        $isDosen     = $pegawai->isDosen();

        // -------------------------------------------------------------------------------------------------
        // RULE 7: Ketua Jurusan Preklinik Keperawatan ──► Disetujui Ketua Jurusan (Kajur)
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
                'rule_number'                => 7,
                'rule_label'                 => 'Rule 7: Ketua Jurusan Preklinik Keperawatan ──► Disetujui Ketua Jurusan (Kajur)',
                'subject_role'               => 'Ketua Jurusan Preklinik Keperawatan',
                'approver_title'             => 'Ketua Jurusan (Kajur)',
                'approver_pegawai'           => $kajur,
                'approver_name'              => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan (Kajur)',
                'approver_nip'               => $kajur?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 6: Ketua Jurusan Klinik dan Komunitas ──► Disetujui Ketua Jurusan (Kajur)
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
                'rule_number'                => 6,
                'rule_label'                 => 'Rule 6: Ketua Jurusan Klinik dan Komunitas ──► Disetujui Ketua Jurusan (Kajur)',
                'subject_role'               => 'Ketua Jurusan Klinik dan Komunitas',
                'approver_title'             => 'Ketua Jurusan (Kajur)',
                'approver_pegawai'           => $kajur,
                'approver_name'              => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan (Kajur)',
                'approver_nip'               => $kajur?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 5: Ketua Jurusan (Kajur) ──► Disetujui Wakil Dekan I (Bid. Akademik)
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
                'rule_number'                => 5,
                'rule_label'                 => 'Rule 5: Ketua Jurusan (Kajur) ──► Disetujui Wakil Dekan I (Bid. Akademik)',
                'subject_role'               => 'Ketua Jurusan (Kajur)',
                'approver_title'             => 'Wakil Dekan I (Bid. Akademik)',
                'approver_pegawai'           => $wadek1,
                'approver_name'              => $wadek1 ? ($wadek1->nama_lengkap ?? $wadek1->nama) : 'Wakil Dekan I (Bid. Akademik)',
                'approver_nip'               => $wadek1?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // RULE 4: Koordinator Prodi S1, S2, dan Ners ──► Disetujui Ketua Jurusan
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
                'rule_number'                => 4,
                'rule_label'                 => 'Rule 4: Koordinator Prodi S1, S2, dan Ners ──► Disetujui Ketua Jurusan',
                'subject_role'               => $pegawai->jabatan?->nama_jabatan ?? 'Koordinator Prodi',
                'approver_title'             => 'Ketua Jurusan (Kajur)',
                'approver_pegawai'           => $kajur,
                'approver_name'              => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan (Kajur)',
                'approver_nip'               => $kajur?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        // -------------------------------------------------------------------------------------------------
        // HIERARKI DOSEN (RULES 1, 2, 3, 8, 9, DAN FALLBACK)
        // -------------------------------------------------------------------------------------------------
        if ($isDosen) {
            // Rule S3: Dosen S3 Keperawatan
            if (str_contains($jabatanNama, 'S3') || str_contains($unitNama, 'S3')) {
                $koor = $this->findPegawaiByJabatan([
                    'Koordinator Prodi S3 Keperawatan',
                    'Koordinator Program Studi S3 Keperawatan',
                    'Koorprodi S3',
                    'Ketua Jurusan Preklinik Keperawatan',
                    'Ketua Jurusan (Kajur)'
                ]);
                return [
                    'rule_number'                => 1,
                    'rule_label'                 => 'Rule S3: Dosen S3 Keperawatan ──► Disetujui Koordinator Prodi S3 Keperawatan',
                    'subject_role'               => 'Dosen S3 Keperawatan',
                    'approver_title'             => 'Koordinator Prodi S3 Keperawatan',
                    'approver_pegawai'           => $koor,
                    'approver_name'              => $koor ? ($koor->nama_lengkap ?? $koor->nama) : 'Koordinator Prodi S3 Keperawatan',
                    'approver_nip'               => $koor?->nip ?? '-',
                    'secondary_approver_title'   => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // Rule 1: Dosen S2 Keperawatan
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
                    'rule_number'                => 1,
                    'rule_label'                 => 'Rule 1: Dosen S2 Keperawatan ──► Disetujui Koordinator Prodi S2 Keperawatan',
                    'subject_role'               => 'Dosen S2 Keperawatan',
                    'approver_title'             => 'Koordinator Prodi S2 Keperawatan',
                    'approver_pegawai'           => $koor,
                    'approver_name'              => $koor ? ($koor->nama_lengkap ?? $koor->nama) : 'Koordinator Prodi S2 Keperawatan',
                    'approver_nip'               => $koor?->nip ?? '-',
                    'secondary_approver_title'   => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // Rule 3: Dosen Profesi Ners
            if (str_contains($jabatanNama, 'NERS') || str_contains($unitNama, 'NERS')) {
                $koor = $this->findPegawaiByJabatan([
                    'Koordinator Prodi Ners',
                    'Koordinator Program Studi Profesi Ners',
                    'Koorprodi Ners',
                    'Ketua Jurusan Klinik dan Komunitas',
                    'Ketua Jurusan (Kajur)'
                ]);
                return [
                    'rule_number'                => 3,
                    'rule_label'                 => 'Rule 3: Dosen Profesi Ners ──► Disetujui Koordinator Prodi Ners',
                    'subject_role'               => 'Dosen Profesi Ners',
                    'approver_title'             => 'Koordinator Prodi Ners',
                    'approver_pegawai'           => $koor,
                    'approver_name'              => $koor ? ($koor->nama_lengkap ?? $koor->nama) : 'Koordinator Prodi Ners',
                    'approver_nip'               => $koor?->nip ?? '-',
                    'secondary_approver_title'   => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // Rule 2: Dosen S1 Keperawatan
            if (str_contains($jabatanNama, 'S1') || (str_contains($unitNama, 'S1') && !str_contains($unitNama, 'S2'))) {
                $koor = $this->findPegawaiByJabatan([
                    'Koordinator Prodi S1 Keperawatan',
                    'Koordinator Program Studi S1 Keperawatan',
                    'Koorprodi S1',
                    'Ketua Jurusan Preklinik Keperawatan',
                    'Ketua Jurusan (Kajur)'
                ]);
                return [
                    'rule_number'                => 2,
                    'rule_label'                 => 'Rule 2: Dosen S1 Keperawatan ──► Disetujui Koordinator Prodi S1 Keperawatan',
                    'subject_role'               => 'Dosen S1 Keperawatan',
                    'approver_title'             => 'Koordinator Prodi S1 Keperawatan',
                    'approver_pegawai'           => $koor,
                    'approver_name'              => $koor ? ($koor->nama_lengkap ?? $koor->nama) : 'Koordinator Prodi S1 Keperawatan',
                    'approver_nip'               => $koor?->nip ?? '-',
                    'secondary_approver_title'   => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // Rule 9: Dosen Jurusan Preklinik Keperawatan
            if (str_contains($jabatanNama, 'PREKLINIK') || str_contains($unitNama, 'PREKLINIK')) {
                $kajurPre = $this->findPegawaiByJabatan([
                    'Ketua Jurusan Preklinik Keperawatan',
                    'Kajur Preklinik',
                    'Ketua Jurusan (Kajur)',
                    'Wakil Dekan I (Bid. Akademik)'
                ]);
                return [
                    'rule_number'                => 9,
                    'rule_label'                 => 'Rule 9: Dosen Jurusan Preklinik Keperawatan ──► Disetujui Ketua Jurusan Preklinik Keperawatan',
                    'subject_role'               => 'Dosen Jurusan Preklinik Keperawatan',
                    'approver_title'             => 'Ketua Jurusan Preklinik Keperawatan',
                    'approver_pegawai'           => $kajurPre,
                    'approver_name'              => $kajurPre ? ($kajurPre->nama_lengkap ?? $kajurPre->nama) : 'Ketua Jurusan Preklinik Keperawatan',
                    'approver_nip'               => $kajurPre?->nip ?? '-',
                    'secondary_approver_title'   => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // Rule 8: Dosen Jurusan Klinik dan Komunitas
            if (str_contains($jabatanNama, 'KLINIK') || str_contains($unitNama, 'KLINIK')) {
                $kajurKlinik = $this->findPegawaiByJabatan([
                    'Ketua Jurusan Klinik dan Komunitas',
                    'Kajur Klinik',
                    'Ketua Jurusan (Kajur)',
                    'Wakil Dekan I (Bid. Akademik)'
                ]);
                return [
                    'rule_number'                => 8,
                    'rule_label'                 => 'Rule 8: Dosen Jurusan Klinik dan Komunitas ──► Disetujui Ketua Jurusan Klinik dan Komunitas',
                    'subject_role'               => 'Dosen Jurusan Klinik dan Komunitas',
                    'approver_title'             => 'Ketua Jurusan Klinik dan Komunitas',
                    'approver_pegawai'           => $kajurKlinik,
                    'approver_name'              => $kajurKlinik ? ($kajurKlinik->nama_lengkap ?? $kajurKlinik->nama) : 'Ketua Jurusan Klinik dan Komunitas',
                    'approver_nip'               => $kajurKlinik?->nip ?? '-',
                    'secondary_approver_title'   => null,
                    'secondary_approver_pegawai' => null,
                ];
            }

            // Fallback Dosen Umum ──► Disetujui Ketua Jurusan (Kajur)
            $kajur = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan Keperawatan',
                'Ketua Jurusan',
                'Koordinator Prodi S1 Keperawatan',
                'Wakil Dekan I (Bid. Akademik)',
                'Dekan'
            ]);
            return [
                'rule_number'                => 4,
                'rule_label'                 => 'Rule 4: Dosen Fakultas Keperawatan ──► Disetujui Ketua Jurusan',
                'subject_role'               => 'Dosen Fakultas Keperawatan',
                'approver_title'             => 'Ketua Jurusan (Kajur)',
                'approver_pegawai'           => $kajur,
                'approver_name'              => $kajur ? ($kajur->nama_lengkap ?? $kajur->nama) : 'Ketua Jurusan (Kajur)',
                'approver_nip'               => $kajur?->nip ?? '-',
                'secondary_approver_title'   => null,
                'secondary_approver_pegawai' => null,
            ];
        }

        return null;
    }

    /**
     * Dapatkan daftar ID bawahan untuk Kajur dan Koorprodi
     */
    public function getBawahanIds(Pegawai $pimpinan): ?array
    {
        $jabatanNama = strtoupper(trim((string)($pimpinan->jabatan->nama_jabatan ?? '')));

        // 1. Ketua Jurusan (Kajur) Keperawatan Utama
        if ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) 
            && !str_contains($jabatanNama, 'PREKLINIK') && !str_contains($jabatanNama, 'KLINIK')) {
            return Pegawai::where(function ($q) {
                $q->whereHas('jabatan', function ($jq) {
                    $jq->where('nama_jabatan', 'like', '%Koordinator Prodi%')
                       ->orWhere('nama_jabatan', 'like', '%Koordinator Program Studi%')
                       ->orWhere('nama_jabatan', 'like', '%Koorprodi%');
                })
                ->orWhereHas('jabatan', function ($jq) {
                    $jq->where('nama_jabatan', 'like', '%Ketua Jurusan Klinik%')
                       ->orWhere('nama_jabatan', 'like', '%Ketua Jurusan Preklinik%');
                })
                ->orWhere(function ($dq) {
                    $dq->where('jenis_pegawai', 'Dosen')
                       ->where(function ($sub) {
                           $sub->whereNull('unit_kerja_id')
                               ->orWhereHas('unitKerja', fn($uq) => $uq->where('nama_unit', 'like', '%Jurusan Keperawatan%'));
                       });
                });
            })->pluck('id')->toArray();
        }

        // 2. Ketua Jurusan Klinik dan Komunitas
        if ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) && str_contains($jabatanNama, 'KLINIK')) {
            return Pegawai::where(function ($q) {
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

        // 3. Ketua Jurusan Preklinik Keperawatan
        if ((str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) && str_contains($jabatanNama, 'PREKLINIK')) {
            return Pegawai::where(function ($q) {
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

        // 4. Koordinator Prodi S1
        if (str_contains($jabatanNama, 'KOORDINATOR PRODI S1') || str_contains($jabatanNama, 'KOORPRODI S1') 
            || (str_contains($jabatanNama, 'KOORDINATOR') && str_contains($jabatanNama, 'S1'))) {
            return Pegawai::where(function ($q) {
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

        // 5. Koordinator Prodi S2
        if (str_contains($jabatanNama, 'KOORDINATOR PRODI S2') || str_contains($jabatanNama, 'KOORPRODI S2') 
            || (str_contains($jabatanNama, 'KOORDINATOR') && str_contains($jabatanNama, 'S2'))) {
            return Pegawai::where(function ($q) {
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

        // 6. Koordinator Prodi Ners
        if (str_contains($jabatanNama, 'KOORDINATOR PRODI NERS') || str_contains($jabatanNama, 'KOORPRODI NERS') 
            || (str_contains($jabatanNama, 'KOORDINATOR') && str_contains($jabatanNama, 'NERS'))) {
            return Pegawai::where(function ($q) {
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

        return null;
    }

    /**
     * Atasan Langsung Cuti untuk Dosen, Koorprodi, dan Kajur
     */
    public function resolveAtasanLangsungCuti(Pegawai $pegawai): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // Ketua Jurusan (Kajur) -> Wadek II memberikan pertimbangan
        if (str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) {
            return $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Wakil Dekan II']);
        }

        // Dosen & Koorprodi -> Kajur memberikan pertimbangan
        if ($pegawai->isDosen() || str_contains($jabatanNama, 'KOORDINATOR') || str_contains($jabatanNama, 'KOORPRODI')) {
            $kajur = $this->findPegawaiByJabatan([
                'Ketua Jurusan (Kajur)',
                'Ketua Jurusan Preklinik Keperawatan',
                'Ketua Jurusan Klinik dan Komunitas',
            ]);

            if ($kajur) {
                return $kajur;
            }

            // Fallback: Jika Kajur kosong, ke Wadek I atau Wadek II
            $wadek1 = $this->findPegawaiByJabatan(['Wakil Dekan I (Bid. Akademik)', 'Wakil Dekan I']);
            if ($wadek1) {
                return $wadek1;
            }

            return $this->findPegawaiByJabatan(['Wakil Dekan II (Bid. Keuangan dan Umum)', 'Wakil Dekan II']);
        }

        return null;
    }

    /**
     * PYBMC Cuti untuk Kajur, Koorprodi, dan Dosen
     */
    public function resolvePybmcCuti(Pegawai $pegawai, ?string $jenisCuti = null): ?Pegawai
    {
        $jabatanNama = strtoupper(trim((string)($pegawai->jabatan->nama_jabatan ?? '')));

        // Pimpinan Struktural Kajur -> PYBMC Dekan
        if (str_contains($jabatanNama, 'KETUA JURUSAN') || str_contains($jabatanNama, 'KAJUR')) {
            return $this->findPegawaiByJabatan(['Dekan']);
        }

        // Dosen & Koorprodi -> PYBMC Wadek II
        return $this->findPegawaiByJabatan([
            'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'Wakil Dekan II',
        ]);
    }
}
