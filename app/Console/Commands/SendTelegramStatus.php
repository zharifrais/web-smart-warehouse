<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorLog;
use App\Services\TelegramService;

class SendTelegramStatus extends Command
{
    protected $signature = 'telegram:send-status';
    protected $description = 'Kirim status gudang otomatis ke Telegram';

    public function handle(TelegramService $telegram)
    {
        $latest = SensorLog::latest()->first();

        if (!$latest) {
            $this->error('Data sensor tidak tersedia');
            return;
        }

        $status = $this->determineStatus($latest->temperature, $latest->humidity);
        $emoji = $this->getStatusEmoji($status);

        $message = 
            "🔔 <b>UPDATE OTOMATIS GUDANG</b>\n\n" .
            "⏰ Waktu: " . now()->format('d/m/Y H:i:s') . "\n\n" .
            "🌡 Suhu: <b>{$latest->temperature} °C</b>\n" .
            "💧 Kelembaban: <b>{$latest->humidity} %</b>\n\n" .
            "{$emoji} Status: <b>{$status}</b>";

        $telegram->send($message);
        $this->info('Status berhasil dikirim ke Telegram');
    }

    private function determineStatus($temp, $hum)
    {
        if ($temp >= 35 || $hum >= 80) {
            return 'ANOMALY';
        }
        if ($temp > 30 || $hum > 70) {
            return 'WARNING';
        }
        return 'NORMAL';
    }

    private function getStatusEmoji($status)
    {
        return match($status) {
            'ANOMALY' => '🔴',
            'WARNING' => '🟡',
            'NORMAL' => '🟢',
            default => '⚪'
        };
    }
}
