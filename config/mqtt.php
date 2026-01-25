<?php

return [
    'host'        => env('MQTT_HOST', '7b3d2ad198b04c44bd1993be8c1c91c6.s1.eu.hivemq.cloud'),
    'port'        => env('MQTT_PORT', 8883),
    'username'    => env('MQTT_USERNAME', 'esp32user'),
    'password'    => env('MQTT_PASSWORD', 'Esp32username'),
    'client_id'   => env('MQTT_CLIENT_ID', 'laravel-subscriber-01'),
    'cafile'      => env('MQTT_SSL_CAFILE', null),
];
