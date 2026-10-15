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

            // 🎯 Ringkasan Eksekutif Manajemen Talenta ASN (PermenPAN-RB No. 3/2020)
            $talentScoringService = app(TalentScoringService::class);
            $currentYear = (int) $now->year;
            if (!TalentMapping::where('tahun', $currentYear)->exists()) {
                $talentScoringService->batchCalculate($currentYear);
            }
            $data['talentSummary'] = $talentScoringService->getNineBoxDistribution($currentYear);
            $data['totalSuccessionPlans'] = SuccessionPlan::count();

            // 🌟 WORKFLOW ACTION CENTER: INBOX PENGESAHAN DOKUMEN & TATA NASKAH BERJENJANG 🌟
            // 1. Admin Kepegawaian (Tahap 1 Screening & Penomoran Surat)
            $adminScreeningKarir = \App\Models\PengajuanKarir::whereNull('verifikasi_admin_at')->where('status', 'diajukan')->count();
            $adminScreeningSurat = \App\Models\LayananSurat::whereNull('verifikasi_admin_at')->where('status', 'diajukan')->count();
            $adminScreeningSatya = \App\Models\UsulanSatyalancana::whereNull('verifikasi_admin_at')->whereIn('status', ['diajukan', 'draft'])->count();
            $adminScreeningCuti  = \App\Models\PengajuanCuti::whereIn('status', ['Menunggu Persetujuan', 'Menunggu Pertimbangan Atasan', 'Menunggu Persetujuan Atasan'])->count();
            $adminScreeningTotal = $adminScreeningKarir + $adminScreeningSurat + $adminScreeningSatya + $adminScreeningCuti;

            $adminNomorSuratCount = \App\Models\LayananSurat::where(fn($q) => $q->whereNull('nomor_surat')->orWhere('nomor_surat', ''))->whereIn('status', ['diparaf_kabag', 'disetujui', 'selesai'])->count();
            $adminNomorKarirCount = \App\Models\PengajuanKarir::where(fn($q) => $q->whereNull('nomor_surat')->orWhere('nomor_surat', ''))->whereIn('status', ['diverifikasi_wd2', 'disetujui_wd2', 'disetujui_dekan', 'selesai'])->count();
            $adminNomorSatyaCount = \App\Models\UsulanSatyalancana::where(fn($q) => $q->whereNull('nomor_surat_usulan')->orWhere('nomor_surat_usulan', ''))->whereIn('status', ['diparaf_kabag', 'disetujui_wd2', 'selesai'])->count();
            $adminNomorCutiCount  = \App\Models\PengajuanCuti::where(fn($q) => $q->whereNull('nomor_surat')->orWhere('nomor_surat', ''))->where('status', \App\Models\PengajuanCuti::STATUS_DISETUJUI)->count();
            $adminNomorTotal      = $adminNomorSuratCount + $adminNomorKarirCount + $adminNomorSatyaCount + $adminNomorCutiCount;

            // 2. Ka Pokja Keu-Kepeg (Tahap 2: Paraf Pokja)
            $pokjaPendingKarir = \App\Models\PengajuanKarir::whereNotNull('verifikasi_admin_at')->whereNull('paraf_kapokja_at')->whereNotIn('status', ['ditolak', 'selesai'])->count();
            $pokjaPendingSurat = \App\Models\LayananSurat::whereNotNull('verifikasi_admin_at')->whereNull('paraf_kapokja_at')->whereNotIn('status', ['ditolak', 'selesai'])->count();
            $pokjaPendingSatya = \App\Models\UsulanSatyalancana::whereNotNull('verifikasi_admin_at')->whereNull('paraf_kapokja_at')->whereNotIn('status', ['ditolak', 'selesai'])->count();
            $pokjaPendingTotal = $pokjaPendingKarir + $pokjaPendingSurat + $pokjaPendingSatya;

            // 3. Kabag Umum (Tahap 3: Paraf Telaah Tata Naskah Kabag)
            $kabagPendingKarir = \App\Models\PengajuanKarir::whereNotNull('paraf_kapokja_at')->whereNull('paraf_kabag_at')->whereNotIn('status', ['ditolak', 'selesai'])->count();
            $kabagPendingSurat = \App\Models\LayananSurat::whereNotNull('paraf_kapokja_at')->whereNull('paraf_kabag_at')->whereNotIn('status', ['ditolak', 'selesai'])->count();
            $kabagPendingSatya = \App\Models\UsulanSatyalancana::whereNotNull('paraf_kapokja_at')->whereNull('paraf_kabag_at')->whereNotIn('status', ['ditolak', 'selesai'])->count();
            $kabagPendingTotal = $kabagPendingKarir + $kabagPendingSurat + $kabagPendingSatya;

            // 4. Wakil Dekan II (Tahap 4: TTE / Persetujuan WD2)
            $wd2PendingKarir = \App\Models\PengajuanKarir::whereNotNull('paraf_kabag_at')->whereNull('paraf_wd2_at')->whereNotIn('status', ['ditolak', 'disetujui_wd2', 'selesai'])->count();
            $wd2PendingSurat = \App\Models\LayananSurat::whereNotNull('paraf_kabag_at')->whereNotIn('status', ['ditolak', 'selesai'])->where(fn($q) => $q->where('penandatangan_jabatan', 'like', '%WAKIL DEKAN%')->orWhere('penandatangan_jabatan', 'like', '%WD%'))->count();
            $wd2PendingSatya = \App\Models\UsulanSatyalancana::whereNotNull('paraf_kabag_at')->whereNull('ttd_wd2_at')->whereNotIn('status', ['ditolak', 'selesai'])->count();
            $wd2PendingTotal = $wd2PendingKarir + $wd2PendingSurat + $wd2PendingSatya;

            // 5. Dekan (Tahap 4: TTE Dekan)
            $dekanPendingSurat = \App\Models\LayananSurat::whereNotNull('paraf_kabag_at')->whereNotIn('status', ['ditolak', 'selesai'])->where(fn($q) => $q->where('penandatangan_jabatan', 'like', '%DEKAN%')->where('penandatangan_jabatan', 'not like', '%WAKIL%'))->count();
            $dekanPendingKarir = \App\Models\PengajuanKarir::whereNotNull('paraf_wd2_at')->whereNull('ttd_dekan_at')->whereNotIn('status', ['ditolak', 'selesai'])->count();
            $dekanPendingTotal = $dekanPendingSurat + $dekanPendingKarir;

            $data['workflowStats'] = [
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
        }

        return view('dashboard', $data);
    }
}