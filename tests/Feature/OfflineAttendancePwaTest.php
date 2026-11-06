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

class OfflineAttendancePwaTest extends TestCase
{
    use RefreshDatabase;

    protected User $pegawaiUser;
    protected Pegawai $pegawai;
    protected AttendanceService $attendanceService;

    // Titik koordinat acuan Gedung FKp UNRI
    protected float $officeLat = 0.478500;
    protected float $officeLng = 101.380200;

    // Dummy 1x1 PNG Base64
    protected string $dummySelfie = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->pegawai = Pegawai::create([
            'nip' => '199105102019031005',
            'nama' => 'Ns. Budi Santoso, M.Kep',
            'jenis_pegawai' => 'Dosen',
            'status_asn' => 'ASN',
            'status_pegawai' => 'Aktif',
        ]);

        $this->pegawaiUser = User::factory()->create([
            'name' => $this->pegawai->nip,
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->pegawai->id,
            'must_change_password' => false,
        ]);

        // Kunci titik acuan WFO di Gedung FKp UNRI
        EmployeeAttendanceLocation::create([
            'user_id' => $this->pegawaiUser->id,
            'wfo_latitude' => $this->officeLat,
            'wfo_longitude' => $this->officeLng,
            'wfo_locked_at' => Carbon::now('Asia/Jakarta')->subDays(7),
        ]);

