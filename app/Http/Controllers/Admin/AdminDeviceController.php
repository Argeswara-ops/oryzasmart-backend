<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDeviceController extends Controller
{
    public function index(): View
    {
        $devices = Device::with(['actuators', 'latestSensorReading'])->latest()->paginate(10);

        return view('admin.devices.index', compact('devices'));
    }

    public function create(): View
    {
        return view('admin.devices.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'device_code' => ['required', 'string', 'max:50', 'unique:devices,device_code'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:esp32,camera,gateway'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:ONLINE,OFFLINE,ERROR'],
        ]);

        $device = Device::create($validated);

        return redirect()->route('admin.devices.index')->with('success', "Perangkat {$device->device_code} berhasil ditambahkan.");
    }

    public function edit(Device $device): View
    {
        return view('admin.devices.edit', compact('device'));
    }

    public function update(Request $request, Device $device): RedirectResponse
    {
        $validated = $request->validate([
            'device_code' => ['required', 'string', 'max:50', Rule::unique('devices')->ignore($device->id)],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:esp32,camera,gateway'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:ONLINE,OFFLINE,ERROR'],
        ]);

        $device->update($validated);

        return redirect()->route('admin.devices.index')->with('success', "Perangkat {$device->device_code} berhasil diperbarui.");
    }

    public function destroy(Device $device): RedirectResponse
    {
        $code = $device->device_code;
        $device->delete();

        return redirect()->route('admin.devices.index')->with('success', "Perangkat {$code} berhasil dihapus.");
    }
}

