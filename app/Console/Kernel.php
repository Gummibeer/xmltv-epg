<?php

namespace App\Console;

use App\Console\Commands\LoadEpgsCommand;
use App\Console\Commands\LoadJoynCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(LoadEpgsCommand::class)->hourly();
        $schedule->command(LoadJoynCommand::class)->hourly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
