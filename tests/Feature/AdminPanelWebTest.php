<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPanelWebTest extends TestCase
{
    use DatabaseTransactions;
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('ORYZA SMART PADDY');
    }

    public function test_operator_can_login_and_view_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'operator',
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Dashboard Ringkasan IoT');
    }

    public function test_admin_can_view_users_crud(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna Sistem');
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
}
