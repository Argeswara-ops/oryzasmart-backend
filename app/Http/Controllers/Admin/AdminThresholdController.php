<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SensorThreshold;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminThresholdController extends Controller
{
    public function index(): View
    {
        // Make sure all defaults exist
        foreach (SensorThreshold::DEFAULTS as $type => $defaults) {
            SensorThreshold::firstOrCreate(['sensor_type' => $type], $defaults);
        }

        $thresholds = SensorThreshold::all()->keyBy('sensor_type');

        return view('admin.thresholds.index', compact('thresholds'));
    }

    public function update(Request $request, string $sensorType): RedirectResponse
    {
        if (!in_array($sensorType, SensorThreshold::SUPPORTED_TYPES)) {
            return back()->with('error', "Tipe sensor '{$sensorType}' tidak didukung.");
        }

        $validated = $request->validate([
            'min_value' => ['required', 'numeric'],
            'max_value' => ['required', 'numeric', 'gte:min_value'],
        ]);

        $threshold = SensorThreshold::updateOrCreate(
            ['sensor_type' => $sensorType],
            [
                'min_value' => $validated['min_value'],
                'max_value' => $validated['max_value'],
            ]
        );

        return back()->with('success', "Ambang batas untuk sensor '{$threshold->sensor_type}' berhasil disimpan.");
    }

    public function reset(string $sensorType): RedirectResponse
    {
        if (isset(SensorThreshold::DEFAULTS[$sensorType])) {
            SensorThreshold::updateOrCreate(
                ['sensor_type' => $sensorType],
                SensorThreshold::DEFAULTS[$sensorType]
            );

            return back()->with('success', "Ambang batas untuk sensor '{$sensorType}' direset ke nilai default.");
        }

        return back()->with('error', 'Tipe sensor tidak valid.');
    }
}

