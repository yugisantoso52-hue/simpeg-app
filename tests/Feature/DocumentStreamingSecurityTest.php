<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentStreamingSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        $adminRole = Role::create([
            'name'         => 'admin',
            'display_name' => 'Admin Kepegawaian',
        ]);

        $pimpinanRole = Role::create([
            'name'         => 'pimpinan',
            'display_name' => 'Pimpinan',
        ]);

        $stafRole = Role::create([
            'name'         => 'staf',
            'display_name' => 'Staf Biasa',
        ]);

        // Create Admin User
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Create Regular User
        $this->regularUser = User::factory()->create([
            'role_id' => $stafRole->id,
        ]);

        // Put dummy test document in storage/app/private
        Storage::disk('local')->put('pegawai/sk/sk_test_dummy.pdf', 'PDF Dummy Content');
    }

    /**
     * Test 1: Authenticated Admin can open authorized private document.
     */
    public function test_authenticated_admin_can_stream_private_document(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/pegawai/sk/sk_test_dummy.pdf');

        $response->assertStatus(200);
    }

    /**
     * Test 2: Guest user cannot open documents and is redirected or unauthenticated.
     */
    public function test_guest_cannot_stream_private_document(): void
    {
        $response = $this->get('/document-preview/pegawai/sk/sk_test_dummy.pdf');

        $response->assertRedirect('/login');
    }

    /**
     * Test 3: Regular user without permission cannot open unowned document (IDOR protection).
     */
    public function test_regular_user_cannot_access_unowned_document(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get('/document-preview/pegawai/sk/sk_test_dummy.pdf');

        $response->assertStatus(403);
    }

    /**
     * Test 4: Path traversal attempts are rejected (403/404).
     */
    public function test_path_traversal_attempts_are_rejected(): void
    {
        // Traversal ../
        $response1 = $this->actingAs($this->adminUser)
            ->get('/document-preview/../../.env');
        $this->assertTrue(in_array($response1->status(), [403, 404]));

        // Encoded Traversal %2e%2e%2f
        $response2 = $this->actingAs($this->adminUser)
            ->get('/document-preview/%2e%2e%2f%2e%2e%2f.env');
        $this->assertTrue(in_array($response2->status(), [403, 404]));

        // Absolute Windows path
        $response3 = $this->actingAs($this->adminUser)
            ->get('/document-preview/C:/Windows/System32/drivers/etc/hosts');
        $this->assertTrue(in_array($response3->status(), [403, 404]));
    }

    /**
     * Test 5: Files outside private root (e.g. .env, source code) are never served.
     */
    public function test_sensitive_files_outside_private_root_never_served(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/../.env');

        $response->assertStatus(403);
    }

    /**
     * Test 6: Non-existent file returns 404.
     */
    public function test_non_existent_file_returns_404(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/non_existent_file_999.pdf');

        $response->assertStatus(404);
    }

    /**
     * Test 7: Error responses do not leak internal stack traces or paths.
     */
    public function test_error_response_does_not_leak_sensitive_info(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/../../.env');

        $content = $response->getContent();
        $this->assertStringNotContainsString('storage_path', $content);
        $this->assertStringNotContainsString('C:\laragon', $content);
    }
}
