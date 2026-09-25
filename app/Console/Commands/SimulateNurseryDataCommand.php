<?php

namespace App\Console\Commands;

use App\Models\Actuator;
use App\Models\Camera;
use App\Models\Device;
use App\Models\SensorThreshold;
use App\Models\User;
use App\Services\SensorService;
use App\Services\VisionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('nursery:simulate
    {--sensor : Generate simulated sensor readings}
    {--detection : Generate simulated vision detections}
    {--count=1 : Number of data points to generate}
    {--scenario=mixed : Data scenario: normal, warning, critical, or mixed}
    {--device=ESP32-01 : Device code used by simulated sensor readings}
    {--camera=CAM-01 : Camera code used by simulated detections}')]
#[Description('Generate simulated nursery data for Flutter integration before real IoT and vision services exist')]
class SimulateNurseryDataCommand extends Command
{
    public function handle(SensorService $sensorService, VisionService $visionService): int
    {
        $count = max(1, (int) $this->option('count'));
        $scenario = mb_strtolower((string) $this->option('scenario'));
        $deviceCode = (string) $this->option('device');
        $cameraCode = (string) $this->option('camera');
        $generateSensor = (bool) $this->option('sensor');
        $generateDetection = (bool) $this->option('detection');

        if (! in_array($scenario, ['normal', 'warning', 'critical', 'mixed'], true)) {
            $this->error('Scenario must be one of: normal, warning, critical, mixed.');

            return self::FAILURE;
        }

        if (! $generateSensor && ! $generateDetection) {
            $generateSensor = true;
            $generateDetection = true;
        }

        $this->ensureIntegrationSeedData($deviceCode, $cameraCode);

        for ($iteration = 0; $iteration < $count; $iteration++) {
            $currentScenario = $scenario === 'mixed'
                ? ['normal', 'warning', 'critical'][$iteration % 3]
                : $scenario;

            if ($generateSensor) {
                $sensorService->processPayload($this->sensorPayload($deviceCode, $currentScenario, $iteration));
            }

            if ($generateDetection) {
                $visionService->processDetection($this->detectionPayload($cameraCode, $currentScenario, $iteration));
            }
        }

        $this->info(sprintf(
            'Generated %d simulated data point(s) for sensor=%s detection=%s scenario=%s.',
            $count,
            $generateSensor ? 'yes' : 'no',
            $generateDetection ? 'yes' : 'no',
            $scenario,
        ));

        return self::SUCCESS;
    }

    private function ensureIntegrationSeedData(string $deviceCode, string $cameraCode): void
    {
        User::firstOrCreate(
            ['email' => 'operator@padibackend.test'],
            [
                'name' => 'Operator Pembibitan',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ],
        );

        $device = Device::firstOrCreate(
            ['device_code' => $deviceCode],
            [
                'name' => "Device {$deviceCode}",
                'type' => 'esp32',
                'location' => 'Rumah Kaca Pembibitan',
                'status' => 'ONLINE',
                'last_seen' => now(),
            ],
        );

        Camera::firstOrCreate(
            ['camera_code' => $cameraCode],
            [
                'name' => "Camera {$cameraCode}",
                'location' => 'Blok A Pembibitan',
                'stream_url' => 'http://localhost:8080/stream',
                'status' => 'ONLINE',
                'last_seen' => now(),
            ],
        );

        Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'fan'],
            [
                'name' => 'Kipas Ventilasi 01',
                'mode' => 'MANUAL',
                'status' => 'OFF',
            ],
        );

        foreach ($this->defaultThresholds() as $threshold) {
            SensorThreshold::updateOrCreate(
                ['sensor_type' => $threshold['sensor_type']],
                $threshold,
            );
        }
    }

    /**
     * @return array<int, array{sensor_type: string, min_value: float, max_value: float}>
     */
    private function defaultThresholds(): array
    {
        return [
            ['sensor_type' => 'temperature', 'min_value' => 20.0, 'max_value' => 30.0],
            ['sensor_type' => 'humidity', 'min_value' => 60.0, 'max_value' => 85.0],
            ['sensor_type' => 'nitrogen', 'min_value' => 30.0, 'max_value' => 80.0],
            ['sensor_type' => 'phosphorus', 'min_value' => 15.0, 'max_value' => 50.0],
            ['sensor_type' => 'potassium', 'min_value' => 20.0, 'max_value' => 60.0],
            ['sensor_type' => 'light', 'min_value' => 200.0, 'max_value' => 1000.0],
            ['sensor_type' => 'ultrasonic', 'min_value' => 5.0, 'max_value' => 50.0],
        ];
    }

    /**
     * @return array{device_id: string, temperature: float, humidity: float, nitrogen: float, phosphorus: float, potassium: float, light: float, ultrasonic: float, recorded_at: string}
     */
    private function sensorPayload(string $deviceCode, string $scenario, int $iteration): array
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
            'recorded_at' => now()->subSeconds($iteration)->toDateTimeString(),
        ];
    }

    /**
     * @return array{camera_id: string, detection_type: string, class_name: string, confidence: float, image_path: string, detected_at: string}
     */
    private function detectionPayload(string $cameraCode, string $scenario, int $iteration): array
    {
        $payloads = [
            'normal' => [
                'detection_type' => 'HEALTHY',
                'class_name' => 'healthy_seedling',
                'confidence' => 0.97,
            ],
            'warning' => [
                'detection_type' => 'PEST',
                'class_name' => 'wereng',
                'confidence' => 0.78,
            ],
            'critical' => [
                'detection_type' => 'DISEASE',
                'class_name' => 'rice_blast',
                'confidence' => 0.93,
            ],
        ];

        return [
            'camera_id' => $cameraCode,
            ...$payloads[$scenario],
            'image_path' => "detections/simulated-{$scenario}-{$iteration}.jpg",
            'detected_at' => now()->subSeconds($iteration)->toDateTimeString(),
        ];
    }
}
