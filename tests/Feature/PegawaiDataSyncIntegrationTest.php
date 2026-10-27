<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatPendidikan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\RiwayatJabatanService;
use App\Services\RiwayatPangkatService;
use App\Services\RiwayatPendidikanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PegawaiDataSyncIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Pegawai $pegawai;
    protected Golongan $golonganAwal;
    protected Golongan $golonganBaru;
    protected Jabatan $jabatanAwal;
    protected Jabatan $jabatanBaru;
    protected UnitKerja $unitKerjaAwal;
    protected UnitKerja $unitKerjaBaru;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);

        $this->golonganAwal = Golongan::create([
            'nama_golongan' => 'III/a',
            'nama_pangkat'  => 'Penata Muda',
        ]);

        $this->golonganBaru = Golongan::create([
            'nama_golongan' => 'III/b',
            'nama_pangkat'  => 'Penata Muda Tk. I',
        ]);

        $this->unitKerjaAwal = UnitKerja::create([
            'kode_unit' => 'BAG-KEP-01',
            'nama_unit' => 'Bagian Kepegawaian',
            'jenis'     => 'Bagian',
        ]);

        $this->unitKerjaBaru = UnitKerja::create([
            'kode_unit' => 'JUR-KEP-02',
            'nama_unit' => 'Jurusan Keperawatan',
            'jenis'     => 'Jurusan',
        ]);

        $this->jabatanAwal = Jabatan::create([
            'kode_jabatan'  => 'JAB-001',
            'nama_jabatan'  => 'Staf Administrasi',
            'jenis_jabatan' => 'Pelaksana',
        ]);

        $this->jabatanBaru = Jabatan::create([
            'kode_jabatan'  => 'JAB-002',
            'nama_jabatan'  => 'Pranata Laboratorium',
            'jenis_jabatan' => 'Fungsional Tertentu',
        ]);

        $this->pegawai = Pegawai::create([
            'nip'                  => '199001012015011001',
            'nama'                 => 'Budi Test Synchronization',
            'jenis_pegawai'        => 'tendik',
            'status_kepegawaian'   => 'pns',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golonganAwal->id,
            'jabatan_id'           => $this->jabatanAwal->id,
            'unit_kerja_id'        => $this->unitKerjaAwal->id,
            'pendidikan_terakhir'  => 'D3',
        ]);
    }

    public function test_riwayat_pangkat_service_synchronizes_pegawai_and_busts_cache(): void
    {
        Cache::put('dashboard_faculty_completeness', ['cached' => true], 3600);
        $this->assertTrue(Cache::has('dashboard_faculty_completeness'));

        $pangkatService = app(RiwayatPangkatService::class);

        // 1. Create riwayat pangkat baru
        $riwayat1 = $pangkatService->create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganAwal->id,
            'tmt'         => '2020-04-01',
            'nomor_sk'    => 'SK-001',
            'status'      => 'aktif',
        ]);

        $riwayat2 = $pangkatService->create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganBaru->id,
            'tmt'         => '2024-04-01',
            'nomor_sk'    => 'SK-002',
            'status'      => 'aktif',
        ]);

        // Dashboard cache harus otomatis ter-flush
        $this->assertFalse(Cache::has('dashboard_faculty_completeness'));

        // Pegawai harus otomatis tersinkron ke golongan baru
        $this->pegawai->refresh();
        $this->assertEquals($this->golonganBaru->id, $this->pegawai->golongan_id);
        $this->assertEquals('2024-04-01', $this->pegawai->tmt_pangkat_terakhir?->toDateString());

        // 2. Delete riwayat pangkat terbaru
        $pangkatService->delete($riwayat2->id);

        // Pegawai harus kembali sinkron ke riwayat1
        $this->pegawai->refresh();
        $this->assertEquals($this->golonganAwal->id, $this->pegawai->golongan_id);
        $this->assertEquals('2020-04-01', $this->pegawai->tmt_pangkat_terakhir?->toDateString());
    }

    public function test_riwayat_jabatan_service_synchronizes_pegawai_and_busts_cache(): void
    {
        $jabatanService = app(RiwayatJabatanService::class);

        // Buat riwayat jabatan awal
        $rj1 = $jabatanService->create([
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatanAwal->id,
            'unit_kerja_id' => $this->unitKerjaAwal->id,
            'tmt'           => '2020-01-01',
            'nomor_sk'      => 'SK-JAB-01',
            'status'        => 'non-aktif',
        ]);

        // Buat riwayat jabatan baru yang aktif
        $rj2 = $jabatanService->create([
            'pegawai_id'    => $this->pegawai->id,
            'jabatan_id'    => $this->jabatanBaru->id,
            'unit_kerja_id' => $this->unitKerjaBaru->id,
            'tmt'           => '2023-06-01',
            'nomor_sk'      => 'SK-JAB-02',
            'status'        => 'aktif',
        ]);

        $this->pegawai->refresh();
        $this->assertEquals($this->jabatanBaru->id, $this->pegawai->jabatan_id);
        $this->assertEquals($this->unitKerjaBaru->id, $this->pegawai->unit_kerja_id);

        // Hapus riwayat jabatan aktif
        $jabatanService->delete($rj2->id);

        $this->pegawai->refresh();
        $this->assertEquals($this->jabatanAwal->id, $this->pegawai->jabatan_id);
        $this->assertEquals($this->unitKerjaAwal->id, $this->pegawai->unit_kerja_id);
    }

    public function test_riwayat_pendidikan_service_synchronizes_highest_education(): void
    {
        $pendidikanService = app(RiwayatPendidikanService::class);

        // Tambah S1
        $rp1 = $pendidikanService->create([
            'pegawai_id'        => $this->pegawai->id,
            'jenjang'           => 'S1',
            'institusi'         => 'Universitas Riau',
            'jurusan'           => 'Ilmu Keperawatan',
            'tahun_lulus'       => 2018,
            'pendidikan_terakhir' => true,
        ], null);

        $this->pegawai->refresh();
        $this->assertEquals('S1', $this->pegawai->pendidikan_terakhir);

        // Tambah S2
        $rp2 = $pendidikanService->create([
            'pegawai_id'        => $this->pegawai->id,
            'jenjang'           => 'S2',
            'institusi'         => 'Universitas Indonesia',
            'jurusan'           => 'Magister Keperawatan',
            'tahun_lulus'       => 2022,
            'pendidikan_terakhir' => true,
        ], null);

        $this->pegawai->refresh();
        $this->assertEquals('S2', $this->pegawai->pendidikan_terakhir);

        // Hapus S2 -> fallback ke S1
        $pendidikanService->delete($rp2->id);

        $this->pegawai->refresh();
        $this->assertEquals('S1', $this->pegawai->pendidikan_terakhir);
    }

    public function test_pegawai_with_null_relations_renders_views_without_exceptions(): void
    {
        // Pegawai dengan data kosong/null pada relasi
        $pegawaiNull = Pegawai::create([
            'nip'                 => '199505052020121002',
            'nama'                => 'Pegawai Serba Null',
            'jenis_pegawai'       => 'dosen',
            'status_kepegawaian'  => 'pns',
            'status_pegawai'      => 'Aktif',
            'golongan_id'         => null,
            'jabatan_id'          => null,
            'unit_kerja_id'       => null,
            'pendidikan_terakhir' => null,
        ]);

        // 1. Profil Detail Lengkap (show)
        $responseShow = $this->actingAs($this->adminUser)->get(route('pegawai.show', $pegawaiNull->id));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Pegawai Serba Null');

        // 2. Halaman DUK
        $responseDuk = $this->actingAs($this->adminUser)->get(route('duk.index'));
        $responseDuk->assertStatus(200);
        $responseDuk->assertSee('Pegawai Serba Null');

        // 3. Cetak PDF DUK
        $responsePdf = $this->actingAs($this->adminUser)->get(route('reports.duk.pdf'));
        $responsePdf->assertStatus(200);

        // 4. Export Excel DUK
        $responseExcel = $this->actingAs($this->adminUser)->get(route('reports.duk.excel'));
        $responseExcel->assertStatus(200);
    }
}
