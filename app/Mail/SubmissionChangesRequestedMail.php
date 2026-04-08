<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionChangesRequestedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Submission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Vivat] Des modifications sont demandées pour votre article',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.submission-changes-requested',
            with: [
                'authorName'   => $this->submission->user->name,
                'articleTitle' => $this->submission->title,
                'note'         => $this->submission->reviewer_notes,
                'editUrl'      => url('/contributor/articles/' . $this->submission->slug . '/edit'),
            ],
        );
    }
}
