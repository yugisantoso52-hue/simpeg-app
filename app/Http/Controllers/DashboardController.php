<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
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

        // Cek identitas & peran user
        $isAdmin  = $user->hasRole('admin');
        $isAtasan = $user->isAtasan() || $user->hasRole('pimpinan') || $user->canAccessExecutiveKepegawaianMenus();
        $isDekan  = $user->isDekan();
        $isWadek2 = $user->isWadek2();
        $isKabag  = $user->isKabagUmum();
        $isPokja  = $user->isKaPokjaKepeg();

        $data['isAdmin']  = $isAdmin;
        $data['isAtasan'] = $isAtasan;
        $data['isDekan']  = $isDekan;
        $data['isWadek2'] = $isWadek2;
        $data['isKabag']  = $isKabag;
        $data['isPokja']  = $isPokja;

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
            try {
                $data['facultyCompleteness'] = Cache::remember('dashboard_faculty_completeness', 600, fn() => PegawaiCompletenessService::getFacultyCompleteness());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard facultyCompleteness error: ' . $e->getMessage());
                $data['facultyCompleteness'] = [];
            }

            // Ringkasan Eksekutif Analisis Jabatan & Formasi Beban Kerja (Anjab & ABK)
            try {
                $data['abkSummary'] = Cache::remember('dashboard_abk_summary', 600, function() {
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
                    })->values();

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
                    'items'              => collect(),
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
            try {
                $talentScoringService = app(TalentScoringService::class);
                if (!TalentMapping::where('tahun', $currentYear)->exists()) {
                    \App\Jobs\BatchCalculateTalentJob::dispatch($currentYear);
                }
                $data['talentSummary'] = Cache::remember("talent_summary_{$currentYear}", 600, fn() => $talentScoringService->getNineBoxDistribution($currentYear));
                $data['totalSuccessionPlans'] = Cache::remember('total_succession_plans', 600, fn() => SuccessionPlan::count());
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard talent summary error: ' . $e->getMessage());
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

                    // 4. Pengajuan Cuti Agregasi
                    $cutiStats = \App\Models\PengajuanCuti::selectRaw("
                        COUNT(CASE WHEN status IN ('Menunggu Persetujuan', 'Menunggu Pertimbangan Atasan', 'Menunggu Persetujuan Atasan') THEN 1 END) as admin_screening,
                        COUNT(CASE WHEN (nomor_surat IS NULL OR nomor_surat = '') AND status = ? THEN 1 END) as admin_nomor
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
                    $pokjaPendingTotal = $pokjaPendingKarir + $pokjaPendingSurat + $pokjaPendingSatya;

                    $kabagPendingKarir = (int) ($karirStats->kabag_pending ?? 0);
                    $kabagPendingSurat = (int) ($suratStats->kabag_pending ?? 0);
                    $kabagPendingSatya = (int) ($satyaStats->kabag_pending ?? 0);
                    $kabagPendingTotal = $kabagPendingKarir + $kabagPendingSurat + $kabagPendingSatya;

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
                        'kabag_pending_total'   => $kabagPendingTotal,
                        'kabag_pending_karir'   => $kabagPendingKarir,
                        'kabag_pending_surat'   => $kabagPendingSurat,
                        'kabag_pending_satya'   => $kabagPendingSatya,
                        'wd2_pending_total'     => $wd2PendingTotal,
                        'wd2_pending_karir'     => $wd2PendingKarir,
                        'wd2_pending_surat'     => $wd2PendingSurat,
                        'wd2_pending_satya'     => $wd2PendingSatya,
                        'dekan_pending_total'   => $dekanPendingTotal,
                        'dekan_pending_surat'   => $dekanPendingSurat,
                        'dekan_pending_karir'   => $dekanPendingKarir,
                    ];
                });
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Dashboard workflow stats error: ' . $e->getMessage());
                $data['workflowStats'] = $defaultWorkflowStats;
            }
        }

        return view('dashboard', $data);
    }
}