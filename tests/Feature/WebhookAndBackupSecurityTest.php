<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WebhookAndBackupSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $pegawaiUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pegawaiRole = Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'must_change_password' => false,
        ]);

        $this->pegawaiUser = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Webhook deploy menolak request tanpa autentikasi (403 Forbidden)
     */
    public function test_webhook_rejects_unauthorized_request(): void
    {
        $response = $this->postJson(route('deploy.webhook'), []);
        $response->assertStatus(403);
    }

    /**
     * Webhook deploy menolak signature HMAC palsu/salah (403 Forbidden)
     */
    public function test_webhook_rejects_invalid_signature(): void
    {
        $response = $this->withHeaders([
            'X-Hub-Signature-256' => 'sha256=invalidsignaturehash1234567890abcdef',
        ])->post(route('deploy.webhook'), ['event' => 'push']);

        $response->assertStatus(403);
    }

    /**
     * Webhook deploy menolak metode GET (405 Method Not Allowed)
     */
    public function test_webhook_rejects_get_method(): void
    {
        $response = $this->get('/deploy-webhook');
        $response->assertStatus(405);
    }

    /**
     * Webhook deploy memverifikasi otorisasi dengan signature HMAC-SHA256 valid
     */
    public function test_webhook_authorizes_with_valid_signature_or_key(): void
    {
        config(['services.deploy.webhook_secret' => 'sikap_deploy_sec_2026_unri']);
        $secret = 'sikap_deploy_sec_2026_unri';
        $payload = json_encode(['test' => 'deployment']);
        $signature = 'sha256='.hash_hmac('sha256', $payload, $secret);

        // Request dengan valid HMAC signature lolos otorisasi 403
        // (Status bukan 403, melainkan melanjutkan eksekusi pipeline deploy)
        $response = $this->call(
            'POST',
            '/deploy-webhook',
            [],
            [],
            [],
            [
                'HTTP_X-Hub-Signature-256' => $signature,
                'CONTENT_TYPE' => 'application/json',
            ],
            $payload
        );

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    /**
     * Backup restore menolak file dengan nama path traversal (regex validation)
     */
    public function test_backup_restore_rejects_path_traversal_filename(): void
    {
        // Karakter ';' atau spasi tidak lolos regex whitelist [a-zA-Z0-9_\-\.]+\.sql
        $file = UploadedFile::fake()->createWithContent('backup;rm-rf.sql', 'SELECT 1;');

        $response = $this->actingAs($this->adminUser)
            ->post(route('backup.restore'), [
                'backup_file' => $file,
            ]);

        $response->assertRedirect(route('backup.index'));
        $response->assertSessionHas('error', 'Nama file backup mengandung karakter tidak valid.');
    }

    /**
     * Backup restore menolak file dengan ekstensi selain .sql
     */
    public function test_backup_restore_rejects_non_sql_extension(): void
    {
        $file = UploadedFile::fake()->create('backdoor.php', 100, 'application/x-php');

        $response = $this->actingAs($this->adminUser)
            ->post(route('backup.restore'), [
                'backup_file' => $file,
            ]);

        $response->assertSessionHasErrors('backup_file');
    }

    /**
     * User bukan admin (pegawai biasa) tidak dapat mengakses menu backup (403 Forbidden)
     */
    public function test_regular_pegawai_cannot_access_backup(): void
    {
        $response = $this->actingAs($this->pegawaiUser)->get(route('backup.index'));
        $response->assertStatus(403);
    }
}
