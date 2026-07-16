<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Services\IoTStateManager;

class ListenMqtt extends Command
{
    protected $signature = 'mqtt:listen {--topic=smartcity/#}';
    protected $description = 'Listen to MQTT messages from ESP32 devices';

    private $stateManager;

    public function handle(IoTStateManager $stateManager)
    {
        $this->stateManager = $stateManager;

        $this->info('🚀 Starting MQTT Listener...');
        $this->info('📡 Broker: HiveMQ Cloud (Production Ready)');
        $this->info('📥 Topic: ' . $this->option('topic'));
        $this->info('Press Ctrl+C to stop');
        $this->newLine();

        $server = '06a04c55a35e4c5fb899a805966b04eb.s1.eu.hivemq.cloud';
        $port = 8883;
        $clientId = 'laravel_smartcity_' . uniqid();
        $username = 'smartcity_user';
        $password = '12345678';

        try {
            // 1. Initialize MQTT Client
            $mqtt = new MqttClient($server, $port, $clientId);

            // 2. Setup Connection Settings dengan TLS
            $connectionSettings = (new ConnectionSettings())
                ->setUsername($username)
                ->setPassword($password)
                ->setUseTls(true)
                ->setTlsSelfSignedAllowed(true);

            $this->info('🔐 Connecting with TLS...');
            $mqtt->connect($connectionSettings, true);
            $this->info('✅ Connected to HiveMQ Cloud broker');

            // 3. Subscribe (SINTAKS VERSI 2.x YANG BENAR)
            $topic = $this->option('topic');
            $this->info("📋 Subscribing to: {$topic}");

            // Subscribe dengan cara sederhana
$mqtt->subscribe($topic, function (string $topic, string $message) {
    $this->info("📨 MESSAGE RECEIVED!");
    $this->info("   Topic: {$topic}");
    $this->info("   Message: {$message}");
    
    // Panggil processMessage
    $this->processMessage($topic, $message);
}, 0);

$this->info("✅ Subscribed and waiting for messages...");

            $this->info('✅ Successfully subscribed!');
            $this->info('⏳ Waiting for messages... (Press Ctrl+C to stop)');
            $this->newLine();

            // 4. Loop (loop(true) sudah blocking, tidak perlu while(true))
            $mqtt->loop(true);

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    public function processMessage($topic, $message)
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $this->info("🎯 Processing message...");
        $this->info("📨 [$timestamp] Topic: $topic");
        $this->info("   Message: $message");

        $data = json_decode($message, true);
        $isJson = (json_last_error() === JSON_ERROR_NONE);

        if (!$isJson) {
            $this->warn("   ⚠️ Plain text message (not JSON)");
            $data = ['raw_value' => $message];
        }

        if (str_contains($topic, '/lamp/') || str_contains($topic, 'lamp') || str_contains($topic, 'led')) {
            $this->handleLampMessage($topic, $data);
        } elseif (str_contains($topic, '/waste/') || str_contains($topic, 'waste') || str_contains($topic, 'bin')) {
            $this->handleWasteMessage($topic, $data);
        } elseif (str_contains($topic, '/parking/') || str_contains($topic, 'parking') || str_contains($topic, 'slot')) {
            $this->handleParkingMessage($topic, $data);
        } else {
            $this->info("   📊 Received data: " . json_encode($data));
        }

        $this->newLine();
    }

    private function handleLampMessage($topic, $data)
    {
        $lampId = null;
        
        if (preg_match('/lamp_(\d+)/', $topic, $matches)) {
            $lampId = 'lamp_' . $matches[1];
        } elseif (isset($data['lamp_id'])) {
            $lampId = $data['lamp_id'];
        } else {
            $lampId = 'lamp_1';
        }

        $updateData = [];
        if (isset($data['status'])) $updateData['status'] = (int)$data['status'];
        if (isset($data['brightness'])) $updateData['brightness'] = (int)$data['brightness'];
        if (isset($data['power'])) $updateData['power'] = (float)$data['power'];

        if (!empty($updateData)) {
            $this->info("   💡 Updating lamp: $lampId");
            $this->stateManager->updateLamp($lampId, $updateData);
            $this->info("   ✅ Lamp updated successfully");
        } else {
            $this->warn("   ⚠️ No valid lamp data to update");
        }
    }

    private function handleWasteMessage($topic, $data)
    {
        $this->info("   🗑️ Received waste data: " . json_encode($data));
        
        $binId = null;
        if (preg_match('/bin[_ ]?(\d+)/i', $topic, $matches)) {
            $binId = 'bin_' . $matches[1];
        } elseif (isset($data['bin_id'])) {
            $binId = $data['bin_id'];
        }

        if (isset($data['level']) || isset($data['fill_level'])) {
            $level = $data['level'] ?? $data['fill_level'];
            $this->info("   📊 Bin $binId level: {$level}%");
        }

        $this->info("   ✅ Waste data processed");
    }

    private function handleParkingMessage($topic, $data)
    {
        $this->info("   🅿️ Received parking data: " . json_encode($data));
        
        $zone = null;
        if (preg_match('/zone_([ab])/i', $topic, $matches)) {
            $zone = 'zone_' . strtolower($matches[1]);
        } elseif (isset($data['zone'])) {
            $zone = $data['zone'];
        }

        if (isset($data['occupied']) && $zone) {
            $occupied = (int)$data['occupied'];
            $this->info("   📊 $zone occupied: $occupied");
        }

        $this->info("   ✅ Parking data processed");
    }
}