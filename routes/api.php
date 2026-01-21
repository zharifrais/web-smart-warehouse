<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Sensor;
use Carbon\Carbon;
use App\Services\AiAnalysisService;
use App\Http\Controllers\SensorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/sensor/latest', fn() => Sensor::latest()->first());

Route::get('/sensor/filter', function (Request $request) {

    $range = $request->get('range', 'today');

    if ($range === 'week') {
        $start = Carbon::now()->subDays(7);
    } elseif ($range === 'month') {
        $start = Carbon::now()->startOfMonth();
    } else {
        $start = Carbon::today();
    }

    $logs = Sensor::where('created_at', '>=', $start)
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json([
        'labels' => $logs->pluck('created_at')->map(fn($t) => $t->format('d-m H:i')),
        'temperature' => $logs->pluck('temperature'),
        'humidity' => $logs->pluck('humidity'),
        'logs' => $logs
    ]);
});

Route::get('/ai/analyze', function () {
    $latest = Sensor::latest()->first();
    $previous = Sensor::latest()->skip(1)->first();

    if (!$latest) {
        return response()->json([
            'status' => 'error',
            'message' => 'Data sensor belum tersedia'
        ]);
    }

    $analysis = AiAnalysisService::analyze([
        'current_temperature' => $latest->temperature,
        'current_humidity' => $latest->humidity,
        'previous_temperature' => $previous->temperature ?? $latest->temperature,
        'previous_humidity' => $previous->humidity ?? $latest->humidity,
        'interval' => 10
    ]);

    return response()->json([
        'status' => 'success',
        'analysis' => $analysis
    ]);
});

Route::get('/sensor/monthly-comparison', [SensorController::class, 'monthlyComparison']);
Route::get('/sensor/weekly-average', [SensorController::class, 'weeklyAverage']);
Route::get('/sensor/monthly-average', [SensorController::class, 'monthlyAverage']);


