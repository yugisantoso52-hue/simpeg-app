<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\UnitKerja;
use App\Models\Logbook;
use App\Models\Attendance;
use App\Models\RiwayatPendidikan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExecutiveAnalyticsService
{
    /**
     * Ringkasan Indikator Kinerja Utama (KPI Eksekutif)
     */
    public function getExecutiveKpis(int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Total Pegawai Aktif & Kategori
        $pegawaiAktif = Pegawai::where('status_pegawai', 'Aktif')->get();
        $totalAktif = $pegawaiAktif->count();
        $totalDosen = $pegawaiAktif->filter(fn($p) => strtolower($p->jenis_pegawai ?? '') === 'dosen')->count();
        $totalTendik = $pegawaiAktif->filter(fn($p) => strtolower($p->jenis_pegawai ?? '') === 'tendik')->count();
        $totalPhl = $pegawaiAktif->filter(fn($p) => str_contains(strtolower($p->jenis_pegawai ?? ''), 'phl') || str_contains(strtolower($p->status_kepegawaian ?? ''), 'honorer'))->count();

        // Kinerja Logbook Bulan Ini
        $logbookTotal = Logbook::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])->count();
        $logbookApproved = Logbook::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', Logbook::STATUS_DISETUJUI)->count();
        $logbookPending = Logbook::whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', Logbook::STATUS_DIAJUKAN)->count();
        $approvalRate = $logbookTotal > 0 ? round(($logbookApproved / $logbookTotal) * 100, 1) : 0;

        // Presensi Bulan Ini
        $attendances = Attendance::whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])->get();
        $totalPresensi = $attendances->count();
        $tepatWaktu = $attendances->where('status', 'present')->count();
        $terlambat = $attendances->where('status', 'late')->count();
        $onTimeRate = $totalPresensi > 0 ? round(($tepatWaktu / $totalPresensi) * 100, 1) : 0;

        // Pensiun Radar 1-5 Tahun
        $pensiunRadar = $this->getRetirementProjections();
        $totalPensiun5Th = $pensiunRadar['summary']['total_5_tahun'] ?? 0;

        return [
            'total_aktif'       => $totalAktif,
            'total_dosen'       => $totalDosen,
            'total_tendik'      => $totalTendik,
            'total_phl'         => $totalPhl,
            'logbook_total'     => $logbookTotal,
            'logbook_approved'  => $logbookApproved,
            'logbook_pending'   => $logbookPending,
            'logbook_rate'      => $approvalRate,
            'presensi_total'    => $totalPresensi,
            'presensi_tepat'    => $tepatWaktu,
            'presensi_telat'    => $terlambat,
            'presensi_rate'     => $onTimeRate,
            'pensiun_5_tahun'   => $totalPensiun5Th,
        ];
    }

    /**
     * Beban Kerja Logbook per Unit Kerja / Program Studi
     */
    public function getLogbookWorkloadByUnit(int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $units = UnitKerja::withCount(['pegawai' => function ($q) {
            $q->where('status_pegawai', 'Aktif');
        }])->get();

        $labels = [];
        $totalHours = [];
        $approvedCounts = [];
        $pendingCounts = [];

        foreach ($units as $unit) {
            $pegawaiIds = Pegawai::where('unit_kerja_id', $unit->id)
                ->where('status_pegawai', 'Aktif')
                ->pluck('id');

            if ($pegawaiIds->isEmpty()) {
                continue;
            }

            $logs = Logbook::whereIn('pegawai_id', $pegawaiIds)
                ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $sumMinutes = $logs->sum(function ($l) {
                if ($l->durasi_menit) return (int) $l->durasi_menit;
                if ($l->jam_mulai && $l->jam_selesai) {
                    try {
                        return Carbon::parse($l->jam_mulai)->diffInMinutes(Carbon::parse($l->jam_selesai));
                    } catch (\Throwable $e) {
                        return 60;
                    }
                }
                return 60;
            });

            $labels[] = $unit->nama_unit;
            $totalHours[] = round($sumMinutes / 60, 1);
            $approvedCounts[] = $logs->where('status', Logbook::STATUS_DISETUJUI)->count();
            $pendingCounts[] = $logs->where('status', Logbook::STATUS_DIAJUKAN)->count();
        }

        return [
            'labels'         => $labels,
            'hours'          => $totalHours,
            'approved'       => $approvedCounts,
            'pending'        => $pendingCounts,
        ];
    }

    /**
     * Tren Presensi Harian Fakultas
     */
    public function getAttendanceTrends(int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $today = Carbon::today('Asia/Jakarta');

        $attendances = Attendance::whereBetween('attendance_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy(function ($item) {
                return Carbon::parse($item->attendance_date)->format('Y-m-d');
            });

        $labels = [];
        $presentCounts = [];
        $lateCounts = [];
        $leaveCounts = [];

        $current = $startDate->copy();
        while ($current->lte($endDate) && $current->lte($today)) {
            // Hanya hari kerja Senin-Jumat
            if ($current->isWeekday()) {
                $dateStr = $current->format('Y-m-d');
                $labels[] = $current->format('d M');
                
                $dayLogs = $attendances->get($dateStr, collect());
                $presentCounts[] = $dayLogs->where('status', 'present')->count();
                $lateCounts[] = $dayLogs->where('status', 'late')->count();
                $leaveCounts[] = $dayLogs->filter(fn($a) => in_array($a->status, ['leave', 'sick', 'permit']))->count();
            }
            $current->addDay();
        }

        return [
            'labels'   => $labels,
            'present'  => $presentCounts,
            'late'     => $lateCounts,
            'leave'    => $leaveCounts,
        ];
    }

    /**
     * Radar & Proyeksi Pensiun Batas Usia Pensiun (1-5 Tahun)
     */
    public function getRetirementProjections(): array
    {
        $hariIni = Carbon::today('Asia/Jakarta');
        $paraPegawai = Pegawai::with(['unitKerja', 'jabatan'])
            ->where('status_pegawai', 'Aktif')
            ->get();

        $tahunIniList = collect();
        $tahun1List = collect();
        $tahun2List = collect();
        $tahun3sd5List = collect();

        foreach ($paraPegawai as $p) {
            if (!$p->tanggal_lahir && !$p->tanggal_pensiun) continue;

            $bup = $p->batas_usia_pensiun ?: $this->hitungBup($p);

            if ($p->tanggal_pensiun) {
                $tglPensiun = Carbon::parse($p->tanggal_pensiun);
            } elseif ($p->tanggal_lahir) {
                $tglPensiun = Carbon::parse($p->tanggal_lahir)->addYears($bup);
            } else {
                continue;
            }

            // Hitung sisa bulan
            $diffMonths = $hariIni->diffInMonths($tglPensiun, false);

            // Sudah lewat atau di luar radar 5 tahun
            if ($diffMonths < 0 || $diffMonths > 60) continue;

            $item = (object) [
                'id'            => $p->id,
                'nip'           => $p->nip ?? '-',
                'nama'          => $p->nama_lengkap ?? $p->nama,
                'unit'          => $p->unitKerja?->nama_unit ?? 'Fakultas Keperawatan',
                'jabatan'       => $p->jabatan?->nama_jabatan ?? $p->jenis_jabatan ?? '-',
                'bup'           => $bup,
                'tgl_pensiun'   => $tglPensiun->format('d-m-Y'),
                'sisa_bulan'    => (int) $diffMonths,
            ];

            if ($tglPensiun->year === $hariIni->year) {
                $tahunIniList->push($item);
            } elseif ($tglPensiun->year === $hariIni->year + 1) {
                $tahun1List->push($item);
            } elseif ($tglPensiun->year === $hariIni->year + 2) {
                $tahun2List->push($item);
            } else {
                $tahun3sd5List->push($item);
            }
        }

        $total5Th = $tahunIniList->count() + $tahun1List->count() + $tahun2List->count() + $tahun3sd5List->count();

        return [
            'summary' => [
                'tahun_ini'      => $tahunIniList->count(),
                'tahun_1'        => $tahun1List->count(),
                'tahun_2'        => $tahun2List->count(),
                'tahun_3_sd_5'   => $tahun3sd5List->count(),
                'total_5_tahun'  => $total5Th,
            ],
            'details' => [
                'tahun_ini'      => $tahunIniList->sortBy('sisa_bulan')->values(),
                'tahun_1'        => $tahun1List->sortBy('sisa_bulan')->values(),
                'tahun_2'        => $tahun2List->sortBy('sisa_bulan')->values(),
                'tahun_3_sd_5'   => $tahun3sd5List->sortBy('sisa_bulan')->values(),
            ]
        ];
    }

    /**
     * Komposisi SDM (Jabatan Fungsional Dosen & Pendidikan Terakhir)
     */
    public function getStaffComposition(): array
    {
        $dosen = Pegawai::with('jabatan')
            ->where('status_pegawai', 'Aktif')
            ->where(function ($q) {
                $q->where('jenis_pegawai', 'Dosen')
                  ->orWhere('jenis_pegawai', 'like', '%dosen%');
            })
            ->get();

        // 1. Jabatan Fungsional Dosen
        $jafungCounts = [
            'Guru Besar'     => 0,
            'Lektor Kepala'  => 0,
            'Lektor'         => 0,
            'Asisten Ahli'   => 0,
            'Tenaga Pengajar'=> 0,
        ];

        foreach ($dosen as $d) {
            $jab = strtolower($d->jabatan?->nama_jabatan ?? $d->jenis_jabatan ?? '');
            if (str_contains($jab, 'profesor') || str_contains($jab, 'guru besar')) {
                $jafungCounts['Guru Besar']++;
            } elseif (str_contains($jab, 'lektor kepala')) {
                $jafungCounts['Lektor Kepala']++;
            } elseif (str_contains($jab, 'lektor')) {
                $jafungCounts['Lektor']++;
            } elseif (str_contains($jab, 'asisten ahli')) {
                $jafungCounts['Asisten Ahli']++;
            } else {
                $jafungCounts['Tenaga Pengajar']++;
            }
        }

        // 2. Kualifikasi Pendidikan Terakhir (Dosen)
        $pendidikanCounts = [
            'S3 (Doktor)'   => 0,
            'S2 (Magister)' => 0,
            'Spesialis'     => 0,
            'Lainnya'       => 0,
        ];

        $pegawaiIds = $dosen->pluck('id');
        $riwayatPendidikan = RiwayatPendidikan::whereIn('pegawai_id', $pegawaiIds)->get();

        foreach ($dosen as $d) {
            $pends = $riwayatPendidikan->where('pegawai_id', $d->id);
            $hasS3 = $pends->filter(fn($p) => str_contains(strtoupper($p->tingkat_pendidikan ?? ''), 'S3') || str_contains(strtoupper($p->tingkat_pendidikan ?? ''), 'DOKTOR'))->isNotEmpty();
            $hasSpesialis = $pends->filter(fn($p) => str_contains(strtoupper($p->tingkat_pendidikan ?? ''), 'SP') || str_contains(strtoupper($p->tingkat_pendidikan ?? ''), 'SPESIALIS'))->isNotEmpty();
            $hasS2 = $pends->filter(fn($p) => str_contains(strtoupper($p->tingkat_pendidikan ?? ''), 'S2') || str_contains(strtoupper($p->tingkat_pendidikan ?? ''), 'MAGISTER'))->isNotEmpty();

            if ($hasS3) {
                $pendidikanCounts['S3 (Doktor)']++;
            } elseif ($hasSpesialis) {
                $pendidikanCounts['Spesialis']++;
            } elseif ($hasS2) {
                $pendidikanCounts['S2 (Magister)']++;
            } else {
                $pendidikanCounts['Lainnya']++;
            }
        }

        return [
            'jafung'     => $jafungCounts,
            'pendidikan' => $pendidikanCounts,
        ];
    }

    /**
     * Menentukan Batas Usia Pensiun (BUP)
     */
    private function hitungBup(Pegawai $pegawai): int
    {
        $jabatan = strtolower($pegawai->jabatan?->nama_jabatan ?? $pegawai->jenis_jabatan ?? '');
        $jenisJabatan = strtolower($pegawai->jenis_jabatan ?? '');

        if (str_contains($jabatan, 'profesor') || str_contains($jabatan, 'guru besar') || 
            str_contains($jabatan, 'peneliti ahli utama') || str_contains($jabatan, 'perekayasa ahli utama')) {
            return 70;
        }

        if (str_contains($jabatan, 'dosen') || str_contains($jabatan, 'ahli utama') || strtolower($pegawai->jenis_pegawai ?? '') === 'dosen') {
            return 65;
        }

        if (str_contains($jabatan, 'guru') || str_contains($jabatan, 'ahli madya') || 
            str_contains($jenisJabatan, 'pimpinan tinggi') || str_contains($jenisJabatan, 'jpt')) {
            return 60;
        }

        return 58;
    }
}
