<?php

namespace App\Console;

use App\Enums\Channel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        foreach(Channel::cases() as $channel) {
            $schedule->call(fn() => $channel->epg())->hourly();
        }
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
