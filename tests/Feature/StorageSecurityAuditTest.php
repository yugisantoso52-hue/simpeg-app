<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageSecurityAuditTest extends TestCase
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

        $this->pegawai = Pegawai::create([
            'nip'  => '199001012015011001',
            'nama' => 'Pegawai Storage Test',
        ]);

        Storage::disk('local')->put('pegawai/sk/sk_valid_test.pdf', 'PDF Content Security Test');
    }

    /**
     * TEST 1 — GUEST CANNOT ACCESS PRIVATE DOCUMENTS
     */
    public function test_guest_cannot_access_private_documents(): void
    {
        $response = $this->get('/document-preview/pegawai/sk/sk_valid_test.pdf');

        $response->assertRedirect('/login');
    }

    /**
     * TEST 2 — AUTHORIZED ADMIN CAN STREAM PRIVATE DOCUMENT
     */
    public function test_authorized_admin_can_stream_private_document(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/pegawai/sk/sk_valid_test.pdf');

        $response->assertStatus(200);
    }

    /**
     * TEST 3 — UNAUTHORIZED STAF CANNOT STREAM UNOWNED DOCUMENT (IDOR)
     */
    public function test_unauthorized_staf_cannot_stream_unowned_document(): void
    {
        $response = $this->actingAs($this->stafUser)
            ->get('/document-preview/pegawai/sk/sk_valid_test.pdf');

        $response->assertStatus(403);
    }

    /**
     * TEST 4 — PATH TRAVERSAL REJECTED (403/404)
     */
    public function test_path_traversal_attempts_are_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/../../.env');

        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * TEST 5 — ENCODED PATH TRAVERSAL REJECTED
     */
    public function test_encoded_path_traversal_is_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/%2e%2e%2f%2e%2e%2f.env');

        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * TEST 6 — ABSOLUTE PATH INJECTION REJECTED
     */
    public function test_absolute_path_injection_is_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/C:/Windows/System32/drivers/etc/hosts');

        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * TEST 7 — NULL BYTE INJECTION REJECTED
     */
    public function test_null_byte_path_injection_is_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/pegawai/sk/sk_valid_test.pdf%00.php');

        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * TEST 8 — SENSITIVE SYSTEM FILES CANNOT BE STREAMED
     */
    public function test_sensitive_system_files_cannot_be_streamed(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/database.sqlite');

        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * TEST 9 — NONEXISTENT FILE RETURNS SAFE 404 WITHOUT PATH LEAKAGE
     */
    public function test_nonexistent_file_returns_safe_404(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/document-preview/nonexistent_file_xyz_999.pdf');

        $response->assertStatus(404);
        $this->assertStringNotContainsString('C:\laragon', $response->getContent());
        $this->assertStringNotContainsString('storage_path', $response->getContent());
    }

    /**
     * TEST 10 — PRIVATE DOCUMENT IS NOT DIRECTLY ACCESSIBLE IN PUBLIC STORAGE URL
     */
    public function test_private_document_is_not_directly_accessible_in_public_web_root(): void
    {
        $response = $this->get('/storage/pegawai/sk/sk_valid_test.pdf');
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    /**
     * TEST 11 — DANGEROUS EXECUTABLE EXTENSIONS ARE REJECTED ON UPLOAD
     */
    public function test_dangerous_executable_extensions_are_rejected_on_upload(): void
    {
        Storage::fake('local');

        $phpFile = UploadedFile::fake()->create('malicious_shell.php', 100, 'text/x-php');

        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', [
                'nip'              => '199001012015011099',
                'nama'             => 'Pegawai Malicious Test',
                'file_sk_pertama'  => $phpFile,
            ]);

        $response->assertSessionHasErrors(['file_sk_pertama']);
    }

    /**
     * TEST 12 — OVERSIZED FILES ARE REJECTED ON UPLOAD (>2MB)
     */
    public function test_oversized_files_are_rejected_on_upload(): void
    {
        Storage::fake('local');

        $hugeFile = UploadedFile::fake()->create('huge_document.pdf', 3000, 'application/pdf');

        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', [
                'nip'             => '199001012015011098',
                'nama'            => 'Pegawai Huge File Test',
                'file_sk_pertama' => $hugeFile,
            ]);

        $response->assertSessionHasErrors(['file_sk_pertama']);
    }

    /**
     * TEST 13 — UNAUTHORIZED STAF CANNOT DELETE PEGAWAI DOKUMEN VIA PEGAWAI DESTROY
     */
    public function test_unauthorized_staf_cannot_delete_pegawai(): void
    {
        $response = $this->actingAs($this->stafUser)
            ->delete("/pegawai/{$this->pegawai->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('pegawai', ['id' => $this->pegawai->id]);
    }
}
