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

        $response = $this->actingAs($this->pimpinanUser)->post(route('pengajuan-cuti.approve', $cuti->id), $rejectPayload);
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

        // Pastikan di bagian V. CATATAN CUTI TAHUNAN tercetak lengkap 3 tahun: N (2026), N-1 (2025), dan N-2 (2024)
        $this->assertStringContainsString('2026 (N)', $renderedHtml);
        $this->assertStringContainsString('Hak Cuti Tahun Berjalan (N)', $renderedHtml);
        $this->assertStringContainsString('2025 (N-1)', $renderedHtml);
        $this->assertStringContainsString('Sisa Cuti 1 Tahun Sebelumnya (N-1)', $renderedHtml);
        $this->assertStringContainsString('2024 (N-2)', $renderedHtml);
        $this->assertStringContainsString('Sisa Cuti 2 Tahun Lalu (N-2)', $renderedHtml);
    }

    public function test_strict_4_stage_cuti_workflow_enforces_each_role_authority_only(): void
    {
        $jabatanPokja = \App\Models\Jabatan::create([
            'nama_jabatan'     => 'Ketua Pokja Keuangan dan Kepegawaian',
            'kode_jabatan'     => 'JAB-PKK-WF',
            'kelompok_jabatan' => 'Tenaga Kependidikan',
        ]);
        $jabatanKabag = \App\Models\Jabatan::create([
            'nama_jabatan'     => 'Kepala Bagian Umum',
            'kode_jabatan'     => 'JAB-KBU-WF',
            'kelompok_jabatan' => 'Tenaga Kependidikan',
        ]);
        $jabatanWd2 = \App\Models\Jabatan::create([
            'nama_jabatan'     => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
            'kode_jabatan'     => 'JAB-WD2-WF',
            'kelompok_jabatan' => 'Dosen',
        ]);
        $jabatanTendik = \App\Models\Jabatan::create([
            'nama_jabatan'     => 'Penata Layanan Operasional',
            'kode_jabatan'     => 'JAB-PLO-WF',
            'kelompok_jabatan' => 'Tenaga Kependidikan',
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
        $kabagPegawai = Pegawai::create([
            'nip'                 => '197507122005011002',
            'nama'                => 'Bakhtiar',
            'gelar_belakang'      => 'S.Sos., M.Si',
            'jenis_pegawai'       => 'Tendik',
            'pendidikan_terakhir' => 'S2',
            'status_pegawai'      => 'Aktif',
            'jabatan_id'          => $jabatanKabag->id,
        ]);
        $wd2Pegawai = Pegawai::create([
            'nip'                 => '198509092014041001',
            'nama'                => 'Safri',
            'gelar_depan'         => 'Dr.',
            'gelar_belakang'      => 'M.Kep, Sp.Kep.M.B',
            'jenis_pegawai'       => 'Dosen',
            'pendidikan_terakhir' => 'S3',
            'status_pegawai'      => 'Aktif',
            'jabatan_id'          => $jabatanWd2->id,
        ]);
        $pemohonTendik = Pegawai::create([
            'nip'                 => '199104102022031001',
            'nama'                => 'Dedi Kurniawan',
            'jenis_pegawai'       => 'Tendik',
            'pendidikan_terakhir' => 'S1',
            'status_pegawai'      => 'Aktif',
            'jabatan_id'          => $jabatanTendik->id,
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $pimpinanRole = Role::where('name', 'pimpinan')->first();

        // Bahkan jika akun Ka Pokja di DB pernah diset role 'admin', isScreeningAdmin() harus false
        $pokjaUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'pegawai_id'           => $pokjaPegawai->id,
            'must_change_password' => false,
        ]);
        $kabagUser = User::factory()->create([
            'role_id'              => $pimpinanRole->id,
            'pegawai_id'           => $kabagPegawai->id,
            'must_change_password' => false,
        ]);
        $wd2User = User::factory()->create([
            'role_id'              => $pimpinanRole->id,
            'pegawai_id'           => $wd2Pegawai->id,
            'must_change_password' => false,
        ]);

        $cuti = PengajuanCuti::create([
            'pegawai_id'      => $pemohonTendik->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => '2026-11-10',
            'tanggal_selesai' => '2026-11-11',
            'jumlah_hari'     => 2,
            'alasan'          => 'Keperluan keluarga',
            'status'          => 'Menunggu Persetujuan',
        ]);

        // 1. Sebelum Tahap 1 (Admin Screening), Ka Pokja TIDAK BISA melakukan Screening Admin maupun Paraf Pokja
        $this->actingAs($pokjaUser)->post(route('pengajuan-cuti.verifikasi', $cuti->id), [
            'tahap' => 'verifikasi_admin',
        ])->assertSessionHas('error');
        $this->assertNull($cuti->fresh()->verifikasi_admin_at);

        // 2. Admin melakukan Tahap 1 (Admin Screening) -> Berhasil
        $this->actingAs($this->adminUser)->post(route('pengajuan-cuti.verifikasi', $cuti->id), [
            'tahap'             => 'verifikasi_admin',
            'nomor_surat_input' => '456',
        ])->assertSessionHas('success');
        $this->assertNotNull($cuti->fresh()->verifikasi_admin_at);

        // 3. Setelah Admin klik Screening, Admin TIDAK BISA klik Paraf Ka Pokja maupun Paraf Kabag!
        $adminShowHtml = $this->actingAs($this->adminUser)->get(route('pengajuan-cuti.show', $cuti->id))->getContent();
        $this->assertStringNotContainsString('Periksa &amp; Paraf Ka Pokja Keu-Kepeg', $adminShowHtml);
        $this->assertStringContainsString('Menunggu Paraf Ka Pokja Keu-Kepeg', $adminShowHtml);

        $this->actingAs($this->adminUser)->post(route('pengajuan-cuti.verifikasi', $cuti->id), [
            'tahap' => 'paraf_kapokja',
        ])->assertSessionHas('error');
        $this->assertNull($cuti->fresh()->paraf_kapokja_at);

        // 4. Kabag Umum TIDAK BISA memberikan Pertimbangan sebelum Ka Pokja memberikan paraf
        $this->actingAs($kabagUser)->post(route('pengajuan-cuti.approve', $cuti->id), [
            'status'                  => 'Disetujui',
            'catatan_atasan_langsung' => 'Coba bypass pokja',
        ])->assertSessionHasErrors('error');

        // 5. Ka Pokja melakukan Tahap 2 (Paraf Ka Pokja Keu-Kepeg) -> Berhasil
        $this->actingAs($pokjaUser)->post(route('pengajuan-cuti.verifikasi', $cuti->id), [
            'tahap' => 'paraf_kapokja',
        ])->assertSessionHas('success');
        $this->assertNotNull($cuti->fresh()->paraf_kapokja_at);

        // 6. Setelah Ka Pokja klik Paraf Pokja, Ka Pokja TIDAK BISA melakukan Paraf Kabag maupun Pertimbangan Atasan!
        $this->actingAs($pokjaUser)->post(route('pengajuan-cuti.verifikasi', $cuti->id), [
            'tahap' => 'paraf_kabag',
        ])->assertSessionHas('error');
        $this->assertNull($cuti->fresh()->paraf_kabag_at);

        $this->actingAs($pokjaUser)->post(route('pengajuan-cuti.approve', $cuti->id), [
            'status'                  => 'Disetujui',
            'catatan_atasan_langsung' => 'Ka Pokja coba approve atasan',
        ])->assertStatus(403);

        // 7. Kabag Umum melakukan Tahap 3 (Pertimbangan & TTE Atasan Langsung) -> Berhasil
        $this->actingAs($kabagUser)->post(route('pengajuan-cuti.approve', $cuti->id), [
            'status'                  => 'Disetujui',
            'catatan_atasan_langsung' => 'Disetujui oleh Kepala Bagian Umum.',
        ])->assertSessionHas('success');
        $this->assertEquals('Disetujui', $cuti->fresh()->pertimbangan_atasan);
        $this->assertNotNull($cuti->fresh()->paraf_kabag_at);

        // 8. Kabag Umum TIDAK BISA melakukan Tahap 4 (Keputusan Akhir PYBMC)
        $this->actingAs($kabagUser)->post(route('pengajuan-cuti.approve', $cuti->id), [
            'status'           => 'Disetujui',
            'catatan_pimpinan' => 'Kabag coba ambil alih PYBMC',
        ])->assertSessionHasErrors('error');
        $this->assertNotEquals('Disetujui', $cuti->fresh()->status);

        // 9. Wakil Dekan II (PYBMC) melakukan Tahap 4 (Keputusan Akhir & TTE PYBMC) -> Berhasil
        $this->actingAs($wd2User)->post(route('pengajuan-cuti.approve', $cuti->id), [
            'status'           => 'Disetujui',
            'catatan_pimpinan' => 'Disetujui oleh Wakil Dekan II.',
        ])->assertSessionHas('success');
        $this->assertEquals('Disetujui', $cuti->fresh()->status);
    }

    public function test_catatan_cuti_tahunan_3_years_n_n1_n2_displayed_in_create_show_index_and_pdf(): void
    {
        $tahunN  = (int) now()->year;
        $tahunN1 = $tahunN - 1;
        $tahunN2 = $tahunN - 2;

        $cuti = PengajuanCuti::create([
            'pegawai_id'      => $this->pegawai->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => "{$tahunN}-10-12",
            'tanggal_selesai' => "{$tahunN}-10-16",
            'jumlah_hari'     => 5,
            'alasan'          => 'Urusan keluarga',
            'status'          => 'Disetujui',
        ]);

        // 1. Halaman Index E-Cuti Pegawai
        $indexResponse = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee("{$tahunN} (N)");
        $indexResponse->assertSee("{$tahunN1} (N-1)");
        $indexResponse->assertSee("{$tahunN2} (N-2)");

        // 2. Halaman Form Permohonan Cuti (Create)
        $createResponse = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.create'));
        $createResponse->assertStatus(200);
        $createResponse->assertSee('V. Catatan Cuti Tahunan (Peraturan BKN Nomor 7 Tahun 2022)');
        $createResponse->assertSee("{$tahunN} (N)");
        $createResponse->assertSee('7 Hari');
        $createResponse->assertSee('Hak Cuti Tahun Berjalan (N)');
        $createResponse->assertSee("{$tahunN1} (N-1)");
        $createResponse->assertSee('Sisa Cuti 1 Tahun Sebelumnya (N-1)');
        $createResponse->assertSee("{$tahunN2} (N-2)");
        $createResponse->assertSee('Sisa Cuti 2 Tahun Lalu (N-2)');

        // 3. Halaman Detail Permohonan Cuti (Show)
        $showResponse = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.show', $cuti->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('V. Catatan Cuti Tahunan (Peraturan BKN Nomor 7 Tahun 2022)');
        $showResponse->assertSee("{$tahunN} (N)");
        $showResponse->assertSee("{$tahunN1} (N-1)");
        $showResponse->assertSee("{$tahunN2} (N-2)");
    }

    public function test_admin_can_manage_kuota_rekap_and_pegawai_can_view_buku_cuti(): void
    {
        $tahunN  = (int) now()->year;
        $tahunN1 = $tahunN - 1;

        // Admin simpan saldo kuota N-1 untuk pegawai
        $responseSimpan = $this->actingAs($this->adminUser)->post(route('pengajuan-cuti.rekap-kuota.simpan'), [
            'pegawai_id'          => $this->pegawai->id,
            'tahun'               => $tahunN1,
            'kuota_awal'          => 12,
            'kuota_terpakai'      => 4,
            'catatan_penyesuaian' => 'Sisa 8 hari kerja sesuai arsip fisik',
        ]);
        $responseSimpan->assertSessionHas('success');

        $this->assertDatabaseHas('kuota_cuti_pegawai', [
            'pegawai_id'     => $this->pegawai->id,
            'tahun'          => $tahunN1,
            'kuota_awal'     => 12,
            'kuota_terpakai' => 4,
            'sisa_kuota'     => 8,
        ]);

        // Admin buka halaman rekap kuota
        $rekapResponse = $this->actingAs($this->adminUser)->get(route('pengajuan-cuti.rekap-kuota', ['tahun' => $tahunN]));
        $rekapResponse->assertStatus(200);
        $rekapResponse->assertSee('Rekapitulasi Kuota Cuti Pegawai');

        // Pegawai buka halaman Buku Cuti Saya
        $bukuCutiResponse = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.buku-cuti', ['tahun' => $tahunN]));
        $bukuCutiResponse->assertStatus(200);
        $bukuCutiResponse->assertSee('Buku Kendali Cuti Pegawai');
        $bukuCutiResponse->assertSee('8 Hari'); // Sisa kuota N-1 yang diinput admin

        // Pimpinan buka halaman Monitoring Eksekutif Cuti
        $pimpinanResponse = $this->actingAs($this->pimpinanUser)->get(route('pengajuan-cuti.monitoring-pimpinan', ['tahun' => $tahunN]));
        $pimpinanResponse->assertStatus(200);
        $pimpinanResponse->assertSee('Monitoring Eksekutif E-Cuti');

        // Test export PDF Rekap Kuota
        $exportPdfResponse = $this->actingAs($this->adminUser)->get(route('pengajuan-cuti.rekap-kuota.export-pdf', ['tahun' => $tahunN]));
        $exportPdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $exportPdfResponse->headers->get('Content-Type'));

        // Test export PDF Buku Cuti
        $exportBukuPdfResponse = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.buku-cuti.pdf', ['tahun' => $tahunN]));
        $exportBukuPdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $exportBukuPdfResponse->headers->get('Content-Type'));
    }

    public function test_tendik_official_bkn_records_and_rekap_columns(): void
    {
        // Pastikan halaman rekap kuota admin menampilkan rujukan Peraturan BKN Nomor 7 Tahun 2022
        $response = $this->actingAs($this->adminUser)->get(route('pengajuan-cuti.rekap-kuota', ['tahun' => 2026, 'jenis_pegawai' => 'Tendik']));
        $response->assertStatus(200);
        $response->assertSee('Peraturan BKN Nomor 7 Tahun 2022');
        $response->assertSee('Pemisahan Hak PNS');

        // Periksa data pegawai jika data seeding ada di DB lokal
        $dolli = Pegawai::where('nip', '197605212014092001')->first();
        if ($dolli) {
            $rDolli = $dolli->getCutiRekapBkn(2026);
            $this->assertEquals(6, $rDolli['n1']);
            $this->assertEquals(12, $rDolli['n_terpakai']);
            $this->assertEquals(0, $rDolli['sisa']);
            $this->assertStringContainsString('4 Mei 2026', $rDolli['tgl_pengajuan']);
        }

        $rahayu = Pegawai::where('nip', '199101272025212049')->first();
        if ($rahayu) {
            $rRahayu = $rahayu->getCutiRekapBkn(2026);
            $this->assertEquals(7, $rRahayu['n_terpakai']);
            $this->assertEquals(5, $rRahayu['sisa']);
            $this->assertEquals('3', $rRahayu['cuti_melahirkan']);
            $this->assertStringContainsString('23 Des 2025', $rRahayu['tgl_pengajuan']);
            $this->assertStringContainsString('2 Nov 2026', $rRahayu['tgl_pengajuan']);
        }
    }

    public function test_pemisahan_hak_cuti_pns_dan_pppk_serta_ringkasan_regulasi(): void
    {
        // 1. Buat pegawai PPPK
        $pegawaiPppk = Pegawai::create([
            'nip'            => '199209072025211099',
            'nama'           => 'Pegawai Uji PPPK',
            'jenis_pegawai'  => 'PPPK',
            'status_asn'     => 'PPPK',
            'status_pegawai' => 'Aktif',
        ]);

        $this->assertTrue($pegawaiPppk->isPppk());
        $this->assertFalse($pegawaiPppk->isPns());
        $this->assertEquals('PPPK', $pegawaiPppk->status_asn_cuti);
        $this->assertTrue($pegawaiPppk->isEntitledToLeaveType('Cuti Tahunan'));
        $this->assertTrue($pegawaiPppk->isEntitledToLeaveType('Cuti Sakit'));
        $this->assertTrue($pegawaiPppk->isEntitledToLeaveType('Cuti Melahirkan'));
        $this->assertFalse($pegawaiPppk->isEntitledToLeaveType('Cuti Besar'));
        $this->assertFalse($pegawaiPppk->isEntitledToLeaveType('Cuti Alasan Penting'));
        $this->assertFalse($pegawaiPppk->isEntitledToLeaveType('Cuti di Luar Tanggungan Negara'));

        // 2. Pastikan pengajuan Cuti Besar untuk PPPK ditolak oleh validasi Pasal 4 Perbkn No. 7 Tahun 2022
        $responseReject = $this->actingAs($this->adminUser)->post(route('pengajuan-cuti.store'), [
            'pegawai_id'      => $pegawaiPppk->id,
            'jenis_cuti'      => 'Cuti Besar',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2026-07-10',
            'alasan'          => 'Mengajukan cuti besar',
        ]);
        $responseReject->assertSessionHasErrors('jenis_cuti');

        // 3. Pastikan pengajuan Cuti Tahunan untuk PPPK berhasil
        $responseValid = $this->actingAs($this->adminUser)->post(route('pengajuan-cuti.store'), [
            'pegawai_id'      => $pegawaiPppk->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => '2026-07-06',
            'tanggal_selesai' => '2026-07-08',
            'alasan'          => 'Keperluan keluarga sesuai Pasal 5 Perbkn No. 7/2022',
        ]);
        $responseValid->assertRedirect(route('pengajuan-cuti.index'));

        // 4. Pastikan seluruh 6 menu Layanan Cuti ASN sinkron dan menampilkan ringkasan peraturan BKN No. 7 Tahun 2022
        $indexRes = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('Ringkasan Syarat, Ketentuan & Pemisahan Hak Cuti ASN (PNS vs PPPK)');

        $monitoringAllRes = $this->actingAs($this->adminUser)->get(route('pengajuan-cuti.index'));
        $monitoringAllRes->assertStatus(200);
        $monitoringAllRes->assertSee('Ringkasan Syarat, Ketentuan & Pemisahan Hak Cuti ASN (PNS vs PPPK)');

        $mineRes = $this->actingAs($this->adminUser)->get(route('pengajuan-cuti.index', ['mine' => 1]));
        $mineRes->assertStatus(200);
        $mineRes->assertSee('Permohonan Cuti Saya');

        $createRes = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.create'));
        $createRes->assertStatus(200);
        $createRes->assertSee('Ringkasan Syarat, Ketentuan & Pemisahan Hak Cuti ASN (PNS vs PPPK)');

        $bukuRes = $this->actingAs($this->pegawaiUser)->get(route('pengajuan-cuti.buku-cuti'));
        $bukuRes->assertStatus(200);
        $bukuRes->assertSee('Ringkasan Syarat, Ketentuan & Pemisahan Hak Cuti ASN (PNS vs PPPK)');

        $rekapRes = $this->actingAs($this->adminUser)->get(route('pengajuan-cuti.rekap-kuota'));
        $rekapRes->assertStatus(200);
        $rekapRes->assertSee('Ringkasan Syarat, Ketentuan & Pemisahan Hak Cuti ASN (PNS vs PPPK)');
        $rekapRes->assertSee('Menampilkan');

        $dukRes = $this->actingAs($this->adminUser)->get(route('duk.index'));
        $dukRes->assertStatus(200);
        $dukRes->assertSee('Daftar Urut Kepangkatan (DUK)');
        $dukRes->assertDontSee('Daftar Urut Kepangkatan (DUK) Pegawai');
        $dukRes->assertSee('I. DAFTAR URUT KEPANGKATAN DOSEN PNS', false);
        $dukRes->assertSee('II. DAFTAR URUT KEPANGKATAN DOSEN PPPK', false);
        $dukRes->assertSee('III. DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PNS', false);
        $dukRes->assertSee('IV. DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PPPK', false);
        $dukRes->assertSee('V. DAFTAR URUT PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK', false);
        $dukRes->assertSee('Menampilkan');

        // Akun Pegawai Biasa (PNS Dosen) hanya melihat tabel kategorinya sendiri (Dosen PNS) dan tidak melihat tabel kosong lainnya
        $dukPegawaiPnsRes = $this->actingAs($this->pegawaiUser)->get(route('duk.index'));
        $dukPegawaiPnsRes->assertStatus(200);
        $dukPegawaiPnsRes->assertSee('Daftar Urut Kepangkatan (DUK)');
        $dukPegawaiPnsRes->assertDontSee('Daftar Urut Kepangkatan (DUK) Pegawai');
        $dukPegawaiPnsRes->assertSee('DAFTAR URUT KEPANGKATAN DOSEN PNS', false);
        $dukPegawaiPnsRes->assertDontSee('DAFTAR URUT KEPANGKATAN DOSEN PPPK', false);
        $dukPegawaiPnsRes->assertDontSee('DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PNS', false);
        $dukPegawaiPnsRes->assertDontSee('DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PPPK', false);
        $dukPegawaiPnsRes->assertDontSee('DAFTAR URUT PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK', false);

        // Akun Pegawai PPPK hanya melihat tabel kategorinya sendiri
        $userPppk = User::factory()->create([
            'role_id'              => $this->pegawaiUser->role_id,
            'pegawai_id'           => $pegawaiPppk->id,
            'must_change_password' => false,
        ]);
        $dukPegawaiPppkRes = $this->actingAs($userPppk)->get(route('duk.index'));
        $dukPegawaiPppkRes->assertStatus(200);
        $dukPegawaiPppkRes->assertSee('DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PPPK', false);
        $dukPegawaiPppkRes->assertDontSee('DAFTAR URUT KEPANGKATAN DOSEN PNS', false);
        $dukPegawaiPppkRes->assertDontSee('DAFTAR URUT KEPANGKATAN DOSEN PPPK', false);
        $dukPegawaiPppkRes->assertDontSee('DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PNS', false);
        $dukPegawaiPppkRes->assertDontSee('DAFTAR URUT PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK', false);

        // Verifikasi seluruh menu tabel lainnya juga merender dengan paginasi 10 baris tanpa error
        $menuRoutes = [
            route('pegawai.index'),
            route('kepegawaian.dosen.index'),
            route('kepegawaian.tendik.index'),
            route('kepegawaian.phl.index'),
            route('pengajuan-karir.index'),
            route('pengajuan-karir.index', ['tab' => 'satyalancana']),
            route('kp.index'),
            route('kgb.index'),
            route('satyalancana.index'),
            route('anjab.index'),
            route('abk.index'),
            route('manajemen-talenta.rekap'),
            route('manajemen-talenta.asesmen.index'),
            route('manajemen-talenta.suksesi.index'),
            route('manajemen-talenta.idp'),
            route('admin.logbook.index', ['view_mode' => 'rekap']),
            route('admin.logbook.index', ['view_mode' => 'detail']),
            route('admin.presensi.index', ['mode' => 'daily']),
            route('admin.presensi.index', ['mode' => 'monthly']),
            route('admin.presensi.locations'),
            route('layanan-surat.index'),
            route('pengajuan-jabatan.index'),
            route('unit-kerja.index'),
            route('jabatan.index'),
            route('golongan.index'),
            route('audit-logs.index'),
            route('notifications.index'),
        ];

        foreach ($menuRoutes as $url) {
            $res = $this->actingAs($this->adminUser)->get($url);
            $res->assertStatus(200);
        }

        // Verifikasi navigasi akun Admin murni (tanpa pegawai_id) tidak menampilkan menu mandiri yang ambigu
        $adminDashRes = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $adminDashRes->assertStatus(200);
        $adminDashRes->assertDontSee('Profil & Karir Saya', false);
        $adminDashRes->assertDontSee('Presensi Harian Saya', false);
        $adminDashRes->assertDontSee('Riwayat Presensi Saya', false);
        $adminDashRes->assertDontSee('Logbook Kinerja Saya', false);
        $adminDashRes->assertDontSee('Permohonan Cuti Saya', false);
        $adminDashRes->assertSee('flex-nowrap', false);

        // Sedangkan akun Pegawai tetap melihat menu mandiri "Profil & Karir Saya"
        $pegawaiDashRes = $this->actingAs($this->pegawaiUser)->get(route('dashboard'));
        $pegawaiDashRes->assertStatus(200);
        $pegawaiDashRes->assertSee('Profil & Karir Saya', false);
        $pegawaiDashRes->assertSee('Presensi Harian Saya', false);
        $pegawaiDashRes->assertSee('Logbook Kinerja Saya', false);
        $pegawaiDashRes->assertSee('Permohonan Cuti Saya', false);

        $eksekutifRes = $this->actingAs($this->pimpinanUser)->get(route('pengajuan-cuti.monitoring-pimpinan'));
        $eksekutifRes->assertStatus(200);
        $eksekutifRes->assertSee('Ringkasan Syarat, Ketentuan & Pemisahan Hak Cuti ASN (PNS vs PPPK)');
    }
}
