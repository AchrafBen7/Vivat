<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionRefundedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Payment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Le remboursement de votre soumission a été traité',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.submission_refunded',
            with: [
                'payment' => $this->payment,
                'submission' => $this->payment->submission,
                'dashboardUrl' => route('contributor.dashboard'),
            ],
        );
    }
}
