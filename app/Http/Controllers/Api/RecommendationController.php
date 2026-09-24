<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    public function index(): JsonResponse
    {
        $recommendations = Recommendation::with('alert')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Recommendations retrieved successfully',
            'data' => $recommendations,
        ]);
    }

    public function latest(): JsonResponse
    {
        $latest = Recommendation::with('alert')->latest()->first();

        return response()->json([
            'success' => true,
            'message' => 'Latest recommendation retrieved successfully',
            'data' => $latest,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $recommendation = Recommendation::with('alert')->find($id);

        if (! $recommendation) {
            return response()->json([
                'success' => false,
                'message' => 'Rekomendasi tidak ditemukan',
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Recommendation detail retrieved successfully',
            'data' => $recommendation,
        ]);
    }
}
