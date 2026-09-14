<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use App\Models\UnitKerja;
use App\Models\Jabatan;
use App\Models\Golongan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiBulkDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create(['name' => 'admin', 'display_name' => 'Admin']);
        $this->adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $roleAdmin->id,
        ]);
        
        UnitKerja::create(['kode_unit' => 'UK1', 'nama_unit' => 'Unit Kerja 1']);
        Jabatan::create(['kode_jabatan' => 'J1', 'nama_jabatan' => 'Jabatan 1']);
        Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);
    }

    public function test_admin_can_bulk_delete_employees(): void
    {
        $pegawai1 = Pegawai::create([
            'nip' => '199001012015011001',
            'nama' => 'Pegawai 1',
            'jenis_pegawai' => 'PNS',
            'status_asn' => 'ASN',
            'status_pegawai' => 'Aktif',
        ]);

        $pegawai2 = Pegawai::create([
            'nip' => '199001012015011002',
            'nama' => 'Pegawai 2',
            'jenis_pegawai' => 'PNS',
            'status_asn' => 'ASN',
            'status_pegawai' => 'Aktif',
        ]);

        $this->assertDatabaseHas('pegawai', ['id' => $pegawai1->id]);
        $this->assertDatabaseHas('pegawai', ['id' => $pegawai2->id]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('pegawai.bulk-delete'), [
                'pegawai_ids' => [$pegawai1->id, $pegawai2->id]
            ]);

        $response->assertRedirect(route('pegawai.index'));
        $response->assertSessionHas('success', 'Berhasil menghapus 2 data pegawai secara massal.');

        $this->assertDatabaseMissing('pegawai', ['id' => $pegawai1->id]);
        $this->assertDatabaseMissing('pegawai', ['id' => $pegawai2->id]);
    }
}
