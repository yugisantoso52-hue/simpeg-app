<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiUnitKerjaPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $pimpinan;
    protected User $pegawaiUser;
    protected Pegawai $pegawaiModel;
    protected UnitKerja $uk1;
    protected UnitKerja $uk2;
    protected Jabatan $jabatan;
    protected Golongan $golongan;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $rolePimpinan = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $rolePegawai = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->uk1 = UnitKerja::create(['nama_unit' => 'Bagian Tata Usaha', 'kode_unit' => 'TU']);
        $this->uk2 = UnitKerja::create(['nama_unit' => 'Program Studi Ilmu Keperawatan', 'kode_unit' => 'PSIK']);

        $this->jabatan = Jabatan::create(['nama_jabatan' => 'Dosen Peneliti', 'kode_jabatan' => 'DSN-01']);
        $this->golongan = Golongan::create(['nama_golongan' => 'III/b', 'nama_pangkat' => 'Penata Muda Tk. I']);

        $this->pegawaiModel = Pegawai::create([
            'nip'           => '198501012015011001',
            'nama'          => 'Dr. Siti Nurhaliza',
            'unit_kerja_id' => $this->uk1->id,
            'golongan_id'   => $this->golongan->id,
        ]);

        $this->admin = User::factory()->create([
            'role_id'              => $roleAdmin->id,
            'must_change_password' => false,
        ]);

        $this->pimpinan = User::factory()->create([
            'role_id'              => $rolePimpinan->id,
            'must_change_password' => false,
        ]);

        $this->pegawaiUser = User::factory()->create([
            'role_id'              => $rolePegawai->id,
            'pegawai_id'           => $this->pegawaiModel->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Test persistensi Unit Kerja saat pegawai tidak memiliki riwayat jabatan awal (kondisi 77 pegawai FKP UNRI).
     */
    public function test_unit_kerja_persists_when_pegawai_has_no_riwayat_jabatan(): void
    {
        $this->assertCount(0, $this->pegawaiModel->riwayatJabatan);

        $payload = [
            'nip'           => $this->pegawaiModel->nip,
            'nama'          => $this->pegawaiModel->nama,
            'unit_kerja_id' => $this->uk2->id,
        ];

        $response = $this->actingAs($this->admin)->put(route('pegawai.update', $this->pegawaiModel->id), $payload);
        $response->assertRedirect();

        $this->pegawaiModel->refresh();
        $this->assertEquals($this->uk2->id, $this->pegawaiModel->unit_kerja_id);
    }

    /**
     * Test persistensi Unit Kerja saat diedit oleh akun Pegawai mandiri (Employee Self Service).
     */
    public function test_unit_kerja_persists_when_edited_by_pegawai_user(): void
    {
        $payload = [
            'nip'           => $this->pegawaiModel->nip,
            'nama'          => $this->pegawaiModel->nama,
            'unit_kerja_id' => $this->uk2->id,
        ];

        $response = $this->actingAs($this->pegawaiUser)->put(route('pegawai.update', $this->pegawaiModel->id), $payload);
        $response->assertRedirect();

        $this->pegawaiModel->refresh();
        $this->assertEquals($this->uk2->id, $this->pegawaiModel->unit_kerja_id);
    }

    /**
     * Test persistensi Unit Kerja saat diedit oleh akun Pimpinan.
     */
    public function test_unit_kerja_persists_when_edited_by_pimpinan_user(): void
    {
        // Berikan pegawai id pada pimpinan
        $pimpinanPegawai = Pegawai::create([
            'nip'           => '197001012000011001',
            'nama'          => 'Prof. Pimpinan Dekan',
            'unit_kerja_id' => $this->uk1->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);
        $this->pimpinan->update(['pegawai_id' => $pimpinanPegawai->id]);

        $payload = [
            'nip'           => $pimpinanPegawai->nip,
            'nama'          => $pimpinanPegawai->nama,
            'unit_kerja_id' => $this->uk2->id,
        ];

        $response = $this->actingAs($this->pimpinan)->put(route('pegawai.update', $pimpinanPegawai->id), $payload);
        $response->assertRedirect();

        $pimpinanPegawai->refresh();
        $this->assertEquals($this->uk2->id, $pimpinanPegawai->unit_kerja_id);
    }

    /**
     * Test persistensi Unit Kerja ketika riwayat jabatan aktif sudah ada sebelumnya.
     */
    public function test_unit_kerja_updates_both_pegawai_and_active_riwayat_jabatan(): void
    {
        RiwayatJabatan::create([
            'pegawai_id'    => $this->pegawaiModel->id,
            'jabatan_id'    => $this->jabatan->id,
            'unit_kerja_id' => $this->uk1->id,
            'tmt_jabatan'   => '2023-01-01',
            'status'        => 'aktif',
        ]);

        $payload = [
            'nip'           => $this->pegawaiModel->nip,
            'nama'          => $this->pegawaiModel->nama,
            'unit_kerja_id' => $this->uk2->id,
            'jabatan_id'    => $this->jabatan->id,
            'riwayat_jabatan' => [
                [
                    'id'            => $this->pegawaiModel->riwayatJabatan()->first()->id,
                    'jabatan_id'    => $this->jabatan->id,
                    'unit_kerja_id' => $this->uk1->id, // row tabel riwayat lama
                    'tmt_jabatan'   => '2023-01-01',
                    'status'        => 'aktif',
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->put(route('pegawai.update', $this->pegawaiModel->id), $payload);
        $response->assertRedirect();

        $this->pegawaiModel->refresh();
        $this->assertEquals($this->uk2->id, $this->pegawaiModel->unit_kerja_id);

        $activeRj = $this->pegawaiModel->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->first();
        $this->assertNotNull($activeRj);
        $this->assertEquals($this->uk2->id, $activeRj->unit_kerja_id);
    }
}
