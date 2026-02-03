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
    
    private $lastAlertTime = null;
    private $alertCooldown = 300; // 5 menit cooldown untuk alert

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
                
                // Cek ambang batas dan kirim alert
                $this->checkThresholdAndAlert($data['temperature'], $data['humidity']);
            }
        }, 0);

        $mqtt->loop(true);
    }
    
    private function checkThresholdAndAlert($temperature, $humidity)
    {
        $tempThreshold = 35; // Ambang batas suhu
        $humThreshold = 80;  // Ambang batas kelembaban
        
        $isAnomalous = false;
        $alertMessage = "🚨 <b>PERINGATAN ANOMALI GUDANG!</b>\n\n";
        
        if ($temperature >= $tempThreshold) {
            $isAnomalous = true;
            $alertMessage .= "🌡 Suhu: <b>{$temperature} °C</b> (⚠️ Melebihi batas {$tempThreshold}°C)\n";
        } else {
            $alertMessage .= "🌡 Suhu: {$temperature} °C (✅ Normal)\n";
        }
        
        if ($humidity >= $humThreshold) {
            $isAnomalous = true;
            $alertMessage .= "💧 Kelembaban: <b>{$humidity} %</b> (⚠️ Melebihi batas {$humThreshold}%)\n";
        } else {
            $alertMessage .= "💧 Kelembaban: {$humidity} % (✅ Normal)\n";
        }
        
        // Kirim alert jika ada anomali dan sudah lewat cooldown
        if ($isAnomalous) {
            $now = time();
            if ($this->lastAlertTime === null || ($now - $this->lastAlertTime) >= $this->alertCooldown) {
                $alertMessage .= "\n⏰ Waktu: " . now()->format('d/m/Y H:i:s');
                $alertMessage .= "\n\n🔴 <b>Segera lakukan tindakan!</b>";
                
                $telegram = new TelegramService();
                $telegram->send($alertMessage);
                
                $this->lastAlertTime = $now;
                echo "🚨 Alert sent to Telegram!\n";
            } else {
                echo "⏳ Alert cooldown active, skipping...\n";
            }
        }
    }
}

