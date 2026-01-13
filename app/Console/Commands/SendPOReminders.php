<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendPOReminders extends Command
{
    /**
     * Nama dan signature command console.
     */
    protected $signature = 'po:send-reminders';

    /**
     * Deskripsi command.
     */
    protected $description = 'Kirim reminder email kepada customer H-1 sebelum TANGGAL PENGAMBILAN barang';

    /**
     * Eksekusi command.
     */
    public function handle()
    {
        $this->info('Memeriksa jadwal pengambilan barang...');

        // 1. Cari Batch Aktif yang:
        //    - Punya Tanggal Pengambilan (pickup_date tidak null)
        //    - Belum dikirimi reminder (is_reminder_sent = false)
        $batches = Batch::where('is_active', true)
                        ->whereNotNull('pickup_date') 
                        ->where('is_reminder_sent', false)
                        ->get();

        if ($batches->isEmpty()) {
            $this->info('Tidak ada batch yang memerlukan reminder pengambilan saat ini.');
            return;
        }

        foreach ($batches as $batch) {
            $pickupDate = Carbon::parse($batch->pickup_date)->startOfDay();
            $reminderDate = $pickupDate->copy()->subDay(); // H-1 Pengambilan
            $today = Carbon::now()->startOfDay();

            // Cek apakah HARI INI >= H-1 Pengambilan
            if ($today->greaterThanOrEqualTo($reminderDate)) {
                
                $this->info("Memproses reminder pengambilan untuk Batch: {$batch->name}");
                
                // Ambil Order Valid (Bukan Ditolak, Punya Email)
                $orders = $batch->orders()
                                ->where('status', '!=', 'Ditolak')
                                ->whereNotNull('customer_email')
                                ->with('orderItems') 
                                ->get();

                $countSent = 0;

                foreach ($orders as $order) {
                    // Buat List Item Belanjaan
                    $itemList = "";
                    foreach($order->orderItems as $item) {
                        $itemList .= "- {$item->quantity}x {$item->product_name_snapshot}\n";
                    }

                    // Format Tanggal Indonesia
                    $formattedDate = $pickupDate->translatedFormat('l, d F Y');

                    // Replace Placeholder Template Pesan
                    $messageBody = str_replace(
                        ['{nama_pemesan}', '{nama_kegiatan}', '{detail_pesanan}'],
                        [$order->customer_name, $batch->name, $itemList],
                        $batch->mail_message
                    );
                    
                    // Tambahkan Info Spesifik Tanggal Pengambilan
                    $messageBody .= "\n\n================================\n";
                    $messageBody .= "📅 JADWAL PENGAMBILAN BARANG:\n";
                    $messageBody .= $formattedDate . "\n";
                    $messageBody .= "================================";

                    // Kirim Email via SMTP
                    try {
                        Mail::raw($messageBody, function ($message) use ($order, $batch) {
                            $message->to($order->customer_email)
                                    ->subject("🔔 Reminder Pengambilan: {$batch->name}");
                        });

                        $countSent++;

                    } catch (\Exception $e) {
                        Log::error("Gagal kirim reminder pengambilan ke {$order->customer_email}: " . $e->getMessage());
                        $this->error("Gagal kirim ke: {$order->customer_email}");
                    }
                }

                // Tandai Batch ini sudah selesai diingatkan
                $batch->update(['is_reminder_sent' => true]);
                $this->info("Selesai! {$countSent} email terkirim untuk batch ini.");

            } else {
                $this->info("Batch '{$batch->name}' belum waktunya (Jadwal Ambil: " . $pickupDate->format('d M Y') . ")");
            }
        }
    }
}