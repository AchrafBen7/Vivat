<?php

namespace App\Services;

use App\Mail\QuoteExpiredMail;
use App\Mail\QuoteSentMail;
use App\Models\PublicationQuote;
use App\Models\Submission;
use App\Models\SubmissionPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PublicationQuoteService
{
    private const DEFAULT_EXPIRY_DAYS = 7;

    /**
     * Modérateur propose un prix. submission passe en price_proposed.
     */
    public function propose(
        Submission $submission,
        User $moderator,
        int $amountCents,
        string $currency = 'eur',
        string $articleType = 'standard',
        ?string $pricePresetId = null,
        ?string $noteToAuthor = null,
        int $expiryDays = self::DEFAULT_EXPIRY_DAYS,
    ): PublicationQuote {
        if ($amountCents <= 0) {
            throw new \InvalidArgumentException('Le montant doit être supérieur à 0.');
        }

        if (! in_array($articleType, ['standard', 'hot_news', 'long_form'], true)) {
            throw new \InvalidArgumentException("Le type d'article est invalide.");
        }

        return DB::transaction(function () use (
            $submission, $moderator, $amountCents, $currency,
            $articleType, $pricePresetId, $noteToAuthor, $expiryDays,
        ): PublicationQuote {
            // Passer la soumission en price_proposed si elle est under_review
            if ($submission->status === 'under_review') {
                $submission->transitionTo(
                    newStatus: 'price_proposed',
                    triggeredBy: $moderator->id,
                    triggerSource: 'moderator',
                );
            }

            $quote = PublicationQuote::create([
                'submission_id'  => $submission->id,
                'proposed_by'    => $moderator->id,
                'price_preset_id'=> $pricePresetId,
                'amount_cents'   => $amountCents,
                'currency'       => strtolower($currency),
                'article_type'   => $articleType,
                'status'         => 'sent',
                'note_to_author' => $noteToAuthor,
                'expires_at'     => now()->addDays($expiryDays),
                'sent_at'        => now(),
            ]);

            // Passer en awaiting_payment
            $submission->transitionTo(
                newStatus: 'awaiting_payment',
                triggeredBy: $moderator->id,
                triggerSource: 'moderator',
                metadata: ['quote_id' => $quote->id],
            );

            return $quote;
        });
    }

    /**
     * Expire les quotes périmées (appelé par job schedulé).
     */
    public function expireOverdueQuotes(): int
    {
        $expired = PublicationQuote::where('status', 'sent')
            ->where('expires_at', '<', now())
            ->with(['submission.user'])
            ->get();

        $count = 0;

        foreach ($expired as $quote) {
            DB::transaction(function () use ($quote): void {
                $quote->update(['status' => 'expired']);

                $submission = $quote->submission;

                if ($submission && in_array($submission->status, ['awaiting_payment', 'payment_pending', 'payment_failed'], true)) {
                    // Annuler le payment Stripe en attente si existant
                    $pendingPayment = $submission->submissionPayments()
                        ->whereIn('status', ['pending', 'processing'])
                        ->latest()
                        ->first();

                    if ($pendingPayment?->stripe_checkout_session_id) {
                        $this->cancelStripeSession($pendingPayment->stripe_checkout_session_id);
                        $pendingPayment->update(['status' => 'canceled']);
                    }

                    $submission->transitionTo(
                        newStatus: 'payment_expired',
                        triggerSource: 'system',
                        metadata: ['quote_id' => $quote->id],
                    );

                    if ($submission->user) {
                        Mail::to($submission->user->email)
                            ->queue(new QuoteExpiredMail($submission, $quote));
                    }
                }
            });

            $count++;
        }

        return $count;
    }

    private function cancelStripeSession(string $sessionId): void
    {
        try {
            $stripe = new \Stripe\StripeClient((string) config('services.stripe.secret'));
            $stripe->checkout->sessions->expire($sessionId);
        } catch (\Throwable $e) {
            // La session peut déjà être expirée pas critique
        }
    }
}
