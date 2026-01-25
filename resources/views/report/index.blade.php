@extends('layouts.app')

@section('content')

@stack('scripts')
<h4 class="mb-1">Laporan Monitoring Gudang</h4>
<p class="text-muted mb-4">Ringkasan Grafik & Log Data Sensor</p>

{{-- CARD RINGKASAN --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm p-3">
            <small>Suhu Rata-rata</small>
            <h5 >{{ $summary['avg_temp'] ? number_format($summary['avg_temp'], 1) : '0.0' }} °C</h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm p-3">
            <small>Kelembaban Rata-rata</small>
            <h5>{{ $summary['avg_hum'] ? number_format($summary['avg_hum'], 1) : '0.0' }} %</h5>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm p-3">
            <small>Suhu Max / Min</small>
            <h6>{{ $summary['max_temp'] ?? 0 }}°C / {{ $summary['min_temp'] ?? 0 }}°C</h6>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm p-3">
            <small>Status Gudang</small><br>
            @if($latest)
                <span class="badge bg-{{ $summary['status_color'] }}">{{ $summary['status'] }}</span><br>
            @else
                <span class="badge bg-secondary">Tidak Ada Data</span>
            @endif
        </div>
    </div>
</div>

{{-- GRAFIK --}}
<div class="card shadow mb-4">
    <div class="card-body">
        <h6>Grafik Suhu & Kelembaban</h6>
        <canvas id="sensorChart" height="80"></canvas>
    </div>
</div>

{{-- TOMBOL EXPORT --}}
<div class="mb-3">
    <div class="row">
        <div class="col-md-8">
            <label class="form-label">Rentang Waktu</label>
            <select id="rangeFilter" class="form-select form-select-sm">
                <option value="today">Hari Ini</option>
                <option value="week">7 Hari Terakhir</option>
                <option value="month">30 Hari Terakhir</option>
                <option value="last_month">Bulan Lalu</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">&nbsp;</label>
            <div class="d-flex gap-2">
                <a id="exportExcelBtn" href="#" class="btn btn-success btn-sm flex-grow-1">
                    ⬇ Excel
                </a>
                <a id="exportPdfBtn" href="#" class="btn btn-danger btn-sm flex-grow-1">
                    ⬇ PDF
                </a>
            </div>
        </div>
    </div>
</div>

{{-- TABEL LOG --}}
<div class="card shadow">
    <div class="card-body">
        <h6>Log Data Sensor</h6>
        <div class="table-responsive">
            <table class="table table-striped table-sm" id="logsTable">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Suhu (°C)</th>
                        <th>Kelembaban (%)</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                    @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                        <td>{{ number_format($log->temperature, 2) }}</td>
                        <td>{{ number_format($log->humidity, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rangeFilter = document.getElementById('rangeFilter');
    const timeFilterContainer = document.getElementById('timeFilterContainer');
    const timeFilter = document.getElementById('timeFilter');
    const logsTableBody = document.getElementById('logsTableBody');
    const exportExcelBtn = document.getElementById('exportExcelBtn');
    const exportPdfBtn = document.getElementById('exportPdfBtn');

    // Load logs when range filter changes
    rangeFilter.addEventListener('change', loadLogs);

    // Update export links
    function updateExportLinks() {
        const range = rangeFilter.value;
        const params = `?range=${range}`;
        
        exportExcelBtn.href = `/laporan/export/excel${params}`;
        exportPdfBtn.href = `/laporan/export/pdf${params}`;
    }

    rangeFilter.addEventListener('change', updateExportLinks);

    // Initial load
    loadLogs();
    updateExportLinks();

    async function loadLogs() {
        const range = rangeFilter.value;

        try {
            const response = await fetch(`/api/report/logs?range=${range}`);
            const data = await response.json();

            // Update logs table
            if (data.logs.length === 0) {
                logsTableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                    </tr>
                `;
            } else {
                logsTableBody.innerHTML = data.logs.map(log => `
                    <tr>
                        <td>${log.time}</td>
                        <td>${log.temperature}</td>
                        <td>${log.humidity}</td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading logs:', error);
            logsTableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center text-danger">Error memuat data</td>
                </tr>
            `;
        }
    }

    const ctx = document.getElementById('sensorChart').getContext('2d');
    const chartData = @json($chart);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Suhu (°C)',
                    data: chartData.temperature,
                    tension: 0.3
                },
                {
                    label: 'Kelembaban (%)',
                    data: chartData.humidity,
                    tension: 0.3
                }
            ]
        }
    });
});
</script>
@endpush

