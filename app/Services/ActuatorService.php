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

        $actuator->update([
            'status' => $commandUpper,
            'mode' => $modeUpper,
        ]);

        ActuatorLog::create([
            'actuator_id' => $actuator->id,
            'user_id' => $user?->id,
            'command' => $commandUpper,
            'mode' => $modeUpper,
            'source' => $source,
            'previous_status' => $previousStatus,
            'new_status' => $commandUpper,
        ]);

        $deviceCode = $actuator->device->device_code ?? (string) config('mqtt.default_device_id', 'ESP32-01');
        $mqttCommand = $modeUpper === 'AUTO' ? 'AUTO' : $commandUpper;

        $this->mqttService->publishFanCommand($deviceCode, $mqttCommand);

        return true;
    }
}
