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
            $host = config('mqtt.host', env('MQTT_HOST', '127.0.0.1'));
            $port = (int) config('mqtt.port', env('MQTT_PORT', 1883));
            $clientId = config('mqtt.client_id', env('MQTT_CLIENT_ID', 'laravel_padibackend_'.uniqid()));

            $this->client = new MqttClient($host, $port, $clientId);

            $settings = (new ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setLastWillTopic('rice-nursery/backend/status')
                ->setLastWillMessage('OFFLINE', 0);

            $username = config('mqtt.username', env('MQTT_USERNAME'));
            $password = config('mqtt.password', env('MQTT_PASSWORD'));

            if ($username) {
                $settings->setUsername($username);
            }
            if ($password) {
                $settings->setPassword($password);
            }

            $this->client->connect($settings, true);
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

    public function publishFanCommand(string $deviceCode, string $command): void
    {
        $topic = "rice-nursery/{$deviceCode}/actuator/fan/command";
        $payload = json_encode([
            'device_id' => $deviceCode,
            'command' => strtoupper($command),
            'timestamp' => now()->toIso8601String(),
        ]);
        $this->publish($topic, $payload);
    }

    public function publishFanMode(string $deviceCode, string $mode): void
    {
        $topic = "rice-nursery/{$deviceCode}/actuator/fan/mode";
        $payload = json_encode([
            'device_id' => $deviceCode,
            'mode' => strtoupper($mode),
            'timestamp' => now()->toIso8601String(),
        ]);
        $this->publish($topic, $payload);
    }
}
