@extends('layouts.app')
@section('title', 'Control Center')
@section('page-title', 'IoT Device Control Center')

@section('content')
<!-- MODE SELECTOR -->
<div class="data-card mb-4" style="border-left: 4px solid #3b82f6;">
    <h6><i class="fas fa-cogs"></i> Control Mode</h6>
    <p style="color:var(--text-secondary); font-size:0.85rem; margin-bottom:15px;">
        Pilih mode operasi lampu. Mode otomatis akan mengontrol lampu berdasarkan jadwal atau sensor cahaya.
    </p>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="mode-card" id="mode-manual" onclick="handleSetMode('manual')" style="cursor:pointer; padding:15px; border-radius:10px; border:2px solid #3b82f6; background:rgba(59,130,246,0.1);">
                <div style="font-size:1.5rem; margin-bottom:8px;">🔧</div>
                <div style="font-weight:700; margin-bottom:5px;">Manual Control</div>
                <div style="font-size:0.8rem; color:var(--text-secondary);">Kontrol penuh via panel ini</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mode-card" id="mode-auto-schedule" onclick="handleSetMode('auto_schedule')" style="cursor:pointer; padding:15px; border-radius:10px; border:2px solid var(--border-color); background:var(--bg-card);">
                <div style="font-size:1.5rem; margin-bottom:8px;">🕐</div>
                <div style="font-weight:700; margin-bottom:5px;">Auto Schedule</div>
                <div style="font-size:0.8rem; color:var(--text-secondary);">Nyala sesuai jadwal (bisa diatur)</div>

                <!-- TAMBAHAN TOGGLE AUTO SCHEDULE (HAPUS 'checked' default) -->
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px; background:rgba(0,0,0,0.2); border-radius:6px; margin-top:10px;">
                    <span style="font-size:0.75rem; color:var(--text-secondary);">Enabled</span>
                    <label class="toggle-switch" style="margin:0;">
                        <input type="checkbox" id="toggleAutoSchedule" onchange="handleToggleAutoSchedule(this.checked); event.stopPropagation();">
                        <span class="toggle-slider" style="width:40px; height:20px;"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mode-card" id="mode-auto-sensor" onclick="handleSetMode('auto_sensor')" style="cursor:pointer; padding:15px; border-radius:10px; border:2px solid var(--border-color); background:var(--bg-card);">
                <div style="font-size:1.5rem; margin-bottom:8px;">🌩️</div>
                <div style="font-weight:700; margin-bottom:5px;">Auto Sensor</div>
                <div style="font-size:0.8rem; color:var(--text-secondary);">Nyala saat gelap (threshold bisa diatur)</div>

                <!-- TAMBAHAN TOGGLE AUTO SENSOR -->
                <div style="display:flex; justify-content:space-between; align-items:center; padding:8px; background:rgba(0,0,0,0.2); border-radius:6px; margin-top:10px;">
                    <span style="font-size:0.75rem; color:var(--text-secondary);">Enabled</span>
                    <label class="toggle-switch" style="margin:0;">
                        <input type="checkbox" id="toggleAutoSensor" onchange="handleToggleAutoSensor(this.checked); event.stopPropagation();">
                        <span class="toggle-slider" style="width:40px; height:20px;"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AUTO SETTINGS PANEL -->
