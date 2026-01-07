<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendQuotaReminders extends Command
{
    // Nama perintah untuk dijalankan di terminal
    protected $signature = 'po:check-quotas';
    protected $description = 'Kirim reminder ke fungsio yang belum target (H-3 s.d H-1)';

    public function handle()
    {
        $this->info('Memulai pengecekan target fungsio...');

        // 1. Ambil Batch yang Sedang Aktif
        $batches = Batch::where('is_active', true)->with('quotas.fungsio')->get();

        foreach ($batches as $batch) {
            $closeDate = Carbon::parse($batch->close_date)->startOfDay();
            $today = Carbon::now()->startOfDay();
            
            // Hitung selisih hari (H-?)
            // diffInDays return positif jika closeDate di masa depan
            $diff = $today->diffInDays($closeDate, false); 

            // Cek Range Waktu: H-3 sampai H-1
            // Artinya selisih hari adalah 1, 2, atau 3
            if ($diff >= 1 && $diff <= 3) {
                
                $this->info("Memproses Batch: {$batch->name} (H-{$diff})");

                foreach ($batch->quotas as $quota) {
                    
                    // Cek apakah sudah diingatkan HARI INI?
                    if ($quota->last_reminded_at == $today->format('Y-m-d')) {
                        continue; // Skip, sudah diingatkan hari ini
                    }

                    // 2. Hitung Realisasi Penjualan Fungsio Ini
                    $totalSold = Order::where('batch_id', $batch->id)
                        ->where('fungsio_id', $quota->fungsio_id)
                        ->where('status', '!=', 'Ditolak')
                        ->withSum('orderItems', 'quantity')
                        ->get()
                        ->sum('order_items_sum_quantity');

                    $remaining = $quota->target_qty - $totalSold;

                    // 3. Jika Belum Target, Kirim Email
                    if ($remaining > 0) {
                        
                        $fungsioName = $quota->fungsio->name;
                        $fungsioEmail = $quota->fungsio->email;

                        // --- TEMPLATE PESAN OTOMATIS ---
                        $message = "Halo {$fungsioName},\n\n";
                        $message .= "Ini adalah pengingat otomatis untuk kegiatan PO: {$batch->name}.\n";
                        $message .= "Waktu penutupan PO tinggal {$diff} hari lagi.\n\n";
                        $message .= "Status Target Anda:\n";
                        $message .= "- Target Wajib: {$quota->target_qty} Porsi\n";
                        $message .= "- Sudah Terjual: {$totalSold} Porsi\n";
                        $message .= "- KEKURANGAN: {$remaining} Porsi\n\n";
                        $message .= "Mohon segera maksimalkan penjualan Anda sebelum tanggal " . $closeDate->format('d M Y') . ".\n\n";
                        $message .= "Semangat!\nSistem Admin";
                        // -------------------------------

                        // Simulasi Kirim Email (Log)
                        Log::channel('daily')->info("
                            [QUOTA REMINDER - H-{$diff}]
                            To: {$fungsioEmail}
                            Subject: ⚠️ Alert Target PO: Kurang {$remaining} Porsi
                            Body:
                            {$message}
                            ------------------------------------------------
                        ");

                        // Update Database: Tandai sudah diingatkan hari ini
                        $quota->update(['last_reminded_at' => $today->format('Y-m-d')]);
                        
                        $this->info("Reminder dikirim ke: {$fungsioName}");
                    }
                }
            } else {
                $this->info("Batch {$batch->name} tidak masuk periode reminder (H-{$diff}).");
            }
        }
        
        $this->info('Selesai.');
    }
}