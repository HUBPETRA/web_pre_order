<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

// Import Semua Mailables
use App\Mail\POReminderMail;
use App\Mail\QuotaReminderMail;
use App\Mail\AdminAlertMail;
use App\Mail\FineNotificationMail;

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

        // =========================================================================
        // [PERUBAHAN LOGIKA QUERY]
        // Kita tidak hanya mengambil yang 'is_active', tapi juga batch yang sudah tutup
        // namun masih butuh reminder Pickup atau Denda.
        // =========================================================================
        $batches = Batch::query()
            ->where('is_active', true) // 1. Ambil Batch Aktif (Normal)
            ->orWhere(function($q) use ($tomorrow) {
                // 2. Ambil Batch Non-Aktif tapi BESOK ada pengambilan (Penting untuk Reminder Pickup)
                $q->whereDate('pickup_date', $tomorrow)
                  ->where('is_reminder_sent', false);
            })
            ->orWhere(function($q) use ($today) {
                // 3. Ambil Batch Non-Aktif tapi masih dalam masa Denda (H+1 s/d 3 Bulan ke belakang)
                // Kita batasi 3 bulan agar tidak menarik data PO yang sudah terlalu lama
                $q->whereDate('close_date', '<', $today)
                  ->whereDate('close_date', '>=', $today->copy()->subMonths(3));
            })
            ->with('quotas.fungsio')
            ->get();

        if ($batches->isEmpty()) {
            $this->info("-> Tidak ada Batch yang perlu diproses saat ini. Selesai.");
            return;
        }

        foreach ($batches as $batch) {
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
            // Hanya jalankan alert admin jika batch masih aktif atau belum lewat jauh
            if ($batch->is_active || $todayStart->diffInDays($closeDate) < 7) {
                $pendingCount = $batch->orders()->where('status', 'Menunggu Verifikasi')->count();
                if ($pendingCount > 0) {
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
            }

            // ==========================================
            // FITUR D: TAGIHAN DENDA (H+1 DST)
            // ==========================================
            
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