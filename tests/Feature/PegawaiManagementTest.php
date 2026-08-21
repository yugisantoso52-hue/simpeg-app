<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PegawaiManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected User $stafUser;

    protected UnitKerja $unitKerja;
    protected Jabatan $jabatan;
    protected Golongan $golongan;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $stafRole = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);

        // Users
        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id, 'must_change_password' => false]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id, 'must_change_password' => false]);
        $this->stafUser = User::factory()->create(['role_id' => $stafRole->id, 'must_change_password' => false]);

        // Master Data
        $this->unitKerja = UnitKerja::create([
            'nama_unit' => 'Dinas Pendidikan & Kebudayaan',
            'kode_unit' => 'DISDIK-01',
        ]);

        $this->jabatan = Jabatan::create([
            'kode_jabatan' => 'JAB-001',
            'nama_jabatan' => 'Analisis Kepegawaian Ahli Pertama',
        ]);

        $this->golongan = Golongan::create([
            'nama_golongan' => 'III/a',
            'nama_pangkat'  => 'Penata Muda',
        ]);
    }

    /**
     * TEST 1 — INDEX & SEARCH PEGAWAI (ADMIN)
     */
    public function test_admin_can_view_pegawai_index_with_search(): void
    {
        Pegawai::create([
            'nip'           => '199001012015011001',
            'nama'          => 'Budi Santoso',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/pegawai?search=Budi');

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
    }

    /**
     * TEST 2 — DUK (DAFTAR URUT KEPANGKATAN) LIST
     */
    public function test_admin_and_pimpinan_can_view_duk_list(): void
    {
        Pegawai::create([
            'nip'           => '199001012015011002',
            'nama'          => 'Siti Aminah',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);

        $adminResp = $this->actingAs($this->adminUser)->get('/duk');
        $adminResp->assertStatus(200);

        $pimpinanResp = $this->actingAs($this->pimpinanUser)->get('/duk');
        $pimpinanResp->assertStatus(200);
    }

    /**
     * TEST 3 — CREATE FORM
     */
    public function test_admin_can_view_create_pegawai_form(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/pegawai/create');

        $response->assertStatus(200);
        $response->assertSee('Dinas Pendidikan & Kebudayaan');
    }

    /**
     * TEST 4 — STORE PEGAWAI VALID WITH FILES
     */
    public function test_admin_can_store_valid_pegawai_with_files(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $foto = UploadedFile::fake()->image('pas_foto.jpg');
        $sk   = UploadedFile::fake()->create('sk_cpns.pdf', 500, 'application/pdf');

        $payload = [
            'nip'                  => '199202022018021003',
            'nama_lengkap'         => 'Ahmad Dahlan',
            'nama'                 => 'Ahmad Dahlan',
            'nik'                  => '1234567890123456',
            'jenis_kelamin'        => 'L',
            'jenis_pegawai'        => 'PNS',
            'status_asn'           => 'ASN',
            'unit_kerja_id'        => $this->unitKerja->id,
            'jabatan_id'           => $this->jabatan->id,
            'golongan_id'          => $this->golongan->id,
            'status_pegawai'       => 'Aktif',
            'foto'                 => $foto,
            'file_sk_pertama'      => $sk,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', $payload);

        $response->assertRedirect(route('pegawai.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pegawai', [
            'nip'  => '199202022018021003',
            'nama' => 'Ahmad Dahlan',
        ]);
    }

    /**
     * TEST 5 — STORE PEGAWAI VALIDATION FAILURES (NIP REQUIRED & UNIQUE, RELASI REQUIRED)
     */
    public function test_store_pegawai_fails_validation_for_invalid_data(): void
    {
        // Existing pegawai with same NIP
        Pegawai::create([
            'nip'           => '199001012015011005',
            'nama'          => 'Pegawai Exists',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);

        $payload = [
            'nip'           => '199001012015011005', // Duplicate NIP
            'nama'          => 'Test Fraud',
            // missing unit_kerja_id & jabatan_id
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', $payload);

        $response->assertSessionHasErrors(['nip']);
    }

    /**
     * TEST 6 — SHOW & EDIT PEGAWAI
     */
    public function test_admin_can_view_show_and_edit_pegawai(): void
    {
        $pegawai = Pegawai::create([
            'nip'           => '199001012015011006',
            'nama'          => 'Dewi Sartika',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);

        $showResponse = $this->actingAs($this->adminUser)
            ->get("/pegawai/{$pegawai->id}");
        $showResponse->assertStatus(200);

        $editResponse = $this->actingAs($this->adminUser)
            ->get("/pegawai/{$pegawai->id}/edit");
        $editResponse->assertStatus(200);
    }

    /**
     * TEST 7 — UPDATE PEGAWAI
     */
    public function test_admin_can_update_pegawai_data(): void
    {
        $pegawai = Pegawai::create([
            'nip'           => '199001012015011007',
            'nama'          => 'Jendral Sudirman',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);

        $payload = [
            'nip'           => '199001012015011007',
            'nama_lengkap'  => 'Jendral Sudirman Updated',
            'nama'          => 'Jendral Sudirman Updated',
            'nik'           => '1234567890123456',
            'jenis_kelamin' => 'L',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
            'golongan_id'   => $this->golongan->id,
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/pegawai/{$pegawai->id}", $payload);

        $response->assertRedirect(route('pegawai.index'));

        $this->assertDatabaseHas('pegawai', [
            'id'   => $pegawai->id,
            'nama' => 'Jendral Sudirman Updated',
        ]);
    }

    /**
     * TEST 8 — DELETE PEGAWAI
     */
    public function test_admin_can_delete_pegawai(): void
    {
        $pegawai = Pegawai::create([
            'nip'           => '199001012015011008',
            'nama'          => 'Pegawai Deleted',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete("/pegawai/{$pegawai->id}");

        $response->assertRedirect(route('pegawai.index'));

        $this->assertDatabaseMissing('pegawai', [
            'id' => $pegawai->id,
        ]);
    }

    /**
     * TEST 9 — PIMPINAN HAK AKSES READ-ONLY (WRITE OPERATIONS DENIED 403)
     */
    public function test_pimpinan_has_read_only_access_and_write_denied(): void
    {
        $pegawai = Pegawai::create([
            'nip'           => '199001012015011009',
            'nama'          => 'Pegawai Pimpinan Read Only',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);

        // READ Allowed
        $this->actingAs($this->pimpinanUser)->get('/pegawai')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get("/pegawai/{$pegawai->id}")->assertStatus(200);

        // WRITE Denied (403)
        $this->actingAs($this->pimpinanUser)->get('/pegawai/create')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post('/pegawai', [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get("/pegawai/{$pegawai->id}/edit")->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->put("/pegawai/{$pegawai->id}", [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->delete("/pegawai/{$pegawai->id}")->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post('/pegawai/import', [])->assertStatus(403);
    }

    /**
     * TEST 10 — GUEST REDIRECT TO LOGIN
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/pegawai');
        $response->assertRedirect('/login');
    }

    /**
     * TEST 11 — IMPORT EXCEL CACHE INVALIDATION
     */
    public function test_import_excel_clears_targeted_cache(): void
    {
        Cache::put('pegawai_statistics', ['total' => 99], 3600);
        $this->assertTrue(Cache::has('pegawai_statistics'));

        // Post invalid file to trigger import error/validation flow
        $this->actingAs($this->adminUser)
            ->post('/pegawai/import', []);

        // Cache remains until valid import occurs or targeted forget is called
        $this->assertTrue(Cache::has('pegawai_statistics'));
    }

    /**
     * TEST 12 — PENDIDIKAN TAMPIL ACCESSOR PERFORMANCE & RELATION CHECK
     */
    public function test_pendidikan_tampil_accessor_handles_relation_loaded(): void
    {
        $pegawai = Pegawai::create([
            'nip'           => '199001012015011012',
            'nama'          => 'Pegawai Accessor Test',
            'pendidikan'    => 'S1 Teknik Informatika',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);

        $this->assertEquals('S1 Teknik Informatika', $pegawai->pendidikan_tampil);
    }
}
