<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;

class DeviceController extends Controller
{
    public function index(): JsonResponse
    {
        $devices = Device::all();

        return response()->json([
            'success' => true,
            'message' => 'Devices retrieved successfully',
            'data' => $devices,
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        $device = is_numeric($id)
            ? Device::find($id)
            : Device::where('device_code', $id)->first();

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
            'data' => $device,
        ]);
    }

    public function status(int|string $id): JsonResponse
    {
        $device = is_numeric($id)
            ? Device::find($id)
            : Device::where('device_code', $id)->first();

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
                'last_seen' => $device->last_seen,
            ],
        ]);
    }
}
