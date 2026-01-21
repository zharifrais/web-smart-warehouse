<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Sensor;
use Carbon\Carbon;

class AiAnalysisService
{
    public static function analyze(array $data)
    {
        $sensors = Sensor::whereDate('created_at', today())->get();
        
        if ($sensors->isEmpty()) {
            return 'Belum tersedia data sensor untuk dianalisis.';
        }

        $maxTemp = $sensors->sortByDesc('temperature')->first();
        $minTemp = $sensors->sortBy('temperature')->first();
        $maxHum = $sensors->sortByDesc('humidity')->first();
        $minHum = $sensors->sortBy('humidity')->first();

        $formatTime = fn ($t) => Carbon::parse($t)->format('H:i');
        $now = now()->format('Y-m-d H:i:s');
        
        $prompt = "
Buatkan analisis kondisi gudang berdasarkan data suhu dan kelembaban berikut.

Gunakan gaya bahasa:
- Natural dan seperti penjelasan manusia
- Ringkas, jelas, dan profesional
- Gunakan emoji yang relevan di setiap bagian untuk membuat tampilan lebih hidup dan menarik        
- Berikan waktu yang spesifik

Strukturkan output menjadi:
1. 📋 Ringkasan Kondisi Gudang 
2. 📊 Analisis Suhu dan Kelembaban (dengan emoji suhu 🌡️ dan kelembaban 💧, serta waktu spesifik)
3. 🚦 Status Gudang (Normal 🟢 / Warning 🟡 / Anomaly 🔴)
4. 💡 Rekomendasi Singkat dan Praktis (gunakan emoji ✅ atau ⚠️)


Hindari format markdown seperti ** dan bullet points.
Gunakan emoji secara konsisten untuk membuat analisis lebih visual dan mudah dibaca
Berikan emoji yang sesuai dengan konteks (contoh: 🌡️ untuk suhu, 💧 untuk kelembaban, ⚠️ untuk peringatan, ✅ untuk rekomendasi positif)


Gunakan data berikut:
- Suhu tertinggi: {$maxTemp->temperature}°C pada {$formatTime($maxTemp->created_at)}
- Suhu terendah: {$minTemp->temperature}°C pada {$formatTime($minTemp->created_at)}
- Kelembaban tertinggi: {$maxHum->humidity}% pada {$formatTime($maxHum->created_at)}
- Kelembaban terendah: {$minHum->humidity}% pada {$formatTime($minHum->created_at)}

";

        $response = Http::timeout(15)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemma-3-1b-it:generateContent?key=AIzaSyCY9MbdmO1pFH-E4T5r45rmClmPyjsMwyM',
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            );

        if (!$response->successful()) {
            Log::error('GEMINI ERROR', [
                'status' => $response->status(),
                'body' => $response->body(),
                'error' => 'Request failed or timed out'
            ]);
            return 'Layanan AI sedang tidak tersedia. Silakan coba lagi nanti.';
        }

        return $response->json('candidates.0.content.parts.0.text')
            ?? 'AI analysis unavailable.';
    }

    public static function analyzeTelegram()
    {
        $sensors = Sensor::whereDate('created_at', today())->get();
        
        if ($sensors->isEmpty()) {
            return '📭 Belum tersedia data sensor untuk dianalisis.';
        }

        $maxTemp = $sensors->sortByDesc('temperature')->first();
        $minTemp = $sensors->sortBy('temperature')->first();
        $maxHum = $sensors->sortByDesc('humidity')->first();
        $minHum = $sensors->sortBy('humidity')->first();

        $formatTime = fn ($t) => Carbon::parse($t)->format('H:i');
        
        // Tentukan status
        $status = 'Normal 🟢';
        $kondisi = 'stabil dan aman';
        if ($maxTemp->temperature >= 35 || $maxHum->humidity >= 80) {
            $status = 'Anomaly 🔴';
            $kondisi = 'mengalami lonjakan signifikan yang perlu penanganan segera';
        } elseif ($maxTemp->temperature >= 30 || $maxHum->humidity >= 70) {
            $status = 'Warning 🟡';
            $kondisi = 'cukup hangat dan lembap, perlu pemantauan ekstra';
        }

        $message = 
"🤖 <b>ANALISIS GUDANG</b>\n\n" .

"📋 <b>Ringkasan:</b>\n" .
"Kondisi gudang saat ini {$kondisi}. " .
"Suhu dan kelembaban menunjukkan pola yang perlu diperhatikan untuk menjaga kualitas penyimpanan.\n\n" .

"Suhu 🌡️ dan Kelembaban💧: \n" .
"🌡️Suhu berada pada rentang {$minTemp->temperature}°C hingga {$maxTemp->temperature}°C.\n" .
"🌡️Puncak suhu terjadi sekitar pukul {$formatTime($maxTemp->created_at)}.\n" .
"💧Kelembaban berkisar antara {$minHum->humidity}% hingga {$maxHum->humidity}%.\n\n" .

"🚦 <b>Status:</b> {$status}\n\n" .

"📌 <b>Rekomendasi:</b>\n" .
($status === 'Normal 🟢' 
    ? "✅ Lanjutkan pemantauan rutin\n✅ Pastikan ventilasi berjalan baik" 
    : "⚠️ Aktifkan ventilasi tambahan\n⚠️ Gunakan dehumidifier jika kelembaban tinggi");
    

        return $message;
    }
}