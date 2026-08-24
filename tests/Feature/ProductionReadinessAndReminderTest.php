<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionReadinessAndReminderTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        Role::firstOrCreate(['name' => 'pimpinan'], ['display_name' => 'Pimpinan']);
        Role::firstOrCreate(['name' => 'pegawai'], ['display_name' => 'Pegawai']);

        $this->adminUser = User::factory()->create([
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);
    }

    public function test_admin_can_export_reminder_to_pdf(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.reminder.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_export_reminder_to_excel(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('reports.reminder.excel'));

        $response->assertStatus(200);
    }

    public function test_backup_command_runs_successfully(): void
    {
        $this->artisan('simpeg:backup --only-db')
            ->assertSuccessful();
    }

    public function test_user_can_skip_forced_password_change(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::where('name', 'pegawai')->first()->id,
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('password.skip'));

        $response->assertRedirect(route('dashboard'));
        $this->assertFalse($user->fresh()->must_change_password);
    }

    public function test_reset_password_flag_command(): void
    {
        User::factory()->create([
            'must_change_password' => true,
        ]);

        $this->artisan('user:reset-password-flag --all')
            ->assertSuccessful();

        $this->assertEquals(0, User::where('must_change_password', true)->count());
    }
}