<div class="data-card mb-4" id="autoSettingsPanel" style="display:none; border-left: 4px solid #f59e0b;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
        <h6><i class="fas fa-sliders-h"></i> Auto Mode Settings</h6>
        <button onclick="handleSaveSettings()" class="btn btn-sm btn-success">
            <i class="fas fa-save"></i> Save Settings
        </button>
    </div>
    
    <div class="row g-3">
        <div class="col-md-6" id="scheduleSettings">
            <h6 style="color:#f59e0b; margin-bottom:12px;"><i class="fas fa-clock"></i> Schedule Settings</h6>
            <div class="row g-3">
                <div class="col-6">
                    <label style="font-size:0.85rem; color:var(--text-secondary);">Turn ON Time</label>
                    <div style="display:flex; gap:8px; margin-top:5px;">
                        <input type="number" id="onHour" min="0" max="23" class="form-control" placeholder="HH" style="flex:1;">
                        <span style="align-self:center; font-weight:700;">:</span>
                        <input type="number" id="onMinute" min="0" max="59" class="form-control" placeholder="MM" style="flex:1;">
                    </div>
                </div>
                <div class="col-6">
                    <label style="font-size:0.85rem; color:var(--text-secondary);">Turn OFF Time</label>
                    <div style="display:flex; gap:8px; margin-top:5px;">
                        <input type="number" id="offHour" min="0" max="23" class="form-control" placeholder="HH" style="flex:1;">
                        <span style="align-self:center; font-weight:700;">:</span>
                        <input type="number" id="offMinute" min="0" max="59" class="form-control" placeholder="MM" style="flex:1;">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6" id="sensorSettings" style="display:none;">
            <h6 style="color:#8b5cf6; margin-bottom:12px;"><i class="fas fa-sun"></i> Sensor Settings</h6>
            <div>
                <label style="font-size:0.85rem; color:var(--text-secondary);">Light Threshold (0-100)</label>
                <input type="range" id="sensorThreshold" min="0" max="100" class="form-range" style="margin-top:10px;" oninput="document.getElementById('thresholdValue').textContent = this.value">
                <div style="text-align:center; margin-top:8px;">
                    <span style="font-size:1.2rem; font-weight:700; color:#8b5cf6;" id="thresholdValue">35</span>
                    <span style="color:var(--text-secondary); font-size:0.85rem;"> / 100</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.75rem; color:var(--text-secondary); margin-top:5px;">
                    <span>Terang</span>
                    <span>Gelap</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SMART LAMP CONTROL -->
<div class="data-card mb-4">
    <h6><i class="fas fa-lightbulb"></i> Smart Lamp Control</h6>
    <div class="alert" id="modeAlert" style="background:rgba(59,130,246,0.1); border:1px solid #3b82f6; color:#3b82f6; font-size:0.85rem; margin-bottom:15px;">
        <i class="fas fa-info-circle"></i> Mode aktif: <strong id="currentModeLabel">Manual Control</strong>
    </div>
    <div class="row g-3" id="lampControlContainer">
        <div class="col-12 text-center text-muted">Loading...</div>
    </div>
</div>

