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

    public function test_pegawai_can_submit_cuti_form_with_attachment_when_cloud_storage_fails(): void
    {
        // Simulasikan kondisi di mana default disk diset ke 'supabase' yang sedang paused/unreachable
        config([
            'filesystems.default' => 'supabase',
            'filesystems.disks.supabase' => [
                'driver'   => 's3',
                'key'      => 'invalid-key',
                'secret'   => 'invalid-secret',
                'region'   => 'ap-northeast-2',
                'bucket'   => 'sikap-files',
                'endpoint' => 'http://127.0.0.1:1', // Unreachable endpoint to trigger connection/write failure
                'throw'    => true,
            ],
        ]);

        $file = UploadedFile::fake()->create('surat_keterangan_dokter.pdf', 250, 'application/pdf');
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(2);

        $createPage = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.create'));
        $createPage->assertStatus(200);
        $createPage->assertSee('Form Permohonan Cuti');

        $response = $this->actingAs($this->pegawaiUser)->post(route('pengajuan-cuti.store'), [
            'jenis_cuti'         => 'Cuti Sakit',
            'tanggal_mulai'      => $start->toDateString(),
            'tanggal_selesai'    => $end->toDateString(),
            'alasan'             => 'Perawatan medis dengan surat keterangan dokter',
            'alamat_selama_cuti' => 'Jl. Diponegoro No. 10 Pekanbaru',
            'nomor_telepon'      => '081234567890',
            'file_lampiran'      => $file,
        ]);

        $response->assertRedirect(route('pengajuan-cuti.index'));
        $response->assertSessionHas('success');

        $cuti = PengajuanCuti::where('pegawai_id', $this->pegawai->id)
            ->where('jenis_cuti', 'Cuti Sakit')
            ->latest('id')
            ->first();

        $this->assertNotNull($cuti);
        $this->assertNotNull($cuti->file_lampiran);
        Storage::disk('local')->assertExists($cuti->file_lampiran);

        // Pastikan halaman index dan detail show juga dapat diakses tanpa 500 error
        $indexResponse = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.index'));
        $indexResponse->assertStatus(200);

        $showResponse = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.show', $cuti->id));
        $showResponse->assertStatus(200);
    }

    public function test_pegawai_with_unlinked_pegawai_id_auto_resolves_and_submits_cuti_without_error(): void
    {
        $unlinkedPegawai = Pegawai::create([
            'nip'                 => '199502022022031005',
            'nama'                => 'Ns. Andi Pratama, S.Kep',
            'email'               => 'andi.pratama@unri.ac.id',
            'jenis_pegawai'       => 'Tendik',
            'pendidikan_terakhir' => 'S1',
            'status_pegawai'      => 'Aktif',
        ]);

        $pegawaiRole = Role::where('name', 'pegawai')->first();
        $unlinkedUser = User::factory()->create([
            'name'                 => '199502022022031005',
            'email'                => 'andi.pratama@unri.ac.id',
            'role_id'              => $pegawaiRole->id,
            'pegawai_id'           => null,
            'must_change_password' => false,
        ]);

        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(1);

        $response = $this->actingAs($unlinkedUser)->post(route('pengajuan-cuti.store'), [
            'jenis_cuti'         => 'Cuti Tahunan',
            'tanggal_mulai'      => $start->toDateString(),
            'tanggal_selesai'    => $end->toDateString(),
            'alasan'             => 'Keperluan keluarga di luar kota',
            'alamat_selama_cuti' => 'Pekanbaru',
            'nomor_telepon'      => '081298765432',
        ]);

        $response->assertRedirect(route('pengajuan-cuti.index'));
        $this->assertEquals($unlinkedPegawai->id, $unlinkedUser->fresh()->pegawai_id);
        $this->assertDatabaseHas('pengajuan_cuti', [
            'pegawai_id' => $unlinkedPegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'status'     => 'Menunggu Persetujuan',
        ]);
    }

    public function test_formulir_cuti_tendik_pertimbangan_atasan_is_kabag_umum_and_paraf_koordinasi_only_kapokja(): void
    {
        $jabatanKabag = \App\Models\Jabatan::create([
            'nama_jabatan'     => 'Kepala Bagian Umum',
            'kode_jabatan'     => 'JAB-KBU-TEST',
            'kelompok_jabatan' => 'Tenaga Kependidikan',
        ]);
        $jabatanPokja = \App\Models\Jabatan::create([
            'nama_jabatan'     => 'Ketua Pokja Keuangan dan Kepegawaian',
            'kode_jabatan'     => 'JAB-PKK-TEST',
            'kelompok_jabatan' => 'Tenaga Kependidikan',
        ]);
        $jabatanTendik = \App\Models\Jabatan::create([
            'nama_jabatan'     => 'Penata Layanan Operasional',
            'kode_jabatan'     => 'JAB-PLO-TEST',
            'kelompok_jabatan' => 'Tenaga Kependidikan',
        ]);

        $kabagPegawai = Pegawai::create([
            'nip'                 => '197507122005011002',
            'nama'                => 'Bakhtiar',
            'gelar_belakang'      => 'S.Sos., M.Si',
            'jenis_pegawai'       => 'Tendik',
            'pendidikan_terakhir' => 'S2',
            'status_pegawai'      => 'Aktif',
            'jabatan_id'          => $jabatanKabag->id,
        ]);

        $pokjaPegawai = Pegawai::create([
            'nip'                 => '197605212014092001',
            'nama'                => 'Dolli Vita Zenitha Harning Arivina',
            'gelar_belakang'      => 'SE',
            'jenis_pegawai'       => 'Tendik',
            'pendidikan_terakhir' => 'S1',
            'status_pegawai'      => 'Aktif',
            'jabatan_id'          => $jabatanPokja->id,
        ]);

        $tendikPegawai = Pegawai::create([
            'nip'                 => '198006152025211060',
            'nama'                => 'Rahmad Hidayat',
            'gelar_belakang'      => 'ST',
            'jenis_pegawai'       => 'Tendik',
            'pendidikan_terakhir' => 'S1',
            'status_pegawai'      => 'Aktif',
            'jabatan_id'          => $jabatanTendik->id,
            'atasan_cuti_id'      => $pokjaPegawai->id, // Bahkan jika terisi Ka Pokja, harus diabaikan & diarahkan ke Kabag Umum
        ]);

        $pegawaiRole = Role::where('name', 'pegawai')->first();
        $pokjaUser = User::factory()->create([
            'role_id'              => $pegawaiRole->id,
            'pegawai_id'           => $pokjaPegawai->id,
            'must_change_password' => false,
        ]);
        $kabagUser = User::factory()->create([
            'role_id'              => $pegawaiRole->id,
            'pegawai_id'           => $kabagPegawai->id,
            'must_change_password' => false,
        ]);

        $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
        $this->assertFalse($hierarchyService->isAtasanLangsungCuti($pokjaPegawai, $tendikPegawai));
        $this->assertTrue($hierarchyService->isAtasanLangsungCuti($kabagPegawai, $tendikPegawai));

        $cuti = PengajuanCuti::create([
            'pegawai_id'              => $tendikPegawai->id,
            'jenis_cuti'              => 'Cuti Tahunan',
            'tanggal_mulai'           => '2026-10-26',
            'tanggal_selesai'         => '2026-10-27',
            'jumlah_hari'             => 2,
            'alasan'                  => 'Keluar kota',
            'status'                  => 'Disetujui',
            'pertimbangan_atasan'     => 'Disetujui',
            'pertimbangan_atasan_at'  => now(),
            'atasan_langsung_id'      => $pokjaUser->id, // Simulasi data lama yang salah menunjuk Ka Pokja
            'verifikasi_admin_at'     => now(),
            'paraf_kapokja_at'        => now(),
            'paraf_kabag_at'          => now(),
        ]);

        $renderedHtml = view('exports.pdf.formulir_cuti', [
            'cuti'                => $cuti->fresh(['pegawai.jabatan', 'atasanLangsung.pegawai.jabatan']),
            'verifyUrl'           => 'https://sikap.fkp.unri.ac.id/verifikasi-dokumen/test',
            'verifyCode'          => 'test',
            'qrCodeDataUri'       => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            'nomorSuratFormatted' => '1235/UN19.5.1.1.10/KP/2026',
            'pejabatKabag'        => $kabagPegawai,
            'pejabatKaPokja'      => $pokjaPegawai,
            'sudahParafPokja'     => true,
            'sudahParafKabag'     => true,
            'parafPokjaAt'        => '26/10/2026 11:17 WIB',
            'parafKabagAt'        => '26/10/2026 11:30 WIB',
            'qrPokjaUri'          => null,
            'qrKabagUri'          => null,
        ])->render();

        // Pastikan di bagian VI. Pertimbangan Atasan Langsung tercetak Bakhtiar, S.Sos., M.Si (197507122005011002)
        $this->assertStringContainsString('Bakhtiar, S.Sos., M.Si', $renderedHtml);
        $this->assertStringContainsString('197507122005011002', $renderedHtml);
        // Pastikan di bagian Paraf Koordinasi Tata Naskah tidak ada "2. Kabag Umum:"
        $this->assertStringNotContainsString('2. Kabag Umum:', $renderedHtml);
        $this->assertStringContainsString('Ka Pokja Keu-Kepeg:', $renderedHtml);
    }
}
