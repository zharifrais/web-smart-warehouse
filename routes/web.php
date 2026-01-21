<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Models\Sensor;
use App\Services\TelegramService;
use App\Services\AiAnalysisService;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\LaporanController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/monitoring', [MonitoringController::class, 'index']);
    Route::get('/api/monitoring/logs', [MonitoringController::class, 'getFilteredLogs']);

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/logout', [ProfileController::class, 'logout'])->name('profile.logout');
    
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings.index');
    Route::post('/settings/colors', [ProfileController::class, 'updateColors'])->name('settings.colors');

    Route::get('/api/sensor/chart', [DashboardController::class, 'getChartData']);
});

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/sensor/latest', function () {
    return Sensor::latest()->first();
});

Route::get('/sensor/history/{range}', function ($range) {
    if ($range === 'hour') {
        return Sensor::where('created_at', '>=', now()->subHour())->latest()->get();
    }

    if ($range === 'day') {
        return Sensor::whereDate('created_at', today())->latest()->get();
    }

    return Sensor::latest()->take(10)->get();
});




Route::get('/test-telegram', function () {
    $telegram = new TelegramService();
    $telegram->send("✅ Telegram Bot Smart Warehouse aktif!");
    return 'OK';
});
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);
Route::get('/telegram/laporan/pdf', [LaporanController::class, 'exportPdfTelegram']);



Route::get('/api/sensor/monthly-comparison', [SensorController::class, 'monthlyComparison']);

Route::get('/laporan', [ReportController::class, 'index']);
Route::get('/api/report/logs', [ReportController::class, 'getFilteredLogs']);

Route::get('/laporan/export/excel', [ReportController::class, 'exportExcel']);
Route::get('/laporan/export/pdf', [ReportController::class, 'exportPdf']);

