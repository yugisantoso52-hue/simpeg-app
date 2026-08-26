<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\RiwayatDiklat;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatPendidikan;
use App\Models\RiwayatSkp;
use App\Models\RiwayatStrSip;
use App\Models\Role;
use App\Models\TugasBelajar;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected User $pegawaiUser;
    protected Pegawai $pegawai;
    protected UnitKerja $unitKerja;
    protected Jabatan $jabatan;
    protected Golongan $golongan;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->unitKerja = UnitKerja::create(['kode_unit' => 'KMB', 'nama_unit' => 'Bagian Keperawatan Medikal Bedah']);
        $this->jabatan = Jabatan::create(['kode_jabatan' => 'LK-01', 'nama_jabatan' => 'Lektor Kepala']);
        $this->golongan = Golongan::create(['nama_golongan' => 'IV/a', 'nama_pangkat' => 'Pembina']);

        $this->pegawai = Pegawai::create([
            'nip'                 => '198001012005011001',
            'nama'                => 'Dr. Ns. Siti Aminah, M.Kep, Sp.Kep.MB',
            'karpeg_karis_karsu'  => 'K.123456',
            'nidn_nuptk'          => '0001018001',
            'mkg_tahun'           => 15,
            'mkg_bulan'           => 6,
            'pendidikan_terakhir' => 'S3',
            'jenis_pegawai'       => 'Dosen',
            'status_pegawai'      => 'Aktif',
            'status_asn'          => 'ASN',
            'unit_kerja_id'       => $this->unitKerja->id,
            'jabatan_id'          => $this->jabatan->id,
            'golongan_id'         => $this->golongan->id,
        ]);

        $this->adminUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->pimpinanUser = User::factory()->create([
            'role_id'              => $pimpinanRole->id,
            'must_change_password' => false,
        ]);

        $this->pegawaiUser = User::factory()->create([
            'role_id'              => $pegawaiRole->id,
            'pegawai_id'           => $this->pegawai->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Audit 1: Model Relationships on Pegawai
     */
    public function test_audit_pegawai_relationships_and_accessors(): void
    {
        // 1. Pendidikan
        RiwayatPendidikan::create([
            'pegawai_id'    => $this->pegawai->id,
            'jenjang'       => 'S3',
            'institusi'     => 'Universitas Indonesia',
            'jurusan'       => 'Ilmu Keperawatan',
            'tahun_lulus'   => 2020,
        ]);

        // 2. Jabatan
        RiwayatJabatan::create([
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatan->id,
            'unit_kerja_id' => $this->unitKerja->id,
            'tmt_jabatan'   => '2021-01-01',
            'nomor_sk'      => 'SK-JAB-01',
        ]);

        // 3. Pangkat
        RiwayatPangkat::create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golongan->id,
            'tmt_pangkat' => '2022-04-01',
            'nomor_sk'    => 'SK-PKT-01',
        ]);

        // 4. Diklat
        RiwayatDiklat::create([
            'pegawai_id'     => $this->pegawai->id,
            'nama_diklat'    => 'Pelatihan Preseptorship Keperawatan',
            'penyelenggara'  => 'AIPNI',
            'tahun'          => 2024,
            'jumlah_jam'     => 32,
            'nomor_sertifikat' => 'SRT-01',
        ]);

        // 5. STR & SIP
        RiwayatStrSip::create([
            'pegawai_id'       => $this->pegawai->id,
            'jenis_dokumen'    => 'STR',
            'nama_dokumen'     => 'Surat Tanda Registrasi Ners',
            'nomor_registrasi' => 'STR-12345678',
            'tanggal_terbit'   => '2024-01-15',
            'is_seumur_hidup'  => true,
            'instansi_penerbit' => 'KTKI / Kemenkes RI',
        ]);

        // 6. Tugas Belajar
        TugasBelajar::create([
            'pegawai_id'         => $this->pegawai->id,
            'jenis_pengembangan' => 'Tugas Belajar',
            'jenjang_studi'      => 'S3',
            'program_studi'      => 'Doktor Keperawatan',
            'perguruan_tinggi'   => 'Universitas Indonesia',
            'negara'             => 'Indonesia',
            'sumber_pembiayaan'  => 'Beasiswa LPDP',
            'nomor_sk'           => 'SK-TB-01',
            'tanggal_mulai'      => '2016-09-01',
            'tanggal_selesai'    => '2020-08-31',
            'status_studi'       => 'Lulus',
        ]);

        // 7. Pengajuan Cuti
        PengajuanCuti::create([
            'pegawai_id'      => $this->pegawai->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2026-07-03',
            'jumlah_hari'     => 3,
            'alasan'          => 'Cuti keluarga',
            'status'          => 'Disetujui',
        ]);

        // 8. Riwayat SKP
        RiwayatSkp::create([
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => 2025,
            'predikat_kinerja' => 'Sangat Baik',
            'pejabat_penilai'  => 'Dekan Fak. Keperawatan',
        ]);

        $this->pegawai->refresh();

        // Assert all relationships
        $this->assertEquals('KMB', $this->pegawai->unitKerja->kode_unit);
        $this->assertEquals('Lektor Kepala', $this->pegawai->jabatan->nama_jabatan);
        $this->assertEquals('IV/a', $this->pegawai->golongan->nama_golongan);
        $this->assertCount(1, $this->pegawai->riwayatPendidikan);
        $this->assertCount(1, $this->pegawai->riwayatJabatan);
        $this->assertCount(1, $this->pegawai->riwayatPangkat);
        $this->assertCount(1, $this->pegawai->riwayatDiklat);
        $this->assertCount(1, $this->pegawai->riwayatStrSip);
        $this->assertCount(1, $this->pegawai->tugasBelajar);
        $this->assertCount(1, $this->pegawai->pengajuanCuti);
        $this->assertCount(1, $this->pegawai->riwayatSkp);
        $this->assertEquals(9, $this->pegawai->sisa_cuti_tahunan); // 12 - 3
        $this->assertNotNull($this->pegawai->getSkpTahun(2025));
        $this->assertNull($this->pegawai->getSkpTahun(2020));
    }

    /**
     * Audit 2: All Admin & Pimpinan Dashboard & Monitoring Routes
     */
    public function test_audit_all_get_monitoring_routes(): void
    {
        $routes = [
            'dashboard',
            'pegawai.index',
            'duk.index',
            'tugas-belajar.index',
            'pengajuan-cuti.index',
            'riwayat-pendidikan.index',
            'riwayat-jabatan.index',
            'riwayat-pangkat.index',
            'riwayat-diklat.index',
            'riwayat-str-sip.index',
            'riwayat-skp.index',
            'unit-kerja.index',
            'jabatan.index',
            'golongan.index',
            'mutasi-pegawai.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->adminUser)->get(route($route));
            $this->assertEquals(200, $response->getStatusCode(), "Route {$route} failed with status {$response->getStatusCode()}");
        }
    }

    /**
     * Audit 3: All Export & PDF Reports
     */
    public function test_audit_all_export_routes(): void
    {
        // Detail PDF Pegawai
        $resPdfProfil = $this->actingAs($this->adminUser)->get(route('pegawai.download-pdf', $this->pegawai->id));
        $resPdfProfil->assertStatus(200);

        // DUK PDF & Excel
        $resDukPdf = $this->actingAs($this->adminUser)->get(route('reports.duk.pdf'));
        $resDukPdf->assertStatus(200);
        $resDukExcel = $this->actingAs($this->adminUser)->get(route('reports.duk.excel'));
        $resDukExcel->assertStatus(200);

        // Reminder PDF & Excel
        $resRemPdf = $this->actingAs($this->adminUser)->get(route('reports.reminder.pdf'));
        $resRemPdf->assertStatus(200);
        $resRemExcel = $this->actingAs($this->adminUser)->get(route('reports.reminder.excel'));
        $resRemExcel->assertStatus(200);
    }
}
