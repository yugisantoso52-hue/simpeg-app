<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\LayananSurat;
use App\Models\MutasiPegawai;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PengajuanKarir;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\Role;
use App\Models\TugasBelajar;
use App\Models\UnitKerja;
use App\Models\User;
use App\Models\UsulanSatyalancana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComprehensiveSystemArchitectureAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pegawaiUser;
    protected Pegawai $pegawaiLengkap;
    protected Pegawai $pegawaiKosong;
    protected UnitKerja $unitKerja;
    protected Jabatan $jabatan;
    protected Golongan $golongan;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->unitKerja = UnitKerja::create([
            'kode_unit' => 'FKP-01',
            'nama_unit' => 'Bagian Keperawatan Medikal',
            'urutan'    => 1,
        ]);

        $this->jabatan = Jabatan::create([
            'kode_jabatan'  => 'JAB-001',
            'nama_jabatan'  => 'Dosen Asisten Ahli',
            'kelas_jabatan' => 9,
        ]);

        $this->golongan = Golongan::create([
            'nama_golongan' => 'III/b',
            'nama_pangkat'  => 'Penata Muda Tingkat I',
        ]);

        // Pegawai dengan data lengkap
        $this->pegawaiLengkap = Pegawai::create([
            'nip'                  => '198501012010011001',
            'nama'                 => 'Dr. Ns. Siti Aminah, M.Kep',
            'jenis_pegawai'        => 'dosen',
            'status_kepegawaian'   => 'pns',
            'status_pegawai'       => 'Aktif',
            'unit_kerja_id'        => $this->unitKerja->id,
            'jabatan_id'           => $this->jabatan->id,
            'golongan_id'          => $this->golongan->id,
            'pendidikan_terakhir'  => 'S3',
            'tmt_pangkat_terakhir' => '2022-04-01',
            'tmt_kgb_terakhir'     => '2023-01-01',
        ]);

        // Pegawai baru dengan relasi yang masih kosong (null)
        $this->pegawaiKosong = Pegawai::create([
            'nip'                 => '199912312024012009',
            'nama'                => 'Pegawai Baru Belum Lengkap',
            'jenis_pegawai'       => 'tendik',
            'status_kepegawaian'  => 'pns',
            'status_pegawai'      => 'Aktif',
            'unit_kerja_id'       => null,
            'jabatan_id'          => null,
            'golongan_id'         => null,
            'pendidikan_terakhir' => null,
        ]);

        $this->pegawaiUser = User::factory()->create([
            'role_id'              => $pegawaiRole->id,
            'pegawai_id'           => $this->pegawaiKosong->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Test 1: Halaman-halaman utama dapat dibuka tanpa error 500 meskipun data berelasi null
     */
    public function test_all_major_modules_render_safely_with_empty_or_null_relations(): void
    {
        $routesToTest = [
            'dashboard',
            'pegawai.index',
            'duk.index',
            'kp.index',
            'kgb.index',
            'pengajuan-karir.index',
            'pengajuan-cuti.index',
            'layanan-surat.index',
            'mutasi-pegawai.index',
            'tugas-belajar.index',
            'riwayat-pangkat.index',
            'riwayat-jabatan.index',
            'riwayat-skp.index',
            'riwayat-pendidikan.index',
            'riwayat-diklat.index',
            'riwayat-organisasi.index',
            'riwayat-penghargaan.index',
            'riwayat-publikasi.index',
            'riwayat-str-sip.index',
            'satyalancana.index',
            'anjab.index',
            'abk.index',
        ];

        foreach ($routesToTest as $routeName) {
            $response = $this->actingAs($this->adminUser)->get(route($routeName));
            $this->assertNotEquals(
                500,
                $response->getStatusCode(),
                "Route [{$routeName}] mengalami kegagalan 500 saat merender pegawai berelasi null."
            );
            $this->assertEquals(200, $response->getStatusCode(), "Route [{$routeName}] gagal dibuka (bukan 200).");
        }
    }

    /**
     * Test 2: User pegawai dengan relasi null membuka dashboard & halaman profil tanpa error
     */
    public function test_regular_pegawai_with_null_relations_can_access_dashboard_and_profile(): void
    {
        // Akses dashboard oleh pegawai yang belum memiliki jabatan/golongan
        $responseDash = $this->actingAs($this->pegawaiUser)->get(route('dashboard'));
        $responseDash->assertStatus(200);

        // Akses halaman detail profil sendiri
        $responseProfile = $this->actingAs($this->pegawaiUser)->get(route('pegawai.show', $this->pegawaiKosong->id));
        $responseProfile->assertStatus(200);
        $responseProfile->assertSee('Pegawai Baru Belum Lengkap');
    }

    /**
     * Test 3: Formulir cetak PDF tidak crash ketika data jabatan/golongan bernilai null
     */
    public function test_print_forms_and_exports_render_safely_when_relations_are_null(): void
    {
        // 1. Buat permohonan cuti untuk pegawai null
        $cuti = PengajuanCuti::create([
            'pegawai_id'      => $this->pegawaiKosong->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'nomor_surat'     => '001/UN19.5.1.1.10/KP/2026',
            'tanggal_mulai'   => '2026-11-01',
            'tanggal_selesai' => '2026-11-05',
            'jumlah_hari'     => 5,
            'alasan'          => 'Keperluan pribadi',
            'status'          => 'Menunggu Persetujuan',
        ]);

        $responseCutiPdf = $this->actingAs($this->adminUser)->get(route('pengajuan-cuti.cetak-pdf', $cuti->id));
        $this->assertEquals(200, $responseCutiPdf->getStatusCode());

        // 2. Buat layanan surat aktif untuk pegawai null
        $suratAktif = LayananSurat::create([
            'pegawai_id'             => $this->pegawaiKosong->id,
            'jenis_layanan'          => 'Surat Keterangan Aktif Bekerja',
            'nomor_surat'            => 'B/0001/UN19.5.1.1.10/KP/2026',
            'keperluan'              => 'Pengajuan Beasiswa',
            'status'                 => 'disetujui_wd2',
            'penandatangan_jabatan'  => 'Wakil Dekan Bidang Keuangan dan Umum',
            'tanggal_surat'          => '2026-10-27',
        ]);

        $responseSuratPdf = $this->actingAs($this->adminUser)->get(route('layanan-surat.surat-aktif.cetak', $suratAktif->id));
        $this->assertEquals(200, $responseSuratPdf->getStatusCode());

        // 3. Export PDF DUK resmi
        $responseDukPdf = $this->actingAs($this->adminUser)->get(route('reports.duk.pdf'));
        $this->assertEquals(200, $responseDukPdf->getStatusCode());
    }

    /**
     * Test 4: Profiling efisiensi kueri dan ketiadaan N+1 pada daftar pegawai
     */
    public function test_pegawai_index_query_count_is_bounded_and_free_of_n_plus_one(): void
    {
        // Buat 10 pegawai tambahan untuk memastikan query tidak meningkat proporsional dengan jumlah baris
        for ($i = 1; $i <= 10; $i++) {
            $p = Pegawai::create([
                'nip'                 => '1990010120200110' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'nama'                => 'Pegawai Batch ' . $i,
                'jenis_pegawai'       => $i % 2 === 0 ? 'dosen' : 'tendik',
                'status_kepegawaian'  => 'pns',
                'status_pegawai'      => 'Aktif',
                'unit_kerja_id'       => $this->unitKerja->id,
                'jabatan_id'          => $this->jabatan->id,
                'golongan_id'         => $this->golongan->id,
            ]);

            RiwayatPangkat::create([
                'pegawai_id'  => $p->id,
                'golongan_id' => $this->golongan->id,
                'tmt'         => '2020-01-01',
                'status'      => 'aktif',
            ]);

            RiwayatJabatan::create([
                'pegawai_id'    => $p->id,
                'jabatan_id'    => $this->jabatan->id,
                'unit_kerja_id' => $this->unitKerja->id,
                'tmt'           => '2020-01-01',
                'status'        => 'aktif',
            ]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($this->adminUser)->get(route('pegawai.index'));
        $response->assertStatus(200);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Dengan 12 pegawai aktif dan eager loading terkonfigurasi, total query untuk auth, session, data & relasi
        // harus tetap terbatas (di bawah 30 query), tidak memicu N query tambahan per pegawai.
        $this->assertLessThan(
            30,
            count($queries),
            "Jumlah query pada pegawai.index terlalu banyak (" . count($queries) . " queries), mengindikasikan N+1 query."
        );
    }
}
