<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengajuanCutiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected User $pegawaiUser;
    protected User $otherPegawaiUser;
    protected Pegawai $pegawai;
    protected Pegawai $otherPegawai;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->pegawai = Pegawai::create([
            'nip'                 => '199001012019011001',
            'nama'                => 'Ns. Budi Santoso, M.Kep',
            'jenis_pegawai'       => 'Dosen',
            'pendidikan_terakhir' => 'S2',
            'status_pegawai'      => 'Aktif',
        ]);

        $this->otherPegawai = Pegawai::create([
            'nip'                 => '199305052020012002',
            'nama'                => 'Rina Rahayu, S.Kep',
            'jenis_pegawai'       => 'PNS',
            'pendidikan_terakhir' => 'S1',
            'status_pegawai'      => 'Aktif',
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

        $this->otherPegawaiUser = User::factory()->create([
            'role_id'              => $pegawaiRole->id,
            'pegawai_id'           => $this->otherPegawai->id,
            'must_change_password' => false,
        ]);
    }

    public function test_pegawai_can_view_cuti_index_and_see_remaining_annual_leave_quota(): void
    {
        $response = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.index'));
        $response->assertStatus(200);
        $response->assertSee('Sisa Kuota Cuti Tahunan');
        $response->assertSee('12'); // default 12 days
    }

    public function test_pegawai_can_submit_valid_cuti_request_and_upload_attachment(): void
    {
        $file = UploadedFile::fake()->create('surat_dokter.pdf', 300, 'application/pdf');

        // Choose next Monday to Wednesday (3 business days)
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(2); // Wednesday

        $payload = [
            'jenis_cuti'         => 'Cuti Sakit',
            'tanggal_mulai'      => $start->toDateString(),
            'tanggal_selesai'    => $end->toDateString(),
            'alasan'             => 'Menjalani perawatan medis rawat jalan',
            'alamat_selama_cuti' => 'Jl. Garuda No. 45 Pekanbaru',
            'nomor_telepon'      => '08123456789',
            'file_lampiran'      => $file,
        ];

        $response = $this->actingAs($this->pegawaiUser)->post(route('pengajuan-cuti.store'), $payload);
        $response->assertRedirect(route('pengajuan-cuti.index'));

        $this->assertDatabaseHas('pengajuan_cuti', [
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'Cuti Sakit',
            'status'     => 'Menunggu Persetujuan',
        ]);

        $cuti = PengajuanCuti::where('pegawai_id', $this->pegawai->id)->first();
        $this->assertEquals(3, $cuti->jumlah_hari);
        $this->assertNotNull($cuti->file_lampiran);
        Storage::disk('local')->assertExists($cuti->file_lampiran);
    }

    public function test_cuti_tahunan_validates_remaining_quota(): void
    {
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(20); // More than 12 days

        $payload = [
            'jenis_cuti'         => 'Cuti Tahunan',
            'tanggal_mulai'      => $start->toDateString(),
            'tanggal_selesai'    => $end->toDateString(),
            'alasan'             => 'Liburan panjang bersama keluarga',
            'alamat_selama_cuti' => 'Padang',
        ];

        $response = $this->actingAs($this->pegawaiUser)->post(route('pengajuan-cuti.store'), $payload);
        $response->assertSessionHasErrors('tanggal_selesai');
    }

    public function test_pimpinan_and_admin_can_approve_cuti_and_it_deducts_quota(): void
    {
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(3); // 4 days

        $cuti = PengajuanCuti::create([
            'pegawai_id'      => $this->pegawai->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => $start->toDateString(),
            'tanggal_selesai' => $end->toDateString(),
            'jumlah_hari'     => 4,
            'alasan'          => 'Urusan keluarga di luar kota',
            'status'          => 'Menunggu Persetujuan',
        ]);

        $approvePayload = [
            'status'           => 'Disetujui',
            'nomor_surat'      => '123/UN19.5.1/KP/2026',
            'catatan_pimpinan' => 'Disetujui sesuai permohonan.',
        ];

        $response = $this->actingAs($this->pimpinanUser)->post(route('pengajuan-cuti.approve', $cuti->id), $approvePayload);
        $response->assertRedirect(route('pengajuan-cuti.show', $cuti->id));

        $this->assertDatabaseHas('pengajuan_cuti', [
            'id'          => $cuti->id,
            'status'      => 'Disetujui',
            'nomor_surat' => '123/UN19.5.1/KP/2026',
            'approved_by' => $this->pimpinanUser->id,
        ]);

        // Kuota tahunan berkurang dari 12 menjadi 8 (12 - 4)
        $this->pegawai->refresh();
        $this->assertEquals(8, $this->pegawai->sisa_cuti_tahunan);
    }

    public function test_pimpinan_and_admin_can_reject_cuti_with_reason(): void
    {
        $cuti = PengajuanCuti::create([
            'pegawai_id'      => $this->pegawai->id,
            'jenis_cuti'      => 'Cuti Alasan Penting',
            'tanggal_mulai'   => '2026-09-01',
            'tanggal_selesai' => '2026-09-05',
            'jumlah_hari'     => 5,
            'alasan'          => 'Ada acara',
            'status'          => 'Menunggu Persetujuan',
        ]);

        $rejectPayload = [
            'status'           => 'Ditolak',
            'catatan_pimpinan' => 'Sedang berlangsung akreditasi prodi, permohonan ditunda.',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('pengajuan-cuti.approve', $cuti->id), $rejectPayload);
        $response->assertRedirect(route('pengajuan-cuti.show', $cuti->id));

        $this->assertDatabaseHas('pengajuan_cuti', [
            'id'               => $cuti->id,
            'status'           => 'Ditolak',
            'catatan_pimpinan' => 'Sedang berlangsung akreditasi prodi, permohonan ditunda.',
        ]);
    }

    public function test_pegawai_can_cancel_own_pending_cuti(): void
    {
        $cuti = PengajuanCuti::create([
            'pegawai_id'      => $this->pegawai->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => '2026-10-01',
            'tanggal_selesai' => '2026-10-03',
            'jumlah_hari'     => 3,
            'alasan'          => 'Rencana liburan',
            'status'          => 'Menunggu Persetujuan',
        ]);

        $response = $this->actingAs($this->pegawaiUser)->post(route('pengajuan-cuti.cancel', $cuti->id));
        $response->assertRedirect(route('pengajuan-cuti.index'));

        $this->assertDatabaseHas('pengajuan_cuti', [
            'id'     => $cuti->id,
            'status' => 'Dibatalkan',
        ]);
    }

    public function test_pegawai_cannot_view_other_pegawai_cuti(): void
    {
        $otherCuti = PengajuanCuti::create([
            'pegawai_id'      => $this->otherPegawai->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => '2026-11-01',
            'tanggal_selesai' => '2026-11-02',
            'jumlah_hari'     => 2,
            'alasan'          => 'Pribadi',
            'status'          => 'Menunggu Persetujuan',
        ]);

        $response = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.show', $otherCuti->id));
        $response->assertStatus(403);
    }

    public function test_pdf_formulir_cuti_bkn_renders_without_errors(): void
    {
        $cuti = PengajuanCuti::create([
            'pegawai_id'      => $this->pegawai->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => '2026-12-01',
            'tanggal_selesai' => '2026-12-05',
            'jumlah_hari'     => 5,
            'alasan'          => 'Cuti akhir tahun bersama keluarga',
            'status'          => 'Disetujui',
        ]);

        $pdfResponse = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.cetak-pdf', $cuti->id));
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }
}