        $this->attendanceService = app(AttendanceService::class);
    }

    public function test_attendance_page_and_token_endpoint_provide_offline_pwa_signing_key(): void
    {
        $response = $this->actingAs($this->pegawaiUser)->get(route('presensi.index'));
        $response->assertStatus(200);
        $response->assertSee('offlineSigningKey');
        $response->assertSee('sikap_offline_attendance_db');

        $tokenResponse = $this->actingAs($this->pegawaiUser)->getJson(route('presensi.token'));
        $tokenResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'token',
                'expires_in',
                'offline_signing_key',
            ]);
    }

    public function test_valid_offline_pwa_check_in_records_actual_capture_time_and_on_time_status(): void
    {
        // Simulasikan waktu server saat sinyal kembali aktif adalah pukul 08:10 WIB (lewat jam masuk 07:30)
        $syncServerTime = Carbon::parse('2026-11-05 08:10:00', 'Asia/Jakarta');
        Carbon::setTestNow($syncServerTime);

        // Namun pegawai melakukan Check-In saat berada di titik kampus pukul 07:20 WIB (Tepat Waktu) saat blank spot
        $capturedTime = Carbon::parse('2026-11-05 07:20:00', 'Asia/Jakarta');
        $capturedAtSec = $capturedTime->timestamp;
        $deviceFingerprint = 'fp_device_budi_1234567890';

        $signingKey = $this->attendanceService->generateOfflineSigningKey($this->pegawaiUser, $capturedTime->toDateString());
        $proofHash = $this->attendanceService->computeOfflineProofHash(
            $signingKey,
            'wfo',
            'check_in',
            $this->officeLat,
            $this->officeLng,
            $capturedAtSec,
            $deviceFingerprint
        );

        $response = $this->actingAs($this->pegawaiUser)->postJson(route('presensi.store'), [
            'attendance_type' => 'wfo',
            'action' => 'check_in',
            'latitude' => $this->officeLat,
            'longitude' => $this->officeLng,
            'photo' => $this->dummySelfie,
            'notes' => 'Mengajar pagi di gedung lab',
            'accuracy' => 12.5,
            'is_mock' => false,
            'liveness_verified' => true,
            'liveness_challenge' => 'blink',
            'device_fingerprint' => $deviceFingerprint,
            'device_platform' => 'Android',
            'is_offline_sync' => true,
            'offline_captured_at' => $capturedAtSec,
            'offline_proof_hash' => $proofHash,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.check_in_time', '07:20')
            ->assertJsonPath('data.status', 'present');

        $attendance = Attendance::where('user_id', $this->pegawaiUser->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('07:20', $attendance->check_in_time->timezone('Asia/Jakarta')->format('H:i'));
        $this->assertEquals('present', $attendance->status);
        $this->assertStringContainsString('[PWA Offline Sync: Direkam 07:20 WIB, Tersinkron 08:10 WIB]', $attendance->notes);

        Carbon::setTestNow();
    }

    public function test_offline_sync_rejects_tampered_timestamp_or_replayed_proof(): void
    {
        $syncServerTime = Carbon::parse('2026-11-05 08:15:00', 'Asia/Jakarta');
        Carbon::setTestNow($syncServerTime);

        $actualCaptureTime = Carbon::parse('2026-11-05 08:05:00', 'Asia/Jakarta');
        $tamperedCaptureTime = Carbon::parse('2026-11-05 07:15:00', 'Asia/Jakarta');
        $deviceFingerprint = 'fp_device_budi_tamper';

        $signingKey = $this->attendanceService->generateOfflineSigningKey($this->pegawaiUser, $actualCaptureTime->toDateString());
        $proofHashFor0805 = $this->attendanceService->computeOfflineProofHash(
            $signingKey,
            'wfo',
            'check_in',
            $this->officeLat,
            $this->officeLng,
            $actualCaptureTime->timestamp,
            $deviceFingerprint
        );

        // 1. Coba kirim dengan waktu yang dimanipulasi ke 07:15 menggunakan hash dari 08:05 -> Ditolak 422
        $tamperedResponse = $this->actingAs($this->pegawaiUser)->postJson(route('presensi.store'), [
            'attendance_type' => 'wfo',
            'action' => 'check_in',
            'latitude' => $this->officeLat,
            'longitude' => $this->officeLng,
            'photo' => $this->dummySelfie,
            'accuracy' => 10,
            'is_mock' => false,
            'liveness_verified' => true,
            'device_fingerprint' => $deviceFingerprint,
            'is_offline_sync' => true,
            'offline_captured_at' => $tamperedCaptureTime->timestamp,
            'offline_proof_hash' => $proofHashFor0805,
        ]);

        $tamperedResponse->assertStatus(422)
            ->assertJsonValidationErrors(['offline_proof_hash']);

        // 2. Kirim dengan waktu asli 08:05 -> Berhasil 200
        $validResponse = $this->actingAs($this->pegawaiUser)->postJson(route('presensi.store'), [
            'attendance_type' => 'wfo',
            'action' => 'check_in',
            'latitude' => $this->officeLat,
            'longitude' => $this->officeLng,
            'photo' => $this->dummySelfie,
            'accuracy' => 10,
            'is_mock' => false,
            'liveness_verified' => true,
            'device_fingerprint' => $deviceFingerprint,
            'is_offline_sync' => true,
            'offline_captured_at' => $actualCaptureTime->timestamp,
            'offline_proof_hash' => $proofHashFor0805,
        ]);
        $validResponse->assertStatus(200);

        // 3. Coba kirim ulang (Replay Attack) bukti offline yang sama -> Ditolak 422
        $replayResponse = $this->actingAs($this->pegawaiUser)->postJson(route('presensi.store'), [
            'attendance_type' => 'wfo',
            'action' => 'check_in',
            'latitude' => $this->officeLat,
            'longitude' => $this->officeLng,
            'photo' => $this->dummySelfie,
            'accuracy' => 10,
            'is_mock' => false,
            'liveness_verified' => true,
            'device_fingerprint' => $deviceFingerprint,
            'is_offline_sync' => true,
            'offline_captured_at' => $actualCaptureTime->timestamp,
            'offline_proof_hash' => $proofHashFor0805,
        ]);
        $replayResponse->assertStatus(422)
            ->assertJsonValidationErrors(['offline_proof_hash']);

        Carbon::setTestNow();
    }

    public function test_offline_sync_still_enforces_75m_geofencing_radius(): void
    {
        $now = Carbon::parse('2026-11-05 07:25:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        // Koordinat berjarak ~500 meter dari kantor
        $farLat = $this->officeLat + 0.0045;
        $farLng = $this->officeLng + 0.0045;
        $deviceFingerprint = 'fp_device_budi_far';

        $signingKey = $this->attendanceService->generateOfflineSigningKey($this->pegawaiUser, $now->toDateString());
        $proofHash = $this->attendanceService->computeOfflineProofHash(
            $signingKey,
            'wfo',
            'check_in',
            $farLat,
            $farLng,
            $now->timestamp,
            $deviceFingerprint
        );

        $response = $this->actingAs($this->pegawaiUser)->postJson(route('presensi.store'), [
            'attendance_type' => 'wfo',
            'action' => 'check_in',
            'latitude' => $farLat,
            'longitude' => $farLng,
            'photo' => $this->dummySelfie,
            'accuracy' => 15,
            'is_mock' => false,
            'liveness_verified' => true,
            'device_fingerprint' => $deviceFingerprint,
            'is_offline_sync' => true,
            'offline_captured_at' => $now->timestamp,
            'offline_proof_hash' => $proofHash,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['radius']);

        Carbon::setTestNow();
    }

    public function test_simplified_one_click_selfie_and_indoor_gps_accuracy_within_75m_radius_succeeds(): void
    {
        $now = Carbon::parse('2026-11-05 07:22:00', 'Asia/Jakarta');
        Carbon::setTestNow($now);

        $token = $this->attendanceService->generateAttendanceToken($this->pegawaiUser);

        // Simulasikan akurasi GPS indoor/Wi-Fi (250 meter), namun titik koordinat aktual berada tepat di dalam radius <= 75m
        // dan foto selfie diambil dengan 1-Klik tanpa perlu tantangan kedip/senyum
        $response = $this->actingAs($this->pegawaiUser)->postJson(route('presensi.store'), [
            'attendance_type' => 'wfo',
            'action' => 'check_in',
            'latitude' => $this->officeLat + 0.0001, // ~15 meter dari titik acuan
            'longitude' => $this->officeLng + 0.0001,
            'photo' => $this->dummySelfie,
            'notes' => 'Presensi 1-klik dari dalam gedung',
            'accuracy' => 250.0,
            'is_mock' => false,
            'device_fingerprint' => 'fp_indoor_wifi_device',
            'token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.check_in_time', '07:22')
            ->assertJsonPath('data.status', 'present');

        Carbon::setTestNow();
    }

    public function test_all_supporting_attendance_menus_and_exports_work_without_error(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $adminUser = User::factory()->create([
            'name' => 'Admin Kepegawaian',
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        // Buat 1 data presensi terlebih dahulu
        $token = $this->attendanceService->generateAttendanceToken($this->pegawaiUser);
        $this->actingAs($this->pegawaiUser)->postJson(route('presensi.store'), [
            'attendance_type' => 'wfo',
            'action' => 'check_in',
            'latitude' => $this->officeLat,
            'longitude' => $this->officeLng,
            'photo' => $this->dummySelfie,
            'accuracy' => 20.0,
            'is_mock' => false,
            'device_fingerprint' => 'fp_test_menu_support',
            'token' => $token,
        ])->assertStatus(200);

        $attendance = Attendance::where('user_id', $this->pegawaiUser->id)->firstOrFail();

        // 1. Halaman Utama Presensi Pegawai
        $this->actingAs($this->pegawaiUser)
            ->get(route('presensi.index'))
            ->assertStatus(200)
            ->assertSee('Ambil Foto Selfie');

        // 2. Halaman Riwayat Presensi Saya
        $this->actingAs($this->pegawaiUser)
            ->get(route('presensi.history'))
            ->assertStatus(200);

        // 3. Streaming Foto Selfie Aman (presensi.photo)
        $this->actingAs($this->pegawaiUser)
            ->get(route('presensi.photo', ['id' => $attendance->id, 'type' => 'in']))
            ->assertStatus(200);

        // 4. Halaman Rekap Presensi Admin (Mode Harian & Mode Matriks Bulanan)
        $this->actingAs($adminUser)
            ->get(route('admin.presensi.index', ['mode' => 'daily']))
            ->assertStatus(200);

        $this->actingAs($adminUser)
            ->get(route('admin.presensi.index', ['mode' => 'monthly']))
            ->assertStatus(200);

        // 5. Halaman Kelola Titik Lokasi Acuan (admin.presensi.locations) & Reset Lokasi
        $this->actingAs($adminUser)
            ->get(route('admin.presensi.locations'))
            ->assertStatus(200);

        $this->actingAs($adminUser)
            ->post(route('admin.presensi.reset-location', $this->pegawaiUser->id), ['type' => 'wfo'])
            ->assertRedirect();

        // 6. Ekspor PDF & Excel Rekap Presensi
        $this->actingAs($adminUser)
            ->get(route('admin.presensi.export.pdf', ['mode' => 'daily']))
            ->assertStatus(200);

        $this->actingAs($adminUser)
            ->get(route('admin.presensi.export.excel', ['mode' => 'daily']))
            ->assertStatus(200);
    }
}
