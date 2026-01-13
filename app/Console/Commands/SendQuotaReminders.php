<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendQuotaReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'po:check-quotas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim reminder ke fungsio yang belum mencapai target (H-3 s.d H-1)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan target fungsio...');

        // Ambil Batch Aktif beserta Quota dan Relasi Fungsio
        $batches = Batch::where('is_active', true)
                        ->with('quotas.fungsio')
                        ->get();

        if ($batches->isEmpty()) {
            $this->info('Tidak ada batch aktif saat ini.');
            return;
        }

        foreach ($batches as $batch) {
            $closeDate = Carbon::parse($batch->close_date)->startOfDay();
            $today = Carbon::now()->startOfDay();
            
            // Hitung selisih hari (false = return integer, negatif jika lewat)
            $daysLeft = $today->diffInDays($closeDate, false); 

            // Logika: Hanya kirim jika sisa waktu 1 sd 3 hari (H-3, H-2, H-1)
            // Jika $daysLeft = 0 (Hari H), biasanya sudah tidak dikirim reminder target lagi, fokus closing.
            if ($daysLeft >= 1 && $daysLeft <= 3) {
                
                $this->info("Memproses Batch: {$batch->name} (Sisa {$daysLeft} hari)");

                foreach ($batch->quotas as $quota) {
                    
                    // Cek Spam: Jangan kirim jika hari ini sudah dikirim
                    if ($quota->last_reminded_at == $today->format('Y-m-d')) {
                        continue; 
                    }

                    // Hitung Realisasi Penjualan
                    // Filter order yang valid (tidak ditolak) milik fungsio ini di batch ini
                    $totalSold = Order::where('batch_id', $batch->id)
                        ->where('fungsio_id', $quota->fungsio_id)
                        ->where('status', '!=', 'Ditolak')
                        ->withSum('orderItems', 'quantity') // Eager load sum quantity
                        ->get()
                        ->sum('order_items_sum_quantity');

                    $remaining = $quota->target_qty - $totalSold;

                    // Kirim Email HANYA JIKA target belum tercapai (remaining > 0)
                    if ($remaining > 0) {
                        
                        $fungsioName = $quota->fungsio->name;
                        $fungsioEmail = $quota->fungsio->email;

                        // Template Pesan Email
                        $message  = "Halo {$fungsioName},\n\n";
                        $message .= "Ini adalah pengingat otomatis untuk kegiatan PO: {$batch->name}.\n";
                        $message .= "Waktu penutupan tinggal {$daysLeft} hari lagi.\n\n";
                        $message .= "--------------------------------------------------\n";
                        $message .= "STATUS TARGET ANDA SAAT INI:\n";
                        $message .= "Target  : {$quota->target_qty} Porsi\n";
                        $message .= "Terjual : {$totalSold} Porsi\n";
                        $message .= "KURANG  : {$remaining} Porsi ⚠️\n";
                        $message .= "--------------------------------------------------\n\n";
                        $message .= "Mohon maksimalkan penjualan sebelum tanggal " . $closeDate->format('d M Y') . ".\n";
                        $message .= "Ingat, jika target tidak tercapai, denda akan berlaku sesuai ketentuan.\n\n";
                        $message .= "Semangat!\n\nSalam,\nSistem Admin";

                        try {
                            // Kirim Email
                            Mail::raw($message, function ($msg) use ($fungsioEmail, $remaining, $batch) {
                                $msg->to($fungsioEmail)
                                    ->subject("⚠️ Alert Target: Kurang {$remaining} Porsi ({$batch->name})");
                            });
                            
                            // Update timestamp 'last_reminded_at' agar tidak dikirim lagi hari ini
                            $quota->update(['last_reminded_at' => $today->format('Y-m-d')]);
                            
                            $this->info("Reminder terkirim ke: {$fungsioName} ({$fungsioEmail})");

                        } catch (\Exception $e) {
                            Log::error("Gagal kirim reminder kuota ke {$fungsioEmail}: " . $e->getMessage());
                            $this->error("Gagal kirim ke: {$fungsioEmail}");
                        }
                    }
                }
            } else {
                // Opsional: Info jika batch di luar range reminder
                // $this->info("Batch {$batch->name} di luar periode reminder (Sisa {$daysLeft} hari).");
            }
        }
        
        $this->info('Pengecekan selesai.');
    }
}