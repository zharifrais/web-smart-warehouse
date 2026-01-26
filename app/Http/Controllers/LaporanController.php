<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\SensorLog;

class LaporanController extends Controller
{
    public function exportPdf()
{
    $logs = SensorLog::latest()->limit(50)->get();

    $summary = [
        'avg_temp' => round(SensorLog::avg('temperature'), 1),
        'avg_hum'  => round(SensorLog::avg('humidity'), 1),
        'max_temp' => round(SensorLog::max('temperature'), 1),
        'min_temp' => round(SensorLog::min('temperature'), 1),
        'max_hum'  => round(SensorLog::max('humidity'), 1),
        'min_hum'  => round(SensorLog::min('humidity'), 1),
        'status'   => 'Normal',
    ];

    $pdf = Pdf::loadView('report.pdf', compact('logs', 'summary'));

    // ⬅️ SIMPAN KE STORAGE
    $fileName = 'Laporan_Monitoring_Gudang.pdf';
    Storage::disk('public')->put($fileName, $pdf->output());

    // ⬅️ tetap kirim ke browser
    return $pdf->download($fileName);
}

    public function exportPdfTelegram()
    {
        try {
            \Log::info('Starting PDF generation for Telegram');
            
            // Generate PDF realtime dari server
            $logs = SensorLog::latest()->limit(50)->get();
            
            \Log::info('Logs fetched', ['count' => $logs->count()]);

            $summary = [
                'avg_temp' => round(SensorLog::avg('temperature'), 1),
                'avg_hum'  => round(SensorLog::avg('humidity'), 1),
                'max_temp' => round(SensorLog::max('temperature'), 1),
                'min_temp' => round(SensorLog::min('temperature'), 1),
                'max_hum'  => round(SensorLog::max('humidity'), 1),
                'min_hum'  => round(SensorLog::min('humidity'), 1),
                'status'   => 'Normal',
            ];

            $pdf = Pdf::loadView('report.pdf', compact('logs', 'summary'));
            
            // Simpan ke storage
            $fileName = 'Laporan_Monitoring_Gudang_' . now()->format('YmdHis') . '.pdf';
            Storage::disk('public')->put($fileName, $pdf->output());
            
            $pdfPath = storage_path('app/public/' . $fileName);
            
            \Log::info('PDF saved', ['path' => $pdfPath, 'exists' => file_exists($pdfPath)]);

            if (!file_exists($pdfPath)) {
                \Log::error('PDF file not found after generation');
                return response()->json(['error' => 'PDF generation failed', 'status' => 'error']);
            }

            $token  = config('services.telegram.token');
            $chatId = config('services.telegram.chat_id');
            
            \Log::info('Sending PDF to Telegram', ['chat_id' => $chatId]);

            $response = Http::attach(
                'document',
                file_get_contents($pdfPath),
                $fileName
            )->post("https://api.telegram.org/bot{$token}/sendDocument", [
                'chat_id' => $chatId,
                'caption' => '📄 Laporan Monitoring Gudang (Generated: ' . now()->format('d/m/Y H:i:s') . ')'
            ]);
            
            \Log::info('Telegram response', ['status' => $response->status(), 'body' => $response->body()]);
            
            // Hapus file setelah dikirim (opsional)
            @unlink($pdfPath);

            return response()->json(['status' => 'sent', 'file' => $fileName]);
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage(), 'status' => 'error'], 500);
        }
    }
}
