<?php

namespace App\Mail;

use App\Models\CorsDailyOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CorsDailyOrderPreparing extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CorsDailyOrder $order)
    {
        $this->order->loadMissing(['station', 'user']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We are preparing your 24-hour CORS file',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.cors-daily.preparing',
        );
    }
}
