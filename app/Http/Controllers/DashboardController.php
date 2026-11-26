<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\LayananSurat;
use App\Models\PengajuanKarir;
use App\Models\UsulanSatyalancana;
use App\Models\Logbook;
use App\Models\Attendance;
use App\Models\AnalisisJabatan;
use App\Models\Jabatan;
use App\Models\TalentMapping;
use App\Models\SuccessionPlan;
use App\Services\DashboardService;
use App\Services\LogbookService;
use App\Services\PegawaiCompletenessService;
use App\Services\TalentScoringService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    // Dependency Injection DashboardService
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Menampilkan Halaman Utama Dashboard
     */
    public function index(): View
    {
        $user = Auth::user();

        $data = [
            'statistik'        => $this->dashboardService->statistics(),
            'grafikGolongan'   => $this->dashboardService->grafikGolongan(),
            'grafikPendidikan' => $this->dashboardService->grafikPendidikan(),
            'grafikUnit'       => $this->dashboardService->grafikUnit(),
            'pegawaiBaru'      => $this->dashboardService->pegawaiBaru(),
            'reminder'         => $this->dashboardService->reminder(),
        ];

        // Cek identitas & peran user (eksklusif sesuai tahapan wewenang masing-masing)
        $isDekan   = $user->isDekan();
        $isWadek1  = $user->isWadek1();
        $isWadek2  = $user->isWadek2();
        $isWadek3  = $user->isWadek3();
        $isKabag   = $user->isKabagUmum();
        $isPokja   = $user->isKaPokjaKepeg();
        $isKajur   = $user->isKajur();
        $isSekjur  = $user->isSekjur();
        $isAdmin   = $user->isScreeningAdmin();
        $isAtasan  = $user->isAtasan() || $user->hasRole('pimpinan') || $user->canAccessExecutiveKepegawaianMenus();

        $data['isAdmin']   = $isAdmin;
        $data['isAtasan']  = $isAtasan;
        $data['isDekan']   = $isDekan;
        $data['isWadek1']  = $isWadek1;
        $data['isWadek2']  = $isWadek2;
        $data['isWadek3']  = $isWadek3;
        $data['isKabag']   = $isKabag;
        $data['isPokja']   = $isPokja;
        $data['isKajur']   = $isKajur;
        $data['isSekjur']  = $isSekjur;

        // Ambil data profil pegawai yang login (baik pegawai mandiri maupun pejabat/admin)
        $pegawaiId = $user->pegawai_id;
        if (!$pegawaiId) {
            $p = Pegawai::where('email', $user->email)->orWhere('nip', $user->name)->first();
            $pegawaiId = $p?->id;
        }

        if ($pegawaiId) {
            $myPegawai = Pegawai::with(['unitKerja', 'jabatan', 'golongan', 'riwayatStrSip', 'riwayatPendidikan', 'riwayatDiklat', 'riwayatSkp'])->find($pegawaiId);
            $myCuti    = PengajuanCuti::where('pegawai_id', $pegawaiId)->latest()->take(5)->get();

            $data['myPegawai']        = $myPegawai;
            $data['myCuti']           = $myCuti;
            $data['completenessData'] = PegawaiCompletenessService::calculate($myPegawai);

            // Tambahan data E-Logbook & Presensi Mandiri
            $logbookService = app(LogbookService::class);
            $now = Carbon::now('Asia/Jakarta');
            $data['myLogbookStats'] = $logbookService->getPegawaiStatistics($pegawaiId, $now->month, $now->year);
            $data['myRecentLogbooks'] = Logbook::where('pegawai_id', $pegawaiId)->latest('tanggal')->latest('jam_mulai')->take(4)->get();

            // Status Kuadran Talenta Mandiri (PermenPAN-RB No. 3/2020)
            $data['myTalentMapping'] = TalentMapping::where('pegawai_id', $pegawaiId)
                ->orderByDesc('tahun')
                ->first();

            // 🌟 DATA LAYANAN & USULAN AKTIF PEGAWAI MANDIRI 🌟
            $data['myLayananSurat']   = \App\Models\LayananSurat::where('pegawai_id', $pegawaiId)->latest()->take(5)->get();
            $data['myPengajuanKarir'] = \App\Models\PengajuanKarir::with(['golonganLama', 'golonganTujuan'])->where('pegawai_id', $pegawaiId)->latest()->take(5)->get();
            $data['mySatyalancana']   = \App\Models\UsulanSatyalancana::where('pegawai_id', $pegawaiId)->latest()->take(5)->get();
        }

        // Jika user yang login adalah Admin / Pimpinan / Atasan Langsung / Pejabat Eksekutif
        if ($isAdmin || $isAtasan) {
            // 🎓 DATA KHUSUS WAKIL DEKAN I (AKADEMIK, DOSEN, NIDN & TUBEL)
            if ($isWadek1 || $isDekan || $isAdmin) {
                try {
                    $data['akademikStats'] = Cache::remember('dashboard_akademik_stats', 300, function() {
                        $totalDosen = Pegawai::where('jenis_pegawai', 'Dosen')->count();
                        $withNidn = Pegawai::where('jenis_pegawai', 'Dosen')->whereNotNull('nidn_nuptk')->where('nidn_nuptk', '!=', '')->count();
                        $withoutNidn = max(0, $totalDosen - $withNidn);
                        $tubelAktif = \App\Models\TugasBelajar::whereIn('status_studi', ['Aktif', 'aktif', 'Sedang Studi'])->count();
                        $strExpiring = Pegawai::where('jenis_pegawai', 'Dosen')
                            ->whereNotNull('tanggal_kadaluarsa_str')
                            ->whereBetween('tanggal_kadaluarsa_str', [now(), now()->addMonths(6)])
                            ->count();
                        return [
                            'totalDosen'   => $totalDosen,
                            'withNidn'     => $withNidn,
                            'withoutNidn'  => $withoutNidn,
                            'tubelAktif'   => $tubelAktif,
                            'strExpiring'  => $strExpiring,
                        ];
                    });
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Dashboard akademik stats error: ' . $e->getMessage());
                    $data['akademikStats'] = [
                        'totalDosen'   => 0,
                        'withNidn'     => 0,
                        'withoutNidn'  => 0,
                        'tubelAktif'   => 0,
                        'strExpiring'  => 0,
                    ];
                }
            }

            // 🏛️ DATA KHUSUS KAJUR & SEKJUR (MONITORING DOSEN JURUSAN & KINERJA)
            if ($isKajur || $isSekjur) {
                try {
                    $bawahanIds = $user->getBawahanIds();
                    $unitKerjaUser = $user->pegawai?->unitKerja?->nama_unit ?? 'Jurusan Keperawatan';

                    $dosenJurusanQuery = Pegawai::whereIn('id', $bawahanIds)->orWhere('unit_kerja_id', $user->pegawai?->unit_kerja_id);
                    $dosenJurusanIds = $dosenJurusanQuery->pluck('id')->toArray();

                    $nowTgl = Carbon::now('Asia/Jakarta');
                    $logbookDosenBulanIni = Logbook::whereIn('pegawai_id', $dosenJurusanIds)
                        ->whereMonth('tanggal', $nowTgl->month)
                        ->whereYear('tanggal', $nowTgl->year)
                        ->distinct('pegawai_id')
                        ->pluck('pegawai_id')
                        ->toArray();

                    $data['jurusanStats'] = [
                        'namaUnit'          => $unitKerjaUser,
                        'totalDosen'        => count($dosenJurusanIds),
                        'sudahLogbookCount' => count($logbookDosenBulanIni),
                        'belumLogbookCount' => max(0, count($dosenJurusanIds) - count($logbookDosenBulanIni)),
                        'pendingLogbook'    => Logbook::whereIn('pegawai_id', $dosenJurusanIds)->where('status', Logbook::STATUS_DIAJUKAN)->count(),
                        'pendingCuti'       => PengajuanCuti::whereIn('pegawai_id', $dosenJurusanIds)->whereIn('status', ['Menunggu Persetujuan Atasan', 'Menunggu Persetujuan', 'Menunggu Pertimbangan Atasan'])->count(),
                        'strExpiringCount'  => Pegawai::whereIn('id', $dosenJurusanIds)->whereNotNull('tanggal_kadaluarsa_str')->whereBetween('tanggal_kadaluarsa_str', [now(), now()->addMonths(6)])->count(),
                        'recentPendingCuti' => PengajuanCuti::with('pegawai')->whereIn('pegawai_id', $dosenJurusanIds)->whereIn('status', ['Menunggu Persetujuan Atasan', 'Menunggu Persetujuan', 'Menunggu Pertimbangan Atasan'])->latest()->take(3)->get(),
                    ];
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Dashboard jurusan stats error: ' . $e->getMessage());
                    $data['jurusanStats'] = [
                        'namaUnit'          => 'Jurusan Keperawatan',
                        'totalDosen'        => 0,
                        'sudahLogbookCount' => 0,
                        'belumLogbookCount' => 0,
                        'pendingLogbook'    => 0,
                        'pendingCuti'       => 0,
                        'strExpiringCount'  => 0,
                        'recentPendingCuti' => collect(),
                    ];
                }
            }

            // Ringkasan Eksekutif Analisis Jabatan & Formasi Beban Kerja (Anjab & ABK)
            try {
                $data['abkSummary'] = Cache::remember('dashboard_abk_summary_v2', 600, function() {
                    $anjabs = AnalisisJabatan::with(['jabatan', 'unitKerja', 'uraianTugas'])->get()->sortByDesc('hierarchy_order')->values();
                    $totalKebutuhan = (int) $anjabs->sum('formasi_pembulatan');
                    $totalBezetting = (int) $anjabs->sum('bezetting');
                    $rasioKeterisian = $totalKebutuhan > 0 ? round(($totalBezetting / $totalKebutuhan) * 100, 1) : 0;

                    $jabatanKurang = $anjabs->filter(fn($a) => $a->selisih_formasi < 0);
                    $totalDefisit = (int) $jabatanKurang->sum(fn($a) => abs($a->selisih_formasi));
                    $totalIdeal = $anjabs->filter(fn($a) => $a->selisih_formasi == 0)->count();
                    $totalLebih = (int) $anjabs->filter(fn($a) => $a->selisih_formasi > 0)->sum('selisih_formasi');

                    $prioritasDefisit = $jabatanKurang->sortBy(fn($a) => $a->selisih_formasi)->take(6)->values();

                    $allAnjabsFormatted = $anjabs->map(function($a) {
                        return [
                            'id'           => $a->id,
                            'jabatan'      => $a->jabatan?->nama_jabatan ?? '-',
                            'unit'         => $a->unitKerja?->nama_unit ?? 'Fakultas Keperawatan',
                            'kelas'        => $a->kelas_jabatan ?? $a->jabatan?->kelas_jabatan ?? '-',
                            'kebutuhan'    => (int) $a->formasi_pembulatan,
                            'bezetting'    => (int) $a->bezetting,
                            'selisih'      => (int) $a->selisih_formasi,
                            'status_label' => $a->status_formasi,
                            'status_color' => $a->status_color,
                            'kode_anjab'   => $a->kode_anjab ?? ('ANJAB-' . $a->id),
                            'total_jam'    => $a->total_jam_beban,
                            'is_defisit'   => $a->selisih_formasi < 0,
                            'is_ideal'     => $a->selisih_formasi == 0,
                            'is_lebih'     => $a->selisih_formasi > 0,
                            'url_abk'      => route('abk.edit', $a->id),
                            'url_anjab'    => route('anjab.show', $a->id),
                        ];
                    })->values()->all();

                    return [
                        'totalKebutuhan'     => $totalKebutuhan,
                        'totalBezetting'     => $totalBezetting,
                        'rasioKeterisian'    => $rasioKeterisian,
                        'totalDefisit'       => $totalDefisit,
                        'totalIdeal'         => $totalIdeal,
                        'totalLebih'         => $totalLebih,
                        'totalDokumen'       => $anjabs->count(),
                        'totalMasterJabatan' => Jabatan::count(),
                        'prioritasDefisit'   => $prioritasDefisit,
                        'items'              => $allAnjabsFormatted,
                    ];
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard abk summary error: ' . $e->getMessage());
                $data['abkSummary'] = [
                    'totalKebutuhan'     => 0,
                    'totalBezetting'     => 0,
                    'rasioKeterisian'    => 0,
                    'totalDefisit'       => 0,
                    'totalIdeal'         => 0,
                    'totalLebih'         => 0,
                    'totalDokumen'       => 0,
                    'totalMasterJabatan' => 0,
                    'prioritasDefisit'   => collect(),
                    'items'              => [],
                ];
            }

            // Tambahan Metrik Manajerial (Action Items & Kinerja Terkini)
            $now = Carbon::now('Asia/Jakarta');
            $bawahanIds = (!$isAdmin && $isAtasan) ? $user->getBawahanIds() : null;

            // Hitung cuti pending yang memerlukan tindakan verifikasi / keputusan user ini
            try {
                $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
                $data['pendingCutiCount'] = $hierarchyService->countPendingCutiForUser($user);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard pending cuti count error: ' . $e->getMessage());
                $data['pendingCutiCount'] = 0;
            }

            try {
                $logbookQuery = Logbook::where('status', Logbook::STATUS_DIAJUKAN);
                if ($bawahanIds !== null) {
                    $logbookQuery->whereIn('pegawai_id', $bawahanIds);
                }
                $data['pendingLogbookCount'] = $logbookQuery->count();
                $data['todayPresentCount'] = Attendance::whereDate('attendance_date', $now->toDateString())->count();
                $data['adminLogbookStats'] = app(LogbookService::class)->getAdminStatistics($now->month, $now->year, null, $bawahanIds);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard logbook/attendance stats error: ' . $e->getMessage());
                $data['pendingLogbookCount'] = 0;
                $data['todayPresentCount']   = 0;
                $data['adminLogbookStats']   = [];
            }

            // 🎯 Ringkasan Eksekutif Manajemen Talenta ASN (PermenPAN-RB No. 3/2020)
            $currentYear = (int) $now->year;
            $talentCacheKey = "talent_summary_{$currentYear}";
            try {
                $cachedTalent = Cache::get($talentCacheKey);
                $isIncomplete = false;
                if (is_array($cachedTalent)) {
                    foreach ($cachedTalent['boxes'] ?? [] as $box) {
                        $boxItems = $box['items'] ?? null;
                        if (is_object($boxItems) && get_class($boxItems) === '__PHP_Incomplete_Class') {
                            $isIncomplete = true;
                            break;
                        }
                    }
                }
                if ($isIncomplete) {
                    Cache::forget($talentCacheKey);
                    $cachedTalent = null;
                }

                $talentScoringService = app(TalentScoringService::class);
                if (!$cachedTalent) {
                    if (!TalentMapping::where('tahun', $currentYear)->exists()) {
                        try {
                            \App\Jobs\BatchCalculateTalentJob::dispatch($currentYear);
                        } catch (\Throwable $e) {
                            \Illuminate\Support\Facades\Log::warning('Dispatch BatchCalculateTalentJob error: ' . $e->getMessage());
                        }
                    }
                    $cachedTalent = Cache::remember($talentCacheKey, 600, fn() => $talentScoringService->getNineBoxDistribution($currentYear));
                }
                $data['talentSummary'] = $cachedTalent;
                $data['totalSuccessionPlans'] = Cache::remember('total_succession_plans', 600, fn() => SuccessionPlan::count());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard talent summary error: ' . $e->getMessage());
                Cache::forget($talentCacheKey);
                $data['talentSummary'] = [
                    'tahun'            => $currentYear,
                    'total'            => 0,
                    'eligible_suksesi' => 0,
                    'boxes'            => [],
                ];
                $data['totalSuccessionPlans'] = 0;
            }

            // 🌟 WORKFLOW ACTION CENTER: INBOX PENGESAHAN DOKUMEN & TATA NASKAH BERJENJANG 🌟
            $defaultWorkflowStats = [
                'admin_screening_total' => 0,
                'admin_screening_karir' => 0,
                'admin_screening_surat' => 0,
                'admin_screening_satya' => 0,
                'admin_screening_cuti'  => 0,
                'admin_nomor_total'     => 0,
                'admin_nomor_surat'     => 0,
                'admin_nomor_karir'     => 0,
                'admin_nomor_satya'     => 0,
                'admin_nomor_cuti'      => 0,
                'pokja_pending_total'   => 0,
                'pokja_pending_karir'   => 0,
                'pokja_pending_surat'   => 0,
                'pokja_pending_satya'   => 0,
                'kabag_pending_total'   => 0,
                'kabag_pending_karir'   => 0,
                'kabag_pending_surat'   => 0,
                'kabag_pending_satya'   => 0,
                'wd2_pending_total'     => 0,
                'wd2_pending_karir'     => 0,
                'wd2_pending_surat'     => 0,
                'wd2_pending_satya'     => 0,
                'dekan_pending_total'   => 0,
                'dekan_pending_surat'   => 0,
                'dekan_pending_karir'   => 0,
            ];

            try {
                $data['workflowStats'] = Cache::remember('dashboard_workflow_stats', 60, function() use ($defaultWorkflowStats) {
                    // 1. Pengajuan Karir Agregasi
                    $karirStats = \App\Models\PengajuanKarir::selectRaw("
                        COUNT(CASE WHEN verifikasi_admin_at IS NULL AND status = 'diajukan' THEN 1 END) as admin_screening,
                        COUNT(CASE WHEN (nomor_surat IS NULL OR nomor_surat = '') AND status IN ('diverifikasi_wd2', 'disetujui_wd2', 'disetujui_dekan', 'selesai') THEN 1 END) as admin_nomor,
                        COUNT(CASE WHEN verifikasi_admin_at IS NOT NULL AND paraf_kapokja_at IS NULL AND status NOT IN ('ditolak', 'selesai') THEN 1 END) as pokja_pending,
                        COUNT(CASE WHEN paraf_kapokja_at IS NOT NULL AND paraf_kabag_at IS NULL AND status NOT IN ('ditolak', 'selesai') THEN 1 END) as kabag_pending,
                        COUNT(CASE WHEN paraf_kabag_at IS NOT NULL AND paraf_wd2_at IS NULL AND status NOT IN ('ditolak', 'disetujui_wd2', 'selesai') THEN 1 END) as wd2_pending,
                        COUNT(CASE WHEN paraf_wd2_at IS NOT NULL AND ttd_dekan_at IS NULL AND status NOT IN ('ditolak', 'selesai') THEN 1 END) as dekan_pending
                    ")->first();

                    // 2. Layanan Surat Agregasi
                    $suratStats = \App\Models\LayananSurat::selectRaw("
                        COUNT(CASE WHEN verifikasi_admin_at IS NULL AND status = 'diajukan' THEN 1 END) as admin_screening,
                        COUNT(CASE WHEN (nomor_surat IS NULL OR nomor_surat = '') AND status IN ('diparaf_kabag', 'disetujui', 'selesai') THEN 1 END) as admin_nomor,
                        COUNT(CASE WHEN verifikasi_admin_at IS NOT NULL AND paraf_kapokja_at IS NULL AND status NOT IN ('ditolak', 'selesai') THEN 1 END) as pokja_pending,
                        COUNT(CASE WHEN paraf_kapokja_at IS NOT NULL AND paraf_kabag_at IS NULL AND status NOT IN ('ditolak', 'selesai') THEN 1 END) as kabag_pending,
                        COUNT(CASE WHEN paraf_kabag_at IS NOT NULL AND status NOT IN ('ditolak', 'selesai') AND (penandatangan_jabatan LIKE '%WAKIL DEKAN%' OR penandatangan_jabatan LIKE '%WD%') THEN 1 END) as wd2_pending,
                        COUNT(CASE WHEN paraf_kabag_at IS NOT NULL AND status NOT IN ('ditolak', 'selesai') AND (penandatangan_jabatan LIKE '%DEKAN%' AND penandatangan_jabatan NOT LIKE '%WAKIL%') THEN 1 END) as dekan_pending
                    ")->first();

                    // 3. Usulan Satyalancana Agregasi
                    $satyaStats = \App\Models\UsulanSatyalancana::selectRaw("
                        COUNT(CASE WHEN verifikasi_admin_at IS NULL AND status IN ('diajukan', 'draft') THEN 1 END) as admin_screening,
                        COUNT(CASE WHEN (nomor_surat_usulan IS NULL OR nomor_surat_usulan = '') AND status IN ('diparaf_kabag', 'disetujui_wd2', 'selesai') THEN 1 END) as admin_nomor,
                        COUNT(CASE WHEN verifikasi_admin_at IS NOT NULL AND paraf_kapokja_at IS NULL AND status NOT IN ('ditolak', 'selesai') THEN 1 END) as pokja_pending,
                        COUNT(CASE WHEN paraf_kapokja_at IS NOT NULL AND paraf_kabag_at IS NULL AND status NOT IN ('ditolak', 'selesai') THEN 1 END) as kabag_pending,
                        COUNT(CASE WHEN paraf_kabag_at IS NOT NULL AND ttd_wd2_at IS NULL AND status NOT IN ('ditolak', 'selesai') THEN 1 END) as wd2_pending
                    ")->first();

                    // 4. Pengajuan Cuti Agregasi (4 Tahap Berjenjang)
                    $cutiStats = \App\Models\PengajuanCuti::selectRaw("
                        COUNT(CASE WHEN verifikasi_admin_at IS NULL AND status IN ('Menunggu Persetujuan', 'Menunggu Pertimbangan Atasan', 'Menunggu Persetujuan Atasan') THEN 1 END) as admin_screening,
                        COUNT(CASE WHEN (nomor_surat IS NULL OR nomor_surat = '') AND status = ? THEN 1 END) as admin_nomor,
                        COUNT(CASE WHEN verifikasi_admin_at IS NOT NULL AND paraf_kapokja_at IS NULL AND status NOT IN ('Disetujui', 'Ditolak', 'Dibatalkan') THEN 1 END) as pokja_pending,
                        COUNT(CASE WHEN paraf_kapokja_at IS NOT NULL AND (pertimbangan_atasan IS NULL OR pertimbangan_atasan = '' OR pertimbangan_atasan = 'Menunggu Persetujuan') AND status NOT IN ('Disetujui', 'Ditolak', 'Dibatalkan') THEN 1 END) as kabag_pending
                    ", [\App\Models\PengajuanCuti::STATUS_DISETUJUI])->first();

                    $adminScreeningKarir = (int) ($karirStats->admin_screening ?? 0);
                    $adminScreeningSurat = (int) ($suratStats->admin_screening ?? 0);
                    $adminScreeningSatya = (int) ($satyaStats->admin_screening ?? 0);
                    $adminScreeningCuti  = (int) ($cutiStats->admin_screening ?? 0);
                    $adminScreeningTotal = $adminScreeningKarir + $adminScreeningSurat + $adminScreeningSatya + $adminScreeningCuti;

                    $adminNomorSuratCount = (int) ($suratStats->admin_nomor ?? 0);
                    $adminNomorKarirCount = (int) ($karirStats->admin_nomor ?? 0);
                    $adminNomorSatyaCount = (int) ($satyaStats->admin_nomor ?? 0);
                    $adminNomorCutiCount  = (int) ($cutiStats->admin_nomor ?? 0);
                    $adminNomorTotal      = $adminNomorSuratCount + $adminNomorKarirCount + $adminNomorSatyaCount + $adminNomorCutiCount;

                    $pokjaPendingKarir = (int) ($karirStats->pokja_pending ?? 0);
                    $pokjaPendingSurat = (int) ($suratStats->pokja_pending ?? 0);
                    $pokjaPendingSatya = (int) ($satyaStats->pokja_pending ?? 0);
                    $pokjaPendingCuti  = (int) ($cutiStats->pokja_pending ?? 0);
                    $pokjaPendingTotal = $pokjaPendingKarir + $pokjaPendingSurat + $pokjaPendingSatya + $pokjaPendingCuti;

                    $kabagPendingKarir = (int) ($karirStats->kabag_pending ?? 0);
                    $kabagPendingSurat = (int) ($suratStats->kabag_pending ?? 0);
                    $kabagPendingSatya = (int) ($satyaStats->kabag_pending ?? 0);
                    $kabagPendingCuti  = (int) ($cutiStats->kabag_pending ?? 0);
                    $kabagPendingTotal = $kabagPendingKarir + $kabagPendingSurat + $kabagPendingSatya + $kabagPendingCuti;

                    $wd2PendingKarir = (int) ($karirStats->wd2_pending ?? 0);
                    $wd2PendingSurat = (int) ($suratStats->wd2_pending ?? 0);
                    $wd2PendingSatya = (int) ($satyaStats->wd2_pending ?? 0);
                    $wd2PendingTotal = $wd2PendingKarir + $wd2PendingSurat + $wd2PendingSatya;

                    $dekanPendingSurat = (int) ($suratStats->dekan_pending ?? 0);
                    $dekanPendingKarir = (int) ($karirStats->dekan_pending ?? 0);
                    $dekanPendingTotal = $dekanPendingSurat + $dekanPendingKarir;

                    return [
                        'admin_screening_total' => $adminScreeningTotal,
                        'admin_screening_karir' => $adminScreeningKarir,
                        'admin_screening_surat' => $adminScreeningSurat,
                        'admin_screening_satya' => $adminScreeningSatya,
                        'admin_screening_cuti'  => $adminScreeningCuti,
                        'admin_nomor_total'     => $adminNomorTotal,
                        'admin_nomor_surat'     => $adminNomorSuratCount,
                        'admin_nomor_karir'     => $adminNomorKarirCount,
                        'admin_nomor_satya'     => $adminNomorSatyaCount,
                        'admin_nomor_cuti'      => $adminNomorCutiCount,
                        'pokja_pending_total'   => $pokjaPendingTotal,
                        'pokja_pending_karir'   => $pokjaPendingKarir,
                        'pokja_pending_surat'   => $pokjaPendingSurat,
                        'pokja_pending_satya'   => $pokjaPendingSatya,
                        'pokja_pending_cuti'    => $pokjaPendingCuti,
                        'kabag_pending_total'   => $kabagPendingTotal,
                        'kabag_pending_karir'   => $kabagPendingKarir,
                        'kabag_pending_surat'   => $kabagPendingSurat,
                        'kabag_pending_satya'   => $kabagPendingSatya,
                        'kabag_pending_cuti'    => $kabagPendingCuti,
                        'wd2_pending_total'     => $wd2PendingTotal,
                        'wd2_pending_karir'     => $wd2PendingKarir,
                        'wd2_pending_surat'     => $wd2PendingSurat,
                        'wd2_pending_satya'     => $wd2PendingSatya,
                        'dekan_pending_total'   => $dekanPendingTotal,
                        'dekan_pending_surat'   => $dekanPendingSurat,
                        'dekan_pending_karir'   => $dekanPendingKarir,
                    ];
                });
                $data['workflowStageLists'] = Cache::remember('dashboard_workflow_stage_lists', 60, function() {
                    return $this->getWorkflowStageLists();
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard workflow stats error: ' . $e->getMessage());
                $data['workflowStats'] = $defaultWorkflowStats;
                $data['workflowStageLists'] = [1 => [], 2 => [], 3 => [], 4 => []];
            }
        } else {
            $data['workflowStageLists'] = [1 => [], 2 => [], 3 => [], 4 => []];
        }

        return view('dashboard', $data);
    }

    /**
     * Mengumpulkan daftar berkas riil yang sedang menunggu tindakan di 4 tahapan SOP Pengesahan Berjenjang:
     * 1. Screening Admin
     * 2. Paraf Ka Pokja
     * 3. Paraf / Telaah Kabag Umum
     * 4. TTE Pimpinan (WD II / Dekan / PYBMC)
     */
    private function getWorkflowStageLists(): array
    {
        $stages = [
            1 => [],
            2 => [],
            3 => [],
            4 => [],
        ];

        $formatNama = function ($pegawai) {
            if (!$pegawai) return 'Pegawai SIKAP';
            return trim(($pegawai->gelar_depan ? $pegawai->gelar_depan . ' ' : '') . $pegawai->nama . ($pegawai->gelar_belakang ? ', ' . $pegawai->gelar_belakang : ''));
        };

        // 1. Layanan Surat (E-Surat Mandiri)
        try {
            $suratList = LayananSurat::with(['pegawai.unitKerja'])
                ->whereNotIn('status', ['ditolak', 'selesai', 'dibatalkan'])
                ->latest('id')
                ->take(50)
                ->get();

            foreach ($suratList as $s) {
                $namaJenis = ucwords(str_replace('_', ' ', $s->jenis_surat ?? 'Surat Keterangan'));
                $item = [
                    'id'           => $s->id,
                    'modul'        => 'surat',
                    'modul_label'  => 'E-Surat Mandiri',
                    'modul_icon'   => '📄',
                    'judul'        => $namaJenis,
                    'nomor'        => $s->nomor_surat ?: 'Belum bernomor',
                    'pegawai_nama' => $formatNama($s->pegawai),
                    'pegawai_nip'  => $s->pegawai?->nip ?? '-',
                    'unit_kerja'   => $s->pegawai?->unitKerja?->nama_unit ?? 'Fakultas Keperawatan',
                    'metode_ttd'   => $s->metode_ttd ?: 'elektronik',
                    'tanggal'      => $s->created_at ? $s->created_at->translatedFormat('d M Y') : '-',
                    'status'       => $s->status,
                    'url'          => route('layanan-surat.index', array_filter(['search' => $s->pegawai?->nip ?: $s->pegawai?->nama])),
                    'url_modul'    => route('layanan-surat.index'),
                ];

                if (is_null($s->verifikasi_admin_at) && $s->status === 'diajukan') {
                    $stages[1][] = $item;
                } elseif (!is_null($s->verifikasi_admin_at) && is_null($s->paraf_kapokja_at)) {
                    $stages[2][] = $item;
                } elseif (!is_null($s->paraf_kapokja_at) && is_null($s->paraf_kabag_at)) {
                    $stages[3][] = $item;
                } elseif (!is_null($s->paraf_kabag_at) && is_null($s->ttd_pimpinan_at)) {
                    $stages[4][] = $item;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard stage list surat error: ' . $e->getMessage());
        }

        // 2. Pengajuan Karir (KP & KGB)
        try {
            $karirList = PengajuanKarir::with(['pegawai.unitKerja', 'golonganTujuan'])
                ->whereNotIn('status', ['ditolak', 'selesai', 'dibatalkan'])
                ->latest('id')
                ->take(50)
                ->get();

            foreach ($karirList as $k) {
                $judul = 'Usulan ' . ($k->jenis_pengajuan ?: 'Karir');
                if ($k->golonganTujuan) {
                    $judul .= ' (' . $k->golonganTujuan->nama_golongan . ')';
                }
                $item = [
                    'id'           => $k->id,
                    'modul'        => 'karir',
                    'modul_label'  => 'Usulan ' . ($k->jenis_pengajuan ?: 'KP & KGB'),
                    'modul_icon'   => '🎖️',
                    'judul'        => $judul,
                    'nomor'        => $k->nomor_surat ?: 'Belum bernomor',
                    'pegawai_nama' => $formatNama($k->pegawai),
                    'pegawai_nip'  => $k->pegawai?->nip ?? '-',
                    'unit_kerja'   => $k->pegawai?->unitKerja?->nama_unit ?? 'Fakultas Keperawatan',
                    'metode_ttd'   => $k->metode_ttd ?: 'elektronik',
                    'tanggal'      => $k->created_at ? $k->created_at->translatedFormat('d M Y') : '-',
                    'status'       => $k->status,
                    'url'          => route('pengajuan-karir.show', $k->id),
                    'url_modul'    => route('pengajuan-karir.index'),
                ];

                if (is_null($k->verifikasi_admin_at) && $k->status === 'diajukan') {
                    $stages[1][] = $item;
                } elseif (!is_null($k->verifikasi_admin_at) && is_null($k->paraf_kapokja_at)) {
                    $stages[2][] = $item;
                } elseif (!is_null($k->paraf_kapokja_at) && is_null($k->paraf_kabag_at)) {
                    $stages[3][] = $item;
                } elseif (!is_null($k->paraf_kabag_at) && (is_null($k->paraf_wd2_at) || is_null($k->ttd_dekan_at))) {
                    $stages[4][] = $item;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard stage list karir error: ' . $e->getMessage());
        }

        // 3. Usulan Satyalancana
        try {
            $satyaList = UsulanSatyalancana::with(['pegawai.unitKerja'])
                ->whereNotIn('status', ['ditolak', 'selesai', 'dibatalkan'])
                ->latest('id')
                ->take(50)
                ->get();

            foreach ($satyaList as $st) {
                $kat = trim($st->kategori ?? '');
                if ($kat && !str_ends_with(strtolower($kat), 'tahun')) {
                    $kat .= ' Tahun';
                }
                $judul = 'Satyalancana ' . ($kat ?: 'Karya Satya');
                $item = [
                    'id'           => $st->id,
                    'modul'        => 'satya',
                    'modul_label'  => 'Usulan Satyalancana',
                    'modul_icon'   => '🏅',
                    'judul'        => $judul,
                    'nomor'        => $st->nomor_surat_usulan ?: 'Belum bernomor',
                    'pegawai_nama' => $formatNama($st->pegawai),
                    'pegawai_nip'  => $st->pegawai?->nip ?? '-',
                    'unit_kerja'   => $st->pegawai?->unitKerja?->nama_unit ?? 'Fakultas Keperawatan',
                    'metode_ttd'   => $st->metode_ttd ?: 'elektronik',
                    'tanggal'      => $st->created_at ? $st->created_at->translatedFormat('d M Y') : '-',
                    'status'       => $st->status,
                    'url'          => route('satyalancana.show', $st->id),
                    'url_modul'    => route('satyalancana.index'),
                ];

                if (is_null($st->verifikasi_admin_at) && in_array($st->status, ['diajukan', 'draft'])) {
                    $stages[1][] = $item;
                } elseif (!is_null($st->verifikasi_admin_at) && is_null($st->paraf_kapokja_at)) {
                    $stages[2][] = $item;
                } elseif (!is_null($st->paraf_kapokja_at) && is_null($st->paraf_kabag_at)) {
                    $stages[3][] = $item;
                } elseif (!is_null($st->paraf_kabag_at) && is_null($st->ttd_wd2_at)) {
                    $stages[4][] = $item;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard stage list satya error: ' . $e->getMessage());
        }

        // 4. Pengajuan Cuti (E-Cuti Pegawai)
        try {
            $cutiList = PengajuanCuti::with(['pegawai.unitKerja'])
                ->whereNotIn('status', ['Disetujui', 'Ditolak', 'Dibatalkan', 'disetujui', 'ditolak', 'dibatalkan'])
                ->latest('id')
                ->take(50)
                ->get();

            foreach ($cutiList as $c) {
                $judul = ($c->jenis_cuti ?: 'Pengajuan Cuti') . ' (' . ($c->jumlah_hari ?: 1) . ' Hari)';
                $item = [
                    'id'           => $c->id,
                    'modul'        => 'cuti',
                    'modul_label'  => 'E-Cuti Pegawai',
                    'modul_icon'   => '🏖️',
                    'judul'        => $judul,
                    'nomor'        => $c->nomor_surat ?: 'Belum bernomor',
                    'pegawai_nama' => $formatNama($c->pegawai),
                    'pegawai_nip'  => $c->pegawai?->nip ?? '-',
                    'unit_kerja'   => $c->pegawai?->unitKerja?->nama_unit ?? 'Fakultas Keperawatan',
                    'metode_ttd'   => $c->metode_ttd ?: 'elektronik',
                    'tanggal'      => $c->created_at ? $c->created_at->translatedFormat('d M Y') : '-',
                    'status'       => $c->status,
                    'url'          => route('pengajuan-cuti.show', $c->id),
                    'url_modul'    => route('pengajuan-cuti.index'),
                ];

                if (is_null($c->verifikasi_admin_at) && in_array($c->status, ['Menunggu Persetujuan', 'Menunggu Pertimbangan Atasan', 'Menunggu Persetujuan Atasan'])) {
                    $stages[1][] = $item;
                } elseif (!is_null($c->verifikasi_admin_at) && is_null($c->paraf_kapokja_at)) {
                    $stages[2][] = $item;
                } elseif (!is_null($c->paraf_kapokja_at) && (is_null($c->pertimbangan_atasan) || $c->pertimbangan_atasan === '' || $c->pertimbangan_atasan === 'Menunggu Persetujuan')) {
                    $stages[3][] = $item;
                } elseif ($c->status === PengajuanCuti::STATUS_DISETUJUI_ATASAN || ($c->pertimbangan_atasan === 'Disetujui' && is_null($c->approved_at))) {
                    $stages[4][] = $item;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Dashboard stage list cuti error: ' . $e->getMessage());
        }

        return $stages;
    }
}