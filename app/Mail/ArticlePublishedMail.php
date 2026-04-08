<?php

namespace App\Mail;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ArticlePublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Submission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Vivat] Votre article est en ligne !',
        );
    }

    public function content(): Content
    {
        $articleUrl = $this->submission->publishedArticle
            ? url('/articles/' . $this->submission->publishedArticle->slug)
            : url('/');

        return new Content(
            markdown: 'emails.article-published',
            with: [
                'authorName'   => $this->submission->user->name,
                'articleTitle' => $this->submission->title,
                'articleUrl'   => $articleUrl,
            ],
        );
    }
}
