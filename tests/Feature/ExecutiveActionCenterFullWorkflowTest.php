<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\LayananSurat;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PengajuanKarir;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Models\UsulanSatyalancana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ExecutiveActionCenterFullWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pokjaUser;
    protected User $kabagUser;
    protected User $wd2User;
    protected User $dekanUser;

    protected Pegawai $pegawaiPemohon;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $unit = UnitKerja::create(['nama_unit' => 'Fakultas Keperawatan', 'kode_unit' => 'FKP']);
        $golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);

        // Jabatan
        $jabatanAdmin = Jabatan::create(['kode_jabatan' => 'JAB-ADM', 'nama_jabatan' => 'Analis Kepegawaian']);
        $jabatanPokja = Jabatan::create(['kode_jabatan' => 'JAB-POKJA', 'nama_jabatan' => 'Kepala Pokja Keuangan dan Kepegawaian']);
        $jabatanKabag = Jabatan::create(['kode_jabatan' => 'JAB-KBG', 'nama_jabatan' => 'Kepala Bagian Umum']);
        $jabatanWd2 = Jabatan::create(['kode_jabatan' => 'JAB-WD2', 'nama_jabatan' => 'Wakil Dekan Bidang Keuangan dan Umum']);
        $jabatanDekan = Jabatan::create(['kode_jabatan' => 'JAB-DKN', 'nama_jabatan' => 'Dekan Fakultas']);
        $jabatanPemohon = Jabatan::create(['kode_jabatan' => 'JAB-TND', 'nama_jabatan' => 'Pengadministrasi Umum']);

        // Pegawai Users
        $pegAdmin = Pegawai::create(['nip' => '198001012005011001', 'nama' => 'Admin SIKAP', 'status_asn' => 'ASN', 'jenis_pegawai' => 'PNS', 'status_pegawai' => 'Aktif', 'unit_kerja_id' => $unit->id, 'jabatan_id' => $jabatanAdmin->id, 'golongan_id' => $golongan->id, 'pendidikan_terakhir' => 'S1']);
        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id, 'pegawai_id' => $pegAdmin->id, 'name' => 'Admin SIKAP']);

        $pegPokja = Pegawai::create(['nip' => '198001012005011002', 'nama' => 'Ka Pokja SIKAP', 'status_asn' => 'ASN', 'jenis_pegawai' => 'PNS', 'status_pegawai' => 'Aktif', 'unit_kerja_id' => $unit->id, 'jabatan_id' => $jabatanPokja->id, 'golongan_id' => $golongan->id, 'pendidikan_terakhir' => 'S1']);
        $this->pokjaUser = User::factory()->create(['role_id' => $pimpinanRole->id, 'pegawai_id' => $pegPokja->id, 'name' => 'Ka Pokja SIKAP']);

        $pegKabag = Pegawai::create(['nip' => '198001012005011003', 'nama' => 'Kabag SIKAP', 'status_asn' => 'ASN', 'jenis_pegawai' => 'PNS', 'status_pegawai' => 'Aktif', 'unit_kerja_id' => $unit->id, 'jabatan_id' => $jabatanKabag->id, 'golongan_id' => $golongan->id, 'pendidikan_terakhir' => 'S1']);
        $this->kabagUser = User::factory()->create(['role_id' => $pimpinanRole->id, 'pegawai_id' => $pegKabag->id, 'name' => 'Kabag SIKAP']);

        $pegWd2 = Pegawai::create(['nip' => '198001012005011004', 'nama' => 'WD2 SIKAP', 'status_asn' => 'ASN', 'jenis_pegawai' => 'PNS', 'status_pegawai' => 'Aktif', 'unit_kerja_id' => $unit->id, 'jabatan_id' => $jabatanWd2->id, 'golongan_id' => $golongan->id, 'pendidikan_terakhir' => 'S2']);
        $this->wd2User = User::factory()->create(['role_id' => $pimpinanRole->id, 'pegawai_id' => $pegWd2->id, 'name' => 'WD2 SIKAP']);

        $pegDekan = Pegawai::create(['nip' => '198001012005011005', 'nama' => 'Dekan SIKAP', 'status_asn' => 'ASN', 'jenis_pegawai' => 'PNS', 'status_pegawai' => 'Aktif', 'unit_kerja_id' => $unit->id, 'jabatan_id' => $jabatanDekan->id, 'golongan_id' => $golongan->id, 'pendidikan_terakhir' => 'S3']);
        $this->dekanUser = User::factory()->create(['role_id' => $pimpinanRole->id, 'pegawai_id' => $pegDekan->id, 'name' => 'Dekan SIKAP']);

        $this->pegawaiPemohon = Pegawai::create(['nip' => '199501012020011010', 'nama' => 'Pemohon Berkas', 'status_asn' => 'ASN', 'jenis_pegawai' => 'Tendik', 'status_pegawai' => 'Aktif', 'unit_kerja_id' => $unit->id, 'jabatan_id' => $jabatanPemohon->id, 'golongan_id' => $golongan->id, 'pendidikan_terakhir' => 'S1']);
    }

    /**
     * Uji alur 4 tahap Pengajuan Karir (KP & KGB)
     */
    public function test_pengajuan_karir_4_stages_workflow(): void
    {
        $karir = PengajuanKarir::create([
            'pegawai_id'      => $this->pegawaiPemohon->id,
            'jenis_pengajuan' => 'KP',
            'status'          => 'diajukan',
            'metode_ttd'      => 'elektronik',
        ]);

        // Tahap 1: Admin Screening
        $res1 = $this->actingAs($this->adminUser)->get('/pengajuan-karir/' . $karir->id);
        $res1->assertStatus(200);
        $res1->assertSee('1. Admin Screening Berkas');

        $act1 = $this->actingAs($this->adminUser)->post('/pengajuan-karir/' . $karir->id . '/verifikasi', [
            'tahap' => 'verifikasi_admin',
            'catatan' => 'Berkas lengkap',
        ]);
        $act1->assertSessionHas('success');
        $karir->refresh();
        $this->assertNotNull($karir->verifikasi_admin_at);

        // Tahap 2: Paraf Ka Pokja
        $res2 = $this->actingAs($this->pokjaUser)->get('/pengajuan-karir/' . $karir->id);
        $res2->assertStatus(200);
        $res2->assertSee('2. Periksa &amp; Paraf Ka Pokja', false);

        $act2 = $this->actingAs($this->pokjaUser)->post('/pengajuan-karir/' . $karir->id . '/verifikasi', [
            'tahap' => 'paraf_kapokja',
        ]);
        $act2->assertSessionHas('success');
        $karir->refresh();
        $this->assertNotNull($karir->paraf_kapokja_at);

        // Tahap 3: Telaah & Paraf Kabag Umum
        $res3 = $this->actingAs($this->kabagUser)->get('/pengajuan-karir/' . $karir->id);
        $res3->assertStatus(200);
        $res3->assertSee('3. Telaah Naskah &amp; Paraf Kabag', false);

        $act3 = $this->actingAs($this->kabagUser)->post('/pengajuan-karir/' . $karir->id . '/verifikasi', [
            'tahap' => 'paraf_kabag',
        ]);
        $act3->assertSessionHas('success');
        $karir->refresh();
        $this->assertNotNull($karir->paraf_kabag_at);

        // Tahap 4: TTE WD II
        $res4 = $this->actingAs($this->wd2User)->get('/pengajuan-karir/' . $karir->id);
        $res4->assertStatus(200);
        $res4->assertSee('4. Setujui &amp; TTE Wakil Dekan II', false);

        $act4 = $this->actingAs($this->wd2User)->post('/pengajuan-karir/' . $karir->id . '/verifikasi', [
            'tahap' => 'paraf_wd2',
            'metode_ttd' => 'elektronik',
        ]);
        $act4->assertSessionHas('success');
        $karir->refresh();
        $this->assertNotNull($karir->paraf_wd2_at);
    }

    /**
     * Uji alur 4 tahap Usulan Satyalancana
     */
    public function test_usulan_satyalancana_4_stages_workflow(): void
    {
        $satya = UsulanSatyalancana::create([
            'pegawai_id'   => $this->pegawaiPemohon->id,
            'kategori'     => '10 Tahun',
            'tahun_usulan' => 2026,
            'status'       => 'diajukan',
            'metode_ttd'   => 'elektronik',
        ]);

        // Tahap 1: Admin Screening
        $res1 = $this->actingAs($this->adminUser)->get('/satyalancana/' . $satya->id);
        $res1->assertStatus(200);
        $res1->assertSee('1. Admin Screening Berkas');

        $act1 = $this->actingAs($this->adminUser)->post('/satyalancana/' . $satya->id . '/verifikasi', [
            'tahap' => 'verifikasi_admin',
            'catatan' => 'Berkas satya lengkap',
        ]);
        $act1->assertSessionHas('success');
        $satya->refresh();
        $this->assertNotNull($satya->verifikasi_admin_at);

        // Tahap 2: Paraf Ka Pokja
        $res2 = $this->actingAs($this->pokjaUser)->get('/satyalancana/' . $satya->id);
        $res2->assertStatus(200);
        $res2->assertSee('2. Periksa &amp; Paraf Ka Pokja', false);

        $act2 = $this->actingAs($this->pokjaUser)->post('/satyalancana/' . $satya->id . '/verifikasi', [
            'tahap' => 'paraf_kapokja',
        ]);
        $act2->assertSessionHas('success');
        $satya->refresh();
        $this->assertNotNull($satya->paraf_kapokja_at);

        // Tahap 3: Telaah & Paraf Kabag Umum
        $res3 = $this->actingAs($this->kabagUser)->get('/satyalancana/' . $satya->id);
        $res3->assertStatus(200);
        $res3->assertSee('3. Telaah Naskah &amp; Paraf Kabag', false);

        $act3 = $this->actingAs($this->kabagUser)->post('/satyalancana/' . $satya->id . '/verifikasi', [
            'tahap' => 'paraf_kabag',
        ]);
        $act3->assertSessionHas('success');
        $satya->refresh();
        $this->assertNotNull($satya->paraf_kabag_at);

        // Tahap 4: TTE WD II
        $res4 = $this->actingAs($this->wd2User)->get('/satyalancana/' . $satya->id);
        $res4->assertStatus(200);
        $res4->assertSee('4. Setujui &amp; TTE Wakil Dekan II', false);

        $act4 = $this->actingAs($this->wd2User)->post('/satyalancana/' . $satya->id . '/verifikasi', [
            'tahap' => 'ttd_wd2',
            'metode_ttd' => 'elektronik',
        ]);
        $act4->assertSessionHas('success');
        $satya->refresh();
        $this->assertNotNull($satya->ttd_wd2_at);
        $this->assertEquals('disetujui_wd2', $satya->status);
    }

    /**
     * Uji alur 4 tahap Layanan Surat Aktif Bekerja
     */
    public function test_layanan_surat_4_stages_workflow(): void
    {
        $surat = LayananSurat::create([
            'pegawai_id'          => $this->pegawaiPemohon->id,
            'jenis_surat'         => 'surat_aktif',
            'nomor_surat'         => 'B/005/UN19.5.1.1/KP/2026',
            'keperluan'           => 'Keperluan Beasiswa',
            'tanggal_surat'       => now()->toDateString(),
            'status'              => 'diajukan',
            'metode_ttd'          => 'elektronik',
            'verifikasi_admin_at' => null,
            'penandatangan_nama'  => 'Dekan SIKAP',
            'penandatangan_jabatan' => 'Dekan Fakultas Keperawatan',
        ]);

        // Tahap 1: Admin Screening
        $act1 = $this->actingAs($this->adminUser)->post('/layanan-surat/surat-aktif/' . $surat->id . '/verifikasi', [
            'tahap' => 'verifikasi_admin',
        ]);
        $act1->assertSessionHas('success');
        $surat->refresh();
        $this->assertNotNull($surat->verifikasi_admin_at);

        // Tahap 2: Paraf Ka Pokja
        $act2 = $this->actingAs($this->pokjaUser)->post('/layanan-surat/surat-aktif/' . $surat->id . '/verifikasi', [
            'tahap' => 'paraf_kapokja',
        ]);
        $act2->assertSessionHas('success');
        $surat->refresh();
        $this->assertNotNull($surat->paraf_kapokja_at);

        // Tahap 3: Paraf Kabag
        $act3 = $this->actingAs($this->kabagUser)->post('/layanan-surat/surat-aktif/' . $surat->id . '/verifikasi', [
            'tahap' => 'paraf_kabag',
        ]);
        $act3->assertSessionHas('success');
        $surat->refresh();
        $this->assertNotNull($surat->paraf_kabag_at);

        // Tahap 4: TTE Dekan
        $act4 = $this->actingAs($this->dekanUser)->post('/layanan-surat/surat-aktif/' . $surat->id . '/verifikasi', [
            'tahap' => 'ttd_pimpinan',
            'metode_ttd' => 'elektronik',
        ]);
        $act4->assertSessionHas('success');
        $surat->refresh();
        $this->assertNotNull($surat->ttd_pimpinan_at);
        $this->assertEquals('selesai', $surat->status);
    }

    /**
     * Uji alur 4 tahap Pengajuan Cuti
     */
    public function test_pengajuan_cuti_4_stages_workflow(): void
    {
        $cuti = PengajuanCuti::create([
            'pegawai_id'          => $this->pegawaiPemohon->id,
            'jenis_cuti'          => 'Cuti Tahunan',
            'nomor_surat'         => '101/UN19.5.1.1.10/KP/2026',
            'tanggal_mulai'       => now()->addDays(5)->toDateString(),
            'tanggal_selesai'     => now()->addDays(7)->toDateString(),
            'jumlah_hari'         => 3,
            'alasan'              => 'Urusan keluarga',
            'alamat_selama_cuti'  => 'Pekanbaru',
            'nomor_telepon'       => '08123456789',
            'status'              => 'Menunggu Persetujuan',
            'metode_ttd'          => 'elektronik',
            'verifikasi_admin_at' => null,
        ]);

        // Tahap 1: Admin Screening
        $res1 = $this->actingAs($this->adminUser)->get('/pengajuan-cuti/' . $cuti->id);
        $res1->assertStatus(200);
        $res1->assertSee('1. Admin Screening Berkas');

        $act1 = $this->actingAs($this->adminUser)->post('/pengajuan-cuti/' . $cuti->id . '/verifikasi', [
            'tahap' => 'verifikasi_admin',
            'catatan' => 'Screening cuti valid',
        ]);
        $act1->assertSessionHas('success');
        $cuti->refresh();
        $this->assertNotNull($cuti->verifikasi_admin_at);

        // Tahap 2: Paraf Ka Pokja
        $res2 = $this->actingAs($this->pokjaUser)->get('/pengajuan-cuti/' . $cuti->id);
        $res2->assertStatus(200);
        $res2->assertSee('2. Periksa &amp; Paraf Ka Pokja Keu-Kepeg', false);

        $act2 = $this->actingAs($this->pokjaUser)->post('/pengajuan-cuti/' . $cuti->id . '/verifikasi', [
            'tahap' => 'paraf_kapokja',
            'catatan' => 'Sesuai kuota',
        ]);
        $act2->assertSessionHas('success');
        $cuti->refresh();
        $this->assertNotNull($cuti->paraf_kapokja_at);

        // Tahap 3: Pertimbangan Atasan Langsung
        $act3 = $this->actingAs($this->kabagUser)->post('/pengajuan-cuti/' . $cuti->id . '/approve', [
            'status' => 'Disetujui',
            'catatan_atasan_langsung' => 'Disetujui untuk diproses',
        ]);
        $act3->assertSessionHas('success');
        $cuti->refresh();
        $this->assertEquals('Disetujui', $cuti->pertimbangan_atasan);

        // Tahap 4: Keputusan PYBMC / Pimpinan (Wakil Dekan II untuk Tendik)
        $act4 = $this->actingAs($this->wd2User)->post('/pengajuan-cuti/' . $cuti->id . '/approve', [
            'status' => 'Disetujui',
            'catatan_pimpinan' => 'Cuti disetujui resmi',
            'metode_ttd' => 'elektronik',
        ]);
        $act4->assertSessionHas('success');
        $cuti->refresh();
        $this->assertEquals('Disetujui', $cuti->status);
        $this->assertNotNull($cuti->approved_at);
    }
}
