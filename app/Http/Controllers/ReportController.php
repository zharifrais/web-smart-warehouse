<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Services\SensorService;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SensorLogExport;

class ReportController extends Controller
{
    public function index()
    {
        $latest = Sensor::latest()->first();
        $previous = Sensor::latest()->skip(1)->first();
        $logs = Sensor::latest()->limit(100)->get();
        $summary = Sensor::summary();
        
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
        
        // Persiapkan data ringkasan
        $summaryData = [
            'avg_temp' => $summary?->avg_temperature ?? 0,
            'avg_hum' => $summary?->avg_humidity ?? 0,
            'max_temp' => $summary?->max_temperature ?? 0,
            'min_temp' => $summary?->min_temperature ?? 0,
            'status' => $status,
            'status_color' => $statusBadge,
            'latest_temp' => $latest?->temperature ?? 0,
            'latest_hum' => $latest?->humidity ?? 0,
        ];
        
        // Persiapkan data grafik
        $chartData = [
            'labels' => $logs->pluck('created_at')->map(fn($d) => $d->format('H:i'))->toArray(),
            'temperature' => $logs->pluck('temperature')->toArray(),
            'humidity' => $logs->pluck('humidity')->toArray(),
        ];
        
        return view('report.index', [
            'logs' => $logs,
            'summary' => $summaryData,
            'chart' => $chartData,
            'latest' => $latest,
        ]);
    }

    private function getFilteredData($range = 'today', $timeFilter = 'full_day')
    {
        $query = Sensor::query();

        // Apply range filter
        if ($range === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($range === 'week') {
            $query->where('created_at', '>=', now()->subDays(7))->where('created_at', '<=', now());
        } elseif ($range === 'month') {
            $query->where('created_at', '>=', now()->subDays(30))->where('created_at', '<=', now());
        } elseif ($range === 'last_month') {
            $query->whereYear('created_at', now()->subMonth()->year)
                  ->whereMonth('created_at', now()->subMonth()->month);
        }

        // Apply time filter for today
        if ($range === 'today') {
            if ($timeFilter === '15min') {
                $query->where('created_at', '>=', now()->subMinutes(15));
            } elseif ($timeFilter === '30min') {
                $query->where('created_at', '>=', now()->subMinutes(30));
            } elseif ($timeFilter === '60min') {
                $query->where('created_at', '>=', now()->subHour());
            }
        }

        return $query->orderBy('created_at', 'asc')->limit(100)->get();
    }

    public function getFilteredLogs(Request $request)
    {
        try {
            $range = $request->get('range', 'today');
            $timeFilter = $request->get('time_filter', 'full_day');

            if ($range === 'week') {
                // Ambil 10 data per hari selama 7 hari terakhir
                $logs = collect();
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i)->toDateString();
                    $dailyLogs = Sensor::whereDate('created_at', $date)
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();
                    $logs = $logs->merge($dailyLogs);
                }
                $logs = $logs->sortBy('created_at')->values();
            } elseif ($range === 'month') {
                // Ambil 3 data per hari selama 30 hari terakhir (total ~90 data)
                $logs = collect();
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i)->toDateString();
                    $dailyLogs = Sensor::whereDate('created_at', $date)
                        ->orderBy('created_at', 'desc')
                        ->limit(3)
                        ->get();
                    $logs = $logs->merge($dailyLogs);
                }
                $logs = $logs->sortBy('created_at')->values();
            } else {
                $logs = $this->getFilteredData($range, $timeFilter);
            }

            return response()->json([
                'logs' => $logs->map(fn($log) => [
                    'time' => $log->created_at->format('d-m-Y H:i:s'),
                    'temperature' => number_format($log->temperature, 2),
                    'humidity' => number_format($log->humidity, 2),
                ])
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getFilteredLogs: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'logs' => []
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $range = $request->get('range', 'today');
        $timeFilter = $request->get('time_filter', 'full_day');
        
        $logs = $this->getFilteredData($range, $timeFilter);

        return Excel::download(
            new SensorLogExport($logs),
            'log_sensor_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        // Increase execution time untuk PDF generation
        set_time_limit(300);
        
        $range = $request->get('range', 'today');
        $timeFilter = $request->get('time_filter', 'full_day');
        
        $logs = $this->getFilteredData($range, $timeFilter);
        $summary = Sensor::summary();

        $data = [
            'logs' => $logs,
            'summary' => $summary
        ];

        $pdf = Pdf::loadView('report.pdf', $data);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('laporan_monitoring_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    
}