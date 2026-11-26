<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Models\UsulanSatyalancana;
use App\Models\PengajuanCuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SatyalancanaShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_satyalancana_show_page_renders_without_500_error(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

        $unit = UnitKerja::create(['nama_unit' => 'Bagian TU', 'kode_unit' => 'TU']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Analis']);
        $golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);

        $pegawai = Pegawai::create([
            'nip'                 => '198501012010011001',
            'nama'                => 'Eka Test',
            'status_asn'          => 'ASN',
            'jenis_pegawai'       => 'PNS',
            'status_pegawai'      => 'Aktif',
            'unit_kerja_id'       => $unit->id,
            'jabatan_id'          => $jabatan->id,
            'golongan_id'         => $golongan->id,
            'pendidikan_terakhir' => 'S1',
        ]);

        $satya = UsulanSatyalancana::create([
            'pegawai_id'          => $pegawai->id,
            'kategori'            => '20 Tahun',
            'tahun_usulan'        => 2026,
            'periode_usulan'      => 'Periode Mei 2027',
            'nomor_surat_usulan'  => 'USL-SLKS/20261022/0039',
            'status'              => 'diverifikasi_admin',
            'verifikasi_admin_at' => now(),
            'metode_ttd'          => 'elektronik',
        ]);

        $response = $this->actingAs($adminUser)->get('/satyalancana/' . $satya->id);
        $response->assertStatus(200);
        $response->assertSee('Satyalancana Karya Satya (20 Tahun)');
        $response->assertSee('Eka Test');

        // Test helper methods on UsulanSatyalancana
        $this->assertFalse($satya->hasDoc('sk_cpns'));
        $this->assertStringContainsString('pengajuan-karir/1/dokumen/sk_cpns', $satya->getDocPreviewUrl('sk_cpns'));
    }

    public function test_dashboard_does_not_repeat_tahun_in_satyalancana_title(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $adminUser = User::factory()->create(['role_id' => $adminRole->id]);

        $unit = UnitKerja::create(['nama_unit' => 'Bagian TU', 'kode_unit' => 'TU']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Analis']);
        $golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);

        $pegawai = Pegawai::create([
            'nip'                 => '198501012010011001',
            'nama'                => 'Eka Febriyanti',
            'status_asn'          => 'ASN',
            'jenis_pegawai'       => 'PNS',
            'status_pegawai'      => 'Aktif',
            'unit_kerja_id'       => $unit->id,
            'jabatan_id'          => $jabatan->id,
            'golongan_id'         => $golongan->id,
            'pendidikan_terakhir' => 'S1',
        ]);

        UsulanSatyalancana::create([
            'pegawai_id'          => $pegawai->id,
            'kategori'            => '20 Tahun',
            'tahun_usulan'        => 2026,
            'periode_usulan'      => 'Periode Mei 2027',
            'nomor_surat_usulan'  => 'USL-SLKS/20261022/0039',
            'status'              => 'diverifikasi_admin',
            'verifikasi_admin_at' => now(),
            'metode_ttd'          => 'elektronik',
        ]);

        Cache::forget('dashboard_workflow_stats');
        Cache::forget('dashboard_workflow_stage_lists');

        $response = $this->actingAs($adminUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Satyalancana 20 Tahun');
        $response->assertDontSee('Satyalancana 20 Tahun Tahun');
    }
}
