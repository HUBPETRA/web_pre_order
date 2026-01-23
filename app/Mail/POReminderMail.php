<?php
namespace App\Mail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class POReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    public $order;

    public function __construct(Order $order) {
        $this->order = $order;
    }

    public function build() {
        return $this->subject('🔔 Reminder: Besok Jadwal Pengambilan - ' . $this->order->batch->name)
                    ->view('emails.po_reminder');
    }
}