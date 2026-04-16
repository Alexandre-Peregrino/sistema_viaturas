<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('rotaweb:sync-unidades')
            ->dailyAt('02:30')
            ->withoutOverlapping()
            ->onSuccess(fn() => info('rotaweb: sync OK'))
            ->onFailure(fn() => info('rotaweb: sync FAIL'));
    }


    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        $console = base_path('routes/console.php');
        if (file_exists($console)) {
            require $console;
        }
    }
}
