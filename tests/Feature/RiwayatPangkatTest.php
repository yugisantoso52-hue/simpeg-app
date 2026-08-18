<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Pegawai;
use App\Models\RiwayatPangkat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RiwayatPangkatTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $pimpinanUser;

    protected Pegawai $pegawai;
    protected Golongan $golonganAwal;
    protected Golongan $golonganBaru;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole    = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $pimpinanRole = Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);

        $this->adminUser    = User::factory()->create(['role_id' => $adminRole->id]);
        $this->pimpinanUser = User::factory()->create(['role_id' => $pimpinanRole->id]);

        $this->golonganAwal = Golongan::create([
            'nama_golongan' => 'III/a',
            'nama_pangkat'  => 'Penata Muda',
        ]);

        $this->golonganBaru = Golongan::create([
            'nama_golongan' => 'III/b',
            'nama_pangkat'  => 'Penata Muda Tk. I',
        ]);

        $this->pegawai = Pegawai::create([
            'nip'                  => '199303032019031001',
            'nama'                 => 'Rian Pangkat',
            'golongan_id'          => $this->golonganAwal->id,
            'tmt_pangkat_terakhir' => '2020-01-01',
        ]);
    }

    public function test_admin_and_pimpinan_can_view_riwayat_pangkat_index(): void
    {
        RiwayatPangkat::create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganAwal->id,
            'tmt'         => '2020-01-01',
            'status'      => 'aktif',
        ]);

        $this->actingAs($this->adminUser)
            ->get('/riwayat-pangkat')
            ->assertStatus(200)
            ->assertSee('Rian Pangkat');

        $this->actingAs($this->pimpinanUser)
            ->get('/riwayat-pangkat')
            ->assertStatus(200)
            ->assertSee('Rian Pangkat');
    }

    public function test_admin_can_store_riwayat_pangkat_and_updates_pegawai_and_previous_status(): void
    {
        Storage::fake('local');

        $oldPangkat = RiwayatPangkat::create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganAwal->id,
            'tmt'         => '2020-01-01',
            'status'      => 'aktif',
        ]);

        $fileSk = UploadedFile::fake()->create('sk_pangkat_3b.pdf', 800, 'application/pdf');

        $payload = [
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganBaru->id,
            'tmt'         => '2024-01-01',
            'nomor_sk'    => 'SK/2024/001',
            'file_sk'     => $fileSk,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post('/riwayat-pangkat', $payload);

        $response->assertRedirect(route('riwayat-pangkat.index'));
        $response->assertSessionHas('success');

        // Assert previous status set to nonaktif
        $oldPangkat->refresh();
        $this->assertEquals('nonaktif', $oldPangkat->status);

        // Assert master pegawai updated with new tmt_pangkat_terakhir
        $this->pegawai->refresh();
        $this->assertEquals($this->golonganBaru->id, $this->pegawai->golongan_id);
        $this->assertEquals('2024-01-01', $this->pegawai->tmt_pangkat_terakhir->format('Y-m-d'));
        $this->assertEquals('2028-01-01', $this->pegawai->kp_berikutnya->format('Y-m-d'));
    }

    public function test_admin_can_delete_riwayat_pangkat_and_resynchronizes_previous_active_pangkat(): void
    {
        $pangkat1 = RiwayatPangkat::create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganAwal->id,
            'tmt'         => '2020-01-01',
            'status'      => 'nonaktif',
        ]);

        $pangkat2 = RiwayatPangkat::create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganBaru->id,
            'tmt'         => '2024-01-01',
            'status'      => 'aktif',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete("/riwayat-pangkat/{$pangkat2->id}");

        $response->assertRedirect(route('riwayat-pangkat.index'));

        // Assert pangkat2 deleted
        $this->assertDatabaseMissing('riwayat_pangkat', ['id' => $pangkat2->id]);

        // Assert pangkat1 reactivated & pegawai synchronized
        $pangkat1->refresh();
        $this->assertEquals('aktif', $pangkat1->status);

        $this->pegawai->refresh();
        $this->assertEquals($this->golonganAwal->id, $this->pegawai->golongan_id);
        $this->assertEquals('2020-01-01', $this->pegawai->tmt_pangkat_terakhir->format('Y-m-d'));
    }

    public function test_pimpinan_has_read_access_and_write_denied(): void
    {
        $riwayat = RiwayatPangkat::create([
            'pegawai_id'  => $this->pegawai->id,
            'golongan_id' => $this->golonganAwal->id,
            'tmt'         => '2020-01-01',
            'status'      => 'aktif',
        ]);

        // READ Allowed
        $this->actingAs($this->pimpinanUser)->get('/riwayat-pangkat')->assertStatus(200);

        // WRITE Denied (403)
        $this->actingAs($this->pimpinanUser)->get('/riwayat-pangkat/create')->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->post('/riwayat-pangkat', [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->get("/riwayat-pangkat/{$riwayat->id}/edit")->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->put("/riwayat-pangkat/{$riwayat->id}", [])->assertStatus(403);
        $this->actingAs($this->pimpinanUser)->delete("/riwayat-pangkat/{$riwayat->id}")->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/riwayat-pangkat')->assertRedirect('/login');
    }
}
