<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SensorController extends Controller
{
        public function weeklyAverage()
    {
         $start = Carbon::now()->startOfWeek()->subDays(2); // Sabtu minggu lalu
         $end   = Carbon::now();                            // Hari ini

        $days = CarbonPeriod::create($start, $end);

        $labels = [];
        $avgTemp = [];
        $avgHum = [];

        foreach ($days as $day) {
            $labels[] = $day->translatedFormat('l'); // Senin, Selasa, ...

            $avgTemp[] = Sensor::whereDate('created_at', $day)
                ->avg('temperature') ?? 0;

            $avgHum[] = Sensor::whereDate('created_at', $day)
                ->avg('humidity') ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'temperature' => $avgTemp,
            'humidity' => $avgHum,
        ]);
    }

    public function monthlyAverage()
{
    $thisMonth = Carbon::now();
    $lastMonth = Carbon::now()->subMonth();

    return response()->json([
        'labels' => [
            $lastMonth->translatedFormat('F'),
            $thisMonth->translatedFormat('F'),
        ],
        'temperature' => [
            Sensor::whereMonth('created_at', $lastMonth->month)
                  ->whereYear('created_at', $lastMonth->year)
                  ->avg('temperature'),

            Sensor::whereMonth('created_at', $thisMonth->month)
                  ->whereYear('created_at', $thisMonth->year)
                  ->avg('temperature'),
        ],
        'humidity' => [
            Sensor::whereMonth('created_at', $lastMonth->month)
                  ->whereYear('created_at', $lastMonth->year)
                  ->avg('humidity'),

            Sensor::whereMonth('created_at', $thisMonth->month)
                  ->whereYear('created_at', $thisMonth->year)
                  ->avg('humidity'),
        ],
    ]);
}

}

