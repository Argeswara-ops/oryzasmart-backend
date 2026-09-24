<?php

namespace App\Services;

use App\Models\Actuator;
use App\Models\Alert;
use App\Models\Detection;
use App\Models\Recommendation;
use App\Models\SensorReading;
use App\Models\SensorThreshold;

class SmartDecisionService
{
    public function __construct(
        public ActuatorService $actuatorService
    ) {}

    public function evaluate(): string
    {
        $latestSensor = SensorReading::latest('recorded_at')->first();
        $latestDetection = Detection::latest('detected_at')->first();
        $thresholds = SensorThreshold::query()->get()->keyBy('sensor_type');

        $systemStatus = 'NORMAL';
        $issues = [];
        $recommendationsText = [];

        // 1. Evaluate Sensor Thresholds
        if ($latestSensor) {
            if ($tempThresh = $thresholds->get('temperature')) {
                if ($latestSensor->temperature > $tempThresh->max_value) {
                    $systemStatus = 'WARNING';
                    $issues[] = "Suhu tinggi: {$latestSensor->temperature}°C (Maks: {$tempThresh->max_value}°C)";
                    $recommendationsText[] = 'Nyalakan kipas pendingin atau tingkatkan sirkulasi udara di area pembibitan.';
                } elseif ($latestSensor->temperature < $tempThresh->min_value) {
                    $systemStatus = 'WARNING';
                    $issues[] = "Suhu rendah: {$latestSensor->temperature}°C (Min: {$tempThresh->min_value}°C)";
                }
            }

            if ($humThresh = $thresholds->get('humidity')) {
                if ($latestSensor->humidity > $humThresh->max_value) {
                    $systemStatus = 'WARNING';
                    $issues[] = "Kelembapan udara tinggi: {$latestSensor->humidity}% (Maks: {$humThresh->max_value}%)";
                    $recommendationsText[] = 'Jaga kelembapan udara agar tidak memicu munculnya jamur/penyakit.';
                }
            }

            if ($nThresh = $thresholds->get('nitrogen')) {
                if ($latestSensor->nitrogen < $nThresh->min_value) {
                    $systemStatus = 'WARNING';
                    $issues[] = "Kadar Nitrogen rendah: {$latestSensor->nitrogen} (Min: {$nThresh->min_value})";
                    $recommendationsText[] = 'Lakukan pemupukan unsur N pada media tanam pembibitan.';
                }
            }

            foreach (['phosphorus', 'potassium', 'light', 'ultrasonic'] as $sensorType) {
                $threshold = $thresholds->get($sensorType);
                $value = $latestSensor->{$sensorType};

                if (! $threshold || $value === null) {
                    continue;
                }

                if ($value < $threshold->min_value || $value > $threshold->max_value) {
                    $systemStatus = 'WARNING';
                    $issues[] = ucfirst($sensorType)." di luar batas: {$value} (Rentang: {$threshold->min_value}-{$threshold->max_value})";
                    $recommendationsText[] = "Periksa dan sesuaikan kondisi {$sensorType} pada area pembibitan.";
                }
            }
        }

        // 2. Evaluate Computer Vision Detection
        if ($latestDetection && in_array(strtoupper($latestDetection->detection_type), ['PEST', 'DISEASE'])) {
            $severityLevel = $latestDetection->confidence > 0.85 ? 'CRITICAL' : 'WARNING';
            $systemStatus = $severityLevel;
            $issues[] = "Terdeteksi {$latestDetection->detection_type} ({$latestDetection->class_name}) dengan confidence ".round($latestDetection->confidence * 100).'%';
            $camCode = $latestDetection->camera->camera_code ?? 'CAM';
            $recommendationsText[] = "Lakukan pemeriksaan fisik pada area penanaman kamera {$camCode} dan berikan penanganan yang tepat.";
        }

        // 3. Create Alert & Recommendation if abnormal
        if ($systemStatus !== 'NORMAL' && ! empty($issues)) {
            $title = "Peringatan Sistem ({$systemStatus})";
            $message = implode(' | ', $issues);

            // Avoid duplicating alert within last 5 minutes
            $recentAlert = Alert::where('title', $title)
                ->where('created_at', '>=', now()->subMinutes(5))
                ->first();

            if (! $recentAlert) {
                $alert = Alert::create([
                    'device_id' => $latestSensor?->device_id,
                    'camera_id' => $latestDetection?->camera_id,
                    'detection_id' => $latestDetection?->id,
                    'type' => 'SMART_DECISION',
                    'source' => 'SmartDecisionService',
                    'severity' => $systemStatus,
                    'title' => $title,
                    'message' => $message,
                    'status' => 'UNREAD',
                ]);

                if (! empty($recommendationsText)) {
                    Recommendation::create([
                        'alert_id' => $alert->id,
                        'status' => $systemStatus,
                        'source' => 'SmartDecisionService',
                        'condition' => $message,
                        'recommendation' => implode(' ', $recommendationsText),
                    ]);
                }
            }
        }

        // 4. Automatic Control Logic (Kipas)
        $this->handleAutoActuatorControl($latestSensor, $thresholds);

        return $systemStatus;
    }

    protected function handleAutoActuatorControl(?SensorReading $sensor, $thresholds): void
    {
        $fans = Actuator::where('type', 'fan')->where('mode', 'AUTO')->get();
        if ($fans->isEmpty() || ! $sensor) {
            return;
        }

        $tempThresh = $thresholds->get('temperature');
        $maxTemp = $tempThresh ? $tempThresh->max_value : 30.0;

        foreach ($fans as $fan) {
            if ($sensor->temperature >= $maxTemp) {
                if ($fan->status !== 'ON') {
                    $this->actuatorService->executeControl($fan, 'ON', 'AUTO', 'SYSTEM');
                }
            } else {
                if ($fan->status === 'ON') {
                    $this->actuatorService->executeControl($fan, 'OFF', 'AUTO', 'SYSTEM');
                }
            }
        }
    }
}
