<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\Device;
use App\Models\Recommendation;
use App\Models\SensorReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulateNurseryDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_generates_sensor_and_detection_data_for_flutter_integration(): void
    {
        $this->artisan('nursery:simulate', [
            '--count' => 2,
            '--scenario' => 'critical',
        ])->assertSuccessful();

        $this->assertSame(1, User::query()->count());
        $this->assertSame(1, Device::query()->count());
        $this->assertSame(1, Camera::query()->count());
        $this->assertSame(2, SensorReading::query()->count());
        $this->assertSame(2, Detection::query()->count());
        $this->assertGreaterThanOrEqual(1, Alert::query()->count());
        $this->assertGreaterThanOrEqual(1, Recommendation::query()->count());

        $this->assertDatabaseHas('detections', [
            'detection_type' => 'DISEASE',
            'class_name' => 'rice_blast',
        ]);
    }

    public function test_command_rejects_unknown_scenario(): void
    {
        $this->artisan('nursery:simulate', [
            '--scenario' => 'broken',
        ])->assertFailed();
    }
}
