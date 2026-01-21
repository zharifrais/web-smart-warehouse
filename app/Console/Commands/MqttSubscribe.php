<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\SensorLog;
use App\Services\TelegramService;

class MqttSubscribe extends Command
{
    protected $signature = 'mqtt:subscribe';
    protected $description = 'Subscribe to MQTT topic warehouse/sensor';

    public function handle()
    {
        $server   = config('mqtt.host');
        $port     = config('mqtt.port');
        $clientId = config('mqtt.client_id');

        $username = config('mqtt.username');
        $password = config('mqtt.password');

        $settings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password)
            ->setUseTls(true)
            ->setTlsSelfSignedAllowed(true)
            ->setKeepAliveInterval(60);

        $mqtt = new MqttClient($server, $port, $clientId);

        $mqtt->connect($settings, true);

        $this->info("✔ Connected to MQTT broker: {$server}");

        // SUBSCRIBE TOPIK
        $mqtt->subscribe('warehouse/sensor', function ($topic, $message) {
            echo "📥 Received from {$topic}: {$message}\n";

            // Decode data JSON dari ESP32
            $data = json_decode($message, true);

            if ($data) {
                SensorLog::create([
                    'temperature' => $data['temperature'],
                    'humidity'    => $data['humidity'],
                ]);
                echo "✔ Data saved to database.\n";
            }
        }, 0);

        $mqtt->loop(true);

        

        
    }
    
}

