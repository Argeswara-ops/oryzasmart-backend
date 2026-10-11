<?php

namespace Tests\Feature;

use App\Services\MqttService;
use Mockery\MockInterface;
use Tests\TestCase;

class SimulateMqttDeviceCommandTest extends TestCase
{
    public function test_command_publishes_project_mqtt_topics_for_simulated_device(): void
    {
        $this->freezeTime();

        $this->mock(MqttService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('publish')
                ->once()
                ->withArgs(function (string $topic, string $message): bool {
                    $payload = json_decode($message, true);

                    return $topic === 'oryza-smart/ESP32-01/status'
                        && $payload['status'] === 'ONLINE';
                });

            $mock->shouldReceive('publish')
                ->once()
                ->withArgs(function (string $topic, string $message): bool {
                    $payload = json_decode($message, true);

                    return $topic === 'oryza-smart/ESP32-01/sensors'
                        && $payload['device_id'] === 'ESP32-01'
                        && (float) $payload['temperature'] === 34.8
                        && (float) $payload['humidity'] === 91.0
                        && (float) $payload['npk']['n'] === 23.0
                        && (float) $payload['light']['uv_index'] === 9.1
                        && (float) $payload['ultrasonic']['distance_cm'] === 3.0;
                });

            $mock->shouldReceive('publish')
                ->once()
                ->withArgs(function (string $topic, string $message): bool {
                    $payload = json_decode($message, true);

                    return $topic === 'oryza-smart/ESP32-01/actuator/fan/status'
                        && $payload['state'] === 'ON'
                        && $payload['mode'] === 'AUTO';
                });
        });

        $this->artisan('mqtt:simulate-device', [
            '--count' => 1,
            '--scenario' => 'critical',
            '--fan-status' => 'ON',
        ])->assertSuccessful();
    }

    public function test_command_rejects_unknown_scenario(): void
    {
        $this->artisan('mqtt:simulate-device', [
            '--scenario' => 'broken',
        ])->assertFailed();
    }
}
