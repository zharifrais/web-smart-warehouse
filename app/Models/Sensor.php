<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Sensor extends Model
{
    use HasFactory;

    // Jika nama tabel bukan "sensors", UNCOMMENT & sesuaikan
    // protected $table = 'nama_tabel_kamu';

    protected $fillable = [
        'temperature',
        'humidity',
    ];

    public $timestamps = true;
    protected $table = 'sensor_logs';

    /**
     * Get summary statistics of sensor data
     */
    public static function summary()
    {
        return self::selectRaw('
            COUNT(*) as total_records,
            AVG(temperature) as avg_temperature,
            MAX(temperature) as max_temperature,
            MIN(temperature) as min_temperature,
            AVG(humidity) as avg_humidity,
            MAX(humidity) as max_humidity,
            MIN(humidity) as min_humidity,
            MAX(created_at) as last_updated
        ')->first();
    }

}
