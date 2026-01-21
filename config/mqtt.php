<?php

return [
    'host'        => env('MQTT_HOST', '41bde8e7e4764a33ad0cf261439dafc9.s1.eu.hivemq.cloud'),
    'port'        => env('MQTT_PORT', 8883),
    'username'    => env('MQTT_USERNAME', 'esp32user'),
    'password'    => env('MQTT_PASSWORD', 'Esp32pass123'),
    'client_id'   => env('MQTT_CLIENT_ID', 'laravel-subscriber-01'),
    'cafile'      => env('MQTT_SSL_CAFILE', null),
];
