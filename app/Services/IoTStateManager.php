<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class IoTStateManager
{
    private $stateFile = 'private/iot_state/central_state.json';
    private $state;

    public function __construct()
    {
        $this->loadState();
    }

    private function loadState()
    {
        if (Storage::exists($this->stateFile)) {
            $this->state = json_decode(Storage::get($this->stateFile), true);
            
            // ✅ PERBAIKAN: Pastikan struktur lamps dan waste ada (jika file JSON corrupt)
            if (!isset($this->state['lamps']) || !is_array($this->state['lamps'])) {
                $this->state['lamps'] = $this->getDefaultState()['lamps'];
                $this->saveState();
            }
            if (!isset($this->state['waste']) || !is_array($this->state['waste'])) {
                $this->state['waste'] = $this->getDefaultState()['waste'];
                $this->saveState();
            }
        } else {
            $this->state = $this->getDefaultState();
            $this->saveState();
        }

        // ====== STRUKTUR CLEANER ======
        if (
            !isset($this->state['parking']) ||
            $this->state['parking']['total_slots'] != 8 ||
            isset($this->state['parking']['zones']['zone_c']) ||
            !isset($this->state['parking']['zones']['zone_b'])
        ) {
            $this->state['parking'] = [
                'total_slots' => 8,
                'occupied' => 4,
                'zones' => [
                    'zone_a' => ['total' => 4, 'occupied' => 2],
                    'zone_b' => ['total' => 4, 'occupied' => 2],
                ]
            ];
            $this->saveState();
        }

        // Pastikan auto_settings ada dengan default value
        if (!isset($this->state['auto_settings'])) {
            $this->state['auto_settings'] = $this->getDefaultState()['auto_settings'];
            $this->saveState();
        }

        // Pastikan flag ON/OFF ada
        if (!isset($this->state['auto_settings']['auto_schedule_enabled'])) {
            $this->state['auto_settings']['auto_schedule_enabled'] = true;
            $this->saveState();
        }
        if (!isset($this->state['auto_settings']['auto_sensor_enabled'])) {
            $this->state['auto_settings']['auto_sensor_enabled'] = false;
            $this->saveState();
        }
    }

    private function getDefaultState()
    {
        return [
            'updated_at' => now()->toIso8601String(),
            'lamps' => [
                'lamp_1' => ['status' => 1, 'brightness' => 80, 'power' => 4.8, 'name' => 'Jl. Sudirman'],
                'lamp_2' => ['status' => 0, 'brightness' => 51, 'power' => 3.1, 'name' => 'Jl. Thamrin'],
                'lamp_3' => ['status' => 1, 'brightness' => 77, 'power' => 4.6, 'name' => 'Jl. Gatot Subroto'],
                'lamp_4' => ['status' => 0, 'brightness' => 63, 'power' => 3.8, 'name' => 'Jl. Rasuna Said'],
            ],
            'waste' => [
                'bin_1' => ['level' => 45, 'location' => 'Jl. Sudirman'],
                'bin_2' => ['level' => 60, 'location' => 'Jl. Thamrin'],
                'bin_3' => ['level' => 30, 'location' => 'Jl. Gatot Subroto'],
                'bin_4' => ['level' => 70, 'location' => 'Jl. Rasuna Said'],
            ],
            'parking' => [
                'total_slots' => 8,
                'occupied' => 4,
                'zones' => [
                    'zone_a' => ['total' => 4, 'occupied' => 2],
                    'zone_b' => ['total' => 4, 'occupied' => 2],
                ]
            ],
            'control_mode' => 'manual',
            'sensor_light_level' => 65,

            // ===== AUTO MODE SETTINGS (CUSTOMIZABLE) =====
            'auto_settings' => [
                'schedule_on_hour' => 17,
                'schedule_on_minute' => 30,
                'schedule_off_hour' => 6,
                'schedule_off_minute' => 0,
                'sensor_threshold' => 35,
                'auto_schedule_enabled' => true,
                'auto_sensor_enabled' => false,
            ],
        ];
    }

    public function updateAutoSettings($settings)
    {
        if (!isset($this->state['auto_settings'])) {
            $this->state['auto_settings'] = $this->getDefaultState()['auto_settings'];
        }
        
        foreach ($settings as $key => $value) {
            if (isset($this->state['auto_settings'][$key])) {
                $this->state['auto_settings'][$key] = (int)$value;
            }
        }
        
        $this->saveState();
    }

    public function getAutoSettings()
    {
        if (!isset($this->state['auto_settings'])) {
            $this->state['auto_settings'] = $this->getDefaultState()['auto_settings'];
        }
        return $this->state['auto_settings'];
    }

    public function toggleAutoSchedule($enabled)
    {
        if (!isset($this->state['auto_settings'])) {
            $this->state['auto_settings'] = $this->getDefaultState()['auto_settings'];
        }
        
        $this->state['auto_settings']['auto_schedule_enabled'] = (bool)$enabled;
        $this->saveState();
        
        return $this->state['auto_settings']['auto_schedule_enabled'];
    }

    public function toggleAutoSensor($enabled)
    {
        if (!isset($this->state['auto_settings'])) {
            $this->state['auto_settings'] = $this->getDefaultState()['auto_settings'];
        }
        
        $this->state['auto_settings']['auto_sensor_enabled'] = (bool)$enabled;
        $this->saveState();
        
        return $this->state['auto_settings']['auto_sensor_enabled'];
    }

    public function saveState()
    {
        $this->state['updated_at'] = now()->toIso8601String();
        Storage::put($this->stateFile, json_encode($this->state, JSON_PRETTY_PRINT));
    }

    public function getState()
    {
        return $this->state;
    }

    public function updateLamp($lampId, $data)
    {
        if (isset($this->state['lamps'][$lampId])) {
            foreach ($data as $key => $value) {
                if (isset($this->state['lamps'][$lampId][$key])) {
                    $this->state['lamps'][$lampId][$key] = $value;
                }
            }
            
            $basePower = ($this->state['lamps'][$lampId]['brightness'] / 100) * 6;
            $variation = rand(-3, 3) / 10;
            $this->state['lamps'][$lampId]['power'] = round($basePower + $variation, 1);
            
            $this->saveState();
        }
    }

    public function setControlMode($mode)
    {
        $this->state['control_mode'] = $mode;
        $this->saveState();
    }

    public function setSensorLightLevel($level)
    {
        $this->state['sensor_light_level'] = $level;
        $this->saveState();
    }

    public function applyAutoLogic()
    {
        $currentHour = (int)now()->format('H');
        $currentMinute = (int)now()->format('i');
        $currentTime = $currentHour * 60 + $currentMinute;

        $timeBasedLight = $this->calculateNaturalLight($currentHour + ($currentMinute / 60));
        $weatherVariation = rand(-3, 3);
        $this->state['sensor_light_level'] = max(0, min(100, $timeBasedLight + $weatherVariation));

        $settings = $this->getAutoSettings();
        $onTimeMinutes = ($settings['schedule_on_hour'] * 60) + $settings['schedule_on_minute'];
        $offTimeMinutes = ($settings['schedule_off_hour'] * 60) + $settings['schedule_off_minute'];

        if ($this->state['control_mode'] === 'auto_schedule' && ($settings['auto_schedule_enabled'] ?? true)) {
            $isNightTime = false;
            if ($onTimeMinutes > $offTimeMinutes) {
                $isNightTime = ($currentTime >= $onTimeMinutes) || ($currentTime < $offTimeMinutes);
            } elseif ($onTimeMinutes < $offTimeMinutes) {
                $isNightTime = ($currentTime >= $onTimeMinutes) && ($currentTime < $offTimeMinutes);
            } else {
                $isNightTime = ($currentTime == $onTimeMinutes);
            }
            
            foreach ($this->state['lamps'] as &$lamp) {
                $lamp['status'] = $isNightTime ? 1 : 0;
                $lamp['brightness'] = $isNightTime ? 85 : 0;
                $lamp['power'] = round(($lamp['brightness'] / 100) * 6, 1);
            }
            
        } elseif ($this->state['control_mode'] === 'auto_sensor' && ($settings['auto_sensor_enabled'] ?? false)) {
            $isDark = $this->state['sensor_light_level'] < $settings['sensor_threshold'];
            
            foreach ($this->state['lamps'] as &$lamp) {
                $lamp['status'] = $isDark ? 1 : 0;
                $lamp['brightness'] = $isDark ? 90 : 0;
                $lamp['power'] = round(($lamp['brightness'] / 100) * 6, 1);
            }
        }

        foreach ($this->state['waste'] as &$bin) {
            $increase = rand(0, 1);
            $bin['level'] = min(100, $bin['level'] + $increase);
            if ($bin['level'] >= 95 && rand(1, 100) <= 10) {
                $bin['level'] = rand(10, 20);
            }
        }

        $change = rand(-1, 1);
        $this->state['parking']['occupied'] = max(1, min(7, $this->state['parking']['occupied'] + $change));
        unset($this->state['parking']['zones']['zone_c']);
        
        $occupied = $this->state['parking']['occupied'];
        $this->state['parking']['zones']['zone_a']['occupied'] = round($occupied / 2);
        $this->state['parking']['zones']['zone_b']['occupied'] = $occupied - round($occupied / 2);

        $this->saveState();
    }

    private function calculateNaturalLight($hour)
    {
        if ($hour >= 6 && $hour <= 12) {
            return (($hour - 6) / 6) * 100;
        } elseif ($hour > 12 && $hour <= 18) {
            return ((18 - $hour) / 6) * 100;
        } else {
            return rand(0, 5);
        }
    }

    public function getLampData()
    {
        // ✅ PERBAIKAN: Fallback jika data null
        $lamps = $this->state['lamps'] ?? $this->getDefaultState()['lamps'];
        
        return [
            'lamp_1' => $lamps['lamp_1'] ?? [],
            'lamp_2' => $lamps['lamp_2'] ?? [],
            'lamp_3' => $lamps['lamp_3'] ?? [],
            'lamp_4' => $lamps['lamp_4'] ?? [],
            'sensor_light_level' => round($this->state['sensor_light_level'] ?? 0, 1),
            'control_mode' => $this->state['control_mode'] ?? 'manual',
            'auto_settings' => $this->getAutoSettings(),
            'timestamp' => now()->format('H:i:s'),
        ];
    }

    public function getWasteData()
    {
        $waste = $this->state['waste'] ?? $this->getDefaultState()['waste'];
        return [
            'bin_1' => ['level' => $waste['bin_1']['level'], 'status' => $waste['bin_1']['level'] > 80 ? 'Penuh' : 'Normal', 'location' => $waste['bin_1']['location']],
            'bin_2' => ['level' => $waste['bin_2']['level'], 'status' => $waste['bin_2']['level'] > 80 ? 'Penuh' : 'Normal', 'location' => $waste['bin_2']['location']],
            'bin_3' => ['level' => $waste['bin_3']['level'], 'status' => $waste['bin_3']['level'] > 80 ? 'Penuh' : 'Normal', 'location' => $waste['bin_3']['location']],
            'bin_4' => ['level' => $waste['bin_4']['level'], 'status' => $waste['bin_4']['level'] > 80 ? 'Penuh' : 'Normal', 'location' => $waste['bin_4']['location']],
            'timestamp' => now()->format('H:i:s'),
        ];
    }

    public function getParkingData()
    {
        return [
            'total_slots' => $this->state['parking']['total_slots'],
            'occupied' => $this->state['parking']['occupied'],
            'available' => $this->state['parking']['total_slots'] - $this->state['parking']['occupied'],
            'zones' => $this->state['parking']['zones'],
            'timestamp' => now()->format('H:i:s'),
        ];
    }

    public function getDashboardSummary()
    {
        $lamps = $this->state['lamps'] ?? $this->getDefaultState()['lamps'];
        $waste = $this->state['waste'] ?? $this->getDefaultState()['waste'];
        $parking = $this->state['parking'] ?? $this->getDefaultState()['parking'];

        $activeLamps = count(array_filter($lamps, fn($l) => $l['status'] === 1));
        $totalBins = count($waste);
        $parkingAvailable = $parking['total_slots'] - $parking['occupied'];
        $alerts = count(array_filter($waste, fn($b) => $b['level'] > 80));

        return [
            'active_lamps' => $activeLamps,
            'total_bins' => $totalBins,
            'parking_available' => $parkingAvailable,
            'alerts' => $alerts,
            'avg_lamp_brightness' => round(array_sum(array_column($lamps, 'brightness')) / 4),
            'avg_waste_level' => round(array_sum(array_column($waste, 'level')) / 4),
        ];
    }
}