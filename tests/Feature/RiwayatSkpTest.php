<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RiwayatSkp;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiwayatSkpTest extends TestCase
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
            'nip'                 => '198505052010121003',
            'nama'                => 'Ns. Hendra Kurniawan, M.Kep',
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

    public function test_admin_and_pimpinan_can_view_skp_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('riwayat-skp.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengarsipan SKP');

        $responsePimpinan = $this->actingAs($this->pimpinanUser)->get(route('riwayat-skp.index'));
        $responsePimpinan->assertStatus(200);
    }

    public function test_admin_can_create_skp_with_both_files(): void
    {
        $fileRencana = UploadedFile::fake()->create('rencana_skp_2026.pdf', 300, 'application/pdf');
        $fileEvaluasi = UploadedFile::fake()->create('evaluasi_skp_2026.pdf', 500, 'application/pdf');

        $payload = [
            'pegawai_id'        => $this->pegawai->id,
            'tahun'             => 2026,
            'predikat_kinerja'  => 'Sangat Baik',
            'file_rencana_skp'  => $fileRencana,
            'file_evaluasi_skp' => $fileEvaluasi,
            'pejabat_penilai'   => 'Dekan Fakultas Keperawatan',
            'keterangan'        => 'Kinerja triwulan 1-4 melampaui ekspektasi',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('riwayat-skp.store'), $payload);
        $response->assertRedirect(route('riwayat-skp.index'));

        $this->assertDatabaseHas('riwayat_skp', [
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => 2026,
            'predikat_kinerja' => 'Sangat Baik',
            'pejabat_penilai'  => 'Dekan Fakultas Keperawatan',
        ]);

        $skp = RiwayatSkp::where('pegawai_id', $this->pegawai->id)->first();
        $this->assertTrue($skp->is_lengkap);
        Storage::disk('local')->assertExists($skp->file_rencana_skp);
        Storage::disk('local')->assertExists($skp->file_evaluasi_skp);
    }

    public function test_cannot_create_duplicate_skp_for_same_pegawai_and_year(): void
    {
        RiwayatSkp::create([
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => 2025,
            'predikat_kinerja' => 'Baik',
        ]);

        $payload = [
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => 2025,
            'predikat_kinerja' => 'Sangat Baik',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('riwayat-skp.store'), $payload);
        $response->assertSessionHasErrors('tahun');
    }

    public function test_admin_can_update_skp_files_and_predikat(): void
    {
        $skp = RiwayatSkp::create([
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => 2025,
            'predikat_kinerja' => 'Baik',
        ]);

        $fileEvaluasi = UploadedFile::fake()->create('evaluasi_2025_rev.pdf', 400, 'application/pdf');

        $updatePayload = [
            'pegawai_id'        => $this->pegawai->id,
            'tahun'             => 2025,
            'predikat_kinerja'  => 'Sangat Baik',
            'file_evaluasi_skp' => $fileEvaluasi,
            'pejabat_penilai'   => 'Wadek II Fak. Keperawatan',
        ];

        $response = $this->actingAs($this->adminUser)->put(route('riwayat-skp.update', $skp->id), $updatePayload);
        $response->assertRedirect(route('riwayat-skp.index'));

        $this->assertDatabaseHas('riwayat_skp', [
            'id'               => $skp->id,
            'predikat_kinerja' => 'Sangat Baik',
            'pejabat_penilai'  => 'Wadek II Fak. Keperawatan',
        ]);
    }

    public function test_admin_can_delete_skp(): void
    {
        $skp = RiwayatSkp::create([
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => 2024,
            'predikat_kinerja' => 'Baik',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('riwayat-skp.destroy', $skp->id));
        $response->assertRedirect(route('riwayat-skp.index'));

        $this->assertDatabaseMissing('riwayat_skp', ['id' => $skp->id]);
    }

    public function test_unauthorized_user_cannot_manage_skp(): void
    {
        $payload = [
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => 2026,
            'predikat_kinerja' => 'Baik',
        ];

        $response = $this->actingAs($this->pegawaiUser)->post(route('riwayat-skp.store'), $payload);
        $response->assertStatus(403);
    }

    public function test_pegawai_profile_show_and_pdf_display_skp_2_years(): void
    {
        $currentYear = now()->year;
        $prevYear = $currentYear - 1;

        RiwayatSkp::create([
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => $currentYear,
            'predikat_kinerja' => 'Sangat Baik',
        ]);

        RiwayatSkp::create([
            'pegawai_id'       => $this->pegawai->id,
            'tahun'            => $prevYear,
            'predikat_kinerja' => 'Baik',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('pegawai.show', $this->pegawai->id));
        $response->assertStatus(200);
        $response->assertSee('Pengarsipan SKP');
        $response->assertSee((string)$currentYear);
        $response->assertSee((string)$prevYear);

        $pdfResponse = $this->actingAs($this->adminUser)->get(route('pegawai.download-pdf', $this->pegawai->id));
        $pdfResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }
}
