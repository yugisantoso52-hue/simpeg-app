<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use App\Notifications\KgbDueDateNotification;
use App\Notifications\KpDueDateNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SensitiveDataLoggingAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
    }

    /**
     * TEST 1 — PASSWORDS AND SENSITIVE CREDENTIALS ARE NOT LOGGED
     */
    public function test_passwords_and_credentials_are_not_logged(): void
    {
        Log::shouldReceive('info')
            ->never()
            ->withArgs(function ($message) {
                return str_contains($message, 'secret_password_123');
            });

        $this->post('/login', [
            'email'    => $this->adminUser->email,
            'password' => 'secret_password_123',
        ]);
    }

    /**
     * TEST 2 — APP_KEY AND DB CREDENTIALS ARE NOT EXPOSED IN HTTP RESPONSES
     */
    public function test_app_key_and_db_credentials_are_not_exposed_in_http_responses(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/nonexistent-route-for-security-test-999');

        $response->assertStatus(404);
        $content = $response->getContent();

        $appKey = env('APP_KEY');
        if (!empty($appKey)) {
            $this->assertStringNotContainsString($appKey, $content);
        }

        $this->assertStringNotContainsString('DB_PASSWORD', $content);
        $this->assertStringNotContainsString('password', strtolower($content));
    }

    /**
     * TEST 3 — SQLSTATE AND DATABASE TRACES ARE NOT EXPOSED IN ERROR PAGES
     */
    public function test_sqlstate_and_database_traces_are_not_exposed_in_error_pages(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/pegawai/99999999');

        $response->assertStatus(404);
        $content = $response->getContent();

        $this->assertStringNotContainsString('SQLSTATE', $content);
        $this->assertStringNotContainsString('select * from', strtolower($content));
    }

    /**
     * TEST 4 — NOTIFICATION PAYLOADS DO NOT CONTAIN PASSWORDS, TOKENS, OR PRIVILEGED SECRETS
     */
    public function test_notification_payloads_do_not_contain_secrets(): void
    {
        $kgbNotif = new KgbDueDateNotification(collect([['id' => 1, 'nama' => 'Pegawai Test']]));
        $kpNotif  = new KpDueDateNotification(collect([['id' => 1, 'nama' => 'Pegawai Test']]));

        $kgbArray = $kgbNotif->toArray($this->adminUser);
        $kpArray  = $kpNotif->toArray($this->adminUser);

        $kgbJson = json_encode($kgbArray);
        $kpJson  = json_encode($kpArray);

        $this->assertStringNotContainsString('password', $kgbJson);
        $this->assertStringNotContainsString('token', $kgbJson);
        $this->assertStringNotContainsString('password', $kpJson);
        $this->assertStringNotContainsString('token', $kpJson);
    }

    /**
     * TEST 5 — PRIVATE DOCUMENT RESPONSE HEADERS AND CONTENT DO NOT LEAK PHYSICAL STORAGE PATHS
     */
    public function test_private_document_response_does_not_leak_storage_paths(): void
    {
        \Illuminate\Support\Facades\Storage::disk('local')->put('pegawai/sk/test_doc.pdf', 'PDF Dummy Content');

        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/pegawai/sk/test_doc.pdf');

        $response->assertStatus(200);

        // Header check
        foreach ($response->headers->all() as $header => $values) {
            foreach ($values as $value) {
                $this->assertStringNotContainsString('C:\laragon', $value);
                $this->assertStringNotContainsString('storage/app/private', $value);
            }
        }
    }

    /**
     * TEST 6 — VALIDATION ERRORS DO NOT LEAK SENSITIVE FIELDS OR STACK TRACES
     */
    public function test_validation_errors_do_not_leak_stack_traces(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', []);

        $response->assertSessionHasErrors(['nip', 'unit_kerja_id', 'jabatan_id']);
        $content = session('errors')->getBag('default')->first('nip');

        $this->assertStringNotContainsString('Exception', $content);
        $this->assertStringNotContainsString('C:\laragon', $content);
    }
}
