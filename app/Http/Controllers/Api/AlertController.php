<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\JsonResponse;

class AlertController extends Controller
{
    public function __construct(
        public AlertService $alertService
    ) {}

    public function index(): JsonResponse
    {
        $alerts = Alert::with(['device', 'camera', 'detection', 'recommendation'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Alerts retrieved successfully',
            'data' => $alerts,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $alert = Alert::with(['device', 'camera', 'detection', 'recommendation'])->find($id);

        if (! $alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alert tidak ditemukan',
                'errors' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alert detail retrieved successfully',
            'data' => $alert,
        ]);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $alert = Alert::find($id);

        if (! $alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alert tidak ditemukan',
                'errors' => null,
            ], 404);
        }

        $updated = $this->alertService->markAsRead($alert);

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read',
            'data' => $updated,
        ]);
    }
}
