<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWeeklyDigestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param array<int, array<string, mixed>> $articles
     */
    public function __construct(
        public readonly array $articles,
        public readonly string $unsubscribeUrl,
        public readonly string $locale = 'fr',
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->locale === 'nl'
            ? 'Uw wekelijkse selectie van Vivat'
            : 'Votre sélection de la semaine — Vivat';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $headerLine = $this->locale === 'nl'
            ? 'De beste artikelen van de week'
            : 'Les meilleurs articles de la semaine';

        $subject = $this->locale === 'nl'
            ? 'Uw wekelijkse selectie van Vivat'
            : 'Votre sélection de la semaine — Vivat';

        return new Content(
            view: 'emails.newsletter_digest',
            with: [
                'articles'      => $this->articles,
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'locale'        => $this->locale,
                'headerLine'    => $headerLine,
                'subject'       => $subject,
            ],
        );
    }
}
