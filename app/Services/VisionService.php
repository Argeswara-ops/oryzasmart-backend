<?php

namespace App\Services;

use App\Models\Camera;
use App\Models\Detection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class VisionService
{
    public function __construct(
        public SmartDecisionService $smartDecisionService
    ) {}

    public function processDetection(array $payload): ?Detection
    {
        $cameraCode = $payload['camera_id'] ?? null;
        if (! $cameraCode) {
            Log::warning('Vision detection payload missing camera_id', $payload);

            return null;
        }

        $camera = Camera::firstOrCreate(
            ['camera_code' => $cameraCode],
            [
                'name' => "Camera {$cameraCode}",
                'status' => 'ONLINE',
                'last_seen' => now(),
            ]
        );

        $camera->update([
            'status' => 'ONLINE',
            'last_seen' => now(),
        ]);

        $detectionType = strtoupper($payload['detection_type'] ?? 'UNKNOWN');
        if (! in_array($detectionType, ['PEST', 'DISEASE', 'HEALTHY', 'UNKNOWN'], true)) {
            $detectionType = 'UNKNOWN';
        }

        $detection = Detection::create([
            'camera_id' => $camera->id,
            'detection_type' => $detectionType,
            'class_name' => $payload['class_name'] ?? 'unknown',
            'confidence' => (float) ($payload['confidence'] ?? 0.0),
            'image_path' => $payload['image_path'] ?? null,
            'detected_at' => isset($payload['detected_at']) ? Carbon::parse($payload['detected_at']) : now(),
        ]);

        $this->smartDecisionService->evaluate();

        return $detection;
    }
}
