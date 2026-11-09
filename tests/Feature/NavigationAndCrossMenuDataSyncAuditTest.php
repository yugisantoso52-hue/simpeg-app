<?php

namespace Tests\Feature;

use App\Models\AnalisisJabatan;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\JenisJabatan;
use App\Models\Pegawai;
use App\Models\PengajuanJabatan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NavigationAndCrossMenuDataSyncAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Pegawai $adminPegawai;
    protected UnitKerja $unitKerja1;
    protected UnitKerja $unitKerja2;
    protected Jabatan $jabatan1;
    protected Jabatan $jabatan2;
    protected Golongan $golongan1;
    protected Golongan $golongan2;
    protected JenisJabatan $jenisJabatan1;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);

        $this->unitKerja1 = UnitKerja::create(['nama_unit' => 'Subbagian Akademik & Kepegawaian', 'kode_unit' => 'AKD', 'urutan' => 1]);
        $this->unitKerja2 = UnitKerja::create(['nama_unit' => 'Jurusan Keperawatan', 'kode_unit' => 'JKP', 'urutan' => 2]);

        $this->jabatan1 = Jabatan::create([
            'kode_jabatan'     => 'JAB-ADM-01',
            'nama_jabatan'     => 'Pengelola Kepegawaian',
            'unit_kerja_id'    => $this->unitKerja1->id,
            'kelas_jabatan'    => 7,
            'kelompok_jabatan' => 'Tenaga Kependidikan & Tata Usaha',
        ]);
        $this->jabatan2 = Jabatan::create([
            'kode_jabatan'     => 'JAB-DOS-01',
            'nama_jabatan'     => 'Lektor',
            'unit_kerja_id'    => $this->unitKerja2->id,
            'kelas_jabatan'    => 11,
            'kelompok_jabatan' => 'Jabatan Fungsional Dosen',
        ]);

        $this->golongan1 = Golongan::create(['nama_golongan' => 'III/b', 'nama_pangkat' => 'Penata Muda Tk. I']);
        $this->golongan2 = Golongan::create(['nama_golongan' => 'III/c', 'nama_pangkat' => 'Penata']);

        $this->jenisJabatan1 = JenisJabatan::create([
            'nama_jenis_jabatan' => 'FUNGSIONAL TERTENTU',
            'keterangan'         => 'Jabatan Fungsional Tertentu ASN',
        ]);

        $this->adminPegawai = Pegawai::create([
            'nip'           => '199001012019031001',
            'nama'          => 'Admin Kepegawaian FKP',
            'jenis_pegawai' => 'Tendik',
            'status_pegawai'=> 'Aktif',
            'jenis_jabatan' => 'FUNGSIONAL TERTENTU',
            'unit_kerja_id' => $this->unitKerja1->id,
            'jabatan_id'    => $this->jabatan1->id,
            'golongan_id'   => $this->golongan1->id,
        ]);

        $this->adminUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'pegawai_id'           => $this->adminPegawai->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Audit seluruh menu utama & submenu dropdown di SIKAP (0% Broken Link / 404 / 500)
     */
    public function test_all_navigation_menus_and_submenus_return_zero_broken_links(): void
    {
        $routesToAudit = [
            // Header & User Bar
            ['GET', route('notifications.index'), 200],
            ['GET', route('profile.edit'), 200],

            // 1. Dashboard Utama
            ['GET', route('dashboard'), 200],

            // 2. Analitik Eksekutif
            ['GET', route('pimpinan.analytics'), 200],

            // 3. Profil & Karir Saya
            ['GET', route('pegawai.my-profile'), 302], // Redirects to pegawai.show
            ['GET', route('duk.index'), 200],
            ['GET', route('pengajuan-karir.index', ['tab' => 'kp', 'jenis' => 'KP']), 200],
            ['GET', route('pengajuan-karir.index', ['tab' => 'kgb', 'jenis' => 'KGB']), 200],
            ['GET', route('pengajuan-karir.index', ['tab' => 'satyalancana']), 200],
            ['GET', route('pengajuan-karir.index', ['scope' => 'saya']), 200],
            ['GET', route('pengajuan-karir.create', ['jenis' => 'KP']), 200],
            ['GET', route('pengajuan-karir.create', ['jenis' => 'KGB']), 200],
            ['GET', route('pengajuan-jabatan.index'), 200],
            ['GET', route('tugas-belajar.index'), 200],
            ['GET', route('manajemen-talenta.my-talent'), 200],

            // 4. Data Kepegawaian (Data Induk & 9 Modul Riwayat)
            ['GET', route('pegawai.index'), 200],
            ['GET', route('kepegawaian.dosen.index'), 200],
            ['GET', route('kepegawaian.tendik.index'), 200],
            ['GET', route('kepegawaian.phl.index'), 200],
            ['GET', route('riwayat-pangkat.index'), 200],
            ['GET', route('riwayat-jabatan.index'), 200],
            ['GET', route('riwayat-pendidikan.index'), 200],
            ['GET', route('riwayat-diklat.index'), 200],
            ['GET', route('riwayat-skp.index'), 200],
            ['GET', route('riwayat-str-sip.index'), 200],
            ['GET', route('riwayat-publikasi.index'), 200],
            ['GET', route('riwayat-penghargaan.index'), 200],
            ['GET', route('riwayat-organisasi.index'), 200],

            // 5. Layanan Karir & Mutasi
            ['GET', route('pengajuan-karir.index'), 200],
            ['GET', route('kp.index'), 200],
            ['GET', route('kgb.index'), 200],
            ['GET', route('satyalancana.index'), 200],
            ['GET', route('mutasi-pegawai.index'), 200],

            // 6. Presensi & Kinerja
            ['GET', route('admin.presensi.index'), 200],
            ['GET', route('admin.presensi.locations'), 200],
            ['GET', route('presensi.index'), 200],
            ['GET', route('presensi.history'), 200],
            ['GET', route('admin.logbook.index'), 200],
            ['GET', route('logbook.index'), 200],
            ['GET', route('logbook.create'), 200],

            // 7. Tata Naskah & E-Layanan
            ['GET', route('layanan-surat.index'), 200],
            ['GET', route('pengajuan-cuti.index'), 200],
            ['GET', route('pengajuan-cuti.index', ['mine' => 1]), 200],
            ['GET', route('pengajuan-cuti.create'), 200],
            ['GET', route('regulasi-tata-naskah.index'), 200],

            // 8. Perencanaan & Talenta
            ['GET', route('anjab.peta-jabatan'), 200],
            ['GET', route('abk.index'), 200],
            ['GET', route('abk.simulasi-formasi'), 200],
            ['GET', route('anjab.index'), 200],
            ['GET', route('anjab.create'), 200],
            ['GET', route('manajemen-talenta.index'), 200],
            ['GET', route('manajemen-talenta.rekap'), 200],
            ['GET', route('manajemen-talenta.suksesi.index'), 200],
            ['GET', route('manajemen-talenta.idp'), 200],
            ['GET', route('manajemen-talenta.sidang-komite.index'), 200],
            ['GET', route('manajemen-talenta.asesmen.index'), 200],

            // 9. Master & Sistem
            ['GET', route('unit-kerja.index'), 200],
            ['GET', route('jabatan.index'), 200],
            ['GET', route('golongan.index'), 200],
            ['GET', route('jenis-jabatan.index'), 200],
            ['GET', route('audit-logs.index'), 200],
            ['GET', route('backup.index'), 200],
        ];

        foreach ($routesToAudit as [$method, $url, $expectedStatus]) {
            $response = $this->actingAs($this->adminUser)->call($method, $url);
            $this->assertEquals(
                $expectedStatus,
                $response->getStatusCode(),
                "Broken route detected on [{$method} {$url}]: expected {$expectedStatus}, got {$response->getStatusCode()}"
            );
        }
    }

    /**
     * Audit sinkronisasi perubahan Master Data (Jenis Jabatan, Jabatan, Unit Kerja, Golongan) ke Modul Kepegawaian & Anjab
     */
    public function test_master_data_updates_and_deletion_guards_synchronize_across_modules(): void
    {
        // 1. Buat dokumen Anjab untuk Jabatan 1
        $anjab = AnalisisJabatan::create([
            'jabatan_id'    => $this->jabatan1->id,
            'unit_kerja_id' => $this->unitKerja1->id,
            'kode_anjab'    => 'ANJAB-001',
            'kelas_jabatan' => 7,
            'status'        => 'disetujui',
        ]);

        // 2. Update Master Jabatan -> pastikan kelas_jabatan & unit_kerja_id pada Anjab ikut tersinkron
        $this->actingAs($this->adminUser)
            ->put(route('jabatan.update', $this->jabatan1->id), [
                'kode_jabatan'     => 'JAB-ADM-01',
                'nama_jabatan'     => 'Pengelola Kepegawaian Ahli',
                'unit_kerja_id'    => $this->unitKerja2->id,
                'kelas_jabatan'    => 8,
                'kelompok_jabatan' => 'Tenaga Kependidikan & Tata Usaha',
            ])
            ->assertRedirect(route('jabatan.index'));

        $anjab->refresh();
        $this->assertEquals($this->unitKerja2->id, $anjab->unit_kerja_id);
        $this->assertEquals(8, $anjab->kelas_jabatan);

        // 3. Coba hapus Master Jabatan yang sedang dipakai pegawai aktif -> harus dicegah tanpa 500 error
        $this->actingAs($this->adminUser)
            ->delete(route('jabatan.destroy', $this->jabatan1->id))
            ->assertRedirect(route('jabatan.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('jabatan', ['id' => $this->jabatan1->id]);

        // 4. Update Master Jenis Jabatan -> pastikan pegawai.jenis_jabatan ikut tersinkron otomatis
        $this->actingAs($this->adminUser)
            ->put(route('jenis-jabatan.update', $this->jenisJabatan1->id), [
                'nama_jenis_jabatan' => 'JABATAN FUNGSIONAL TERTENTU',
                'keterangan'         => 'Updated',
            ])
            ->assertRedirect(route('jenis-jabatan.index'));

        $this->adminPegawai->refresh();
        $this->assertEquals('JABATAN FUNGSIONAL TERTENTU', $this->adminPegawai->jenis_jabatan);

        // 5. Coba hapus Master Jenis Jabatan yang sedang dipakai pegawai aktif -> harus dicegah tanpa 500 error
        $this->actingAs($this->adminUser)
            ->delete(route('jenis-jabatan.destroy', $this->jenisJabatan1->id))
            ->assertRedirect(route('jenis-jabatan.index'))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('jenis_jabatan', ['id' => $this->jenisJabatan1->id]);

        // 6. Verifikasi Pengajuan Jabatan Struktural -> sinkron ke Pegawai & RiwayatJabatan
        $pengajuanJabatan = PengajuanJabatan::create([
            'pegawai_id'    => $this->adminPegawai->id,
            'jabatan_id'    => $this->jabatan2->id,
            'unit_kerja_id' => $this->unitKerja2->id,
            'nomor_sk'      => 'SK-JAB/2026/01',
            'tanggal_sk'    => '2026-01-10',
            'tmt_jabatan'   => '2026-02-01',
            'status'        => PengajuanJabatan::STATUS_MENUNGGU,
            'diajukan_oleh' => $this->adminUser->id,
        ]);

        $this->actingAs($this->adminUser)
            ->post(route('pengajuan-jabatan.verifikasi', $pengajuanJabatan->id), [
                'aksi'    => 'setujui',
                'catatan' => 'SK Valid',
            ])
            ->assertRedirect();

        $this->adminPegawai->refresh();
        $this->assertEquals($this->jabatan2->id, $this->adminPegawai->jabatan_id);
        $this->assertEquals($this->unitKerja2->id, $this->adminPegawai->unit_kerja_id);
        $this->assertDatabaseHas('riwayat_jabatan', [
            'pegawai_id' => $this->adminPegawai->id,
            'jabatan_id' => $this->jabatan2->id,
            'nomor_sk'   => 'SK-JAB/2026/01',
        ]);
    }
}
