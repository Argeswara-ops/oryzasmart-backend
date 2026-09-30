<?php

return [
    'host' => env('MQTT_HOST', 'broker.hivemq.com'),
    'port' => env('MQTT_PORT', 1883),
    'client_id' => env('MQTT_CLIENT_ID', 'laravel_oryza_smart'),
    'username' => env('MQTT_USERNAME', null),
    'password' => env('MQTT_PASSWORD', null),
    'default_device_id' => env('MQTT_DEFAULT_DEVICE_ID', 'ESP32-01'),
    'sensor_topic' => env('MQTT_SENSOR_TOPIC', 'oryza-smart/ESP32-01/sensors'),
    'status_topic' => env('MQTT_STATUS_TOPIC', 'oryza-smart/ESP32-01/status'),
    'fan_status_topic' => env('MQTT_FAN_STATUS_TOPIC', 'oryza-smart/ESP32-01/actuator/fan/status'),
    'fan_command_topic' => env('MQTT_FAN_COMMAND_TOPIC', 'oryza-smart/ESP32-01/actuator/fan/command'),
    'last_will_topic' => env('MQTT_LAST_WILL_TOPIC', 'oryza-smart/backend/status'),
];
