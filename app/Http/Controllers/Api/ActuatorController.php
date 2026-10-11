<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actuator;
use App\Models\ActuatorLog;
use App\Models\Device;
use App\Services\ActuatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActuatorController extends Controller
{
    public function __construct(
        public ActuatorService $actuatorService
    ) {}

    public function controlFan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
            'command' => ['required', 'string', 'in:ON,OFF,AUTO,on,off,auto'],
            'mode' => ['nullable', 'string', 'in:AUTO,MANUAL,auto,manual'],
        ]);

        $deviceCode = $validated['device_id'];
        $device = Device::where('device_code', $deviceCode)->first();

        if (! $device) {
            $device = Device::create([
                'device_code' => $deviceCode,
                'name' => "Device {$deviceCode}",
                'status' => 'ONLINE',
                'last_seen' => now(),
            ]);
        }

        $actuator = Actuator::firstOrCreate(
            ['device_id' => $device->id, 'type' => 'fan'],
            ['name' => 'Kipas Ventilasi', 'mode' => strtoupper($validated['mode'] ?? 'MANUAL'), 'status' => 'OFF']
        );

        $command = strtoupper($validated['command']);
        $mode = strtoupper($validated['mode'] ?? ($command === 'AUTO' ? 'AUTO' : 'MANUAL'));

        $user = $request->user();
        $source = $user ? 'USER' : 'SYSTEM';

        $this->actuatorService->executeControl($actuator, $command, $mode, $source, $user);

        return response()->json([
            'success' => true,
            'message' => "Kipas berhasil diubah menjadi {$command} (Mode: {$mode})",
            'data' => [
                'device_id' => $deviceCode,
                'actuator' => $actuator->fresh(),
            ],
        ]);
    }

    public function statusFan(): JsonResponse
    {
        $fan = Actuator::where('type', 'fan')->first();

        return response()->json([
            'success' => true,
            'message' => 'Fan status retrieved successfully',
            'data' => [
                'status' => $fan ? $fan->status : 'OFF',
                'mode' => $fan ? $fan->mode : 'AUTO',
                'updated_at' => $fan ? $fan->updated_at : null,
            ],
        ]);
    }

    public function logs(): JsonResponse
    {
        $logs = ActuatorLog::with(['actuator', 'user'])->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Actuator logs retrieved successfully',
            'data' => $logs,
        ]);
    }
}
