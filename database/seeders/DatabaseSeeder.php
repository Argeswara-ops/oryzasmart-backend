<?php

namespace Database\Seeders;

use App\Models\Actuator;
use App\Models\Camera;
use App\Models\Device;
use App\Models\SensorThreshold;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Operator User
        User::firstOrCreate(
            ['email' => 'operator@padibackend.test'],
            [
                'name' => 'Operator Pembibitan',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ]
        );

        // 2. Create ESP32 Device
        $device = Device::firstOrCreate(
            ['device_code' => 'ESP32-01'],
            [
                'name' => 'ESP32 Utama',
                'type' => 'esp32',
                'location' => 'Rumah Kaca Pembibitan',
                'status' => 'ONLINE',
                'last_seen' => now(),
            ]
        );

        // 3. Create Camera Device
        Camera::firstOrCreate(
            ['camera_code' => 'CAM-01'],
            [
                'name' => 'Camera Vision Pembibitan 01',
                'location' => 'Blok A Pembibitan',
                'stream_url' => 'http://192.168.1.100:8080/stream',
                'status' => 'ONLINE',
                'last_seen' => now(),
            ]
        );

        // 4. Create Fan Actuator
        Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'fan'],
            [
                'name' => 'Kipas Ventilasi 01',
                'mode' => 'AUTO',
                'status' => 'OFF',
            ]
        );

        // 5. Create Default Sensor Thresholds
        $thresholds = [
            ['sensor_type' => 'temperature', 'min_value' => 20.0, 'max_value' => 30.0],
            ['sensor_type' => 'humidity', 'min_value' => 60.0, 'max_value' => 85.0],
            ['sensor_type' => 'nitrogen', 'min_value' => 30.0, 'max_value' => 80.0],
            ['sensor_type' => 'phosphorus', 'min_value' => 15.0, 'max_value' => 50.0],
            ['sensor_type' => 'potassium', 'min_value' => 20.0, 'max_value' => 60.0],
            ['sensor_type' => 'light', 'min_value' => 200.0, 'max_value' => 1000.0],
            ['sensor_type' => 'ultrasonic', 'min_value' => 5.0, 'max_value' => 50.0],
        ];

        foreach ($thresholds as $t) {
            SensorThreshold::updateOrCreate(['sensor_type' => $t['sensor_type']], $t);
        }
    }
}
