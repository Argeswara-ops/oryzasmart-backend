<?php

namespace App\Services;

use App\Models\Actuator;
use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Support\Arr;
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
        $payload = $this->normalizePayload($payload);
        $deviceCode = $payload['device_id'] ?? null;

        $validator = Validator::make($payload, [
            'device_id' => ['required', 'string', 'max:100'],
            'uptime_s' => ['nullable', 'integer', 'min:0'],
            'temperature' => ['required', 'numeric'],
            'humidity' => ['required', 'numeric'],
            'nitrogen' => ['nullable', 'numeric'],
            'phosphorus' => ['nullable', 'numeric'],
            'potassium' => ['nullable', 'numeric'],
            'light' => ['nullable', 'numeric'],
            'light_percent' => ['nullable', 'numeric'],
            'uv_index' => ['nullable', 'numeric'],
            'ultrasonic' => ['nullable', 'numeric'],
            'distance_cm' => ['nullable', 'numeric'],
            'level_percent' => ['nullable', 'numeric'],
            'fan_state' => ['nullable', 'string', 'max:50'],
            'fan_mode' => ['nullable', 'string', 'max:50'],
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
            'uptime_s' => isset($payload['uptime_s']) ? (int) $payload['uptime_s'] : null,
            'temperature' => (float) $payload['temperature'],
            'humidity' => (float) $payload['humidity'],
            'nitrogen' => isset($payload['nitrogen']) ? (float) $payload['nitrogen'] : null,
            'phosphorus' => isset($payload['phosphorus']) ? (float) $payload['phosphorus'] : null,
            'potassium' => isset($payload['potassium']) ? (float) $payload['potassium'] : null,
            'light' => isset($payload['light']) ? (float) $payload['light'] : null,
            'light_percent' => isset($payload['light_percent']) ? (float) $payload['light_percent'] : null,
            'uv_index' => isset($payload['uv_index']) ? (float) $payload['uv_index'] : null,
            'ultrasonic' => isset($payload['ultrasonic']) ? (float) $payload['ultrasonic'] : null,
            'distance_cm' => isset($payload['distance_cm']) ? (float) $payload['distance_cm'] : null,
            'level_percent' => isset($payload['level_percent']) ? (float) $payload['level_percent'] : null,
            'fan_state' => $payload['fan_state'] ?? null,
            'fan_mode' => $payload['fan_mode'] ?? null,
            'recorded_at' => isset($payload['recorded_at']) ? Carbon::parse($payload['recorded_at']) : now(),
        ]);

        $this->smartDecisionService->evaluate();
        $this->syncFanState($device, $payload);

        return $reading;
    }

    public function getLatest(?Device $device = null): array
    {
        $latest = SensorReading::query()
            ->when($device, fn ($query) => $query->whereBelongsTo($device))
            ->latest('id')
            ->first();
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
            'light_percent' => $latest->light_percent,
            'uv_index' => $latest->uv_index,
            'ultrasonic' => $latest->ultrasonic,
            'distance_cm' => $latest->distance_cm,
            'level_percent' => $latest->level_percent,
            'water_level_percent' => $latest->level_percent,
            'water_level' => [
                'distance_cm' => $latest->distance_cm,
                'percent' => $latest->level_percent,
            ],
            'fan_state' => $latest->fan_state,
            'fan_mode' => $latest->fan_mode,
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
        $latest = $readings->sortByDesc('id')->first();

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
                'light_percent' => $r->light_percent,
                'uv_index' => $r->uv_index,
                'ultrasonic' => $r->ultrasonic,
                'distance_cm' => $r->distance_cm,
                'level_percent' => $r->level_percent,
                'fan_state' => $r->fan_state,
                'fan_mode' => $r->fan_mode,
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        $lightPercent = Arr::get($payload, 'light.percent', Arr::get($payload, 'light_percent'));
        $light = Arr::get($payload, 'light.value', Arr::get($payload, 'light', $lightPercent));
        $distanceCm = Arr::get($payload, 'ultrasonic.distance_cm', Arr::get($payload, 'distance_cm'));
        $ultrasonic = Arr::get($payload, 'ultrasonic.value', Arr::get($payload, 'ultrasonic', $distanceCm));

        return [
            ...$payload,
            'device_id' => Arr::get($payload, 'device_id', Arr::get($payload, 'deviceId', Arr::get($payload, 'device'))),
            'uptime_s' => Arr::get($payload, 'uptime_s'),
            'temperature' => Arr::get($payload, 'temperature'),
            'humidity' => Arr::get($payload, 'humidity'),
            'nitrogen' => Arr::get($payload, 'npk.n', Arr::get($payload, 'nitrogen')),
            'phosphorus' => Arr::get($payload, 'npk.p', Arr::get($payload, 'phosphorus')),
            'potassium' => Arr::get($payload, 'npk.k', Arr::get($payload, 'potassium')),
            'light' => is_array($light) ? $lightPercent : $light,
            'light_percent' => $lightPercent,
            'uv_index' => Arr::get($payload, 'light.uv_index', Arr::get($payload, 'uv_index')),
            'ultrasonic' => is_array($ultrasonic) ? $distanceCm : $ultrasonic,
            'distance_cm' => $distanceCm,
            'level_percent' => Arr::get($payload, 'ultrasonic.level_percent', Arr::get($payload, 'level_percent')),
            'fan_state' => Arr::get($payload, 'fan.state', Arr::get($payload, 'fan_state')),
            'fan_mode' => Arr::get($payload, 'fan.mode', Arr::get($payload, 'fan_mode')),
            'recorded_at' => Arr::get($payload, 'recorded_at'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function syncFanState(Device $device, array $payload): void
    {
        $fanState = mb_strtoupper((string) ($payload['fan_state'] ?? ''));
        $fanMode = mb_strtoupper((string) ($payload['fan_mode'] ?? ''));

        if (! in_array($fanState, ['ON', 'OFF'], true)) {
            return;
        }

        if (! in_array($fanMode, ['AUTO', 'MANUAL'], true)) {
            $fanMode = 'AUTO';
        }

        Actuator::updateOrCreate(
            ['device_id' => $device->id, 'type' => 'fan'],
            [
                'name' => 'Kipas Ventilasi',
                'status' => $fanState,
                'mode' => $fanMode,
            ],
        );
    }
}
