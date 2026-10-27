<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\PengajuanKarir;
use App\Models\UsulanSatyalancana;
use Carbon\Carbon;

class KelayakanKarirService
{
    /**
     * Dapatkan status periode aktif pengusulan KP BKN sesuai Peraturan BKN No. 4/2023
     */
    public function getPeriodeBknStatus(?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now();
        $year = $now->year;

        // 6 Jendela Periode Pengusulan BKN
        // 1. Feb: 15 Des s.d. 15 Jan
        // 2. Apr: 1 Feb s.d. 28/29 Feb
        // 3. Jun: 1 Apr s.d. 30 Apr
        // 4. Ags: 1 Jun s.d. 30 Jun
        // 5. Okt: 1 Ags s.d. 31 Ags
        // 6. Des: 1 Okt s.d. 31 Okt
        $windows = [
            [
                'periode'     => 'Februari',
                'target_tmt'  => "01-02-{$year}",
                'start'       => Carbon::create($year - 1, 12, 15, 0, 0, 0),
                'end'         => Carbon::create($year, 1, 15, 23, 59, 59),
                'label'       => '15 Des ' . ($year - 1) . ' s.d. 15 Jan ' . $year,
            ],
            [
                'periode'     => 'April',
                'target_tmt'  => "01-04-{$year}",
                'start'       => Carbon::create($year, 2, 1, 0, 0, 0),
                'end'         => Carbon::create($year, 2, 1)->endOfMonth(),
                'label'       => '1 s.d. ' . Carbon::create($year, 2, 1)->endOfMonth()->day . ' Feb ' . $year,
            ],
            [
                'periode'     => 'Juni',
                'target_tmt'  => "01-06-{$year}",
                'start'       => Carbon::create($year, 4, 1, 0, 0, 0),
                'end'         => Carbon::create($year, 4, 30, 23, 59, 59),
                'label'       => '1 s.d. 30 Apr ' . $year,
            ],
            [
                'periode'     => 'Agustus',
                'target_tmt'  => "01-08-{$year}",
                'start'       => Carbon::create($year, 6, 1, 0, 0, 0),
                'end'         => Carbon::create($year, 6, 30, 23, 59, 59),
                'label'       => '1 s.d. 30 Jun ' . $year,
            ],
            [
                'periode'     => 'Oktober',
                'target_tmt'  => "01-10-{$year}",
                'start'       => Carbon::create($year, 8, 1, 0, 0, 0),
                'end'         => Carbon::create($year, 8, 31, 23, 59, 59),
                'label'       => '1 s.d. 31 Ags ' . $year,
            ],
            [
                'periode'     => 'Desember',
                'target_tmt'  => "01-12-{$year}",
                'start'       => Carbon::create($year, 10, 1, 0, 0, 0),
                'end'         => Carbon::create($year, 10, 31, 23, 59, 59),
                'label'       => '1 s.d. 31 Okt ' . $year,
            ],
            [
                'periode'     => 'Februari',
                'target_tmt'  => '01-02-' . ($year + 1),
                'start'       => Carbon::create($year, 12, 15, 0, 0, 0),
                'end'         => Carbon::create($year + 1, 1, 15, 23, 59, 59),
                'label'       => '15 Des ' . $year . ' s.d. 15 Jan ' . ($year + 1),
            ],
        ];

        $currentWindow = null;
        foreach ($windows as $w) {
            if ($now->between($w['start'], $w['end'])) {
                $currentWindow = $w;
                break;
            }
        }

        $nextWindow = null;
        if (!$currentWindow) {
            foreach ($windows as $w) {
                if ($now->lt($w['start'])) {
                    $nextWindow = $w;
                    break;
                }
            }
        }

        return [
            'is_open'        => !empty($currentWindow),
            'current_window' => $currentWindow,
            'next_window'    => $nextWindow,
            'active_periode' => $currentWindow['periode'] ?? ($nextWindow['periode'] ?? 'Desember'),
            'label'          => $currentWindow 
                ? "Jendela Pengusulan Periode {$currentWindow['periode']} ({$currentWindow['label']}) Sedang DIBUKA" 
                : "Jendela Pengusulan BKN Saat Ini DITUTUP. Periode berikutnya: Periode " . ($nextWindow['periode'] ?? 'Desember') . " (Dibuka " . ($nextWindow['label'] ?? '-') . ")",
        ];
    }

    /**
     * Evaluasi Kelayakan & Waktu Pengajuan Kenaikan Pangkat (KP)
     */
    public function evaluasiKp(Pegawai $pegawai): array
    {
        $isPns = str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PNS') || 
                 (!str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PPPK') && !str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PHL'));

        $periodeBkn = $this->getPeriodeBknStatus();
        $kpService = app(KpService::class);
        $waktuCukup = $kpService->cekKelayakanKp($pegawai, 6);

        $syaratBerkas = $pegawai->evaluasiSyaratKp();

