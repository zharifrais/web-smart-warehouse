<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SensorLog;
use App\Services\TelegramService;
use App\Services\AiAnalysisService;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, TelegramService $telegram)
    {
        $message = $request->input('message.text');
        $chatId  = $request->input('message.chat.id');

        if (!$message) {
            return response()->json(['status' => 'ignored']);
        }

        if ($message === '/status') {
            $latest = SensorLog::latest()->first();

            if (!$latest) {
                $telegram->send("❌ Data sensor belum tersedia.");
                return response()->json(['status' => 'ok']);
            }

            $status = $this->determineStatus(
                $latest->temperature,
                $latest->humidity
            );

            $telegram->send(
                "📊 <b>STATUS GUDANG</b>\n\n" .
                "🌡 Suhu: {$latest->temperature} °C\n" .
                "💧 Kelembaban: {$latest->humidity} %\n\n" .
                "🟡 Status: <b>{$status}</b>"
            );
        }
        
        if ($message === '/analisis') {
            $telegram->send("⏳ Menganalisis data gudang...");
            
            $analysis = AiAnalysisService::analyzeTelegram();
            $telegram->send($analysis);
        }
    if ($message === '/laporan') {

    $logs = SensorLog::latest()->limit(100)->get();

    if ($logs->isEmpty()) {
        $telegram->send("❌ Data laporan belum tersedia.");
        return response()->json(['status' => 'ok']);
    }

            $avgTemp = round($logs->avg('temperature'), 1);
            $avgHum  = round($logs->avg('humidity'), 1);

            $maxTemp = round($logs->max('temperature'), 1);
            $minTemp = round($logs->min('temperature'), 1);

            $status = $this->determineStatus($maxTemp, $avgHum);

            $text =
                "📄 <b>LAPORAN MONITORING GUDANG</b>\n\n" .
                "🌡 Suhu rata-rata: {$avgTemp} °C\n" .
                "💧 Kelembaban rata-rata: {$avgHum} %\n\n" .
                "🔺 Suhu tertinggi: {$maxTemp} °C\n" .
                "🔻 Suhu terendah: {$minTemp} °C\n\n" .
                "⚠️ Status Gudang: <b>{$status}</b>\n\n" .
                "Ketik /laporan_pdf untuk mengunduh laporan lengkap (PDF).";

            $telegram->send($text);
        }
        if ($message === '/laporan_pdf') {

                $telegram->send("⏳ Mengirim laporan PDF...");

            $pdfPath = storage_path('app/public/Laporan_Monitoring_Gudang.pdf');

            if (!file_exists($pdfPath)) {
                $telegram->send("❌ File laporan PDF tidak ditemukan.");
                return response()->json(['status' => 'ok']);
            }

            $success = $telegram->sendDocument(
                $pdfPath,
                '📄 Laporan Monitoring Gudang'
            );

            if ($success) {
                $telegram->send("✅ Laporan PDF berhasil dikirim.");
            } else {
                $telegram->send("❌ Gagal mengirim laporan PDF.");
            }

            return response()->json(['status' => 'ok']);
        }


        return response()->json(['status' => 'ok']);
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
}