<!-- QUICK ACTIONS -->
<div class="data-card" id="quickActions">
    <h6><i class="fas fa-bolt"></i> Quick Actions</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <button class="btn btn-success w-100" onclick="handleTurnOnAll()">
                <i class="fas fa-power-off"></i> Turn ON All Lamps
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-danger w-100" onclick="handleTurnOffAll()">
                <i class="fas fa-power-off"></i> Turn OFF All Lamps
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-warning w-100" onclick="handleSetAllBrightness(75)">
                <i class="fas fa-sliders-h"></i> Set All to 75%
            </button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .mode-card { transition: all 0.3s; }
    .mode-card:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(59,130,246,0.2); }
    .mode-card.active { border-color: #3b82f6 !important; background: rgba(59,130,246,0.1) !important; box-shadow: 0 0 20px rgba(59,130,246,0.3); }
    .lamp-card-disabled { opacity: 0.5; pointer-events: none; filter: grayscale(0.8); }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 20px;
    }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #4b5563;
        transition: 0.3s;
        border-radius: 20px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 16px; width: 16px;
        left: 2px; bottom: 2px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider { background-color: #3b82f6; }
    input:checked + .toggle-slider:before { transform: translateX(20px); }
</style>
@endpush

@push('scripts')
<script>
    let currentMode = 'manual';
    let autoSettings = {};
    const lampNames = {
        lamp_1: 'Street Lamp A - Jl. Sudirman',
        lamp_2: 'Street Lamp B - Jl. Thamrin',
        lamp_3: 'Street Lamp C - Jl. Gatot Subroto',
        lamp_4: 'Street Lamp D - Jl. Rasuna Said'
    };

    // ===== 1. LOAD SETTINGS (HANYA 1 FUNGSI, SUDAH DIPERBAIKI) =====
    function loadAutoSettings() {
        fetch('/api/auto-settings')
            .then(r => r.json())
            .then(data => {
                autoSettings = data;
                document.getElementById('onHour').value = data.schedule_on_hour || 17;
                document.getElementById('onMinute').value = data.schedule_on_minute || 30;
                document.getElementById('offHour').value = data.schedule_off_hour || 6;
                document.getElementById('offMinute').value = data.schedule_off_minute || 0;
                document.getElementById('sensorThreshold').value = data.sensor_threshold || 35;
                document.getElementById('thresholdValue').textContent = data.sensor_threshold || 35;
                
                // ✅ FIX: Update state toggle berdasarkan data server
                const scheduleToggle = document.getElementById('toggleAutoSchedule');
                if (scheduleToggle) scheduleToggle.checked = data.auto_schedule_enabled ?? true;
                
                const sensorToggle = document.getElementById('toggleAutoSensor');
                if (sensorToggle) sensorToggle.checked = data.auto_sensor_enabled ?? false;
            })
            .catch(err => console.error('Error loading settings:', err));
    }

    // ===== 2. TOGGLE HANDLERS =====
    function handleToggleAutoSchedule(enabled) {
        fetch('/api/auto-schedule/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ enabled: enabled })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                console.log('Auto Schedule is now:', enabled ? 'ON' : 'OFF');
                loadAutoSettings(); // Reload untuk memastikan UI sinkron
            }
        })
        .catch(err => {
            console.error('Error:', err);
            document.getElementById('toggleAutoSchedule').checked = !enabled; // Revert jika gagal
        });
    }

    function handleToggleAutoSensor(enabled) {
        fetch('/api/auto-sensor/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ enabled: enabled })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                console.log('Auto Sensor is now:', enabled ? 'ON' : 'OFF');
                loadAutoSettings(); // Reload untuk memastikan UI sinkron
            }
        })
        .catch(err => {
            console.error('Error:', err);
            document.getElementById('toggleAutoSensor').checked = !enabled; // Revert jika gagal
        });
    }

    // ===== 3. MODE SELECTION =====
    function handleSetMode(mode) {
        currentMode = mode;
        
        document.querySelectorAll('.mode-card').forEach(card => {
            card.classList.remove('active');
            card.style.borderColor = 'var(--border-color)';
            card.style.background = 'var(--bg-card)';
        });
        
        const activeCard = document.getElementById('mode-' + mode.replace('_', '-'));
        if (activeCard) {
            activeCard.classList.add('active');
            activeCard.style.borderColor = '#3b82f6';
            activeCard.style.background = 'rgba(59,130,246,0.1)';
        }

        document.getElementById('autoSettingsPanel').style.display = (mode !== 'manual') ? 'block' : 'none';
        document.getElementById('scheduleSettings').style.display = (mode === 'auto_schedule') ? 'block' : 'none';
        document.getElementById('sensorSettings').style.display = (mode === 'auto_sensor') ? 'block' : 'none';

        const modeLabels = {
            'manual': 'Manual Control - Anda mengontrol lampu secara langsung',
            'auto_schedule': `Auto Schedule - Lampu nyala ${autoSettings.schedule_on_hour || 17}:${String(autoSettings.schedule_on_minute || 30).padStart(2,'0')} - ${autoSettings.schedule_off_hour || 6}:${String(autoSettings.schedule_off_minute || 0).padStart(2,'0')}`,
            'auto_sensor': `Auto Sensor - Lampu nyala saat cahaya < ${autoSettings.sensor_threshold || 35}`
        };
        document.getElementById('currentModeLabel').textContent = modeLabels[mode];

        const lampContainer = document.getElementById('lampControlContainer');
        if (lampContainer) lampContainer.classList.toggle('lamp-card-disabled', mode !== 'manual');
        document.getElementById('quickActions').style.display = (mode === 'manual') ? 'block' : 'none';

        fetch('/api/control/mode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ mode: mode })
        })
        .then(r => r.json())
        .then(data => {
            loadAutoSettings();
            fetchLampData();
        })
        .catch(err => console.error('Error setting mode:', err));
    }

    // ===== 4. SAVE SETTINGS =====
    function handleSaveSettings() {
        const settings = {
            schedule_on_hour: parseInt(document.getElementById('onHour').value) || 17,
            schedule_on_minute: parseInt(document.getElementById('onMinute').value) || 30,
            schedule_off_hour: parseInt(document.getElementById('offHour').value) || 6,
            schedule_off_minute: parseInt(document.getElementById('offMinute').value) || 0,
            sensor_threshold: parseInt(document.getElementById('sensorThreshold').value) || 35
        };

        fetch('/api/auto-settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(settings)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                autoSettings = data.settings;
                alert('✅ Settings berhasil disimpan!');
                handleSetMode(currentMode);
            }
        })
        .catch(err => alert('❌ Gagal menyimpan settings'));
    }

    // ===== 5. LAMP CONTROL =====
    function handleToggleLamp(lampId, isOn) {
        if (currentMode !== 'manual') { alert('Switch to Manual mode to control lamps'); return; }
        fetch('/api/lamp/control', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ lamp_id: lampId, status: isOn ? 1 : 0 })
        }).then(() => fetchLampData());
    }

    function handleSetBrightness(lampId, brightness) {
        if (currentMode !== 'manual') { alert('Switch to Manual mode to control lamps'); return; }
        fetch('/api/lamp/control', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ lamp_id: lampId, brightness: parseInt(brightness) })
        }).then(() => fetchLampData());
    }

    function handleTurnOnAll() {
        if (currentMode !== 'manual') { alert('Switch to Manual mode'); return; }
        Object.keys(lampNames).forEach(lampId => handleToggleLamp(lampId, true));
    }

    function handleTurnOffAll() {
        if (currentMode !== 'manual') { alert('Switch to Manual mode'); return; }
        Object.keys(lampNames).forEach(lampId => handleToggleLamp(lampId, false));
    }

    function handleSetAllBrightness(value) {
        if (currentMode !== 'manual') { alert('Switch to Manual mode'); return; }
        Object.keys(lampNames).forEach(lampId => handleSetBrightness(lampId, value));
    }

    // ===== 6. RENDER & FETCH =====
    function renderLampControl(data) {
        const container = document.getElementById('lampControlContainer');
        if (!container) return;
        container.innerHTML = '';
        const isManual = currentMode === 'manual';

        Object.keys(lampNames).forEach((key) => {
            const lamp = data[key];
            const isOn = lamp.status === 1;
            const card = document.createElement('div');
            card.className = 'col-md-6 col-lg-3';
            card.innerHTML = `
                <div class="stat-card" style="border-left: 3px solid ${isOn ? '#f59e0b' : '#4b5563'}">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <h6 style="margin:0;">${lampNames[key]}</h6>
                        <label class="toggle-switch">
                            <input type="checkbox" ${isOn ? 'checked' : ''} ${!isManual ? 'disabled' : ''} onchange="handleToggleLamp('${key}', this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div style="margin-bottom:15px;">
                        <div style="display:flex; justify-content:space-between; font-size:0.85rem; color:var(--text-secondary); margin-bottom:5px;">
                            <span>Brightness</span><span>${lamp.brightness}%</span>
                        </div>
                        <input type="range" min="0" max="100" value="${lamp.brightness}" class="form-range" ${!isManual ? 'disabled' : ''} onchange="handleSetBrightness('${key}', this.value)">
                        <div style="text-align:center; font-size:0.75rem; color:var(--text-secondary); margin-top:5px;">${lamp.brightness}%</div>
                    </div>
                    <div style="font-size:0.85rem; color:var(--text-secondary);"><i class="fas fa-bolt"></i> Power: ${lamp.power}W</div>
                    ${!isManual ? '<div style="margin-top:10px; font-size:0.75rem; color:#f59e0b;"><i class="fas fa-robot"></i> Auto-controlled</div>' : ''}
                </div>
            `;
            container.appendChild(card);
        });
    }

    function fetchLampData() {
        fetch('/api/lamp')
            .then(r => r.json())
            .then(data => renderLampControl(data))
            .catch(err => console.error('Error fetching lamp data:', err));
    }

    // ===== 7. INIT =====
    document.addEventListener('DOMContentLoaded', () => {
        loadAutoSettings();
        fetchLampData();
    });

    setInterval(fetchLampData, 15000);
</script>
@endpush