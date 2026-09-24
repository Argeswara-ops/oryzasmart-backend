<?php

namespace Tests\Feature;

use App\Models\Camera;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\SensorThreshold;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartRiceNurseryApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'operator@padibackend.test',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);

        SensorThreshold::create(['sensor_type' => 'temperature', 'min_value' => 20.0, 'max_value' => 30.0]);
        SensorThreshold::create(['sensor_type' => 'humidity', 'min_value' => 60.0, 'max_value' => 85.0]);
    }

    public function test_auth_login_logout_user(): void
    {
        // Login
        $response = $this->postJson('/api/login', [
            'email' => 'operator@padibackend.test',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);

        $token = $response->json('data.token');

        // User profile
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/user')
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'operator@padibackend.test');

        // Logout
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/logout')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_vision_detection_endpoint(): void
    {
        $payload = [
            'camera_id' => 'CAM-01',
            'detection_type' => 'disease',
            'class_name' => 'rice_blast',
            'confidence' => 0.92,
            'image_path' => 'detections/cam01-001.jpg',
            'detected_at' => now()->toDateTimeString(),
        ];

        $response = $this->postJson('/api/vision/detections', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.class_name', 'rice_blast');

        $this->assertDatabaseHas('detections', [
            'class_name' => 'rice_blast',
        ]);
    }

    public function test_dashboard_and_sensors_authenticated(): void
    {
        $device = Device::create([
            'device_code' => 'ESP32-01',
            'name' => 'ESP32 Utama',
            'status' => 'ONLINE',
        ]);

        SensorReading::create([
            'device_id' => $device->id,
            'temperature' => 32.5, // Exceeds threshold -> triggers WARNING
            'humidity' => 88.0,
            'nitrogen' => 40.0,
            'phosphorus' => 25.0,
            'potassium' => 35.0,
            'light' => 700.0,
            'ultrasonic' => 15.0,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/dashboard');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sensors.temperature', 32.5);

        // Sensors latest
        $this->actingAs($this->user)->getJson('/api/sensors/latest')
            ->assertStatus(200)
            ->assertJsonPath('data.temperature', 32.5);

        // Sensors history
        $this->actingAs($this->user)->getJson('/api/sensors/history?range=today')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_actuator_fan_control(): void
    {
        $device = Device::create([
            'device_code' => 'ESP32-01',
            'name' => 'ESP32 Utama',
            'status' => 'ONLINE',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/actuators/fan/control', [
            'device_id' => 'ESP32-01',
            'command' => 'ON',
            'mode' => 'MANUAL',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('actuators', [
            'status' => 'ON',
            'mode' => 'MANUAL',
        ]);

        $this->assertDatabaseHas('actuator_logs', [
            'command' => 'ON',
            'source' => 'USER',
        ]);
    }

    public function test_devices_cameras_alerts_recommendations_index(): void
    {
        Device::create(['device_code' => 'ESP32-01', 'name' => 'ESP32 Utama', 'status' => 'ONLINE']);
        Camera::create(['camera_code' => 'CAM-01', 'name' => 'CAM Utama', 'status' => 'ONLINE']);

        $this->actingAs($this->user)->getJson('/api/devices')->assertStatus(200)->assertJsonCount(1, 'data');
        $this->actingAs($this->user)->getJson('/api/cameras')->assertStatus(200)->assertJsonCount(1, 'data');
        $this->actingAs($this->user)->getJson('/api/alerts')->assertStatus(200);
        $this->actingAs($this->user)->getJson('/api/recommendations')->assertStatus(200);
    }
}
