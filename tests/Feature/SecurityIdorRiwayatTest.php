<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatPendidikan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityIdorRiwayatTest extends TestCase
{
    use RefreshDatabase;

    protected User $pegawaiUserA;

    protected User $pegawaiUserB;

    protected User $adminUser;

    protected Pegawai $pegawaiA;

    protected Pegawai $pegawaiB;

    protected Golongan $golongan;

    protected Jabatan $jabatan;

    protected UnitKerja $unitKerja;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->unitKerja = UnitKerja::firstOrCreate(
            ['nama_unit' => 'Fakultas Keperawatan'],
            ['kode_unit' => 'FKP', 'tipe_unit' => 'Fakultas']
        );

        $this->golongan = Golongan::create([
            'nama_golongan' => 'III/a',
            'nama_pangkat' => 'Penata Muda',
        ]);

        $this->jabatan = Jabatan::create([
            'nama_jabatan' => 'Dosen S1 Keperawatan',
        ]);

        $this->pegawaiA = Pegawai::create([
            'nip' => '199001012019011001',
            'nama' => 'Pegawai Korban A',
            'jenis_pegawai' => 'Dosen',
            'pendidikan_terakhir' => 'S2',
            'status_pegawai' => 'Aktif',
        ]);

        $this->pegawaiB = Pegawai::create([
            'nip' => '199202022020022002',
            'nama' => 'Pegawai Pelaku B',
            'jenis_pegawai' => 'Dosen',
            'pendidikan_terakhir' => 'S2',
            'status_pegawai' => 'Aktif',
        ]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->pegawaiUserA = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->pegawaiA->id,
            'must_change_password' => false,
        ]);

        $this->pegawaiUserB = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->pegawaiB->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Pegawai B tidak boleh mengedit/update Riwayat Pangkat milik Pegawai A (IDOR blocked -> 403)
     */
    public function test_pegawai_cannot_update_other_pegawai_riwayat_pangkat(): void
    {
        $riwayatA = RiwayatPangkat::create([
            'pegawai_id' => $this->pegawaiA->id,
            'golongan_id' => $this->golongan->id,
            'tmt' => '2021-04-01',
            'status' => 'aktif',
        ]);

        $payload = [
            'pegawai_id' => $this->pegawaiA->id,
            'golongan_id' => $this->golongan->id,
            'tmt' => '2022-04-01',
            'status' => 'aktif',
        ];

        $response = $this->actingAs($this->pegawaiUserB)
            ->put(route('riwayat-pangkat.update', $riwayatA->id), $payload);

        $response->assertStatus(403);
    }

    /**
     * Pegawai B tidak boleh menghapus Riwayat Pangkat milik Pegawai A (IDOR blocked -> 403)
     */
    public function test_pegawai_cannot_delete_other_pegawai_riwayat_pangkat(): void
    {
        $riwayatA = RiwayatPangkat::create([
            'pegawai_id' => $this->pegawaiA->id,
            'golongan_id' => $this->golongan->id,
            'tmt' => '2021-04-01',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->pegawaiUserB)
            ->delete(route('riwayat-pangkat.destroy', $riwayatA->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('riwayat_pangkat', ['id' => $riwayatA->id]);
    }

    /**
     * Pegawai B tidak boleh menghapus Riwayat Jabatan milik Pegawai A (IDOR blocked -> 403)
     */
    public function test_pegawai_cannot_delete_other_pegawai_riwayat_jabatan(): void
    {
        $riwayatJabatanA = RiwayatJabatan::create([
            'pegawai_id' => $this->pegawaiA->id,
            'jabatan_id' => $this->jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'tmt' => '2020-01-01',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->pegawaiUserB)
            ->delete(route('riwayat-jabatan.destroy', $riwayatJabatanA->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('riwayat_jabatan', ['id' => $riwayatJabatanA->id]);
    }

    /**
     * Pegawai B tidak boleh menghapus Riwayat Pendidikan milik Pegawai A (IDOR blocked -> 403)
     */
    public function test_pegawai_cannot_delete_other_pegawai_riwayat_pendidikan(): void
    {
        $pendidikanA = RiwayatPendidikan::create([
            'pegawai_id' => $this->pegawaiA->id,
            'jenjang' => 'S2',
            'institusi' => 'Universitas Riau',
            'jurusan' => 'Ilmu Keperawatan',
            'tahun_lulus' => 2018,
            'nomor_ijazah' => 'IJZ-12345',
            'tingkat_pendidikan' => 'S2',
        ]);

        $response = $this->actingAs($this->pegawaiUserB)
            ->delete(route('riwayat-pendidikan.destroy', $pendidikanA->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('riwayat_pendidikan', ['id' => $pendidikanA->id]);
    }

    /**
     * Admin berhak mengupdate dan menghapus data riwayat pegawai (Authorized -> 200/302)
     */
    public function test_admin_can_delete_riwayat_pangkat(): void
    {
        $riwayatA = RiwayatPangkat::create([
            'pegawai_id' => $this->pegawaiA->id,
            'golongan_id' => $this->golongan->id,
            'tmt' => '2021-04-01',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('riwayat-pangkat.destroy', $riwayatA->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('riwayat_pangkat', ['id' => $riwayatA->id]);
    }

    /**
     * Pegawai pemilik sah dapat menghapus data riwayat miliknya sendiri
     */
    public function test_owner_can_delete_own_riwayat_pangkat(): void
    {
        $riwayatA = RiwayatPangkat::create([
            'pegawai_id' => $this->pegawaiA->id,
            'golongan_id' => $this->golongan->id,
            'tmt' => '2021-04-01',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->pegawaiUserA)
            ->delete(route('riwayat-pangkat.destroy', $riwayatA->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('riwayat_pangkat', ['id' => $riwayatA->id]);
    }
}
