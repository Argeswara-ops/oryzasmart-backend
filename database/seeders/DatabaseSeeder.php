<?php

namespace Database\Seeders;

use App\Models\Actuator;
use App\Models\Device;
use App\Models\SensorThreshold;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
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
            ['device_code' => 'ESP32-01'],
            [
                'name' => 'ESP32 Utama',
                'type' => 'esp32',
                'location' => 'Rumah Kaca Pembibitan',
                'status' => 'OFFLINE',
                'last_seen' => null,
            ],
        );

        Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'fan'],
            [
                'name' => 'Pompa Air 01',
                'mode' => 'AUTO',
                'status' => 'OFF',
            ],
        );

        foreach (SensorThreshold::DEFAULTS as $sensorType => $values) {
            SensorThreshold::updateOrCreate(
                ['sensor_type' => $sensorType],
                $values,
            );
        }
    }
}
