<?php

namespace App\Mail;

use App\Models\Batch;
use App\Models\BatchQuota;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FineNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $quota;
    public $batch;
    public $currentFineTotal;
    public $multiplier;

    public function __construct(BatchQuota $quota, Batch $batch, $currentFineTotal, $multiplier)
    {
        $this->quota = $quota;
        $this->batch = $batch;
        $this->currentFineTotal = $currentFineTotal;
        $this->multiplier = $multiplier;
    }

    public function build()
    {
        return $this->subject('🚨 TAGIHAN DENDA: ' . $this->batch->name)
                    ->view('emails.fine_notification');
    }
}