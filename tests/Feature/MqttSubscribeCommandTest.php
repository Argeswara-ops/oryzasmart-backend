<?php

namespace Tests\Feature;

use App\Console\Commands\MqttListenCommand;
use App\Models\SensorReading;
use App\Services\MqttService;
use App\Services\SensorService;
use Illuminate\Support\Facades\Log;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class MqttSubscribeCommandTest extends TestCase
{
    public function test_valid_wokwi_payload_is_forwarded_to_sensor_service(): void
    {
        config(['mqtt.default_device_id' => 'ESP32-01']);
        $command = app(MqttListenCommand::class);
        $sensorReading = new SensorReading(['temperature' => 27.2]);
        $payload = json_encode([
            'device_id' => 'ESP32-01',
            'uptime_s' => 123,
            'temperature' => 27.2,
            'humidity' => 76.0,
            'npk' => ['n' => 45.0, 'p' => 25.0, 'k' => 35.0],
            'light' => ['percent' => 65.0, 'uv_index' => 3.2],
            'ultrasonic' => ['distance_cm' => 18.0, 'level_percent' => 72.0],
            'fan' => ['state' => 'OFF', 'mode' => 'AUTO'],
            'recorded_at' => '2026-09-30 10:15:00',
        ], JSON_THROW_ON_ERROR);
        $capturedPayload = null;
        $sensorService = $this->mock(SensorService::class, function (MockInterface $mock) use (&$capturedPayload, $sensorReading): void {
            $mock->shouldReceive('processPayload')
                ->once()
                ->withAnyArgs()
                ->andReturnUsing(function (array $payload) use (&$capturedPayload, $sensorReading): SensorReading {
                    $capturedPayload = $payload;

                    return $sensorReading;
                });
        });

        $reading = $command->processSensorMessage('oryza-smart/ESP32-01/sensors', $payload, $sensorService);

        $this->assertSame($sensorReading, $reading);
        $this->assertSame(27.2, $reading->temperature);
        $this->assertSame('ESP32-01', $capturedPayload['device_id']);
        $this->assertSame(45.0, (float) $capturedPayload['npk']['n']);
        $this->assertSame(3.2, $capturedPayload['light']['uv_index']);
        $this->assertSame('AUTO', $capturedPayload['fan']['mode']);
        $this->assertSame('2026-09-30 10:15:00', $capturedPayload['recorded_at']);
    }

    public function test_payload_device_id_overrides_default_device_id(): void
    {
        config(['mqtt.default_device_id' => 'ESP32-01']);
        $command = app(MqttListenCommand::class);
        $sensorReading = new SensorReading;
        $payload = json_encode([
            'device_id' => 'ESP32-ORYZA-01',
            'temperature' => 28.4,
            'humidity' => 74.0,
            'nitrogen' => 44.0,
            'phosphorus' => 24.0,
            'potassium' => 34.0,
            'light' => 640.0,
            'ultrasonic' => 17.0,
        ], JSON_THROW_ON_ERROR);
        $capturedPayload = null;
        $sensorService = $this->mock(SensorService::class, function (MockInterface $mock) use (&$capturedPayload, $sensorReading): void {
            $mock->shouldReceive('processPayload')
                ->once()
                ->withAnyArgs()
                ->andReturnUsing(function (array $payload) use (&$capturedPayload, $sensorReading): SensorReading {
                    $capturedPayload = $payload;

                    return $sensorReading;
                });
        });

        $reading = $command->processSensorMessage('oryza-smart/ESP32-01/sensors', $payload, $sensorService);

        $this->assertSame($sensorReading, $reading);
        $this->assertSame('ESP32-ORYZA-01', $capturedPayload['device_id']);
        $this->assertSame(28.4, $capturedPayload['temperature']);
    }

    public function test_invalid_json_payload_is_logged_and_ignored(): void
    {
        Log::spy();
        $command = app(MqttListenCommand::class);
        $sensorService = $this->mock(SensorService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('processPayload');
        });

        $reading = $command->processSensorMessage('oryza-smart/ESP32-01/sensors', '{broken-json', $sensorService);

        $this->assertNull($reading);
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $message === 'Invalid MQTT JSON payload'
                && $context['topic'] === 'oryza-smart/ESP32-01/sensors');
    }

    public function test_fan_command_is_published_to_wokwi_command_topic(): void
    {
        $this->freezeTime();
        config([
            'mqtt.default_device_id' => 'ESP32-01',
            'mqtt.fan_command_topic' => 'oryza-smart/ESP32-01/actuator/fan/command',
        ]);

        $mqttService = Mockery::mock(MqttService::class)->makePartial();
        $mqttService->shouldReceive('publish')
            ->once()
            ->withArgs(function (string $topic, string $message): bool {
                $payload = json_decode($message, true);

                return $topic === 'oryza-smart/ESP32-01/actuator/fan/command'
                    && $payload['device_id'] === 'ESP32-01'
                    && $payload['command'] === 'AUTO';
            });

        $mqttService->publishFanCommand('ESP32-01', 'AUTO');
    }
}
