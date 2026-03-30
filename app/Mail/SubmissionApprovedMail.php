<?php

namespace App\Mail;

use App\Models\Article;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubmissionApprovedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Submission $submission,
        public Article $article,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre article a été accepté par Vivat',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.submission_approved',
            with: [
                'submission' => $this->submission,
                'article' => $this->article,
                'articleUrl' => route('articles.show', ['slug' => $this->article->slug]),
            ],
        );
    }
}
