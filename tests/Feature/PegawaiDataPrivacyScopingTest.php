<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RiwayatDiklat;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatOrganisasi;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatPendidikan;
use App\Models\RiwayatPenghargaan;
use App\Models\RiwayatPublikasi;
use App\Models\RiwayatSkp;
use App\Models\RiwayatStrSip;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiDataPrivacyScopingTest extends TestCase
{
    use RefreshDatabase;

    protected User $pegawaiUser1;
    protected Pegawai $pegawai1;

    protected User $pegawaiUser2;
    protected Pegawai $pegawai2;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $golongan = Golongan::create(['nama_golongan' => 'IV/a', 'nama_pangkat' => 'Pembina']);
        $unit = UnitKerja::create(['kode_unit' => 'JIK', 'nama_unit' => 'Jurusan Ilmu Kelautan', 'urutan' => 1]);
        $jabatan = Jabatan::create(['kode_jabatan' => 'LK-01', 'nama_jabatan' => 'Lektor Kepala', 'unit_kerja_id' => $unit->id]);

        $this->pegawai1 = Pegawai::create([
            'nama' => 'Dr. Pegawai Satu',
            'nip' => '198001012005011001',
            'email' => 'pegawai1@unri.ac.id',
            'no_hp' => '081234567891',
            'kategori_kepegawaian' => 'Dosen',
            'jenis_pegawai' => 'PNS',
            'status_asn' => 'ASN',
            'status_pegawai' => 'Aktif',
            'golongan_id' => $golongan->id,
            'unit_kerja_id' => $unit->id,
            'jabatan_id' => $jabatan->id,
        ]);

        $this->pegawai2 = Pegawai::create([
            'nama' => 'Dr. Pegawai Dua',
            'nip' => '198202022006021002',
            'email' => 'pegawai2@unri.ac.id',
            'no_hp' => '081234567892',
            'kategori_kepegawaian' => 'Dosen',
            'jenis_pegawai' => 'PNS',
            'status_asn' => 'ASN',
            'status_pegawai' => 'Aktif',
            'golongan_id' => $golongan->id,
            'unit_kerja_id' => $unit->id,
            'jabatan_id' => $jabatan->id,
        ]);

        $this->pegawaiUser1 = User::factory()->create([
            'email' => 'pegawai1@unri.ac.id',
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->pegawai1->id,
        ]);

        $this->pegawaiUser2 = User::factory()->create([
            'email' => 'pegawai2@unri.ac.id',
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->pegawai2->id,
        ]);

        $this->adminUser = User::factory()->create([
            'email' => 'admin@unri.ac.id',
            'role_id' => $adminRole->id,
        ]);
    }

    public function test_pegawai_only_sees_their_own_record_on_duk(): void
    {
        $response = $this->actingAs($this->pegawaiUser1)->get(route('duk.index'));
        $response->assertStatus(200);
        $response->assertSee('Dr. Pegawai Satu');
        $response->assertDontSee('Dr. Pegawai Dua');
        $response->assertSee('Pemberitahuan Privasi Data ASN');
        $response->assertDontSee('Cetak DUK (PDF)');
    }

    public function test_admin_sees_all_records_on_duk_and_export_buttons(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('duk.index'));
        $response->assertStatus(200);
        $response->assertSee('Dr. Pegawai Satu');
        $response->assertSee('Dr. Pegawai Dua');
        $response->assertSee('Cetak DUK (PDF)');
        $response->assertSee('Export DUK (Excel)');
    }

    public function test_pegawai_cannot_export_faculty_wide_duk(): void
    {
        $this->actingAs($this->pegawaiUser1)
            ->get(route('reports.duk.pdf'))
            ->assertStatus(403);

        $this->actingAs($this->pegawaiUser1)
            ->get(route('reports.duk.excel'))
            ->assertStatus(403);
    }

    public function test_pegawai_only_sees_their_own_riwayat_records(): void
    {
        // 1. Pendidikan
        RiwayatPendidikan::create([
            'pegawai_id' => $this->pegawai1->id,
            'jenjang' => 'S3',
            'institusi' => 'Institut Pegawai Satu',
            'tahun_lulus' => 2015,
        ]);
        RiwayatPendidikan::create([
            'pegawai_id' => $this->pegawai2->id,
            'jenjang' => 'S3',
            'institusi' => 'Institut Pegawai Dua',
            'tahun_lulus' => 2018,
        ]);

        $resPend = $this->actingAs($this->pegawaiUser1)->get(route('riwayat-pendidikan.index'));
        $resPend->assertStatus(200);
        $resPend->assertSee('Institut Pegawai Satu');
        $resPend->assertDontSee('Institut Pegawai Dua');

        // 2. Diklat
        RiwayatDiklat::create([
            'pegawai_id' => $this->pegawai1->id,
            'nama_diklat' => 'Diklat Khusus Pegawai Satu',
            'penyelenggara' => 'LAN RI',
            'tanggal_mulai' => '2023-01-01',
            'tanggal_selesai' => '2023-01-10',
            'status' => 'Aktif',
        ]);
        RiwayatDiklat::create([
            'pegawai_id' => $this->pegawai2->id,
            'nama_diklat' => 'Diklat Rahasia Pegawai Dua',
            'penyelenggara' => 'BKN',
            'tanggal_mulai' => '2023-02-01',
            'tanggal_selesai' => '2023-02-10',
            'status' => 'Aktif',
        ]);

        $resDik = $this->actingAs($this->pegawaiUser1)->get(route('riwayat-diklat.index'));
        $resDik->assertStatus(200);
        $resDik->assertSee('Diklat Khusus Pegawai Satu');
        $resDik->assertDontSee('Diklat Rahasia Pegawai Dua');
    }

    public function test_peta_jabatan_masks_sensitive_contact_for_non_executive(): void
    {
        $jabatan = $this->pegawai1->jabatan;

        // Akun pegawai biasa
        $resPegawai = $this->actingAs($this->pegawaiUser1)->get(route('anjab.peta-jabatan.detail', $jabatan->id));
        $resPegawai->assertStatus(200);
        $dataPegawai = $resPegawai->json('pegawai_list');
        $this->assertNotEmpty($dataPegawai);
        $this->assertArrayNotHasKey('no_hp', $dataPegawai[0]);
        $this->assertArrayNotHasKey('email', $dataPegawai[0]);

        // Akun admin
        $resAdmin = $this->actingAs($this->adminUser)->get(route('anjab.peta-jabatan.detail', $jabatan->id));
        $resAdmin->assertStatus(200);
        $dataAdmin = $resAdmin->json('pegawai_list');
        $this->assertNotEmpty($dataAdmin);
        $this->assertArrayHasKey('no_hp', $dataAdmin[0]);
        $this->assertArrayHasKey('email', $dataAdmin[0]);
    }
}
