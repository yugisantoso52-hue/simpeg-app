<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected User $stafUser;

    protected Pegawai $pegawai;
    protected UnitKerja $unitKerja;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $stafRole     = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);
        $this->stafUser     = User::factory()->create(['role_id' => $stafRole->id]);

        $this->unitKerja = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru Ahli']);
        $golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);

        $this->pegawai = Pegawai::create([
            'nip'            => '198808082012011001',
            'nama'           => 'Pegawai Report Test',
            'status_pegawai' => 'Aktif',
            'unit_kerja_id'  => $this->unitKerja->id,
            'jabatan_id'     => $jabatan->id,
            'golongan_id'    => $golongan->id,
        ]);
    }

    public function test_admin_and_pimpinan_can_export_duk_pdf(): void
    {
        $responseAdmin = $this->actingAs($this->adminUser)
            ->get('/reports/duk/pdf');

        $responseAdmin->assertStatus(200);
        $responseAdmin->assertHeader('content-type', 'application/pdf');

        $responsePimpinan = $this->actingAs($this->pimpinanUser)
            ->get('/reports/duk/pdf?unit_kerja_id=' . $this->unitKerja->id);

        $responsePimpinan->assertStatus(200);
        $responsePimpinan->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_and_pimpinan_can_export_kgb_pdf(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get("/reports/kgb/{$this->pegawai->id}/pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_and_pimpinan_can_export_individual_profil_pdf(): void
    {
        $response = $this->actingAs($this->pimpinanUser)
            ->get("/pegawai/{$this->pegawai->id}/download-pdf");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_duk_pdf_handles_empty_database_safely(): void
    {
        Pegawai::query()->delete();

        $response = $this->actingAs($this->adminUser)
            ->get('/reports/duk/pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_guest_is_redirected_to_login_for_reports(): void
    {
        $this->get('/reports/duk/pdf')->assertRedirect('/login');
    }
}
