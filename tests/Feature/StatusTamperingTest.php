<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\PengajuanKarir;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusTamperingTest extends TestCase
{
    use RefreshDatabase;

    protected User $pegawaiUserA;

    protected User $pegawaiUserB;

    protected Pegawai $pegawaiA;

    protected Pegawai $pegawaiB;

    protected function setUp(): void
    {
        parent::setUp();

        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->pegawaiA = Pegawai::create([
            'nip' => '199001012019011001',
            'nama' => 'Budi Submitter',
            'jenis_pegawai' => 'PNS',
            'pendidikan_terakhir' => 'S1',
            'status_pegawai' => 'Aktif',
            'mkg_tahun' => 4,
            'mkg_bulan' => 0,
        ]);

        $this->pegawaiB = Pegawai::create([
            'nip' => '199202022020022002',
            'nama' => 'Target Korban',
            'jenis_pegawai' => 'PNS',
            'pendidikan_terakhir' => 'S1',
            'status_pegawai' => 'Aktif',
            'mkg_tahun' => 2,
            'mkg_bulan' => 0,
        ]);

        $this->pegawaiUserA = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->pegawaiA->id,
            'must_change_password' => false,
        ]);

        $this->pegawaiUserB = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'pegawai_id' => $this->pegawaiB->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Test pegawai biasa tidak bisa memanipulasi status menjadi Disetujui saat submit cuti
     */
    public function test_submitting_cuti_ignores_injected_status_and_approver_fields(): void
    {
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(2);

        $payload = [
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => $start->toDateString(),
            'tanggal_selesai' => $end->toDateString(),
            'alasan' => 'Keperluan keluarga',
            'alamat_selama_cuti' => 'Pekanbaru',
            // Field injeksi yang dicoba dimanipulasi
            'status' => 'Disetujui',
            'approved_by' => 999,
            'approved_at' => now()->toDateTimeString(),
            'pertimbangan_atasan' => 'Disetujui',
            'catatan_pimpinan' => 'Injected approval note',
        ];

        $response = $this->actingAs($this->pegawaiUserA)
            ->post(route('pengajuan-cuti.store'), $payload);

        $response->assertRedirect(route('pengajuan-cuti.index'));

        $cuti = PengajuanCuti::where('pegawai_id', $this->pegawaiA->id)->first();
        $this->assertNotNull($cuti);

        // Status WAJIB 'Menunggu Persetujuan', bukan 'Disetujui'
        $this->assertEquals('Menunggu Persetujuan', $cuti->status);
        $this->assertNull($cuti->approved_by);
        $this->assertNull($cuti->pertimbangan_atasan);
        $this->assertNull($cuti->catatan_pimpinan);
    }

    /**
     * Test pegawai biasa tidak bisa memalsukan status pengajuan karir
     */
    public function test_submitting_karir_ignores_injected_status_and_metadata(): void
    {
        $payload = [
            'jenis_pengajuan' => 'KGB',
            'catatan_pegawai' => 'Pengajuan KGB 2026',
            // Field injeksi
            'status' => 'disetujui',
            'nomor_surat' => 'SURAT-PALSU-001/UNRI/2026',
        ];

        $response = $this->actingAs($this->pegawaiUserA)
            ->post(route('pengajuan-karir.store'), $payload);

        $response->assertRedirect(route('pengajuan-karir.index'));

        $karir = PengajuanKarir::where('pegawai_id', $this->pegawaiA->id)->first();
        $this->assertNotNull($karir);

        // Status WAJIB 'diajukan', bukan 'disetujui'
        $this->assertEquals('diajukan', $karir->status);
        $this->assertNull($karir->nomor_surat);
    }

    /**
     * Test pegawai biasa tidak bisa memalsukan pegawai_id milik orang lain saat submit cuti
     */
    public function test_pegawai_cannot_spoof_another_pegawai_id_when_submitting_cuti(): void
    {
        $start = Carbon::now()->next(Carbon::MONDAY);
        $end = $start->copy()->addDays(2);

        $payload = [
            'jenis_cuti' => 'Cuti Tahunan',
            'tanggal_mulai' => $start->toDateString(),
            'tanggal_selesai' => $end->toDateString(),
            'alasan' => 'Mencoba submit atas nama pegawai B',
            'alamat_selama_cuti' => 'Pekanbaru',
            'pegawai_id' => $this->pegawaiB->id, // Mencoba memalsukan ID
        ];

        $response = $this->actingAs($this->pegawaiUserA)
            ->post(route('pengajuan-cuti.store'), $payload);

        $response->assertRedirect(route('pengajuan-cuti.index'));

        // Cuti harus tercatat atas nama Pegawai A, BUKAN Pegawai B
        $this->assertDatabaseHas('pengajuan_cuti', [
            'pegawai_id' => $this->pegawaiA->id,
            'alasan' => 'Mencoba submit atas nama pegawai B',
        ]);
        $this->assertDatabaseMissing('pengajuan_cuti', [
            'pegawai_id' => $this->pegawaiB->id,
            'alasan' => 'Mencoba submit atas nama pegawai B',
        ]);
    }
}
