<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\RiwayatPendidikan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiwayatPendidikanTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;
    protected Pegawai $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);

        $this->pegawai = Pegawai::create([
            'nip'  => '199505052020011001',
            'nama' => 'Budi Pendidikan',
        ]);
    }

    public function test_admin_and_pimpinan_can_view_riwayat_pendidikan_index(): void
    {
        RiwayatPendidikan::create([
            'pegawai_id'  => $this->pegawai->id,
            'jenjang'     => 'S1',
            'institusi'   => 'Universitas Indonesia',
            'fakultas'    => 'Ilmu Komputer',
            'jurusan'     => 'Teknik Informatika',
            'tahun_lulus' => 2017,
        ]);

        $this->actingAs($this->adminUser)
            ->get('/riwayat-pendidikan')
            ->assertStatus(200)
            ->assertSee('Universitas Indonesia');

        $this->actingAs($this->pimpinanUser)
            ->get('/riwayat-pendidikan')
            ->assertStatus(200)
            ->assertSee('Universitas Indonesia');
    }

    public function test_admin_can_store_valid_riwayat_pendidikan_with_ijazah_file(): void
    {
        Storage::fake('public');

        $ijazah = UploadedFile::fake()->create('ijazah.pdf', 1000, 'application/pdf');

        $payload = [
            'pegawai_id'  => $this->pegawai->id,
            'jenjang'     => 'S2',
            'institusi'   => 'Institut Teknologi Bandung',
            'fakultas'    => 'STEI',
            'jurusan'     => 'Informatika',
            'tahun_lulus' => 2020,
            'ijazah'      => $ijazah,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/riwayat-pendidikan', $payload);

        $response->assertRedirect(route('riwayat-pendidikan.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('riwayat_pendidikan', [
            'pegawai_id' => $this->pegawai->id,
            'jenjang'    => 'S2',
            'institusi'  => 'Institut Teknologi Bandung',
        ]);
    }

    public function test_admin_can_update_riwayat_pendidikan(): void
    {
        $riwayat = RiwayatPendidikan::create([
            'pegawai_id'  => $this->pegawai->id,
            'jenjang'     => 'S1',
            'institusi'   => 'Universitas Gadjah Mada',
            'fakultas'    => 'MIPA',
            'jurusan'     => 'Keluarga',
            'tahun_lulus' => 2018,
        ]);

        $payload = [
            'pegawai_id'  => $this->pegawai->id,
            'jenjang'     => 'S1',
            'institusi'   => 'Universitas Gadjah Mada',
            'fakultas'    => 'MIPA',
            'jurusan'     => 'Ilmu Komputer Updated',
            'tahun_lulus' => 2018,
        ];

        $response = $this->actingAs($this->adminUser)
            ->put("/riwayat-pendidikan/{$riwayat->id}", $payload);

        $response->assertRedirect(route('riwayat-pendidikan.index'));

        $this->assertDatabaseHas('riwayat_pendidikan', [
            'id'      => $riwayat->id,
            'jurusan' => 'Ilmu Komputer Updated',
        ]);
    }

    public function test_admin_can_delete_riwayat_pendidikan(): void
    {
        $riwayat = RiwayatPendidikan::create([
            'pegawai_id'  => $this->pegawai->id,
            'jenjang'     => 'D3',
            'institusi'   => 'Politeknik Negeri',
            'fakultas'    => 'Teknik',
            'jurusan'     => 'Elektronika',
            'tahun_lulus' => 2015,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete("/riwayat-pendidikan/{$riwayat->id}");

        $response->assertRedirect(route('riwayat-pendidikan.index'));

        $this->assertDatabaseMissing('riwayat_pendidikan', [
            'id' => $riwayat->id,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/riwayat-pendidikan', []);

        $response->assertSessionHasErrors(['pegawai_id', 'jenjang', 'institusi']);
    }

    public function test_pimpinan_has_read_only_access_and_write_denied(): void
    {
        $riwayat = RiwayatPendidikan::create([
            'pegawai_id'  => $this->pegawai->id,
            'jenjang'     => 'S1',
            'institusi'   => 'UNPAD',
            'fakultas'    => 'Hukum',
            'jurusan'     => 'Ilmu Hukum',
            'tahun_lulus' => 2019,
        ]);

        // READ Allowed
        $this->actingAs($this->pimpinanUser)->get('/riwayat-pendidikan')->assertStatus(200);

        // WRITE Denied (403)
        $this->actingAs($this->pimpinanUser)->get('/riwayat-pendidikan/create')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post('/riwayat-pendidikan', [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get("/riwayat-pendidikan/{$riwayat->id}/edit")->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->put("/riwayat-pendidikan/{$riwayat->id}", [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->delete("/riwayat-pendidikan/{$riwayat->id}")->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/riwayat-pendidikan')->assertRedirect('/login');
    }
}