        $alasan = [];
        if (!$isPns) {
            $alasan[] = 'Sesuai UU ASN No. 20/2023, Kenaikan Pangkat reguler hanya berlaku bagi Pegawai Negeri Sipil (PNS). Pegawai PPPK memiliki hak atas Kenaikan Gaji Berkala (KGB).';
        }
        if (!$waktuCukup) {
            $tglNext = $pegawai->kp_berikutnya ? Carbon::parse($pegawai->kp_berikutnya)->format('d-m-Y') : 'belum tercatat';
            $alasan[] = "Masa kerja dalam pangkat terakhir Anda belum mencapai batas minimal 4 tahun (TMT KP Berikutnya: {$tglNext}).";
        }
        if (!$periodeBkn['is_open']) {
            $alasan[] = "Jadwal jendela pengusulan KP BKN saat ini sedang ditutup. {$periodeBkn['label']}.";
        }

        $usulanAktif = PengajuanKarir::where('pegawai_id', $pegawai->id)
            ->where('jenis_pengajuan', 'KP')
            ->whereNotIn('status', ['disetujui_wd2', 'ditolak'])
            ->latest()
            ->first();

        $canSubmit = $isPns && $waktuCukup && $periodeBkn['is_open'] && empty($usulanAktif);

        return [
            'is_pns'         => $isPns,
            'waktu_cukup'    => $waktuCukup,
            'periode_bkn'    => $periodeBkn,
            'can_submit'     => $canSubmit,
            'alasan_kunci'   => $alasan,
            'syarat_berkas'  => $syaratBerkas,
            'usulan_aktif'   => $usulanAktif,
            'kp_berikutnya'  => $pegawai->kp_berikutnya ? Carbon::parse($pegawai->kp_berikutnya)->format('d-m-Y') : null,
        ];
    }

    /**
     * Evaluasi Kelayakan & Waktu Pengajuan Kenaikan Gaji Berkala (KGB)
     */
    public function evaluasiKgb(Pegawai $pegawai): array
    {
        $isEligibleRole = !str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PHL');

        $kgbService = app(KgbService::class);
        $waktuCukup = $kgbService->cekKelayakanKgb($pegawai, 3);

        $alasan = [];
        if (!$isEligibleRole) {
            $alasan[] = 'Kenaikan Gaji Berkala hanya berlaku bagi ASN (PNS & PPPK).';
        }
        if (!$waktuCukup) {
            if ($pegawai->kgb_berikutnya) {
                $tglNext = Carbon::parse($pegawai->kgb_berikutnya);
                $tglBuka = $tglNext->copy()->subMonths(3)->format('d-m-Y');
                $alasan[] = "Masa kerja KGB belum mencapai 2 tahun. Sesuai regulasi PP No. 7/1977 & PermenPAN-RB No. 7/2023, pengusulan dibuka mulai 3 bulan sebelum jatuh tempo (TMT KGB Berikutnya: {$tglNext->format('d-m-Y')}, Pengusulan Dibuka: {$tglBuka}).";
            } else {
                $alasan[] = 'Tanggal TMT KGB berikutnya belum tercatat di data kepegawaian Anda.';
            }
        }

        $hasSkPangkat = !empty($pegawai->file_sk_pangkat_terakhir) || ($pegawai->riwayatPangkat()->whereNotNull('file_sk')->exists());
        $hasSkKgb = !empty($pegawai->file_sk_kgb_terakhir);
        $skpValid = $pegawai->riwayatSkp()->where(fn($q) => $q->whereNotNull('file_evaluasi_skp')->orWhereNotNull('file_rencana_skp'))->count();
        $hasSkp = $skpValid >= 2;

        $syaratBerkas = [
            'is_lengkap' => $hasSkPangkat && $hasSkKgb && $hasSkp,
            'skor'       => (($hasSkPangkat?1:0)+($hasSkKgb?1:0)+($hasSkp?1:0)) . ' / 3',
            'items'      => [
                [
                    'label'       => 'SK Pangkat Terakhir / SK Pengangkatan',
                    'status'      => $hasSkPangkat,
                    'file_url'    => $pegawai->file_sk_pangkat_terakhir_url,
                    'field'       => 'file_sk_pangkat_terakhir',
                ],
                [
                    'label'       => 'SK KGB Terakhir',
                    'status'      => $hasSkKgb,
                    'file_url'    => $pegawai->file_sk_kgb_terakhir_url,
                    'field'       => 'file_sk_kgb_terakhir',
                ],
                [
                    'label'       => 'Penilaian Kinerja (SKP) 2 Tahun Terakhir (Minimal "Baik")',
                    'status'      => $hasSkp,
                    'file_url'    => null,
                    'field'       => 'riwayat_skp',
                    'count'       => $skpValid,
                ],
            ]
        ];

        $usulanAktif = PengajuanKarir::where('pegawai_id', $pegawai->id)
            ->where('jenis_pengajuan', 'KGB')
            ->whereNotIn('status', ['disetujui_wd2', 'ditolak'])
            ->latest()
            ->first();

        $canSubmit = $isEligibleRole && $waktuCukup && empty($usulanAktif);

        return [
            'is_eligible_role' => $isEligibleRole,
            'waktu_cukup'      => $waktuCukup,
            'can_submit'       => $canSubmit,
            'alasan_kunci'     => $alasan,
            'syarat_berkas'    => $syaratBerkas,
            'usulan_aktif'     => $usulanAktif,
            'kgb_berikutnya'   => $pegawai->kgb_berikutnya ? Carbon::parse($pegawai->kgb_berikutnya)->format('d-m-Y') : null,
        ];
    }

    /**
     * Evaluasi Kelayakan & Waktu Pengajuan Satyalancana Karya Satya
     */
    public function evaluasiSatyalancana(Pegawai $pegawai): array
    {
        $isPns = str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PNS') || 
                 (!str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PPPK') && !str_contains(strtoupper($pegawai->jenis_pegawai ?? ''), 'PHL'));

        $tanggalAcuan = $pegawai->tmt_sk_pertama ?? $pegawai->tanggal_masuk;
        $masaKerjaTahun = 0;
        $masaKerjaBulan = 0;

        if ($tanggalAcuan) {
            $tgl = Carbon::parse($tanggalAcuan);
            $diff = $tgl->diff(Carbon::now());
            $masaKerjaTahun = $diff->y;
            $masaKerjaBulan = $diff->m;
        }

        $satyalancanaService = app(SatyalancanaService::class);
        $kategoriLayak = $isPns ? $satyalancanaService->cekKelayakanSatyalancana($pegawai) : null;

        $alasan = [];
        if (!$isPns) {
            $alasan[] = 'Sesuai UU No. 20 Tahun 2009 dan PP No. 35 Tahun 2010, Tanda Kehormatan Satyalancana Karya Satya diperuntukkan bagi Pegawai Negeri Sipil (PNS).';
        } elseif (!$kategoriLayak) {
            if ($masaKerjaTahun < 10) {
                $alasan[] = "Masa kerja pengabdian Anda saat ini baru {$masaKerjaTahun} Tahun {$masaKerjaBulan} Bulan. Belum memenuhi syarat minimal 10 Tahun pengabdian.";
            } else {
                $alasan[] = 'Anda belum memenuhi syarat untuk kenaikan tingkat Satyalancana berikutnya atau tanda kehormatan periode ini telah diterima.';
            }
        }

        $tahunIni = (int) date('Y');
        $usulanAktif = UsulanSatyalancana::where('pegawai_id', $pegawai->id)
            ->where('tahun_usulan', $tahunIni)
            ->first();

        $canSubmit = $isPns && !empty($kategoriLayak) && empty($usulanAktif);

        $hasSkCpns = !empty($pegawai->file_sk_pertama);
        $hasSkPangkat = !empty($pegawai->file_sk_pangkat_terakhir) || ($pegawai->riwayatPangkat()->whereNotNull('file_sk')->exists());
        $hasSkJabatan = $pegawai->riwayatJabatan()->whereNotNull('file_sk')->exists();
        $skpValid = $pegawai->riwayatSkp()->where(fn($q) => $q->whereNotNull('file_evaluasi_skp')->orWhereNotNull('file_rencana_skp'))->count();
        $hasSkp = $skpValid >= 2;
        $hasKarpeg = !empty($pegawai->file_karpeg);

        $syaratBerkas = [
            'is_lengkap' => $hasSkCpns && $hasSkPangkat && $hasSkJabatan && $hasSkp && $hasKarpeg,
            'skor'       => (($hasSkCpns?1:0)+($hasSkPangkat?1:0)+($hasSkJabatan?1:0)+($hasSkp?1:0)+($hasKarpeg?1:0)) . ' / 5',
            'items'      => [
                [
                    'label'    => 'SK Pengangkatan Pertama / CPNS',
                    'status'   => $hasSkCpns,
                    'file_url' => $pegawai->file_sk_pertama_url,
                    'field'    => 'file_sk_pertama',
                ],
                [
                    'label'    => 'SK Pangkat Terakhir',
                    'status'   => $hasSkPangkat,
                    'file_url' => $pegawai->file_sk_pangkat_terakhir_url,
                    'field'    => 'file_sk_pangkat_terakhir',
                ],
                [
                    'label'    => 'SK Jabatan Terakhir',
                    'status'   => $hasSkJabatan,
                    'file_url' => null,
                    'field'    => 'riwayat_jabatan',
                ],
                [
                    'label'    => 'Penilaian Kinerja (SKP) 2 Tahun Terakhir',
                    'status'   => $hasSkp,
                    'file_url' => null,
                    'field'    => 'riwayat_skp',
                    'count'    => $skpValid,
                ],
                [
                    'label'    => 'Fotocopy Karpeg / Identitas ASN',
                    'status'   => $hasKarpeg,
                    'file_url' => $pegawai->file_karpeg_url,
                    'field'    => 'file_karpeg',
                ],
            ]
        ];

        return [
            'is_pns'           => $isPns,
            'masa_kerja_tahun' => $masaKerjaTahun,
            'masa_kerja_bulan' => $masaKerjaBulan,
            'kategori_layak'   => $kategoriLayak,
            'can_submit'       => $canSubmit,
            'alasan_kunci'     => $alasan,
            'syarat_berkas'    => $syaratBerkas,
            'usulan_aktif'     => $usulanAktif,
        ];
    }
}
