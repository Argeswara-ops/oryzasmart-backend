<?php

namespace Tests\Feature;

use App\Models\Camera;
use App\Models\Detection;
use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulateNurseryDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_generates_esp_sensor_data_without_camera_data(): void
    {
        $this->artisan('nursery:simulate', [
            '--count' => 2,
            '--scenario' => 'critical',
        ])->assertSuccessful();

        $this->assertSame(1, Device::query()->count());
        $this->assertSame(2, SensorReading::query()->count());
        $this->assertSame(0, Camera::query()->count());
        $this->assertSame(0, Detection::query()->count());

        $this->assertDatabaseHas('sensor_readings', [
            'level_percent' => 8,
            'fan_state' => 'ON',
            'fan_mode' => 'AUTO',
        ]);
    }

    public function test_command_rejects_unknown_scenario(): void
    {
        $this->artisan('nursery:simulate', [
            '--scenario' => 'broken',
        ])->assertFailed();
    }
}
