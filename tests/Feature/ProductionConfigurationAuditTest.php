<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionConfigurationAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TEST 1 — APP_KEY IS PRESENT AND NOT EMPTY
     */
    public function test_app_key_is_present_and_not_empty(): void
    {
        $appKey = config('app.key');
        $this->assertNotEmpty($appKey, 'APP_KEY should be set and non-empty.');
    }

    /**
     * TEST 2 — APP_KEY IS NOT LEAKED IN HTTP ERROR RESPONSES
     */
    public function test_app_key_is_not_leaked_in_http_responses(): void
    {
        $response = $this->get('/nonexistent-test-route-999');
        $response->assertStatus(404);

        $appKey = config('app.key');
        if ($appKey) {
            $this->assertStringNotContainsString($appKey, $response->getContent());
        }
    }

    /**
     * TEST 3 — PRIVATE STORAGE DISK IS NOT PUBLICLY SERVED ON WEB ROOT
     */
    public function test_private_storage_disk_is_not_publicly_accessible(): void
    {
        $response = $this->get('/storage/app/private/.env');
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * TEST 4 — HTTP METHOD ENFORCEMENT STAYS ACTIVE
     */
    public function test_http_method_enforcement_remains_active(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/kgb/proses/1');
        $response->assertStatus(405);
    }
}
