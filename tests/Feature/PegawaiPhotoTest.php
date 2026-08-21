<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PegawaiPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);

        $this->adminUser = User::factory()->create([
            'role_id'              => $adminRole->id,
            'must_change_password' => false,
        ]);
    }

    /**
     * Test photo upload and streaming via dedicated route
     */
    public function test_can_upload_and_stream_pegawai_photo(): void
    {
        $photo = UploadedFile::fake()->image('pasfoto.jpg', 300, 400);

        $response = $this->actingAs($this->adminUser)->post(route('pegawai.store'), [
            'nip'  => '199201012020011001',
            'nama' => 'Foto Tester',
            'foto' => $photo,
        ]);

        $response->assertRedirect(route('pegawai.index'));

        $pegawai = Pegawai::where('nip', '199201012020011001')->first();
        $this->assertNotNull($pegawai);
        $this->assertNotNull($pegawai->foto);
        $this->assertTrue(Storage::disk('public')->exists($pegawai->foto));

        // Test streaming via route
        $fotoResponse = $this->actingAs($this->adminUser)->get(route('pegawai.foto', $pegawai->id));
        $fotoResponse->assertStatus(200);

        // Test show view contains foto_url
        $showResponse = $this->actingAs($this->adminUser)->get(route('pegawai.show', $pegawai->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee(route('pegawai.foto', $pegawai->id));
    }

    /**
     * Test fallback when no photo exists
     */
    public function test_photo_route_fallback_when_no_photo(): void
    {
        $pegawai = Pegawai::create([
            'nip'  => '199201012020011002',
            'nama' => 'Tanpa Foto',
        ]);

        $fotoResponse = $this->actingAs($this->adminUser)->get(route('pegawai.foto', $pegawai->id));
        $fotoResponse->assertRedirect();
        $this->assertStringContainsString('ui-avatars.com', $fotoResponse->headers->get('Location'));
    }
}
