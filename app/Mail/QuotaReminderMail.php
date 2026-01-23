<?php
namespace App\Mail;
use App\Models\BatchQuota;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotaReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    public $quota;
    public $batchName;

    public function __construct(BatchQuota $quota, $batchName) {
        $this->quota = $quota;
        $this->batchName = $batchName;
    }

    public function build() {
        return $this->subject('⚠️ Peringatan Target Kuota: ' . $this->batchName)
                    ->view('emails.quota_reminder');
    }
}