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

class QuoteSentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Submission $submission,
        public readonly PublicationQuote $quote,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Vivat] Votre article a été accepté paiement requis',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quote-sent',
            with: [
                'authorName'    => $this->submission->user->name,
                'articleTitle'  => $this->submission->title,
                'amount'        => $this->quote->formatted_amount,
                'noteToAuthor'  => $this->quote->note_to_author,
                'expiresAt'     => $this->quote->expires_at->locale('fr')->isoFormat('dddd D MMMM YYYY à HH:mm'),
                'paymentUrl'    => url('/contributor/articles/' . $this->submission->slug . '/pay'),
            ],
        );
    }
}
