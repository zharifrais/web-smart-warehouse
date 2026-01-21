<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Sensor;

class LaporanController extends Controller
{
    public function exportPdf()
{
    $logs = Sensor::latest()->limit(50)->get();

    $summary = [
        'avg_temp' => Sensor::avg('temperature'),
        'avg_hum'  => Sensor::avg('humidity'),
        'max_temp' => Sensor::max('temperature'),
        'min_temp' => Sensor::min('temperature'),
        'status'   => 'Warning',
    ];

    $pdf = Pdf::loadView('laporan.pdf', compact('logs', 'summary'));

    // ⬅️ SIMPAN KE STORAGE
    $fileName = 'Laporan_Monitoring_Gudang.pdf';
    Storage::disk('public')->put($fileName, $pdf->output());

    // ⬅️ tetap kirim ke browser
    return $pdf->download($fileName);
}

    public function exportPdfTelegram()
    {
        $pdfPath = storage_path('app/public/Laporan_Monitoring_Gudang.pdf');

        // Pastikan PDF sudah ada
        if (!file_exists($pdfPath)) {
            return response()->json(['error' => 'PDF not found']);
        }

        $token  = config('services.telegram.token');
        $chatId = config('services.telegram.chat_id');

        Http::attach(
            'document',
            file_get_contents($pdfPath),
            'Laporan_Monitoring_Gudang.pdf'
        )->post("https://api.telegram.org/bot{$token}/sendDocument", [
            'chat_id' => $chatId,
            'caption' => '📄 Laporan Monitoring Gudang'
        ]);

        return response()->json(['status' => 'sent']);
    }
}
