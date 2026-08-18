<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\MutasiPegawai;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected User $stafUser;

    protected Pegawai $pegawai;
    protected UnitKerja $unit;
    protected Jabatan $jabatan;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        $stafRole     = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);
        $this->stafUser     = User::factory()->create(['role_id' => $stafRole->id]);

        $this->unit    = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $this->jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru']);

        $this->pegawai = Pegawai::create([
            'nip'           => '199001012015011001',
            'nama'          => 'Pegawai Auth Test',
            'unit_kerja_id' => $this->unit->id,
            'jabatan_id'    => $this->jabatan->id,
        ]);
    }

    /**
     * TEST 1 — GUEST REDIRECTS TO LOGIN (AUTHENTICATION BOUNDARY)
     */
    public function test_guest_is_redirected_to_login_on_all_protected_endpoints(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/pegawai')->assertRedirect('/login');
        $this->get('/kgb')->assertRedirect('/login');
        $this->get('/kenaikan-pangkat')->assertRedirect('/login');
        $this->get('/mutasi-pegawai')->assertRedirect('/login');
        $this->get('/reports/duk/pdf')->assertRedirect('/login');
    }

    /**
     * TEST 2 — ADMIN FULL ACCESS
     */
    public function test_admin_has_full_access_to_management_and_process_routes(): void
    {
        $this->actingAs($this->adminUser)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/pegawai')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/pegawai/create')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/kgb')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/kenaikan-pangkat')->assertStatus(200);
        $this->actingAs($this->adminUser)->get('/mutasi-pegawai')->assertStatus(200);
    }

    /**
     * TEST 3 — PIMPINAN READ-ONLY ACCESS & WRITE DENIAL (VERTICAL ESCALATION PREVENTION)
     */
    public function test_pimpinan_has_read_only_access_and_is_denied_write_operations(): void
    {
        // Read-only ALLOWED
        $this->actingAs($this->pimpinanUser)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get('/pegawai')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get('/duk')->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get("/pegawai/{$this->pegawai->id}")->assertStatus(200);
        $this->actingAs($this->pimpinanUser)->get('/reports/duk/pdf')->assertStatus(200);

        // Write DENIED 403
        $this->actingAs($this->pimpinanUser)->get('/pegawai/create')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post('/pegawai', [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get('/kgb')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post("/kgb/proses/{$this->pegawai->id}")->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get('/kenaikan-pangkat')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post("/kenaikan-pangkat/proses/{$this->pegawai->id}")->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get('/mutasi-pegawai')->assertStatus(403);
    }

    /**
     * TEST 4 — STAF USER ACCESS DENIAL ON PRIVILEGED ROUTES
     */
    public function test_staf_user_is_denied_on_admin_and_monitoring_routes(): void
    {
        $this->actingAs($this->stafUser)->get('/pegawai/create')->assertStatus(403);
        $this->actingAs($this->stafUser)->get('/kgb')->assertStatus(403);
        $this->actingAs($this->stafUser)->get('/kenaikan-pangkat')->assertStatus(403);
        $this->actingAs($this->stafUser)->get('/mutasi-pegawai')->assertStatus(403);
        $this->actingAs($this->stafUser)->get('/duk')->assertStatus(403);
    }

    /**
     * TEST 5 — IDOR NOTIFICATION PROTECTION
     */
    public function test_user_cannot_access_or_mark_other_users_notification(): void
    {
        $this->stafUser->notify(new \App\Notifications\KgbDueDateNotification(collect([['id' => 1]])));
        $notif = $this->stafUser->unreadNotifications()->first();

        // Admin cannot read stafUser's notification via endpoint
        $this->actingAs($this->adminUser)
            ->get("/notifications/{$notif->id}/read")
            ->assertStatus(404);
    }

    /**
     * TEST 6 — IDOR PRIVATE DOCUMENT STREAMING PROTECTION
     */
    public function test_unauthorized_staf_cannot_stream_private_document_belonging_to_another_employee(): void
    {
        \Illuminate\Support\Facades\Storage::disk('local')->put('pegawai/sk/sk_secret_123.pdf', 'dummy content');

        $this->pegawai->update(['file_sk_pertama' => 'pegawai/sk/sk_secret_123.pdf']);

        // Staf with different email/nip cannot stream private doc of pegawai
        $this->actingAs($this->stafUser)
            ->get('/document-preview/pegawai/sk/sk_secret_123.pdf')
            ->assertStatus(403);
    }

    /**
     * TEST 7 — HTTP METHOD BYPASS PREVENTION
     */
    public function test_http_method_bypass_is_prevented_by_router(): void
    {
        // GET on POST-only route /kgb/proses/{id}
        $this->actingAs($this->adminUser)
            ->get("/kgb/proses/{$this->pegawai->id}")
            ->assertStatus(405); // Method Not Allowed
    }
}
