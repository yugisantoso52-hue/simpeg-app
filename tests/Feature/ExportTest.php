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

class ExportTest extends TestCase
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

        $this->unitKerja = UnitKerja::create(['nama_unit' => 'Dinas Perhubungan', 'kode_unit' => 'DISHUB']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-005', 'nama_jabatan' => 'Pengawas']);
        $golongan = Golongan::create(['nama_golongan' => 'III/b', 'nama_pangkat' => 'Penata Muda Tk. I']);

        $this->pegawai = Pegawai::create([
            'nip'            => '198909092013011001',
            'nama'           => 'Pegawai Export Excel Test',
            'status_pegawai' => 'Aktif',
            'unit_kerja_id'  => $this->unitKerja->id,
            'jabatan_id'     => $jabatan->id,
            'golongan_id'    => $golongan->id,
        ]);
    }

    public function test_admin_and_pimpinan_can_export_duk_excel(): void
    {
        $responseAdmin = $this->actingAs($this->adminUser)
            ->get('/reports/duk/excel');

        $responseAdmin->assertStatus(200);

        $responsePimpinan = $this->actingAs($this->pimpinanUser)
            ->get('/reports/duk/excel?unit_kerja_id=' . $this->unitKerja->id);

        $responsePimpinan->assertStatus(200);
    }

    public function test_admin_can_download_import_pegawai_template_excel(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/pegawai/template');

        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login_for_exports(): void
    {
        $this->get('/reports/duk/excel')->assertRedirect('/login');
        $this->get('/pegawai/template')->assertRedirect('/login');
    }
}
