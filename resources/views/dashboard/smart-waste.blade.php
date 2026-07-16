@extends('layouts.app')
@section('title', 'Smart Waste')
@section('page-title', 'Smart Waste Monitoring')

@section('content')
<!-- TOAST NOTIFICATION CONTAINER -->
<div id="toastContainer" style="position:fixed; top:20px; right:20px; z-index:9999; display:flex; flex-direction:column; gap:10px; max-width:350px;"></div>

<!-- WASTE BINS CARDS -->
<div class="row g-4" id="wasteContainer">
    <div class="col-12 text-center text-muted">Memuat data...</div>
</div>

<!-- WASTE LEVEL COMPARISON CHART -->
<div class="data-card mt-4">
    <h6><i class="fas fa-chart-bar"></i> WASTE LEVEL COMPARISON</h6>
    <canvas id="wasteChart" height="80"></canvas>
</div>

<!-- ALERT HISTORY / RIWAYAT -->
<div class="data-card mt-4">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h6 style="margin:0;"><i class="fas fa-history"></i> RIWAYAT ALERT TONG SAMPAH</h6>
        <button onclick="clearHistory()" style="background:transparent; border:1px solid var(--border-color); color:var(--text-secondary); padding:5px 12px; border-radius:6px; font-size:0.8rem; cursor:pointer;">
            <i class="fas fa-trash-alt"></i> Clear History
        </button>
    </div>
    <div id="alertHistory" style="max-height:300px; overflow-y:auto;">
        <div style="text-align:center; color:var(--text-secondary); padding:20px; font-size:0.85rem;">
            <i class="fas fa-inbox" style="font-size:1.5rem; margin-bottom:8px; display:block;"></i>
            Belum ada riwayat alert
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Toast Notification Styles */
    .toast-notification {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid #ef4444;
        border-left: 4px solid #ef4444;
        border-radius: 10px;
        padding: 15px 20px;
        color: #e2e8f0;
        box-shadow: 0 10px 40px rgba(239, 68, 68, 0.3);
        animation: slideInRight 0.4s ease-out;
        position: relative;
        overflow: hidden;
    }

    .toast-notification::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #ef4444, #f59e0b);
        animation: progressLine 5s linear forwards;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    @keyframes progressLine {
        from { width: 100%; }
        to { width: 0%; }
    }

    .toast-notification.removing {
        animation: slideOutRight 0.3s ease-in forwards;
    }

    .toast-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .toast-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .toast-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #ef4444;
    }

    .toast-message {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-left: 42px;
    }

    .toast-close {
        position: absolute;
        top: 8px;
        right: 10px;
        background: transparent;
        border: none;
        color: #64748b;
        cursor: pointer;
        font-size: 1rem;
    }

    .toast-close:hover { color: #ef4444; }

    /* Alert History Styles */
    .alert-entry {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 15px;
        border-bottom: 1px solid var(--border-color);
        transition: background 0.2s;
    }

    .alert-entry:hover {
        background: rgba(239, 68, 68, 0.05);
    }

    .alert-entry:last-child {
        border-bottom: none;
    }

    .alert-badge {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .alert-info {
        flex: 1;
    }

    .alert-title {
        font-weight: 600;
        font-size: 0.9rem;
        color: #e2e8f0;
        margin-bottom: 3px;
    }

    .alert-desc {
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .alert-time {
        font-size: 0.75rem;
        color: #64748b;
        white-space: nowrap;
    }

    .alert-level {
        font-size: 0.75rem;
        font-weight: 700;
        color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
        padding: 3px 8px;
        border-radius: 4px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const binNames = {
        bin_1: 'Bin A',
        bin_2: 'Bin B',
        bin_3: 'Bin C',
        bin_4: 'Bin D'
    };

    const binLocations = {
        bin_1: 'Jl. Sudirman',
        bin_2: 'Jl. Thamrin',
        bin_3: 'Jl. Gatot Subroto',
        bin_4: 'Jl. Rasuna Said'
    };

    // Track notifikasi yang sudah ditampilkan (hindari spam)
    let notifiedBins = {};
    let alertHistoryData = JSON.parse(localStorage.getItem('wasteAlertHistory') || '[]');

    function getLevelColor(level) {
        if (level > 80) return '#ef4444';
        if (level > 50) return '#f59e0b';
        return '#10b981';
    }

    // ===== CHART =====
    const wasteCtx = document.getElementById('wasteChart')?.getContext('2d');
    let wasteChart;
    if (wasteCtx) {
        wasteChart = new Chart(wasteCtx, {
            type: 'bar',
            data: {
                labels: ['Bin A', 'Bin B', 'Bin C', 'Bin D'],
                datasets: [{
                    label: 'Fill Level (%)',
                    data: [0, 0, 0, 0],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
                    y: { ticks: { color: '#4b5563' }, grid: { color: '#1e293b' }, min: 0, max: 100 }
                }
            }
        });
    }

    // ===== RENDER BINS =====
    function renderWasteBins(data) {
        const container = document.getElementById('wasteContainer');
        container.innerHTML = '';

        Object.keys(binNames).forEach(key => {
            const bin = data[key];
            const color = getLevelColor(bin.level);
            const isFull = bin.level > 80;

            container.innerHTML += `
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card" style="border-top: 3px solid ${color}">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <div class="icon-box" style="background:${isFull ? 'rgba(239,68,68,0.15)' : 'rgba(16,185,129,0.15)'}; color:${color}; margin-bottom:0;">
                                <i class="fas fa-trash-alt"></i>
                            </div>
                            <span style="background:${isFull ? 'rgba(239,68,68,0.2)' : 'rgba(16,185,129,0.2)'}; color:${color}; padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:600;">
                                ${isFull ? '️ PENUH' : '✅ Normal'}
                            </span>
                        </div>
                        <h6 style="margin-bottom:5px;">${binNames[key]}</h6>
                        <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:15px;">
                            <i class="fas fa-map-marker-alt"></i> ${bin.location}
                        </div>
                        <div style="text-align:center; margin: 20px 0;">
                            <div style="font-size:2.5rem; font-weight:700; color:${color}">${bin.level}%</div>
                            <div style="font-size:0.85rem; color:var(--text-secondary);">Fill Level</div>
                        </div>
                        <div class="waste-bar" style="height:12px;">
                            <div class="waste-bar-fill" style="width:${bin.level}%; background:${color}"></div>
                        </div>
                    </div>
                </div>
            `;

            // Cek notifikasi
            checkNotification(key, bin);
        });
    }

    // ===== CEK & TAMPILKAN NOTIFIKASI =====
    function checkNotification(binKey, bin) {
        const isFull = bin.level > 80;
        const binName = binNames[binKey];
        const location = bin.location;

        // Tampilkan notifikasi jika penuh dan belum dinotif untuk level ini
        if (isFull && notifiedBins[binKey] !== bin.level) {
            notifiedBins[binKey] = bin.level;
            showToast(binName, location, bin.level);
            addToHistory(binName, location, bin.level);
        }

        // Reset notif jika level turun di bawah 80
        if (!isFull && notifiedBins[binKey]) {
            delete notifiedBins[binKey];
        }
    }

    // ===== TAMPILKAN TOAST NOTIFICATION =====
    function showToast(binName, location, level) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
            <div class="toast-header">
                <div class="toast-icon">🗑️</div>
                <div class="toast-title">⚠️ ${binName} PENUH!</div>
            </div>
            <div class="toast-message">
                <strong>${location}</strong> mencapai <strong>${level}%</strong>. Segera lakukan pengangkutan!
            </div>
        `;
        container.appendChild(toast);

        // Auto remove setelah 5 detik
        setTimeout(() => {
            if (toast.parentElement) {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    // ===== TAMBAH KE RIWAYAT =====
    function addToHistory(binName, location, level) {
        const now = new Date();
        const timeStr = now.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const entry = {
            id: Date.now(),
            binName: binName,
            location: location,
            level: level,
            time: timeStr,
            timestamp: now.getTime()
        };

        alertHistoryData.unshift(entry);

        // Batasi 50 riwayat terbaru
        if (alertHistoryData.length > 50) {
            alertHistoryData = alertHistoryData.slice(0, 50);
        }

        // Simpan ke localStorage
        localStorage.setItem('wasteAlertHistory', JSON.stringify(alertHistoryData));

        renderHistory();
    }

    // ===== RENDER RIWAYAT =====
    function renderHistory() {
        const container = document.getElementById('alertHistory');

        if (alertHistoryData.length === 0) {
            container.innerHTML = `
                <div style="text-align:center; color:var(--text-secondary); padding:20px; font-size:0.85rem;">
                    <i class="fas fa-inbox" style="font-size:1.5rem; margin-bottom:8px; display:block;"></i>
                    Belum ada riwayat alert
                </div>
            `;
            return;
        }

        container.innerHTML = alertHistoryData.map(entry => `
            <div class="alert-entry">
                <div class="alert-badge">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="alert-info">
                    <div class="alert-title">${entry.binName} - ${entry.location}</div>
                    <div class="alert-desc">Tong sampah mencapai kapasitas penuh</div>
                </div>
                <div class="alert-level">${entry.level}%</div>
                <div class="alert-time">${entry.time}</div>
            </div>
        `).join('');
    }

    // ===== CLEAR HISTORY =====
    function clearHistory() {
        if (confirm('Yakin ingin menghapus semua riwayat alert?')) {
            alertHistoryData = [];
            localStorage.removeItem('wasteAlertHistory');
            renderHistory();
        }
    }

    // ===== FETCH DATA =====
    function fetchWasteData() {
        fetch('/api/waste')
            .then(r => r.json())
            .then(data => {
                renderWasteBins(data);
                if (wasteChart) {
                    wasteChart.data.datasets[0].data = [
                        data.bin_1.level, data.bin_2.level,
                        data.bin_3.level, data.bin_4.level
                    ];
                    wasteChart.update();
                }
            });
    }

    // Init
    renderHistory();
    setInterval(fetchWasteData, 25000);
    fetchWasteData();
</script>
@endpush