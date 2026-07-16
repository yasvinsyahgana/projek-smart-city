<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart City IoT - @yield('title', 'Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --bg-dark: #0a0e1a;
            --bg-card: #111827;
            --bg-sidebar: #0d1321;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-yellow: #f59e0b;
            --accent-red: #ef4444;
            --text-primary: #e2e8f0;
            --text-secondary: #94a3b8;
            --border-color: #1e293b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            text-align: center;
        }

        .sidebar-header h4 {
            color: var(--accent-blue);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .sidebar-header small {
            color: var(--text-secondary);
            font-size: 0.75rem;
        }

        .sidebar-nav {
            flex: 1;
            padding: 15px 0;
            overflow-y: auto;
        }

        .nav-label {
            padding: 10px 20px 5px;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-secondary);
            letter-spacing: 1px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            margin: 2px 0;
        }

        .nav-item:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: rgba(59, 130, 246, 0.15);
            color: var(--accent-blue);
            border-left-color: var(--accent-blue);
        }

        .nav-item i {
            width: 24px;
            margin-right: 12px;
            font-size: 1rem;
            text-align: center;
        }

        /* ===== ESP CONNECT (Bottom Sidebar) ===== */
        .esp-connect {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            background: rgba(0,0,0,0.2);
        }

        .esp-status {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
        }

        .esp-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: var(--accent-green);
            animation: pulse 2s infinite;
        }

        .esp-dot.offline { background: var(--accent-red); animation: none; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        .esp-info { color: var(--text-secondary); font-size: 0.7rem; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: var(--bg-sidebar);
            border-bottom: 1px solid var(--border-color);
        }

        .top-bar h5 { font-weight: 600; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 35px; height: 35px;
            border-radius: 50%;
            background: var(--accent-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .content-area { padding: 25px 30px; }

        /* ===== CARDS ===== */
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .stat-card .icon-box {
            width: 48px; height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 12px;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .stat-card .label {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .data-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .data-card h6 {
            color: var(--text-secondary);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }

        /* ===== TOGGLE SWITCH ===== */
        .toggle-switch {
            position: relative;
            width: 50px; height: 26px;
            cursor: pointer;
        }

        .toggle-switch input { display: none; }

        .toggle-slider {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #374151;
            border-radius: 26px;
            transition: 0.3s;
        }

        .toggle-slider:before {
            content: "";
            position: absolute;
            height: 20px; width: 20px;
            left: 3px; bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s;
        }

        .toggle-switch input:checked + .toggle-slider {
            background: var(--accent-blue);
        }

        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        /* ===== PROGRESS BAR ===== */
        .waste-bar {
            height: 8px;
            background: #1e293b;
            border-radius: 4px;
            overflow: hidden;
        }

        .waste-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* ===== PARKING GRID ===== */
        .parking-slot {
            width: 40px; height: 50px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .parking-slot.available {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid var(--accent-green);
            color: var(--accent-green);
        }

        .parking-slot.occupied {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid var(--accent-red);
            color: var(--accent-red);
        }

        /* ===== MQTT STATUS BAR ===== */
        .mqtt-bar {
            position: fixed;
            bottom: 0;
            left: var(--sidebar-width);
            right: 0;
            background: var(--bg-sidebar);
            border-top: 1px solid var(--border-color);
            padding: 8px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: var(--text-secondary);
            z-index: 999;
        }

        .mqtt-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }

        .mqtt-dot.connected { background: var(--accent-green); }
        .mqtt-dot.disconnected { background: var(--accent-red); }

        .content-area { padding-bottom: 60px; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mqtt-bar { left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="fas fa-city"></i> SmartCity</h4>
            <small>Control & Monitoring</small>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Main Menu</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <div class="nav-label">IoT Devices</div>
            <a href="{{ route('smart-lamp') }}" class="nav-item {{ request()->routeIs('smart-lamp') ? 'active' : '' }}">
                <i class="fas fa-lightbulb"></i> Smart Lamp
            </a>
            <a href="{{ route('smart-waste') }}" class="nav-item {{ request()->routeIs('smart-waste') ? 'active' : '' }}">
                <i class="fas fa-trash-alt"></i> Smart Waste
            </a>
            <a href="{{ route('smart-parking') }}" class="nav-item {{ request()->routeIs('smart-parking') ? 'active' : '' }}">
                <i class="fas fa-car"></i> Smart Parking
            </a>
                        <div class="nav-label">Control</div>
            <a href="{{ route('control') }}" class="nav-item {{ request()->routeIs('control') ? 'active' : '' }}">
                <i class="fas fa-sliders-h"></i> Control Center
            </a>

            <div class="nav-label">System</div>
            <a href="#" class="nav-item" id="mqttConnectBtn">
                <i class="fas fa-wifi"></i> MQTT Broker
            </a>
        </nav>

        <!-- Logout Button -->
<div style="margin-top: auto; padding: 15px; border-top: 1px solid var(--border-color);">
    <form method="POST" action="/logout" style="margin: 0;">
        @csrf
        <button type="submit" class="btn btn-danger w-100" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </button>
    </form>
    <div class="text-center mt-2" style="font-size: 0.75rem; color: var(--text-secondary);">
        <i class="fas fa-user-shield"></i> {{ session('admin_username', 'Admin') }}
    </div>
</div>

        <!-- ESP CONNECT (Pojok Kiri Bawah Sidebar) -->
        <div class="esp-connect">
            <div class="esp-status">
                <div class="esp-dot" id="espDot"></div>
                <div>
                    <div style="font-weight:600; font-size:0.85rem;">ESP32 Connected</div>
                    <div class="esp-info" id="espInfo">Node: ESP-SMARTCITY-01</div>
                </div>
            </div>
            <div style="margin-top:8px;">
                <small style="color:var(--text-secondary); font-size:0.7rem;">
                    IP: 192.168.1.100 | RSSI: -45dBm
                </small>
            </div>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div style="display:flex; align-items:center; gap:15px;">
                <button class="btn btn-sm d-md-none" onclick="document.getElementById('sidebar').classList.toggle('show')" style="color:var(--text-primary);">
                    <i class="fas fa-bars"></i>
                </button>
                <h5>@yield('page-title', 'Dashboard')</h5>
            </div>
            <div class="user-info">
    <span style="font-size:0.85rem;">Admin</span>
    <div class="user-avatar">AD</div>
</div>
        </div>

        <!-- Page Content -->
        <div class="content-area">
            @yield('content')
        </div>
    </div>

    <!-- ===== MQTT STATUS BAR ===== -->
    <div class="mqtt-bar">
        <div>
            <span class="mqtt-dot disconnected" id="mqttDot"></span>
            MQTT: <span id="mqttStatus">Disconnected</span>
        </div>
        <div id="mqttLastMsg">Last message: -</div>
        <div id="clockDisplay"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ===== CLOCK =====
        function updateClock() {
            const now = new Date();
            document.getElementById('clockDisplay').textContent = now.toLocaleString('id-ID');
        }
        setInterval(updateClock, 1000);
        updateClock();

        // ===== MQTT CLIENT =====
        let mqttClient = null;
        const MQTT_BROKER = "{{ config('app.mqtt_broker', 'ws://broker.hivemq.com:8000/mqtt') }}";
        const MQTT_TOPIC = "smartcity/#";

        function connectMQTT() {
            if (typeof mqtt === 'undefined') return;

            const clientId = 'web_' + Math.random().toString(16).substr(2, 8);
            mqttClient = mqtt.connect(MQTT_BROKER, { clientId: clientId });

            mqttClient.on('connect', () => {
                document.getElementById('mqttDot').className = 'mqtt-dot connected';
                document.getElementById('mqttStatus').textContent = 'Connected';
                mqttClient.subscribe(MQTT_TOPIC);
            });

            mqttClient.on('message', (topic, message) => {
                document.getElementById('mqttLastMsg').textContent =
                    'Last: ' + topic + ' → ' + message.toString().substring(0, 50);
                // Handle incoming MQTT messages here
                handleMQTTMessage(topic, message.toString());
            });

            mqttClient.on('error', () => {
                document.getElementById('mqttDot').className = 'mqtt-dot disconnected';
                document.getElementById('mqttStatus').textContent = 'Error';
            });

            mqttClient.on('close', () => {
                document.getElementById('mqttDot').className = 'mqtt-dot disconnected';
                document.getElementById('mqttStatus').textContent = 'Disconnected';
            });
        }

        function publishMQTT(topic, message) {
            if (mqttClient && mqttClient.connected) {
                mqttClient.publish(topic, message);
            }
        }

        function handleMQTTMessage(topic, message) {
            // Override this function in specific pages
        }

        // Auto connect MQTT on page load
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof mqtt !== 'undefined') {
                connectMQTT();
            }
        });

        document.getElementById('mqttConnectBtn')?.addEventListener('click', (e) => {
            e.preventDefault();
            if (mqttClient && mqttClient.connected) {
                mqttClient.end();
            } else {
                connectMQTT();
            }
        });
    </script>
        <!-- ===== GLOBAL WASTE NOTIFICATION ===== -->
    <div id="globalWasteNotification" style="position:fixed; top:20px; right:20px; z-index:9999; display:flex; flex-direction:column; gap:10px; max-width:350px; pointer-events:none;"></div>

    <!-- ===== GLOBAL NOTIFICATION SCRIPT ===== -->
    <script>
    // Track notifikasi waste global (di semua halaman)
    let globalWasteNotified = {};
    let lastWasteCheck = 0;

    function showGlobalWasteNotification(binName, location, level) {
        const container = document.getElementById('globalWasteNotification');
        
        // Buat toast
        const toast = document.createElement('div');
        toast.style.cssText = `
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #ef4444;
            border-left: 4px solid #ef4444;
            border-radius: 10px;
            padding: 15px 20px;
            color: #e2e8f0;
            box-shadow: 0 10px 40px rgba(239, 68, 68, 0.3);
            animation: slideInRight 0.4s ease-out;
            pointer-events: auto;
            position: relative;
            overflow: hidden;
        `;
        
        toast.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                <div style="width:32px; height:32px; border-radius:50%; background:rgba(239,68,68,0.2); display:flex; align-items:center; justify-content:center; font-size:1rem;">
                    🗑️
                </div>
                <div style="font-weight:700; font-size:0.95rem; color:#ef4444;">
                    ⚠️ ${binName} PENUH!
                </div>
            </div>
            <div style="font-size:0.85rem; color:#94a3b8; margin-left:42px;">
                <strong>${location}</strong> mencapai <strong>${level}%</strong>. Segera lakukan pengangkutan!
            </div>
        `;
        
        container.appendChild(toast);

        // Auto remove setelah 6 detik
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOutRight 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, 6000);
    }

    function checkGlobalWasteAlerts() {
        // Cek setiap 30 detik (tidak terlalu sering)
        const now = Date.now();
        if (now - lastWasteCheck < 30000) return;
        lastWasteCheck = now;

        fetch('/api/waste')
            .then(r => r.json())
            .then(data => {
                const binNames = {
                    'bin_1': 'Bin A',
                    'bin_2': 'Bin B',
                    'bin_3': 'Bin C',
                    'bin_4': 'Bin D'
                };

                Object.keys(data).forEach(key => {
                    const bin = data[key];
                    const binName = binNames[key] || key;
                    const isFull = bin.level > 80;

                    // Tampilkan notifikasi jika penuh dan belum dinotif
                    if (isFull && globalWasteNotified[key] !== bin.level) {
                        globalWasteNotified[key] = bin.level;
                        showGlobalWasteNotification(binName, bin.location, bin.level);
                        
                        // Tambahkan ke history di localStorage
                        addToGlobalHistory(binName, bin.location, bin.level);
                    }

                    // Reset notif jika level turun
                    if (!isFull && globalWasteNotified[key]) {
                        delete globalWasteNotified[key];
                    }
                });
            })
            .catch(err => console.log('Waste check error:', err));
    }

    function addToGlobalHistory(binName, location, level) {
        const now = new Date();
        const timeStr = now.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        let history = JSON.parse(localStorage.getItem('globalWasteAlertHistory') || '[]');
        history.unshift({
            binName,
            location,
            level,
            time: timeStr,
            timestamp: now.getTime()
        });

        // Batasi 100 riwayat
        if (history.length > 100) history = history.slice(0, 100);
        localStorage.setItem('globalWasteAlertHistory', JSON.stringify(history));
    }

    // Jalankan check setiap 30 detik
    setInterval(checkGlobalWasteAlerts, 30000);
    
    // Check pertama kali saat load
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(checkGlobalWasteAlerts, 2000); // Delay 2 detik agar tidak mengganggu load
    });
    </script>
    @stack('scripts')
</body>
</html>