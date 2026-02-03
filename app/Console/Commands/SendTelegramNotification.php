<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sensor;
use App\Services\TelegramService;

class SendTelegramNotification extends Command
{
    protected $signature = 'telegram:notify';
    protected $description = 'Kirim notifikasi Telegram setiap 15 menit jika alat menyala';

    public function handle(TelegramService $telegram)
    {
        // Cek apakah ada data hari ini
        $todayData = Sensor::whereDate('created_at', today())->exists();
        
        if (!$todayData) {
            $this->info('Tidak ada data hari ini, notifikasi tidak dikirim');
            return;
        }

        // Ambil data terbaru
        $latest = Sensor::latest()->first();
        
        if (!$latest) {
            $this->info('Tidak ada data sensor');
            return;
        }

        // Cek apakah alat masih menyala (data terbaru dalam 15 menit terakhir)
        $isDeviceActive = $latest->created_at->diffInMinutes(now()) <= 15;
        
        if (!$isDeviceActive) {
            $this->info('Alat sudah mati (tidak ada data dalam 15 menit terakhir), notifikasi dihentikan');
            return;
        }

        // Tentukan status
        $status = $this->determineStatus($latest->temperature, $latest->humidity);
        $emoji = $this->getStatusEmoji($status);

        // Kirim notifikasi
        $message = 
            "🔔 <b>NOTIFIKASI OTOMATIS</b>\n\n" .
            "⏰ Waktu: " . now()->format('d/m/Y H:i:s') . "\n\n" .
            "🌡 Suhu: <b>{$latest->temperature} °C</b>\n" .
            "💧 Kelembaban: <b>{$latest->humidity} %</b>\n\n" .
            "{$emoji} Status: <b>{$status}</b>";

        $telegram->send($message);
        $this->info('Notifikasi berhasil dikirim ke Telegram');
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
