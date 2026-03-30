<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionRejectedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Submission $submission,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre article nécessite des corrections',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.submission_rejected',
            with: [
                'submission' => $this->submission,
                'dashboardUrl' => route('contributor.dashboard'),
                'editUrl' => route('contributor.articles.edit', ['submission' => $this->submission->slug]),
            ],
        );
    }
}
