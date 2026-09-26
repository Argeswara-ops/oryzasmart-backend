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
                "rice-nursery/{$deviceCode}/status",
                $this->jsonPayload(['status' => $status, 'timestamp' => now()->toIso8601String()])
            );

            $mqttService->publish(
                "rice-nursery/{$deviceCode}/sensors",
                $this->jsonPayload($this->sensorPayload($deviceCode, $currentScenario))
            );

            if ($fanStatus !== null) {
                $mqttService->publish(
                    "rice-nursery/{$deviceCode}/actuator/fan/status",
                    $this->jsonPayload([
                        'status' => mb_strtoupper((string) $fanStatus),
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
     * @return array{device_id: string, temperature: float, humidity: float, nitrogen: float, phosphorus: float, potassium: float, light: float, ultrasonic: float, recorded_at: string}
     */
    private function sensorPayload(string $deviceCode, string $scenario): array
    {
        $payloads = [
            'normal' => [
                'temperature' => 27.2,
                'humidity' => 76.0,
                'nitrogen' => 45.0,
                'phosphorus' => 25.0,
                'potassium' => 35.0,
                'light' => 650.0,
                'ultrasonic' => 18.0,
            ],
            'warning' => [
                'temperature' => 32.4,
                'humidity' => 88.0,
                'nitrogen' => 26.0,
                'phosphorus' => 25.0,
                'potassium' => 35.0,
                'light' => 650.0,
                'ultrasonic' => 18.0,
            ],
            'critical' => [
                'temperature' => 34.8,
                'humidity' => 91.0,
                'nitrogen' => 23.0,
                'phosphorus' => 12.0,
                'potassium' => 18.0,
                'light' => 1100.0,
                'ultrasonic' => 3.0,
            ],
        ];

        return [
            'device_id' => $deviceCode,
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
}
