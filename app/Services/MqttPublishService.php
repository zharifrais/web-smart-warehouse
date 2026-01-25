<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttPublishService
{
    public function publishRelayCommand($state)
    {
        $host = config('mqtt.host');
        $port = config('mqtt.port');
        $username = config('mqtt.username');
        $password = config('mqtt.password');
        $clientId = 'laravel-publisher-' . uniqid();

        $connectionSettings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password)
            ->setUseTls(true)
            ->setTlsSelfSignedAllowed(true);

        $mqtt = new MqttClient($host, $port, $clientId);
        
        try {
            $mqtt->connect($connectionSettings, true);
            $mqtt->publish('warehouse/relay/control', $state, 0);
            $mqtt->disconnect();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('MQTT Publish Error: ' . $e->getMessage());
            return false;
        }
    }
}
