<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IoTStateManager;

class ApplyAutoLogic extends Command
{
    protected $signature = 'iot:apply-auto-logic';
    protected $description = 'Apply auto logic to all IoT devices every minute';

    public function handle(IoTStateManager $stateManager)
    {
        $this->info('Applying auto logic...');
        $stateManager->applyAutoLogic();
        $this->info('Auto logic applied successfully!');
    }
}