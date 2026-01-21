<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    protected string $token;
    protected string $chatId;

    public function __construct()
    {
        $this->token  = config('services.telegram.token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function send(string $message): void
    {
        Http::post("https://api.telegram.org/bot{$this->token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);
    }

    public function sendDocument($filePath, $caption = null)
    {
        if (!file_exists($filePath)) {
            \Log::error('PDF FILE NOT FOUND', ['path' => $filePath]);
            return false;
        }

        $token  = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        $response = \Illuminate\Support\Facades\Http::attach(
            'document',
            file_get_contents($filePath),
            'Laporan_Monitoring_Gudang.pdf'
        )->post("https://api.telegram.org/bot{$token}/sendDocument", [
            'chat_id' => $chatId,
            'caption' => $caption
        ]);

        if (!$response->successful()) {
            \Log::error('TELEGRAM PDF ERROR', $response->json());
        }
        $pdfPath = public_path('storage/Laporan_Monitoring_Gudang.pdf');
        \Log::info('PDF DEBUG', [
    'path' => $pdfPath,
    'exists' => file_exists($pdfPath),
]);



        return $response->successful();
    }
}