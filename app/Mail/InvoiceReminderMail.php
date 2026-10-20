<?php

namespace App\Mail;

use App\Models\BtDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BtDocument $document,
        public string $body,
    ) {
        $this->document->loadMissing(['contact', 'business']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment reminder — invoice '.$this->document->number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice-reminder');
    }
}
