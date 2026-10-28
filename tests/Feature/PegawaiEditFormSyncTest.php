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

class PegawaiEditFormSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected Pegawai $pegawai;
    protected UnitKerja $unitLama;
    protected UnitKerja $unitBaru;
    protected Jabatan $jabatan;
    protected Golongan $golongan;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $this->unitLama = UnitKerja::create(['kode_unit' => 'UL', 'nama_unit' => 'Bagian Tata Usaha Lama', 'urutan' => 1]);
        $this->unitBaru = UnitKerja::create(['kode_unit' => 'UB', 'nama_unit' => 'Program Studi S1 Baru', 'urutan' => 2]);
        $this->jabatan = Jabatan::create(['kode_jabatan' => 'JB-01', 'nama_jabatan' => 'Lektor Kepala', 'unit_kerja_id' => $this->unitLama->id]);
        $this->golongan = Golongan::create(['nama_golongan' => 'IV/a', 'nama_pangkat' => 'Pembina']);

        $this->pegawai = Pegawai::create([
            'nip' => '197507122005011002',
            'nama' => 'Bakhtiar Test',
            'nama_lengkap' => 'Bakhtiar Test, S.Sos., M.Si.',
            'email' => 'bakhtiar.test@unri.ac.id',
            'unit_kerja_id' => $this->unitLama->id,
            'jabatan_id' => $this->jabatan->id,
            'golongan_id' => $this->golongan->id,
            'status_pegawai' => 'Aktif',
            'jenis_pegawai' => 'PNS',
            'status_asn' => 'ASN',
        ]);

        // Buat riwayat jabatan aktif awal yang merujuk ke unitLama
        RiwayatJabatan::create([
            'pegawai_id' => $this->pegawai->id,
            'jabatan_id' => $this->jabatan->id,
            'unit_kerja_id' => $this->unitLama->id,
            'status' => 'aktif',
            'tmt_jabatan' => '2020-01-01',
        ]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'email' => 'admin@unri.ac.id',
        ]);

        $this->pimpinanUser = User::factory()->create([
            'role_id' => $pimpinanRole->id,
            'pegawai_id' => $this->pegawai->id,
            'email' => 'pimpinan@unri.ac.id',
        ]);
    }

    public function test_updating_unit_kerja_persists_and_does_not_revert_to_old_unit(): void
    {
        $payload = [
            'nip' => $this->pegawai->nip,
            'nama' => $this->pegawai->nama,
            'nama_lengkap' => $this->pegawai->nama_lengkap,
            'unit_kerja_id' => $this->unitBaru->id, // Ganti ke unit baru
            'jabatan_id' => $this->jabatan->id,
            'golongan_id' => $this->golongan->id,
            'status_pegawai' => 'Aktif',
            'jenis_pegawai' => 'PNS',
            'status_asn' => 'ASN',
        ];

        $response = $this->actingAs($this->adminUser)
            ->put(route('pegawai.update', $this->pegawai->id), $payload);

        $response->assertSessionHasNoErrors();

        // Refresh model dari database
        $this->pegawai->refresh();

        // Verifikasi bahwa unit kerja pegawai benar-benar berubah ke unit baru
        $this->assertEquals($this->unitBaru->id, $this->pegawai->unit_kerja_id);

        // Verifikasi bahwa riwayat jabatan aktif juga ikut tersinkronisasi
        $activeJabatan = $this->pegawai->riwayatJabatan()->where('status', 'aktif')->first();
        $this->assertNotNull($activeJabatan);
        $this->assertEquals($this->unitBaru->id, $activeJabatan->unit_kerja_id);
    }

    public function test_pimpinan_executive_can_update_pegawai_unit_kerja_and_kepegawaian(): void
    {
        $payload = [
            'nip' => $this->pegawai->nip,
            'nama' => $this->pegawai->nama,
            'nama_lengkap' => $this->pegawai->nama_lengkap,
            'unit_kerja_id' => $this->unitBaru->id,
            'jabatan_id' => $this->jabatan->id,
            'golongan_id' => $this->golongan->id,
            'status_pegawai' => 'Aktif',
            'jenis_pegawai' => 'PNS',
            'status_asn' => 'ASN',
        ];

        $response = $this->actingAs($this->pimpinanUser)
            ->put(route('pegawai.update', $this->pegawai->id), $payload);

        $response->assertSessionHasNoErrors();

        $this->pegawai->refresh();
        $this->assertEquals($this->unitBaru->id, $this->pegawai->unit_kerja_id);
    }
}
