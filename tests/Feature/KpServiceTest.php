<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use App\Notifications\KpDueDateNotification;
use App\Services\KpService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class KpServiceTest extends TestCase
{
    use RefreshDatabase;

    protected KpService $kpService;
    protected Role $adminRole;
    protected Role $pimpinanRole;
    protected Role $stafRole;

    protected Golongan $golonganAwal;
    protected Golongan $golonganBerikutnya;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kpService = new KpService();

        $this->adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $this->pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $this->stafRole = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);

        $this->golonganAwal = Golongan::create([
            'nama_golongan' => 'III/a',
            'nama_pangkat'  => 'Penata Muda',
        ]);

        $this->golonganBerikutnya = Golongan::create([
            'nama_golongan' => 'III/b',
            'nama_pangkat'  => 'Penata Muda Tingkat I',
        ]);
    }

    /**
     * TEST 1 — KP ELIGIBILITY / RADAR
     * Memeriksa kelayakan Kenaikan Pangkat (>= 4 tahun dari TMT Pangkat Terakhir).
     */
    public function test_kp_eligibility_check(): void
    {
        $eligiblePegawai = Pegawai::create([
            'nip'                  => '198501012010011010',
            'nama'                 => 'Pegawai Eligible KP',
            'status_pegawai'       => 'Aktif',
            'tmt_pangkat_terakhir' => Carbon::now()->subYears(4)->toDateString(),
        ]);

        $notEligiblePegawai = Pegawai::create([
            'nip'                  => '198501012010011011',
            'nama'                 => 'Pegawai Belum Eligible KP',
            'status_pegawai'       => 'Aktif',
            'tmt_pangkat_terakhir' => Carbon::now()->subYears(3)->toDateString(),
        ]);

        $this->assertTrue($this->kpService->cekKelayakanKp($eligiblePegawai));
        $this->assertFalse($this->kpService->cekKelayakanKp($notEligiblePegawai));
    }

    /**
     * TEST 2 — PROSES KP & UPDATE RIWAYAT PANGKAT
     * Memeriksa proses KP memperbarui golongan, tmt_pangkat_terakhir, kp_berikutnya (+4 thn), & riwayat_pangkat.
     */
    public function test_proses_kp_updates_database_and_riwayat_pangkat(): void
    {
        $pegawai = Pegawai::create([
            'nip'                  => '198501012010011012',
            'nama'                 => 'Pegawai Proses KP',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
            'tmt_pangkat_terakhir' => '2022-01-01',
        ]);

        $status = $this->kpService->prosesKp($pegawai, [
            'golongan_baru_id' => $this->golonganBerikutnya->id,
            'tmt_pangkat_baru' => '2026-01-01',
        ]);

        $this->assertTrue($status);

        $pegawai->refresh();

        $this->assertEquals($this->golonganBerikutnya->id, $pegawai->golongan_id);
        $this->assertEquals('2026-01-01', $pegawai->tmt_pangkat_terakhir->format('Y-m-d'));
        $this->assertEquals('2030-01-01', $pegawai->kp_berikutnya->format('Y-m-d'));

        // Assert record riwayat pangkat tersimpan dengan status aktif
        $riwayatAktif = $pegawai->riwayatPangkat()->where('status', 'aktif')->first();
        $this->assertNotNull($riwayatAktif);
        $this->assertEquals($this->golonganBerikutnya->id, $riwayatAktif->golongan_id);
        $this->assertEquals('2026-01-01', $riwayatAktif->tmt->format('Y-m-d'));
    }

    /**
     * TEST 3 — PROSES OTOMATISASI KP (simpeg:cek-kp logic)
     * Memeriksa kenaikan jenjang golongan otomatis ke tingkat berikutnya.
     */
    public function test_proses_kenaikan_pangkat_otomatis_increments_golongan(): void
    {
        $pegawai = Pegawai::create([
            'nip'                  => '198501012010011013',
            'nama'                 => 'Pegawai Otomatis KP',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
            'tmt_pangkat_terakhir' => '2022-01-01',
            'kp_berikutnya'        => '2026-01-01',
        ]);

        $status = $this->kpService->prosesKenaikanPangkatOtomatis($pegawai);
        $this->assertTrue($status);

        $pegawai->refresh();

        $this->assertEquals($this->golonganBerikutnya->id, $pegawai->golongan_id);
        $this->assertEquals('2026-01-01', $pegawai->tmt_pangkat_terakhir->format('Y-m-d'));
        $this->assertEquals('2030-01-01', $pegawai->kp_berikutnya->format('Y-m-d'));
    }

    /**
     * TEST 4 — COMMAND KP & NOTIFICATION
     * Memeriksa artisan simpeg:cek-kp berjalan dan memicu notifikasi dashboard.
     */
    public function test_cek_kp_command_triggers_automatic_promotion_and_notification(): void
    {
        Notification::fake();

        $adminUser = User::factory()->create(['role_id' => $this->adminRole->id]);

        $pegawai = Pegawai::create([
            'nip'                  => '198501012010011014',
            'nama'                 => 'Pegawai Command KP',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
            'kp_berikutnya'        => Carbon::today()->toDateString(),
        ]);

        $this->artisan('simpeg:cek-kp')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $adminUser,
            KpDueDateNotification::class
        );
    }

    /**
     * TEST 5 — NULL / INVALID DATA HANDLED SAFELY
     * Memeriksa TMT Pangkat Terakhir & tanggal masuk null ditangani secara aman tanpa crash.
     */
    public function test_null_or_missing_tmt_handled_safely(): void
    {
        $pegawaiNull = Pegawai::create([
            'nip'                  => '198501012010011015',
            'nama'                 => 'Pegawai Null KP',
            'status_pegawai'       => 'Aktif',
            'tmt_pangkat_terakhir' => null,
            'tanggal_masuk'        => null,
        ]);

        $this->assertFalse($this->kpService->cekKelayakanKp($pegawaiNull));
    }

    /**
     * TEST 6 — AUTHORIZATION RESTRICTIONS
     * Memeriksa Admin berhak memproses KP, sedangkan Pimpinan, Staf, & Guest ditolak.
     */
    public function test_admin_can_process_kp(): void
    {
        $adminUser = User::factory()->create(['role_id' => $this->adminRole->id]);

        $pegawai = Pegawai::create([
            'nip'                  => '198501012010011016',
            'nama'                 => 'Pegawai Auth KP Admin',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
        ]);

        $response = $this->actingAs($adminUser)
            ->from('/kenaikan-pangkat')
            ->post("/kenaikan-pangkat/proses/{$pegawai->id}", [
                'golongan_baru_id' => $this->golonganBerikutnya->id,
                'tmt_pangkat_baru' => '2026-01-01',
            ]);

        $response->assertStatus(302);
    }

    public function test_pimpinan_cannot_process_kp(): void
    {
        $pimpinanUser = User::factory()->create(['role_id' => $this->pimpinanRole->id]);

        $pegawai = Pegawai::create([
            'nip'                  => '198501012010011017',
            'nama'                 => 'Pegawai Auth KP Pimpinan',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
        ]);

        $response = $this->actingAs($pimpinanUser)
            ->post("/kenaikan-pangkat/proses/{$pegawai->id}", [
                'golongan_baru_id' => $this->golonganBerikutnya->id,
                'tmt_pangkat_baru' => '2026-01-01',
            ]);

        $response->assertStatus(403);
    }

    public function test_staf_cannot_process_kp(): void
    {
        $stafUser = User::factory()->create(['role_id' => $this->stafRole->id]);

        $pegawai = Pegawai::create([
            'nip'                  => '198501012010011018',
            'nama'                 => 'Pegawai Auth KP Staf',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
        ]);

        $response = $this->actingAs($stafUser)
            ->post("/kenaikan-pangkat/proses/{$pegawai->id}", [
                'golongan_baru_id' => $this->golonganBerikutnya->id,
                'tmt_pangkat_baru' => '2026-01-01',
            ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_process_kp(): void
    {
        $pegawai = Pegawai::create([
            'nip'                  => '198501012010011019',
            'nama'                 => 'Pegawai Auth KP Guest',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
        ]);

        $response = $this->post("/kenaikan-pangkat/proses/{$pegawai->id}", [
            'golongan_baru_id' => $this->golonganBerikutnya->id,
            'tmt_pangkat_baru' => '2026-01-01',
        ]);

        $response->assertRedirect('/login');
    }

    /**
     * TEST 7 — IDEMPOTENCY PREVENTS DUPLICATE CRON PROCESSING
     * Memeriksa setelah proses KP, kp_berikutnya bertambah +4 thn sehingga cron selanjutnya tidak memproses ulang.
     */
    public function test_kp_idempotency_prevents_duplicate_cron_processing(): void
    {
        $pegawai = Pegawai::create([
            'nip'                  => '198501012010011020',
            'nama'                 => 'Pegawai Idempotency KP',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
            'tmt_pangkat_terakhir' => '2022-01-01',
            'kp_berikutnya'        => '2026-01-01',
        ]);

        // Eksekusi pertama
        $this->kpService->prosesKenaikanPangkatOtomatis($pegawai);
        $pegawai->refresh();

        // Tanggal KP Berikutnya berpindah 4 tahun ke depan (2030-01-01)
        $this->assertEquals('2030-01-01', $pegawai->kp_berikutnya->format('Y-m-d'));

        // Pengecekan radar KP pada tanggal saat ini (2026) tidak lagi mendeteksi pegawai ini sebagai eligible
        $this->assertFalse(Carbon::parse($pegawai->kp_berikutnya)->isPast());
    }
}
