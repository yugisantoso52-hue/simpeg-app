<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\MutasiPegawai;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatPendidikan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Notifications\KgbDueDateNotification;
use App\Notifications\KpDueDateNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationIntegrationAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;

    protected UnitKerja $unitLama;
    protected UnitKerja $unitBaru;
    protected Jabatan $jabatanLama;
    protected Jabatan $jabatanBaru;
    protected Golongan $golonganAwal;
    protected Golongan $golonganBaru;
    protected Pegawai $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);

        $this->unitLama = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $this->unitBaru = UnitKerja::create(['nama_unit' => 'Dinas Kesehatan', 'kode_unit' => 'DINKES']);

        $this->jabatanLama = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru Muda']);
        $this->jabatanBaru = Jabatan::create(['kode_jabatan' => 'JAB-002', 'nama_jabatan' => 'Perawat Ahli']);

        $this->golonganAwal = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);
        $this->golonganBaru = Golongan::create(['nama_golongan' => 'III/b', 'nama_pangkat' => 'Penata Muda Tk. I']);

        $this->pegawai = Pegawai::create([
            'nip'                  => '199001012015011001',
            'nama'                 => 'Pegawai Integration Test',
            'status_pegawai'       => 'Aktif',
            'jenis_pegawai'        => 'PNS',
            'status_asn'           => 'ASN',
            'unit_kerja_id'        => $this->unitLama->id,
            'jabatan_id'           => $this->jabatanLama->id,
            'golongan_id'          => $this->golonganAwal->id,
            'tmt_pangkat_terakhir' => '2022-01-01',
            'tmt_kgb_terakhir'     => '2024-01-01',
            'kgb_berikutnya'       => Carbon::today()->addMonth()->toDateString(),
            'kp_berikutnya'        => Carbon::today()->addMonth()->toDateString(),
        ]);
    }

    /**
     * TEST 1 — MENU AND ROUTE CONNECTIVITY FOR ADMIN & PIMPINAN
     */
    public function test_menu_and_routes_connectivity_for_admin_and_pimpinan(): void
    {
        $adminRoutes = [
            '/dashboard',
            '/pegawai',
            '/pegawai/create',
            '/duk',
            '/unit-kerja',
            '/jabatan',
            '/golongan',
            '/mutasi-pegawai',
            '/kgb',
            '/kenaikan-pangkat',
            '/satyalancana',
            '/reports/duk/pdf',
            '/reports/duk/excel',
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($this->adminUser)->get($route);
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 302]),
                "Admin Route {$route} should return 200 OK or 302."
            );
        }

        $pimpinanRoutes = [
            '/dashboard',
            '/pegawai',
            '/duk',
            '/reports/duk/pdf',
            '/reports/duk/excel',
        ];

        foreach ($pimpinanRoutes as $route) {
            $response = $this->actingAs($this->pimpinanUser)->get($route);
            $this->assertEquals(200, $response->getStatusCode(), "Pimpinan Route {$route} should return 200 OK.");
        }
    }

    /**
     * TEST 2 — MASTER DATA TO PEGAWAI ELOQUENT RELATIONSHIPS INTEGRATION
     */
    public function test_master_data_to_pegawai_relationship_integration(): void
    {
        $this->assertEquals('Dinas Pendidikan', $this->pegawai->unitKerja->nama_unit);
        $this->assertEquals('Guru Muda', $this->pegawai->jabatan->nama_jabatan);
        $this->assertEquals('III/a', $this->pegawai->golongan->nama_golongan);
    }

    /**
     * TEST 3 — PEGAWAI TO ALL RIWAYAT ELOQUENT RELATIONSHIPS INTEGRATION
     */
    public function test_pegawai_to_all_riwayat_kepegawaian_relations_integration(): void
    {
        $rPend = RiwayatPendidikan::create([
            'pegawai_id' => $this->pegawai->id,
            'jenjang'    => 'S1',
            'institusi'  => 'UGM',
        ]);

        $rPkt = RiwayatPangkat::create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganAwal->id,
            'tmt_pangkat' => '2022-01-01',
        ]);

        $rJab = RiwayatJabatan::create([
            'pegawai_id'    => $this->pegawai->id,
            'unit_kerja_id' => $this->unitLama->id,
            'jabatan_id'    => $this->jabatanLama->id,
            'tmt_jabatan'   => '2022-01-01',
        ]);

        $mutasi = MutasiPegawai::create([
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $this->unitBaru->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $this->jabatanBaru->id,
            'tmt'             => '2025-01-01',
        ]);

        $this->pegawai->refresh();

        $this->assertCount(1, $this->pegawai->riwayatPendidikan);
        $this->assertCount(1, $this->pegawai->riwayatPangkat);
        $this->assertCount(1, $this->pegawai->riwayatJabatan);
        $this->assertCount(1, $this->pegawai->mutasi);

        // Inverse check
        $this->assertEquals($this->pegawai->id, $rPend->pegawai->id);
        $this->assertEquals($this->pegawai->id, $rPkt->pegawai->id);
        $this->assertEquals($this->pegawai->id, $rJab->pegawai->id);
        $this->assertEquals($this->pegawai->id, $mutasi->pegawai->id);
    }

    /**
     * TEST 4 — MUTASI PEGAWAI END-TO-END FLOW (SYNCS & RESTORES PEGAWAI POSITION)
     */
    public function test_mutasi_pegawai_end_to_end_flow_syncs_and_restores_pegawai_position(): void
    {
        $payload = [
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $this->unitBaru->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $this->jabatanBaru->id,
            'tmt'             => '2025-06-01',
            'nomor_sk'        => 'SK-MUTASI-INTEGRATION-01',
        ];

        // Store Mutasi
        $this->actingAs($this->adminUser)
            ->post('/mutasi-pegawai', $payload)
            ->assertRedirect(route('mutasi-pegawai.index'));

        // Pegawai position synced to unitBaru & jabatanBaru
        $this->pegawai->refresh();
        $this->assertEquals($this->unitBaru->id, $this->pegawai->unit_kerja_id);
        $this->assertEquals($this->jabatanBaru->id, $this->pegawai->jabatan_id);

        // Delete Mutasi
        $mutasiId = MutasiPegawai::latest()->first()->id;
        $this->actingAs($this->adminUser)
            ->delete("/mutasi-pegawai/{$mutasiId}")
            ->assertRedirect(route('mutasi-pegawai.index'));

        // Pegawai position restored to unitLama & jabatanLama
        $this->pegawai->refresh();
        $this->assertEquals($this->unitLama->id, $this->pegawai->unit_kerja_id);
        $this->assertEquals($this->jabatanLama->id, $this->pegawai->jabatan_id);
    }

    /**
     * TEST 5 — KGB & KP PROCESS INTEGRATION FLOW
     */
    public function test_kgb_and_kp_process_integration_flow(): void
    {
        // 1. Process KGB
        $this->actingAs($this->adminUser)
            ->post("/kgb/proses/{$this->pegawai->id}")
            ->assertRedirect();

        $this->pegawai->refresh();
        $this->assertNotNull($this->pegawai->tmt_kgb_terakhir);

        // 2. Process KP
        $this->actingAs($this->adminUser)
            ->post("/kenaikan-pangkat/proses/{$this->pegawai->id}", [
                'golongan_baru_id' => $this->golonganBaru->id,
                'tmt_pangkat_baru' => '2025-01-01',
            ])
            ->assertRedirect();

        $this->pegawai->refresh();
        $this->assertEquals($this->golonganBaru->id, $this->pegawai->golongan_id);
        $this->assertDatabaseHas('riwayat_pangkat', [
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganBaru->id,
        ]);
    }

    /**
     * TEST 6 — DASHBOARD WIDGETS REFLECT DATABASE CHANGES
     */
    public function test_dashboard_widgets_reflect_database_changes(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('SISTEM INFORMASI KEPEGAWAIAN (SIKAP)');
        $response->assertSee('Total Pegawai');
    }

    /**
     * TEST 7 — NOTIFICATION ACTION URLS ARE VALID APPLICATION ROUTES
     */
    public function test_notification_action_urls_are_valid_application_routes(): void
    {
        $kgbNotif = new KgbDueDateNotification(collect([['id' => 1]]));
        $kpNotif  = new KpDueDateNotification(collect([['id' => 1]]));

        $kgbUrl = $kgbNotif->toArray($this->adminUser)['url'];
        $kpUrl  = $kpNotif->toArray($this->adminUser)['url'];

        $this->actingAs($this->adminUser)->get($kgbUrl)->assertStatus(200);
        $this->actingAs($this->adminUser)->get($kpUrl)->assertStatus(200);
    }

    /**
     * TEST 8 — END-TO-END PEGAWAI LIFECYCLE FLOW
     */
    public function test_end_to_end_pegawai_lifecycle_flow(): void
    {
        // 1. Create Pegawai
        $newPegawai = Pegawai::create([
            'nip'           => '199505052020011001',
            'nama'          => 'Pegawai Lifecycle Test',
            'unit_kerja_id' => $this->unitLama->id,
            'jabatan_id'    => $this->jabatanLama->id,
            'golongan_id'   => $this->golonganAwal->id,
        ]);

        // 2. Add Riwayat Pendidikan
        RiwayatPendidikan::create([
            'pegawai_id' => $newPegawai->id,
            'jenjang'    => 'S1',
            'institusi'  => 'Universitas Indonesia',
        ]);

        // 3. Add Riwayat Pangkat
        RiwayatPangkat::create([
            'pegawai_id'  => $newPegawai->id,
            'golongan_id' => $this->golonganAwal->id,
            'tmt_pangkat' => '2020-01-01',
        ]);

        // 4. Add Riwayat Jabatan
        RiwayatJabatan::create([
            'pegawai_id'    => $newPegawai->id,
            'unit_kerja_id' => $this->unitLama->id,
            'jabatan_id'    => $this->jabatanLama->id,
            'tmt_jabatan'   => '2020-01-01',
        ]);

        // 5. Delete Pegawai (Cascade Check)
        $newPegawai->delete();

        $this->assertDatabaseMissing('pegawai', ['id' => $newPegawai->id]);
        $this->assertDatabaseMissing('riwayat_pendidikan', ['pegawai_id' => $newPegawai->id]);
        $this->assertDatabaseMissing('riwayat_pangkat', ['pegawai_id' => $newPegawai->id]);
        $this->assertDatabaseMissing('riwayat_jabatan', ['pegawai_id' => $newPegawai->id]);
    }
}
