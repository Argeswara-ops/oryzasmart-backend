<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorThreshold;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThresholdController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Sensor thresholds retrieved successfully',
            'data' => SensorThreshold::orderBy('sensor_type')->get(),
        ]);
    }

    public function update(Request $request, string $sensorType): JsonResponse
    {
        $validated = $request->validate([
            'min_value' => ['required', 'numeric'],
            'max_value' => ['required', 'numeric', 'gte:min_value'],
        ]);

        $threshold = SensorThreshold::updateOrCreate(
            ['sensor_type' => strtolower($sensorType)],
            $validated,
        );

        return response()->json([
            'success' => true,
            'message' => 'Sensor threshold updated successfully',
            'data' => $threshold,
        ]);
    }
}
