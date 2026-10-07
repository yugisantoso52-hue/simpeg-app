<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\UnitKerjaController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\GolonganController;
use App\Http\Controllers\RiwayatPendidikanController;
use App\Http\Controllers\RiwayatJabatanController;
use App\Http\Controllers\RiwayatPangkatController;
use App\Http\Controllers\RiwayatDiklatController;
use App\Http\Controllers\RiwayatStrSipController;
use App\Http\Controllers\RiwayatSkpController;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\TugasBelajarController;
use App\Http\Controllers\MutasiPegawaiController;
use App\Http\Controllers\KgbController;
use App\Http\Controllers\KpController;
use App\Http\Controllers\SatyalancanaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RiwayatPenghargaanController;
use App\Http\Controllers\RiwayatOrganisasiController;
use App\Http\Controllers\RiwayatPublikasiController;
use App\Http\Controllers\CloudSyncController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\JenisJabatanController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AttendanceManageController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\Admin\LogbookManageController;
use App\Http\Controllers\TalentManagementController;

/*
|--------------------------------------------------------------------------
| Web Routes - SIMPEG Enterprise (Production Ready)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/* Public Verification Endpoint (QR Code Document Scanner) */
Route::get('/verifikasi-dokumen/{code}', [\App\Http\Controllers\PublicVerificationController::class, 'verify'])->name('verify.document');

/* Automated 1-Click Deployment Webhook */
Route::match(['get', 'post'], '/sistem-auto-update', [\App\Http\Controllers\DeployWebhookController::class, 'handle'])->name('deploy.webhook');
Route::match(['get', 'post'], '/deploy-webhook', [\App\Http\Controllers\DeployWebhookController::class, 'handle']);
Route::match(['get', 'post'], '/api/deploy-webhook', [\App\Http\Controllers\DeployWebhookController::class, 'handle']);

