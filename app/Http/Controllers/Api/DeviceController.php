<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    public function index(): JsonResponse
    {
        $devices = Device::query()
            ->where('type', 'esp32')
            ->with(['fan', 'latestSensorReading'])
            ->orderByDesc('last_seen')
            ->orderBy('device_code')
            ->get()
            ->map(fn (Device $device): array => $this->deviceData($device));

        return response()->json([
            'success' => true,
            'message' => 'Devices retrieved successfully',
            'data' => $devices,
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        $device = $this->findDevice($id);

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat tidak ditemukan',
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device detail retrieved successfully',
            'data' => $this->deviceData($device),
        ]);
    }

    public function status(int|string $id): JsonResponse
    {
        $device = $this->findDevice($id);

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat tidak ditemukan',
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Device status retrieved successfully',
            'data' => [
                'device_code' => $device->device_code,
                'status' => $device->status,
                'is_online' => $device->status === 'ONLINE',
                'last_seen' => $device->last_seen,
                'fan' => $this->fanData($device),
            ],
        ]);
    }

    private function findDevice(int|string $id): ?Device
    {
        $query = Device::query()
            ->where('type', 'esp32')
            ->with(['fan', 'latestSensorReading']);

        return is_numeric($id)
            ? $query->find((int) $id)
            : $query->where('device_code', $id)->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function deviceData(Device $device): array
    {
        $sensor = $device->latestSensorReading;

        return [
            'id' => $device->id,
            'device_code' => $device->device_code,
            'name' => $device->name,
            'type' => $device->type,
            'location' => $device->location,
            'status' => $device->status,
            'is_online' => $device->status === 'ONLINE',
            'last_seen' => $device->last_seen,
            'fan' => $this->fanData($device),
            'latest_sensor' => $sensor ? [
                'temperature' => $sensor->temperature,
                'humidity' => $sensor->humidity,
                'nitrogen' => $sensor->nitrogen,
                'phosphorus' => $sensor->phosphorus,
                'potassium' => $sensor->potassium,
                'light_percent' => $sensor->light_percent,
                'uv_index' => $sensor->uv_index,
                'distance_cm' => $sensor->distance_cm,
                'water_level_percent' => $sensor->level_percent,
                'recorded_at' => $sensor->recorded_at,
            ] : null,
        ];
    }

    /**
     * @return array{status: string, is_on: bool, mode: string}|null
     */
    private function fanData(Device $device): ?array
    {
        if (! $device->fan) {
            return null;
        }

        return [
            'status' => $device->fan->status,
            'is_on' => $device->fan->status === 'ON',
            'mode' => $device->fan->mode,
        ];
    }
}
