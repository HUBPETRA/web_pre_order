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

        // 1. [BARU] Alert untuk Admin (Setiap Pukul 07:00)
        // Mengecek apakah besok PO tutup TAPI tanggal pengambilan belum diisi.
        $schedule->command('po:alert-admin-pickup')
                 ->dailyAt('07:00')
                 ->withoutOverlapping(); // Mencegah command berjalan dobel jika server lemot

        // 2. Reminder Pengambilan Barang untuk Customer (Setiap Pukul 08:00)
        // Mengecek H-1 dari 'Pickup Date'.
        $schedule->command('po:send-reminders')
                 ->dailyAt('08:00')
                 ->withoutOverlapping();

        // 3. Reminder Target Kuota untuk Fungsio (Setiap Pukul 09:00)
        // Mengecek H-3 s/d H-1 penutupan PO jika target belum tercapai.
        $schedule->command('po:check-quotas')
                 ->dailyAt('09:00')
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