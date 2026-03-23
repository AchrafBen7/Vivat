<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterConfirmationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmez votre inscription à la newsletter Vivat',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter_confirmation',
            with: [
                'subscriber' => $this->subscriber,
                'confirmUrl' => route('newsletter.confirm', ['token' => $this->subscriber->confirm_token]),
                'unsubscribeUrl' => route('newsletter.unsubscribe', ['token' => $this->subscriber->unsubscribe_token]),
            ],
        );
    }
}
