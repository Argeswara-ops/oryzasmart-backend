<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actuator;
use App\Models\Alert;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\User;
use App\Models\VisionScan;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $devices = Device::with(['fan', 'latestSensorReading'])->get();
        $totalDevices = $devices->count();
        $onlineDevices = $devices->where('status', 'ONLINE')->count();

        $primaryDevice = $devices->firstWhere('device_code', 'ESP32-01') ?? $devices->first();

        $latestSensor = SensorReading::latest('recorded_at')->first()
            ?? ($primaryDevice?->latestSensorReading);

        $fan = Actuator::where('type', 'fan')->first();

        $unreadAlertsCount = Alert::where('status', 'UNREAD')->count();
        $totalUsersCount = User::count();
        $totalScansCount = VisionScan::count();

        $recentAlerts = Alert::with('device')->latest()->take(5)->get();
        $recentScans = VisionScan::latest('scanned_at')->take(4)->get();

        // Chart data for last 24 hours (up to 30 points)
        $chartReadings = SensorReading::latest('recorded_at')
            ->take(30)
            ->get()
            ->reverse()
            ->values();

        $chartLabels = $chartReadings->map(fn ($r) => $r->recorded_at ? $r->recorded_at->format('H:i') : '');
        $chartTemp = $chartReadings->map(fn ($r) => (float) $r->temperature);
        $chartHumidity = $chartReadings->map(fn ($r) => (float) $r->humidity);
        $chartWater = $chartReadings->map(fn ($r) => (float) ($r->level_percent ?? 0));

        return view('admin.dashboard', compact(
            'devices',
            'totalDevices',
            'onlineDevices',
            'primaryDevice',
            'latestSensor',
            'fan',
            'unreadAlertsCount',
            'totalUsersCount',
            'totalScansCount',
            'recentAlerts',
            'recentScans',
            'chartLabels',
            'chartTemp',
            'chartHumidity',
            'chartWater'
        ));
    }
}

