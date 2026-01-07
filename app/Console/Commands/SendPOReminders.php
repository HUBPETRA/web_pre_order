<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPOReminders extends Command
{
    protected $signature = 'po:send-reminders';
    protected $description = 'Kirim reminder email H-1 penutupan PO';

    public function handle()
    {
        $this->info('Memeriksa jadwal reminder PO...');

        // 1. Cari Batch Aktif yang Belum Kirim Reminder
        $batches = Batch::where('is_active', true)
                        ->where('is_reminder_sent', false)
                        ->get();

        foreach ($batches as $batch) {
            $closeDate = Carbon::parse($batch->close_date);
            $reminderDate = $closeDate->subDay(); // H-1
            $today = Carbon::now();

            // Cek apakah HARI INI >= H-1 (Reminder Date)
            // Note: Untuk testing, kamu bisa hapus kondisi 'if' ini agar bisa kirim kapan saja
            if ($today->gte($reminderDate)) {
                
                $this->info("Mengirim reminder untuk: {$batch->name}");
                
                // Ambil semua pesanan di batch ini yang valid (bukan Ditolak)
                foreach ($batch->orders as $order) {
                    if($order->status == 'Ditolak') continue; 

                    // --- [LOGIKA REPLACE BARU] ---
                    
                    // 1. Buat List Item (Contoh: "- 2x Ayam\n- 1x Bebek")
                    $itemList = "";
                    foreach($order->orderItems as $item) {
                        $itemList .= "- {$item->quantity}x {$item->product_name_snapshot}\n";
                    }

                    // 2. Replace Placeholder
                    $messageBody = $batch->mail_message;
                    $messageBody = str_replace('{nama_pemesan}', $order->customer_name, $messageBody);
                    $messageBody = str_replace('{nama_kegiatan}', $batch->name, $messageBody);
                    $messageBody = str_replace('{detail_pesanan}', $itemList, $messageBody);
                    // -----------------------------

                    // SIMULASI KIRIM (Catat ke Log)
                    Log::channel('daily')->info("
                        [EMAIL SIMULATION]
                        To: {$order->customer_email}
                        Subject: Reminder PO {$batch->name}
                        Body: 
                        {$messageBody}
                        ------------------------------------------------
                    ");
                }

                // Tandai sudah terkirim
                $batch->update(['is_reminder_sent' => true]);
                $this->info("Sukses! Semua email simulasi telah dikirim ke Log.");
            } else {
                $this->info("Batch {$batch->name} belum waktunya (Jadwal: {$reminderDate->format('d M Y')})");
            }
        }
    }
}