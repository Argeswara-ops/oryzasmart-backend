<?php

namespace Tests\Feature;

use App\Models\Actuator;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\SensorThreshold;
use App\Models\User;
use App\Services\MqttService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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
            'light_percent' => 70.0,
            'uv_index' => 4.0,
            'distance_cm' => 15.0,
            'level_percent' => 65.0,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/dashboard');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.device.is_online', true)
            ->assertJsonPath('data.sensors.temperature', 32.5)
            ->assertJsonPath('data.sensors.water_level_percent', 65);

        // Sensors latest
        $this->actingAs($this->user)->getJson('/api/sensors/latest')
            ->assertStatus(200)
            ->assertJsonPath('data.temperature', 32.5);

        // Sensors history
        $this->actingAs($this->user)->getJson('/api/sensors/history?range=today')
            ->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_dashboard_device_and_history_use_latest_received_sensor_reading(): void
    {
        $device = Device::create([
            'device_code' => 'ESP32-01',
            'name' => 'ESP32 Utama',
            'status' => 'ONLINE',
            'last_seen' => now(),
        ]);

        SensorReading::create([
            'device_id' => $device->id,
            'temperature' => 27.2,
            'humidity' => 76.0,
            'recorded_at' => now(),
        ]);
        SensorReading::create([
            'device_id' => $device->id,
            'temperature' => 32.4,
            'humidity' => 88.0,
            'recorded_at' => now()->subMinute(),
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.sensors.temperature', 32.4);

        $this->actingAs($this->user)
            ->getJson('/api/devices')
            ->assertOk()
            ->assertJsonPath('data.0.latest_sensor.temperature', 32.4);

        $this->actingAs($this->user)
            ->getJson('/api/sensors/history?range=today')
            ->assertOk()
            ->assertJsonPath('data.summary.temperature.sensor', 'temperature')
            ->assertJsonPath('data.summary.temperature.current', 32.4);
    }

    public function test_actuator_fan_control(): void
    {
        $this->mock(MqttService::class)
            ->shouldReceive('publishFanCommand')
            ->once()
            ->with('ESP32-01', 'ON', 'MANUAL');

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

    public function test_device_index_returns_real_esp_state_without_camera_routes(): void
    {
        $device = Device::create([
            'device_code' => 'ESP32-01',
            'name' => 'ESP32 Utama',
            'type' => 'esp32',
            'status' => 'ONLINE',
            'last_seen' => now(),
        ]);
        Actuator::create([
            'device_id' => $device->id,
            'name' => 'Kipas',
            'type' => 'fan',
            'status' => 'ON',
            'mode' => 'AUTO',
        ]);
        SensorReading::create([
            'device_id' => $device->id,
            'temperature' => 28.0,
            'humidity' => 75.0,
            'nitrogen' => 40.0,
            'phosphorus' => 25.0,
            'potassium' => 35.0,
            'level_percent' => 72.0,
            'recorded_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/devices')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.device_code', 'ESP32-01')
            ->assertJsonPath('data.0.is_online', true)
            ->assertJsonPath('data.0.fan.is_on', true)
            ->assertJsonPath('data.0.latest_sensor.water_level_percent', 72);

        $this->actingAs($this->user)->getJson('/api/cameras')->assertNotFound();
        $this->actingAs($this->user)->getJson('/api/detections')->assertNotFound();
        $this->actingAs($this->user)->getJson('/api/alerts')->assertStatus(200);
        $this->actingAs($this->user)->getJson('/api/recommendations')->assertStatus(200);
    }

    public function test_operator_can_read_and_update_sensor_thresholds(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/thresholds')
            ->assertOk()
            ->assertJsonPath('data.0.sensor_type', 'humidity');

        $this->actingAs($this->user)
            ->putJson('/api/thresholds/temperature', [
                'min_value' => 21,
                'max_value' => 31,
            ])
            ->assertOk()
            ->assertJsonPath('data.max_value', 31);

        $this->assertDatabaseHas('sensor_thresholds', [
            'sensor_type' => 'temperature',
            'max_value' => 31,
        ]);

        $this->actingAs($this->user)
            ->putJson('/api/thresholds/water_level', [
                'min_value' => 25,
                'max_value' => 85,
            ])
            ->assertOk()
            ->assertJsonPath('data.sensor_type', 'water_level');

        $this->actingAs($this->user)
            ->putJson('/api/thresholds/camera', [
                'min_value' => 0,
                'max_value' => 1,
            ])
            ->assertUnprocessable();
    }

    public function test_paddy_prediction_forwards_image_to_oryza_vision(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'vision.test/predict' => Http::response([
                'success' => true,
                'prediction' => [
                    'class_id' => 3,
                    'class' => 'blast',
                    'display_name' => 'Blast',
                    'confidence' => 92.74,
                    'status' => 'high_confidence',
                ],
            ]),
        ]);
        config([
            'services.oryza_vision.url' => 'http://vision.test',
            'services.oryza_vision.api_key' => 'secret-key',
        ]);

        $response = $this->actingAs($this->user)->post('/api/paddy/predict', [
            'image' => UploadedFile::fake()->image('paddy.jpg', 224, 224)->size(256),
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('prediction.class', 'blast');

        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'http://vision.test/predict'
            && $request->method() === 'POST'
            && $request->hasHeader('X-API-Key', 'secret-key')
            && $request->hasFile('file', filename: 'paddy.jpg'));
    }

    public function test_realtime_paddy_prediction_forwards_stream_id_to_oryza_vision(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'vision.test/predict/realtime' => Http::response([
                'success' => true,
                'prediction' => [
                    'class' => 'blast',
                    'confidence' => 88.5,
                ],
                'stable_prediction' => null,
            ]),
        ]);
        config([
            'services.oryza_vision.url' => 'http://vision.test',
            'services.oryza_vision.api_key' => 'secret-key',
        ]);

        $response = $this->actingAs($this->user)->post('/api/paddy/predict/realtime', [
            'image' => UploadedFile::fake()->image('frame.png', 224, 224)->size(128),
            'stream_id' => 'camera-page-1',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('prediction.class', 'blast')
            ->assertJsonPath('stable_prediction', null);

        Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'http://vision.test/predict/realtime'
            && $request->method() === 'POST'
            && $request->hasHeader('X-API-Key', 'secret-key')
            && $request->hasFile('file', filename: 'frame.png')
            && str_contains($request->body(), 'name="stream_id"')
            && str_contains($request->body(), 'camera-page-1'));
    }

    public function test_paddy_prediction_returns_error_when_oryza_vision_fails(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'vision.test/predict' => Http::response([
                'success' => false,
                'message' => 'Invalid image',
            ], 422),
        ]);
        config([
            'services.oryza_vision.url' => 'http://vision.test',
            'services.oryza_vision.api_key' => 'secret-key',
        ]);

        $response = $this->actingAs($this->user)->post('/api/paddy/predict', [
            'image' => UploadedFile::fake()->image('bad.jpg', 224, 224)->size(256),
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Vision prediction failed')
            ->assertJsonPath('error.message', 'Invalid image');
    }
}
