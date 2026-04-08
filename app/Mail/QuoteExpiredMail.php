<?php

namespace App\Mail;

use App\Models\PublicationQuote;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteExpiredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Submission $submission,
        public readonly PublicationQuote $quote,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Vivat] Votre offre de publication a expiré',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quote-expired',
            with: [
                'authorName'   => $this->submission->user->name,
                'articleTitle' => $this->submission->title,
                'amount'       => $this->quote->formatted_amount,
                'contactUrl'   => url('/contact'),
            ],
        );
    }
}
