<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Device;
use App\Services\SensorService;
use App\Services\SmartDecisionService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        public SensorService $sensorService,
        public SmartDecisionService $smartDecisionService
    ) {}

    public function index(): JsonResponse
    {
        $systemStatus = $this->smartDecisionService->evaluate();

        $latestDevice = Device::query()
            ->where('type', 'esp32')
            ->with('fan')
            ->latest('last_seen')
            ->first();
        $deviceStatus = $latestDevice ? $latestDevice->status : 'OFFLINE';

        $sensorsData = $this->sensorService->getLatest($latestDevice);
        $fan = $latestDevice?->fan;
        $fanData = [
            'status' => $fan ? $fan->status : 'OFF',
            'is_on' => $fan?->status === 'ON',
            'mode' => $fan ? $fan->mode : 'AUTO',
        ];

        $unreadAlerts = Alert::where('status', 'UNREAD')->count();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'system_status' => $systemStatus,
                'device_status' => $deviceStatus,
                'device' => $latestDevice ? [
                    'device_code' => $latestDevice->device_code,
                    'status' => $latestDevice->status,
                    'is_online' => $latestDevice->status === 'ONLINE',
                    'last_seen' => $latestDevice->last_seen,
                ] : null,
                'sensors' => $sensorsData,
                'fan' => $fanData,
                'unread_alerts' => $unreadAlerts,
            ],
        ]);
    }
}
