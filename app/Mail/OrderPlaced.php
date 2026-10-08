<?php

namespace App\Mail;

use App\Models\PackageOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PackageOrder $order,
        public bool $toAdmin = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->toAdmin
            ? 'New order '.$this->order->reference.' — '.$this->order->package.' (UGX '.number_format($this->order->total_amount).')'
            : 'Your Ttryy order '.$this->order->reference.' is received';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-placed');
    }
}
