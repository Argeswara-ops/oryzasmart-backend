<?php

namespace App\Services;

use App\Models\Actuator;
use App\Models\ActuatorLog;
use App\Models\User;

class ActuatorService
{
    public function __construct(
        public MqttService $mqttService
    ) {}

    public function executeControl(
        Actuator $actuator,
        string $command,
        string $mode = 'AUTO',
        string $source = 'SYSTEM',
        ?User $user = null
    ): bool {
        $previousStatus = $actuator->status;
        $commandUpper = strtoupper($command);
        $modeUpper = strtoupper($mode);
        $newStatus = $commandUpper === 'AUTO' ? $previousStatus : $commandUpper;
        $newMode = $commandUpper === 'AUTO' ? 'AUTO' : $modeUpper;

        $actuator->update([
            'status' => $newStatus,
            'mode' => $newMode,
        ]);

        ActuatorLog::create([
            'actuator_id' => $actuator->id,
            'user_id' => $user?->id,
            'command' => $commandUpper,
            'mode' => $newMode,
            'source' => $source,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
        ]);

        $deviceCode = $actuator->device->device_code ?? (string) config('mqtt.default_device_id', 'ESP32-01');
        $this->mqttService->publishFanCommand($deviceCode, $commandUpper, $newMode);

        return true;
    }
}
