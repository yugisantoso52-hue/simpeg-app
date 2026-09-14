<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $stafUser;

    protected Pegawai $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $stafRole  = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);

        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        $this->stafUser  = User::factory()->create(['role_id' => $stafRole->id]);

        $unit = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru']);

        $this->pegawai = Pegawai::create([
            'nip'           => '199001012015011001',
            'nama'          => 'Pegawai Web Security Test',
            'unit_kerja_id' => $unit->id,
            'jabatan_id'    => $jabatan->id,
        ]);
    }

    /**
     * TEST 1 — HTTP METHOD ENFORCEMENT (405 METHOD NOT ALLOWED)
     */
    public function test_http_method_enforcement_returns_405(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get("/kgb/proses/{$this->pegawai->id}");

        $response->assertStatus(405);
    }

    /**
     * TEST 2 — XSS PROTECTION IN BLADE OUTPUT ESCAPING
     */
    public function test_xss_payloads_in_employee_name_are_safely_escaped_in_blade(): void
    {
        $xssPegawai = Pegawai::create([
            'nip'  => '199001012015011099',
            'nama' => "<script>alert('XSS_SECURITY_TEST')</script>",
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get("/pegawai/{$xssPegawai->id}");

        $response->assertStatus(200);
        $response->assertDontSee("<script>alert('XSS_SECURITY_TEST')</script>", false);
        $response->assertSee("&lt;script&gt;alert(&#039;XSS_SECURITY_TEST&#039;)&lt;/script&gt;", false);
    }

    /**
     * TEST 3 — SQL INJECTION RESISTANCE IN SEARCH & FILTER PARAMETERS
     */
    public function test_sql_injection_payloads_in_search_parameters_do_not_cause_sql_errors(): void
    {
        $sqlPayload = "' OR '1'='1' -- ";

        $response = $this->actingAs($this->adminUser)
            ->get('/pegawai?search=' . urlencode($sqlPayload));

        $response->assertStatus(200);
        $response->assertDontSee('SQLSTATE', false);
        $response->assertDontSee('Syntax error', false);
    }

    /**
     * TEST 4 — SESSION INVALIDATION ON LOGOUT
     */
    public function test_session_invalidation_on_logout(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * TEST 5 — ROUTE EXPOSURE PROTECTION FOR GUESTS
     */
    public function test_protected_routes_are_hidden_from_guests(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/pegawai')->assertRedirect('/login');
        $this->get('/kgb')->assertRedirect('/login');
        $this->get('/kenaikan-pangkat')->assertRedirect('/login');
        $this->get('/mutasi-pegawai')->assertRedirect('/login');
        $this->get('/reports/duk/pdf')->assertRedirect('/login');
    }

    /**
     * TEST 6 — ERROR RESPONSE SECURITY DOES NOT LEAK INTERNAL PATHS OR SQL TRACES
     */
    public function test_error_response_does_not_leak_internal_paths_or_sql_details(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/pegawai/99999999');

        $response->assertStatus(404);
        $content = $response->getContent();

        $this->assertStringNotContainsString('C:\laragon', $content);
        $this->assertStringNotContainsString('storage_path', $content);
        $this->assertStringNotContainsString('SQLSTATE', $content);
    }
}
