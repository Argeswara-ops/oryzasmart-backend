<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class MqttService
{
    protected ?MqttClient $client = null;

    public function getClient(): MqttClient
    {
        if ($this->client === null) {
            $host = (string) config('mqtt.host', '127.0.0.1');
            $port = (int) config('mqtt.port', 1883);
            $clientIdPrefix = (string) config('mqtt.client_id', 'laravel_oryza_smart');
            $clientId = $clientIdPrefix.'_'.getmypid().'_'.bin2hex(random_bytes(3));

            Log::info('Connecting to MQTT broker', [
                'host' => $host,
                'port' => $port,
                'client_id' => $clientId,
            ]);

            $this->client = new MqttClient($host, $port, $clientId);

            $settings = (new ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setReconnectAutomatically(true)
                ->setLastWillTopic((string) config('mqtt.last_will_topic', 'oryza-smart/backend/status'))
                ->setLastWillMessage('OFFLINE', 0);

            $username = config('mqtt.username');
            $password = config('mqtt.password');

            if ($username) {
                $settings->setUsername($username);
            }
            if ($password) {
                $settings->setPassword($password);
            }

            $this->client->connect($settings, false);

            Log::info('Connected to MQTT broker', [
                'host' => $host,
                'port' => $port,
                'client_id' => $clientId,
            ]);
        }

        return $this->client;
    }

    public function publish(string $topic, string $message, int $qos = 0, bool $retain = false): void
    {
        try {
            $client = $this->getClient();
            $client->publish($topic, $message, $qos, $retain);
            Log::info("MQTT Published to {$topic}: {$message}");
        } catch (\Throwable $e) {
            Log::error("MQTT Publish Error on topic {$topic}: ".$e->getMessage());
        }
    }

    public function publishFanCommand(string $deviceCode, string $command, ?string $mode = null): void
    {
        $commandUpper = mb_strtoupper($command);
        if (! in_array($commandUpper, ['ON', 'OFF', 'AUTO'], true)) {
            Log::warning('Invalid MQTT fan command skipped', [
                'device_id' => $deviceCode,
                'command' => $command,
            ]);

            return;
        }

        $topic = $this->topicForDevice((string) config('mqtt.fan_command_topic', 'oryza-smart/ESP32-01/actuator/fan/command'), $deviceCode);
        $payload = $commandUpper;

        if (config('mqtt.fan_command_payload') === 'wokwi_json') {
            $modeUpper = $mode !== null ? mb_strtoupper($mode) : null;
            $payloadData = $commandUpper === 'AUTO'
                ? ['mode' => 'AUTO']
                : ['state' => $commandUpper, 'mode' => $modeUpper ?: 'MANUAL'];

            $payload = json_encode($payloadData, JSON_THROW_ON_ERROR);
        } elseif (config('mqtt.fan_command_payload') === 'json') {
            $payload = json_encode([
                'device_id' => $deviceCode,
                'command' => $commandUpper,
                'mode' => $mode ? mb_strtoupper($mode) : null,
                'timestamp' => now()->toIso8601String(),
            ], JSON_THROW_ON_ERROR);
        }

        $this->publish($topic, $payload);
    }

    public function publishFanMode(string $deviceCode, string $mode): void
    {
        if (mb_strtoupper($mode) === 'AUTO') {
            $this->publishFanCommand($deviceCode, 'AUTO', 'AUTO');
        }
    }

    private function topicForDevice(string $topic, string $deviceCode): string
    {
        $defaultDeviceId = (string) config('mqtt.default_device_id', 'ESP32-01');

        return str_replace("/{$defaultDeviceId}/", "/{$deviceCode}/", $topic);
    }
}
