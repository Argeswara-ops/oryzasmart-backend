<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorThreshold;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ThresholdController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Sensor thresholds retrieved successfully',
            'data' => SensorThreshold::query()
                ->whereIn('sensor_type', SensorThreshold::SUPPORTED_TYPES)
                ->orderBy('sensor_type')
                ->get(),
        ]);
    }

    public function update(Request $request, string $sensorType): JsonResponse
    {
        $normalizedSensorType = mb_strtolower($sensorType);
        $validatedSensorType = Validator::make(
            ['sensor_type' => $normalizedSensorType],
            ['sensor_type' => ['required', Rule::in(SensorThreshold::SUPPORTED_TYPES)]],
        )->validate()['sensor_type'];

        $validated = $request->validate([
            'min_value' => ['required', 'numeric'],
            'max_value' => ['required', 'numeric', 'gte:min_value'],
        ]);

        $threshold = SensorThreshold::updateOrCreate(
            ['sensor_type' => $validatedSensorType],
            $validated,
        );

        return response()->json([
            'success' => true,
            'message' => 'Sensor threshold updated successfully',
            'data' => $threshold,
        ]);
    }
}
