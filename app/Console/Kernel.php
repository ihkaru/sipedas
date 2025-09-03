<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // ... (mungkin ada task lain)

        // Jalankan command ini setiap hari pada pukul 01:00 pagi
        $schedule->command('sipancong:auto-approve-ppk')->dailyAt('01:00');
    }

    // ...
}
