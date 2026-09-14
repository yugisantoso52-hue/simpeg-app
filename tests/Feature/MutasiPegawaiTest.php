<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\MutasiPegawai;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MutasiPegawaiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected User $stafUser;

    protected Pegawai $pegawai;
    protected UnitKerja $unitLama;
    protected UnitKerja $unitBaru;
    protected Jabatan $jabatanLama;
    protected Jabatan $jabatanBaru;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $stafRole     = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);
        $this->stafUser     = User::factory()->create(['role_id' => $stafRole->id]);

        $this->unitLama = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $this->unitBaru = UnitKerja::create(['nama_unit' => 'Dinas Kesehatan', 'kode_unit' => 'DINKES']);

        $this->jabatanLama = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Staf Edukasi']);
        $this->jabatanBaru = Jabatan::create(['kode_jabatan' => 'JAB-002', 'nama_jabatan' => 'Staf Medis']);

        $this->pegawai = Pegawai::create([
            'nip'           => '199606062021011001',
            'nama'          => 'Pegawai Mutasi Test',
            'unit_kerja_id' => $this->unitLama->id,
            'jabatan_id'    => $this->jabatanLama->id,
        ]);
    }

    /**
     * TEST 1 — INDEX MUTASI PEGAWAI (ADMIN ONLY)
     */
    public function test_admin_can_view_mutasi_index(): void
    {
        MutasiPegawai::create([
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $this->unitBaru->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $this->jabatanBaru->id,
            'tmt'             => '2025-01-01',
            'nomor_sk'        => 'SK-MUTASI-001',
        ]);

        $this->actingAs($this->adminUser)
            ->get('/mutasi-pegawai')
            ->assertStatus(200)
            ->assertSee('Pegawai Mutasi Test');
    }

    /**
     * TEST 2 — CREATE FORM
     */
    public function test_admin_can_view_create_mutasi_form(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/mutasi-pegawai/create')
            ->assertStatus(200)
            ->assertSee('Dinas Pendidikan');
    }

    /**
     * TEST 3 — STORE MUTASI PEGAWAI & SYNCS MASTER PEGAWAI
     */
    public function test_admin_can_store_valid_mutasi_and_syncs_pegawai(): void
    {
        Storage::fake('local');

        $fileSk = UploadedFile::fake()->create('sk_mutasi.pdf', 500, 'application/pdf');

        $payload = [
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $this->unitBaru->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $this->jabatanBaru->id,
            'tmt'             => '2025-06-01',
            'nomor_sk'        => 'SK-MUTASI-2025-002',
            'file_sk'         => $fileSk,
            'keterangan'      => 'Mutasi antar dinas',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/mutasi-pegawai', $payload);

        $response->assertRedirect(route('mutasi-pegawai.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mutasi_pegawai', [
            'pegawai_id' => $this->pegawai->id,
            'nomor_sk'   => 'SK-MUTASI-2025-002',
        ]);

        // Sync check in master Pegawai
        $this->pegawai->refresh();
        $this->assertEquals($this->unitBaru->id, $this->pegawai->unit_kerja_id);
        $this->assertEquals($this->jabatanBaru->id, $this->pegawai->jabatan_id);
    }

    /**
     * TEST 4 — STORE MUTASI VALIDATION FAILURES
     */
    public function test_store_mutasi_fails_validation_for_missing_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/mutasi-pegawai', []);

        $response->assertSessionHasErrors([
            'pegawai_id',
            'unit_lama_id',
            'unit_baru_id',
            'jabatan_lama_id',
            'jabatan_baru_id',
            'tmt',
        ]);
    }

    /**
     * TEST 5 — EDIT FORM
     */
    public function test_admin_can_view_edit_mutasi_form(): void
    {
        $mutasi = MutasiPegawai::create([
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $this->unitBaru->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $this->jabatanBaru->id,
            'tmt'             => '2025-01-01',
        ]);

        $this->actingAs($this->adminUser)
            ->get("/mutasi-pegawai/{$mutasi->id}/edit")
            ->assertStatus(200);
    }

    /**
     * TEST 6 — UPDATE MUTASI & SYNCS PEGAWAI
     */
    public function test_admin_can_update_mutasi_and_syncs_pegawai(): void
    {
        $unitKetiga = UnitKerja::create(['nama_unit' => 'Dinas Perhubungan', 'kode_unit' => 'DISHUB']);
        $jabatanKetiga = Jabatan::create(['kode_jabatan' => 'JAB-003', 'nama_jabatan' => 'Staf Operasional']);

        $mutasi = MutasiPegawai::create([
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $this->unitBaru->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $this->jabatanBaru->id,
            'tmt'             => '2025-01-01',
        ]);

        $payload = [
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $unitKetiga->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $jabatanKetiga->id,
            'tmt'             => '2025-02-01',
            'nomor_sk'        => 'SK-UPDATED-003',
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/mutasi-pegawai/{$mutasi->id}", $payload);

        $response->assertRedirect(route('mutasi-pegawai.index'));

        $this->assertDatabaseHas('mutasi_pegawai', [
            'id'       => $mutasi->id,
            'nomor_sk' => 'SK-UPDATED-003',
        ]);

        $this->pegawai->refresh();
        $this->assertEquals($unitKetiga->id, $this->pegawai->unit_kerja_id);
        $this->assertEquals($jabatanKetiga->id, $this->pegawai->jabatan_id);
    }

    /**
     * TEST 7 — DELETE MUTASI RESTORES ORIGINAL UNIT & JABATAN
     */
    public function test_admin_can_delete_mutasi_and_restores_pegawai_original_position(): void
    {
        // First mutasi changed pegawai position from unitLama/jabatanLama to unitBaru/jabatanBaru
        $this->pegawai->update([
            'unit_kerja_id' => $this->unitBaru->id,
            'jabatan_id'    => $this->jabatanBaru->id,
        ]);

        $mutasi = MutasiPegawai::create([
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $this->unitBaru->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $this->jabatanBaru->id,
            'tmt'             => '2025-01-01',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete("/mutasi-pegawai/{$mutasi->id}");

        $response->assertRedirect(route('mutasi-pegawai.index'));

        $this->assertDatabaseMissing('mutasi_pegawai', ['id' => $mutasi->id]);

        // Pegawai position restored to unitLama & jabatanLama
        $this->pegawai->refresh();
        $this->assertEquals($this->unitLama->id, $this->pegawai->unit_kerja_id);
        $this->assertEquals($this->jabatanLama->id, $this->pegawai->jabatan_id);
    }

    /**
     * TEST 8 — AUTHORIZATION RESTRICTIONS (PIMPINAN & STAF DENIED 403)
     */
    public function test_pimpinan_and_staf_are_denied_write_access_to_mutasi(): void
    {
        $mutasi = MutasiPegawai::create([
            'pegawai_id'      => $this->pegawai->id,
            'unit_lama_id'    => $this->unitLama->id,
            'unit_baru_id'    => $this->unitBaru->id,
            'jabatan_lama_id' => $this->jabatanLama->id,
            'jabatan_baru_id' => $this->jabatanBaru->id,
            'tmt'             => '2025-01-01',
        ]);

        // Pimpinan Denied 403 on ALL mutasi routes (since route group is role:admin)
        $this->actingAs($this->pimpinanUser)->get('/mutasi-pegawai')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get('/mutasi-pegawai/create')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post('/mutasi-pegawai', [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get("/mutasi-pegawai/{$mutasi->id}/edit")->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->put("/mutasi-pegawai/{$mutasi->id}", [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->delete("/mutasi-pegawai/{$mutasi->id}")->assertStatus(403);

        // Staf Denied 403
        $this->actingAs($this->stafUser)->get('/mutasi-pegawai')->assertStatus(403);
    }

    /**
     * TEST 9 — GUEST REDIRECT TO LOGIN
     */
    public function test_guest_redirected_to_login_for_mutasi(): void
    {
        $this->get('/mutasi-pegawai')->assertRedirect('/login');
    }

    /**
     * TEST 10 — GET PEGAWAI MUTASI JSON ENDPOINT
     */
    public function test_admin_can_fetch_pegawai_details_json_for_mutasi(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get("/pegawai-mutasi/{$this->pegawai->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'unit_id'    => $this->unitLama->id,
            'unit'       => 'Dinas Pendidikan',
            'jabatan_id' => $this->jabatanLama->id,
            'jabatan'    => 'Staf Edukasi',
        ]);
    }
}
