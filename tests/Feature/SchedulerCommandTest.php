<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SchedulerCommandTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
    }

    /**
     * TEST 1 — CEK KGB COMMAND EXECUTION
     */
    public function test_simpeg_cek_kgb_command_runs_successfully(): void
    {
        Notification::fake();

        $unit = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru']);

        Pegawai::create([
            'nip'            => '199001012015011001',
            'nama'           => 'Pegawai KGB Test',
            'status_pegawai' => 'Aktif',
            'kgb_berikutnya' => Carbon::today()->addMonth()->toDateString(),
            'unit_kerja_id'  => $unit->id,
            'jabatan_id'     => $jabatan->id,
        ]);

        $this->artisan('simpeg:cek-kgb')
            ->assertExitCode(0);
    }

    /**
     * TEST 2 — CEK KP COMMAND EXECUTION
     */
    public function test_simpeg_cek_kp_command_runs_successfully(): void
    {
        Notification::fake();

        $unit = UnitKerja::create(['nama_unit' => 'Dinas Kesehatan', 'kode_unit' => 'DINKES']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-002', 'nama_jabatan' => 'Perawat']);
        $golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);

        Pegawai::create([
            'nip'            => '199101012016011002',
            'nama'           => 'Pegawai KP Test',
            'status_pegawai' => 'Aktif',
            'kp_berikutnya'  => Carbon::today()->addMonth()->toDateString(),
            'unit_kerja_id'  => $unit->id,
            'jabatan_id'     => $jabatan->id,
            'golongan_id'    => $golongan->id,
        ]);

        $this->artisan('simpeg:cek-kp')
            ->assertExitCode(0);
    }

    /**
     * TEST 3 — CEK PENSIUN COMMAND EXECUTION
     */
    public function test_simpeg_cek_pensiun_command_runs_successfully(): void
    {
        Notification::fake();

        Pegawai::create([
            'nip'            => '196801011990011001',
            'nama'           => 'Pegawai Pensiun Test',
            'status_pegawai' => 'Aktif',
            'tanggal_lahir'  => Carbon::today()->subYears(58)->addMonth()->toDateString(),
        ]);

        $this->artisan('simpeg:cek-pensiun')
            ->assertExitCode(0);
    }

    /**
     * TEST 4 — CEK SATYALANCANA COMMAND EXECUTION
     */
    public function test_simpeg_cek_satyalancana_command_runs_successfully(): void
    {
        Notification::fake();

        Pegawai::create([
            'nip'            => '198601012016011001',
            'nama'           => 'Pegawai Satyalancana Test',
            'status_pegawai' => 'Aktif',
            'tanggal_masuk'  => Carbon::today()->subYears(10)->addMonth()->toDateString(),
        ]);

        $this->artisan('simpeg:cek-satyalancana')
            ->assertExitCode(0);
    }

    /**
     * TEST 5 — IDEMPOTENCY: RUNNING COMMANDS MULTIPLE TIMES PRODUCES NO ERRORS OR DUPLICATE DB MUTATIONS
     */
    public function test_commands_are_idempotent_when_run_multiple_times(): void
    {
        Notification::fake();

        Pegawai::create([
            'nip'            => '199001012015011003',
            'nama'           => 'Pegawai Idempotency Test',
            'status_pegawai' => 'Aktif',
            'kgb_berikutnya' => Carbon::today()->addMonth()->toDateString(),
            'kp_berikutnya'  => Carbon::today()->addMonth()->toDateString(),
            'tanggal_lahir'  => Carbon::today()->subYears(58)->addMonth()->toDateString(),
            'tanggal_masuk'  => Carbon::today()->subYears(10)->addMonth()->toDateString(),
        ]);

        // Run 3 times
        for ($i = 0; $i < 3; $i++) {
            $this->artisan('simpeg:cek-kgb')->assertExitCode(0);
            $this->artisan('simpeg:cek-kp')->assertExitCode(0);
            $this->artisan('simpeg:cek-pensiun')->assertExitCode(0);
            $this->artisan('simpeg:cek-satyalancana')->assertExitCode(0);
        }

        $this->assertDatabaseHas('pegawai', ['nip' => '199001012015011003']);
    }

    /**
     * TEST 6 — EMPTY DATABASE SAFETY
     */
    public function test_commands_handle_empty_database_safely(): void
    {
        $this->artisan('simpeg:cek-kgb')->assertExitCode(0);
        $this->artisan('simpeg:cek-kp')->assertExitCode(0);
        $this->artisan('simpeg:cek-pensiun')->assertExitCode(0);
        $this->artisan('simpeg:cek-satyalancana')->assertExitCode(0);
    }

    /**
     * TEST 7 — NULL DATES SAFETY
     */
    public function test_commands_handle_null_dates_safely(): void
    {
        Pegawai::create([
            'nip'            => '199901012022011001',
            'nama'           => 'Pegawai Null Dates',
            'status_pegawai' => 'Aktif',
            'kgb_berikutnya' => null,
            'kp_berikutnya'  => null,
            'tanggal_lahir'  => null,
            'tanggal_masuk'  => null,
        ]);

        $this->artisan('simpeg:cek-kgb')->assertExitCode(0);
        $this->artisan('simpeg:cek-kp')->assertExitCode(0);
        $this->artisan('simpeg:cek-pensiun')->assertExitCode(0);
        $this->artisan('simpeg:cek-satyalancana')->assertExitCode(0);
    }

    /**
     * TEST 8 — SCHEDULER REGISTRATION INTEGRITY
     */
    public function test_scheduler_registration_contains_all_simpeg_commands(): void
    {
        $schedule = app(Schedule::class);
        $events = collect($schedule->events());

        $commandSignatures = $events->map(function ($event) {
            return $event->command;
        })->implode(' ');

        $this->assertStringContainsString('simpeg:cek-kgb', $commandSignatures);
        $this->assertStringContainsString('simpeg:cek-kp', $commandSignatures);
        $this->assertStringContainsString('simpeg:cek-satyalancana', $commandSignatures);
        $this->assertStringContainsString('simpeg:cek-pensiun', $commandSignatures);
    }
}
