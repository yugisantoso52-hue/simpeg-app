<?php

namespace Tests\Feature;

use App\Models\Jabatan;
use App\Models\KelompokPenugasan;
use App\Models\Pegawai;
use App\Models\PengajuanJabatan;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PenugasanAndPengajuanJabatanTest extends TestCase
{
    use RefreshDatabase;

    protected Role $rolePegawai;
    protected Role $roleAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rolePegawai = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);
        $this->roleAdmin = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
    }

    protected function createPegawai(string $nip = '199001012020011001', string $nama = 'Pegawai Uji'): Pegawai
    {
        return Pegawai::create([
            'nip' => $nip,
            'nama' => $nama,
            'jenis_kelamin' => 'L',
            'status_pegawai' => 'Aktif',
            'status_kepegawaian' => 'PNS',
        ]);
    }

    public function test_pegawai_can_save_multiple_penugasan_mandiri(): void
    {
        $pegawai = $this->createPegawai('199001012020011001');
        $user = User::factory()->create([
            'role_id' => $this->rolePegawai->id,
            'pegawai_id' => $pegawai->id,
            'must_change_password' => false,
        ]);

        $kelompok1 = KelompokPenugasan::firstOrCreate(
            ['kode' => 'kjfd_medikal_bedah'],
            ['nama' => 'Medikal Bedah', 'kategori' => 'kjfd', 'urutan' => 1]
        );
        $kelompok2 = KelompokPenugasan::firstOrCreate(
            ['kode' => 'lab_medikal_bedah'],
            ['nama' => 'Medikal Bedah', 'kategori' => 'laboratorium', 'urutan' => 2]
        );

        $response = $this->actingAs($user)->put(route('pegawai.update', $pegawai->id), [
            'nama' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'jenis_kelamin' => $pegawai->jenis_kelamin ?: 'L',
            'penugasan_submitted' => 1,
            'penugasan' => [
                $kelompok1->id => [
                    'kelompok_penugasan_id' => $kelompok1->id,
                    'peran' => 'Koordinator',
                ],
                $kelompok2->id => [
                    'kelompok_penugasan_id' => $kelompok2->id,
                    'peran' => 'Anggota',
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pegawai_penugasan', [
            'pegawai_id' => $pegawai->id,
            'kelompok_penugasan_id' => $kelompok1->id,
            'peran' => 'Koordinator',
        ]);
        $this->assertDatabaseHas('pegawai_penugasan', [
            'pegawai_id' => $pegawai->id,
            'kelompok_penugasan_id' => $kelompok2->id,
            'peran' => 'Anggota',
        ]);
    }

    public function test_non_admin_cannot_directly_change_structural_jabatan_id(): void
    {
        $pegawai = $this->createPegawai('199001012020011002');
        $originalJabatanId = $pegawai->jabatan_id;

        $user = User::factory()->create([
            'role_id' => $this->rolePegawai->id,
            'pegawai_id' => $pegawai->id,
            'must_change_password' => false,
        ]);

        $targetJabatan = Jabatan::create([
            'kode_jabatan' => 'KBU',
            'nama_jabatan' => 'Kepala Bagian Khusus',
            'jenis_jabatan' => 'Struktural'
        ]);

        $this->actingAs($user)->put(route('pegawai.update', $pegawai->id), [
            'nama' => $pegawai->nama,
            'nip' => $pegawai->nip,
            'jenis_kelamin' => $pegawai->jenis_kelamin ?: 'L',
            'jabatan_id' => $targetJabatan->id,
        ]);

        $pegawai->refresh();
        $this->assertEquals($originalJabatanId, $pegawai->jabatan_id);
    }

    public function test_pegawai_can_submit_pengajuan_jabatan_with_sk(): void
    {
        Storage::fake('public');

        $pegawai = $this->createPegawai('199001012020011003');
        $user = User::factory()->create([
            'role_id' => $this->rolePegawai->id,
            'pegawai_id' => $pegawai->id,
            'must_change_password' => false,
        ]);

        $jabatan = Jabatan::create(['kode_jabatan' => 'J1', 'nama_jabatan' => 'Kepala Bagian Umum', 'jenis_jabatan' => 'Struktural']);
        $unitKerja = UnitKerja::create(['kode_unit' => 'U1', 'nama_unit' => 'Bagian Umum']);

        $file = UploadedFile::fake()->create('sk_jabatan.pdf', 500, 'application/pdf');

        $response = $this->actingAs($user)->post(route('pengajuan-jabatan.store'), [
            'jabatan_id' => $jabatan->id,
            'unit_kerja_id' => $unitKerja->id,
            'nomor_sk' => 'SK/UNRI/2026/001',
            'tanggal_sk' => '2026-01-15',
            'tmt_jabatan' => '2026-02-01',
            'file_sk' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengajuan_jabatan', [
            'pegawai_id' => $pegawai->id,
            'jabatan_id' => $jabatan->id,
            'unit_kerja_id' => $unitKerja->id,
            'nomor_sk' => 'SK/UNRI/2026/001',
            'status' => PengajuanJabatan::STATUS_MENUNGGU,
        ]);
    }

    public function test_admin_or_ka_pokja_can_verify_and_approve_pengajuan_jabatan(): void
    {
        Storage::fake('public');

        $pegawai = $this->createPegawai('199001012020011004');
        $admin = User::factory()->create([
            'role_id' => $this->roleAdmin->id,
            'must_change_password' => false,
        ]);

        $jabatan = Jabatan::create(['kode_jabatan' => 'J2', 'nama_jabatan' => 'Kepala Bagian Umum', 'jenis_jabatan' => 'Struktural']);
        $unitKerja = UnitKerja::create(['kode_unit' => 'U2', 'nama_unit' => 'Bagian Umum']);

        $pengajuan = PengajuanJabatan::create([
            'pegawai_id' => $pegawai->id,
            'jabatan_id' => $jabatan->id,
            'unit_kerja_id' => $unitKerja->id,
            'nomor_sk' => 'SK/UNRI/2026/002',
            'tanggal_sk' => '2026-01-15',
            'tmt_jabatan' => '2026-02-01',
            'status' => PengajuanJabatan::STATUS_MENUNGGU,
            'diajukan_oleh' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('pengajuan-jabatan.verifikasi', $pengajuan->id), [
            'aksi' => 'setujui',
            'catatan' => 'SK telah diverifikasi dan valid.',
        ]);

        $response->assertRedirect();
        $pengajuan->refresh();
        $pegawai->refresh();

        $this->assertEquals(PengajuanJabatan::STATUS_DISETUJUI, $pengajuan->status);
        $this->assertEquals($admin->id, $pengajuan->diverifikasi_oleh);
        $this->assertEquals($jabatan->id, $pegawai->jabatan_id);
        $this->assertEquals($unitKerja->id, $pegawai->unit_kerja_id);

        $this->assertDatabaseHas('riwayat_jabatan', [
            'pegawai_id' => $pegawai->id,
            'jabatan_id' => $jabatan->id,
            'nomor_sk' => 'SK/UNRI/2026/002',
            'status' => 'aktif',
        ]);
    }
}
