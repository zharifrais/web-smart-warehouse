<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Services\AiAnalysisService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function index()
    {
        $latest = Sensor::latest()->first();
        $previous = Sensor::latest()->skip(1)->first();
        $logs = Sensor::latest()->take(20)->get();

        // Determine warehouse status based on thresholds
        $status = 'Normal';
        $statusBadge = 'success';

        if ($latest) {
            if ($latest->temperature > 30 || $latest->humidity > 70) {
                $status = 'Warning';
                $statusBadge = 'warning';
            }
            
            if ($previous) {
                $deltaTemp = abs($latest->temperature - $previous->temperature);
                $deltaHum = abs($latest->humidity - $previous->humidity);
                
                if ($deltaTemp >= 5 || $deltaHum >= 10) {
                    $status = 'Anomaly';
                    $statusBadge = 'danger';
                }
            }
        }

        // OPTIONAL: AI analysis
        $analysis = 'Klik tombol untuk menjalankan analisis AI.';

        return view('monitoring.index', compact(
            'latest',
            'logs',
            'analysis',
            'status',
            'statusBadge'
        ));
    }
}
