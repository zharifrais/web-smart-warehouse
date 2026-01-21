<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SensorLog;
use App\Models\Sensor;
use App\Services\AiAnalysisService;

class DashboardController extends Controller
{
    public function index()
{
    $latest = Sensor::latest()->first();
    $previous = Sensor::latest()->skip(1)->first();

    $status = 'Normal';

    if ($latest && $previous) {
        $deltaTemp = abs($latest->temperature - $previous->temperature);
        $deltaHum  = abs($latest->humidity - $previous->humidity);

        if ($deltaTemp >= 5 || $deltaHum >= 10) {
            $status = 'Anomaly';
        } elseif ($latest->temperature > 30 || $latest->humidity > 70) {
            $status = 'Warning';
        }
    }

    $analysis = AiAnalysisService::analyze([
        'current_temperature' => $latest->temperature ?? 0,
        'current_humidity' => $latest->humidity ?? 0,
        'previous_temperature' => $previous->temperature ?? 0,
        'previous_humidity' => $previous->humidity ?? 0,
        'interval' => 10
    ]);

    $logs = SensorLog::orderBy('created_at', 'desc')->limit(50)->get();

    return view('dashboard.index', compact('latest', 'analysis', 'status', 'logs'));
}

    public function getChartData()
    {
        $logs = SensorLog::orderBy('created_at', 'asc')->limit(50)->get();

        return response()->json([
            'labels' => $logs->pluck('created_at')->map->format('H:i'),
            'temperature' => $logs->pluck('temperature'),
            'humidity' => $logs->pluck('humidity'),
        ]);
    }

    
}

