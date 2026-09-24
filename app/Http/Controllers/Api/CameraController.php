<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use Illuminate\Http\JsonResponse;

class CameraController extends Controller
{
    public function index(): JsonResponse
    {
        $cameras = Camera::all();

        return response()->json([
            'success' => true,
            'message' => 'Cameras retrieved successfully',
            'data' => $cameras,
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        $camera = is_numeric($id)
            ? Camera::find($id)
            : Camera::where('camera_code', $id)->first();

        if (! $camera) {
            return response()->json([
                'success' => false,
                'message' => 'Kamera tidak ditemukan',
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Camera detail retrieved successfully',
            'data' => $camera,
        ]);
    }

    public function status(int|string $id): JsonResponse
    {
        $camera = is_numeric($id)
            ? Camera::find($id)
            : Camera::where('camera_code', $id)->first();

        if (! $camera) {
            return response()->json([
                'success' => false,
                'message' => 'Kamera tidak ditemukan',
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Camera status retrieved successfully',
            'data' => [
                'camera_code' => $camera->camera_code,
                'status' => $camera->status,
                'last_seen' => $camera->last_seen,
            ],
        ]);
    }
}
