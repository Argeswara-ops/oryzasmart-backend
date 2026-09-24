<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Camera;
use App\Models\Device;

class AlertService
{
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
        return Alert::firstOrCreate([
            'device_id' => $device->id,
            'type' => 'DEVICE_OFFLINE',
            'status' => 'UNREAD',
        ], [
            'source' => 'DeviceMonitor',
            'severity' => 'CRITICAL',
            'title' => "Perangkat {$device->device_code} offline",
            'message' => 'Perangkat tidak mengirim heartbeat dalam batas waktu yang ditentukan.',
        ]);
    }

    public function createCameraOfflineAlert(Camera $camera): Alert
    {
        return Alert::firstOrCreate([
            'camera_id' => $camera->id,
            'type' => 'CAMERA_OFFLINE',
            'status' => 'UNREAD',
        ], [
            'source' => 'DeviceMonitor',
            'severity' => 'CRITICAL',
            'title' => "Kamera {$camera->camera_code} offline",
            'message' => 'Kamera tidak mengirim heartbeat dalam batas waktu yang ditentukan.',
        ]);
    }
}
