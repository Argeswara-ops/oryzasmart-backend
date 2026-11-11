<?php

namespace Database\Seeders;

use App\Models\Actuator;
use App\Models\ActuatorLog;
use App\Models\Alert;
use App\Models\Device;
use App\Models\Recommendation;
use App\Models\SensorReading;
use App\Models\SensorThreshold;
use App\Models\User;
use App\Models\VisionScan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Pengguna (Admin & Operator)
        $admin = User::firstOrCreate(
            ['email' => 'admin@padibackend.test'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator@padibackend.test'],
            [
                'name' => 'Operator Pembibitan',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ],
        );

        $technician = User::firstOrCreate(
            ['email' => 'teknisi@padibackend.test'],
            [
                'name' => 'Budi Santoso (Teknisi)',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ],
        );

        // 2. Perangkat IoT (ESP32 & Node)
        $device1 = Device::updateOrCreate(
            ['device_code' => 'ESP32-01'],
            [
                'name' => 'ESP32 Utama',
                'type' => 'esp32',
                'location' => 'Rumah Kaca Pembibitan',
                'status' => 'ONLINE',
                'last_seen' => now(),
            ],
        );

        $device2 = Device::updateOrCreate(
            ['device_code' => 'ESP32-02'],
            [
                'name' => 'Node Sawah Blok B',
                'type' => 'esp32',
                'location' => 'Petak Sawah Barat',
                'status' => 'ONLINE',
                'last_seen' => now()->subMinutes(3),
            ],
        );

        $deviceCamera = Device::updateOrCreate(
            ['device_code' => 'CAM-01'],
            [
                'name' => 'Kamera Monitor Daun',
                'type' => 'camera',
                'location' => 'Lahan Riset Agrotek',
                'status' => 'ONLINE',
                'last_seen' => now()->subMinutes(12),
            ],
        );

        // 3. Aktuator Kipas & Pompa
        $actuator1 = Actuator::updateOrCreate(
            ['device_id' => $device1->id, 'type' => 'fan'],
            [
                'name' => 'Pompa Air & Kipas 01',
                'mode' => 'AUTO',
                'status' => 'ON',
            ],
        );

        $actuator2 = Actuator::updateOrCreate(
            ['device_id' => $device2->id, 'type' => 'fan'],
            [
                'name' => 'Ventilasi Sawah 02',
                'mode' => 'AUTO',
                'status' => 'OFF',
            ],
        );

        // 4. Actuator Logs (Audit trail)
        if (ActuatorLog::count() < 5) {
            $logSamples = [
                ['command' => 'ON', 'mode' => 'AUTO', 'source' => 'SYSTEM', 'prev' => 'OFF', 'new' => 'ON', 'user' => null, 'time' => now()->subMinutes(15)],
                ['command' => 'ON', 'mode' => 'AUTO', 'source' => 'USER', 'prev' => 'OFF', 'new' => 'ON', 'user' => $operator->id, 'time' => now()->subHours(2)],
                ['command' => 'OFF', 'mode' => 'MANUAL', 'source' => 'USER', 'prev' => 'ON', 'new' => 'OFF', 'user' => $admin->id, 'time' => now()->subHours(4)],
                ['command' => 'ON', 'mode' => 'AUTO', 'source' => 'SYSTEM', 'prev' => 'OFF', 'new' => 'ON', 'user' => null, 'time' => now()->subHours(6)],
                ['command' => 'OFF', 'mode' => 'AUTO', 'source' => 'SYSTEM', 'prev' => 'ON', 'new' => 'OFF', 'user' => null, 'time' => now()->subHours(9)],
            ];

            foreach ($logSamples as $l) {
                ActuatorLog::create([
                    'actuator_id' => $actuator1->id,
                    'user_id' => $l['user'],
                    'command' => $l['command'],
                    'mode' => $l['mode'],
                    'source' => $l['source'],
                    'previous_status' => $l['prev'],
                    'new_status' => $l['new'],
                    'created_at' => $l['time'],
                    'updated_at' => $l['time'],
                ]);
            }
        }

        // 5. Ambang Batas Sensor
        foreach (SensorThreshold::DEFAULTS as $sensorType => $values) {
            SensorThreshold::updateOrCreate(
                ['sensor_type' => $sensorType],
                $values,
            );
        }

        // 6. Sensor Readings (35 titik data telemetri agar grafik dan KPI hidup)
        if (SensorReading::count() < 25) {
            $baseTime = now()->subHours(24);
            $temps = [27.2, 27.5, 26.8, 26.5, 26.2, 26.0, 26.8, 27.9, 29.1, 30.5, 31.4, 32.2, 31.8, 31.0, 30.2, 29.5, 28.8, 28.2, 27.9, 28.1, 28.4, 28.6, 28.8, 29.2, 29.5];
            $hums =  [82,   84,   85,   86,   88,   87,   83,   78,   74,   68,   65,   62,   64,   68,   72,   75,   78,   80,   82,   80,   78,   76,   75,   74,   72];
            $waters =[75,   75,   74,   74,   73,   73,   74,   76,   78,   80,   80,   79,   78,   78,   77,   77,   78,   78,   79,   80,   82,   82,   80,   80,   82];

            $totalPoints = count($temps);
            for ($i = 0; $i < $totalPoints; $i++) {
                $pointTime = (clone $baseTime)->addMinutes($i * 58);
                SensorReading::create([
                    'device_id' => $device1->id,
                    'uptime_s' => ($i + 1) * 3600,
                    'temperature' => $temps[$i],
                    'humidity' => $hums[$i],
                    'nitrogen' => 45 + ($i % 8),
                    'phosphorus' => 24 + ($i % 5),
                    'potassium' => 35 + ($i % 6),
                    'light' => 600 + ($i * 25),
                    'light_percent' => min(95, 55 + ($i % 35)),
                    'uv_index' => round(2.5 + (($i % 6) * 0.4), 1),
                    'ultrasonic' => 15.0 - (($i % 4) * 0.5),
                    'distance_cm' => 15.0 - (($i % 4) * 0.5),
                    'level_percent' => $waters[$i],
                    'fan_state' => $temps[$i] > 30 ? 'ON' : 'OFF',
                    'fan_mode' => 'AUTO',
                    'recorded_at' => $pointTime,
                    'created_at' => $pointTime,
                    'updated_at' => $pointTime,
                ]);
            }
        }

        // 7. Alerts & Recommendations
        if (Alert::count() < 4) {
            $alert1 = Alert::create([
                'device_id' => $device1->id,
                'type' => 'THRESHOLD_EXCEEDED',
                'source' => 'SENSOR',
                'severity' => 'CRITICAL',
                'title' => 'Suhu Lingkungan Tinggi',
                'message' => 'Suhu udara mencapai 32.2°C di atas batas toleransi maksimum. Kipas pendingin otomatis diaktifkan.',
                'status' => 'UNREAD',
                'created_at' => now()->subMinutes(25),
                'updated_at' => now()->subMinutes(25),
            ]);
            Recommendation::create([
                'alert_id' => $alert1->id,
                'status' => 'CRITICAL',
                'source' => 'SMART_DECISION',
                'condition' => 'HIGH_TEMP',
                'recommendation' => 'Pastikan ventilasi rumah kaca terbuka maksimal dan sirkulasi air pembibitan mengalir stabil.',
            ]);

            $alert2 = Alert::create([
                'device_id' => $device1->id,
                'type' => 'THRESHOLD_EXCEEDED',
                'source' => 'SENSOR',
                'severity' => 'WARNING',
                'title' => 'Kelembaban Udara Turun',
                'message' => 'Kelembaban udara tercatat 62% mendekati batas kritis minimum (60%).',
                'status' => 'UNREAD',
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ]);
            Recommendation::create([
                'alert_id' => $alert2->id,
                'status' => 'WARNING',
                'source' => 'SMART_DECISION',
                'condition' => 'LOW_HUMIDITY',
                'recommendation' => 'Jalankan penyemprotan kabut air halus (mist sprayer) untuk menjaga kelembaban mikro.',
            ]);

            $alert3 = Alert::create([
                'device_id' => $device2->id,
                'type' => 'SYSTEM_INFO',
                'source' => 'DEVICE',
                'severity' => 'INFO',
                'title' => 'Ketinggian Air Normal',
                'message' => 'Level air petak sawah blok barat stabil pada 82% elevasi bibit aman.',
                'status' => 'READ',
                'read_at' => now()->subHours(3),
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(3),
            ]);
            Recommendation::create([
                'alert_id' => $alert3->id,
                'status' => 'INFO',
                'source' => 'SMART_DECISION',
                'condition' => 'NORMAL_WATER',
                'recommendation' => 'Pertahankan debit irigasi saat ini.',
            ]);

            $alert4 = Alert::create([
                'device_id' => $device1->id,
                'type' => 'NUTRIENT_WARNING',
                'source' => 'SENSOR',
                'severity' => 'WARNING',
                'title' => 'Kadar Nitrogen Menurun',
                'message' => 'Sensor NPK mendeteksi penurunan kadar nitrogen pada area persemaian.',
                'status' => 'READ',
                'read_at' => now()->subHours(6),
                'created_at' => now()->subHours(8),
                'updated_at' => now()->subHours(6),
            ]);
            Recommendation::create([
                'alert_id' => $alert4->id,
                'status' => 'WARNING',
                'source' => 'SMART_DECISION',
                'condition' => 'LOW_NUTRIENT',
                'recommendation' => 'Berikan larutan nutrisi urea cair berkadar 1% pada media semai.',
            ]);
        }

        // 8. Vision Scans (Data arsip deteksi daun padi)
        if (VisionScan::count() < 4) {
            VisionScan::create([
                'user_id' => $operator->id,
                'client_id' => 'scan_client_' . uniqid(),
                'scan_type' => 'disease',
                'class_name' => 'leaf_blast',
                'display_name' => 'Blas Daun (Pyricularia)',
                'confidence' => 0.9412500,
                'image_path' => 'vision/sample_leaf_blast.jpg',
                'scanned_at' => now()->subHours(1),
            ]);

            VisionScan::create([
                'user_id' => $operator->id,
                'client_id' => 'scan_client_' . uniqid(),
                'scan_type' => 'disease',
                'class_name' => 'brown_spot',
                'display_name' => 'Bercak Cokelat Daun',
                'confidence' => 0.8875000,
                'image_path' => 'vision/sample_brown_spot.jpg',
                'scanned_at' => now()->subHours(3),
            ]);

            VisionScan::create([
                'user_id' => $technician->id,
                'client_id' => 'scan_client_' . uniqid(),
                'scan_type' => 'disease',
                'class_name' => 'bacterial_leaf_blight',
                'display_name' => 'Hawar Daun Bakteri',
                'confidence' => 0.9150000,
                'image_path' => 'vision/sample_bacterial.jpg',
                'scanned_at' => now()->subHours(7),
            ]);

            VisionScan::create([
                'user_id' => $admin->id,
                'client_id' => 'scan_client_' . uniqid(),
                'scan_type' => 'pest',
                'class_name' => 'stem_borer',
                'display_name' => 'Penggerek Batang Padi',
                'confidence' => 0.9620000,
                'image_path' => 'vision/sample_pest.jpg',
                'scanned_at' => now()->subDay(),
            ]);
        }
    }
}
