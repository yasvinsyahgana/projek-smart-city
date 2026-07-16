@extends('layouts.app')
@section('title', 'Smart Lamp')
@section('page-title', 'Smart Lamp Monitoring')

@section('content')
<!-- STATUS MODE -->
<div class="data-card mb-4" style="border-left: 4px solid #3b82f6;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div>
            <h6 style="margin-bottom:5px;"><i class="fas fa-info-circle"></i> System Status</h6>
            <div style="font-size:0.85rem; color:var(--text-secondary);">
                Mode: <strong id="monitorMode" style="color:#3b82f6;">-</strong> | 
                Light Sensor: <strong id="monitorLight" style="color:#f59e0b;">-</strong> |
                Time: <strong id="monitorTime" style="color:#10b981;">-</strong>
            </div>
        </div>
        <div id="autoStatus" style="display:none;">
            <span style="background:rgba(245,158,11,0.2); color:#f59e0b; padding:6px 14px; border-radius:20px; font-size:0.8rem; font-weight:600;">
                <i class="fas fa-robot"></i> AUTO MODE ACTIVE
            </span>
        </div>
    </div>
</div>

<div class="row g-4" id="lampContainer">
    <div class="col-12 text-center text-muted">Memuat data...</div>
</div>

<div class="data-card mt-4">
    <h6><i class="fas fa-chart-line"></i> Lamp Power Consumption History</h6>
    <div style="position: relative; height: 300px; width: 100%;">
    <canvas id="lampChart"></canvas>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const lampNames = {
        lamp_1: 'Street Lamp A - Jl. Sudirman',
        lamp_2: 'Street Lamp B - Jl. Thamrin',
        lamp_3: 'Street Lamp C - Jl. Gatot Subroto',
        lamp_4: 'Street Lamp D - Jl. Rasuna Said'
    };

       const lampCtx = document.getElementById('lampChart')?.getContext('2d');
    let lampChart;
    if (lampCtx) {
        lampChart = new Chart(lampCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    { label: 'Lamp 1', data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', tension: 0.4, fill: true, borderWidth: 2 },
                    { label: 'Lamp 2', data: [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.4, fill: true, borderWidth: 2 },
                    { label: 'Lamp 3', data: [], borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,0.1)', tension: 0.4, fill: true, borderWidth: 2 },
                    { label: 'Lamp 4', data: [], borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', tension: 0.4, fill: true, borderWidth: 2 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { labels: { color: '#94a3b8' } },
                    title: { display: true, text: 'Power Consumption (Watt)', color: '#94a3b8' }
                },
                scales: {
                    x: { 
                        ticks: { color: '#4b5563', maxTicksLimit: 10 }, 
                        grid: { color: '#1e293b' } 
                    },
                    y: { 
                        ticks: { color: '#4b5563' }, 
                        grid: { color: '#1e293b' }, 
                        min: 0, 
                        max: 10,
                        title: { display: true, text: 'Watt', color: '#94a3b8' }
                    }
                }
            }
        });
    }

    function renderLamps(data) {
        const container = document.getElementById('lampContainer');
        container.innerHTML = '';

        // Update status info
        const modeLabels = {
            'manual': ' Manual',
            'auto_schedule': ' Auto Schedule (Maghrib-Subuh)',
            'auto_sensor': '🌩️ Auto Sensor (Cahaya)'
        };
        document.getElementById('monitorMode').textContent = modeLabels[data.control_mode] || data.control_mode;
        document.getElementById('monitorLight').textContent = data.sensor_light_level + '%';
        document.getElementById('monitorTime').textContent = data.timestamp;
        
        const isAuto = data.control_mode !== 'manual';
        document.getElementById('autoStatus').style.display = isAuto ? 'block' : 'none';

        Object.keys(lampNames).forEach((key) => {
            const lamp = data[key];
            const isOn = lamp.status === 1;
            const color = isOn ? '#f59e0b' : '#4b5563';

            container.innerHTML += `
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card" style="border-top: 3px solid ${color}">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <div class="icon-box" style="background:${isOn ? 'rgba(245,158,11,0.15)' : 'rgba(75,85,99,0.15)'}; color:${color}; margin-bottom:0;">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <span style="background:${isOn ? 'rgba(245,158,11,0.2)' : 'rgba(75,85,99,0.2)'}; color:${color}; padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:600;">
                                ${isOn ? ' ON' : '⚫ OFF'}
                            </span>
                        </div>
                        <h6 style="margin-bottom:10px;">${lampNames[key]}</h6>
                        <div style="margin-bottom:15px;">
                            <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-secondary); margin-bottom:5px;">
                                <span>Brightness</span>
                                <span style="color:${color}; font-weight:600;">${lamp.brightness}%</span>
                            </div>
                            <div class="waste-bar">
                                <div class="waste-bar-fill" style="width:${lamp.brightness}%; background:${color}"></div>
                            </div>
                        </div>
                        <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:8px;">
                            <i class="fas fa-bolt"></i> Power: ${lamp.power}W
                        </div>
                        ${isAuto ? '<div style="font-size:0.75rem; color:#f59e0b;"><i class="fas fa-robot"></i> Auto-controlled</div>' : '<div style="font-size:0.75rem; color:#3b82f6;"><i class="fas fa-hand-pointer"></i> Manual</div>'}
                    </div>
                </div>
            `;
        });
    }

        function fetchLampData() {
        fetch('/api/lamp')
            .then(r => r.json())
            .then(data => {
                renderLamps(data);
                if (lampChart) {
                    const now = new Date().toLocaleTimeString('id-ID');
                    lampChart.data.labels.push(now);
                    lampChart.data.datasets[0].data.push(data.lamp_1.power);
                    lampChart.data.datasets[1].data.push(data.lamp_2.power);
                    lampChart.data.datasets[2].data.push(data.lamp_3.power);
                    lampChart.data.datasets[3].data.push(data.lamp_4.power);
                    
                    // Batasi 20 data points
                    if (lampChart.data.labels.length > 20) {
                        lampChart.data.labels.shift();
                        lampChart.data.datasets.forEach(ds => ds.data.shift());
                    }
                    lampChart.update('none'); // Update tanpa animasi
                }
            });
    }

    setInterval(fetchLampData, 15000);
    fetchLampData();
</script>
@endpush