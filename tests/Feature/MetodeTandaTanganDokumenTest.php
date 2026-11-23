<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class MetodeTandaTanganDokumenTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected Pegawai $pegawai;
    protected UnitKerja $unitKerja;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $this->unitKerja = UnitKerja::create([
            'kode_unit' => 'FKP',
            'nama_unit' => 'Fakultas Keperawatan',
            'jenis_unit' => 'fakultas',
        ]);

        $this->pegawai = Pegawai::create([
            'nama' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_fungsional' => 'Lektor',
            'golongan_darah' => 'O',
        ]);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'pegawai_id' => $this->pegawai->id,
            'must_change_password' => false,
        ]);

        $jabatanWd2 = Jabatan::create([
            'nama_jabatan' => 'Wakil Dekan Bidang Keuangan dan Umum',
            'jenis_jabatan' => 'struktural',
        ]);

        $pejabatWd2 = Pegawai::create([
            'nama' => 'Dr. Safri, M.Kep, Sp.Kep.M.B',
            'nip' => '198509092014041001',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'unit_kerja_id' => $this->unitKerja->id,
            'jabatan_id' => $jabatanWd2->id,
            'jabatan_fungsional' => 'Lektor Kepala',
        ]);

        $this->pimpinanUser = User::factory()->create([
            'role_id' => $pimpinanRole->id,
            'pegawai_id' => $pejabatWd2->id,
            'must_change_password' => false,
        ]);
    }

    public function test_kolom_metode_ttd_memiliki_default_elektronik(): void
    {
        $cuti = PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'tahunan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'jumlah_hari' => 3,
            'alasan' => 'Urusan keluarga',
            'status' => 'draft',
        ]);

        $this->assertEquals('elektronik', $cuti->fresh()->metode_ttd);

        $karir = PengajuanKarir::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_pengajuan' => 'KGB',
            'status' => 'diajukan',
        ]);

        $this->assertEquals('elektronik', $karir->fresh()->metode_ttd);

        $surat = LayananSurat::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_surat' => 'surat_aktif',
            'nomor_surat' => '001/UN19/KP/2026',
            'tanggal_surat' => now()->toDateString(),
            'status' => 'diajukan',
        ]);

        $this->assertEquals('elektronik', $surat->fresh()->metode_ttd);

        $satyalancana = UsulanSatyalancana::create([
            'pegawai_id' => $this->pegawai->id,
            'kategori' => '10 Tahun',
            'tahun_usulan' => 2026,
            'status' => 'diajukan',
        ]);

        $this->assertEquals('elektronik', $satyalancana->fresh()->metode_ttd);
    }

    public function test_approval_cuti_dapat_menyimpan_opsi_manual_dan_elektronik(): void
    {
        $cuti = PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'tahunan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'jumlah_hari' => 3,
            'alasan' => 'Urusan keluarga',
            'status' => 'Diajukan',
            'atasan_langsung_id' => $this->pimpinanUser->pegawai_id,
            'pertimbangan_atasan' => 'Disetujui',
            'tahap_persetujuan' => 'atasan_approved',
        ]);

        // Simpan keputusan dengan metode_ttd manual
        $response = $this->actingAs($this->pimpinanUser)
            ->post(route('pengajuan-cuti.approve', $cuti->id), [
                'status' => 'Disetujui',
                'catatan_pimpinan' => 'Disetujui untuk tanda tangan basah',
                'metode_ttd' => 'manual',
            ]);

        $response->assertRedirect();
        $this->assertEquals('manual', $cuti->fresh()->metode_ttd);
        $this->assertEquals('Disetujui', $cuti->fresh()->status);
    }

    public function test_cetak_pdf_cuti_menghormati_metode_ttd_manual_dan_elektronik(): void
    {
        $cutiManual = PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'tahunan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'jumlah_hari' => 3,
            'alasan' => 'Urusan keluarga',
            'status' => 'Disetujui',
            'metode_ttd' => 'manual',
            'nomor_surat' => '123/UN19.5.1.1.10/KP/2026',
            'atasan_langsung_id' => $this->pimpinanUser->pegawai_id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('pengajuan-cuti.cetak-pdf', $cutiManual->id));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        // Tes dengan query override metode_ttd=elektronik
        $responseElektronik = $this->actingAs($this->adminUser)
            ->get(route('pengajuan-cuti.cetak-pdf', [$cutiManual->id, 'metode_ttd' => 'elektronik']));

        $responseElektronik->assertStatus(200);
    }

    public function test_backward_compatibility_jika_metode_ttd_null(): void
    {
        $cuti = PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'tahunan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'jumlah_hari' => 3,
            'alasan' => 'Urusan keluarga',
            'status' => 'Disetujui',
            'nomor_surat' => '124/UN19.5.1.1.10/KP/2026',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('pengajuan-cuti.cetak-pdf', $cuti->id));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_verifikasi_layanan_surat_menyimpan_metode_ttd(): void
    {
        $surat = LayananSurat::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_surat' => 'surat_aktif',
            'nomor_surat' => '002/UN19/KP/2026',
            'tanggal_surat' => now()->toDateString(),
            'status' => 'diparaf_kabag',
            'verifikasi_admin_at' => now(),
            'paraf_kapokja_at' => now(),
            'paraf_kabag_at' => now(),
            'penandatangan_jabatan' => 'Wakil Dekan Bidang Keuangan dan Umum',
            'penandatangan_nama' => 'Dr. Safri, M.Kep, Sp.Kep.M.B',
            'penandatangan_nip' => '198509092014041001',
        ]);

        $response = $this->actingAs($this->pimpinanUser)
            ->post(route('layanan-surat.surat-aktif.verifikasi', $surat->id), [
                'tahap' => 'ttd_pimpinan',
                'metode_ttd' => 'manual',
            ]);

        $response->assertRedirect();
        $this->assertEquals('manual', $surat->fresh()->metode_ttd);
        $this->assertEquals('selesai', $surat->fresh()->status);
    }
}
