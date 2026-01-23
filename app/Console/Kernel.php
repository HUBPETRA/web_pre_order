<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // =========================================================================
        // JADWAL OTOMATISASI (CRON JOB)
        // =========================================================================

        // Jalankan setiap hari jam 08:00 WIB (Asia/Jakarta)
        $schedule->command('po:send-reminders')
                 ->timezone('Asia/Jakarta') // Pastikan ikut jam Indonesia
                 ->dailyAt('08:00')
                 ->withoutOverlapping();
    }
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}