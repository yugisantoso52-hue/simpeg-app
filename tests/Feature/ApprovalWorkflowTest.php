<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $pemohonUser;

    protected User $atasanUser;

    protected User $pybmcUser;

    protected User $adminUser;

    protected User $unauthorizedUser;

    protected Pegawai $pemohonPegawai;

    protected Pegawai $atasanPegawai;

    protected Pegawai $pybmcPegawai;

    protected Pegawai $unauthorizedPegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        // 1. Pegawai Pemohon Cuti
        $this->pemohonPegawai = Pegawai::create([
            'nip' => '199501012022011001',
            'nama' => 'Dina Pemohon',
            'jenis_pegawai' => 'PNS',
            'pendidikan_terakhir' => 'S1',
            'status_pegawai' => 'Aktif',
        ]);
        $this->pemohonUser = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->pemohonPegawai->id,
            'must_change_password' => false,
        ]);

        // 2. Atasan Langsung (misal Ketua Bagian/Koorprodi)
        $this->atasanPegawai = Pegawai::create([
            'nip' => '198802022015021002',
            'nama' => 'Dr. Hendra Atasan, M.Kes',
            'jenis_pegawai' => 'Dosen',
            'pendidikan_terakhir' => 'S3',
            'status_pegawai' => 'Aktif',
        ]);
        $this->atasanUser = User::factory()->create([
            'role_id' => $pimpinanRole->id,
            'pegawai_id' => $this->atasanPegawai->id,
            'must_change_password' => false,
        ]);

        // Set relasi atasan pada pegawai pemohon
        $this->pemohonPegawai->update(['atasan_id' => $this->atasanPegawai->id]);

        // 3. PYBMC (Dekan / Pimpinan Utama)
        $this->pybmcPegawai = Pegawai::create([
            'nip' => '197503032000031003',
            'nama' => 'Prof. Dr. Irwan Dekan, M.Kep',
            'jenis_pegawai' => 'Dosen',
            'pendidikan_terakhir' => 'S3',
            'status_pegawai' => 'Aktif',
        ]);
        $this->pybmcUser = User::factory()->create([
            'role_id' => $pimpinanRole->id,
            'pegawai_id' => $this->pybmcPegawai->id,
            'must_change_password' => false,
        ]);

        // 4. Admin
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        // 5. Pegawai Lain (Tanpa hak hierarki)
        $this->unauthorizedPegawai = Pegawai::create([
            'nip' => '199909092024011009',
            'nama' => 'Rian Non Atasan',
            'jenis_pegawai' => 'Tendik',
            'status_pegawai' => 'Aktif',
        ]);
        $this->unauthorizedUser = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->unauthorizedPegawai->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Test alur lengkap persetujuan berjenjang cuti (Tahap 1 Atasan -> Tahap 2 PYBMC)
     */
    public function test_cuti_full_approval_lifecycle_transitions(): void
    {
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(2);

        // Tahap 0: Pembuatan pengajuan cuti
        $cuti = PengajuanCuti::create([
            'pegawai_id' => $this->pemohonPegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => $start->toDateString(),
            'tanggal_selesai' => $end->toDateString(),
            'jumlah_hari' => 3,
            'alasan' => 'Keperluan keluarga',
            'status' => 'Menunggu Persetujuan',
            'atasan_langsung_id' => $this->atasanUser->id,
            'pybmc_id' => $this->pybmcUser->id,
        ]);

        $this->assertEquals('Menunggu Persetujuan', $cuti->status);

        // Tahap 1: Pertimbangan Atasan Langsung
        $responseAtasan = $this->actingAs($this->atasanUser)
            ->post(route('pengajuan-cuti.approve', $cuti->id), [
                'status' => 'Disetujui',
                'catatan_atasan_langsung' => 'Disetujui oleh Atasan Langsung',
            ]);

        $responseAtasan->assertRedirect(route('pengajuan-cuti.show', $cuti->id));

        $cuti->refresh();
        $this->assertEquals('Disetujui Atasan (Menunggu PYBMC)', $cuti->status);
        $this->assertEquals('Disetujui', $cuti->pertimbangan_atasan);
        $this->assertEquals('Disetujui oleh Atasan Langsung', $cuti->catatan_atasan_langsung);
        $this->assertNull($cuti->approved_by); // Belum final

        // Tahap 2: Keputusan Akhir PYBMC (Dekan)
        $responsePybmc = $this->actingAs($this->pybmcUser)
            ->post(route('pengajuan-cuti.approve', $cuti->id), [
                'status' => 'Disetujui',
                'catatan_pimpinan' => 'Disetujui PYBMC secara definitif',
            ]);

        $responsePybmc->assertRedirect(route('pengajuan-cuti.show', $cuti->id));

        $cuti->refresh();
        $this->assertEquals('Disetujui', $cuti->status);
        $this->assertEquals($this->pybmcUser->id, $cuti->approved_by);
        $this->assertNotNull($cuti->approved_at);
        $this->assertEquals('Disetujui PYBMC secara definitif', $cuti->catatan_pimpinan);
    }

    /**
     * Test penolakan oleh Atasan Langsung pada Tahap 1 langsung menghentikan workflow (Ditolak)
     */
    public function test_cuti_rejection_by_atasan_directly_terminates_workflow(): void
    {
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(2);

        $cuti = PengajuanCuti::create([
            'pegawai_id' => $this->pemohonPegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => $start->toDateString(),
            'tanggal_selesai' => $end->toDateString(),
            'jumlah_hari' => 3,
            'alasan' => 'Liburan',
            'status' => 'Menunggu Persetujuan',
            'atasan_langsung_id' => $this->atasanUser->id,
            'pybmc_id' => $this->pybmcUser->id,
        ]);

        $response = $this->actingAs($this->atasanUser)
            ->post(route('pengajuan-cuti.approve', $cuti->id), [
                'status' => 'Ditolak',
                'catatan_atasan_langsung' => 'Kegiatan akademik mendesak tidak dapat ditinggalkan',
            ]);

        $response->assertRedirect(route('pengajuan-cuti.show', $cuti->id));

        $cuti->refresh();
        $this->assertEquals('Ditolak', $cuti->status);
        $this->assertEquals('Ditolak', $cuti->pertimbangan_atasan);
        $this->assertEquals($this->atasanUser->id, $cuti->approved_by);
    }

    /**
     * Test pegawai lain tanpa wewenang hierarki dilarang menyetujui cuti
     */
    public function test_unauthorized_user_cannot_approve_cuti(): void
    {
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(1);

        $cuti = PengajuanCuti::create([
            'pegawai_id' => $this->pemohonPegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => $start->toDateString(),
            'tanggal_selesai' => $end->toDateString(),
            'jumlah_hari' => 2,
            'alasan' => 'Istirahat',
            'status' => 'Menunggu Persetujuan',
            'atasan_langsung_id' => $this->atasanUser->id,
            'pybmc_id' => $this->pybmcUser->id,
        ]);

        // Unauthorized user bukan atasan dan bukan admin -> ditolak oleh FormRequest / Controller
        $response = $this->actingAs($this->unauthorizedUser)
            ->post(route('pengajuan-cuti.approve', $cuti->id), [
                'status' => 'Disetujui',
            ]);

        // FormRequest authorize() mengembalikan false => 403 Forbidden
        $response->assertStatus(403);

        $cuti->refresh();
        $this->assertEquals('Menunggu Persetujuan', $cuti->status);
    }
}
