<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RiwayatStrSip;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiwayatStrSipTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected User $pegawaiUser;
    protected Pegawai $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->pegawai = Pegawai::create([
            'nip'                 => '198801012015012001',
            'nama'                => 'Ns. Dian Fitriani, M.Kep',
            'jenis_pegawai'       => 'Dosen',
            'pendidikan_terakhir' => 'S2',
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
    }

    public function test_admin_and_pimpinan_can_view_riwayat_str_sip_index(): void
    {
        $responseAdmin = $this->actingAs($this->adminUser)->get(route('riwayat-str-sip.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Legalitas Profesi');

        $responsePimpinan = $this->actingAs($this->pimpinanUser)->get(route('riwayat-str-sip.index'));
        $responsePimpinan->assertStatus(200);
        $responsePimpinan->assertSee('Legalitas Profesi');
    }

    public function test_admin_can_create_str_with_file_upload(): void
    {
        $file = UploadedFile::fake()->create('str_legalisir.pdf', 500, 'application/pdf');

        $payload = [
            'pegawai_id'        => $this->pegawai->id,
            'jenis_dokumen'     => 'STR',
            'nomor_registrasi'  => '1401521234567890',
            'nama_dokumen'      => 'Perawat Ahli / Ners',
            'instansi_penerbit' => 'KTKI / Kemenkes RI',
            'tanggal_terbit'    => '2024-01-15',
            'tanggal_berakhir'  => Carbon::now()->addMonths(5)->toDateString(),
            'is_seumur_hidup'   => 0,
            'file_dokumen'      => $file,
            'keterangan'        => 'STR perpanjangan',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('riwayat-str-sip.store'), $payload);
        $response->assertRedirect(route('riwayat-str-sip.index'));

        $this->assertDatabaseHas('riwayat_str_sip', [
            'pegawai_id'       => $this->pegawai->id,
            'jenis_dokumen'    => 'STR',
            'nomor_registrasi' => '1401521234567890',
            'nama_dokumen'     => 'Perawat Ahli / Ners',
        ]);

        $created = RiwayatStrSip::where('nomor_registrasi', '1401521234567890')->first();
        $this->assertNotNull($created->file_dokumen);
        Storage::disk('local')->assertExists($created->file_dokumen);
    }

    public function test_admin_can_create_seumur_hidup_str(): void
    {
        $payload = [
            'pegawai_id'        => $this->pegawai->id,
            'jenis_dokumen'     => 'STR',
            'nomor_registrasi'  => '1401529999999999',
            'nama_dokumen'      => 'STR Ners Seumur Hidup',
            'instansi_penerbit' => 'KTKI',
            'tanggal_terbit'    => '2024-06-01',
            'is_seumur_hidup'   => 1,
        ];

        $response = $this->actingAs($this->adminUser)->post(route('riwayat-str-sip.store'), $payload);
        $response->assertRedirect(route('riwayat-str-sip.index'));

        $this->assertDatabaseHas('riwayat_str_sip', [
            'nomor_registrasi' => '1401529999999999',
            'is_seumur_hidup'  => 1,
            'tanggal_berakhir' => null,
            'status'           => 'Aktif',
        ]);

        $str = RiwayatStrSip::where('nomor_registrasi', '1401529999999999')->first();
        $this->assertEquals('Seumur Hidup', $str->status_label);
    }

    public function test_admin_can_update_riwayat_str_sip(): void
    {
        $str = RiwayatStrSip::create([
            'pegawai_id'        => $this->pegawai->id,
            'jenis_dokumen'     => 'SIP',
            'nomor_registrasi'  => 'SIP-RS-001',
            'nama_dokumen'      => 'SIP RS Arifin Achmad',
            'instansi_penerbit' => 'Dinkes Pekanbaru',
            'tanggal_terbit'    => '2023-01-01',
            'tanggal_berakhir'  => '2028-01-01',
            'status'            => 'Aktif',
        ]);

        $updatePayload = [
            'pegawai_id'        => $this->pegawai->id,
            'jenis_dokumen'     => 'SIP',
            'nomor_registrasi'  => 'SIP-RS-001-REV',
            'nama_dokumen'      => 'SIP RSUD Arifin Achmad Updated',
            'instansi_penerbit' => 'Dinkes Pekanbaru',
            'tanggal_terbit'    => '2023-01-01',
            'tanggal_berakhir'  => '2028-01-01',
            'status'            => 'Aktif',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('riwayat-str-sip.update', $str->id), $updatePayload);
        $response->assertRedirect(route('riwayat-str-sip.index'));

        $this->assertDatabaseHas('riwayat_str_sip', [
            'id'               => $str->id,
            'nomor_registrasi' => 'SIP-RS-001-REV',
            'nama_dokumen'     => 'SIP RSUD Arifin Achmad Updated',
        ]);
    }

    public function test_admin_can_delete_riwayat_str_sip(): void
    {
        $str = RiwayatStrSip::create([
            'pegawai_id'        => $this->pegawai->id,
            'jenis_dokumen'     => 'STR',
            'nomor_registrasi'  => 'STR-TO-DELETE',
            'tanggal_terbit'    => '2022-01-01',
            'tanggal_berakhir'  => '2027-01-01',
            'status'            => 'Aktif',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('riwayat-str-sip.destroy', $str->id));
        $response->assertRedirect(route('riwayat-str-sip.index'));

        $this->assertDatabaseMissing('riwayat_str_sip', [
            'id' => $str->id,
        ]);
    }

    public function test_pimpinan_and_pegawai_cannot_create_or_delete_str_sip(): void
    {
        $payload = [
            'pegawai_id'        => $this->pegawai->id,
            'jenis_dokumen'     => 'STR',
            'nomor_registrasi'  => 'FORBIDDEN-001',
            'tanggal_terbit'    => '2024-01-01',
            'tanggal_berakhir'  => '2029-01-01',
        ];

        $resPimpinan = $this->actingAs($this->pimpinanUser)->post(route('riwayat-str-sip.store'), $payload);
        $resPimpinan->assertStatus(403);

        $resPegawai = $this->actingAs($this->pegawaiUser)->post(route('riwayat-str-sip.store'), $payload);
        $resPegawai->assertStatus(403);
    }

    public function test_dashboard_radar_catches_str_expiring_within_6_months(): void
    {
        // STR expiring in 2 months (should appear in radar)
        RiwayatStrSip::create([
            'pegawai_id'        => $this->pegawai->id,
            'jenis_dokumen'     => 'STR',
            'nomor_registrasi'  => 'STR-EXP-RADAR',
            'nama_dokumen'      => 'STR Expiring Radar',
            'tanggal_terbit'    => '2021-01-01',
            'tanggal_berakhir'  => Carbon::now()->addMonths(2)->toDateString(),
            'is_seumur_hidup'   => 0,
            'status'            => 'Aktif',
        ]);

        $dashboardResponse = $this->actingAs($this->adminUser)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('STR & SIP (Ners/Klinis)', false);
        $dashboardResponse->assertSee('Ns. Dian Fitriani, M.Kep');
    }
}
