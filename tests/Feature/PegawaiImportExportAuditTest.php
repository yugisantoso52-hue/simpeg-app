<?php

namespace Tests\Feature;

use App\Exports\DukExport;
use App\Exports\PegawaiTemplateExport;
use App\Imports\PegawaiImport;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class PegawaiImportExportAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $this->adminUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);
    }

    public function test_template_export_headings_and_structure_match_new_schema(): void
    {
        $export = new PegawaiTemplateExport();
        $headings = $export->headings();

        // 1. Pastikan kolom-kolom baru ada
        $this->assertContains('KARPEG / KARIS / KARSU', $headings);
        $this->assertContains('NIDN / NUPTK', $headings);
        $this->assertContains('MKG Tahun', $headings);
        $this->assertContains('MKG Bulan', $headings);
        $this->assertContains('Pendidikan Terakhir (SD/SMP/SMA/D1/D2/D3/D4/S1/S2/S3/Profesi)', $headings);
        $this->assertContains('Jenis Pegawai (Dosen/PNS/PPPK/PHL)', $headings);

        // 2. Pastikan kolom lama yang di-drop TIDAK ada
        $this->assertNotContains('NIK', $headings);
        $this->assertNotContains('NPWP', $headings);
        $this->assertNotContains('BPJS', $headings);
        $this->assertNotContains('Golongan Darah', $headings);
        $this->assertNotContains('Tinggi Badan', $headings);
        $this->assertNotContains('Berat Badan', $headings);
    }

    public function test_pegawai_import_maps_all_new_columns_and_calculates_dates(): void
    {
        $unit = UnitKerja::create(['nama_unit' => 'Keperawatan Medikal', 'kode_unit' => 'KM']);
        $jabatan = Jabatan::firstOrCreate(['kode_jabatan' => 'DOC-LK'], ['nama_jabatan' => 'Lektor']);
        $golongan = Golongan::create(['nama_golongan' => 'III/c', 'nama_pangkat' => 'Penata']);

        $import = new PegawaiImport();

        // Row simulating Maatwebsite Excel parsed header row from PegawaiTemplateExport
        $row = [
            'nip'                                                           => '198501012010011099',
            'karpeg_karis_karsu'                                            => 'KPG-12345',
            'nidn_nuptk'                                                    => '0012345678',
            'nama_lengkap'                                                  => 'Dr. Ns. Siti Dosen, M.Kep',
            'gelar_depan'                                                   => 'Dr.',
            'gelar_belakang'                                                => 'M.Kep',
            'tempat_lahir'                                                  => 'Pekanbaru',
            'tanggal_lahir_yyyy_mm_dd'                                      => '1985-01-01',
            'jenis_kelamin_l_p'                                             => 'P',
            'agama'                                                         => 'Islam',
            'pendidikan_terakhir_sd_smp_sma_d1_d2_d3_d4_s1_s2_s3_profesi'   => 'S3',
            'jenis_pegawai_dosen_pns_pppk_phl'                              => 'Dosen',
            'status_asn_asn_non_asn'                                        => 'ASN',
            'id_unit_kerja'                                                 => (string)$unit->id,
            'id_jabatan'                                                    => (string)$jabatan->id,
            'id_golongan'                                                   => (string)$golongan->id,
            'mkg_tahun'                                                     => '12',
            'mkg_bulan'                                                     => '6',
            'tanggal_masuk_yyyy_mm_dd'                                      => '2010-01-01',
            'tmt_sk_pertama_yyyy_mm_dd'                                     => '2010-01-01',
            'tmt_pangkat_terakhir_yyyy_mm_dd'                               => '2022-04-01',
            'tmt_kgb_terakhir_yyyy_mm_dd'                                   => '2024-04-01',
            'status_pegawai_aktif_tugas_belajar_non_aktif_pensiun'          => 'Aktif',
        ];

        $pegawai = $import->model($row);
        $pegawai->save();

        $this->assertDatabaseHas('pegawai', [
            'nip'                 => '198501012010011099',
            'karpeg_karis_karsu'  => 'KPG-12345',
            'nidn_nuptk'          => '0012345678',
            'nama'                => 'Dr. Ns. Siti Dosen, M.Kep',
            'jenis_pegawai'       => 'Dosen',
            'pendidikan_terakhir' => 'S3',
            'mkg_tahun'           => 12,
            'mkg_bulan'           => 6,
            'status_pegawai'      => 'Aktif',
        ]);

        $saved = Pegawai::where('nip', '198501012010011099')->first();
        $this->assertEquals('2026-04-01', $saved->kgb_berikutnya->format('Y-m-d'));
        $this->assertEquals('2026-04-01', $saved->kp_berikutnya->format('Y-m-d'));
    }

    public function test_duk_export_and_rekapitulasi_includes_dosen_and_phl(): void
    {
        $unit = UnitKerja::create(['nama_unit' => 'Keperawatan Jiwa', 'kode_unit' => 'KJ']);
        $jabatan = Jabatan::firstOrCreate(['kode_jabatan' => 'DOC-AA'], ['nama_jabatan' => 'Asisten Ahli']);
        $golongan = Golongan::create(['nama_golongan' => 'III/b', 'nama_pangkat' => 'Penata Muda Tk. I']);

        Pegawai::create([
            'nip'                 => '199201012018011001',
            'nama'                => 'Dosen Baru',
            'jenis_pegawai'       => 'Dosen',
            'pendidikan_terakhir' => 'S2',
            'unit_kerja_id'       => $unit->id,
            'jabatan_id'          => $jabatan->id,
            'golongan_id'         => $golongan->id,
            'status_pegawai'      => 'Aktif',
        ]);

        Pegawai::create([
            'nip'                 => '199501012023011002',
            'nama'                => 'Staf PHL',
            'jenis_pegawai'       => 'PHL',
            'pendidikan_terakhir' => 'D3',
            'status_pegawai'      => 'Aktif',
        ]);

        $export = new DukExport();
        $collection = $export->collection();

        $this->assertTrue($collection->contains(function ($row) {
            return is_array($row) && in_array('199201012018011001', str_replace("'", "", $row));
        }));

        // Pastikan rekapitulasi mencakup Dosen dan PHL
        $hasDosenRekap = $collection->contains(function ($row) {
            return is_array($row) && isset($row[1]) && str_contains($row[1], 'Dosen');
        });
        $hasPhlRekap = $collection->contains(function ($row) {
            return is_array($row) && isset($row[1]) && str_contains($row[1], 'PHL');
        });

        $this->assertTrue($hasDosenRekap, 'Rekapitulasi DUK harus memiliki baris Dosen');
        $this->assertTrue($hasPhlRekap, 'Rekapitulasi DUK harus memiliki baris PHL');
    }

    public function test_pdf_profil_and_duk_render_without_errors(): void
    {
        $unit = UnitKerja::create(['nama_unit' => 'Dekanat', 'kode_unit' => 'DKN']);
        $jabatan = Jabatan::firstOrCreate(['kode_jabatan' => 'DOC-GB'], ['nama_jabatan' => 'Guru Besar']);
        $golongan = Golongan::create(['nama_golongan' => 'IV/d', 'nama_pangkat' => 'Pembina Utama Madya']);

        $pegawai = Pegawai::create([
            'nip'                 => '197501012000011001',
            'nama'                => 'Prof. Dr. Hendra',
            'karpeg_karis_karsu'  => 'A.998877',
            'nidn_nuptk'          => '0075010101',
            'jenis_pegawai'       => 'Dosen',
            'pendidikan_terakhir' => 'S3',
            'mkg_tahun'           => 20,
            'mkg_bulan'           => 4,
            'status_pegawai'      => 'Aktif',
            'unit_kerja_id'       => $unit->id,
            'jabatan_id'          => $jabatan->id,
            'golongan_id'         => $golongan->id,
        ]);

        $htmlProfil = view('exports.pdf.profil_pegawai', compact('pegawai'))->render();
        $this->assertStringContainsString('Prof. Dr. Hendra', $htmlProfil);
        $this->assertStringContainsString('A.998877', $htmlProfil);
        $this->assertStringContainsString('0075010101', $htmlProfil);
        $this->assertStringNotContainsString('DATA FISIK', $htmlProfil);
        $this->assertStringNotContainsString('NIK', $htmlProfil);

        $pegawais = collect([$pegawai]);
        $htmlDuk = view('exports.pdf.duk', compact('pegawais'))->render();
        $this->assertStringContainsString('Prof. Dr. Hendra', $htmlDuk);
        $this->assertStringContainsString('Dosen', $htmlDuk);
        $this->assertStringContainsString('PHL', $htmlDuk);
    }
}
