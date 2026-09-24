<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SensorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function __construct(
        public SensorService $sensorService
    ) {}

    public function latest(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Latest sensor data retrieved successfully',
            'data' => $this->sensorService->getLatest(),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'range' => ['nullable', 'in:today,7days,30days'],
        ]);
        $range = $validated['range'] ?? 'today';

        return response()->json([
            'success' => true,
            'message' => 'Sensor history retrieved successfully',
            'data' => $this->sensorService->getHistory($range),
        ]);
    }
}
