<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\Jabatan;
use App\Models\Golongan;
use App\Models\UnitKerja;
use App\Models\KuotaCutiPegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TendikDataSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'admin', 'display_name' => 'Administrator']);
        $this->adminUser = User::create([
            'name'     => 'Admin Tendik Test',
            'email'    => 'admin_test_tendik@fkp.unri.ac.id',
            'password' => bcrypt('password'),
            'role_id'  => $roleAdmin->id,
        ]);
    }

    public function test_daftar_tendik_page_renders_with_tendik_scope()
    {
        $unitKerja = UnitKerja::create(['kode_unit' => 'UK-KEP', 'nama_unit' => 'Keuangan dan Kepegawaian']);
        $jabatan   = Jabatan::create(['kode_jabatan' => 'JAB-KABAG', 'nama_jabatan' => 'Kepala Bagian Umum', 'unit_kerja_id' => $unitKerja->id]);
        $golongan  = Golongan::create(['kode_golongan' => 'IV/B', 'nama_golongan' => 'IV/b', 'nama_pangkat' => 'Pembina Tingkat I']);

        $tendikPns = Pegawai::create([
            'nip'                 => '197507122005011002',
            'nama'                => 'Bakhtiar',
            'gelar_belakang'      => 'S.Sos., M.Si',
            'jenis_kelamin'       => 'L',
            'jenis_pegawai'       => 'PNS',
            'status_asn'          => 'ASN',
            'jabatan_id'          => $jabatan->id,
            'golongan_id'         => $golongan->id,
            'unit_kerja_id'       => $unitKerja->id,
            'status_kepegawaian'  => 'Aktif',
            'tmt_sk_pertama'      => '2005-11-01',
            'tmt_pangkat_terakhir'=> '2025-11-17',
            'tmt_kgb_terakhir'    => '2025-04-01',
        ]);

        $tendikPppk = Pegawai::create([
            'nip'                 => '198006152025211060',
            'nama'                => 'Rahmad Hidayat',
            'gelar_belakang'      => 'ST',
            'jenis_kelamin'       => 'L',
            'jenis_pegawai'       => 'PPPK',
            'status_asn'          => 'PPPK',
            'jabatan_id'          => $jabatan->id,
            'unit_kerja_id'       => $unitKerja->id,
            'status_kepegawaian'  => 'Aktif',
            'tmt_sk_pertama'      => '2025-10-01',
        ]);

        // Cek Kuota Cuti
        KuotaCutiPegawai::create([
            'pegawai_id'     => $tendikPns->id,
            'tahun'          => 2026,
            'kuota_awal'     => 12,
            'kuota_terpakai' => 0,
            'sisa_kuota'     => 12,
        ]);

        // Verifikasi Scope Tendik
        $tendiks = Pegawai::tendik()->get();
        $this->assertEquals(2, $tendiks->count());

        // Verifikasi HTTP endpoint
        $response = $this->actingAs($this->adminUser)->get(route('kepegawaian.tendik.index'));
        $response->assertStatus(200);
        $response->assertSee('Bakhtiar');
        $response->assertSee('Rahmad Hidayat');
    }
}
