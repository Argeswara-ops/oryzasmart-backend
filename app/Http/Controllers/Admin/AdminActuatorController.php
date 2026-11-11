<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actuator;
use App\Models\ActuatorLog;
use App\Models\Device;
use App\Services\ActuatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminActuatorController extends Controller
{
    public function __construct(
        public ActuatorService $actuatorService
    ) {}

    public function index(Request $request): View
    {
        $devices = Device::all();
        $actuators = Actuator::with('device')->get();

        $query = ActuatorLog::with(['actuator.device', 'user']);

        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        return view('admin.actuators.index', compact('devices', 'actuators', 'logs'));
    }

    public function control(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'actuator_id' => ['required', 'exists:actuators,id'],
            'command' => ['required', 'string', 'in:ON,OFF,AUTO'],
            'mode' => ['nullable', 'string', 'in:AUTO,MANUAL'],
        ]);

        $actuator = Actuator::with('device')->findOrFail($validated['actuator_id']);

        $command = strtoupper($validated['command']);
        $mode = strtoupper($validated['mode'] ?? ($command === 'AUTO' ? 'AUTO' : 'MANUAL'));
        $user = Auth::user();

        try {
            $this->actuatorService->executeControl($actuator, $command, $mode, 'USER', $user);

            return back()->with('success', "Perintah {$command} (Mode {$mode}) untuk {$actuator->name} berhasil dikirim.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengeksekusi kontrol aktuator: '.$e->getMessage());
        }
    }
}