/* PWA Offline Fallback */
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// AUTHENTICATED USERS (Semua User Login)
Route::middleware(['auth', 'force.password.change'])->group(function () {

    /* Dashboard & Profile Akun User */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /* Notifications & Document Preview & Foto Pegawai */
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'readAndRedirect'])->name('notifications.readAndRedirect');
    Route::get('/document-preview/{path}', [ReportController::class, 'streamPrivateFile'])->where('path', '.*')->name('document.preview');
    Route::get('/pegawai/{pegawai}/foto', [PegawaiController::class, 'foto'])->name('pegawai.foto');

    /* Modul Presensi Karyawan (GPS Geolocation & Selfie) */
    Route::get('/presensi', [AttendanceController::class, 'index'])->name('presensi.index');
    Route::post('/presensi', [AttendanceController::class, 'store'])->name('presensi.store');
    Route::get('/presensi/riwayat', [AttendanceController::class, 'history'])->name('presensi.history');
    Route::get('/presensi/foto/{id}/{type}', [AttendanceController::class, 'streamPhoto'])->name('presensi.photo');
    Route::get('/presensi/token', [AttendanceController::class, 'refreshToken'])->name('presensi.token');

    /* Modul E-Logbook Kinerja Harian Pegawai */
    Route::prefix('logbook')->name('logbook.')->group(function () {
        Route::get('/', [LogbookController::class, 'index'])->name('index');
        Route::get('/create', [LogbookController::class, 'create'])->name('create');
        Route::post('/', [LogbookController::class, 'store'])->name('store');
        Route::get('/export/pdf', [LogbookController::class, 'exportPdf'])->name('export.pdf');
        Route::get('/export/excel', [LogbookController::class, 'exportExcel'])->name('export.excel');
        Route::post('/submit-bulk', [LogbookController::class, 'submitBulk'])->name('submit-bulk');
        Route::get('/{id}', [LogbookController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LogbookController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LogbookController::class, 'update'])->name('update');
        Route::delete('/{id}', [LogbookController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/submit', [LogbookController::class, 'submit'])->name('submit');
    });

    /* Transparansi Talenta Mandiri (Semua ASN) */
    Route::get('/talenta-saya', [TalentManagementController::class, 'myTalent'])->name('manajemen-talenta.my-talent');

    // ======================================================================
    // ROUTE PEGAWAI BIASA (Akses Data Diri Sendiri)
    // ======================================================================
    Route::middleware(['role:pegawai'])->group(function () {
        Route::get('/my-profile', function() {
            $user = auth()->user();
            if (!$user->pegawai_id) {
                return redirect()->route('dashboard')->with('error', 'Akun Anda belum terhubung dengan data pegawai.');
            }
            return redirect()->route('pegawai.show', $user->pegawai_id);
        })->name('pegawai.my-profile');
    });

    // ======================================================================
    // ROUTE EDIT PEGAWAI & CRUD RIWAYAT MANDIRI (ADMIN & PEGAWAI)
    // ======================================================================
    Route::middleware(['role:admin,pegawai'])->group(function () {
        Route::get('/pegawai/{pegawai}/edit', [PegawaiController::class, 'edit'])->name('pegawai.edit');
        Route::put('/pegawai/{pegawai}', [PegawaiController::class, 'update'])->name('pegawai.update');

        // CRUD Riwayat Mandiri Pegawai (Pangkat, Jabatan, Mutasi, STR/SIP, Tubel, SKP, Penghargaan, Organisasi, Publikasi, Pendidikan, Diklat)
        Route::resource('riwayat-pangkat', RiwayatPangkatController::class)->except(['index', 'show']);
        Route::resource('riwayat-jabatan', RiwayatJabatanController::class)->except(['index', 'show']);
        Route::resource('mutasi-pegawai', MutasiPegawaiController::class)->parameters(['mutasi-pegawai' => 'mutasi_pegawai'])->except(['index', 'show']);
        Route::get('/pegawai-mutasi/{id}', [MutasiPegawaiController::class, 'getPegawai'])->name('pegawai-mutasi');
        Route::resource('riwayat-pendidikan', RiwayatPendidikanController::class)->except(['index', 'show']);
        Route::resource('riwayat-diklat', RiwayatDiklatController::class)->except(['index', 'show']);
        Route::resource('riwayat-str-sip', RiwayatStrSipController::class)->except(['index', 'show']);
        Route::resource('tugas-belajar', TugasBelajarController::class)->except(['index', 'show']);
        Route::resource('riwayat-skp', RiwayatSkpController::class)->except(['index', 'show']);
        Route::resource('riwayat-penghargaan', RiwayatPenghargaanController::class)->except(['show']);
        Route::resource('riwayat-organisasi', RiwayatOrganisasiController::class)->except(['show']);
        Route::resource('riwayat-publikasi', RiwayatPublikasiController::class)->except(['show']);
    });

    // ======================================================================
    // ADMIN KEPEGAWAIAN (FULL WRITE & MANAGEMENT ACCESS)
    // ======================================================================
    Route::middleware(['role:admin'])->group(function () {

        Route::resource('unit-kerja', UnitKerjaController::class)->parameters(['unit-kerja' => 'unit_kerja']);
        Route::resource('jabatan', JabatanController::class)->parameters(['jabatan' => 'jabatan']);
        Route::resource('golongan', GolonganController::class)->parameters(['golongan' => 'golongan']);
        Route::resource('jenis-jabatan', JenisJabatanController::class)->parameters(['jenis-jabatan' => 'jenis_jabatan']);

        // Mutasi (Index & Management Khusus Admin)
        Route::get('/mutasi-pegawai', [MutasiPegawaiController::class, 'index'])->name('mutasi-pegawai.index');

        // KGB & KP (Proses Transaksi Khusus Admin)
        Route::post('/kgb/proses/{id}', [KgbController::class, 'proses'])->name('kgb.proses');
        Route::post('/kenaikan-pangkat/proses/{id}', [KpController::class, 'proses'])->name('kp.proses');

        // Impor & Template
        Route::get('/pegawai/template', [PegawaiController::class, 'downloadTemplate'])->name('pegawai.template');
        Route::post('/pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');

        // Tambah & Hapus Pegawai (Khusus Admin)
        Route::get('/sync-from-cloud', [CloudSyncController::class, 'pullFromWeb'])->name('cloud-sync.pull-web');
        Route::get('/pegawai/create', [PegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::post('/pegawai/bulk-delete', [PegawaiController::class, 'bulkDelete'])->name('pegawai.bulk-delete');
        Route::delete('/pegawai/{pegawai}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');
        Route::delete('/pengajuan-cuti/{id}', [PengajuanCutiController::class, 'destroy'])->name('pengajuan-cuti.destroy');

        // Backup & Restore Database
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::get('/backup/export', [BackupController::class, 'export'])->name('backup.export');
        Route::post('/backup/restore', [BackupController::class, 'restore'])->name('backup.restore');
    });

    // ======================================================================
    // ADMIN, PIMPINAN & PEGAWAI (READ ACCESS / PROTECTED BY POLICY)
    // ======================================================================
    Route::middleware(['role:admin,pimpinan,pegawai'])->group(function () {
        Route::get('/pegawai/{pegawai}', [PegawaiController::class, 'show'])->name('pegawai.show');
        Route::get('/pegawai/{id}/download-pdf', [PegawaiController::class, 'exportProfilPdf'])->name('pegawai.download-pdf');

        /* Modul E-Cuti Pegawai (Self-Service) */
        Route::get('/pengajuan-cuti', [PengajuanCutiController::class, 'index'])->name('pengajuan-cuti.index');
        Route::get('/pengajuan-cuti/create', [PengajuanCutiController::class, 'create'])->name('pengajuan-cuti.create');
        Route::post('/pengajuan-cuti', [PengajuanCutiController::class, 'store'])->name('pengajuan-cuti.store');
        Route::get('/pengajuan-cuti/{id}', [PengajuanCutiController::class, 'show'])->name('pengajuan-cuti.show');
        Route::post('/pengajuan-cuti/{id}/cancel', [PengajuanCutiController::class, 'cancel'])->name('pengajuan-cuti.cancel');
        Route::get('/pengajuan-cuti/{id}/cetak-pdf', [PengajuanCutiController::class, 'cetakFormPdf'])->name('pengajuan-cuti.cetak-pdf');

        /* Modul Pengajuan Karir (KGB & KP Mandiri Pegawai) */
        Route::get('/pengajuan-karir', [\App\Http\Controllers\PengajuanKarirController::class, 'index'])->name('pengajuan-karir.index');
        Route::get('/pengajuan-karir/create', [\App\Http\Controllers\PengajuanKarirController::class, 'create'])->name('pengajuan-karir.create');
        Route::post('/pengajuan-karir', [\App\Http\Controllers\PengajuanKarirController::class, 'store'])->name('pengajuan-karir.store');
        Route::get('/pengajuan-karir/{id}', [\App\Http\Controllers\PengajuanKarirController::class, 'show'])->name('pengajuan-karir.show');
        Route::get('/reports/kgb/{id}/pdf', [ReportController::class, 'exportKgbPdf'])->name('reports.kgb.pdf');
        Route::get('/reports/kp/{id}/pdf', [ReportController::class, 'exportUsulanKpPdf'])->name('reports.kp.pdf');
    });

    // ======================================================================
    // APPROVAL CUTI, LOGBOOK & PRESENSI (ADMIN, PIMPINAN & ATASAN LANGSUNG)
    // ======================================================================
    Route::middleware(['role:admin,pimpinan,atasan'])->group(function () {
        /* Approval Pengajuan Cuti */
        Route::post('/pengajuan-cuti/{id}/approve', [PengajuanCutiController::class, 'approve'])->name('pengajuan-cuti.approve');

        /* Monitoring & Verifikasi Logbook Kinerja Pegawai */
        Route::prefix('admin/logbook')->name('admin.logbook.')->group(function () {
            Route::get('/', [LogbookManageController::class, 'index'])->name('index');
            Route::post('/bulk-verify', [LogbookManageController::class, 'bulkVerify'])->name('bulk-verify');
            Route::post('/verify-pegawai', [LogbookManageController::class, 'verifyPegawaiBulanan'])->name('verify-pegawai');
            Route::post('/{id}/verify', [LogbookManageController::class, 'verify'])->name('verify');
            Route::get('/export/pdf', [LogbookManageController::class, 'exportRekapPdf'])->name('export.pdf');
            Route::get('/export/excel', [LogbookManageController::class, 'exportRekapExcel'])->name('export.excel');
        });

        /* Rekap Presensi Karyawan & Bawahan (Admin, Pimpinan & Atasan) */
        Route::prefix('admin/presensi')->name('admin.presensi.')->group(function () {
            Route::get('/', [AttendanceManageController::class, 'index'])->name('index');
            Route::get('/export/pdf', [AttendanceManageController::class, 'exportPdf'])->name('export.pdf');
            Route::get('/export/excel', [AttendanceManageController::class, 'exportExcel'])->name('export.excel');
        });
    });

    // ======================================================================
    // ======================================================================
    // KHUSUS ADMIN & EXECUTIVE KEPEGAWAIAN (DEKAN, WADEK II, KABAG UMUM, KA POKJA KEU-KEPEG)
    // ======================================================================
    Route::middleware(['role:admin,executive_kepegawaian'])->group(function () {
        /* Executive Analytics Dashboard Dekanat & Pimpinan */
        Route::get('/pimpinan/analytics', [\App\Http\Controllers\Pimpinan\AnalyticsController::class, 'index'])->name('pimpinan.analytics');
        Route::get('/pimpinan/analytics/pdf', [\App\Http\Controllers\Pimpinan\AnalyticsController::class, 'exportPdf'])->name('pimpinan.analytics.pdf');
        Route::get('/pimpinan/executive-brief', [\App\Http\Controllers\Pimpinan\AnalyticsController::class, 'executiveBrief'])->name('pimpinan.executive-brief');

        /* Data Kepegawaian Berdasarkan Kategori (Dosen, Tendik, PHL) */
        Route::prefix('kepegawaian')->name('kepegawaian.')->group(function () {
            Route::get('/dosen', [PegawaiController::class, 'dosen'])->name('dosen.index');
            Route::get('/tendik', [PegawaiController::class, 'tendik'])->name('tendik.index');
            Route::get('/phl', [PegawaiController::class, 'phl'])->name('phl.index');
        });

        Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('/duk', [PegawaiController::class, 'duk'])->name('duk.index');
        Route::get('/reports/duk/pdf', [PegawaiController::class, 'exportDukPdf'])->name('reports.duk.pdf');
        Route::get('/reports/duk/excel', [PegawaiController::class, 'exportDukExcel'])->name('reports.duk.excel');

        /* Modul Karir & Monitoring Kepegawaian */
        Route::get('/kgb', [KgbController::class, 'index'])->name('kgb.index');
        Route::get('/kenaikan-pangkat', [KpController::class, 'index'])->name('kp.index');
        Route::get('/satyalancana', [SatyalancanaController::class, 'index'])->name('satyalancana.index');
        Route::get('/tugas-belajar', [TugasBelajarController::class, 'index'])->name('tugas-belajar.index');

        /* Read-Only Riwayat List */
        Route::get('/riwayat-pendidikan', [RiwayatPendidikanController::class, 'index'])->name('riwayat-pendidikan.index');
        Route::get('/riwayat-jabatan', [RiwayatJabatanController::class, 'index'])->name('riwayat-jabatan.index');
        Route::get('/riwayat-pangkat', [RiwayatPangkatController::class, 'index'])->name('riwayat-pangkat.index');
        Route::get('/riwayat-diklat', [RiwayatDiklatController::class, 'index'])->name('riwayat-diklat.index');
        Route::get('/riwayat-str-sip', [RiwayatStrSipController::class, 'index'])->name('riwayat-str-sip.index');
        Route::get('/riwayat-skp', [RiwayatSkpController::class, 'index'])->name('riwayat-skp.index');

        Route::get('/reports/reminder/pdf', [ReportController::class, 'exportReminderPdf'])->name('reports.reminder.pdf');
        Route::get('/reports/reminder/excel', [ReportController::class, 'exportReminderExcel'])->name('reports.reminder.excel');

        /* Verifikasi & Paraf Hirarkis Pengajuan Karir */
        Route::post('/pengajuan-karir/{id}/verifikasi', [\App\Http\Controllers\PengajuanKarirController::class, 'verifikasi'])->name('pengajuan-karir.verifikasi');

        /* Pengaturan Titik Lokasi Acuan Presensi (Admin & Dekanat) */
        Route::prefix('admin/presensi')->name('admin.presensi.')->group(function () {
            Route::get('/locations', [AttendanceManageController::class, 'locations'])->name('locations');
            Route::post('/{userId}/reset-location', [AttendanceManageController::class, 'resetLocation'])->name('reset-location');
            Route::delete('/{id}', [AttendanceManageController::class, 'destroy'])->name('destroy');
        });

        /* ======================================================================
         * MODUL ANALISIS JABATAN (ANJAB) & ANALISIS BEBAN KERJA (ABK)
         * Acuan: PermenPAN-RB No. 1/2020 & Peraturan BKN No. 12 & 19/2011
         * ====================================================================== */
        // Peta Jabatan Digital Interaktif
        Route::get('/anjab/peta-jabatan', [\App\Http\Controllers\PetaJabatanController::class, 'index'])->name('anjab.peta-jabatan');
        Route::get('/anjab/peta-jabatan/{jabatan}', [\App\Http\Controllers\PetaJabatanController::class, 'getJabatanDetail'])->name('anjab.peta-jabatan.detail');

        // Analisis Beban Kerja (ABK) & Formasi
        Route::get('/abk', [\App\Http\Controllers\AbkController::class, 'index'])->name('abk.index');
        Route::get('/abk/{anjab}/edit', [\App\Http\Controllers\AbkController::class, 'edit'])->name('abk.edit');
        Route::post('/abk/{anjab}/tugas', [\App\Http\Controllers\AbkController::class, 'storeTugas'])->name('abk.tugas.store');
        Route::put('/abk/tugas/{tugas}', [\App\Http\Controllers\AbkController::class, 'updateTugas'])->name('abk.tugas.update');
        Route::delete('/abk/tugas/{tugas}', [\App\Http\Controllers\AbkController::class, 'destroyTugas'])->name('abk.tugas.destroy');
        Route::get('/abk/print/rekap', [\App\Http\Controllers\AbkController::class, 'printRekap'])->name('abk.print.rekap');

        // Analisis Jabatan (E-Anjab - 17 Butir PermenPAN-RB No. 1/2020)
        Route::get('/anjab', [\App\Http\Controllers\AnjabController::class, 'index'])->name('anjab.index');
        Route::get('/anjab/create', [\App\Http\Controllers\AnjabController::class, 'create'])->name('anjab.create');
        Route::post('/anjab', [\App\Http\Controllers\AnjabController::class, 'store'])->name('anjab.store');
        Route::get('/anjab/{anjab}', [\App\Http\Controllers\AnjabController::class, 'show'])->name('anjab.show');
        Route::get('/anjab/{anjab}/edit', [\App\Http\Controllers\AnjabController::class, 'edit'])->name('anjab.edit');
        Route::put('/anjab/{anjab}', [\App\Http\Controllers\AnjabController::class, 'update'])->name('anjab.update');
        Route::delete('/anjab/{anjab}', [\App\Http\Controllers\AnjabController::class, 'destroy'])->name('anjab.destroy');
        Route::get('/anjab/{anjab}/print', [\App\Http\Controllers\AnjabController::class, 'print'])->name('anjab.print');

        /* ======================================================================
         * MODUL MANAJEMEN TALENTA ASN (PermenPAN-RB No. 3/2020 & UU No. 20/2023)
         * ====================================================================== */
        Route::prefix('manajemen-talenta')->name('manajemen-talenta.')->group(function () {
            Route::get('/', [TalentManagementController::class, 'index'])->name('index');
            Route::get('/rekap', [TalentManagementController::class, 'rekap'])->name('rekap');
            Route::post('/calculate', [TalentManagementController::class, 'calculate'])->name('calculate');
            Route::get('/pegawai/{pegawai}', [TalentManagementController::class, 'show'])->name('show');
            Route::post('/pegawai/{pegawai}/validasi', [TalentManagementController::class, 'validateStatus'])->name('validate');
            
            // Asesmen Kompetensi / Assessment Center BKN
            Route::get('/asesmen', [TalentManagementController::class, 'asesmenIndex'])->name('asesmen.index');
            Route::get('/asesmen/create', [TalentManagementController::class, 'asesmenCreate'])->name('asesmen.create');
            Route::post('/asesmen', [TalentManagementController::class, 'storeAsesmen'])->name('asesmen.store');
            Route::delete('/asesmen/{asesmen}', [TalentManagementController::class, 'destroyAsesmen'])->name('asesmen.destroy');

            // Rencana Suksesi Jabatan & Job Matching Anjab
            Route::get('/suksesi', [TalentManagementController::class, 'suksesiIndex'])->name('suksesi.index');
            Route::get('/suksesi/jabatan/{jabatan}', [TalentManagementController::class, 'suksesiJabatan'])->name('suksesi.jabatan');
            Route::post('/suksesi/nominasi', [TalentManagementController::class, 'storeSuksesiNominasi'])->name('suksesi.nominasi.store');
            Route::delete('/suksesi/nominasi/{plan}', [TalentManagementController::class, 'destroySuksesiNominasi'])->name('suksesi.nominasi.destroy');
        });
    });

    // ======================================================================
    // KHUSUS SUPERADMIN (AUDIT LOG & SYSTEM MAINTENANCE)
    // ======================================================================
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::post('/audit-logs/prune', [AuditLogController::class, 'prune'])->name('audit-logs.prune');
    });

    /* Fallback Route untuk modul yang masih tahap pengembangan / Coming Soon */
    Route::get('/feature/coming-soon/{module?}', [PegawaiController::class, 'comingSoon'])->name('coming.soon');
});

