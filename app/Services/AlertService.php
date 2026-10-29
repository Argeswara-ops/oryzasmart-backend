<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Camera;
use App\Models\Device;
use Illuminate\Support\Facades\Log;

class AlertService
{
    public function __construct(
        protected ?FirebaseService $firebaseService = null
    ) {
        $this->firebaseService = $firebaseService ?? app(FirebaseService::class);
    }

    public function markAsRead(Alert $alert): Alert
    {
        $alert->update([
            'status' => 'READ',
            'read_at' => now(),
        ]);

        return $alert;
    }

    public function createDeviceOfflineAlert(Device $device): Alert
    {
        $alert = Alert::firstOrCreate([
            'device_id' => $device->id,
            'type' => 'DEVICE_OFFLINE',
            'status' => 'UNREAD',
        ], [
            'source' => 'DeviceMonitor',
            'severity' => 'CRITICAL',
            'title' => "Perangkat {$device->device_code} offline",
            'message' => 'Perangkat tidak mengirim heartbeat dalam batas waktu yang ditentukan.',
        ]);

        if ($alert->wasRecentlyCreated) {
            $this->notifyAlert($alert);
        }

        return $alert;
    }

    public function createCameraOfflineAlert(Camera $camera): Alert
    {
        $alert = Alert::firstOrCreate([
            'camera_id' => $camera->id,
            'type' => 'CAMERA_OFFLINE',
            'status' => 'UNREAD',
        ], [
            'source' => 'DeviceMonitor',
            'severity' => 'CRITICAL',
            'title' => "Kamera {$camera->camera_code} offline",
            'message' => 'Kamera tidak mengirim heartbeat dalam batas waktu yang ditentukan.',
        ]);

        if ($alert->wasRecentlyCreated) {
            $this->notifyAlert($alert);
        }

        return $alert;
    }

    public function notifyAlert(Alert $alert): void
    {
        try {
            $this->firebaseService->broadcastToUsers(
                $alert->title,
                $alert->message,
                [
                    'type' => $alert->type,
                    'alert_id' => (string) $alert->id,
                    'severity' => $alert->severity,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to send Firebase alert notification: ' . $e->getMessage());
        }
    }
}
