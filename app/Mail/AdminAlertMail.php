<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminAlertMail extends Mailable
{
    use Queueable, SerializesModels;
    public $pendingCount;
    public $batchName;

    public function __construct($pendingCount, $batchName) {
        $this->pendingCount = $pendingCount;
        $this->batchName = $batchName;
    }

    public function build() {
        return $this->subject('⚡ Action Needed: ' . $this->pendingCount . ' Pesanan Menunggu')
                    ->view('emails.admin_alert');
    }
}