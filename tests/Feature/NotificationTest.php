<?php

namespace Tests\Feature;

use App\Models\Golongan;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\User;
use App\Notifications\KgbDueDateNotification;
use App\Notifications\KpDueDateNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Kepegawaian']);
        $stafRole  = Role::firstOrCreate(['name' => 'staf'], ['display_name' => 'Staf Biasa']);

        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        $this->otherUser = User::factory()->create(['role_id' => $stafRole->id]);
    }

    /**
     * TEST 1 — KGB NOTIFICATION PAYLOAD & DATABASE CHANNEL
     */
    public function test_kgb_due_date_notification_payload_and_database_channel(): void
    {
        $notification = new KgbDueDateNotification(collect([['id' => 1, 'nama' => 'Test Pegawai']]));

        $this->assertEquals(['database'], $notification->via($this->adminUser));

        $data = $notification->toArray($this->adminUser);

        $this->assertEquals('Pengingat KGB Pegawai', $data['title']);
        $this->assertEquals('kgb_due', $data['type']);
        $this->assertEquals(1, $data['data_count']);
        $this->assertArrayHasKey('url', $data);

        // Security check: No password, token, or private file path in payload
        $jsonPayload = json_encode($data);
        $this->assertStringNotContainsString('password', $jsonPayload);
        $this->assertStringNotContainsString('token', $jsonPayload);
    }

    /**
     * TEST 2 — KP NOTIFICATION PAYLOAD & DATABASE CHANNEL
     */
    public function test_kp_due_date_notification_payload_and_database_channel(): void
    {
        $notification = new KpDueDateNotification(collect([['id' => 1, 'nama' => 'Test Pegawai']]));

        $this->assertEquals(['database'], $notification->via($this->adminUser));

        $data = $notification->toArray($this->adminUser);

        $this->assertEquals('Pengingat Kenaikan Pangkat (KP)', $data['title']);
        $this->assertEquals('kp_due', $data['type']);
        $this->assertEquals(1, $data['data_count']);
        $this->assertArrayHasKey('url', $data);
    }

    /**
     * TEST 3 — MARK ALL NOTIFICATIONS AS READ
     */
    public function test_user_can_receive_notification_and_mark_all_as_read(): void
    {
        $this->adminUser->notify(new KgbDueDateNotification(collect([['id' => 1]])));

        $this->assertEquals(1, $this->adminUser->unreadNotifications()->count());

        $response = $this->actingAs($this->adminUser)
            ->post('/notifications/mark-all-read');

        $response->assertStatus(302);
        $this->assertEquals(0, $this->adminUser->fresh()->unreadNotifications()->count());
    }

    /**
     * TEST 4 — READ AND REDIRECT SINGLE NOTIFICATION
     */
    public function test_user_can_read_and_redirect_single_notification(): void
    {
        $this->adminUser->notify(new KpDueDateNotification(collect([['id' => 1]])));

        $notification = $this->adminUser->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $response = $this->actingAs($this->adminUser)
            ->get("/notifications/{$notification->id}/read");

        $response->assertRedirect($notification->data['url']);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /**
     * TEST 5 — IDOR PROTECTION: USER CANNOT READ NOTIFICATION BELONGING TO ANOTHER USER
     */
    public function test_user_cannot_read_or_mark_notification_belonging_to_another_user(): void
    {
        // Notify otherUser
        $this->otherUser->notify(new KgbDueDateNotification(collect([['id' => 1]])));

        $notificationUserOther = $this->otherUser->unreadNotifications()->first();

        // Acting as adminUser trying to read otherUser's notification
        $response = $this->actingAs($this->adminUser)
            ->get("/notifications/{$notificationUserOther->id}/read");

        $response->assertStatus(404);
    }

    /**
     * TEST 6 — GUEST CANNOT ACCESS NOTIFICATION ENDPOINTS
     */
    public function test_guest_is_redirected_to_login_for_notification_endpoints(): void
    {
        $this->post('/notifications/mark-all-read')->assertRedirect('/login');
        $this->get('/notifications/some-uuid/read')->assertRedirect('/login');
    }

    /**
     * TEST 7 — CEK KGB COMMAND DISPATCHES NOTIFICATION
     */
    public function test_command_cek_kgb_dispatches_notification(): void
    {
        Notification::fake();

        $unit = UnitKerja::create(['nama_unit' => 'Dinas Pendidikan', 'kode_unit' => 'DISDIK']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-001', 'nama_jabatan' => 'Guru']);

        Pegawai::create([
            'nip'            => '199001012015011001',
            'nama'           => 'Pegawai KGB Command Test',
            'status_pegawai' => 'Aktif',
            'kgb_berikutnya' => Carbon::today()->toDateString(),
            'unit_kerja_id'  => $unit->id,
            'jabatan_id'     => $jabatan->id,
        ]);

        $this->artisan('simpeg:cek-kgb')->assertExitCode(0);

        Notification::assertSentTo($this->adminUser, KgbDueDateNotification::class);
    }

    /**
     * TEST 8 — CEK KP COMMAND DISPATCHES NOTIFICATION
     */
    public function test_command_cek_kp_dispatches_notification(): void
    {
        Notification::fake();

        $unit = UnitKerja::create(['nama_unit' => 'Dinas Kesehatan', 'kode_unit' => 'DINKES']);
        $jabatan = Jabatan::create(['kode_jabatan' => 'JAB-002', 'nama_jabatan' => 'Perawat']);
        $golongan = Golongan::create(['nama_golongan' => 'III/a', 'nama_pangkat' => 'Penata Muda']);

        Pegawai::create([
            'nip'            => '199101012016011002',
            'nama'           => 'Pegawai KP Command Test',
            'status_pegawai' => 'Aktif',
            'kp_berikutnya'  => Carbon::today()->toDateString(),
            'unit_kerja_id'  => $unit->id,
            'jabatan_id'     => $jabatan->id,
            'golongan_id'    => $golongan->id,
        ]);

        $this->artisan('simpeg:cek-kp')->assertExitCode(0);

        Notification::assertSentTo($this->adminUser, KpDueDateNotification::class);
    }
}
