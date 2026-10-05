<?php

namespace App\Services;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\TalentMapping;
use App\Models\SuccessionPlan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SuccessionMatchingService
{
    /**
     * Cari dan rangking kandidat suksesi terbaik untuk jabatan target tertentu
     */
    public function findCandidatePool(Jabatan $jabatanTarget, ?int $year = null): Collection
    {
        $year = $year ?? (int) date('Y');

        // Ambil ID pegawai yang saat ini sedang menduduki jabatan target (inkumben) agar tidak dinominasikan ke dirinya sendiri
        $incumbentIds = $jabatanTarget->pegawai()
            ->where('status_pegawai', 'Aktif')
            ->pluck('id')
            ->toArray();

        // Ambil data pemetaan talenta ASN pada tahun tersebut
        // Prioritas kandidat suksesi: Kotak 9, 8, 7, 6, 5
        $talentMappings = TalentMapping::with([
            'pegawai.golongan',
            'pegawai.unitKerja',
            'pegawai.jabatan',
            'pegawai.riwayatDiklat',
            'pegawai.riwayatPenghargaan'
        ])
        ->where('tahun', $year)
        ->whereNotIn('pegawai_id', $incumbentIds)
        ->whereIn('kuadran_box', [9, 8, 7, 6, 5, 4])
        ->orderByRaw('CASE WHEN kuadran_box = 9 THEN 1 WHEN kuadran_box = 8 THEN 2 WHEN kuadran_box = 7 THEN 3 WHEN kuadran_box = 6 THEN 4 ELSE 5 END')
        ->orderBy('sumbu_kinerja_nilai', 'desc')
        ->get();

        $anjab = $jabatanTarget->analisisJabatan;

        $results = collect();

        foreach ($talentMappings as $tm) {
            $pegawai = $tm->pegawai;
            if (!$pegawai || $pegawai->status_pegawai !== 'Aktif') {
                continue;
            }

            $scoreDetail = $this->calculateMatchScore($pegawai, $tm, $jabatanTarget, $anjab);

            $results->push((object) [
                'pegawai'          => $pegawai,
                'mapping'          => $tm,
                'match_score'      => $scoreDetail['total_score'],
                'score_breakdown'  => $scoreDetail['breakdown'],
                'gap_analysis'     => $scoreDetail['gap_items'],
                'status_kesiapan'  => $scoreDetail['status_kesiapan'],
                'kuadran_box'      => $tm->kuadran_box,
                'is_current_plan'  => SuccessionPlan::where('jabatan_target_id', $jabatanTarget->id)
                                        ->where('pegawai_id', $pegawai->id)
                                        ->where('tahun', $year)
                                        ->exists(),
            ]);
        }

        // Urutkan berdasarkan Match Score tertinggi
        return $results->sortByDesc('match_score')->values();
    }

    /**
     * Hitung Indeks Kecocokan Kompetensi (Competency & Qualification Fit Index)
     */
    public function calculateMatchScore(Pegawai $pegawai, TalentMapping $mapping, Jabatan $target, $anjab = null): array
    {
        $gapItems = [];

        // 1. SKOR KINERJA & POTENSI TALENTA (Bobot 35%)
        // Kotak 9 = 100, Kotak 8 = 90, Kotak 7/6 = 78, Kotak 5 = 70, Kotak 4 = 60
        $talentBaseScore = match ($mapping->kuadran_box) {
            9       => 100.00,
            8       => 90.00,
            7, 6    => 78.00,
            5       => 70.00,
            default => 60.00,
        };
        $weightedTalent = round($talentBaseScore * 0.35, 2);

        $gapItems[] = [
            'aspek'   => 'Kotak Talenta (9-Box Grid)',
            'status'  => in_array($mapping->kuadran_box, [9, 8]) ? 'Memenuhi' : 'Perlu Peningkatan',
            'detail'  => "Berada di {$mapping->box_name} dengan skor Kinerja {$mapping->sumbu_kinerja_nilai} dan Potensi {$mapping->sumbu_potensi_nilai}.",
            'is_pass' => in_array($mapping->kuadran_box, [9, 8]),
        ];

        // 2. KECOCOKAN KUALIFIKASI PENDIDIKAN (Bobot 25%)
        $syaratPendidikan = strtoupper($anjab?->kualifikasi_pendidikan ?? '');
        $pendidikanPegawai = strtoupper($pegawai->pendidikan_terakhir ?? '');

        $scorePendidikan = 70.00;
        $isPassPendidikan = true;
        $detailPendidikan = "Pendidikan terakhir: {$pegawai->pendidikan_terakhir}.";

        if (str_contains($syaratPendidikan, 'S3') || str_contains($syaratPendidikan, 'DOKTOR')) {
            if (str_contains($pendidikanPegawai, 'S3') || str_contains($pendidikanPegawai, 'DOKTOR')) {
                $scorePendidikan = 100.00;
                $detailPendidikan .= " Memenuhi syarat kualifikasi Doktor/S3.";
            } else {
                $scorePendidikan = 60.00;
                $isPassPendidikan = false;
                $detailPendidikan .= " Syarat jabatan menghendaki S3/Doktor. Disarankan Tugas Belajar S3.";
            }
        } elseif (str_contains($syaratPendidikan, 'S2') || str_contains($syaratPendidikan, 'MAGISTER')) {
            if (str_contains($pendidikanPegawai, 'S3') || str_contains($pendidikanPegawai, 'DOKTOR')) {
                $scorePendidikan = 100.00;
                $detailPendidikan .= " Melebihi syarat kualifikasi (Memiliki gelar S3).";
            } elseif (str_contains($pendidikanPegawai, 'S2') || str_contains($pendidikanPegawai, 'MAGISTER')) {
                $scorePendidikan = 100.00;
                $detailPendidikan .= " Memenuhi syarat kualifikasi Magister/S2.";
            } else {
                $scorePendidikan = 65.00;
                $isPassPendidikan = false;
                $detailPendidikan .= " Syarat jabatan menghendaki S2. Disarankan Tugas Belajar S2.";
            }
        } else {
            // Syarat S1 / D4 / umum
            if (str_contains($pendidikanPegawai, 'S3') || str_contains($pendidikanPegawai, 'S2') || str_contains($pendidikanPegawai, 'S1')) {
                $scorePendidikan = 100.00;
                $detailPendidikan .= " Memenuhi standar kualifikasi pendidikan.";
            }
        }
        $weightedPendidikan = round($scorePendidikan * 0.25, 2);

        $gapItems[] = [
            'aspek'   => 'Kualifikasi Pendidikan',
            'status'  => $isPassPendidikan ? 'Memenuhi' : 'Kesenjangan (Gap)',
            'detail'  => $detailPendidikan,
            'is_pass' => $isPassPendidikan,
        ];

        // 3. KECOCOKAN DIKLAT & KOMPETENSI (Bobot 20%)
        $syaratPelatihan = strtoupper($anjab?->kualifikasi_pelatihan ?? '');
        $scoreDiklat = 70.00;
        $isPassDiklat = true;
        $detailDiklat = "Pemenuhan jam diklat & pelatihan teknis.";

        $riwayatDiklatText = strtoupper($pegawai->riwayatDiklat->pluck('nama_diklat')->implode(' '));

        if (str_contains($syaratPelatihan, 'PIM') || str_contains($syaratPelatihan, 'PKA') || str_contains($syaratPelatihan, 'PKP') || str_contains($syaratPelatihan, 'KEPEMIMPINAN')) {
            if (str_contains($riwayatDiklatText, 'PKA') || str_contains($riwayatDiklatText, 'PKP') || str_contains($riwayatDiklatText, 'KEPEMIMPINAN') || str_contains($riwayatDiklatText, 'PIM')) {
                $scoreDiklat = 100.00;
                $detailDiklat = "Telah lulus Pelatihan Kepemimpinan Struktural (PKA/PKP/PIM).";
            } else {
                $scoreDiklat = 65.00;
                $isPassDiklat = false;
                $detailDiklat = "Belum memiliki sertifikasi Pelatihan Kepemimpinan struktural yang disyaratkan.";
            }
        } else {
            // Cek pemenuhan 20 JP
            $totalJP = (int) $pegawai->riwayatDiklat->sum('jumlah_jam');
            if ($totalJP >= 20) {
                $scoreDiklat = 100.00;
                $detailDiklat = "Memenuhi hak minimal 20 JP pengembangan kompetensi (Total: {$totalJP} JP).";
            } else {
                $scoreDiklat = 75.00;
                $detailDiklat = "Akumulasi jam diklat masih di bawah 20 JP (Total: {$totalJP} JP).";
            }
        }
        $weightedDiklat = round($scoreDiklat * 0.20, 2);

        $gapItems[] = [
            'aspek'   => 'Pelatihan & Kompetensi',
            'status'  => $isPassDiklat ? 'Memenuhi' : 'Kesenjangan (Gap)',
            'detail'  => $detailDiklat,
            'is_pass' => $isPassDiklat,
        ];

        // 4. KECOCOKAN PANGKAT/GOLONGAN & KELAS JABATAN (Bobot 20%)
        $kelasJabatanTarget = (int) ($target->kelas_jabatan ?? 9);
        $kodeGolongan = $pegawai->golongan?->kode ?? '';

        $scorePangkat = 75.00;
        $isPassPangkat = true;
        $detailPangkat = "Golongan saat ini: {$kodeGolongan}.";

        // Pemetaan standar kelas jabatan ke golongan ruang minimal:
        // Kelas 12-14 (Eselon II/Dekan/Guru Besar): Gol IV
        // Kelas 10-11 (Eselon III/Kabag/Lektor Kepala): Gol III/d atau IV/a
        // Kelas 8-9 (Eselon IV/Subkoor/Lektor): Gol III/c
        if ($kelasJabatanTarget >= 12) {
            if (str_starts_with($kodeGolongan, 'IV')) {
                $scorePangkat = 100.00;
                $detailPangkat .= " Memenuhi golongan minimal Pembina (Golongan IV) untuk Kelas Jabatan {$kelasJabatanTarget}.";
            } else {
                $scorePangkat = 60.00;
                $isPassPangkat = false;
                $detailPangkat .= " Belum mencapai Golongan IV yang dipersyaratkan untuk Kelas Jabatan {$kelasJabatanTarget}.";
            }
        } elseif ($kelasJabatanTarget >= 10) {
            if (str_starts_with($kodeGolongan, 'IV') || in_array($kodeGolongan, ['III/d', 'III/c'])) {
                $scorePangkat = 100.00;
                $detailPangkat .= " Memenuhi syarat kepangkatan untuk Kelas Jabatan {$kelasJabatanTarget}.";
            } else {
                $scorePangkat = 70.00;
                $isPassPangkat = false;
                $detailPangkat .= " Pangkat masih di bawah standar minimal untuk Kelas Jabatan {$kelasJabatanTarget}.";
            }
        } else {
            $scorePangkat = 90.00;
            $detailPangkat .= " Memenuhi syarat kepangkatan.";
        }
        $weightedPangkat = round($scorePangkat * 0.20, 2);

        $gapItems[] = [
            'aspek'   => 'Pangkat & Kelas Jabatan',
            'status'  => $isPassPangkat ? 'Memenuhi' : 'Kesenjangan (Gap)',
            'detail'  => $detailPangkat,
            'is_pass' => $isPassPangkat,
        ];

        // TOTAL SKOR AKHIR KECOCOKAN (0 - 100%)
        $totalScore = round($weightedTalent + $weightedPendidikan + $weightedDiklat + $weightedPangkat, 1);
        $totalScore = min(100.0, max(0.0, $totalScore));

        // STATUS KESIAPAN SUKSESI (PermenPAN-RB No. 3/2020)
        $statusKesiapan = match (true) {
            $totalScore >= 85.0 && in_array($mapping->kuadran_box, [9, 8]) => 'Siap Sekarang',
            $totalScore >= 72.0                                             => 'Siap 1-2 Tahun',
            default                                                         => 'Potensial Jangka Panjang',
        };

        return [
            'total_score'     => $totalScore,
            'status_kesiapan' => $statusKesiapan,
            'breakdown'       => [
                'talent'     => ['score' => $talentBaseScore, 'weighted' => $weightedTalent],
                'pendidikan' => ['score' => $scorePendidikan, 'weighted' => $weightedPendidikan],
                'diklat'     => ['score' => $scoreDiklat, 'weighted' => $weightedDiklat],
                'pangkat'    => ['score' => $scorePangkat, 'weighted' => $weightedPangkat],
            ],
            'gap_items'       => $gapItems,
        ];
    }
}
