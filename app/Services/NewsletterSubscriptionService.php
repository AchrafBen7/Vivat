<?php

namespace App\Services;

use App\Mail\NewsletterConfirmationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class NewsletterSubscriptionService
{
    /**
     * @param  array{email:string,name?:string|null,interests?:array<int,string>|null}  $data
     * @return array{status:string,message:string,subscriber:NewsletterSubscriber,mail_delivered:?bool}
     */
    public function subscribe(array $data): array
    {
        $email = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $name = isset($data['name']) ? trim((string) $data['name']) : null;
        $interests = $this->normalizeInterests($data['interests'] ?? null);

        $existing = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($existing) {
            if ($existing->confirmed && $existing->unsubscribed_at === null) {
                $existing->update([
                    'name' => $name !== '' ? $name : $existing->name,
                    'interests' => $interests,
                ]);

                return [
                    'status' => 'already_active',
                    'message' => 'Cette adresse est déjà inscrite à la newsletter Vivat.',
                    'subscriber' => $existing->fresh(),
                    'mail_delivered' => null,
                ];
            }

            $existing->update([
                'name' => $name !== '' ? $name : $existing->name,
                'interests' => $interests,
                'confirmed' => false,
                'confirmed_at' => null,
                'unsubscribed_at' => null,
                'confirm_token' => Str::random(64),
            ]);

            $subscriber = $existing->fresh();
            $mailOk = $this->sendConfirmationEmail($subscriber);

            return [
                'status' => 'confirmation_resent',
                'message' => $mailOk
                    ? 'Votre demande est enregistrée. Vérifiez votre boîte mail pour confirmer votre inscription.'
                    : 'Inscription enregistrée, mais l’envoi de l’e-mail de confirmation a échoué (service indisponible ou configuration d’envoi). Réessayez plus tard ou contactez-nous.',
                'subscriber' => $subscriber,
                'mail_delivered' => $mailOk,
            ];
        }

        $subscriber = NewsletterSubscriber::query()->create([
            'email' => $email,
            'name' => $name !== '' ? $name : null,
            'interests' => $interests,
        ]);

        $mailOk = $this->sendConfirmationEmail($subscriber);

        return [
            'status' => 'created',
            'message' => $mailOk
                ? 'Inscription enregistrée. Vérifiez votre boîte mail pour confirmer votre abonnement.'
                : 'Inscription enregistrée, mais l’envoi de l’e-mail de confirmation a échoué (service indisponible ou configuration d’envoi). Réessayez plus tard ou contactez-nous.',
            'subscriber' => $subscriber,
            'mail_delivered' => $mailOk,
        ];
    }

    /**
     * @return array{status:string,message:string,subscriber:?NewsletterSubscriber}
     */
    public function confirm(?string $token): array
    {
        $token = is_string($token) ? trim($token) : '';

        if ($token === '') {
            return [
                'status' => 'missing_token',
                'message' => 'Le lien de confirmation est incomplet.',
                'subscriber' => null,
            ];
        }

        $subscriber = NewsletterSubscriber::query()->where('confirm_token', $token)->first();

        if (! $subscriber) {
            return [
                'status' => 'invalid_token',
                'message' => 'Ce lien de confirmation est invalide ou a déjà été utilisé.',
                'subscriber' => null,
            ];
        }

        $subscriber->confirm();

        return [
            'status' => 'confirmed',
            'message' => 'Votre abonnement à la newsletter Vivat est confirmé.',
            'subscriber' => $subscriber->fresh(),
        ];
    }

    /**
     * @return array{status:string,message:string,subscriber:?NewsletterSubscriber}
     */
    public function unsubscribe(?string $token): array
    {
        $token = is_string($token) ? trim($token) : '';

        if ($token === '') {
            return [
                'status' => 'missing_token',
                'message' => 'Le lien de désinscription est incomplet.',
                'subscriber' => null,
            ];
        }

        $subscriber = NewsletterSubscriber::query()->where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return [
                'status' => 'invalid_token',
                'message' => 'Ce lien de désinscription est invalide.',
                'subscriber' => null,
            ];
        }

        $subscriber->unsubscribe();

        return [
            'status' => 'unsubscribed',
            'message' => 'Vous êtes bien désinscrit de la newsletter Vivat.',
            'subscriber' => $subscriber->fresh(),
        ];
    }

    /**
     * @param  array<int, string>|null  $interests
     * @return array<int, string>
     */
    private function normalizeInterests(?array $interests): array
    {
        $normalized = collect($interests ?? [])
            ->filter(fn ($value) => is_string($value) && trim($value) !== '')
            ->map(fn (string $value) => Str::slug($value))
            ->unique()
            ->values()
            ->all();

        return $normalized !== [] ? $normalized : ['general'];
    }

    /**
     * @return bool True si l’e-mail a bien été accepté par le transporteur.
     */
    private function sendConfirmationEmail(NewsletterSubscriber $subscriber): bool
    {
        try {
            Mail::to($subscriber->email)->send(new NewsletterConfirmationMail($subscriber));

            return true;
        } catch (Throwable $e) {
            Log::warning('newsletter.confirmation_email_failed', [
                'subscriber_id' => $subscriber->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
