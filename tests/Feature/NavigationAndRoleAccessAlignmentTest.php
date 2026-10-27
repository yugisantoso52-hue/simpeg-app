<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationAndRoleAccessAlignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected User $pegawaiUser;
    protected Pegawai $pimpinanPegawai;
    protected Pegawai $regularPegawai;
    protected UnitKerja $unitKerja;
    protected Jabatan $jabatanDekan;
    protected Jabatan $jabatanDosen;
    protected Golongan $golongan;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->unitKerja = UnitKerja::firstOrCreate(['kode_unit' => 'FKP-DKN'], ['nama_unit' => 'Dekanat FKp']);
        $this->jabatanDekan = Jabatan::firstOrCreate(['kode_jabatan' => 'DKN-01'], ['nama_jabatan' => 'Dekan Fakultas Keperawatan', 'unit_kerja_id' => $this->unitKerja->id]);
        $this->jabatanDosen = Jabatan::firstOrCreate(['kode_jabatan' => 'DSN-01'], ['nama_jabatan' => 'Dosen Keperawatan Komunitas', 'unit_kerja_id' => $this->unitKerja->id]);
        $this->golongan = Golongan::firstOrCreate(['nama_golongan' => 'IV/a'], ['nama_pangkat' => 'Pembina']);

        // 1. Akun Admin
        $this->adminUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);

        // 2. Akun Pimpinan (Dekan)
        $this->pimpinanPegawai = Pegawai::create([
            'nip'             => '197001011995011001',
            'nama'            => 'Prof. Dr. Dekan FKp',
            'email'           => 'dekan@fkp.unri.ac.id',
            'jenis_pegawai'   => 'Dosen',
            'status_asn'      => 'PNS',
            'status_pegawai'  => 'Aktif',
            'unit_kerja_id'   => $this->unitKerja->id,
            'jabatan_id'      => $this->jabatanDekan->id,
            'golongan_id'     => $this->golongan->id,
        ]);

        $this->pimpinanUser = User::factory()->create([
            'role_id'              => $pimpinanRole->id,
            'pegawai_id'           => $this->pimpinanPegawai->id,
            'email'                => 'dekan@fkp.unri.ac.id',
            'must_change_password' => false,
        ]);

        // 3. Akun Pegawai Biasa
        $this->regularPegawai = Pegawai::create([
            'nip'             => '199201012022031002',
            'nama'            => 'Ns. Dosen Biasa, M.Kep',
            'email'           => 'dosen.biasa@fkp.unri.ac.id',
            'jenis_pegawai'   => 'Dosen',
            'status_asn'      => 'PNS',
            'status_pegawai'  => 'Aktif',
            'unit_kerja_id'   => $this->unitKerja->id,
            'jabatan_id'      => $this->jabatanDosen->id,
            'golongan_id'     => $this->golongan->id,
        ]);

        $this->pegawaiUser = User::factory()->create([
            'role_id'              => $pegawaiRole->id,
            'pegawai_id'           => $this->regularPegawai->id,
            'email'                => 'dosen.biasa@fkp.unri.ac.id',
            'must_change_password' => false,
        ]);
    }

    /**
     * 1. Test antarmuka & proteksi navigasi untuk Akun Admin
     */
    /**
     * 1. Test antarmuka & proteksi navigasi untuk Akun Admin
     */
    public function test_admin_has_full_navigation_access_including_master_system(): void
    {
        $dashboardResponse = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);

        // Admin melihat menu Master & Sistem
        $dashboardResponse->assertSee('Master & Sistem', false);
        $dashboardResponse->assertSee(route('unit-kerja.index'));
        $dashboardResponse->assertSee(route('jabatan.index'));
        $dashboardResponse->assertSee(route('backup.index'));

        // Admin dapat mengakses halaman Master Sistem
        $this->actingAs($this->adminUser)->get(route('unit-kerja.index'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('backup.index'))->assertStatus(200);

        // Admin dapat mengakses modul pemantauan KP, KGB, dan Satyalancana
        $this->actingAs($this->adminUser)->get(route('kp.index'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('kgb.index'))->assertStatus(200);
        $this->actingAs($this->adminUser)->get(route('satyalancana.index'))->assertStatus(200);
    }

    /**
     * 2. Test antarmuka & proteksi navigasi untuk Akun Pimpinan (Dekan / Wadek)
     */
    public function test_pimpinan_has_executive_navigation_and_appropriate_career_supervision(): void
    {
        $dashboardResponse = $this->actingAs($this->pimpinanUser)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);

        // Pimpinan melihat menu Analitik Eksekutif dan Pengawasan Karir
        $dashboardResponse->assertSee('Analitik Eksekutif');
        $dashboardResponse->assertSee('Layanan Karir');
        $dashboardResponse->assertSee('Verifikasi Usulan Karir & SLKS', false);
        $dashboardResponse->assertSee('Satyalancana Karya Satya');

        // Pimpinan TIDAK melihat menu Master & Sistem admin
        $dashboardResponse->assertDontSee(route('backup.index'));
        $dashboardResponse->assertDontSee('Master & Sistem', false);

        // Pimpinan dapat mengakses pemantauan Satyalancana
        $satyaResponse = $this->actingAs($this->pimpinanUser)->get(route('satyalancana.index'));
        $satyaResponse->assertStatus(200);

        // Halaman eksekusi pemrosesan KP & KGB dibatasi untuk admin (403 untuk pimpinan)
        $this->actingAs($this->pimpinanUser)->get(route('kp.index'))->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get(route('kgb.index'))->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get(route('backup.index'))->assertStatus(403);
    }

    /**
     * 3. Test antarmuka & proteksi navigasi untuk Akun Pegawai Biasa
     */
    public function test_pegawai_has_clean_mandiri_navigation_without_403_on_duk_and_tugas_belajar(): void
    {
        $dashboardResponse = $this->actingAs($this->pegawaiUser)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);

        // Pegawai melihat Profil & Karir Saya dengan sub-opsi KP, KGB, dan Satyalancana yang jelas
        $dashboardResponse->assertSee('Profil & Karir Saya', false);
        $dashboardResponse->assertSee('Kenaikan Pangkat (KP)');
        $dashboardResponse->assertSee('Kenaikan Gaji Berkala (KGB)');
        $dashboardResponse->assertSee('Satyalancana Karya Satya');
        $dashboardResponse->assertSee('Daftar Urut Kepangkatan (DUK)');
        $dashboardResponse->assertSee('Tugas Belajar & Studi Saya', false);

        // Pegawai TIDAK melihat menu admin & eksekutif
        $dashboardResponse->assertDontSee('Analitik Eksekutif');
        $dashboardResponse->assertDontSee('Master & Sistem', false);
        $dashboardResponse->assertDontSee('Semua Pegawai (Master)');
        $dashboardResponse->assertDontSee('href="' . route('pegawai.index') . '"', false);

        // Pegawai dapat membuka DUK tanpa 403
        $dukResponse = $this->actingAs($this->pegawaiUser)->get(route('duk.index'));
        $dukResponse->assertStatus(200);

        // Pegawai dapat membuka Tugas Belajar tanpa 403
        $tubelResponse = $this->actingAs($this->pegawaiUser)->get(route('tugas-belajar.index'));
        $tubelResponse->assertStatus(200);

        // Jika pegawai mengakses route /kenaikan-pangkat atau /kgb, dialihkan dengan aman ke pengajuan karir mandiri (tanpa 403)
        $kpRedirect = $this->actingAs($this->pegawaiUser)->get(route('kp.index'));
        $kpRedirect->assertRedirect(route('pengajuan-karir.index', ['tab' => 'kp', 'jenis' => 'KP']));

        $kgbRedirect = $this->actingAs($this->pegawaiUser)->get(route('kgb.index'));
        $kgbRedirect->assertRedirect(route('pengajuan-karir.index', ['tab' => 'kgb', 'jenis' => 'KGB']));

        $satyaRedirect = $this->actingAs($this->pegawaiUser)->get(route('satyalancana.index'));
        $satyaRedirect->assertRedirect(route('pengajuan-karir.index', ['tab' => 'satyalancana']));

        // Pegawai diblokir (403) jika mencoba membobol halaman eksekutif
        $this->actingAs($this->pegawaiUser)->get(route('pimpinan.analytics'))->assertStatus(403);
        $this->actingAs($this->pegawaiUser)->get(route('backup.index'))->assertStatus(403);
    }
}
