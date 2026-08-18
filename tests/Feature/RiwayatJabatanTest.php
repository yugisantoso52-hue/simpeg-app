<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiwayatJabatanTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;

    protected Pegawai $pegawai;
    protected Jabatan $jabatan;
    protected UnitKerja $unitKerja;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);

        $this->unitKerja = UnitKerja::create([
            'nama_unit' => 'Dinas Kesehatan',
            'kode_unit' => 'DINKES-01',
        ]);

        $this->jabatan = Jabatan::create([
            'kode_jabatan' => 'JAB-HEALTH-01',
            'nama_jabatan' => 'Dokter Umum Pertama',
        ]);

        $this->pegawai = Pegawai::create([
            'nip'           => '199404042019041001',
            'nama'          => 'Dr. Anton Jabatan',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);
    }

    public function test_admin_and_pimpinan_can_view_riwayat_jabatan_index(): void
    {
        RiwayatJabatan::create([
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'tmt_jabatan'   => '2021-01-01',
        ]);

        $this->actingAs($this->adminUser)
            ->get('/riwayat-jabatan')
            ->assertStatus(200)
            ->assertSee('Dr. Anton Jabatan');

        $this->actingAs($this->pimpinanUser)
            ->get('/riwayat-jabatan')
            ->assertStatus(200)
            ->assertSee('Dr. Anton Jabatan');
    }

    public function test_admin_can_store_riwayat_jabatan_with_sk_file(): void
    {
        Storage::fake('local');

        $fileSk = UploadedFile::fake()->create('sk_jabatan.pdf', 900, 'application/pdf');

        $payload = [
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'tmt_jabatan'   => '2023-01-01',
            'nomor_sk'      => 'SK-JAB/2023/100',
            'file_sk'       => $fileSk,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/riwayat-jabatan', $payload);

        $response->assertRedirect(route('riwayat-jabatan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('riwayat_jabatan', [
            'pegawai_id' => $this->pegawai->id,
            'nomor_sk'   => 'SK-JAB/2023/100',
        ]);
    }

    public function test_admin_can_update_riwayat_jabatan(): void
    {
        $riwayat = RiwayatJabatan::create([
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'tmt_jabatan'   => '2021-01-01',
            'nomor_sk'      => 'OLD-SK-001',
        ]);

        $payload = [
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'tmt_jabatan'   => '2021-01-01',
            'nomor_sk'      => 'UPDATED-SK-002',
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/riwayat-jabatan/{$riwayat->id}", $payload);

        $response->assertRedirect(route('riwayat-jabatan.index'));

        $this->assertDatabaseHas('riwayat_jabatan', [
            'id'       => $riwayat->id,
            'nomor_sk' => 'UPDATED-SK-002',
        ]);
    }

    public function test_admin_can_delete_riwayat_jabatan(): void
    {
        $riwayat = RiwayatJabatan::create([
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'tmt_jabatan'   => '2021-01-01',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete("/riwayat-jabatan/{$riwayat->id}");

        $response->assertRedirect(route('riwayat-jabatan.index'));

        $this->assertDatabaseMissing('riwayat_jabatan', [
            'id' => $riwayat->id,
        ]);
    }

    public function test_pimpinan_has_read_access_and_write_denied(): void
    {
        $riwayat = RiwayatJabatan::create([
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'tmt_jabatan'   => '2021-01-01',
        ]);

        // READ Allowed
        $this->actingAs($this->pimpinanUser)->get('/riwayat-jabatan')->assertStatus(200);

        // WRITE Denied (403)
        $this->actingAs($this->pimpinanUser)->get('/riwayat-jabatan/create')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post('/riwayat-jabatan', [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get("/riwayat-jabatan/{$riwayat->id}/edit")->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->put("/riwayat-jabatan/{$riwayat->id}", [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->delete("/riwayat-jabatan/{$riwayat->id}")->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/riwayat-jabatan')->assertRedirect('/login');
    }
}
