<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPanelWebTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk Panel');
    }

    public function test_operator_can_login_and_view_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'operator',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_admin_can_view_users_crud(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Manajemen User');
    }

    public function test_admin_can_create_new_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $testEmail = 'operator.' . uniqid() . '@example.com';

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Test Operator Baru',
            'email' => $testEmail,
            'role' => 'operator',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => $testEmail]);
    }

    public function test_admin_can_update_threshold(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($operator)->put('/admin/thresholds/temperature', [
            'min_value' => 22.5,
            'max_value' => 32.0,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('sensor_thresholds', [
            'sensor_type' => 'temperature',
            'min_value' => 22.5,
            'max_value' => 32.0,
        ]);
    }

    public function test_admin_can_view_devices_and_sensors(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $device = Device::firstOrCreate(
            ['device_code' => 'ESP32-TEST'],
            [
                'name' => 'ESP32 Test',
                'type' => 'esp32',
                'location' => 'Test Location',
                'status' => 'ONLINE',
            ]
        );

        // Create sensor readings with level_percent
        \App\Models\SensorReading::create([
            'device_id' => $device->id,
            'temperature' => 28.5,
            'humidity' => 75,
            'level_percent' => 80.0,
            'distance_cm' => 15.0,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($operator)->get('/admin/devices');
        $response->assertStatus(200);

        $response = $this->actingAs($operator)->get('/admin/sensors');
        $response->assertStatus(200);
        $response->assertSee('80 %');
    }

    public function test_admin_can_view_four_actuator_types_and_controls(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $device = Device::firstOrCreate(
            ['device_code' => 'ESP32-TEST'],
            [
                'name' => 'ESP32 Test',
                'type' => 'esp32',
                'location' => 'Test Location',
                'status' => 'ONLINE',
            ]
        );

        \App\Models\Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'water_pump'],
            ['name' => 'Pompa Air', 'mode' => 'AUTO', 'status' => 'ON']
        );
        \App\Models\Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'fan'],
            ['name' => 'Kipas / Fan', 'mode' => 'AUTO', 'status' => 'ON']
        );
        \App\Models\Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'led_uv'],
            ['name' => 'LED UV', 'mode' => 'AUTO', 'status' => 'OFF']
        );
        \App\Models\Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'mist_pump'],
            ['name' => 'Pompa Kabut', 'mode' => 'AUTO', 'status' => 'OFF']
        );

        $response = $this->actingAs($operator)->get('/admin/actuators');
        $response->assertStatus(200);
        $response->assertSee('Kontrol Aktuator');
        $response->assertSee('Pompa Air');
        $response->assertSee('Kipas / Fan');
        $response->assertSee('LED UV');
        $response->assertSee('Pompa Kabut');

        // Verify navbar does not contain ESP32-01: ONLINE
        $response->assertDontSee('ESP32-01: ONLINE');
        // Verify branding shows Oryza Smart
        $response->assertSee('ORYZA <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-semibold border border-emerald-500/30">SMART</span>', false);
    }

    public function test_operator_can_control_actuator(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);
        $device = Device::firstOrCreate(
            ['device_code' => 'ESP32-TEST'],
            [
                'name' => 'ESP32 Test',
                'type' => 'esp32',
                'location' => 'Test Location',
                'status' => 'ONLINE',
            ]
        );
        $actuator = \App\Models\Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'water_pump'],
            ['name' => 'Pompa Air', 'mode' => 'AUTO', 'status' => 'ON']
        );

        $response = $this->actingAs($operator)->post('/admin/actuators/control', [
            'actuator_id' => $actuator->id,
            'command' => 'ON',
            'mode' => 'MANUAL',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('actuators', [
            'id' => $actuator->id,
            'status' => 'ON',
            'mode' => 'MANUAL',
        ]);
    }

    public function test_operator_cannot_access_user_management(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $response = $this->actingAs($operator)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'operator',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals('admin', $admin->fresh()->role);
    }

    public function test_admin_cannot_delete_last_admin(): void
    {
        User::where('role', 'admin')->delete();
        $admin = User::factory()->create(['role' => 'admin']);

        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->delete("/admin/users/{$otherAdmin->id}");
        $response->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $otherAdmin->id]);

        // Now $admin is the last admin
        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
