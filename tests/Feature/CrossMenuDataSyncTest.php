<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Logbook;
use App\Models\MutasiPegawai;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\RiwayatStrSip;
use App\Models\Role;
use App\Models\TalentMapping;
use App\Models\TugasBelajar;
use App\Models\UnitKerja;
use App\Models\User;
use App\Repositories\Eloquent\DashboardRepository;
use App\Services\AttendanceService;
use App\Services\ExecutiveAnalyticsService;
use App\Services\KgbService;
use App\Services\KpService;
use App\Services\LogbookService;
use App\Services\PegawaiService;
use App\Services\RiwayatDiklatService;
use App\Services\RiwayatStrSipService;
use App\Services\TugasBelajarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossMenuDataSyncTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminRole;
    protected Role $pegawaiRole;
    protected UnitKerja $unitA;
    protected UnitKerja $unitB;
    protected Jabatan $jabatanDosen;
    protected Jabatan $jabatanTendik;
    protected Golongan $golongan3C;
    protected Golongan $golongan3D;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $this->pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->unitA = UnitKerja::create(['kode_unit' => 'JKP', 'nama_unit' => 'Jurusan Keperawatan']);
        $this->unitB = UnitKerja::create(['kode_unit' => 'SBU', 'nama_unit' => 'Subbagian Umum']);

        $this->jabatanDosen = Jabatan::create([
            'nama_jabatan'  => 'Lektor Dosen Keperawatan',
            'unit_kerja_id' => $this->unitA->id,
        ]);
        $this->jabatanTendik = Jabatan::create([
            'nama_jabatan'  => 'Pengadministrasi Kepegawaian',
            'unit_kerja_id' => $this->unitB->id,
        ]);

        $this->golongan3C = Golongan::create([
            'kode'          => 'III/c',
            'nama_golongan' => 'III/c',
            'nama_pangkat'  => 'Penata',
        ]);
        $this->golongan3D = Golongan::create([
            'kode'          => 'III/d',
            'nama_golongan' => 'III/d',
            'nama_pangkat'  => 'Penata Tingkat I',
        ]);
    }

    public function test_approved_cuti_syncs_to_monthly_matrix_today_stats_and_executive_trends(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 5, 9, 0, 0, 'Asia/Jakarta')); // Kamis

        $pegawai = Pegawai::create([
            'nip'            => '198801012015041001',
            'nama'           => 'Ns. Budi Santoso, M.Kep',
            'jenis_pegawai'  => 'Dosen',
            'status_asn'     => 'ASN',
            'status_pegawai' => 'Aktif',
            'unit_kerja_id'  => $this->unitA->id,
            'jabatan_id'     => $this->jabatanDosen->id,
        ]);

        User::create([
            'name'       => $pegawai->nama,
            'email'      => 'budi@unri.ac.id',
            'password'   => bcrypt('password'),
            'role_id'    => $this->pegawaiRole->id,
            'pegawai_id' => $pegawai->id,
        ]);

        PengajuanCuti::create([
            'pegawai_id'      => $pegawai->id,
            'jenis_cuti'      => 'Cuti Tahunan',
            'tanggal_mulai'   => '2026-11-04',
            'tanggal_selesai' => '2026-11-06',
            'jumlah_hari'     => 3,
            'alasan'          => 'Keperluan keluarga',
            'status'          => 'Disetujui',
        ]);

        $attendanceService = app(AttendanceService::class);

        // 1. Cek Matriks Bulanan (Tanggal 4 & 5 harus 'C' / 'leave', bukan 'A' / 'absent')
        $matrix = $attendanceService->getMonthlyMatrix(11, 2026);
        $row = collect($matrix['rows'])->first(fn ($r) => $r['pegawai']->id === $pegawai->id);

        $this->assertNotNull($row);
        $this->assertEquals('leave', $row['days'][4]['status']);
        $this->assertEquals('C', $row['days'][4]['badge']);
        $this->assertEquals('leave', $row['days'][5]['status']);
        $this->assertEquals('C', $row['days'][5]['badge']);
        $this->assertGreaterThanOrEqual(2, $row['total_cuti']);

        // 2. Cek Statistik Hari Ini
        $todayStats = $attendanceService->todayStatistics();
        $this->assertEquals(1, $todayStats['total_cuti']);

        // 3. Cek Tren Presensi Eksekutif Pimpinan
        $analytics = app(ExecutiveAnalyticsService::class);
        $trends = $analytics->getAttendanceTrends(11, 2026);
        $this->assertGreaterThanOrEqual(1, array_sum($trends['leave']));

        Carbon::setTestNow();
    }

    public function test_mutasi_pegawai_creates_active_riwayat_jabatan_and_survives_history_resync(): void
    {
        $admin = User::create([
            'name'     => 'Admin Kepegawaian',
            'email'    => 'admin@unri.ac.id',
            'password' => bcrypt('password'),
            'role_id'  => $this->adminRole->id,
        ]);

        $pegawai = Pegawai::create([
            'nip'            => '199002022018032001',
            'nama'           => 'Siti Aminah, S.Kom',
            'jenis_pegawai'  => 'Tendik',
            'status_asn'     => 'ASN',
            'status_pegawai' => 'Aktif',
            'unit_kerja_id'  => $this->unitA->id,
            'jabatan_id'     => $this->jabatanDosen->id,
        ]);

        RiwayatJabatan::create([
            'pegawai_id'    => $pegawai->id,
            'jabatan_id'    => $this->jabatanDosen->id,
            'unit_kerja_id' => $this->unitA->id,
            'tmt_jabatan'   => '2020-01-01',
            'status'        => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->post(route('mutasi-pegawai.store'), [
            'pegawai_id'      => $pegawai->id,
            'unit_lama_id'    => $this->unitA->id,
            'unit_baru_id'    => $this->unitB->id,
            'jabatan_lama_id' => $this->jabatanDosen->id,
            'jabatan_baru_id' => $this->jabatanTendik->id,
            'tmt'             => '2026-06-01',
            'nomor_sk'        => 'SK-MUTASI/2026/099',
            'keterangan'      => 'Rotasi penataan SDM',
        ]);

        $response->assertRedirect(route('mutasi-pegawai.index'));

        // Pastikan RiwayatJabatan baru tercatat aktif
        $this->assertDatabaseHas('riwayat_jabatan', [
            'pegawai_id'    => $pegawai->id,
            'jabatan_id'    => $this->jabatanTendik->id,
            'unit_kerja_id' => $this->unitB->id,
            'nomor_sk'      => 'SK-MUTASI/2026/099',
            'status'        => 'Aktif',
        ]);

        // Jalankan syncPegawaiFromHistories dan pastikan mutasi TIDAK ter-reset ke jabatan lama
        app(PegawaiService::class)->syncPegawaiFromHistories($pegawai->fresh());
        $fresh = $pegawai->fresh();

        $this->assertEquals($this->unitB->id, $fresh->unit_kerja_id);
        $this->assertEquals($this->jabatanTendik->id, $fresh->jabatan_id);
        $this->assertEquals(1, $fresh->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->count());
    }

    public function test_kp_and_kgb_services_sync_sk_details_to_riwayat_and_master_pegawai(): void
    {
        $pegawai = Pegawai::create([
            'nip'            => '198503032010121002',
            'nama'           => 'Dr. Hendra Wijaya, M.Kep',
            'jenis_pegawai'  => 'Dosen',
            'status_asn'     => 'ASN',
            'status_pegawai' => 'Aktif',
            'golongan_id'    => $this->golongan3C->id,
            'unit_kerja_id'  => $this->unitA->id,
            'jabatan_id'     => $this->jabatanDosen->id,
        ]);

        // 1. Proses KP lengkap dengan Nomor SK dan File SK
        app(KpService::class)->prosesKp($pegawai, [
            'golongan_baru_id' => $this->golongan3D->id,
            'tmt_pangkat_baru' => '2026-04-01',
            'nomor_sk'         => 'SK-KP/UN19/2026/01',
            'tanggal_sk'       => '2026-03-25',
            'file_sk'          => 'pegawai/sk/sk_kp_2026.pdf',
        ]);

        $fresh = $pegawai->fresh();
        $this->assertEquals($this->golongan3D->id, $fresh->golongan_id);
        $this->assertEquals('SK-KP/UN19/2026/01', $fresh->nomor_sk_pangkat_terakhir);
        $this->assertEquals('pegawai/sk/sk_kp_2026.pdf', $fresh->file_sk_pangkat_terakhir);

        $this->assertDatabaseHas('riwayat_pangkat', [
            'pegawai_id'  => $pegawai->id,
            'golongan_id' => $this->golongan3D->id,
            'nomor_sk'    => 'SK-KP/UN19/2026/01',
            'file_sk'     => 'pegawai/sk/sk_kp_2026.pdf',
            'status'      => 'aktif',
        ]);

        // 2. Proses KGB
        app(KgbService::class)->prosesKgb($fresh, [
            'tmt_kgb_baru' => '2026-05-01',
            'file_sk'      => 'pegawai/sk/sk_kgb_2026.pdf',
        ]);

        $freshAfterKgb = $pegawai->fresh();
        $this->assertEquals('2026-05-01', $freshAfterKgb->tmt_kgb_terakhir->format('Y-m-d'));
        $this->assertEquals('2028-05-01', $freshAfterKgb->kgb_berikutnya->format('Y-m-d'));
        $this->assertEquals('pegawai/sk/sk_kgb_2026.pdf', $freshAfterKgb->file_sk_kgb_terakhir);
    }

    public function test_riwayat_str_sip_syncs_to_pegawai_master_columns(): void
    {
        $pegawai = Pegawai::create([
            'nip'            => '199104042019032005',
            'nama'           => 'Ns. Rina Kartika, M.Kep',
            'jenis_pegawai'  => 'Dosen',
            'status_asn'     => 'ASN',
            'status_pegawai' => 'Aktif',
            'unit_kerja_id'  => $this->unitA->id,
            'jabatan_id'     => $this->jabatanDosen->id,
        ]);

        $strService = app(RiwayatStrSipService::class);
        $strService->create([
            'pegawai_id'        => $pegawai->id,
            'jenis_dokumen'     => 'STR',
            'nomor_registrasi'  => 'STR-99887766',
            'instansi_penerbit' => 'KTKI Kemenkes RI',
            'tanggal_terbit'    => '2025-01-10',
            'tanggal_berakhir'  => '2027-01-10',
            'is_seumur_hidup'   => false,
        ]);

        $fresh = $pegawai->fresh();
        $this->assertEquals('STR-99887766', $fresh->nomor_str);
        $this->assertEquals('2027-01-10', $fresh->tanggal_kadaluarsa_str?->format('Y-m-d'));
    }

    public function test_dashboard_retirement_reminder_uses_dynamic_bup_for_dosen_vs_tendik(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 1, 8, 0, 0, 'Asia/Jakarta'));

        // Dosen berusia 57 tahun 8 bulan (BUP Dosen = 65 tahun -> belum pensiun dalam 1 tahun ke depan)
        $dosen57 = Pegawai::create([
            'nip'            => '196810011995031001',
            'nama'           => 'Dr. Dosen Senior, M.Kep',
            'jenis_pegawai'  => 'Dosen',
            'status_asn'     => 'ASN',
            'status_pegawai' => 'Aktif',
            'tanggal_lahir'  => '1968-10-01',
            'unit_kerja_id'  => $this->unitA->id,
            'jabatan_id'     => $this->jabatanDosen->id,
        ]);

        // Tendik berusia 57 tahun 8 bulan (BUP Tendik = 58 tahun -> pensiun 4 bulan lagi)
        $tendik57 = Pegawai::create([
            'nip'            => '196810011995032002',
            'nama'           => 'Tendik Senior, S.AP',
            'jenis_pegawai'  => 'Tendik',
            'status_asn'     => 'ASN',
            'status_pegawai' => 'Aktif',
            'tanggal_lahir'  => '1968-10-01',
            'unit_kerja_id'  => $this->unitB->id,
            'jabatan_id'     => $this->jabatanTendik->id,
        ]);

        $reminder = app(DashboardRepository::class)->getReminder();
        $pensiunIds = collect($reminder['pensiun'])->pluck('id')->all();

        $this->assertContains($tendik57->id, $pensiunIds);
        $this->assertNotContains($dosen57->id, $pensiunIds);

        Carbon::setTestNow();
    }

    public function test_diklat_and_tugas_belajar_automatically_sync_talent_mapping_and_pegawai_status(): void
    {
        $pegawai = Pegawai::create([
            'nip'                 => '199205052020121003',
            'nama'                => 'Ns. Andi Pratama, M.Kep',
            'jenis_pegawai'       => 'Dosen',
            'status_asn'          => 'ASN',
            'status_pegawai'      => 'Aktif',
            'pendidikan_terakhir' => 'S2',
            'unit_kerja_id'       => $this->unitA->id,
            'jabatan_id'          => $this->jabatanDosen->id,
            'golongan_id'         => $this->golongan3C->id,
        ]);

        // 1. Tambah Diklat 24 JP -> harus otomatis memicu pembuatan/pembaruan TalentMapping
        app(RiwayatDiklatService::class)->create([
            'pegawai_id'            => $pegawai->id,
            'nama_diklat'           => 'Pelatihan Pekerti & AA Dosen',
            'penyelenggara'         => 'LPMPP UNRI',
            'tanggal_mulai'         => date('Y') . '-02-01',
            'tanggal_selesai'       => date('Y') . '-02-05',
            'jumlah_jam'            => 24,
            'nomor_sertifikat'      => 'SERT/2026/01',
        ]);

        $mapping = TalentMapping::where('pegawai_id', $pegawai->id)->where('tahun', (int) date('Y'))->first();
        $this->assertNotNull($mapping);
        $this->assertEquals(100.00, (float) $mapping->skor_pengembangan_kompetensi);

        // 2. Tambah Tugas Belajar tanpa mengisi jenis_pengembangan eksplisit -> harus menyinkronkan status_pegawai menjadi 'Tugas Belajar'
        $tubel = app(TugasBelajarService::class)->create([
            'pegawai_id'        => $pegawai->id,
            'jenjang_studi'     => 'S3',
            'perguruan_tinggi'  => 'Universitas Indonesia',
            'program_studi'     => 'Ilmu Keperawatan',
            'sumber_pembiayaan' => 'Beasiswa LPDP',
            'nomor_sk'          => 'SK-TUBEL/2026/01',
            'tanggal_sk'        => date('Y') . '-08-15',
            'tanggal_mulai'     => date('Y') . '-09-01',
            'tanggal_selesai'   => ((int) date('Y') + 3) . '-08-31',
            'status_studi'      => 'Sedang Studi',
        ]);

        $this->assertEquals(Pegawai::STATUS_TUGAS_BELAJAR, $pegawai->fresh()->status_pegawai);

        // Hapus Tugas Belajar -> status kembali menjadi 'Aktif'
        app(TugasBelajarService::class)->delete($tubel->id);
        $this->assertEquals(Pegawai::STATUS_AKTIF, $pegawai->fresh()->status_pegawai);
    }
}
