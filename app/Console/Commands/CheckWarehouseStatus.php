<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TelegramService;
use App\Models\Sensor;
class CheckWarehouseStatus extends Command
{
    protected $signature = 'warehouse:check';

public function handle(TelegramService $telegram)
{
    $latest = Sensor::latest()->first();

    $status = $this->determineStatus(
        $latest->temperature,
        $latest->humidity
    );

    if ($status !== cache('last_status')) {
        cache(['last_status' => $status]);

        $telegram->send(
            "⚠️ STATUS GUDANG: {$status}\n"
            ."🌡 Suhu: {$latest->temperature}°C\n"
            ."💧 Kelembaban: {$latest->humidity}%"
        );
    }
}

}