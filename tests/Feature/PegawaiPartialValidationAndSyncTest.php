<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PegawaiPartialValidationAndSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected UnitKerja $unitKerja1;
    protected UnitKerja $unitKerja2;
    protected Jabatan $jabatan1;
    protected Jabatan $jabatan2;
    protected Golongan $golongan1;
    protected Golongan $golongan2;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);

        $this->adminUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->unitKerja1 = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $this->unitKerja2 = UnitKerja::create(['nama_unit' => 'Badan Kepegawaian', 'kode_unit' => 'BKD']);

        $this->jabatan1 = Jabatan::create(['kode_jabatan' => 'JAB-01', 'nama_jabatan' => 'Staf Administrasi']);
        $this->jabatan2 = Jabatan::create(['kode_jabatan' => 'JAB-02', 'nama_jabatan' => 'Kepala Seksi']);

        $this->golongan1 = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);
        $this->golongan2 = Golongan::create(['nama_golongan' => 'III/b', 'nama_pangkat' => 'Penata Muda Tk. I']);
    }

    /**
     * Test partial saving: only NIP and Nama are required, all others optional.
     */
    public function test_can_create_pegawai_with_only_nip_and_nama_partially(): void
    {
        $payload = [
            'nip'  => '199501012022011001',
            'nama' => 'Budi Santoso',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);

        $response->assertRedirect(route('pegawai.index'));
        $this->assertDatabaseHas('pegawai', [
            'nip'           => '199501012022011001',
            'nama'          => 'Budi Santoso',
            'unit_kerja_id' => null,
            'jabatan_id'    => null,
            'golongan_id'   => null,
        ]);
    }

    /**
     * Test partial update: can update without supplying optional fields or uploads.
     */
    public function test_can_update_pegawai_partially_without_file_uploads(): void
    {
        $pegawai = Pegawai::create([
            'nip'  => '199501012022011002',
            'nama' => 'Siti Aminah',
        ]);

        $payload = [
            'nip'                => '199501012022011002',
            'nama'               => 'Siti Aminah, S.Kom',
            'karpeg_karis_karsu' => '12345678',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('pegawai.update', $pegawai->id), $payload);

        $response->assertRedirect(route('pegawai.index'));
        $this->assertDatabaseHas('pegawai', [
            'id'                 => $pegawai->id,
            'nama'               => 'Siti Aminah, S.Kom',
            'karpeg_karis_karsu' => '12345678',
        ]);
    }

    /**
     * Test saving riwayat pangkat marks only one active row and syncs to pegawai table.
     */
    public function test_multi_row_pangkat_enforces_single_active_and_syncs_to_pegawai(): void
    {
        $fileSk = UploadedFile::fake()->create('sk_pangkat.pdf', 100, 'application/pdf');

        $payload = [
            'nip'             => '199501012022011003',
            'nama'            => 'Ahmad Dahlan',
            'riwayat_pangkat' => [
                [
                    'golongan_id' => $this->golongan1->id,
                    'tmt'         => '2020-01-01',
                    'nomor_sk'    => 'SK/001/2020',
                    'tanggal_sk'  => '2020-01-02',
                    'status'      => 'aktif',
                ],
                [
                    'golongan_id' => $this->golongan2->id,
                    'tmt'         => '2024-01-01',
                    'nomor_sk'    => 'SK/002/2024',
                    'tanggal_sk'  => '2024-01-02',
                    'status'      => 'aktif',
                    'file_sk'     => $fileSk,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);
        $response->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::where('nip', '199501012022011003')->first();
        $this->assertNotNull($pegawai);

        // Check single active row in riwayat
        $activePangkats = $pegawai->riwayatPangkat()->whereIn('status', ['aktif', 'Aktif'])->get();
        $this->assertCount(1, $activePangkats);
        $this->assertEquals($this->golongan2->id, $activePangkats->first()->golongan_id);

        // Check sync to Pegawai table
        $this->assertEquals($this->golongan2->id, $pegawai->golongan_id);
        $this->assertEquals('2024-01-01', Carbon::parse($pegawai->tmt_pangkat_terakhir)->format('Y-m-d'));
        $this->assertEquals('SK/002/2024', $pegawai->nomor_sk_pangkat_terakhir);
        $this->assertEquals('2024-01-02', Carbon::parse($pegawai->tanggal_sk_pangkat_terakhir)->format('Y-m-d'));
        $this->assertEquals('2028-01-01', Carbon::parse($pegawai->kp_berikutnya)->format('Y-m-d'));
    }

    /**
     * Test saving riwayat jabatan marks only one active row and syncs to pegawai table.
     */
    public function test_multi_row_jabatan_enforces_single_active_and_syncs_to_pegawai(): void
    {
        $payload = [
            'nip'             => '199501012022011004',
            'nama'            => 'Dewi Lestari',
            'riwayat_jabatan' => [
                [
                    'jabatan_id'    => $this->jabatan1->id,
                    'unit_kerja_id' => $this->unitKerja1->id,
                    'tmt_jabatan'   => '2021-01-01',
                    'nomor_sk'      => 'SK/JAB/001',
                    'tanggal_sk'    => '2021-01-02',
                    'status'        => 'aktif',
                ],
                [
                    'jabatan_id'    => $this->jabatan2->id,
                    'unit_kerja_id' => $this->unitKerja2->id,
                    'tmt_jabatan'   => '2025-01-01',
                    'nomor_sk'      => 'SK/JAB/002',
                    'tanggal_sk'    => '2025-01-02',
                    'status'        => 'aktif',
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), $payload);
        $response->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::where('nip', '199501012022011004')->first();
        $this->assertNotNull($pegawai);

        // Check single active row
        $activeJabatans = $pegawai->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->get();
        $this->assertCount(1, $activeJabatans);
        $this->assertEquals($this->jabatan2->id, $activeJabatans->first()->jabatan_id);

        // Check sync to Pegawai table
        $this->assertEquals($this->jabatan2->id, $pegawai->jabatan_id);
        $this->assertEquals($this->unitKerja2->id, $pegawai->unit_kerja_id);
        $this->assertEquals('SK/JAB/002', $pegawai->nomor_sk_pertama);
        $this->assertEquals('2025-01-02', Carbon::parse($pegawai->tanggal_sk_pertama)->format('Y-m-d'));
    }

    /**
     * Test two-way sync: updating top-form jabatan/golongan creates/updates active riwayat.
     */
    public function test_top_form_input_syncs_to_active_riwayat(): void
    {
        $pegawai = Pegawai::create([
            'nip'  => '199501012022011005',
            'nama' => 'Rahmat Hidayat',
        ]);

        $payload = [
            'nip'                         => '199501012022011005',
            'nama'                        => 'Rahmat Hidayat',
            'unit_kerja_id'               => $this->unitKerja1->id,
            'jabatan_id'                  => $this->jabatan1->id,
            'golongan_id'                 => $this->golongan1->id,
            'nomor_sk_pertama'            => 'SK-UTAMA-01',
            'tanggal_sk_pertama'          => '2023-05-10',
            'nomor_sk_pangkat_terakhir'   => 'SK-PANGKAT-01',
            'tanggal_sk_pangkat_terakhir' => '2023-05-10',
            'tmt_pangkat_terakhir'        => '2023-05-10',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('pegawai.update', $pegawai->id), $payload);
        $response->assertRedirect(route('pegawai.index'));

        $pegawai->refresh();

        // Check active riwayat jabatan created
        $activeJabatan = $pegawai->riwayatJabatan()->whereIn('status', ['aktif', 'Aktif'])->first();
        $this->assertNotNull($activeJabatan);
        $this->assertEquals($this->jabatan1->id, $activeJabatan->jabatan_id);
        $this->assertEquals($this->unitKerja1->id, $activeJabatan->unit_kerja_id);

        // Check active riwayat pangkat created
        $activePangkat = $pegawai->riwayatPangkat()->whereIn('status', ['aktif', 'Aktif'])->first();
        $this->assertNotNull($activePangkat);
        $this->assertEquals($this->golongan1->id, $activePangkat->golongan_id);
        $this->assertEquals('2023-05-10', Carbon::parse($activePangkat->tmt)->format('Y-m-d'));
    }

    /**
     * Test dashboard and DUK consistency.
     */
    public function test_dashboard_and_duk_consistency_with_partial_and_synced_pegawai(): void
    {
        $pegawai = Pegawai::create([
            'nip'                  => '199501012022011006',
            'nama'                 => 'Rina Marlina',
            'status_pegawai'       => 'Aktif',
            'golongan_id'          => $this->golongan1->id,
            'jabatan_id'           => $this->jabatan1->id,
            'unit_kerja_id'        => $this->unitKerja1->id,
            'tmt_pangkat_terakhir' => now()->subYears(4)->format('Y-m-d'),
            'kp_berikutnya'        => now()->addDays(10)->format('Y-m-d'),
        ]);

        // Access Dashboard
        $dashboardResponse = $this->actingAs($this->adminUser)->get('/dashboard');
        $dashboardResponse->assertStatus(200);

        // Access DUK
        $dukResponse = $this->actingAs($this->adminUser)->get('/duk');
        $dukResponse->assertStatus(200);
        $dukResponse->assertSee('Rina Marlina');
        $dukResponse->assertSee('199501012022011006');
    }
}
