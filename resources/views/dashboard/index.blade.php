@extends('layouts.app')

@section('content')
    <h2>Dashboard Monitoring</h2>
    <p class="text-muted">Smart Warehouse - Verza Audio Sound System</p>

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

    <!-- FILTER -->
    <div class="mb-3">
        <label>Rentang Waktu</label>
        <select id="rangeFilter" class="form-select w-25">
            <option value="today">Hari Ini</option>
            <option value="week">7 Hari Terakhir</option>
            <option value="month">30 Hari Terakhir</option>
        </select>
    </div>



<div class="card shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Monitoring Suhu dan Kelembaban Gudang</h5>
        <a href="/monitoring" class="fs-3 text-decoration-none">›</a>
    </div>

    {{-- CHART --}}
<div class="card shadow mb-3">
    <div class="card-body">
        <canvas id="sensorChart" height="120"></canvas>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/monitoring-chart.js') }}"></script>

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

@endsection
