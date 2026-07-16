@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box" style="background:rgba(59,130,246,0.15); color:#3b82f6;">
                <i class="fas fa-lightbulb"></i>
            </div>
            <div class="value" id="activeLamps">-</div>
            <div class="label">Smart Lamps Active</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box" style="background:rgba(16,185,129,0.15); color:#10b981;">
                <i class="fas fa-trash-alt"></i>
            </div>
            <div class="value" id="totalBins">-</div>
            <div class="label">Waste Bins Monitored</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box" style="background:rgba(245,158,11,0.15); color:#f59e0b;">
                <i class="fas fa-car"></i>
            </div>
            <div class="value" id="parkingAvailable">-</div>
            <div class="label">Parking Available</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="icon-box" style="background:rgba(239,68,68,0.15); color:#ef4444;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="value" id="alertsCount">-</div>
            <div class="label">Active Alerts</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="data-card">
            <h6><i class="fas fa-chart-line"></i> REALTIME SENSOR DATA</h6>
            <canvas id="mainChart" height="100"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="data-card">
            <h6><i class="fas fa-chart-pie"></i> PARKING OCCUPANCY</h6>
            <canvas id="parkingChart" height="200"></canvas>
        </div>
    </div>
</div>

<!-- WASTE ALERT HISTORY (GLOBAL) -->
<div class="data-card mt-4">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h6 style="margin:0;"><i class="fas fa-bell"></i> RIWAYAT NOTIFIKASI TONG SAMPAH</h6>
        <button onclick="clearGlobalWasteHistory()" style="background:transparent; border:1px solid var(--border-color); color:var(--text-secondary); padding:5px 12px; border-radius:6px; font-size:0.8rem; cursor:pointer;">
            <i class="fas fa-trash-alt"></i> Clear History
        </button>
    </div>
    <div id="globalWasteHistory" style="max-height:250px; overflow-y:auto;">
        <div style="text-align:center; color:var(--text-secondary); padding:20px; font-size:0.85rem;">
            <i class="fas fa-inbox" style="font-size:1.5rem; margin-bottom:8px; display:block;"></i>
            Belum ada riwayat notifikasi
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Main Chart
    const mainCtx = document.getElementById('mainChart').getContext('2d');
    const mainChart = new Chart(mainCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: 'Lamp Brightness', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4, borderWidth: 2 },
                { label: 'Waste Level (%)', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', fill: true, tension: 0.4, borderWidth: 2 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#94a3b8' } } },
            scales: {
                x: { ticks: { color: '#4b5563', maxTicksLimit: 8 }, grid: { color: '#1e293b' } },
                y: { ticks: { color: '#4b5563' }, grid: { color: '#1e293b' }, min: 0, max: 100 }
            }
        }
    });

    // Parking Chart
    const parkCtx = document.getElementById('parkingChart').getContext('2d');
    const parkingChart = new Chart(parkCtx, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Available'],
            datasets: [{
                data: [25, 25],
                backgroundColor: ['#ef4444', '#10b981'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', padding: 15 } } }
        }
    });

    function fetchDashboardData() {
        fetch('/api/dashboard')
            .then(r => r.json())
            .then(data => {
                document.getElementById('activeLamps').textContent = data.active_lamps;
                document.getElementById('totalBins').textContent = data.total_bins;
                document.getElementById('parkingAvailable').textContent = data.parking_available;
                document.getElementById('alertsCount').textContent = data.alerts;

                // Update main chart
                const now = new Date().toLocaleTimeString('id-ID');
                mainChart.data.labels.push(now);
                mainChart.data.datasets[0].data.push(data.avg_lamp_brightness);
                mainChart.data.datasets[1].data.push(data.avg_waste_level);
                if (mainChart.data.labels.length > 15) {
                    mainChart.data.labels.shift();
                    mainChart.data.datasets.forEach(ds => ds.data.shift());
                }
                mainChart.update('none');

                // Update parking chart
                fetch('/api/parking')
                    .then(r => r.json())
                    .then(parking => {
                        parkingChart.data.datasets[0].data = [parking.occupied, parking.available];
                        parkingChart.update();
                    });
            });
    }

    // ===== GLOBAL WASTE HISTORY =====
    function renderGlobalWasteHistory() {
        const container = document.getElementById('globalWasteHistory');
        if (!container) return;

        let history = JSON.parse(localStorage.getItem('globalWasteAlertHistory') || '[]');

        if (history.length === 0) {
            container.innerHTML = `
                <div style="text-align:center; color:var(--text-secondary); padding:20px; font-size:0.85rem;">
                    <i class="fas fa-inbox" style="font-size:1.5rem; margin-bottom:8px; display:block;"></i>
                    Belum ada riwayat notifikasi
                </div>
            `;
            return;
        }

        container.innerHTML = history.map(entry => `
            <div style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-bottom:1px solid var(--border-color);">
                <div style="width:36px; height:36px; border-radius:8px; background:rgba(239,68,68,0.15); color:#ef4444; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:600; font-size:0.9rem; color:#e2e8f0; margin-bottom:3px;">
                        ${entry.binName} - ${entry.location}
                    </div>
                    <div style="font-size:0.8rem; color:#94a3b8;">
                        Tong sampah mencapai kapasitas penuh
                    </div>
                </div>
                <div style="font-size:0.75rem; font-weight:700; color:#ef4444; background:rgba(239,68,68,0.1); padding:3px 8px; border-radius:4px;">
                    ${entry.level}%
                </div>
                <div style="font-size:0.75rem; color:#64748b; white-space:nowrap;">
                    ${entry.time}
                </div>
            </div>
        `).join('');
    }

    function clearGlobalWasteHistory() {
        if (confirm('Yakin ingin menghapus semua riwayat notifikasi?')) {
            localStorage.removeItem('globalWasteAlertHistory');
            renderGlobalWasteHistory();
        }
    }

    // Render history saat load
    document.addEventListener('DOMContentLoaded', () => {
        renderGlobalWasteHistory();
    });

    setInterval(fetchDashboardData, 15000);
    fetchDashboardData();
</script>
@endpush