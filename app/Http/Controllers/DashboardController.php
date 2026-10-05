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

        // Jika user yang login adalah Pegawai Perorangan (Bukan Admin/Pimpinan)
        if ($user->hasRole('pegawai')) {
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
            }
        }

        // Jika user yang login adalah Admin / Pimpinan / Atasan Langsung / Pejabat Eksekutif
        $isAdmin = $user->hasRole('admin');
        $isAtasan = $user->isAtasan() || $user->hasRole('pimpinan') || $user->canAccessExecutiveKepegawaianMenus();

        if ($isAdmin || $isAtasan) {
            $data['facultyCompleteness'] = PegawaiCompletenessService::getFacultyCompleteness();

            // Ringkasan Eksekutif Analisis Jabatan & Formasi Beban Kerja (Anjab & ABK)
            // Acuan: PermenPAN-RB No. 1/2020 & Peraturan BKN No. 12 & 19/2011
            $anjabs = AnalisisJabatan::with(['jabatan', 'unitKerja', 'uraianTugas'])->get()->sortByDesc('hierarchy_order')->values();
            $totalKebutuhan = (int) $anjabs->sum('formasi_pembulatan');
            $totalBezetting = (int) $anjabs->sum('bezetting');
            $rasioKeterisian = $totalKebutuhan > 0 ? round(($totalBezetting / $totalKebutuhan) * 100, 1) : 0;

            $jabatanKurang = $anjabs->filter(fn($a) => $a->selisih_formasi < 0);
            $totalDefisit = (int) $jabatanKurang->sum(fn($a) => abs($a->selisih_formasi));
            $totalIdeal = $anjabs->filter(fn($a) => $a->selisih_formasi == 0)->count();
            $totalLebih = (int) $anjabs->filter(fn($a) => $a->selisih_formasi > 0)->sum('selisih_formasi');

            // Top formasi yang paling defisit / mendesak untuk diusulkan formasi baru
            $prioritasDefisit = $jabatanKurang->sortBy(fn($a) => $a->selisih_formasi)->take(6)->values();

            // Koleksi lengkap data jabatan teranalisis untuk interaktivitas kartu & tabel (Alpine.js)
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

            $data['abkSummary'] = [
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

            // Tambahan Metrik Manajerial (Action Items & Kinerja Terkini)
            $now = Carbon::now('Asia/Jakarta');
            $bawahanIds = (!$isAdmin && $isAtasan) ? $user->getBawahanIds() : null;

            // Hitung cuti pending yang memerlukan tindakan verifikasi / keputusan user ini
            $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
            $data['pendingCutiCount'] = $hierarchyService->countPendingCutiForUser($user);

            $logbookQuery = Logbook::where('status', Logbook::STATUS_DIAJUKAN);
            if ($bawahanIds !== null) {
                $logbookQuery->whereIn('pegawai_id', $bawahanIds);
            }

            $data['pendingLogbookCount'] = $logbookQuery->count();
            $data['todayPresentCount'] = Attendance::whereDate('attendance_date', $now->toDateString())->count();
            $data['adminLogbookStats'] = app(LogbookService::class)->getAdminStatistics($now->month, $now->year, null, $bawahanIds);
            $data['isAtasan'] = $isAtasan;

            // 🎯 Ringkasan Eksekutif Manajemen Talenta ASN (PermenPAN-RB No. 3/2020)
            $talentScoringService = app(TalentScoringService::class);
            $currentYear = (int) $now->year;
            if (!TalentMapping::where('tahun', $currentYear)->exists()) {
                $talentScoringService->batchCalculate($currentYear);
            }
            $data['talentSummary'] = $talentScoringService->getNineBoxDistribution($currentYear);
            $data['totalSuccessionPlans'] = SuccessionPlan::count();
        }

        return view('dashboard', $data);
    }
}