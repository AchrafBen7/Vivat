<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Submission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Vivat] Votre soumission n\'a pas été retenue',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.submission-rejected',
            with: [
                'authorName'   => $this->submission->user->name,
                'articleTitle' => $this->submission->title,
                'reason'       => $this->submission->reviewer_notes,
                'submitNewUrl' => url('/contributor/new'),
            ],
        );
    }
}
