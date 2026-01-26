document.addEventListener('DOMContentLoaded', function () {

    if (!document.getElementById('sensorChart')) {
        return; // stop kalau halaman ini tidak punya chart
    }

    let chart;
    const ctx = document.getElementById('sensorChart').getContext('2d');

    function renderStandardChart(data, range) {
    const ctx = document.getElementById('sensorChart').getContext('2d');
    if (chart) chart.destroy();

    const isToday = range === 'today';

    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Suhu (°C)',
                    data: data.temperature,
                    tension: 0.4,
                    borderWidth: 2,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59,130,246,0.15)'
                },
                {
                    label: 'Kelembaban (%)',
                    data: data.humidity,
                    tension: 0.4,
                    borderWidth: 2,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249,115,22,0.15)'
                }
            ]
        },
        options: {
            responsive: true,
            animation: isToday
                ? {
                      x: {
                          type: 'number',
                          easing: 'easeOutQuart',
                          duration: 1000,
                          from: NaN,
                          delay(ctx) {
                              return ctx.dataIndex * 40;
                          }
                      },
                      y: {
                          duration: 600,
                          easing: 'easeOutQuad'
                      },
                      opacity: {
                          duration: 1200,
                          easing: 'linear',
                          from: 0
                      }
                  }
                : false,
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
}

    function renderBarChart(data) {
    if (chart) chart.destroy();

    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Rata-rata Suhu (°C)',
                    data: data.temperature,
                    backgroundColor: '#3b82f6'
                },
                {
                    label: 'Rata-rata Kelembaban (%)',
                    data: data.humidity,
                    backgroundColor: '#f97316'
                }
            ]
        }
    });
}

function renderMonthlyChart(data) {
    if (chart) chart.destroy();

    chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Rata-rata Suhu (°C)',
                    data: data.temperature,
                    backgroundColor: '#3B82F6'
                },
                {
                    label: 'Rata-rata Kelembaban (%)',
                    data: data.humidity,
                    backgroundColor: '#f97316'
                }
            ]
        }
    });
}

    function loadChart(range = 'today') {
    
    if (range === 'week') {
    fetch('/api/sensor/weekly-average')
        .then(res => res.json())
        .then(data => renderBarChart(data));
    return;
    }

    if (range === 'month') {
        fetch('/api/sensor/monthly-average')
            .then(res => res.json())
            .then(data => renderMonthlyChart(data));
        return;
    }
    // MODE NORMAL
    fetch(`/api/sensor/filter?range=${range}`)
        .then(res => res.json())
        .then(data => {
            renderStandardChart(data);
        });
}

    loadChart();

    const rangeFilter = document.getElementById('rangeFilter');
    if (rangeFilter) {
        rangeFilter.addEventListener('change', e => {
            loadChart(e.target.value);
        });
    }

    let autoRefreshInterval = null;
    function startRealtime() {
    stopRealtime();
    autoRefreshInterval = setInterval(() => {
        loadChart('today');
    }, 2000);
}

function stopRealtime() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}


});
