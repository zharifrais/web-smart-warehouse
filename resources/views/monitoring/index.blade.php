@extends('layouts.app')

@section('content')

<h4 class="mb-3">Monitoring Detail Gudang</h4>
<p class="text-muted">Analisis & Log Data Sensor</p>

<!-- KARTU RINGKAS -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm p-3">
                <h6>Suhu Terbaru</h6>
                <h3 id="latest-temperature">{{ $latest->temperature ?? '-' }}°C</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm p-3">
                <h6>Kelembaban Terbaru</h6>
                <h3 id="latest-humidity">{{ $latest->humidity ?? '-' }}%</h3>
            </div>
        </div>
    </div>

{{-- STATUS --}}
<div class="card shadow mb-3">
    <div class="card-body">
        <h5>Status Gudang</h5>
        @if($latest)
            <div class="mb-2">
                <span class="badge bg-{{ $statusBadge }}">{{ $status }}</span>
            </div>
            <small class="text-muted d-block">
               {{-- <strong>Suhu:</strong> {{ $latest->temperature }}°C (Batas Aman: ≤ 30°C)<br>
                <strong>Kelembaban:</strong> {{ $latest->humidity }}% (Batas Aman: ≤ 70%)--}}
            </small>
        @else
            <span class="badge bg-secondary">Tidak Ada Data</span>
        @endif
    </div>
</div>

<!-- FILTER -->
<div class="mb-3">
    <label>Rentang Waktu</label>
    <select id="rangeFilter" class="form-select w-25">
        <option value="today">Hari Ini</option>
        <option value="week">7 Hari Terakhir</option>
        <option value="month">30 Hari Terakhir</option>
    </select>
</div>


{{-- CHART --}}
<div class="card shadow mb-3">
    <div class="card-body">
        <canvas id="sensorChart" height="120"></canvas>
    </div>
</div>


{{-- AI --}}
<div class="card shadow mb-3">
    <div class="card-body">
        <h5>Analisis AI</h5>
        <span id="ai-status" class="badge bg-secondary mb-2">Siap</span><br>
        <button id="refresh-ai" class="btn btn-outline-primary btn-sm mb-2">
            🔄 Refresh Analisis AI
        </button>
        <div id="ai-analysis-text" class="ai-output">
    <em>Silakan klik tombol Refresh Analisis AI</em>
</div>
    </div>
</div>

{{-- LOG --}}
<div class="card shadow">
    <div class="card-body">
        <h5>Log Sensor</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Suhu</th>
                    <th>Kelembaban</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->temperature }}</td>
                    <td>{{ $log->humidity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>


@endsection
<style>
.ai-output {
    white-space: pre-line;
    line-height: 1.7;
    font-size: 0.95rem;
}

.ai-output h5 {
    margin-top: 12px;
    font-weight: 600;
}

.ai-output ul {
    padding-left: 18px;
}

.ai-output li {
    margin-bottom: 6px;
}
</style>
<script>
function updateLatest() {
    fetch('/sensor/latest')
        .then(res => res.json())
        .then(data => {
            if (!data) return;
            const tempEl = document.getElementById('latest-temperature');
            const humEl = document.getElementById('latest-humidity');
            if (tempEl) tempEl.innerText = data.temperature + '°C';
            if (humEl) humEl.innerText = data.humidity + '%';
        })
        .catch(err => console.error('Error fetching sensor data:', err));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        updateLatest();
        setInterval(updateLatest, 2000);
    });
} else {
    updateLatest();
    setInterval(updateLatest, 2000);
}
</script>
<script src="{{ asset('js/ai.js') }}?v={{ time() }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/monitoring-chart.js') }}?v={{ time() }}"></script>