/* Endpoint Sinkronisasi Aman Data Cloud (Railway ke Localhost) */
Route::get('/api/cloud-sync/export', [CloudSyncController::class, 'export'])->name('cloud-sync.export');
Route::get('/api/cloud-sync/file/{path}', [CloudSyncController::class, 'downloadFile'])->where('path', '.*')->name('cloud-sync.file');

Route::get('/sync-db-production-simpeg-secure', function() {
    return abort(403, 'SYSTEM HALTED: Fitur sinkronisasi lama dinonaktifkan demi keamanan data. Sistem sinkronisasi baru sedang dikembangkan.');

    $output = "=== SIMPEG PRODUCTION SYNC & MIGRATION TOOL ===\n\n";

    // 1. Run Migrations
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $output .= "[MIGRATE]:\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";

    // 2. Sync Users
    \Illuminate\Support\Facades\Artisan::call('pegawai:sync-users');
    $output .= "[SYNC USERS]:\n" . \Illuminate\Support\Facades\Artisan::output() . "\n";

    // 3. Clear Caches
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    $output .= "[VIEW CLEAR]: " . \Illuminate\Support\Facades\Artisan::output() . "\n";
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    $output .= "[CACHE CLEAR]: " . \Illuminate\Support\Facades\Artisan::output() . "\n";
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    $output .= "[CONFIG CLEAR]: " . \Illuminate\Support\Facades\Artisan::output() . "\n";

    $output .= "\n=== ALL PRODUCTION OPERATIONS COMPLETED SUCCESSFULLY ===";

    return "<pre style='background:#1e1e1e;color:#00ff66;padding:20px;font-family:monospace;border-radius:8px;'>" . $output . "</pre>";
});

require __DIR__ . '/auth.php';