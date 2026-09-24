<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Detection;
use App\Services\VisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetectionController extends Controller
{
    public function __construct(
        public VisionService $visionService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'camera_id' => 'required|string',
            'detection_type' => 'required|string',
            'class_name' => 'required|string',
            'confidence' => 'required|numeric',
            'image_path' => 'nullable|string',
            'detected_at' => 'nullable|string',
        ]);

        $detection = $this->visionService->processDetection($validated);

        return response()->json([
            'success' => true,
            'message' => 'Vision detection recorded successfully',
            'data' => $detection,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = Detection::with('camera');

        if ($request->has('type')) {
            $query->where('detection_type', strtoupper($request->query('type')));
        }

        if ($request->has('camera')) {
            $cameraCode = $request->query('camera');
            $query->whereHas('camera', function ($q) use ($cameraCode) {
                $q->where('camera_code', $cameraCode);
            });
        }

        $detections = $query->latest('detected_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'Detections retrieved successfully',
            'data' => $detections,
        ]);
    }

    public function latest(): JsonResponse
    {
        $latest = Detection::with('camera')->latest('detected_at')->first();

        return response()->json([
            'success' => true,
            'message' => 'Latest detection retrieved successfully',
            'data' => $latest,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $detection = Detection::with('camera')->find($id);

        if (! $detection) {
            return response()->json([
                'success' => false,
                'message' => 'Detection tidak ditemukan',
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detection detail retrieved successfully',
            'data' => $detection,
        ]);
    }
}
