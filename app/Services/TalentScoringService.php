<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\TalentMapping;
use App\Models\TalentAssessment;
use App\Models\RiwayatSkp;
use App\Models\RiwayatDiklat;
use App\Models\Attendance;
use App\Models\Logbook;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TalentScoringService
{
    /**
     * Hitung dan simpan snapshot evaluasi talenta seorang pegawai untuk tahun tertentu.
     */
    public function calculatePegawaiTalent(Pegawai $pegawai, ?int $year = null): TalentMapping
    {
        $year = $year ?? (int) date('Y');

        // 1. Hitung Sumbu Kinerja (X)
        $kinerja = $this->calculateSumbuKinerja($pegawai, $year);

        // 2. Hitung Sumbu Potensi (Y)
        $potensi = $this->calculateSumbuPotensi($pegawai, $year);

        // 3. Tentukan Kotak 9-Box Grid PermenPAN-RB No. 3/2020
        $boxData = $this->determineNineBox($kinerja['kategori'], $potensi['kategori']);

        // 4. Simpan atau perbarui data snapshot TalentMapping
        $mapping = TalentMapping::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'tahun'      => $year,
                'periode'    => 'Tahunan',
            ],
            [
                'jabatan_saat_ini_id'         => $pegawai->jabatan_id,
                'unit_kerja_saat_ini_id'      => $pegawai->unit_kerja_id,
                // Sumbu Kinerja
                'skor_skp_n'                  => $kinerja['skor_skp_n'],
                'predikat_skp_n'              => $kinerja['predikat_skp_n'],
                'skor_skp_n_minus_1'          => $kinerja['skor_skp_n_minus_1'],
                'predikat_skp_n_minus_1'      => $kinerja['predikat_skp_n_minus_1'],
                'skor_kinerja_skp'            => $kinerja['skor_kinerja_skp'],
                'skor_disiplin_kehadiran'     => $kinerja['skor_disiplin'],
                'skor_aktivitas_logbook'      => $kinerja['skor_logbook'],
                'sumbu_kinerja_nilai'         => $kinerja['nilai_akhir'],
                'sumbu_kinerja_kategori'      => $kinerja['kategori'],
                // Sumbu Potensi
                'skor_kualifikasi_pendidikan' => $potensi['skor_pendidikan'],
                'skor_pengembangan_kompetensi'=> $potensi['skor_diklat'],
                'skor_rekam_jejak'            => $potensi['skor_rekam_jejak'],
                'skor_asesmen_kompetensi'     => $potensi['skor_asesmen'],
                'sumbu_potensi_nilai'         => $potensi['nilai_akhir'],
                'sumbu_potensi_kategori'      => $potensi['kategori'],
                // Matriks 9-Kotak
                'kuadran_box'                 => $boxData['box_number'],
                'box_name'                    => $boxData['box_name'],
                'status_talenta'              => $boxData['status_talenta'],
                'rekomendasi_kebijakan'       => $boxData['rekomendasi'],
                'is_suksesi_eligible'         => $boxData['is_suksesi_eligible'],
            ]
        );

        return $mapping;
    }

    /**
     * Hitung Sumbu Kinerja (X) berdasarkan PermenPAN-RB No. 6/2022 & data penunjang
     */
    public function calculateSumbuKinerja(Pegawai $pegawai, int $year): array
    {
        // Ambil SKP Tahun N dan Tahun N-1
        $skpN = $pegawai->riwayatSkp()->where('tahun', $year)->first();
        $skpNMinus1 = $pegawai->riwayatSkp()->where('tahun', $year - 1)->first();

        $valSkpN = $this->convertSkpScore($skpN);
        $valSkpNMinus1 = $this->convertSkpScore($skpNMinus1);

        // Agregasi Nilai SKP (60% Thn N + 40% Thn N-1 jika ada keduanya, atau 100% jika hanya ada satu)
        if ($valSkpN !== null && $valSkpNMinus1 !== null) {
            $skorSkp = round(($valSkpN * 0.60) + ($valSkpNMinus1 * 0.40), 2);
        } elseif ($valSkpN !== null) {
            $skorSkp = $valSkpN;
        } elseif ($valSkpNMinus1 !== null) {
            $skorSkp = $valSkpNMinus1;
        } else {
            // Default moderat jika belum ada rekam SKP
            $skorSkp = 75.00;
        }

        // Hitung Indikator Disiplin Kehadiran (skor 0-100)
        $skorDisiplin = $this->calculateDisiplinKehadiran($pegawai, $year);

        // Hitung Indikator Logbook Kinerja Harian (skor 0-100)
        $skorLogbook = $this->calculateKeaktifanLogbook($pegawai, $year);

        // Komposit Nilai Sumbu Kinerja:
        // Jika data kehadiran/logbook ada: SKP 80%, Presensi 10%, Logbook 10%
        // Jika belum ada data presensi/logbook: SKP 100%
        if ($skorDisiplin !== null && $skorLogbook !== null) {
            $nilaiAkhirKinerja = round(($skorSkp * 0.80) + ($skorDisiplin * 0.10) + ($skorLogbook * 0.10), 2);
        } elseif ($skorDisiplin !== null) {
            $nilaiAkhirKinerja = round(($skorSkp * 0.85) + ($skorDisiplin * 0.15), 2);
        } else {
            $nilaiAkhirKinerja = $skorSkp;
        }

        // Kategori Kinerja PermenPAN-RB No. 3/2020:
        // Tinggi: >= 90.00, Sedang: 75.00 - 89.99, Rendah: < 75.00
        $kategori = match (true) {
            $nilaiAkhirKinerja >= 90.00 => 'Tinggi',
            $nilaiAkhirKinerja >= 75.00 => 'Sedang',
            default                     => 'Rendah',
        };

        return [
            'skor_skp_n'             => $valSkpN,
            'predikat_skp_n'         => $skpN?->predikat_kinerja ?? '-',
            'skor_skp_n_minus_1'     => $valSkpNMinus1,
            'predikat_skp_n_minus_1' => $skpNMinus1?->predikat_kinerja ?? '-',
            'skor_kinerja_skp'       => $skorSkp,
            'skor_disiplin'          => $skorDisiplin,
            'skor_logbook'           => $skorLogbook,
            'nilai_akhir'            => min(100.00, max(0.00, $nilaiAkhirKinerja)),
            'kategori'               => $kategori,
        ];
    }

    /**
     * Hitung Sumbu Potensi (Y) berdasarkan PermenPAN-RB No. 3/2020 & PP 11/2017
     */
    public function calculateSumbuPotensi(Pegawai $pegawai, int $year): array
    {
        // 1. Kualifikasi Pendidikan (Standar Indeks Profesionalitas ASN BKN)
        $pendidikanTerakhir = strtoupper($pegawai->pendidikan_terakhir ?? '');
        $skorPendidikan = match (true) {
            str_contains($pendidikanTerakhir, 'S3') || str_contains($pendidikanTerakhir, 'DOKTOR')   => 100.00,
            str_contains($pendidikanTerakhir, 'S2') || str_contains($pendidikanTerakhir, 'MAGISTER') => 85.00,
            str_contains($pendidikanTerakhir, 'S1') || str_contains($pendidikanTerakhir, 'D4')       => 75.00,
            str_contains($pendidikanTerakhir, 'D3')                                                  => 60.00,
            str_contains($pendidikanTerakhir, 'D2') || str_contains($pendidikanTerakhir, 'D1')       => 50.00,
            str_contains($pendidikanTerakhir, 'SMA') || str_contains($pendidikanTerakhir, 'SMK')     => 40.00,
            default                                                                                  => 70.00,
        };

        // 2. Pengembangan Kompetensi (PP 11/2017 jo PP 17/2020: Hak 20 JP per tahun)
        // Hitung total jam diklat dalam 2 tahun terakhir
        $totalJP = (int) $pegawai->riwayatDiklat()
            ->where(function ($q) use ($year) {
                $q->whereYear('tanggal_selesai', $year)
                  ->orWhereYear('tanggal_mulai', $year)
                  ->orWhereYear('tanggal_sertifikat', $year)
                  ->orWhereYear('tanggal_selesai', $year - 1);
            })
            ->sum('jumlah_jam');

        $skorDiklat = match (true) {
            $totalJP >= 20 => 100.00,
            $totalJP >= 15 => 80.00,
            $totalJP >= 10 => 65.00,
            $totalJP >= 1  => 50.00,
            default        => 35.00,
        };

        // 3. Rekam Jejak (Kepangkatan, Masa Kerja, Penghargaan)
        $skorRekamJejak = $this->calculateRekamJejak($pegawai);

        // 4. Asesmen Kompetensi / Assessment Center (BKN / Mandiri)
        $latestAsesmen = $pegawai->talentAssessments()
            ->orderBy('tanggal_asesmen', 'desc')
            ->first();

        $skorAsesmen = $latestAsesmen?->skor_total !== null
            ? (float) $latestAsesmen->skor_total
            : null;

        // Hitung Komposit Nilai Sumbu Potensi:
        if ($skorAsesmen !== null) {
            // Jika ada data Assessment Center: Asesmen berbobot 50%, Pendidikan 20%, Diklat 15%, Rekam Jejak 15%
            $nilaiAkhirPotensi = round(
                ($skorAsesmen * 0.50) +
                ($skorPendidikan * 0.20) +
                ($skorDiklat * 0.15) +
                ($skorRekamJejak * 0.15),
                2
            );
        } else {
            // Jika belum ada Assessment Center: Pendidikan 40%, Pemenuhan 20 JP Diklat 30%, Rekam Jejak 30%
            $nilaiAkhirPotensi = round(
                ($skorPendidikan * 0.40) +
                ($skorDiklat * 0.30) +
                ($skorRekamJejak * 0.30),
                2
            );
        }

        // Kategori Potensi PermenPAN-RB No. 3/2020:
        // Tinggi: >= 90.00, Sedang: 75.00 - 89.99, Rendah: < 75.00
        $kategori = match (true) {
            $nilaiAkhirPotensi >= 90.00 => 'Tinggi',
            $nilaiAkhirPotensi >= 75.00 => 'Sedang',
            default                     => 'Rendah',
        };

        return [
            'skor_pendidikan'  => $skorPendidikan,
            'skor_diklat'      => $skorDiklat,
            'skor_rekam_jejak' => $skorRekamJejak,
            'skor_asesmen'     => $skorAsesmen,
            'nilai_akhir'      => min(100.00, max(0.00, $nilaiAkhirPotensi)),
            'kategori'         => $kategori,
        ];
    }

    /**
     * Konversi nilai / predikat SKP ke skor numerik (0-100)
     */
    private function convertSkpScore(?RiwayatSkp $skp): ?float
    {
        if (!$skp) {
            return null;
        }

        // Jika sudah ada nilai_akhir atau nilai_skp numerik
        if (!empty($skp->nilai_akhir) && $skp->nilai_akhir > 0) {
            return (float) $skp->nilai_akhir;
        }
        if (!empty($skp->nilai_skp) && $skp->nilai_skp > 0) {
            return (float) $skp->nilai_skp;
        }

        // Konversi berdasarkan predikat kinerja PermenPAN-RB No. 6/2022
        return match ($skp->predikat_kinerja) {
            'Sangat Baik'     => 100.00,
            'Baik'            => 85.00,
            'Butuh Perbaikan' => 65.00,
            'Kurang'          => 45.00,
            'Sangat Kurang'   => 25.00,
            default           => 80.00,
        };
    }

    /**
     * Hitung skor rekam jejak kepangkatan & penghargaan
     */
    private function calculateRekamJejak(Pegawai $pegawai): float
    {
        $baseScore = 70.00;

        // Pangkat/Golongan
        $kodeGol = $pegawai->golongan?->kode ?? '';
        if (str_starts_with($kodeGol, 'IV')) {
            $baseScore += 15.00;
        } elseif (str_starts_with($kodeGol, 'III')) {
            $baseScore += 10.00;
        } elseif (str_starts_with($kodeGol, 'II')) {
            $baseScore += 5.00;
        }

        // Masa Kerja (MKG Tahun)
        $mkg = $pegawai->mkg_tahun ?? 0;
        if ($mkg >= 15) {
            $baseScore += 10.00;
        } elseif ($mkg >= 8) {
            $baseScore += 5.00;
        }

        // Riwayat Penghargaan (Bonus +5 per penghargaan, max 10)
        $countPenghargaan = $pegawai->riwayatPenghargaan()->count();
        $baseScore += min(10.00, $countPenghargaan * 5.00);

        return min(100.00, $baseScore);
    }

    /**
     * Hitung disiplin kehadiran tahun evaluasi
     */
    private function calculateDisiplinKehadiran(Pegawai $pegawai, int $year): ?float
    {
        $userId = $pegawai->user?->id;
        if (!$userId) {
            return null;
        }

        $attendanceCount = Attendance::where('user_id', $userId)
            ->whereYear('attendance_date', $year)
            ->count();

        if ($attendanceCount === 0) {
            return null;
        }

        $hadirTepatWaktu = Attendance::where('user_id', $userId)
            ->whereYear('attendance_date', $year)
            ->where('status', 'hadir')
            ->where(function ($q) {
                $q->whereNull('is_suspicious')->orWhere('is_suspicious', false);
            })
            ->count();

        return round(($hadirTepatWaktu / $attendanceCount) * 100, 2);
    }

    /**
     * Hitung keaktifan logbook
     */
    private function calculateKeaktifanLogbook(Pegawai $pegawai, int $year): ?float
    {
        $totalLogbook = Logbook::where('pegawai_id', $pegawai->id)
            ->whereYear('tanggal', $year)
            ->count();

        if ($totalLogbook === 0) {
            return null;
        }

        $approvedLogbook = Logbook::where('pegawai_id', $pegawai->id)
            ->whereYear('tanggal', $year)
            ->where('status', Logbook::STATUS_DISETUJUI)
            ->count();

        return round(($approvedLogbook / $totalLogbook) * 100, 2);
    }

    /**
     * Pemetaan Matriks 9 Kotak (9-Box Grid) Sesuai PermenPAN-RB No. 3 Tahun 2020 Lampiran
     * 
     * Sumbu X: Kinerja (Rendah, Sedang, Tinggi)
     * Sumbu Y: Potensi (Rendah, Sedang, Tinggi)
     */
    public function determineNineBox(string $kinerjaKategori, string $potensiKategori): array
    {
        // Kotak IX: Kinerja Tinggi, Potensi Tinggi
        if ($kinerjaKategori === 'Tinggi' && $potensiKategori === 'Tinggi') {
            return [
                'box_number'          => 9,
                'box_name'            => 'Kotak IX: Kinerja Tinggi & Potensi Tinggi',
                'status_talenta'      => 'Talenta Unggul (Siap Dipromosikan)',
                'rekomendasi'         => 'Masuk kelompok rencana suksesi (Talent Pool Prioritas 1), promosi jabatan struktural/fungsional target, rotasi pengayaan tugas, program retensi talenta.',
                'is_suksesi_eligible' => true,
            ];
        }

        // Kotak VIII: Kinerja Tinggi, Potensi Sedang
        if ($kinerjaKategori === 'Tinggi' && $potensiKategori === 'Sedang') {
            return [
                'box_number'          => 8,
                'box_name'            => 'Kotak VIII: Kinerja Tinggi & Potensi Sedang',
                'status_talenta'      => 'Kinerja Di Atas Ekspektasi (Suksesi Cadangan)',
                'rekomendasi'         => 'Masuk kelompok rencana suksesi prioritas 2, pengayaan tugas dan rotasi lintas unit, akselerasi pengembangan kompetensi untuk mendorong potensi ke Kotak IX.',
                'is_suksesi_eligible' => true,
            ];
        }

        // Kotak VII: Kinerja Tinggi, Potensi Rendah
        if ($kinerjaKategori === 'Tinggi' && $potensiKategori === 'Rendah') {
            return [
                'box_number'          => 7,
                'box_name'            => 'Kotak VII: Kinerja Tinggi & Potensi Rendah',
                'status_talenta'      => 'Spesialis / Solid Performer',
                'rekomendasi'         => 'Dipertahankan pada jabatan saat ini, pengayaan peran teknis, sertifikasi profesi lanjutan, bimbingan berkala, dan apresiasi kinerja.',
                'is_suksesi_eligible' => false,
            ];
        }

        // Kotak VI: Kinerja Sedang, Potensi Tinggi
        if ($kinerjaKategori === 'Sedang' && $potensiKategori === 'Tinggi') {
            return [
                'box_number'          => 6,
                'box_name'            => 'Kotak VI: Kinerja Sedang & Potensi Tinggi',
                'status_talenta'      => 'Calon Talenta Berpotensi',
                'rekomendasi'         => 'Bimbingan kinerja (coaching & mentoring intensif) oleh pimpinan/atasan langsung, penugasan proyek strategis agar capaian kinerja meningkat ke Kotak IX.',
                'is_suksesi_eligible' => false,
            ];
        }

        // Kotak V: Kinerja Sedang, Potensi Sedang
        if ($kinerjaKategori === 'Sedang' && $potensiKategori === 'Sedang') {
            return [
                'box_number'          => 5,
                'box_name'            => 'Kotak V: Kinerja Sedang & Potensi Sedang',
                'status_talenta'      => 'Pekerja Inti (Core Employee)',
                'rekomendasi'         => 'Dipertahankan pada posisi, rotasi tugas yang setara secara berkala, pemenuhan hak minimal 20 JP diklat teknis per tahun sesuai PP 11/2017.',
                'is_suksesi_eligible' => false,
            ];
        }

        // Kotak IV: Kinerja Sedang, Potensi Rendah
        if ($kinerjaKategori === 'Sedang' && $potensiKategori === 'Rendah') {
            return [
                'box_number'          => 4,
                'box_name'            => 'Kotak IV: Kinerja Sedang & Potensi Rendah',
                'status_talenta'      => 'Pekerja Efektif',
                'rekomendasi'         => 'Dipertahankan pada jabatan saat ini, pelatihan penyegaran keterampilan teknis operasional sesuai ABK, monitoring berkala.',
                'is_suksesi_eligible' => false,
            ];
        }

        // Kotak III: Kinerja Rendah, Potensi Tinggi
        if ($kinerjaKategori === 'Rendah' && $potensiKategori === 'Tinggi') {
            return [
                'box_number'          => 3,
                'box_name'            => 'Kotak III: Kinerja Rendah & Potensi Tinggi',
                'status_talenta'      => 'Kurang Selaras (Misaligned / Perlu Penataan)',
                'rekomendasi'         => 'Evaluasi kesesuaian penempatan jabatan (person-job match), konseling karier, rotasi atau mutasi ke unit kerja yang selaras dengan minat dan potensi.',
                'is_suksesi_eligible' => false,
            ];
        }

        // Kotak II: Kinerja Rendah, Potensi Sedang
        if ($kinerjaKategori === 'Rendah' && $potensiKategori === 'Sedang') {
            return [
                'box_number'          => 2,
                'box_name'            => 'Kotak II: Kinerja Rendah & Potensi Sedang',
                'status_talenta'      => 'Kurang Efektif',
                'rekomendasi'         => 'Bimbingan kinerja terstruktur, penyesuaian target SKP, penempatan di bawah supervisi ketat atasan langsung, evaluasi berkala per triwulan.',
                'is_suksesi_eligible' => false,
            ];
        }

        // Kotak I: Kinerja Rendah, Potensi Rendah
        return [
            'box_number'          => 1,
            'box_name'            => 'Kotak I: Kinerja Rendah & Potensi Rendah',
            'status_talenta'      => 'Perlu Pembinaan Khusus / Underperformer',
            'rekomendasi'         => 'Pembinaan disiplin dan kinerja intensif, konseling, pertimbangan demosi jabatan atau relokasi tugas non-kritis sesuai mekanisme PP 94/2021.',
            'is_suksesi_eligible' => false,
        ];
    }

    /**
     * Hitung evaluasi seluruh pegawai aktif sekaligus (Batch Processing)
     */
    public function batchCalculate(?int $year = null, ?int $unitKerjaId = null): array
    {
        $year = $year ?? (int) date('Y');

        $query = Pegawai::where('status_pegawai', 'Aktif');
        if ($unitKerjaId) {
            $query->where('unit_kerja_id', $unitKerjaId);
        }

        $pegawais = $query->with([
            'golongan', 'unitKerja', 'jabatan', 'riwayatSkp', 'riwayatDiklat', 'talentAssessments', 'riwayatPenghargaan'
        ])->get();

        $processed = 0;
        foreach ($pegawais as $pegawai) {
            $this->calculatePegawaiTalent($pegawai, $year);
            $processed++;
        }

        return [
            'total_pegawai' => $pegawais->count(),
            'processed'     => $processed,
            'tahun'         => $year,
        ];
    }

    /**
     * Rekap statistik distribusi 9-Kotak untuk Dashboard Pimpinan
     */
    public function getNineBoxDistribution(?int $year = null, ?int $unitKerjaId = null): array
    {
        $year = $year ?? (int) date('Y');

        $query = TalentMapping::where('tahun', $year);
        if ($unitKerjaId) {
            $query->where('unit_kerja_saat_ini_id', $unitKerjaId);
        }

        $allMappings = $query->with(['pegawai.unitKerja', 'pegawai.jabatan'])->get();

        $distribution = [];
        for ($i = 1; $i <= 9; $i++) {
            $items = $allMappings->where('kuadran_box', $i)->values();
            $distribution[$i] = [
                'box'   => $i,
                'count' => $items->count(),
                'items' => $items,
            ];
        }

        return [
            'tahun'              => $year,
            'total'              => $allMappings->count(),
            'eligible_suksesi'   => $allMappings->where('is_suksesi_eligible', true)->count(),
            'boxes'              => $distribution,
        ];
    }
}
