<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use App\Notifications\KgbDueDateNotification;
use App\Services\KgbService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class KgbServiceTest extends TestCase
{
    use RefreshDatabase;

    protected KgbService $kgbService;
    protected Role $adminRole;
    protected Role $pimpinanRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kgbService = new KgbService();

        $this->adminRole = Role::create([
            'name'         => 'admin',
            'display_name' => 'Admin Kepegawaian',
        ]);

        $this->pimpinanRole = Role::create([
            'name'         => 'pimpinan',
            'display_name' => 'Pimpinan',
        ]);
    }

    /**
     * TEST 1 — KGB ELIGIBILITY
     * Memeriksa kelayakan KGB berdasarkan TMT KGB terakhir (>= 2 tahun).
     */
    public function test_kgb_eligibility_check(): void
    {
        // Pegawai eligible (TMT KGB 2 tahun lalu)
        $eligiblePegawai = Pegawai::create([
            'nip'              => '198501012010011001',
            'nama'             => 'Pegawai Eligible KGB',
            'status_pegawai'   => 'Aktif',
            'tmt_kgb_terakhir' => Carbon::now()->subYears(2)->toDateString(),
        ]);

        // Pegawai belum eligible (TMT KGB 1 tahun lalu)
        $notEligiblePegawai = Pegawai::create([
            'nip'              => '198501012010011002',
            'nama'             => 'Pegawai Belum Eligible KGB',
            'status_pegawai'   => 'Aktif',
            'tmt_kgb_terakhir' => Carbon::now()->subYears(1)->toDateString(),
        ]);

        $this->assertTrue($this->kgbService->cekKelayakanKgb($eligiblePegawai));
        $this->assertFalse($this->kgbService->cekKelayakanKgb($notEligiblePegawai));
    }

    /**
     * TEST 2 — PROSES KGB
     * Memeriksa proses KGB memperbarui tmt_kgb_terakhir dan kgb_berikutnya (+2 tahun) ke database.
     */
    public function test_proses_kgb_updates_database(): void
    {
        $pegawai = Pegawai::create([
            'nip'              => '198501012010011003',
            'nama'             => 'Pegawai Proses KGB',
            'status_pegawai'   => 'Aktif',
            'tmt_kgb_terakhir' => '2024-01-01',
        ]);

        $status = $this->kgbService->prosesKgb($pegawai, [
            'tmt_kgb_baru' => '2026-01-01',
        ]);

        $this->assertTrue($status);

        $pegawai->refresh();

        $this->assertEquals('2026-01-01', $pegawai->tmt_kgb_terakhir->format('Y-m-d'));
        $this->assertEquals('2028-01-01', $pegawai->kgb_berikutnya->format('Y-m-d'));
    }

    /**
     * TEST 3 — COMMAND KGB & NOTIFICATION
     * Memeriksa artisan simpeg:cek-kgb berjalan dan mengirim notifikasi ke user.
     */
    public function test_cek_kgb_command_triggers_notification(): void
    {
        Notification::fake();

        $user = User::create([
            'name'     => 'Admin User Test',
            'email'    => 'admintest@simpeg.com',
            'password' => bcrypt('password'),
            'role_id'  => $this->adminRole->id,
        ]);

        Pegawai::create([
            'nip'              => '198501012010011004',
            'nama'             => 'Pegawai Radar KGB',
            'status_pegawai'   => 'Aktif',
            'kgb_berikutnya'   => Carbon::today()->addDays(30)->toDateString(),
        ]);

        $this->artisan('simpeg:cek-kgb')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            KgbDueDateNotification::class
        );
    }

    /**
     * TEST 4 — NULL / INVALID TMT
     * Memeriksa penanganan aman jika TMT KGB null atau payload kosong tanpa crash.
     */
    public function test_null_or_invalid_tmt_handled_safely(): void
    {
        $pegawaiNull = Pegawai::create([
            'nip'              => '198501012010011005',
            'nama'             => 'Pegawai TMT Null',
            'status_pegawai'   => 'Aktif',
            'tmt_kgb_terakhir' => null,
        ]);

        // Cek kelayakan pegawai TMT null mengembalikan false secara aman
        $this->assertFalse($this->kgbService->cekKelayakanKgb($pegawaiNull));

        // Proses KGB dengan payload kosong mengembalikan false secara aman
        $status = $this->kgbService->prosesKgb($pegawaiNull, []);
        $this->assertFalse($status);
    }

    /**
     * TEST 5 — AUTHORIZATION FOR KGB PROCESS
     * Memeriksa batasan hak akses operasi proses KGB (Admin diizinkan, Pimpinan/Staf/Guest ditolak).
     */
    public function test_admin_can_process_kgb(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

        $pegawai = Pegawai::create([
            'nip'              => '198501012010011006',
            'nama'             => 'Pegawai Test Auth KGB Admin',
            'status_pegawai'   => 'Aktif',
            'tmt_kgb_terakhir' => '2024-01-01',
        ]);

        $response = $this->actingAs($adminUser)
            ->from('/kgb')
            ->post("/kgb/proses/{$pegawai->id}", [
                'tmt_kgb_baru' => '2026-01-01',
            ]);

        $response->assertStatus(302);
    }

    public function test_pimpinan_cannot_process_kgb(): void
    {
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);

        $pegawai = Pegawai::create([
            'nip'              => '198501012010011007',
            'nama'             => 'Pegawai Test Auth KGB Pimpinan',
            'status_pegawai'   => 'Aktif',
            'tmt_kgb_terakhir' => '2024-01-01',
        ]);

        $response = $this->actingAs($pimpinanUser)
            ->post("/kgb/proses/{$pegawai->id}", [
                'tmt_kgb_baru' => '2026-01-01',
            ]);

        $response->assertStatus(403);
    }

    public function test_staf_cannot_process_kgb(): void
    {
        $stafRole = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);
        $stafUser = User::factory()->create(['role_id' => $stafRole->id]);

        $pegawai = Pegawai::create([
            'nip'              => '198501012010011009',
            'nama'             => 'Pegawai Test Auth KGB Staf',
            'status_pegawai'   => 'Aktif',
            'tmt_kgb_terakhir' => '2024-01-01',
        ]);

        $response = $this->actingAs($stafUser)
            ->post("/kgb/proses/{$pegawai->id}", [
                'tmt_kgb_baru' => '2026-01-01',
            ]);

        $response->assertStatus(403);
    }
}
