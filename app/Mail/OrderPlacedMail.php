<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Order; // <-- Import Model Order

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order; // Variabel untuk menampung data pesanan

    // Terima data order saat class ini dipanggil
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Pesanan #' . $this->order->id . ' - Dapur Enak PO',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order_placed', // Kita akan buat view ini di tahap 3
        );
    }

    public function attachments(): array
    {
        return [];
    }
}