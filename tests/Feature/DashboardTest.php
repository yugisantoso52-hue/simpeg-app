<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);
    }

    public function test_authenticated_user_can_view_dashboard_index_with_statistics(): void
    {
        $unit = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru']);
        $golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);

        Pegawai::create([
            'nip'            => '199001012015011001',
            'nama'           => 'Budi Dashboard',
            'status_asn'     => 'ASN',
            'jenis_pegawai'  => 'PNS',
            'status_pegawai' => 'Aktif',
            'unit_kerja_id'  => $unit->id,
            'jabatan_id'     => $jabatan->id,
            'golongan_id'    => $golongan->id,
            'pendidikan'     => 'S1 Informatika',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('SISTEM INFORMASI KEPEGAWAIAN (SIKAP)');
        $response->assertSee('Total Pegawai');
    }

    public function test_dashboard_handles_empty_database_safely(): void
    {
        Cache::forget('dashboard_statistics');
        Cache::forget('dashboard_grafik_golongan');
        Cache::forget('dashboard_grafik_pendidikan');
        Cache::forget('dashboard_grafik_unit');

        $response = $this->actingAs($this->pimpinanUser)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_reminders_repository_query(): void
    {
        $unit = UnitKerja::create(['nama_unit' => 'Dinas Kesehatan', 'kode_unit' => 'DINKES']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-002', 'nama_jabatan' => 'Perawat']);

        Pegawai::create([
            'nip'            => '199101012016011002',
            'nama'           => 'Pegawai KGB Radar',
            'status_pegawai' => 'Aktif',
            'kgb_berikutnya' => Carbon::now()->addMonth()->toDateString(),
            'unit_kerja_id'  => $unit->id,
            'jabatan_id'     => $jabatan->id,
        ]);

        $repo = app(DashboardRepositoryInterface::class);
        $reminder = $repo->getReminder();

        $this->assertNotEmpty($reminder['kgb']);
        $this->assertEquals('Pegawai KGB Radar', $reminder['kgb'][0]->nama);
    }

    /**
     * BOUNDARY TEST A — TEPAT AWAL HARI INI (INCLUDED)
     */
    public function test_boundary_reminders_start_of_day_included(): void
    {
        Pegawai::create([
            'nip'            => '199101012016011003',
            'nama'           => 'Pegawai Start Of Day',
            'status_pegawai' => 'Aktif',
            'kgb_berikutnya' => Carbon::now()->startOfDay()->toDateString(),
        ]);

        $repo = app(DashboardRepositoryInterface::class);
        $reminder = $repo->getReminder();

        $this->assertNotEmpty($reminder['kgb']);
        $this->assertTrue($reminder['kgb']->contains('nama', 'Pegawai Start Of Day'));
    }

    /**
     * BOUNDARY TEST B — TEPAT AKHIR HARI TARGET 3 BULAN (INCLUDED)
     */
    public function test_boundary_reminders_end_of_target_range_included(): void
    {
        Pegawai::create([
            'nip'            => '199101012016011004',
            'nama'           => 'Pegawai End Of Target Range',
            'status_pegawai' => 'Aktif',
            'kp_berikutnya'  => Carbon::now()->addMonths(3)->toDateString(),
        ]);

        $repo = app(DashboardRepositoryInterface::class);
        $reminder = $repo->getReminder();

        $this->assertNotEmpty($reminder['kp']);
        $this->assertTrue($reminder['kp']->contains('nama', 'Pegawai End Of Target Range'));
    }

    /**
     * BOUNDARY TEST C — SATU HARI SETELAH BATAS TARGET (EXCLUDED)
     */
    public function test_boundary_reminders_after_target_range_excluded(): void
    {
        Pegawai::create([
            'nip'            => '199101012016011005',
            'nama'           => 'Pegawai Outside Target Range',
            'status_pegawai' => 'Aktif',
            'kgb_berikutnya' => Carbon::now()->addMonths(3)->addDays(2)->toDateString(),
        ]);

        $repo = app(DashboardRepositoryInterface::class);
        $reminder = $repo->getReminder();

        $this->assertFalse($reminder['kgb']->contains('nama', 'Pegawai Outside Target Range'));
    }

    /**
     * BOUNDARY TEST D — SEBELUM HARI INI (EXCLUDED)
     */
    public function test_boundary_reminders_before_today_excluded(): void
    {
        Pegawai::create([
            'nip'            => '199101012016011006',
            'nama'           => 'Pegawai Before Today',
            'status_pegawai' => 'Aktif',
            'kgb_berikutnya' => Carbon::now()->subDays(2)->toDateString(),
        ]);

        $repo = app(DashboardRepositoryInterface::class);
        $reminder = $repo->getReminder();

        $this->assertFalse($reminder['kgb']->contains('nama', 'Pegawai Before Today'));
    }

    /**
     * TEST MASA PENSIUN (BUP 58) — RADAR 1 TAHUN (INCLUDED)
     */
    public function test_pensiun_reminder_radar_1_year_included(): void
    {
        // Pegawai yang akan berusia 58 tahun dalam 6 bulan ke depan
        $tglLahir = Carbon::now()->subYears(58)->addMonths(6)->toDateString();

        Pegawai::create([
            'nip'            => '196808211990011001',
            'nama'           => 'Pegawai Pensiun 6 Bulan',
            'status_pegawai' => 'Aktif',
            'tanggal_lahir'  => $tglLahir,
        ]);

        $repo = app(DashboardRepositoryInterface::class);
        $reminder = $repo->getReminder();

        $this->assertNotEmpty($reminder['pensiun']);
        $this->assertTrue($reminder['pensiun']->contains('nama', 'Pegawai Pensiun 6 Bulan'));
    }

    /**
     * TEST MASA PENSIUN (BUP 58) — DI LUAR RADAR 1 TAHUN (EXCLUDED)
     */
    public function test_pensiun_reminder_radar_beyond_1_year_excluded(): void
    {
        // Pegawai yang baru berusia 58 tahun dalam 18 bulan ke depan
        $tglLahir = Carbon::now()->subYears(58)->addMonths(18)->toDateString();

        Pegawai::create([
            'nip'            => '196808211990011002',
            'nama'           => 'Pegawai Pensiun Jauh',
            'status_pegawai' => 'Aktif',
            'tanggal_lahir'  => $tglLahir,
        ]);

        $repo = app(DashboardRepositoryInterface::class);
        $reminder = $repo->getReminder();

        $this->assertFalse($reminder['pensiun']->contains('nama', 'Pegawai Pensiun Jauh'));
    }

    public function test_guest_is_redirected_to_login_for_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
