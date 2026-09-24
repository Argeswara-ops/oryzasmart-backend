<?php

namespace App\Services;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SensorService
{
    public function __construct(
        public SmartDecisionService $smartDecisionService
    ) {}

    public function processPayload(array $payload): ?SensorReading
    {
        $deviceCode = $payload['device_id'] ?? null;
        if (! $deviceCode) {
            Log::warning('Sensor payload missing device_id', $payload);

            return null;
        }

        $device = Device::firstOrCreate(
            ['device_code' => $deviceCode],
            [
                'name' => "Device {$deviceCode}",
                'type' => 'esp32',
                'status' => 'ONLINE',
                'last_seen' => now(),
            ]
        );

        $device->update([
            'status' => 'ONLINE',
            'last_seen' => now(),
        ]);

        $reading = SensorReading::create([
            'device_id' => $device->id,
            'temperature' => isset($payload['temperature']) ? (float) $payload['temperature'] : null,
            'humidity' => isset($payload['humidity']) ? (float) $payload['humidity'] : null,
            'nitrogen' => isset($payload['nitrogen']) ? (float) $payload['nitrogen'] : null,
            'phosphorus' => isset($payload['phosphorus']) ? (float) $payload['phosphorus'] : null,
            'potassium' => isset($payload['potassium']) ? (float) $payload['potassium'] : null,
            'light' => isset($payload['light']) ? (float) $payload['light'] : null,
            'ultrasonic' => isset($payload['ultrasonic']) ? (float) $payload['ultrasonic'] : null,
            'recorded_at' => now(),
        ]);

        $this->smartDecisionService->evaluate();

        return $reading;
    }

    public function getLatest(): array
    {
        $latest = SensorReading::latest('recorded_at')->first();
        if (! $latest) {
            return [
                'temperature' => 0,
                'humidity' => 0,
                'nitrogen' => 0,
                'phosphorus' => 0,
                'potassium' => 0,
                'light' => 0,
                'ultrasonic' => 0,
            ];
        }

        return [
            'temperature' => $latest->temperature,
            'humidity' => $latest->humidity,
            'nitrogen' => $latest->nitrogen,
            'phosphorus' => $latest->phosphorus,
            'potassium' => $latest->potassium,
            'light' => $latest->light,
            'ultrasonic' => $latest->ultrasonic,
            'recorded_at' => $latest->recorded_at,
        ];
    }

    public function getHistory(string $range = 'today'): array
    {
        $query = SensorReading::query();

        match ($range) {
            'today' => $query->whereDate('recorded_at', Carbon::today()),
            '7days' => $query->where('recorded_at', '>=', Carbon::now()->subDays(7)),
            '30days' => $query->where('recorded_at', '>=', Carbon::now()->subDays(30)),
            default => $query->whereDate('recorded_at', Carbon::today()),
        };

        $readings = $query->orderBy('recorded_at', 'asc')->get();
        $fields = ['temperature', 'humidity', 'nitrogen', 'phosphorus', 'potassium', 'light', 'ultrasonic'];

        $summary = [];
        $latest = $readings->last();

        foreach ($fields as $field) {
            $values = $readings->pluck($field)->filter(fn ($v) => ! is_null($v));
            $summary[$field] = [
                'sensor' => $field,
                'current' => $latest ? $latest->{$field} : 0,
                'average' => round($values->avg() ?? 0, 2),
                'minimum' => $values->min() ?? 0,
                'maximum' => $values->max() ?? 0,
            ];
        }

        return [
            'range' => $range,
            'summary' => $summary,
            'series' => $readings->map(fn ($r) => [
                'recorded_at' => $r->recorded_at,
                'temperature' => $r->temperature,
                'humidity' => $r->humidity,
                'nitrogen' => $r->nitrogen,
                'phosphorus' => $r->phosphorus,
                'potassium' => $r->potassium,
                'light' => $r->light,
                'ultrasonic' => $r->ultrasonic,
            ]),
        ];
    }
}
