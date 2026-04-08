<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PipelineHealthAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly array $snapshot) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Vivat] Alerte pipeline IA',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pipeline-health-alert',
            with: [
                'snapshot' => $this->snapshot,
            ],
        );
    }
}
