<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PegawaiLoginCredentialsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);
    }

    /**
     * Test pegawai can log in using NIP and default password 'Password'
     */
    public function test_pegawai_can_login_with_nip_and_default_password(): void
    {
        $pegawai = Pegawai::create([
            'nip'            => '198505102010011005',
            'nama'           => 'Dr. Siti Nurhaliza',
            'status_pegawai' => 'Aktif',
            'tanggal_lahir'  => '1985-05-10',
        ]);

        $rolePegawai = Role::where('name', 'pegawai')->first();
        User::create([
            'name'                 => $pegawai->nama,
            'email'                => '198505102010011005@staff.unri.ac.id',
            'password'             => Hash::make('Password'),
            'role_id'              => $rolePegawai->id,
            'pegawai_id'           => $pegawai->id,
            'must_change_password' => true,
        ]);

        $response = $this->post('/login', [
            'login'    => '198505102010011005',
            'password' => 'Password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    /**
     * Test pegawai auto-sync on login when User account doesn't exist yet
     */
    public function test_pegawai_auto_creates_user_with_default_password_on_first_login(): void
    {
        $pegawai = Pegawai::create([
            'nip'            => '199012312015011009',
            'nama'           => 'Ahmad Junaidi',
            'status_pegawai' => 'Aktif',
            'tanggal_lahir'  => '1990-12-31',
        ]);

        // User account is not created yet
        $this->assertDatabaseMissing('users', ['email' => '199012312015011009@staff.unri.ac.id']);

        $response = $this->post('/login', [
            'login'    => '199012312015011009',
            'password' => 'Password',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email'      => '199012312015011009@staff.unri.ac.id',
            'pegawai_id' => $pegawai->id,
        ]);
    }

    /**
     * Test production NIP login and self-healing from previous password hash
     */
    public function test_production_nip_login_self_heals_with_password(): void
    {
        $pegawai = Pegawai::create([
            'nip'            => '198006152025211060',
            'nama'           => 'Rahmad Hidayat',
            'status_pegawai' => 'Aktif',
            'tanggal_lahir'  => '1980-06-15',
        ]);

        $rolePegawai = Role::where('name', 'pegawai')->first();
        // User exists with old birthdate password hash
        User::create([
            'name'                 => $pegawai->nama,
            'email'                => '198006152025211060@staff.unri.ac.id',
            'password'             => Hash::make('19800615'),
            'role_id'              => $rolePegawai->id,
            'pegawai_id'           => $pegawai->id,
            'must_change_password' => true,
        ]);

        // Login with 'Password'
        $response = $this->post('/login', [
            'login'    => '198006152025211060',
            'password' => 'Password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }
}
