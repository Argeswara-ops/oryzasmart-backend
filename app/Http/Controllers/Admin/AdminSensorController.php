<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSensorController extends Controller
{
    public function index(Request $request): View
    {
        $devices = Device::all();

        $query = SensorReading::with('device');

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->input('device_id'));
        }

        $range = $request->input('range', 'all');
        if ($range === 'today') {
            $query->whereDate('recorded_at', now()->today());
        } elseif ($range === '7days') {
            $query->where('recorded_at', '>=', now()->subDays(7));
        } elseif ($range === '30days') {
            $query->where('recorded_at', '>=', now()->subDays(30));
        }

        $readings = (clone $query)->latest('recorded_at')->paginate(20)->withQueryString();

        // Statistics
        $stats = [
            'total_records' => SensorReading::count(),
            'avg_temp' => round((float) SensorReading::avg('temperature'), 1),
            'avg_humidity' => round((float) SensorReading::avg('humidity'), 1),
            'avg_water' => round((float) SensorReading::avg('level_percent'), 1),
        ];

        // Chart data (50 sample points)
        $chartData = (clone $query)->latest('recorded_at')->take(50)->get()->reverse()->values();

        $chartLabels = $chartData->map(fn ($r) => $r->recorded_at ? $r->recorded_at->format('d/m H:i') : '');
        $chartTemp = $chartData->map(fn ($r) => (float) $r->temperature);
        $chartHumidity = $chartData->map(fn ($r) => (float) $r->humidity);
        $chartWater = $chartData->map(fn ($r) => (float) ($r->level_percent ?? 0));
        $chartN = $chartData->map(fn ($r) => (float) ($r->nitrogen ?? 0));
        $chartP = $chartData->map(fn ($r) => (float) ($r->phosphorus ?? 0));
        $chartK = $chartData->map(fn ($r) => (float) ($r->potassium ?? 0));

        return view('admin.sensors.index', compact(
            'readings',
            'devices',
            'stats',
            'chartLabels',
            'chartTemp',
            'chartHumidity',
            'chartWater',
            'chartN',
            'chartP',
            'chartK'
        ));
    }
}

