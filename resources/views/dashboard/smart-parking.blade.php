@extends('layouts.app')
@section('title', 'Smart Parking')
@section('page-title', 'Smart Parking Monitoring')

@section('content')
<!-- STAT CARDS -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="icon-box" style="background:rgba(59,130,246,0.15); color:#3b82f6;">
                <i class="fas fa-th"></i>
            </div>
            <div class="value" id="totalSlots">8</div>
            <div class="label">Total Slots</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="icon-box" style="background:rgba(239,68,68,0.15); color:#ef4444;">
                <i class="fas fa-car"></i>
            </div>
            <div class="value" id="occupiedSlots">-</div>
            <div class="label">Occupied</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div class="icon-box" style="background:rgba(16,185,129,0.15); color:#10b981;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="value" id="availableSlots">-</div>
            <div class="label">Available</div>
        </div>
    </div>
</div>

<!-- ZONE CARDS -->
<div class="row g-4 mb-4" id="zoneGrid">
    <!-- Zones akan di-render oleh JavaScript -->
</div>

<!-- PARKING VISUAL MAP -->
<div class="data-card">
    <h6><i class="fas fa-map"></i> PARKING LOT VISUAL MAP</h6>
    <div style="display:flex; gap:20px; margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem;">
            <div style="width:18px; height:18px; background:rgba(16,185,129,0.3); border:2px solid #10b981; border-radius:6px;"></div>
            <span style="color:var(--text-secondary);">Available</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem;">
            <div style="width:18px; height:18px; background:rgba(239,68,68,0.3); border:2px solid #ef4444; border-radius:6px;"></div>
            <span style="color:var(--text-secondary);">Occupied</span>
        </div>
    </div>
    <div id="parkingMap" style="display:flex; flex-wrap:wrap; gap:12px; justify-content:center; padding:20px; background:rgba(15,23,42,0.5); border-radius:12px;">
        <!-- Slots akan di-render oleh JavaScript -->
    </div>
</div>
@endsection

@push('scripts')
<script>
    const zoneNames = {
        'zone_a': 'ZONE A',
        'zone_b': 'ZONE B'
    };
    
    const zoneColors = {
        'zone_a': '#3b82f6',
        'zone_b': '#f59e0b'
    };

    const zoneSlots = {
        'zone_a': [1, 2, 3, 4],
        'zone_b': [5, 6, 7, 8]
    };

    function renderZones(data) {
        const grid = document.getElementById('zoneGrid');
        grid.innerHTML = '';

        Object.keys(data.zones).forEach(key => {
            const zone = data.zones[key];
            const available = zone.total - zone.occupied;
            const pct = Math.round((zone.occupied / zone.total) * 100);
            const color = zoneColors[key];

            grid.innerHTML += `
                <div class="col-md-6">
                    <div class="data-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h6 style="margin:0; color:${color}; font-size:1rem; font-weight:700;">${zoneNames[key]}</h6>
                            <span style="font-size:0.85rem; color:var(--text-secondary);">${zone.total} slots</span>
                        </div>
                        <div style="font-size:2.5rem; font-weight:700; margin-bottom:5px;">${available}</div>
                        <div style="color:var(--text-secondary); font-size:0.9rem; margin-bottom:15px;">Available spots</div>
                        <div style="margin-bottom:8px;">
                            <div style="display:flex; justify-content:space-between; font-size:0.8rem; color:var(--text-secondary); margin-bottom:5px;">
                                <span>Occupancy</span>
                                <span>${pct}%</span>
                            </div>
                            <div class="waste-bar" style="height:10px;">
                                <div class="waste-bar-fill" style="width:${pct}%; background:${color}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
    }

    function renderParkingMap(total, occupied) {
        const map = document.getElementById('parkingMap');
        map.innerHTML = '';
        
        // Generate random occupied slots
        const occupiedSet = new Set();
        while (occupiedSet.size < occupied) {
            occupiedSet.add(Math.floor(Math.random() * total) + 1);
        }

        for (let i = 1; i <= total; i++) {
            const isOccupied = occupiedSet.has(i);
            const slot = document.createElement('div');
            slot.className = `parking-slot ${isOccupied ? 'occupied' : 'available'}`;
            slot.style.cssText = `
                width: 50px; 
                height: 50px; 
                border-radius: 8px; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                font-size: 0.9rem; 
                font-weight: 700;
                border: 2px solid ${isOccupied ? '#ef4444' : '#10b981'};
                background: ${isOccupied ? 'rgba(239,68,68,0.2)' : 'rgba(16,185,129,0.2)'};
                color: ${isOccupied ? '#ef4444' : '#10b981'};
                transition: all 0.3s;
                cursor: pointer;
            `;
            slot.textContent = i;
            slot.onmouseover = function() {
                this.style.transform = 'scale(1.1)';
            };
            slot.onmouseout = function() {
                this.style.transform = 'scale(1)';
            };
            map.appendChild(slot);
        }
    }

    function fetchParkingData() {
        fetch('/api/parking')
            .then(r => r.json())
            .then(data => {
                document.getElementById('totalSlots').textContent = data.total_slots;
                document.getElementById('occupiedSlots').textContent = data.occupied;
                document.getElementById('availableSlots').textContent = data.available;
                renderZones(data);
                renderParkingMap(data.total_slots, data.occupied);
            });
    }

    setInterval(fetchParkingData, 15000);
    fetchParkingData();
</script>
@endpush