<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.4; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; text-align: center; }
        h2 { font-size: 26px; margin-bottom: 5px; }
        p { font-size: 16px; color: #666; }
        .summary { margin: 15px 0; }
        .summary ul { margin-left: 15px; }
        .summary li { margin: 3px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 14px; }
        th { background: #e8e8e8; border: 1px solid #999; padding: 5px 3px; text-align: center; font-weight: bold; }
        td { border: 1px solid #999; padding: 4px 3px; text-align: center; }
        tr:nth-child(even) { background: #f9f9f9; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

<div class="header">
    <h2>LAPORAN MONITORING GUDANG</h2>
    <p>Smart Warehouse Monitoring System | Dicetak: {{ now()->format('d M Y H:i:s') }}</p>
</div>

<div class="summary">
    <strong>Ringkasan Kondisi Gudang:</strong>
    <ul>
        <li>Suhu Rata-rata: <strong>{{ number_format($summary?->avg_temperature ?? 0, 2) }} °C</strong></li>
        <li>Kelembaban Rata-rata: <strong>{{ number_format($summary?->avg_humidity ?? 0, 2) }} %</strong></li>
        <li>Suhu Maks / Min: <strong>{{ $summary?->max_temperature ?? 0 }} °C / {{ $summary?->min_temperature ?? 0 }} °C</strong></li>
        <li>Kelembaban Maks / Min: <strong>{{ $summary?->max_humidity ?? 0 }} % / {{ $summary?->min_humidity ?? 0 }} %</strong></li>
    </ul>
</div>

<h3 style="font-size: 12px; margin-top: 20px; margin-bottom: 5px;">Log Data Sensor</h3>
<table>
    <thead>
        <tr>
            <th style="width: 35%;">Waktu</th>
            <th style="width: 32.5%;">Suhu (°C)</th>
            <th style="width: 32.5%;">Kelembaban (%)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($logs as $log)
        <tr>
            <td>{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
            <td>{{ number_format($log->temperature, 2) }}</td>
            <td>{{ number_format($log->humidity, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="3" style="text-align: center; color: #999;">Tidak ada data</td>
        </tr>
        @endforelse
    </tbody>
</table>

</body>
</html>
