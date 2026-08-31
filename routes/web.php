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

/*
|--------------------------------------------------------------------------
| Web Routes - SIMPEG Enterprise (Production Ready)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

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

        // CRUD Riwayat Mandiri Pegawai (STR/SIP, Tubel, SKP, Penghargaan, Organisasi, Publikasi, Pendidikan, Diklat)
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

        // Mutasi
        Route::resource('mutasi-pegawai', MutasiPegawaiController::class)->parameters(['mutasi-pegawai' => 'mutasi_pegawai']);
        Route::get('/pegawai-mutasi/{id}', [MutasiPegawaiController::class, 'getPegawai'])->name('pegawai-mutasi');

        // KGB & KP & Satyalancana
        Route::get('/kgb', [KgbController::class, 'index'])->name('kgb.index');
        Route::post('/kgb/proses/{id}', [KgbController::class, 'proses'])->name('kgb.proses');

        Route::get('/kenaikan-pangkat', [KpController::class, 'index'])->name('kp.index');
        Route::post('/kenaikan-pangkat/proses/{id}', [KpController::class, 'proses'])->name('kp.proses');

        Route::get('/satyalancana', [SatyalancanaController::class, 'index'])->name('satyalancana.index');

        // Impor & Template
        Route::get('/pegawai/template', [PegawaiController::class, 'downloadTemplate'])->name('pegawai.template');
        Route::post('/pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');

        // Tambah & Hapus Pegawai (Khusus Admin)
        Route::get('/sync-from-cloud', [CloudSyncController::class, 'pullFromWeb'])->name('cloud-sync.pull-web');
        Route::get('/pegawai/create', [PegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::post('/pegawai/bulk-delete', [PegawaiController::class, 'bulkDelete'])->name('pegawai.bulk-delete');
        Route::delete('/pegawai/{pegawai}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');

        // Riwayat Khusus Admin (Struktural / Kepangkatan)
        Route::resource('riwayat-jabatan', RiwayatJabatanController::class)->except(['index', 'show']);
        Route::resource('riwayat-pangkat', RiwayatPangkatController::class)->except(['index', 'show']);
        Route::delete('/pengajuan-cuti/{id}', [PengajuanCutiController::class, 'destroy'])->name('pengajuan-cuti.destroy');
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

        /* Monitoring Tugas Belajar & Riwayat SKP */
        Route::get('/tugas-belajar', [TugasBelajarController::class, 'index'])->name('tugas-belajar.index');
        Route::get('/riwayat-skp', [RiwayatSkpController::class, 'index'])->name('riwayat-skp.index');
    });

    // ======================================================================
    // KHUSUS ADMIN & PIMPINAN (MONITORING, APPROVAL & REPORTS)
    // ======================================================================
    Route::middleware(['role:admin,pimpinan'])->group(function () {
        /* Approval Pengajuan Cuti */
        Route::post('/pengajuan-cuti/{id}/approve', [PengajuanCutiController::class, 'approve'])->name('pengajuan-cuti.approve');

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

        /* Read-Only Riwayat List */
        Route::get('/riwayat-pendidikan', [RiwayatPendidikanController::class, 'index'])->name('riwayat-pendidikan.index');
        Route::get('/riwayat-jabatan', [RiwayatJabatanController::class, 'index'])->name('riwayat-jabatan.index');
        Route::get('/riwayat-pangkat', [RiwayatPangkatController::class, 'index'])->name('riwayat-pangkat.index');
        Route::get('/riwayat-diklat', [RiwayatDiklatController::class, 'index'])->name('riwayat-diklat.index');
        Route::get('/riwayat-str-sip', [RiwayatStrSipController::class, 'index'])->name('riwayat-str-sip.index');

        Route::get('/reports/kgb/{id}/pdf', [ReportController::class, 'exportKgbPdf'])->name('reports.kgb.pdf');
        Route::get('/reports/reminder/pdf', [ReportController::class, 'exportReminderPdf'])->name('reports.reminder.pdf');
        Route::get('/reports/reminder/excel', [ReportController::class, 'exportReminderExcel'])->name('reports.reminder.excel');
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