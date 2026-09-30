<?php

namespace App\Console\Commands;

use App\Services\MqttService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mqtt:simulate-device
    {--device=ESP32-01 : Device code to simulate}
    {--scenario=mixed : Sensor scenario: normal, warning, critical, or mixed}
    {--count=10 : Number of sensor messages to publish}
    {--interval=1 : Seconds to wait between messages}
    {--status=ONLINE : Device status heartbeat value}
    {--fan-status= : Optional fan status feedback: ON or OFF}')]
#[Description('Publish simulated ESP32 MQTT messages for the smart rice nursery project')]
class SimulateMqttDeviceCommand extends Command
{
    public function handle(MqttService $mqttService): int
    {
        $deviceCode = (string) $this->option('device');
        $scenario = mb_strtolower((string) $this->option('scenario'));
        $count = max(1, (int) $this->option('count'));
        $interval = max(0, (int) $this->option('interval'));
        $status = mb_strtoupper((string) $this->option('status'));
        $fanStatus = $this->option('fan-status');

        if (! in_array($scenario, ['normal', 'warning', 'critical', 'mixed'], true)) {
            $this->error('Scenario must be one of: normal, warning, critical, mixed.');

            return self::FAILURE;
        }

        if ($fanStatus !== null && ! in_array(mb_strtoupper((string) $fanStatus), ['ON', 'OFF'], true)) {
            $this->error('Fan status must be ON or OFF.');

            return self::FAILURE;
        }

        for ($iteration = 0; $iteration < $count; $iteration++) {
            $currentScenario = $scenario === 'mixed'
                ? ['normal', 'warning', 'critical'][$iteration % 3]
                : $scenario;

            $mqttService->publish(
                $this->topicForDevice((string) config('mqtt.status_topic', 'oryza-smart/ESP32-01/status'), $deviceCode),
                $this->jsonPayload(['status' => $status, 'timestamp' => now()->toIso8601String()])
            );

            $mqttService->publish(
                $this->topicForDevice((string) config('mqtt.sensor_topic', 'oryza-smart/ESP32-01/sensors'), $deviceCode),
                $this->jsonPayload($this->sensorPayload($deviceCode, $currentScenario))
            );

            if ($fanStatus !== null) {
                $mqttService->publish(
                    $this->topicForDevice((string) config('mqtt.fan_status_topic', 'oryza-smart/ESP32-01/actuator/fan/status'), $deviceCode),
                    $this->jsonPayload([
                        'state' => mb_strtoupper((string) $fanStatus),
                        'mode' => 'AUTO',
                        'timestamp' => now()->toIso8601String(),
                    ])
                );
            }

            $this->line("Published {$currentScenario} MQTT sample #".($iteration + 1)." for {$deviceCode}.");

            if ($iteration < $count - 1 && $interval > 0) {
                sleep($interval);
            }
        }

        $this->info("Published {$count} simulated MQTT sample(s) for {$deviceCode}.");

        return self::SUCCESS;
    }

    /**
     * @return array{device_id: string, uptime_s: int, temperature: float, humidity: float, npk: array{n: float, p: float, k: float}, light: array{percent: float, uv_index: float}, ultrasonic: array{distance_cm: float, level_percent: float}, fan: array{state: string, mode: string}, recorded_at: string}
     */
    private function sensorPayload(string $deviceCode, string $scenario): array
    {
        $payloads = [
            'normal' => [
                'temperature' => 27.2,
                'humidity' => 76.0,
                'npk' => ['n' => 45.0, 'p' => 25.0, 'k' => 35.0],
                'light' => ['percent' => 65.0, 'uv_index' => 3.2],
                'ultrasonic' => ['distance_cm' => 18.0, 'level_percent' => 72.0],
                'fan' => ['state' => 'OFF', 'mode' => 'AUTO'],
            ],
            'warning' => [
                'temperature' => 32.4,
                'humidity' => 88.0,
                'npk' => ['n' => 26.0, 'p' => 25.0, 'k' => 35.0],
                'light' => ['percent' => 65.0, 'uv_index' => 3.2],
                'ultrasonic' => ['distance_cm' => 18.0, 'level_percent' => 72.0],
                'fan' => ['state' => 'ON', 'mode' => 'AUTO'],
            ],
            'critical' => [
                'temperature' => 34.8,
                'humidity' => 91.0,
                'npk' => ['n' => 23.0, 'p' => 12.0, 'k' => 18.0],
                'light' => ['percent' => 95.0, 'uv_index' => 9.1],
                'ultrasonic' => ['distance_cm' => 3.0, 'level_percent' => 94.0],
                'fan' => ['state' => 'ON', 'mode' => 'AUTO'],
            ],
        ];

        return [
            'device_id' => $deviceCode,
            'uptime_s' => 120,
            ...$payloads[$scenario],
            'recorded_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function jsonPayload(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    private function topicForDevice(string $topic, string $deviceCode): string
    {
        $defaultDeviceId = (string) config('mqtt.default_device_id', 'ESP32-01');

        return str_replace("/{$defaultDeviceId}/", "/{$deviceCode}/", $topic);
    }
}
