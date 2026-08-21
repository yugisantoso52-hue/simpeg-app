<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_authenticate_using_nip(): void
    {
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $pegawai = \App\Models\Pegawai::create([
            'nip' => '1234567890',
            'nama' => 'Test NIP Login',
            'nik' => '1234567890123456',
            'jenis_kelamin' => 'L',
            'unit_kerja_id' => \App\Models\UnitKerja::firstOrCreate(['nama_unit' => 'Unit Test', 'kode_unit' => 'UT'])->id,
            'jabatan_id' => \App\Models\Jabatan::firstOrCreate(['nama_jabatan' => 'Jabatan Test', 'kode_jabatan' => 'JT'])->id,
            'golongan_id' => \App\Models\Golongan::firstOrCreate(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda'])->id,
        ]);
        $user = User::factory()->create([
            'pegawai_id' => $pegawai->id,
            'role_id' => $adminRole->id,
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'must_change_password' => false,
        ]);

        $response = $this->post('/login', [
            'login' => '12 34 56 78 90', // NIP with spaces
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
