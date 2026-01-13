<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Batch;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AlertAdminMissingPickup extends Command
{
    protected $signature = 'po:alert-admin-pickup';
    protected $description = 'Ingatkan Admin jika H-1 Tutup PO tapi Tanggal Ambil belum diset';

    public function handle()
    {
        // Cari batch aktif yang pickup_date-nya KOSONG
        $batches = Batch::where('is_active', true)
                        ->whereNull('pickup_date')
                        ->get();

        // Email Admin (Bisa ambil dari .env atau User pertama)
        // Pastikan Anda sudah set MAIL_FROM_ADDRESS atau buat config khusus admin
        $adminEmail = config('mail.from.address'); // Atau 'admin@domain.com'
        
        foreach ($batches as $batch) {
            $closeDate = Carbon::parse($batch->close_date)->startOfDay();
            $today = Carbon::now()->startOfDay();
            
            // Cek H-1 Penutupan PO
            $diff = $today->diffInDays($closeDate, false); // 1 = Besok tutup
            
            if ($diff == 1) {
                $this->info("Peringatan dikirim untuk Batch: {$batch->name}");

                $message  = "Halo Admin,\n\n";
                $message .= "Peringatan Sistem: Kegiatan PO '{$batch->name}' akan ditutup BESOK ({$batch->close_date}).\n";
                $message .= "Namun, Anda BELUM menentukan 'Tanggal Pengambilan' (Pickup Date).\n\n";
                $message .= "Mohon segera login ke dashboard dan update Tanggal Pengambilan agar sistem bisa mengirim reminder otomatis ke customer nantinya.\n\n";
                $message .= "Terima kasih.";

                try {
                    Mail::raw($message, function ($msg) use ($adminEmail, $batch) {
                        $msg->to($adminEmail)
                            ->subject("⚠️ Action Required: Setup Tanggal Ambil PO {$batch->name}");
                    });
                } catch (\Exception $e) {
                    Log::error("Gagal kirim alert admin: " . $e->getMessage());
                }
            }
        }
    }
}