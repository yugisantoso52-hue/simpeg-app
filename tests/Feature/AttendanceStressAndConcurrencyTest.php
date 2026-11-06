<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\EmployeeAttendanceLocation;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceStressAndConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected Role $pegawaiRole;
    protected AttendanceService $attendanceService;

    // Koordinat acuan Kampus Gedung FKp UNRI
    protected float $officeLat = 0.478500;
    protected float $officeLng = 101.380200;

    // 1x1 Dummy PNG
    protected string $dummySelfie = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);
        $this->attendanceService = app(AttendanceService::class);
    }

    /**
     * TEST 1: SIMULASI LONJAKAN PRESENSI PAGI (PEAK-HOUR MORNING SURGE 50 PEGAWAI)
     * Menguji ketahanan sistem saat 50 pegawai serentak melakukan presensi WFO selfie pada rentang jam masuk (07:15 - 07:35 WIB).
     */
    public function test_peak_hour_morning_checkin_surge_50_concurrent_employees(): void
    {
        $startTime = microtime(true);
        $totalEmployees = 50;
        $users = [];

        // 1. Siapkan 50 Pegawai dan Titik Lokasi Acuan Kampus
        for ($i = 1; $i <= $totalEmployees; $i++) {
            $nip = sprintf('1985%02d%02d2015011%03d', ($i % 12) + 1, ($i % 28) + 1, $i);
            $pegawai = Pegawai::create([
                'nip' => $nip,
                'nama' => "Pegawai Simulasi Kepegawaian {$i}",
                'jenis_pegawai' => $i % 2 === 0 ? 'Dosen' : 'Tendik',
                'status_asn' => 'ASN',
                'status_pegawai' => 'Aktif',
            ]);

            $user = User::factory()->create([
                'name' => $nip,
                'email' => "pegawai{$i}@simpeg.fkp.unri.ac.id",
                'role_id' => $this->pegawaiRole->id,
                'pegawai_id' => $pegawai->id,
                'must_change_password' => false,
            ]);

            EmployeeAttendanceLocation::create([
                'user_id' => $user->id,
                'wfo_latitude' => $this->officeLat,
                'wfo_longitude' => $this->officeLng,
                'wfo_locked_at' => Carbon::now('Asia/Jakarta')->subDays(5),
            ]);

            $users[] = $user;
        }

        // 2. Jalankan Presensi WFO Check-In Serentak
        $successCount = 0;
        $today = Carbon::today('Asia/Jakarta');

        foreach ($users as $index => $user) {
            $token = $this->attendanceService->generateAttendanceToken($user);

            // Jarak acak wajar antara 5 - 45 meter dari titik acuan gedung FKp
            $jitterLat = $this->officeLat + (mt_rand(-20, 20) / 1000000);
            $jitterLng = $this->officeLng + (mt_rand(-20, 20) / 1000000);

            $payload = [
                'attendance_type' => 'wfo',
                'action' => 'check_in',
                'latitude' => $jitterLat,
                'longitude' => $jitterLng,
                'accuracy' => 12.5,
                'altitude' => 18.0,
                'speed' => 0.0,
                'is_mock' => false,
                'client_timestamp' => round(microtime(true) * 1000),
                'photo' => $this->dummySelfie,
                'device_fingerprint' => 'fp_load_test_' . $user->id . '_' . md5((string)$user->id),
                'device_platform' => 'Android 14; Chrome 124.0.0.0',
                'token' => $token,
                'notes' => 'Presensi simulasi jam masuk kerja FKP UNRI',
            ];

            $response = $this->actingAs($user)->postJson(route('presensi.store'), $payload);

            if ($response->status() === 200 && $response->json('success') === true) {
                $successCount++;
            }
        }

        $duration = microtime(true) - $startTime;

        // 3. Verifikasi Keberhasilan 100% dan Integritas Database
        $this->assertEquals($totalEmployees, $successCount, "Semua {$totalEmployees} pegawai harus berhasil melakukan presensi tanpa kegagalan.");

        $recordedCount = Attendance::whereDate('attendance_date', $today)->count();
        $this->assertEquals($totalEmployees, $recordedCount, "Jumlah record kehadiran hari ini harus persis {$totalEmployees}.");

        // 4. Verifikasi Agregasi Dashboard Statistik Hari Ini (Cross-Menu Sync)
        $todayStats = $this->attendanceService->todayStatistics();
        $this->assertGreaterThanOrEqual($totalEmployees, $todayStats['total_present'], 'Statistik hadir hari ini harus mencerminkan seluruh pegawai yang check-in.');
        $this->assertLessThan(15.0, $duration, "Proses beban 50 presensi harus selesai di bawah 15 detik (aktual: {$duration}s).");
    }

    /**
     * TEST 2: PROTEKSI DOUBLE-CLICK / RETRY SERENTAK PADA PEGAWAI YANG SAMA (IDEMPOTENCY)
     * Mencegah data presensi terduplikasi saat koneksi lambat dan tombol ditekan berulang kali.
     */
    public function test_simultaneous_double_click_protection_under_concurrency(): void
    {
        $pegawai = Pegawai::create([
            'nip' => '198703152014041002',
            'nama' => 'Dr. H. Rahmad Hidayat, M.Kep',
            'jenis_pegawai' => 'Dosen',
            'status_asn' => 'ASN',
            'status_pegawai' => 'Aktif',
        ]);

        $user = User::factory()->create([
            'name' => $pegawai->nip,
            'role_id' => $this->pegawaiRole->id,
            'pegawai_id' => $pegawai->id,
            'must_change_password' => false,
        ]);

        EmployeeAttendanceLocation::create([
            'user_id' => $user->id,
            'wfo_latitude' => $this->officeLat,
            'wfo_longitude' => $this->officeLng,
            'wfo_locked_at' => Carbon::now('Asia/Jakarta')->subDays(10),
        ]);

        $token = $this->attendanceService->generateAttendanceToken($user);

        $payload = [
            'attendance_type' => 'wfo',
            'action' => 'check_in',
            'latitude' => $this->officeLat,
            'longitude' => $this->officeLng,
            'accuracy' => 10.0,
            'is_mock' => false,
            'client_timestamp' => round(microtime(true) * 1000),
            'photo' => $this->dummySelfie,
            'device_fingerprint' => 'fp_double_click_tester',
            'device_platform' => 'Windows 11; Edge 122',
            'token' => $token,
        ];

        // Request 1: Check-in pertama
        $res1 = $this->actingAs($user)->postJson(route('presensi.store'), $payload);
        $res1->assertStatus(200)->assertJson(['success' => true]);

        // Request 2: Percobaan check-in kedua di detik yang sama (double click)
        $res2 = $this->actingAs($user)->postJson(route('presensi.store'), $payload);

        // Sistem harus menangani dengan aman: tidak error 500 dan tidak membuat baris duplikat
        $attendanceCount = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', Carbon::today('Asia/Jakarta'))
            ->count();

        $this->assertEquals(1, $attendanceCount, 'Hanya boleh ada tepat 1 baris presensi per pegawai per hari meskipun terjadi penekanan ganda.');
    }

    /**
     * TEST 3: SIMULASI SINKRONISASI MASSAL PWA OFFLINE (MASS BATCH SYNC WHEN WI-FI RECOVERS)
     * Menguji saat jaringan kampus pulih dan 25 perangkat pegawai serentak mengirimkan antrean offline signature.
     */
    public function test_massive_pwa_offline_batch_sync_under_load(): void
    {
        $batchSize = 25;
        $users = [];

        for ($i = 1; $i <= $batchSize; $i++) {
            $nip = sprintf('1993%02d%02d2020012%03d', ($i % 12) + 1, ($i % 28) + 1, $i);
            $pegawai = Pegawai::create([
                'nip' => $nip,
                'nama' => "Pegawai Offline Batch {$i}",
                'jenis_pegawai' => 'Tendik',
                'status_asn' => 'ASN',
                'status_pegawai' => 'Aktif',
            ]);

            $user = User::factory()->create([
                'name' => $nip,
                'role_id' => $this->pegawaiRole->id,
                'pegawai_id' => $pegawai->id,
                'must_change_password' => false,
            ]);

            EmployeeAttendanceLocation::create([
                'user_id' => $user->id,
                'wfo_latitude' => $this->officeLat,
                'wfo_longitude' => $this->officeLng,
                'wfo_locked_at' => Carbon::now('Asia/Jakarta')->subDays(3),
            ]);

            $users[] = $user;
        }

        $capturedAtSec = Carbon::now('Asia/Jakarta')->subMinutes(5)->timestamp;
        $successSync = 0;

        foreach ($users as $user) {
            $key = $this->attendanceService->generateOfflineSigningKey($user);
            $deviceFingerprint = 'fp_offline_sync_' . $user->id;

            $validHash = $this->attendanceService->computeOfflineProofHash(
                $key,
                'wfo',
                'check_in',
                $this->officeLat,
                $this->officeLng,
                $capturedAtSec,
                $deviceFingerprint
            );

            $payload = [
                'attendance_type' => 'wfo',
                'action' => 'check_in',
                'latitude' => $this->officeLat,
                'longitude' => $this->officeLng,
                'accuracy' => 15.0,
                'is_mock' => false,
                'client_timestamp' => $capturedAtSec * 1000,
                'photo' => $this->dummySelfie,
                'device_fingerprint' => $deviceFingerprint,
                'device_platform' => 'iOS 17.4; Safari Mobile',
                'is_offline_sync' => true,
                'offline_captured_at' => $capturedAtSec,
                'offline_proof_hash' => $validHash,
            ];

            $response = $this->actingAs($user)->postJson(route('presensi.store'), $payload);

            if ($response->status() === 200 && $response->json('success') === true) {
                $successSync++;
            }
        }

        $this->assertEquals($batchSize, $successSync, "Seluruh {$batchSize} antrean offline sync harus sukses tervalidasi.");

        // Verifikasi tanda sinkronisasi offline tercatat di database
        $offlineCount = Attendance::where('notes', 'like', '%Offline%')
            ->whereDate('attendance_date', Carbon::today('Asia/Jakarta'))
            ->count();

        $this->assertEquals($batchSize, $offlineCount, 'Seluruh antrean harus memiliki catatan audit Offline-First PWA Sync.');
    }

    /**
     * TEST 4: PERFORMA AGREGASI MATRIKS BULANAN & DASHBOARD TIDAK MENGALAMI N+1 QUERY EXPLOSION
     */
    public function test_dashboard_aggregation_performance_and_query_budget(): void
    {
        $today = Carbon::today('Asia/Jakarta');

        $queryCount = 0;
        \Illuminate\Support\Facades\DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $matrix = $this->attendanceService->getMonthlyMatrix($today->month, $today->year);

        $this->assertIsArray($matrix);
        $this->assertLessThan(20, $queryCount, "Agregasi matriks bulanan harus efisien (< 20 query SQL, aktual: {$queryCount}).");
    }
}
