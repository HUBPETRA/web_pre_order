<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Models\Order;
use App\Models\BatchQuota; // Pastikan model ini ada
use Illuminate\Support\Facades\Mail;
use App\Mail\POReminderMail;
use App\Mail\QuotaReminderMail;
use App\Mail\AdminAlertMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendDailyReminders extends Command
{
    protected $signature = 'po:send-reminders';
    protected $description = 'Kirim email otomatis untuk User, Fungsio, dan Admin';

    public function handle()
    {
        $today = Carbon::now();
        $tomorrow = Carbon::tomorrow();

        $this->info("Memulai proses pengecekan jadwal...");

        // 1. Ambil Batch yang SEDANG AKTIF
        $activeBatches = Batch::where('is_active', true)->get();

        foreach ($activeBatches as $batch) {
            
            // --- FITUR A: REMINDER USER (H-1) ---
            if ($batch->pickup_date && !$batch->is_reminder_sent) {
                $pickupDate = Carbon::parse($batch->pickup_date);
                
                // Cek apakah BESOK adalah hari pengambilan
                if ($pickupDate->isSameDay($tomorrow)) {
                    $orders = $batch->orders()->where('status', 'Lunas')->whereNotNull('customer_email')->get();
                    
                    $this->info(" mengirim PO Reminder untuk batch: {$batch->name}");
                    
                    foreach ($orders as $order) {
                        try {
                            Mail::to($order->customer_email)->send(new POReminderMail($order));
                        } catch (\Exception $e) {
                            Log::error("Gagal kirim PO Reminder ke {$order->customer_email}");
                        }
                    }

                    // Tandai batch ini sudah dikirimi reminder, biar gak dobel
                    $batch->update(['is_reminder_sent' => true]);
                }
            }

            // --- FITUR B: REMINDER FUNGSIO (H-3 S/D HARI H) ---
            $closeDate = Carbon::parse($batch->close_date)->startOfDay(); 
            $todayStart = $today->copy()->startOfDay(); 

            // Logika:
            // 1. $todayStart->lte($closeDate): Hari ini belum lewat tanggal tutup (Less Than or Equal)
            // 2. diffInDays <= 3: Selisihnya 3 hari, 2 hari, 1 hari, atau 0 hari (Hari H)
            if ($todayStart->lessThanOrEqualTo($closeDate) && $todayStart->diffInDays($closeDate) <= 3) {
                 
                 // Hitung sisa hari untuk pesan log (Opsional, agar log rapi)
                 $daysLeft = $todayStart->diffInDays($closeDate);
                 $statusHari = $daysLeft == 0 ? "HARI INI (D-Day)" : "H-{$daysLeft}";
                 
                 $this->info(" mengirim Quota Reminder ({$statusHari}) untuk batch: {$batch->name}");

                 foreach ($batch->quotas as $quota) {
                    // Hitung realisasi saat ini
                    $sold = Order::where('batch_id', $batch->id)
                                 ->where('fungsio_id', $quota->fungsio_id)
                                 ->where('status', '!=', 'Ditolak')
                                 ->withSum('orderItems', 'quantity')
                                 ->get()
                                 ->sum('order_items_sum_quantity');
                    
                    // Jika masih kurang dari target, kirim email
                    if ($sold < $quota->target_qty) {
                        $quota->achieved_qty = $sold; // Inject data sementara untuk view
                        try {
                            // Kirim email
                            Mail::to($quota->fungsio->email)->send(new QuotaReminderMail($quota, $batch->name));
                        } catch (\Exception $e) {
                            Log::error("Gagal kirim Quota Reminder ke {$quota->fungsio->email}");
                        }
                    }
                 }
            }

            // --- FITUR C: ADMIN ALERT (HARIAN) ---
            $pendingCount = $batch->orders()->where('status', 'Menunggu Verifikasi')->count();
            if ($pendingCount > 0) {
                // Email admin hardcode, atau bisa ambil dari tabel users role admin
                $adminEmail = 'admin@pogenta.com'; // GANTI DENGAN EMAIL ADMIN ASLI
                try {
                    Mail::to($adminEmail)->send(new AdminAlertMail($pendingCount, $batch->name));
                    $this->info(" mengirim Admin Alert: {$pendingCount} pending orders");
                } catch (\Exception $e) {
                    Log::error("Gagal kirim Admin Alert");
                }
            }
        }

        // --- FITUR D: TAGIHAN DENDA (SETIAP HARI MULAI H+1) ---
            $closeDate = Carbon::parse($batch->close_date)->startOfDay();
            $todayStart = $today->copy()->startOfDay();

            // Cek apakah HARI INI > TANGGAL TUTUP (Artinya sudah H+1 ke atas)
            if ($todayStart->greaterThan($closeDate)) {
                
                // 1. Hitung Multiplier Denda Mingguan (Logic sama dengan AdminController)
                $weeksLate = $closeDate->diffInWeeks($todayStart);
                $multiplier = 10000 + ($weeksLate * 10000);

                foreach ($batch->quotas as $quota) {
                    // Hanya proses jika BELUM BAYAR DENDA
                    if (!$quota->is_fine_paid) {
                        
                        // Hitung Realisasi & Defisit
                        $sold = Order::where('batch_id', $batch->id)
                                     ->where('fungsio_id', $quota->fungsio_id)
                                     ->where('status', '!=', 'Ditolak')
                                     ->withSum('orderItems', 'quantity')
                                     ->get()
                                     ->sum('order_items_sum_quantity');

                        $deficit = max(0, $quota->target_qty - $sold);

                        // Jika ada defisit (kena denda), kirim email
                        if ($deficit > 0) {
                            $totalFine = $deficit * $multiplier;
                            
                            // Inject data untuk view
                            $quota->deficit = $deficit; 

                            $this->info(" mengirim Tagihan Denda ke {$quota->fungsio->name} (Rp " . number_format($totalFine) . ")");

                            try {
                                Mail::to($quota->fungsio->email)
                                    ->send(new FineNotificationMail($quota, $batch, $totalFine, $multiplier));
                            } catch (\Exception $e) {
                                Log::error("Gagal kirim Tagihan Denda ke {$quota->fungsio->email}");
                            }
                        }
                    }
                }
            }

        $this->info('Selesai.');
    }
}