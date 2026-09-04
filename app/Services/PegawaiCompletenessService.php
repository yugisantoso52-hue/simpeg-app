<?php

namespace App\Services;

use App\Models\Pegawai;
use Illuminate\Support\Collection;

class PegawaiCompletenessService
{
    /**
     * Menghitung Persentase Kelengkapan Data Profil Pegawai (0% - 100%)
     */
    public static function calculate(?Pegawai $p): array
    {
        if (!$p) {
            return [
                'score'         => 0,
                'status_label'  => 'Belum Ada Data',
                'badge_color'   => 'bg-rose-100 text-rose-800 border-rose-200',
                'progress_color'=> 'bg-rose-500',
                'missing_items' => [],
                'completed_count' => 0,
                'total_count'   => 15,
            ];
        }

        $items = [];
        $earnedPoints = 0;
        $totalPoints = 100;

        // ==========================================
        // KATEGORI 1: IDENTITAS & KONTAK (BOBOT: 20%)
        // ==========================================
        $hasFoto = !empty($p->foto);
        $items[] = [
            'category' => 'Identitas & Kontak',
            'label'    => 'Foto Profil Resmi Pegawai',
            'points'   => 5,
            'is_complete' => $hasFoto,
            'action_label' => 'Upload Foto',
            'action_url'   => route('pegawai.edit', ['pegawai' => $p->id, 'tab' => 'foto']),
        ];
        if ($hasFoto) $earnedPoints += 5;

        $hasNip = !empty($p->nip) && $p->nip !== '-';
        $items[] = [
            'category' => 'Identitas & Kontak',
            'label'    => 'Nomor NIP / NIDN / NUPTK / ID',
            'points'   => 5,
            'is_complete' => $hasNip,
            'action_label' => 'Isi Identitas',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasNip) $earnedPoints += 5;

        $hasTTL = !empty($p->tempat_lahir) && !empty($p->tanggal_lahir);
        $items[] = [
            'category' => 'Identitas & Kontak',
            'label'    => 'Tempat & Tanggal Lahir',
            'points'   => 5,
            'is_complete' => $hasTTL,
            'action_label' => 'Isi TTL',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasTTL) $earnedPoints += 5;

        $hasKontak = !empty($p->no_hp) && !empty($p->email);
        $items[] = [
            'category' => 'Identitas & Kontak',
            'label'    => 'Nomor HP & Alamat Email',
            'points'   => 5,
            'is_complete' => $hasKontak,
            'action_label' => 'Isi Kontak',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasKontak) $earnedPoints += 5;

        // ==========================================
        // KATEGORI 2: KEPEGAWAIAN UTAMA (BOBOT: 20%)
        // ==========================================
        $hasJabatan = !empty($p->jabatan_id);
        $items[] = [
            'category' => 'Kepegawaian',
            'label'    => 'Jabatan Terkini',
            'points'   => 8,
            'is_complete' => $hasJabatan,
            'action_label' => 'Pilih Jabatan',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasJabatan) $earnedPoints += 8;

        $hasUnit = !empty($p->unit_kerja_id);
        $items[] = [
            'category' => 'Kepegawaian',
            'label'    => 'Unit Kerja',
            'points'   => 6,
            'is_complete' => $hasUnit,
            'action_label' => 'Pilih Unit Kerja',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasUnit) $earnedPoints += 6;

        $hasGolongan = !empty($p->golongan_id) || str_contains(strtolower((string)$p->jenis_pegawai), 'phl');
        $items[] = [
            'category' => 'Kepegawaian',
            'label'    => 'Pangkat / Golongan',
            'points'   => 6,
            'is_complete' => $hasGolongan,
            'action_label' => 'Set Golongan',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasGolongan) $earnedPoints += 6;

        // ==========================================
        // KATEGORI 3: DOKUMEN ARSIP & SK (BOBOT: 30%)
        // ==========================================
        $hasSkPertama = !empty($p->file_sk_pertama);
        $items[] = [
            'category' => 'Berkas Digital',
            'label'    => 'Upload PDF SK Pertama (CPNS/Kontrak)',
            'points'   => 6,
            'is_complete' => $hasSkPertama,
            'action_label' => 'Upload SK Pertama',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasSkPertama) $earnedPoints += 6;

        $hasSkPangkat = !empty($p->file_sk_pangkat_terakhir) || str_contains(strtolower((string)$p->jenis_pegawai), 'phl');
        $items[] = [
            'category' => 'Berkas Digital',
            'label'    => 'Upload PDF SK Pangkat Terakhir',
            'points'   => 6,
            'is_complete' => $hasSkPangkat,
            'action_label' => 'Upload SK Pangkat',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasSkPangkat) $earnedPoints += 6;

        $hasSkKgb = !empty($p->file_sk_kgb_terakhir) || str_contains(strtolower((string)$p->jenis_pegawai), 'phl');
        $items[] = [
            'category' => 'Berkas Digital',
            'label'    => 'Upload PDF SK KGB Terakhir',
            'points'   => 6,
            'is_complete' => $hasSkKgb,
            'action_label' => 'Upload SK KGB',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasSkKgb) $earnedPoints += 6;

        $hasKarpeg = !empty($p->file_karpeg);
        $items[] = [
            'category' => 'Berkas Digital',
            'label'    => 'Upload Fotocopy / Scan KARPEG',
            'points'   => 6,
            'is_complete' => $hasKarpeg,
            'action_label' => 'Upload KARPEG',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasKarpeg) $earnedPoints += 6;

        $hasPak = !empty($p->file_pak) || !str_contains(strtolower((string)$p->jenis_pegawai), 'dosen');
        $items[] = [
            'category' => 'Berkas Digital',
            'label'    => 'Upload PDF Dokumen SK PAK / Dupak',
            'points'   => 6,
            'is_complete' => $hasPak,
            'action_label' => 'Upload SK PAK',
            'action_url'   => route('pegawai.edit', $p->id),
        ];
        if ($hasPak) $earnedPoints += 6;

        // ==========================================
        // KATEGORI 4: RIWAYAT KARIR (BOBOT: 15%)
        // ==========================================
        $hasPendidikan = isset($p->riwayatPendidikan) ? $p->riwayatPendidikan->count() > 0 : false;
        $items[] = [
            'category' => 'Riwayat Karir',
            'label'    => 'Minimal 1 Riwayat Pendidikan',
            'points'   => 10,
            'is_complete' => $hasPendidikan,
            'action_label' => '+ Riwayat Pendidikan',
            'action_url'   => route('riwayat-pendidikan.index'),
        ];
        if ($hasPendidikan) $earnedPoints += 10;

        $hasDiklat = isset($p->riwayatDiklat) ? $p->riwayatDiklat->count() > 0 : false;
        $items[] = [
            'category' => 'Riwayat Karir',
            'label'    => 'Minimal 1 Riwayat Diklat / Pelatihan',
            'points'   => 5,
            'is_complete' => $hasDiklat,
            'action_label' => '+ Riwayat Diklat',
            'action_url'   => route('riwayat-diklat.index'),
        ];
        if ($hasDiklat) $earnedPoints += 5;

        // ==========================================
        // KATEGORI 5: RIWAYAT SKP & LEGALITAS (BOBOT: 15%)
        // ==========================================
        $hasSkp = isset($p->riwayatSkp) ? $p->riwayatSkp->count() > 0 : false;
        $items[] = [
            'category' => 'Kinerja & SKP',
            'label'    => 'Minimal 1 Riwayat Nilai SKP',
            'points'   => 10,
            'is_complete' => $hasSkp,
            'action_label' => '+ Riwayat SKP',
            'action_url'   => route('riwayat-skp.index'),
        ];
        if ($hasSkp) $earnedPoints += 10;

        $hasStr = isset($p->riwayatStrSip) ? $p->riwayatStrSip->count() > 0 : false;
        $isDosenOrKlinis = str_contains(strtolower((string)$p->jenis_pegawai), 'dosen') || str_contains(strtolower((string)$p->jabatan?->nama_jabatan), 'perawat');
        $hasStrOrNotNeeded = $hasStr || !$isDosenOrKlinis;
        $items[] = [
            'category' => 'Kinerja & SKP',
            'label'    => 'Dokumen STR / SIP Legalitas Profesi',
            'points'   => 5,
            'is_complete' => $hasStrOrNotNeeded,
            'action_label' => '+ STR/SIP',
            'action_url'   => route('riwayat-str-sip.index'),
        ];
        if ($hasStrOrNotNeeded) $earnedPoints += 5;

        $score = min(100, max(0, $earnedPoints));

        // Tentukan warna & status
        if ($score >= 100) {
            $statusLabel  = '100% Lengkap & Terverifikasi 💎';
            $badgeColor   = 'bg-emerald-100 text-emerald-800 border-emerald-300';
            $progressColor= 'bg-emerald-500';
        } elseif ($score >= 80) {
            $statusLabel  = 'Sangat Baik (Hampir Sempurna) 🟢';
            $badgeColor   = 'bg-blue-100 text-blue-800 border-blue-300';
            $progressColor= 'bg-blue-600';
        } elseif ($score >= 50) {
            $statusLabel  = 'Cukup Lengkap (Perlu Dilengkapi) 🟡';
            $badgeColor   = 'bg-amber-100 text-amber-800 border-amber-300';
            $progressColor= 'bg-amber-500';
        } else {
            $statusLabel  = 'Kurang Lengkap (Harap Segera Lengkapi) 🔴';
            $badgeColor   = 'bg-rose-100 text-rose-800 border-rose-300';
            $progressColor= 'bg-rose-500';
        }

        $missingItems = array_values(array_filter($items, fn($i) => !$i['is_complete']));
        $completedCount = count(array_filter($items, fn($i) => $i['is_complete']));

        return [
            'score'           => $score,
            'status_label'    => $statusLabel,
            'badge_color'     => $badgeColor,
            'progress_color'  => $progressColor,
            'all_items'       => $items,
            'missing_items'   => $missingItems,
            'completed_count' => $completedCount,
            'total_count'     => count($items),
        ];
    }

