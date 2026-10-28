<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\AlertService;
use Illuminate\Console\Command;

class MarkOfflineDevicesCommand extends Command
{
    protected $signature = 'devices:mark-offline {--minutes=5 : Minutes without heartbeat before a device is offline}';

    protected $description = 'Mark ESP devices without a recent heartbeat as offline';

    public function handle(AlertService $alertService): int
    {
        $cutoff = now()->subMinutes((int) $this->option('minutes'));
        $devices = Device::whereIn('status', ['ONLINE', 'ERROR'])
            ->whereNotNull('last_seen')
            ->where('last_seen', '<', $cutoff)
            ->get();

        foreach ($devices as $device) {
            $device->update(['status' => 'OFFLINE']);
            $alertService->createDeviceOfflineAlert($device);
        }

        $this->info(sprintf('Marked %d ESP devices offline.', $devices->count()));

        return self::SUCCESS;
    }
}
