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
        $editResp->assertDontSee('\${newIndex}', false);
    }

    /**
     * Test all 14 modules synchronization, Penugasan Ketua/Sekretaris roles,
     * Tugas Belajar status_pegawai sync, and Satyalancana eligibility normalization.
     */
    public function test_comprehensive_14_modules_synchronization_and_safe_patching(): void
    {
        $kelompokSenat = \App\Models\KelompokPenugasan::firstOrCreate(
            ['kode' => 'senat_ketua_test'],
            ['nama' => 'Ketua Senat', 'kategori' => 'senat', 'urutan' => 1]
        );
        $kelompokSekretaris = \App\Models\KelompokPenugasan::firstOrCreate(
            ['kode' => 'senat_sekretaris_test'],
            ['nama' => 'Sekretaris Senat', 'kategori' => 'senat', 'urutan' => 2]
        );

        // 1. Pastikan template JS pada form create tidak memiliki escaped \${newIndex}
        $createResp = $this->actingAs($this->adminUser)->get(route('pegawai.create'));
        $createResp->assertStatus(200);
        $createResp->assertDontSee('\${newIndex}', false);
        $createResp->assertDontSee('\${jabatanOptions}', false);
        $createResp->assertDontSee('\${unitOptions}', false);
        $createResp->assertSee('Penugasan &amp; Keanggotaan Unit', false);

        // 2. Uji Store Pegawai dengan 14 Modul + Penugasan (Peran Ketua & Sekretaris) + Tugas Belajar Aktif + Piagam Satyalancana
        $payload = [
            // Modul 1: Data Pribadi
            'nip'                  => '198505102010011099',
            'nik'                  => '1471011005850099',
            'nidn_nuptk'           => '0010058509',
            'nama'                 => 'Dr. Hendra Kurniawan',
            'gelar_depan'          => 'Dr. Ns.',
            'gelar_belakang'       => 'M.Kep., Sp.Kep.K',
            'tempat_lahir'         => 'Pekanbaru',
            'tanggal_lahir'        => '1985-05-10',
            'jenis_kelamin'        => 'Laki-laki', // Menguji normalisasi prepareForValidation pada Store
            'agama'                => 'Islam',
            'karpeg_karis_karsu'   => 'KPG-998877',
            'npwp'                 => '12.345.678.9-211.000',
            'bpjs_kesehatan'       => '0009988776655',
            'bpjs_ketenagakerjaan' => '20099887766',

            // Modul 2: Kontak & Domisili
            'email'                   => 'hendra.kurniawan@unri.ac.id',
            'no_hp'                   => '081234567890',
            'nama_kontak_darurat'     => 'Ratna Sari',
            'hubungan_kontak_darurat' => 'Istri',
            'no_hp_darurat'           => '081298765432',
            'alamat'                  => 'Jl. Pattimura No. 9, Pekanbaru',
            'alamat_domisili'         => 'Jl. HR Soebrantas Panam, Pekanbaru',
            'kota_domisili'           => 'Pekanbaru',
            'provinsi'                => 'Riau',
            'kode_pos'                => '28293',

            // Modul 3: Data Keluarga
            'status_pernikahan' => 'Menikah',
            'nama_pasangan'     => 'Ratna Sari',
            'jumlah_anak'       => 2,

            // Modul 4: Data Kepegawaian & Jabatan + Penugasan
            'unit_kerja_id'       => $this->unitKerja1->id,
            'jabatan_id'          => $this->jabatan1->id,
            'golongan_id'         => $this->golongan1->id,
            'jenis_jabatan'       => 'Fungsional Tertentu',
            'jenis_pegawai'       => 'Dosen',
            'status_asn'          => 'ASN',
            'pendidikan_terakhir' => 'S2',
            'mkg_tahun'           => 16,
            'mkg_bulan'           => 4,
            'penugasan_submitted' => 1,
            'penugasan' => [
                $kelompokSenat->id => [
                    'kelompok_penugasan_id' => $kelompokSenat->id,
                    'peran'                 => 'Ketua',
                ],
                $kelompokSekretaris->id => [
                    'kelompok_penugasan_id' => $kelompokSekretaris->id,
                    'peran'                 => 'Sekretaris',
                ],
            ],

            // Modul 5: Legalitas Profesi Kesehatan (STR & SIP)
            'nomor_str'              => 'STR-NERS-2026-001',
            'tanggal_kadaluarsa_str' => '2030-05-10',
            'nomor_sip'              => 'SIP-NERS-2026-002',
            'tanggal_kadaluarsa_sip' => '2030-05-10',

            // Modul 6: Penilaian Angka Kredit (PAK)
            'angka_kredit' => 450.75,
            'nomor_pak'    => 'SK-PAK/UNRI/2026/01',
            'tanggal_pak'  => '2026-01-15',

            // Modul 7: Administrasi Kepegawaian & Legalitas
            'tanggal_masuk'               => '2010-01-01',
            'status_pegawai'              => 'Aktif',
            'tmt_sk_pertama'              => '2010-01-01',
            'nomor_sk_pertama'            => 'SK-CPNS/2010/01',
            'tanggal_sk_pertama'          => '2009-12-20',
            'tmt_pangkat_terakhir'        => '2022-04-01',
            'nomor_sk_pangkat_terakhir'   => 'SK-KP/2022/04',
            'tanggal_sk_pangkat_terakhir' => '2022-03-15',
            'tmt_kgb_terakhir'            => '2025-01-01',
            'batas_usia_pensiun'          => 65,

            // Modul 8: Riwayat Pangkat/Golongan
            'riwayat_pangkat' => [
                [
                    'golongan_id' => $this->golongan1->id,
                    'tmt'         => '2018-04-01',
                    'nomor_sk'    => 'SK-KP/2018/01',
                    'tanggal_sk'  => '2018-03-20',
                    'status'      => 'riwayat',
                ],
                [
                    'golongan_id' => $this->golongan2->id,
                    'tmt'         => '2022-04-01',
                    'nomor_sk'    => 'SK-KP/2022/04',
                    'tanggal_sk'  => '2022-03-15',
                    'status'      => 'aktif',
                ],
            ],

            // Modul 9: Riwayat Pendidikan & Diklat
            'riwayat_pendidikan' => [
                [
                    'jenjang'     => 'S3',
                    'institusi'   => 'Universitas Indonesia',
                    'fakultas'    => 'Fakultas Ilmu Keperawatan',
                    'jurusan'     => 'Ilmu Keperawatan',
                    'tahun_lulus' => 2024,
                ],
            ],
            'riwayat_diklat' => [
                [
                    'nama_diklat'      => 'Pelatihan Kepemimpinan Akademik',
                    'penyelenggara'    => 'Kemendikbudristek',
                    'jenis_diklat'     => 'Struktural / Kepemimpinan',
                    'nomor_sertifikat' => 'SERT-PKA-2026-09',
                    'tanggal_mulai'    => '2026-02-01',
                    'tanggal_selesai'  => '2026-02-07',
                    'jumlah_jam'       => 40,
                    'status'           => 'Aktif',
                ],
            ],

            // Modul 10 & 11: Pengarsipan SKP (2 Tahun), Piagam Satyalancana Terdahulu & Penghargaan
            'riwayat_skp' => [
                0 => [
                    'tahun'            => 2025,
                    'predikat_kinerja' => 'Sangat Baik',
                    'pejabat_penilai'  => 'Dekan FKP UNRI',
                ],
                1 => [
                    'tahun'            => 2024,
                    'predikat_kinerja' => 'Baik',
                    'pejabat_penilai'  => 'Dekan FKP UNRI',
                ],
            ],
            'piagam_satyalancana' => [
                'nama_penghargaan' => 'Satyalancana Karya Satya 10 Tahun',
                'nomor_sk'         => 'Keppres No. 45/TK/2020',
                'tanggal_sk'       => '2020-08-17',
                'instansi_pemberi' => 'Presiden Republik Indonesia',
            ],
            'riwayat_penghargaan' => [
                [
                    'nama_penghargaan'  => 'Peneliti Terbaik UNRI',
                    'jenis_penghargaan' => 'Akademik',
                    'instansi_pemberi'  => 'LPPM UNRI',
                    'nomor_sk'          => 'SK-LPPM/2025/12',
                    'tanggal_terima'    => '2025-11-10',
                ],
            ],

            // Modul 12: Riwayat Keanggotaan Organisasi
            'riwayat_organisasi' => [
                [
                    'nama_organisasi'    => 'AIPNI Regional Sumatera',
                    'jabatan_organisasi' => 'Ketua Divisi Kurikulum',
                    'tahun_mulai'        => 2023,
                    'masih_aktif'        => 1,
                ],
            ],

            // Modul 13: Riwayat Publikasi Ilmiah & Karya
            'riwayat_publikasi' => [
                [
                    'judul_publikasi' => 'Community Nursing Intervention in Coastal Areas',
                    'jenis_publikasi' => 'Jurnal',
                    'indeksasi'       => 'Scopus',
                    'nama_jurnal'     => 'International Journal of Nursing Studies',
                    'tahun_terbit'    => 2025,
                    'url_doi'         => 'https://doi.org/10.1016/j.ijnurstu.2025.01',
                ],
            ],

            // Modul 14: Tugas Belajar & Izin Belajar (Studi Lanjut)
            'tugas_belajar' => [
                [
                    'jenis_pengembangan' => 'Tugas Belajar',
                    'jenjang_studi'      => 'Post Doctoral',
                    'perguruan_tinggi'   => 'University of Melbourne',
                    'program_studi'      => 'Advanced Nursing Research',
                    'negara'             => 'Australia',
                    'sumber_pembiayaan'  => 'Beasiswa Dikti',
                    'nomor_sk'           => 'SK-POSTDOC/2026/01',
                    'tanggal_mulai'      => '2026-01-10',
                    'tanggal_selesai'    => '2027-01-10',
                    'semester_berjalan'  => 2,
                    'status_studi'       => 'Sedang Studi',
                ],
            ],
        ];

        $resp = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);
        $resp->assertSessionHasNoErrors();
        $resp->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::where('nip', '198505102010011099')->first();
        $this->assertNotNull($pegawai);

        // Verifikasi normalisasi jenis_kelamin & pendidikan_terakhir tertinggi (S3 dari riwayat_pendidikan)
        $this->assertEquals('L', $pegawai->jenis_kelamin);
        $this->assertEquals('S3', $pegawai->pendidikan_terakhir);

        // Verifikasi sinkronisasi Penugasan dengan peran Ketua & Sekretaris
        $this->assertDatabaseHas('pegawai_penugasan', [
            'pegawai_id'            => $pegawai->id,
            'kelompok_penugasan_id' => $kelompokSenat->id,
            'peran'                 => 'Ketua',
        ]);
        $this->assertDatabaseHas('pegawai_penugasan', [
            'pegawai_id'            => $pegawai->id,
            'kelompok_penugasan_id' => $kelompokSekretaris->id,
            'peran'                 => 'Sekretaris',
        ]);

        // Verifikasi sinkronisasi STR & SIP ke tabel riwayat_str_sip
        $this->assertDatabaseHas('riwayat_str_sip', [
            'pegawai_id'       => $pegawai->id,
            'jenis_dokumen'    => 'STR',
            'nomor_registrasi' => 'STR-NERS-2026-001',
        ]);
        $this->assertDatabaseHas('riwayat_str_sip', [
            'pegawai_id'       => $pegawai->id,
            'jenis_dokumen'    => 'SIP',
            'nomor_registrasi' => 'SIP-NERS-2026-002',
        ]);

        // Verifikasi nomor_sertifikat Diklat tersimpan
        $this->assertDatabaseHas('riwayat_diklat', [
            'pegawai_id'       => $pegawai->id,
            'nomor_sertifikat' => 'SERT-PKA-2026-09',
            'jumlah_jam'       => 40,
        ]);

        // Verifikasi status_pegawai otomatis menjadi 'Tugas Belajar' karena ada Tugas Belajar berstatus 'Sedang Studi'
        $this->assertEquals(Pegawai::STATUS_TUGAS_BELAJAR, $pegawai->status_pegawai);

        // Verifikasi SatyalancanaService: masa kerja ~16 tahun (TMT 2010) dan sudah punya 'Satyalancana Karya Satya 10 Tahun'
        // maka cekKelayakanSatyalancana harus mengembalikan null (tidak dianggap belum dapat 10 Tahun)
        $satyalancanaService = app(\App\Services\SatyalancanaService::class);
        $this->assertNull($satyalancanaService->cekKelayakanSatyalancana($pegawai));
    }
}
