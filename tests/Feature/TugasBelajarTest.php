<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\TugasBelajar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TugasBelajarTest extends TestCase
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
            'nip'                 => '198810102015041002',
            'nama'                => 'Ns. Ahmad Fauzi, M.Kep',
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

    public function test_admin_and_pimpinan_can_view_tugas_belajar_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('tugas-belajar.index'));
        $response->assertStatus(200);
        $response->assertSee('Tugas Belajar');

        $responsePimpinan = $this->actingAs($this->pimpinanUser)->get(route('tugas-belajar.index'));
        $responsePimpinan->assertStatus(200);
    }

    public function test_admin_can_create_tugas_belajar_and_auto_updates_pegawai_status(): void
    {
        $fileSk = UploadedFile::fake()->create('sk_tubel.pdf', 300, 'application/pdf');

        $payload = [
            'pegawai_id'         => $this->pegawai->id,
            'jenis_pengembangan' => 'Tugas Belajar',
            'jenjang_studi'      => 'S3',
            'program_studi'      => 'Doctor of Nursing Science',
            'perguruan_tinggi'   => 'Monash University',
            'negara'             => 'Australia',
            'sumber_pembiayaan'  => 'Beasiswa LPDP',
            'nama_sponsor'       => 'LPDP Kemenkeu',
            'nomor_sk'           => '889/UN19/KP/2026',
            'tanggal_sk'         => '2026-08-01',
            'tanggal_mulai'      => '2026-09-01',
            'tanggal_selesai'    => '2030-08-31',
            'semester_berjalan'  => 1,
            'status_studi'       => 'Sedang Studi',
            'file_sk'            => $fileSk,
            'keterangan'         => 'Riset fokus pada Digital Health in Emergency Nursing',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('tugas-belajar.store'), $payload);
        $response->assertRedirect(route('tugas-belajar.index'));

        $this->assertDatabaseHas('tugas_belajar', [
            'pegawai_id'    => $this->pegawai->id,
            'jenjang_studi' => 'S3',
            'status_studi'  => 'Sedang Studi',
        ]);

        // Pastikan status_pegawai berubah menjadi Tugas Belajar
        $this->pegawai->refresh();
        $this->assertEquals(Pegawai::STATUS_TUGAS_BELAJAR, $this->pegawai->status_pegawai);

        $tubel = TugasBelajar::where('pegawai_id', $this->pegawai->id)->first();
        $this->assertNotNull($tubel->file_sk);
        Storage::disk('local')->assertExists($tubel->file_sk);
    }

    public function test_admin_can_update_status_to_lulus_and_auto_restores_pegawai_status_to_aktif(): void
    {
        $tubel = TugasBelajar::create([
            'pegawai_id'         => $this->pegawai->id,
            'jenis_pengembangan' => 'Tugas Belajar',
            'jenjang_studi'      => 'S3',
            'program_studi'      => 'Doktor Keperawatan',
            'perguruan_tinggi'   => 'Universitas Indonesia',
            'negara'             => 'Indonesia',
            'sumber_pembiayaan'  => 'Beasiswa BPI / Kemendikbud',
            'nomor_sk'           => '123/UN19/KP/2023',
            'tanggal_mulai'      => '2023-09-01',
            'tanggal_selesai'    => '2026-08-31',
            'semester_berjalan'  => 6,
            'status_studi'       => 'Sedang Studi',
        ]);

        $this->pegawai->update(['status_pegawai' => Pegawai::STATUS_TUGAS_BELAJAR]);

        $updatePayload = [
            'pegawai_id'         => $this->pegawai->id,
            'jenis_pengembangan' => 'Tugas Belajar',
            'jenjang_studi'      => 'S3',
            'program_studi'      => 'Doktor Keperawatan',
            'perguruan_tinggi'   => 'Universitas Indonesia',
            'negara'             => 'Indonesia',
            'sumber_pembiayaan'  => 'Beasiswa BPI / Kemendikbud',
            'nomor_sk'           => '123/UN19/KP/2023',
            'tanggal_mulai'      => '2023-09-01',
            'tanggal_selesai'    => '2026-08-31',
            'semester_berjalan'  => 6,
            'status_studi'       => 'Lulus',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('tugas-belajar.update', $tubel->id), $updatePayload);
        $response->assertRedirect(route('tugas-belajar.index'));

        $this->assertDatabaseHas('tugas_belajar', [
            'id'           => $tubel->id,
            'status_studi' => 'Lulus',
        ]);

        // Status pegawai harus kembali menjadi Aktif
        $this->pegawai->refresh();
        $this->assertEquals(Pegawai::STATUS_AKTIF, $this->pegawai->status_pegawai);
    }

    public function test_admin_can_delete_tugas_belajar_and_restores_pegawai_status(): void
    {
        $tubel = TugasBelajar::create([
            'pegawai_id'         => $this->pegawai->id,
            'jenis_pengembangan' => 'Tugas Belajar',
            'jenjang_studi'      => 'S3',
            'program_studi'      => 'Doktor Keperawatan',
            'perguruan_tinggi'   => 'Universitas Indonesia',
            'negara'             => 'Indonesia',
            'sumber_pembiayaan'  => 'Beasiswa BPI / Kemendikbud',
            'nomor_sk'           => '123/UN19/KP/2023',
            'tanggal_mulai'      => '2023-09-01',
            'tanggal_selesai'    => '2026-08-31',
            'semester_berjalan'  => 6,
            'status_studi'       => 'Sedang Studi',
        ]);

        $this->pegawai->update(['status_pegawai' => Pegawai::STATUS_TUGAS_BELAJAR]);

        $response = $this->actingAs($this->adminUser)->delete(route('tugas-belajar.destroy', $tubel->id));
        $response->assertRedirect(route('tugas-belajar.index'));

        $this->assertDatabaseMissing('tugas_belajar', ['id' => $tubel->id]);

        $this->pegawai->refresh();
        $this->assertEquals(Pegawai::STATUS_AKTIF, $this->pegawai->status_pegawai);
    }

    public function test_unauthorized_user_cannot_create_or_delete_tugas_belajar(): void
    {
        $payload = [
            'pegawai_id'         => $this->pegawai->id,
            'jenis_pengembangan' => 'Tugas Belajar',
            'jenjang_studi'      => 'S3',
            'program_studi'      => 'Doktor Keperawatan',
            'perguruan_tinggi'   => 'UI',
            'negara'             => 'Indonesia',
            'sumber_pembiayaan'  => 'Mandiri / Swadana',
            'nomor_sk'           => '123/UN19/KP/2026',
            'tanggal_mulai'      => '2026-09-01',
            'tanggal_selesai'    => '2029-08-31',
            'semester_berjalan'  => 1,
            'status_studi'       => 'Sedang Studi',
        ];

        // Pegawai role tidak boleh membuat tugas belajar
        $response = $this->actingAs($this->pegawaiUser)->post(route('tugas-belajar.store'), $payload);
        $response->assertStatus(403);
    }

    public function test_pegawai_profile_show_and_pdf_display_tugas_belajar(): void
    {
        TugasBelajar::create([
            'pegawai_id'         => $this->pegawai->id,
            'jenis_pengembangan' => 'Tugas Belajar',
            'jenjang_studi'      => 'S3',
            'program_studi'      => 'Doktor Keperawatan Klinis',
            'perguruan_tinggi'   => 'Universitas Gadjah Mada',
            'negara'             => 'Indonesia',
            'sumber_pembiayaan'  => 'Beasiswa LPDP',
            'nomor_sk'           => '555/UN19/KP/2025',
            'tanggal_mulai'      => '2025-09-01',
            'tanggal_selesai'    => '2028-08-31',
            'semester_berjalan'  => 2,
            'status_studi'       => 'Sedang Studi',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('pegawai.show', $this->pegawai->id));
        $response->assertStatus(200);
        $response->assertSee('Tugas Belajar');
        $response->assertSee('Doktor Keperawatan Klinis');

        $pdfResponse = $this->actingAs($this->adminUser)->get(route('pegawai.download-pdf', $this->pegawai->id));
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }
}
