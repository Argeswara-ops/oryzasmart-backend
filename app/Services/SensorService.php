<?php

namespace App\Services;

use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SensorService
{
    public function __construct(
        public SmartDecisionService $smartDecisionService
    ) {}

    public function processPayload(array $payload): ?SensorReading
    {
        $deviceCode = $payload['device_id'] ?? null;
        $validator = Validator::make($payload, [
            'device_id' => ['required', 'string', 'max:100'],
            'temperature' => ['required', 'numeric'],
            'humidity' => ['required', 'numeric'],
            'nitrogen' => ['required', 'numeric'],
            'phosphorus' => ['required', 'numeric'],
            'potassium' => ['required', 'numeric'],
            'light' => ['required', 'numeric'],
            'ultrasonic' => ['required', 'numeric'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid MQTT sensor payload', [
                'payload' => $payload,
                'errors' => $validator->errors()->toArray(),
            ]);

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
            'temperature' => (float) $payload['temperature'],
            'humidity' => (float) $payload['humidity'],
            'nitrogen' => (float) $payload['nitrogen'],
            'phosphorus' => (float) $payload['phosphorus'],
            'potassium' => (float) $payload['potassium'],
            'light' => (float) $payload['light'],
            'ultrasonic' => (float) $payload['ultrasonic'],
            'recorded_at' => isset($payload['recorded_at']) ? Carbon::parse($payload['recorded_at']) : now(),
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
