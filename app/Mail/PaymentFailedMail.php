<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentFailedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Submission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Vivat] Problème avec votre paiement',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-failed',
            with: [
                'authorName'   => $this->submission->user->name,
                'articleTitle' => $this->submission->title,
                'retryUrl'     => url('/contributor/articles/' . $this->submission->slug . '/pay'),
            ],
        );
    }
}
