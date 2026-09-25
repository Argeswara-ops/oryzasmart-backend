<?php

namespace Database\Seeders;

use App\Models\Actuator;
use App\Models\Alert;
use App\Models\Camera;
use App\Models\Detection;
use App\Models\Device;
use App\Models\Recommendation;
use App\Models\SensorReading;
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
        $camera = Camera::firstOrCreate(
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

        // 6. Create dummy sensor readings and vision detections for mobile previews
        $sensorSamples = [
            [
                'recorded_at' => now()->startOfDay()->addHours(7),
                'temperature' => 27.8,
                'humidity' => 73.5,
                'nitrogen' => 42.0,
                'phosphorus' => 24.0,
                'potassium' => 38.0,
                'light' => 620.0,
                'ultrasonic' => 21.0,
            ],
            [
                'recorded_at' => now()->startOfDay()->addHours(10),
                'temperature' => 29.4,
                'humidity' => 78.2,
                'nitrogen' => 36.0,
                'phosphorus' => 22.0,
                'potassium' => 35.0,
                'light' => 710.0,
                'ultrasonic' => 19.5,
            ],
            [
                'recorded_at' => now()->startOfDay()->addHours(13),
                'temperature' => 31.2,
                'humidity' => 86.4,
                'nitrogen' => 27.0,
                'phosphorus' => 18.0,
                'potassium' => 31.0,
                'light' => 680.0,
                'ultrasonic' => 18.0,
            ],
        ];

        foreach ($sensorSamples as $sample) {
            SensorReading::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'recorded_at' => $sample['recorded_at'],
                ],
                array_merge($sample, ['device_id' => $device->id])
            );
        }

        $visionSamples = [
            [
                'detection_type' => 'HEALTHY',
                'class_name' => 'Bibit padi sehat',
                'confidence' => 0.94,
                'image_path' => 'dummy/vision/bibit-sehat-01.jpg',
                'detected_at' => now()->subHours(1),
                'alert' => null,
            ],
            [
                'detection_type' => 'DISEASE',
                'class_name' => 'Bercak daun',
                'confidence' => 0.88,
                'image_path' => 'dummy/vision/bercak-daun-01.jpg',
                'detected_at' => now()->subMinutes(20),
                'alert' => [
                    'severity' => 'CRITICAL',
                    'title' => 'Penyakit daun terdeteksi',
                    'message' => 'Vision mendeteksi gejala bercak daun pada area pembibitan Blok A.',
                    'condition' => 'Deteksi DISEASE bercak daun dengan confidence 88%.',
                    'recommendation' => 'Pisahkan bibit yang menunjukkan gejala, kurangi kelembapan berlebih, dan lakukan pemeriksaan manual pada daun.',
                ],
            ],
            [
                'detection_type' => 'PEST',
                'class_name' => 'Hama wereng',
                'confidence' => 0.81,
                'image_path' => 'dummy/vision/hama-wereng-01.jpg',
                'detected_at' => now(),
                'alert' => [
                    'severity' => 'WARNING',
                    'title' => 'Hama terdeteksi',
                    'message' => 'Vision mendeteksi indikasi hama wereng pada bibit padi.',
                    'condition' => 'Deteksi PEST hama wereng dengan confidence 81%.',
                    'recommendation' => 'Periksa permukaan daun dan batang bibit, lalu lakukan pengendalian hama sesuai SOP pembibitan.',
                ],
            ],
        ];

        foreach ($visionSamples as $sample) {
            $detection = Detection::updateOrCreate(
                ['image_path' => $sample['image_path']],
                [
                    'camera_id' => $camera->id,
                    'detection_type' => $sample['detection_type'],
                    'class_name' => $sample['class_name'],
                    'confidence' => $sample['confidence'],
                    'image_path' => $sample['image_path'],
                    'detected_at' => $sample['detected_at'],
                ]
            );

            if (! $sample['alert']) {
                continue;
            }

            $alert = Alert::updateOrCreate(
                [
                    'source' => 'SeederVisionDummy',
                    'detection_id' => $detection->id,
                ],
                [
                    'device_id' => $device->id,
                    'camera_id' => $camera->id,
                    'type' => 'VISION',
                    'severity' => $sample['alert']['severity'],
                    'title' => $sample['alert']['title'],
                    'message' => $sample['alert']['message'],
                    'status' => 'UNREAD',
                    'read_at' => null,
                ]
            );

            Recommendation::updateOrCreate(
                [
                    'source' => 'SeederVisionDummy',
                    'alert_id' => $alert->id,
                ],
                [
                    'status' => $sample['alert']['severity'],
                    'condition' => $sample['alert']['condition'],
                    'recommendation' => $sample['alert']['recommendation'],
                ]
            );
        }
    }
}
