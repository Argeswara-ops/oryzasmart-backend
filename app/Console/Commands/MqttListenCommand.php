<?php

namespace App\Console\Commands;

use App\Models\Actuator;
use App\Models\Device;
use App\Services\MqttService;
use App\Services\SensorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MqttListenCommand extends Command
{
    protected $signature = 'mqtt:listen';

    protected $description = 'Listen to incoming MQTT topics for smart rice nursery sensor & device data';

    public function handle(MqttService $mqttService, SensorService $sensorService): int
    {
        $this->info('Starting MQTT Listener...');

        try {
            $client = $mqttService->getClient();

            // 1. Sensor Readings
            $client->subscribe('rice-nursery/+/sensors', function (string $topic, string $message) use ($sensorService) {
                $this->info("Received sensor message on {$topic}: {$message}");
                Log::info("MQTT Sensor payload received on {$topic}: {$message}");

                $payload = json_decode($message, true);
                if (is_array($payload)) {
                    // Extract device_id from topic if missing in payload
                    if (empty($payload['device_id']) && preg_match('#rice-nursery/([^/]+)/sensors#', $topic, $matches)) {
                        $payload['device_id'] = $matches[1];
                    }
                    $sensorService->processPayload($payload);
                }
            });

            // 2. Device Status (Heartbeat)
            $client->subscribe('rice-nursery/+/status', function (string $topic, string $message) {
                $this->info("Received status message on {$topic}: {$message}");
                if (preg_match('#rice-nursery/([^/]+)/status#', $topic, $matches)) {
                    $deviceCode = $matches[1];
                    $payload = json_decode($message, true);
                    $status = is_array($payload) && isset($payload['status']) ? strtoupper($payload['status']) : 'ONLINE';

                    Device::updateOrCreate(
                        ['device_code' => $deviceCode],
                        [
                            'name' => "Device {$deviceCode}",
                            'status' => $status,
                            'last_seen' => now(),
                        ]
                    );
                }
            });

            // 3. Actuator Status Feedback
            $client->subscribe('rice-nursery/+/actuator/+/status', function (string $topic, string $message) {
                $this->info("Received actuator status on {$topic}: {$message}");
                if (preg_match('#rice-nursery/([^/]+)/actuator/([^/]+)/status#', $topic, $matches)) {
                    $deviceCode = $matches[1];
                    $actuatorType = $matches[2];
                    $payload = json_decode($message, true);
                    $status = is_array($payload) && isset($payload['status']) ? strtoupper($payload['status']) : 'OFF';

                    $device = Device::where('device_code', $deviceCode)->first();
                    if ($device) {
                        Actuator::updateOrCreate(
                            ['device_id' => $device->id, 'type' => $actuatorType],
                            [
                                'name' => ucfirst($actuatorType),
                                'status' => $status,
                            ]
                        );
                    }
                }
            });

            $client->loop(true);
        } catch (\Throwable $e) {
            $this->error('MQTT Listener exception: '.$e->getMessage());
            Log::error('MQTT Listener crashed: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
