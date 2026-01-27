<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // [PENTING] Untuk Anti-Spam

// Import Semua Mailables
use App\Mail\POReminderMail;
use App\Mail\QuotaReminderMail;
use App\Mail\AdminAlertMail;
use App\Mail\FineNotificationMail; // [FIX] Ditambahkan karena sebelumnya kurang

class SendDailyReminders extends Command
{
    protected $signature = 'po:send-reminders';
    protected $description = 'Kirim reminder otomatis (User, Fungsio, Admin) dengan Cache Anti-Spam';

    public function handle()
    {
        $this->info('================================================');
        $this->info('   MEMULAI PROSES REMINDER HARIAN PO GENTA');
        $this->info('================================================');
        
        $today = Carbon::now();
        $tomorrow = Carbon::tomorrow();
        $todayStr = $today->format('Y-m-d'); // Key untuk Cache

        // 1. Ambil Batch yang SEDANG AKTIF (Beserta Relasi untuk Optimasi)
        $activeBatches = Batch::where('is_active', true)->with('quotas.fungsio')->get();

        if ($activeBatches->isEmpty()) {
            $this->info("-> Tidak ada Batch Aktif saat ini. Selesai.");
            return;
        }

        foreach ($activeBatches as $batch) {
            $this->info("\n[PROSES BATCH] : {$batch->name}");
            
            // ==========================================
            // FITUR A: REMINDER USER (H-1 PENGAMBILAN)
            // ==========================================
            if ($batch->pickup_date && !$batch->is_reminder_sent) {
                $pickupDate = Carbon::parse($batch->pickup_date);
                
                if ($pickupDate->isSameDay($tomorrow)) {
                    $this->info("   [FITUR A] Jadwal Pengambilan BESOK. Memproses User...");
                    
                    $orders = $batch->orders()->where('status', 'Lunas')->whereNotNull('customer_email')->get();
                    
                    foreach ($orders as $order) {
                        try {
                            Mail::to($order->customer_email)->send(new POReminderMail($order));
                            $this->info("      -> Email terkirim ke: {$order->customer_email}");
                        } catch (\Exception $e) {
                            Log::error("Gagal kirim PO Reminder ke {$order->customer_email}");
                            $this->error("      -> GAGAL kirim ke: {$order->customer_email}");
                        }
                    }

                    // Tandai batch ini sudah dikirimi reminder (Kolom DB)
                    $batch->update(['is_reminder_sent' => true]);
                    $this->info("   -> Status Batch diupdate: Reminder User Selesai.");
                } else {
                    $this->info("   [FITUR A] Skip (Jadwal ambil: " . $pickupDate->format('Y-m-d') . ")");
                }
            }

            // ==========================================
            // FITUR B: REMINDER TARGET FUNGSIO (H-3 S/D HARI H)
            // ==========================================
            $closeDate = Carbon::parse($batch->close_date)->startOfDay();
            $todayStart = $today->copy()->startOfDay();
            $selisih = $todayStart->diffInDays($closeDate, false);

            // Logika: Hari ini belum lewat tanggal tutup DAN selisih <= 3 hari
            if ($todayStart->lessThanOrEqualTo($closeDate) && $selisih <= 3 && $selisih >= 0) {
                
                $this->info("   [FITUR B] Masuk Periode Cek Target (H-{$selisih})");

                foreach ($batch->quotas as $quota) {
                    
                    // 1. CEK CACHE (ANTI SPAM)
                    $cacheKey = "quota_reminded:{$quota->id}:{$todayStr}";
                    if (Cache::has($cacheKey)) {
                        $this->info("      -> [SKIP] {$quota->fungsio->name} (Sudah dikirim hari ini)");
                        continue;
                    }

                    // 2. CEK TARGET
                    $sold = Order::where('batch_id', $batch->id)
                                 ->where('fungsio_id', $quota->fungsio_id)
                                 ->where('status', '!=', 'Ditolak')
                                 ->withSum('orderItems', 'quantity')
                                 ->get()
                                 ->sum('order_items_sum_quantity');
                    
                    if ($sold < $quota->target_qty) {
                        $quota->achieved_qty = $sold; 
                        
                        try {
                            Mail::to($quota->fungsio->email)->send(new QuotaReminderMail($quota, $batch->name));
                            $this->info("      -> [KIRIM] Reminder Target ke {$quota->fungsio->email}");
                            
                            // SIMPAN CACHE (Agar tidak kirim lagi hari ini)
                            Cache::put($cacheKey, true, now()->endOfDay());
                        } catch (\Exception $e) {
                            Log::error("Gagal kirim Quota Reminder ke {$quota->fungsio->email}");
                        }
                    } else {
                        // $this->info("      -> [AMAN] {$quota->fungsio->name} sudah capai target.");
                    }
                }
            } else {
                $this->info("   [FITUR B] Skip (Masih H-{$selisih}, belum H-3)");
            }

            // ==========================================
            // FITUR C: ADMIN ALERT (HARIAN)
            // ==========================================
            $pendingCount = $batch->orders()->where('status', 'Menunggu Verifikasi')->count();
            if ($pendingCount > 0) {
                // Cek Cache Admin agar tidak spam juga
                $adminCacheKey = "admin_alert:{$batch->id}:{$todayStr}";
                
                if (!Cache::has($adminCacheKey)) {
                    $adminEmail = 'admin@pogenta.com'; // Ganti email admin asli
                    try {
                        Mail::to($adminEmail)->send(new AdminAlertMail($pendingCount, $batch->name));
                        $this->info("   [FITUR C] Admin Alert dikirim ({$pendingCount} pending).");
                        Cache::put($adminCacheKey, true, now()->endOfDay());
                    } catch (\Exception $e) {
                        Log::error("Gagal kirim Admin Alert");
                    }
                } else {
                    $this->info("   [FITUR C] Skip Admin Alert (Sudah dikirim hari ini).");
                }
            }

            // ==========================================
            // FITUR D: TAGIHAN DENDA (H+1 DST)
            // ==========================================
            // [FIX] Kode ini sekarang ada DI DALAM loop foreach, jadi aman.
            
            $closeDate = Carbon::parse($batch->close_date)->startOfDay();
            $todayStart = $today->copy()->startOfDay();

            if ($todayStart->greaterThan($closeDate)) {
                $this->info("   [FITUR D] PO Sudah Tutup. Cek Denda...");
                
                $weeksLate = $closeDate->diffInWeeks($todayStart);
                $multiplier = 10000 + ($weeksLate * 10000);

                foreach ($batch->quotas as $quota) {
                    
                    // Cek Cache Denda
                    $fineCacheKey = "fine_reminded:{$quota->id}:{$todayStr}";

                    if (!$quota->is_fine_paid && !Cache::has($fineCacheKey)) {
                        
                        $sold = Order::where('batch_id', $batch->id)
                                     ->where('fungsio_id', $quota->fungsio_id)
                                     ->where('status', '!=', 'Ditolak')
                                     ->withSum('orderItems', 'quantity')
                                     ->get()
                                     ->sum('order_items_sum_quantity');

                        $deficit = max(0, $quota->target_qty - $sold);

                        if ($deficit > 0) {
                            $totalFine = $deficit * $multiplier;
                            $quota->deficit = $deficit; 

                            try {
                                Mail::to($quota->fungsio->email)
                                    ->send(new FineNotificationMail($quota, $batch, $totalFine, $multiplier));
                                $this->info("      -> [TAGIHAN] Dikirim ke {$quota->fungsio->email} (Rp " . number_format($totalFine) . ")");
                                
                                Cache::put($fineCacheKey, true, now()->endOfDay());
                            } catch (\Exception $e) {
                                Log::error("Gagal kirim Tagihan Denda ke {$quota->fungsio->email}");
                            }
                        }
                    }
                }
            } else {
                 $this->info("   [FITUR D] Skip (PO Belum Tutup)");
            }

        } // End Foreach Batch

        $this->info("\n================================================");
        $this->info("   PROSES SELESAI.");
        $this->info("================================================");
    }
}