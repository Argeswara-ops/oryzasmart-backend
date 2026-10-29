<?php

namespace App\Console\Commands;

use App\Models\SensorThreshold;
use App\Services\SensorService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('nursery:simulate
    {--count=1 : Number of sensor readings to generate}
    {--scenario=mixed : Data scenario: normal, warning, critical, or mixed}
    {--device=ESP32-01 : Device code used by simulated sensor readings}')]
#[Description('Generate simulated ESP32 sensor data for local development')]
class SimulateNurseryDataCommand extends Command
{
    public function handle(SensorService $sensorService): int
    {
        $count = max(1, (int) $this->option('count'));
        $scenario = mb_strtolower((string) $this->option('scenario'));
        $deviceCode = (string) $this->option('device');

        if (! in_array($scenario, ['normal', 'warning', 'critical', 'mixed'], true)) {
            $this->error('Scenario must be one of: normal, warning, critical, mixed.');

            return self::FAILURE;
        }

        foreach (SensorThreshold::DEFAULTS as $sensorType => $values) {
            SensorThreshold::updateOrCreate(['sensor_type' => $sensorType], $values);
        }

        for ($iteration = 0; $iteration < $count; $iteration++) {
            $currentScenario = $scenario === 'mixed'
                ? ['normal', 'warning', 'critical'][$iteration % 3]
                : $scenario;

            $sensorService->processPayload($this->sensorPayload($deviceCode, $currentScenario, $iteration));
        }

        $this->info("Generated {$count} simulated ESP32 sensor reading(s) for scenario={$scenario}.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function sensorPayload(string $deviceCode, string $scenario, int $iteration): array
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
                'light' => ['percent' => 92.0, 'uv_index' => 8.5],
                'ultrasonic' => ['distance_cm' => 25.0, 'level_percent' => 18.0],
                'fan' => ['state' => 'ON', 'mode' => 'AUTO'],
            ],
            'critical' => [
                'temperature' => 34.8,
                'humidity' => 91.0,
                'npk' => ['n' => 23.0, 'p' => 12.0, 'k' => 18.0],
                'light' => ['percent' => 96.0, 'uv_index' => 10.0],
                'ultrasonic' => ['distance_cm' => 28.0, 'level_percent' => 8.0],
                'fan' => ['state' => 'ON', 'mode' => 'AUTO'],
            ],
        ];

        return [
            'device_id' => $deviceCode,
            'uptime_s' => 120 + $iteration,
            ...$payloads[$scenario],
            'recorded_at' => now()->toDateTimeString(),
        ];
    }
}
