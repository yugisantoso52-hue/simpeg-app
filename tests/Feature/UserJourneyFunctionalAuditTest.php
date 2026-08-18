<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Notifications\KgbDueDateNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserJourneyFunctionalAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;

    protected UnitKerja $unit;
    protected Jabatan $jabatan;
    protected Golongan $golongan;
    protected Pegawai $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);

        $this->unit     = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $this->jabatan  = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru Madya']);
        $this->golongan = Golongan::create(['nama_golongan' => 'III/c', 'nama_pangkat' => 'Penata']);

        $this->pegawai = Pegawai::create([
            'nip'                  => '199001012015011001',
            'nama'                 => 'Pegawai Journey Test',
            'status_pegawai'       => 'Aktif',
            'jenis_pegawai'        => 'PNS',
            'status_asn'           => 'ASN',
            'unit_kerja_id'        => $this->unit->id,
            'jabatan_id'           => $this->jabatan->id,
            'golongan_id'          => $this->golongan->id,
            'tmt_pangkat_terakhir' => '2022-01-01',
            'tmt_kgb_terakhir'     => '2024-01-01',
            'kgb_berikutnya'       => Carbon::today()->addMonth()->toDateString(),
            'kp_berikutnya'        => Carbon::today()->addMonth()->toDateString(),
        ]);
    }

    /**
     * TEST 1 — ADMIN USER JOURNEY END-TO-END FUNCTIONAL NAVIGATION
     */
    public function test_admin_user_journey_end_to_end_functional_navigation(): void
    {
        // 1. Dashboard
        $this->actingAs($this->adminUser)->get('/dashboard')->assertStatus(200);

        // 2. Master Data
        $this->actingAs($this->adminUser)->get('/unit-kerja')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/jabatan')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/golongan')->assertStatus(200);

        // 3. Pegawai Management
        $this->actingAs($this->adminUser)->get('/pegawai')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/pegawai/create')->assertStatus(200);
        $this->actingAs($this->adminUser)->get("/pegawai/{$this->pegawai->id}")->assertStatus(200);
        $this->actingAs($this->adminUser)->get("/pegawai/{$this->pegawai->id}/edit")->assertStatus(200);

        // 4. Riwayat Pages
        $this->actingAs($this->adminUser)->get('/riwayat-pendidikan')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/riwayat-pangkat')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/riwayat-jabatan')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/mutasi-pegawai')->assertStatus(200);

        // 5. KGB, KP, Satyalancana
        $this->actingAs($this->adminUser)->get('/kgb')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/kenaikan-pangkat')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/satyalancana')->assertStatus(200);

        // 6. Reports & DUK
        $this->actingAs($this->adminUser)->get('/duk')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/reports/duk/pdf')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/reports/duk/excel')->assertStatus(200);

        // 7. Logout
        $this->actingAs($this->adminUser)->post('/logout')->assertRedirect('/');
    }

    /**
     * TEST 2 — PIMPINAN USER JOURNEY FUNCTIONAL NAVIGATION
     */
    public function test_pimpinan_user_journey_functional_navigation(): void
    {
        $this->actingAs($this->pimpinanUser)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get('/pegawai')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get("/pegawai/{$this->pegawai->id}")->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get('/duk')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get('/reports/duk/pdf')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get('/reports/duk/excel')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->post('/logout')->assertRedirect('/');
    }

    /**
     * TEST 3 — GUEST FLOW SECURITY AND REDIRECTION
     */
    public function test_guest_flow_security_and_redirection(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/pegawai')->assertRedirect('/login');
        $this->get('/duk')->assertRedirect('/login');
    }

    /**
     * TEST 4 — MASTER DATA DROPDOWN RENDERING IN PEGAWAI CREATE FORM
     */
    public function test_master_data_dropdown_rendering_in_pegawai_create_form(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/pegawai/create');

        $response->assertStatus(200);
        $response->assertSee('Dinas Pendidikan');
        $response->assertSee('Guru Madya');
        $response->assertSee('III/c');
    }

    /**
     * TEST 5 — PEGAWAI SEARCH AND FILTER FUNCTIONAL AUDIT
     */
    public function test_pegawai_search_and_filter_functional_audit(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Pegawai::create([
                'nip'           => '19900101201501108' . $i,
                'nama'          => "Pegawai Search {$i}",
                'unit_kerja_id' => $this->unit->id,
                'jabatan_id'    => $this->jabatan->id,
            ]);
        }

        $response = $this->actingAs($this->adminUser)
            ->get('/pegawai?search=Search+3');

        $response->assertStatus(200);
        $response->assertSee('Pegawai Search 3');
        $response->assertDontSee('Pegawai Search 1');
    }

    /**
     * TEST 6 — FORM VALIDATION UI ERROR HANDLING
     */
    public function test_form_validation_ui_error_handling(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', []);

        $response->assertSessionHasErrors(['nip', 'unit_kerja_id', 'jabatan_id']);
        $response->assertStatus(302);
    }

    /**
     * TEST 7 — REPORT AND EXPORT FILE GENERATION FUNCTIONAL AUDIT
     */
    public function test_report_and_export_file_generation_functional_audit(): void
    {
        // 1. DUK PDF
        $responseDukPdf = $this->actingAs($this->adminUser)->get('/reports/duk/pdf');
        $responseDukPdf->assertStatus(200);
        $responseDukPdf->assertHeader('content-type', 'application/pdf');

        // 2. DUK Excel
        $responseDukExcel = $this->actingAs($this->adminUser)->get('/reports/duk/excel');
        $responseDukExcel->assertStatus(200);

        // 3. SK KGB PDF
        $responseKgbPdf = $this->actingAs($this->adminUser)->get("/reports/kgb/{$this->pegawai->id}/pdf");
        $responseKgbPdf->assertStatus(200);
        $responseKgbPdf->assertHeader('content-type', 'application/pdf');

        // 4. Profil PDF
        $responseProfilPdf = $this->actingAs($this->adminUser)->get("/pegawai/{$this->pegawai->id}/download-pdf");
        $responseProfilPdf->assertStatus(200);
        $responseProfilPdf->assertHeader('content-type', 'application/pdf');
    }

    /**
     * TEST 8 — NOTIFICATION LIST, MARK READ, AND REDIRECTION JOURNEY
     */
    public function test_notification_list_mark_read_and_redirection_journey(): void
    {
        $this->adminUser->notify(new KgbDueDateNotification(collect([['id' => $this->pegawai->id]])));

        $notif = $this->adminUser->unreadNotifications()->first();
        $this->assertNotNull($notif);

        // Read and redirect
        $this->actingAs($this->adminUser)
            ->get("/notifications/{$notif->id}/read")
            ->assertRedirect($notif->data['url']);

        // Mark all as read
        $this->actingAs($this->adminUser)
            ->post('/notifications/mark-all-read')
            ->assertRedirect();
    }
}
