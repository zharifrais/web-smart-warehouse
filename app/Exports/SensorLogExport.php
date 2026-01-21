<?php

namespace App\Exports;

use App\Models\SensorLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SensorLogExport implements FromCollection, WithHeadings, WithMapping
{
    protected $logs;

    public function __construct($logs = null)
    {
        $this->logs = $logs;
    }

    public function collection()
    {
        if ($this->logs) {
            return $this->logs;
        }
        return SensorLog::orderBy('created_at', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Waktu',
            'Suhu (°C)',
            'Kelembaban (%)'
        ];
    }

    public function map($log): array
    {
        return [
            $log->created_at->format('d-m-Y H:i:s'),
            $log->temperature,
            $log->humidity,
        ];
    }
}
