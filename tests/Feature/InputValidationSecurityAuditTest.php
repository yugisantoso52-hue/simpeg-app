<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InputValidationSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $stafUser;

    protected UnitKerja $unit;
    protected Jabatan $jabatan;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $stafRole  = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);

        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id, 'must_change_password' => false]);
        $this->stafUser  = User::factory()->create(['role_id' => $stafRole->id, 'must_change_password' => false]);

        $this->unit    = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $this->jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru']);
        $this->golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);
    }

    /**
     * TEST 1 — REQUIRED FIELD BYPASS ATTEMPT
     */
    public function test_required_fields_cannot_be_bypassed(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', []);

        $response->assertSessionHasErrors(['nip', 'nama']);
    }

    /**
     * TEST 2 — INVALID FOREIGN KEY ATTEMPT
     */
    public function test_invalid_foreign_keys_are_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', [
                'nip'           => '199001012015011099',
                'nama'          => 'Pegawai Test Invalid FK',
                'nama_lengkap'  => 'Pegawai Test Invalid FK',
                'nik'           => '1234567890123456',
                'jenis_kelamin' => 'L',
                'unit_kerja_id' => 99999, // Nonexistent
                'jabatan_id'    => 99999, // Nonexistent
                'golongan_id'   => $this->golongan->id,
            ]);

        $response->assertSessionHasErrors(['unit_kerja_id', 'jabatan_id']);
    }

    /**
     * TEST 3 — DUPLICATE NIP ATTEMPT
     */
    public function test_duplicate_nip_is_rejected(): void
    {
        Pegawai::create([
            'nip'           => '199001012015011001',
            'nama'          => 'Pegawai Existing',
            'nama_lengkap'  => 'Pegawai Existing',
            'nik'           => '1234567890123456',
            'jenis_kelamin' => 'L',
            'unit_kerja_id' => $this->unit->id,
            'jabatan_id'    => $this->jabatan->id,
            'golongan_id'   => $this->golongan->id,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', [
                'nip'           => '199001012015011001',
                'nama'          => 'Pegawai Duplicate NIP',
                'nama_lengkap'  => 'Pegawai Duplicate NIP',
                'nik'           => '1234567890123456',
                'jenis_kelamin' => 'L',
                'unit_kerja_id' => $this->unit->id,
                'jabatan_id'    => $this->jabatan->id,
                'golongan_id'   => $this->golongan->id,
            ]);

        $response->assertSessionHasErrors(['nip']);
    }

    /**
     * TEST 4 — INVALID ENUM / DATA TYPE ATTEMPT
     */
    public function test_invalid_enum_and_types_are_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', [
                'nip'           => '199001012015011002',
                'nama'          => 'Pegawai Invalid Enum',
                'nama_lengkap'  => 'Pegawai Invalid Enum',
                'nik'           => '1234567890123456',
                'unit_kerja_id' => $this->unit->id,
                'jabatan_id'    => $this->jabatan->id,
                'golongan_id'   => $this->golongan->id,
                'jenis_kelamin' => 'INVALID_GENDER',
                'status_asn'    => 'MALICIOUS_STATUS',
                'tanggal_lahir' => 'NOT_A_DATE',
            ]);

        $response->assertSessionHasErrors(['jenis_kelamin', 'status_asn', 'tanggal_lahir']);
    }

    /**
     * TEST 5 — OVERSIZED STRING ATTEMPT
     */
    public function test_oversized_string_input_is_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', [
                'nip'           => str_repeat('1', 100), // Max 50
                'nama'          => str_repeat('A', 300), // Max 150
                'nama_lengkap'  => str_repeat('A', 300), // Max 150
                'nik'           => '1234567890123456',
                'jenis_kelamin' => 'L',
                'unit_kerja_id' => $this->unit->id,
                'jabatan_id'    => $this->jabatan->id,
                'golongan_id'   => $this->golongan->id,
            ]);

        $response->assertSessionHasErrors(['nip', 'nama']);
    }

    /**
     * TEST 6 — UNKNOWN EXTRA FIELDS ARE IGNORED OR STRIPPED
     */
    public function test_unknown_fields_are_ignored_by_fillable_protection(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', [
                'nip'           => '199001012015011003',
                'nama'          => 'Pegawai Unknown Fields',
                'nama_lengkap'  => 'Pegawai Unknown Fields',
                'nik'           => '1234567890123456',
                'jenis_kelamin' => 'L',
                'unit_kerja_id' => $this->unit->id,
                'jabatan_id'    => $this->jabatan->id,
                'golongan_id'   => $this->golongan->id,
                'is_admin'      => true, // Injection attempt
                'role_id'       => 1,    // Injection attempt
            ]);

        $response->assertRedirect(route('pegawai.index'));

        $this->assertDatabaseHas('pegawai', ['nip' => '199001012015011003']);
    }

    /**
     * TEST 7 — ROLE ESCALATION VIA PROFILE UPDATE IS PREVENTED
     */
    public function test_user_cannot_escalate_role_via_profile_update(): void
    {
        $adminRole = Role::where('name', 'admin')->first();

        $response = $this->actingAs($this->stafUser)
            ->patch('/profile', [
                'name'    => 'Staf Hacked Name',
                'email'   => 'staf_hacked@example.com',
                'role_id' => $adminRole->id,
            ]);

        $response->assertRedirect('/profile');

        $this->stafUser->refresh();
        $this->assertNotEquals($adminRole->id, $this->stafUser->role_id);
    }

    /**
     * TEST 8 — EXECUTABLE FILE UPLOAD BYPASS IS REJECTED
     */
    public function test_executable_file_upload_is_rejected(): void
    {
        Storage::fake('local');

        $phpShell = UploadedFile::fake()->create('backdoor.php', 50, 'application/x-php');

        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai', [
                'nip'             => '199001012015011004',
                'nama'            => 'Pegawai Malicious Upload',
                'unit_kerja_id'   => $this->unit->id,
                'jabatan_id'      => $this->jabatan->id,
                'file_sk_pertama' => $phpShell,
            ]);

        $response->assertSessionHasErrors(['file_sk_pertama']);
    }

    /**
     * TEST 9 — IMPORT EXCEL WITH INVALID FILE FORMAT IS REJECTED
     */
    public function test_import_excel_rejects_invalid_file_format(): void
    {
        Storage::fake('local');

        $exeFile = UploadedFile::fake()->create('import_payload.exe', 100, 'application/x-msdownload');

        $response = $this->actingAs($this->adminUser)
            ->post('/pegawai/import', [
                'file' => $exeFile,
            ]);

        $response->assertSessionHasErrors(['file']);
    }
}
