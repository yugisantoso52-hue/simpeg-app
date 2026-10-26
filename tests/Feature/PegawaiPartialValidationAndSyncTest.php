<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PegawaiPartialValidationAndSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected UnitKerja $unitKerja1;
    protected UnitKerja $unitKerja2;
    protected Jabatan $jabatan1;
    protected Jabatan $jabatan2;
    protected Golongan $golongan1;
    protected Golongan $golongan2;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);

        $this->adminUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->unitKerja1 = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $this->unitKerja2 = UnitKerja::create(['nama_unit' => 'Badan Kepegawaian', 'kode_unit' => 'BKD']);

        $this->jabatan1 = Jabatan::create(['kode_jabatan' => 'JAB-01', 'nama_jabatan' => 'Staf Administrasi']);
        $this->jabatan2 = Jabatan::create(['kode_jabatan' => 'JAB-02', 'nama_jabatan' => 'Kepala Seksi']);

        $this->golongan1 = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);
        $this->golongan2 = Golongan::create(['nama_golongan' => 'III/b', 'nama_pangkat' => 'Penata Muda Tk. I']);
    }

    /**
     * Test partial saving: only NIP and Nama are required, all others optional.
     */
    public function test_can_create_pegawai_with_only_nip_and_nama_partially(): void
    {
        $payload = [
            'nip'  => '199501012022011001',
            'nama' => 'Budi Santoso',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);

        $response->assertRedirect(route('pegawai.index'));
        $this->assertDatabaseHas('pegawai', [
            'nip'           => '199501012022011001',
            'nama'          => 'Budi Santoso',
            'unit_kerja_id' => null,
            'jabatan_id'    => null,
            'golongan_id'   => null,
        ]);
    }

    /**
     * Test partial update: can update without supplying optional fields or uploads.
     */
    public function test_can_update_pegawai_partially_without_file_uploads(): void
    {
        $pegawai = Pegawai::create([
            'nip'  => '199501012022011002',
            'nama' => 'Siti Aminah',
        ]);

        $payload = [
            'nip'                => '199501012022011002',
            'nama'               => 'Siti Aminah, S.Kom',
            'karpeg_karis_karsu' => '12345678',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('pegawai.update', $pegawai->id), $payload);

        $response->assertRedirect(route('pegawai.index'));
        $this->assertDatabaseHas('pegawai', [
            'id'                 => $pegawai->id,
            'nama'               => 'Siti Aminah, S.Kom',
            'karpeg_karis_karsu' => '12345678',
        ]);
    }

    /**
     * Test saving riwayat pangkat marks only one active row and syncs to pegawai table.
     */
    public function test_multi_row_pangkat_enforces_single_active_and_syncs_to_pegawai(): void
    {
        $fileSk = UploadedFile::fake()->create('sk_pangkat.pdf', 100, 'application/pdf');

        $payload = [
            'nip'             => '199501012022011003',
            'nama'            => 'Ahmad Dahlan',
            'riwayat_pangkat' => [
                [
                    'golongan_id' => $this->golongan1->id,
                    'tmt'         => '2020-01-01',
                    'nomor_sk'    => 'SK/001/2020',
                    'tanggal_sk'  => '2020-01-02',
                    'status'      => 'aktif',
                ],
                [
                    'golongan_id' => $this->golongan2->id,
                    'tmt'         => '2024-01-01',
                    'nomor_sk'    => 'SK/002/2024',
                    'tanggal_sk'  => '2024-01-02',
                    'status'      => 'aktif',
                    'file_sk'     => $fileSk,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);
        $response->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::where('nip', '199501012022011003')->first();
        $this->assertNotNull($pegawai);

        // Check single active row in riwayat
        $activePangkats = $pegawai->riwayatPangkat()->whereIn('status', ['aktif', 'Aktif'])->get();
        $this->assertCount(1, $activePangkats);
        $this->assertEquals($this->golongan2->id, $activePangkats->first()->golongan_id);

        // Check sync to Pegawai table
        $this->assertEquals($this->golongan2->id, $pegawai->golongan_id);
        $this->assertEquals('2024-01-01', Carbon::parse($pegawai->tmt_pangkat_terakhir)->format('Y-m-d'));
        $this->assertEquals('SK/002/2024', $pegawai->nomor_sk_pangkat_terakhir);
        $this->assertEquals('2024-01-02', Carbon::parse($pegawai->tanggal_sk_pangkat_terakhir)->format('Y-m-d'));
        $this->assertEquals('2028-01-01', Carbon::parse($pegawai->kp_berikutnya)->format('Y-m-d'));
    }

    /**
     * Test saving riwayat jabatan marks only one active row and syncs to pegawai table.
     */
    public function test_multi_row_jabatan_enforces_single_active_and_syncs_to_pegawai(): void
    {
        $payload = [
            'nip'             => '199501012022011004',
            'nama'            => 'Dewi Lestari',
            'riwayat_jabatan' => [
                [
                    'jabatan_id'    => $this->jabatan1->id,
                    'unit_kerja_id' => $this->unitKerja1->id,
                    'tmt_jabatan'   => '2021-01-01',
                    'nomor_sk'      => 'SK/JAB/001',
                    'tanggal_sk'    => '2021-01-02',
                    'status'        => 'aktif',
                ],
                [
                    'jabatan_id'    => $this->jabatan2->id,
                    'unit_kerja_id' => $this->unitKerja2->id,
                    'tmt_jabatan'   => '2025-01-01',
                    'nomor_sk'      => 'SK/JAB/002',
                    'tanggal_sk'    => '2025-01-02',
                    'status'        => 'aktif',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);
        $response->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::where('nip', '199501012022011004')->first();
        $this->assertNotNull($pegawai);

        // Check single active row
        $activeJabatans = $pegawai->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->get();
        $this->assertCount(1, $activeJabatans);
        $this->assertEquals($this->jabatan2->id, $activeJabatans->first()->jabatan_id);

        // Check sync to Pegawai table
        $this->assertEquals($this->jabatan2->id, $pegawai->jabatan_id);
        $this->assertEquals($this->unitKerja2->id, $pegawai->unit_kerja_id);
        $this->assertEquals('SK/JAB/002', $pegawai->nomor_sk_pertama);
        $this->assertEquals('2025-01-02', Carbon::parse($pegawai->tanggal_sk_pertama)->format('Y-m-d'));
    }

    /**
     * Test two-way sync: updating top-form jabatan/golongan creates/updates active riwayat.
     */
    public function test_top_form_input_syncs_to_active_riwayat(): void
    {
        $pegawai = Pegawai::create([
            'nip'  => '199501012022011005',
            'nama' => 'Rahmat Hidayat',
        ]);

        $payload = [
            'nip'                         => '199501012022011005',
            'nama'                        => 'Rahmat Hidayat',
            'unit_kerja_id'               => $this->unitKerja1->id,
            'jabatan_id'                  => $this->jabatan1->id,
            'golongan_id'                 => $this->golongan1->id,
            'nomor_sk_pertama'            => 'SK-UTAMA-01',
            'tanggal_sk_pertama'          => '2023-05-10',
            'nomor_sk_pangkat_terakhir'   => 'SK-PANGKAT-01',
            'tanggal_sk_pangkat_terakhir' => '2023-05-10',
            'tmt_pangkat_terakhir'        => '2023-05-10',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('pegawai.update', $pegawai->id), $payload);
        $response->assertRedirect(route('pegawai.index'));

        $pegawai->refresh();

        // Check active riwayat jabatan created
        $activeJabatan = $pegawai->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->first();
        $this->assertNotNull($activeJabatan);
        $this->assertEquals($this->jabatan1->id, $activeJabatan->jabatan_id);
        $this->assertEquals($this->unitKerja1->id, $activeJabatan->unit_kerja_id);

        // Check active riwayat pangkat created
        $activePangkat = $pegawai->riwayatPangkat()->whereIn('status', ['aktif', 'Aktif'])->first();
        $this->assertNotNull($activePangkat);
        $this->assertEquals($this->golongan1->id, $activePangkat->golongan_id);
        $this->assertEquals('2023-05-10', Carbon::parse($activePangkat->tmt)->format('Y-m-d'));
    }

    /**
     * Test dashboard and DUK consistency.
     */
    public function test_dashboard_and_duk_consistency_with_partial_and_synced_pegawai(): void
    {
        $pegawai = Pegawai::create([
            'nip'                  => '199501012022011006',
            'nama'                 => 'Rina Marlina',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golongan1->id,
            'jabatan_id'           => $this->jabatan1->id,
            'unit_kerja_id'        => $this->unitKerja1->id,
            'tmt_pangkat_terakhir' => now()->subYears(4)->format('Y-m-d'),
            'kp_berikutnya'        => now()->addDays(10)->format('Y-m-d'),
        ]);

        // Access Dashboard
        $dashboardResponse = $this->actingAs($this->adminUser)->get('/dashboard');
        $dashboardResponse->assertStatus(200);

        // Access DUK
        $dukResponse = $this->actingAs($this->adminUser)->get('/duk');
        $dukResponse->assertStatus(200);
        $dukResponse->assertSee('Rina Marlina');
        $dukResponse->assertSee('199501012022011006');
    }

    /**
     * Test full parity between Form Tambah Pegawai Baru (Sections 1-15) and Detail Lengkap Profil Pegawai.
     */
    public function test_create_and_show_profile_full_data_parity(): void
    {
        $createPage = $this->actingAs($this->adminUser)->get(route('pegawai.create'));
        $createPage->assertStatus(200);
        $createPage->assertSee('Nomor SK Pangkat Terakhir');
        $createPage->assertSee('Tanggal SK Pangkat Terakhir');
        $createPage->assertSee('Riwayat Keanggotaan Organisasi');
        $createPage->assertSee('Riwayat Publikasi Ilmiah & Karya', false);
        $createPage->assertSee('Tugas Belajar & Izin Belajar', false);

        $payload = [
            'nip'                         => '198807152019032010',
            'nama'                        => 'Siti Aisyah',
            'gelar_depan'                 => 'Ns.',
            'gelar_belakang'              => 'S.Kep., M.Kep., Sp.Kep.MB',
            'nik'                         => '1471085507880001',
            'npwp'                        => '09.123.456.7-211.000',
            'bpjs_kesehatan'              => '0001234567890',
            'bpjs_ketenagakerjaan'        => '19023456789',
            'jenis_pegawai'               => 'Dosen',
            'status_asn'                  => 'ASN',
            'unit_kerja_id'               => $this->unitKerja1->id,
            'jabatan_id'                  => $this->jabatan1->id,
            'golongan_id'                 => $this->golongan1->id,
            'angka_kredit'                => 300.50,
            'nomor_pak'                   => 'PAK/FKP/2025/099',
            'tanggal_pak'                 => '2025-06-15',
            'tmt_sk_pertama'              => '2019-03-01',
            'nomor_sk_pertama'            => 'SK-CPNS-2019-01',
            'tanggal_sk_pertama'          => '2019-02-20',
            'tmt_pangkat_terakhir'        => '2024-04-01',
            'nomor_sk_pangkat_terakhir'   => 'SK-KP-2024-88',
            'tanggal_sk_pangkat_terakhir' => '2024-03-25',
            'riwayat_pendidikan' => [
                [
                    'jenjang'      => 'Profesi',
                    'institusi'    => 'Universitas Riau',
                    'fakultas'     => 'Fakultas Keperawatan',
                    'jurusan'      => 'Profesi Ners',
                    'tahun_lulus'  => 2015,
                ],
            ],
            'riwayat_diklat' => [
                [
                    'nama_diklat'   => 'Pelatihan Pekerti & AA',
                    'jenis_diklat'  => 'Diklat Fungsional',
                    'penyelenggara' => 'LPMPP UNRI',
                    'tahun'         => 2024,
                    'jumlah_jam'    => 64,
                    'status'        => 'Lulus',
                ],
            ],
            'riwayat_skp' => [
                [
                    'tahun'            => now()->year - 1,
                    'predikat_kinerja' => 'Sangat Baik',
                    'pejabat_penilai'  => 'Prof. Dr. Dekan FKP',
                ],
            ],
            'riwayat_penghargaan' => [
                [
                    'nama_penghargaan' => 'Dosen Berprestasi Tingkat Universitas',
                    'jenis_penghargaan'=> 'Piagam Penghargaan',
                    'instansi_pemberi' => 'Rektor Universitas Riau',
                    'nomor_sk'         => 'SK/REKTOR/2025/11',
                    'tanggal_terima'   => '2025-08-17',
                ],
            ],
            'riwayat_organisasi' => [
                [
                    'nama_organisasi'    => 'PPNI Provinsi Riau',
                    'jabatan_organisasi' => 'Pengurus Bidang Pendidikan',
                    'tahun_mulai'        => 2022,
                    'masih_aktif'        => 1,
                ],
            ],
            'riwayat_publikasi' => [
                [
                    'judul_publikasi' => 'Efektivitas Asuhan Keperawatan Medikal Bedah Modern',
                    'jenis_publikasi' => 'Jurnal Internasional Bereputasi',
                    'indeksasi'       => 'Scopus Q2',
                    'nama_jurnal'     => 'Journal of Nursing Care',
                    'tahun_terbit'    => 2025,
                    'url_doi'         => 'https://doi.org/10.1000/jnc.2025.01',
                ],
            ],
            'tugas_belajar' => [
                [
                    'jenis_pengembangan' => 'Tugas Belajar',
                    'jenjang_studi'      => 'S3 (Doktoral)',
                    'perguruan_tinggi'   => 'Universitas Indonesia',
                    'program_studi'      => 'Ilmu Keperawatan',
                    'negara'             => 'Indonesia',
                    'sumber_pembiayaan'  => 'Beasiswa LPDP / BPI',
                    'nomor_sk'           => 'SK-TUBEL-2025-07',
                    'tanggal_mulai'      => '2025-09-01',
                    'semester_berjalan'  => 2,
                    'status_studi'       => 'Aktif Studi',
                ],
            ],
        ];

        $storeResp = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);
        $storeResp->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::where('nip', '198807152019032010')->first();
        $this->assertNotNull($pegawai);

        // Verify saved fields & histories
        $this->assertEquals('SK-KP-2024-88', $pegawai->nomor_sk_pangkat_terakhir);
        $this->assertEquals(64, $pegawai->riwayatDiklat()->first()->jumlah_jam);
        $this->assertCount(1, $pegawai->riwayatPenghargaan);
        $this->assertCount(1, $pegawai->riwayatOrganisasi);
        $this->assertCount(1, $pegawai->riwayatPublikasi);
        $this->assertCount(1, $pegawai->tugasBelajar);

        // Verify Detail Lengkap Profil Pegawai (show) displays all synchronized fields
        $showResp = $this->actingAs($this->adminUser)->get(route('pegawai.show', $pegawai->id));
        $showResp->assertStatus(200);
        $showResp->assertSee('Ns. Siti Aisyah, S.Kep., M.Kep., Sp.Kep.MB');
        $showResp->assertSee('SK-KP-2024-88');
        $showResp->assertSee('PAK/FKP/2025/099');
        $showResp->assertSee('15/06/2025');
        $showResp->assertSee('Fakultas Keperawatan');
        $showResp->assertSee('64 JP');
        $showResp->assertSee('Diklat Fungsional');
        $showResp->assertSee('Prof. Dr. Dekan FKP');
        $showResp->assertSee('Dosen Berprestasi Tingkat Universitas');
        $showResp->assertSee('SK/REKTOR/2025/11');
        $showResp->assertSee('PPNI Provinsi Riau');
        $showResp->assertSee('Efektivitas Asuhan Keperawatan Medikal Bedah Modern');
        $showResp->assertSee('SK-TUBEL-2025-07');

        // Verify Edit page renders all sections without error
        $editResp = $this->actingAs($this->adminUser)->get(route('pegawai.edit', $pegawai->id));
        $editResp->assertStatus(200);
        $editResp->assertSee('PPNI Provinsi Riau');
        $editResp->assertSee('Efektivitas Asuhan Keperawatan Medikal Bedah Modern');
        $editResp->assertSee('SK-TUBEL-2025-07');
    }
}
