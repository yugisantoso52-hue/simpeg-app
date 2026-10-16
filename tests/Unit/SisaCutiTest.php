<?php

namespace Tests\Unit;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SisaCutiTest extends TestCase
{
    use RefreshDatabase;

    protected Pegawai $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pegawai = Pegawai::create([
            'nip' => '198501012010011001',
            'nama' => 'Ahmad Zaki',
            'jenis_pegawai' => 'Dosen',
            'pendidikan_terakhir' => 'S2',
            'status_pegawai' => 'Aktif',
        ]);
    }

    /**
     * Test sisa kuota cuti tahunan awal adalah default 12 hari
     */
    public function test_default_sisa_cuti_tahunan_is_12(): void
    {
        $this->assertEquals(12, $this->pegawai->sisa_cuti_tahunan);
    }

    /**
     * Test cuti tahunan yang Disetujui mengurangi kuota
     */
    public function test_approved_cuti_tahunan_reduces_quota(): void
    {
        PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => now()->startOfYear()->addMonths(2)->toDateString(),
            'tanggal_selesai' => now()->startOfYear()->addMonths(2)->addDays(2)->toDateString(),
            'jumlah_hari' => 3,
            'alasan' => 'Keperluan keluarga',
            'status' => 'Disetujui',
        ]);

        $this->pegawai->refresh();
        $this->assertEquals(9, $this->pegawai->sisa_cuti_tahunan);
    }

    /**
     * Test cuti tahunan dengan status Menunggu Persetujuan (Pending) juga memotong kuota
     */
    public function test_pending_cuti_tahunan_reduces_quota(): void
    {
        PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(1)->toDateString(),
            'jumlah_hari' => 2,
            'alasan' => 'Mudik',
            'status' => 'Menunggu Persetujuan',
        ]);

        $this->pegawai->refresh();
        $this->assertEquals(10, $this->pegawai->sisa_cuti_tahunan);
    }

    /**
     * Test cuti yang ditolak (Ditolak) TIDAK mengurangi kuota
     */
    public function test_rejected_cuti_does_not_reduce_quota(): void
    {
        PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(4)->toDateString(),
            'jumlah_hari' => 5,
            'alasan' => 'Liburan',
            'status' => 'Ditolak',
        ]);

        $this->pegawai->refresh();
        $this->assertEquals(12, $this->pegawai->sisa_cuti_tahunan);
    }

    /**
     * Test jenis cuti selain Cuti Tahunan (misal Cuti Sakit) TIDAK mengurangi kuota cuti tahunan
     */
    public function test_other_cuti_types_do_not_reduce_annual_quota(): void
    {
        PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'Cuti Sakit',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(5)->toDateString(),
            'jumlah_hari' => 6,
            'alasan' => 'Rawat inap RS',
            'status' => 'Disetujui',
        ]);

        $this->pegawai->refresh();
        $this->assertEquals(12, $this->pegawai->sisa_cuti_tahunan);
    }

    /**
     * Test cuti tahun lalu TIDAK mengurangi kuota tahun berjalan
     */
    public function test_previous_year_cuti_does_not_reduce_current_year_quota(): void
    {
        PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => now()->subYear()->toDateString(),
            'tanggal_selesai' => now()->subYear()->addDays(4)->toDateString(),
            'jumlah_hari' => 5,
            'alasan' => 'Cuti tahun lalu',
            'status' => 'Disetujui',
        ]);

        $this->pegawai->refresh();
        $this->assertEquals(12, $this->pegawai->sisa_cuti_tahunan);
    }

    /**
     * Test sisa kuota cuti tidak pernah bernilai negatif (minimal 0)
     */
    public function test_quota_never_drops_below_zero(): void
    {
        PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(14)->toDateString(),
            'jumlah_hari' => 15,
            'alasan' => 'Alasan khusus',
            'status' => 'Disetujui',
        ]);

        $this->pegawai->refresh();
        $this->assertEquals(0, $this->pegawai->sisa_cuti_tahunan);
    }

    /**
     * Test kalkulasi sisa cuti saat relasi pengajuanCuti sudah di-eager load (mencegah N+1)
     */
    public function test_sisa_cuti_with_eager_loaded_relation(): void
    {
        PengajuanCuti::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_selesai' => now()->addDays(3)->toDateString(),
            'jumlah_hari' => 4,
            'alasan' => 'Istirahat',
            'status' => 'Disetujui',
        ]);

        // Eager load relasi
        $eagerLoadedPegawai = Pegawai::with('pengajuanCuti')->find($this->pegawai->id);
        $this->assertTrue($eagerLoadedPegawai->relationLoaded('pengajuanCuti'));
        $this->assertEquals(8, $eagerLoadedPegawai->sisa_cuti_tahunan);
    }
}
