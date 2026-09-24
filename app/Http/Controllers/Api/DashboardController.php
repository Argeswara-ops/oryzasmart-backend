<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actuator;
use App\Models\Alert;
use App\Models\Detection;
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

        $latestDevice = Device::latest('last_seen')->first();
        $deviceStatus = $latestDevice ? $latestDevice->status : 'OFFLINE';

        $sensorsData = $this->sensorService->getLatest();

        $latestDetection = Detection::latest('detected_at')->first();
        $detectionData = $latestDetection ? [
            'type' => $latestDetection->detection_type,
            'class' => $latestDetection->class_name,
            'confidence' => $latestDetection->confidence,
            'detected_at' => $latestDetection->detected_at,
        ] : null;

        $fan = Actuator::where('type', 'fan')->first();
        $fanData = [
            'status' => $fan ? $fan->status : 'OFF',
            'mode' => $fan ? $fan->mode : 'AUTO',
        ];

        $unreadAlerts = Alert::where('status', 'UNREAD')->count();

        return response()->json([
            'success' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => [
                'system_status' => $systemStatus,
                'device_status' => $deviceStatus,
                'sensors' => $sensorsData,
                'latest_detection' => $detectionData,
                'fan' => $fanData,
                'unread_alerts' => $unreadAlerts,
            ],
        ]);
    }
}
