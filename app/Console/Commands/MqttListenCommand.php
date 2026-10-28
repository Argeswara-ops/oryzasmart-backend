<?php

namespace App\Console\Commands;

use App\Models\Actuator;
use App\Models\Device;
use App\Models\SensorReading;
use App\Services\MqttService;
use App\Services\SensorService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use JsonException;

class MqttListenCommand extends Command
{
    protected $signature = 'mqtt:subscribe';

    protected $aliases = ['mqtt:listen'];

    protected $description = 'Listen to incoming MQTT topics for smart rice nursery sensor & device data';

    public function handle(MqttService $mqttService, SensorService $sensorService): int
    {
        $sensorTopic = (string) config('mqtt.sensor_topic', 'oryza-smart/ESP32-01/sensors');
        $statusTopic = (string) config('mqtt.status_topic', 'oryza-smart/ESP32-01/status');
        $fanStatusTopic = (string) config('mqtt.fan_status_topic', 'oryza-smart/ESP32-01/actuator/fan/status');

        $this->info("Starting MQTT listener on {$sensorTopic}...");

        try {
            $client = $mqttService->getClient();

            $client->subscribe($sensorTopic, function (string $topic, string $message) use ($sensorService) {
                $this->processSensorMessage($topic, $message, $sensorService);
            });

            $client->subscribe($statusTopic, function (string $topic, string $message) {
                $this->processStatusMessage($topic, $message);
            });

            $client->subscribe($fanStatusTopic, function (string $topic, string $message) {
                $this->processFanStatusMessage($topic, $message);
            });

            Log::info('MQTT subscriptions registered', [
                'sensor_topic' => $sensorTopic,
                'status_topic' => $statusTopic,
                'fan_status_topic' => $fanStatusTopic,
            ]);

            $client->loop(true);
        } catch (\Throwable $e) {
            $this->error('MQTT Listener exception: '.$e->getMessage());
            Log::error('MQTT Listener crashed', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    public function processSensorMessage(string $topic, string $message, SensorService $sensorService): ?SensorReading
    {
        try {
            $payload = json_decode($message, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::warning('Invalid MQTT JSON payload', [
                'topic' => $topic,
                'message' => $message,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! is_array($payload)) {
            Log::warning('Invalid MQTT payload type', [
                'topic' => $topic,
                'payload' => $payload,
            ]);

            return null;
        }

        $payload['device_id'] = $this->resolveDeviceId($topic, $payload);

        Log::info('MQTT sensor payload received', [
            'topic' => $topic,
            'device_id' => $payload['device_id'],
        ]);

        try {
            return $sensorService->processPayload($payload);
        } catch (\Throwable $exception) {
            Log::error('Failed to process MQTT sensor payload', [
                'topic' => $topic,
                'payload' => $payload,
                'exception' => $exception,
            ]);

            return null;
        }
    }

    public function processStatusMessage(string $topic, string $message): ?Device
    {
        $payload = $this->decodeStatusMessage($topic, $message);
        if ($payload === null) {
            return null;
        }

        $deviceCode = $this->resolveDeviceId($topic, $payload);
        $status = $this->normalizeDeviceStatus(
            (string) (Arr::get($payload, 'status') ?? Arr::get($payload, 'state') ?? 'ONLINE')
        );

        if ($status === null) {
            Log::warning('Invalid MQTT device status ignored', [
                'topic' => $topic,
                'payload' => $payload,
            ]);

            return null;
        }

        Log::info('MQTT device status received', [
            'topic' => $topic,
            'device_id' => $deviceCode,
            'status' => $status,
        ]);

        return Device::updateOrCreate(
            ['device_code' => $deviceCode],
            [
                'name' => "Device {$deviceCode}",
                'type' => 'esp32',
                'status' => $status,
                'last_seen' => now(),
            ]
        );
    }

    public function processFanStatusMessage(string $topic, string $message): ?Actuator
    {
        $payload = $this->decodeJsonMessage($topic, $message);
        if ($payload === null) {
            return null;
        }

        $deviceCode = $this->resolveDeviceId($topic, $payload);
        $status = mb_strtoupper((string) (Arr::get($payload, 'state') ?? Arr::get($payload, 'status') ?? 'OFF'));
        $mode = mb_strtoupper((string) (Arr::get($payload, 'mode') ?? 'AUTO'));

        if (! in_array($status, ['ON', 'OFF'], true) || ! in_array($mode, ['AUTO', 'MANUAL'], true)) {
            Log::warning('Invalid MQTT fan status ignored', [
                'topic' => $topic,
                'payload' => $payload,
            ]);

            return null;
        }

        $device = Device::updateOrCreate(
            ['device_code' => $deviceCode],
            [
                'name' => "Device {$deviceCode}",
                'type' => 'esp32',
                'status' => 'ONLINE',
                'last_seen' => now(),
            ]
        );

        Log::info('MQTT fan status received', [
            'topic' => $topic,
            'device_id' => $deviceCode,
            'status' => $status,
            'mode' => $mode,
        ]);

        return Actuator::updateOrCreate(
            ['device_id' => $device->id, 'type' => 'fan'],
            [
                'name' => 'Fan',
                'status' => $status,
                'mode' => $mode,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveDeviceId(string $topic, array $payload): string
    {
        $deviceId = Arr::get($payload, 'device_id')
            ?? Arr::get($payload, 'deviceId')
            ?? Arr::get($payload, 'device');

        if (is_string($deviceId) && $deviceId !== '') {
            return $deviceId;
        }

        if (preg_match('#rice-nursery/([^/]+)/sensors#', $topic, $matches)) {
            return $matches[1];
        }

        if (preg_match('#oryza-smart/([^/]+)/(?:sensors|status|actuator/fan/status)#', $topic, $matches)) {
            return $matches[1];
        }

        return (string) config('mqtt.default_device_id', 'ESP32-01');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonMessage(string $topic, string $message): ?array
    {
        try {
            $payload = json_decode($message, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            Log::warning('Invalid MQTT JSON payload', [
                'topic' => $topic,
                'message' => $message,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! is_array($payload)) {
            Log::warning('Invalid MQTT payload type', [
                'topic' => $topic,
                'payload' => $payload,
            ]);

            return null;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeStatusMessage(string $topic, string $message): ?array
    {
        $plainStatus = trim($message);
        if ($this->normalizeDeviceStatus($plainStatus) !== null) {
            return ['status' => $plainStatus];
        }

        return $this->decodeJsonMessage($topic, $message);
    }

    private function normalizeDeviceStatus(string $status): ?string
    {
        return match (mb_strtoupper($status)) {
            'ONLINE', 'ON', '1', 'TRUE' => 'ONLINE',
            'OFFLINE', 'OFF', '0', 'FALSE' => 'OFFLINE',
            'ERROR' => 'ERROR',
            default => null,
        };
    }
}