    /**
     * Mengambil Ringkasan Statistik Kelengkapan Data Fakultas (Khusus Admin/Pimpinan)
     */
    public static function getFacultyCompleteness(): array
    {
        $pegawaiList = Pegawai::with(['unitKerja', 'jabatan', 'golongan', 'riwayatPendidikan', 'riwayatDiklat', 'riwayatSkp', 'riwayatStrSip'])->get();

        if ($pegawaiList->isEmpty()) {
            return [
                'average_score'   => 0,
                'total_complete'  => 0,
                'total_moderate'  => 0,
                'total_low'       => 0,
                'pegawai_scores'  => collect(),
            ];
        }

        $totalScore = 0;
        $totalComplete = 0;
        $totalModerate = 0;
        $totalLow = 0;

        $scores = $pegawaiList->map(function ($p) use (&$totalScore, &$totalComplete, &$totalModerate, &$totalLow) {
            $data = self::calculate($p);
            $score = $data['score'];
            $totalScore += $score;

            if ($score >= 100) {
                $totalComplete++;
            } elseif ($score >= 50) {
                $totalModerate++;
            } else {
                $totalLow++;
            }

            return [
                'pegawai'       => $p,
                'score'         => $score,
                'status_label'  => $data['status_label'],
                'badge_color'   => $data['badge_color'],
                'progress_color'=> $data['progress_color'],
                'missing_count' => count($data['missing_items']),
                'missing_items' => $data['missing_items'],
            ];
        })->sortBy('score');

        $averageScore = round($totalScore / $pegawaiList->count(), 1);

        return [
            'average_score'  => $averageScore,
            'total_complete' => $totalComplete,
            'total_moderate' => $totalModerate,
            'total_low'      => $totalLow,
            'pegawai_scores' => $scores,
        ];
    }
}
