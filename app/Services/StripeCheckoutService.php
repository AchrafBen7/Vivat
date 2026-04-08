<?php

namespace App\Services;

use App\Models\PublicationQuote;
use App\Models\Submission;
use App\Models\SubmissionPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\StripeClient;

class StripeCheckoutService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient((string) config('services.stripe.secret'));
    }

    /**
     * Crée (ou retourne) la Checkout Session Stripe pour une quote.
     * Idempotent : si une session pending existe déjà, la retourne.
     */
    public function getOrCreateSession(Submission $submission, PublicationQuote $quote, User $author): SubmissionPayment
    {
        // Guard : double paiement
        $succeeded = $submission->submissionPayments()->succeeded()->first();
        if ($succeeded) {
            throw new \RuntimeException('Cette soumission a déjà été payée.');
        }

        // Guard : soumission dans un état cohérent
        if (! in_array($submission->status, ['awaiting_payment', 'payment_failed'], true)) {
            throw new \RuntimeException("Statut [{$submission->status}] invalide pour initier un paiement.");
        }

        // Guard : quote valide
        if ($quote->status !== 'sent' || $quote->expires_at->isPast()) {
            throw new \RuntimeException('La proposition de prix est expirée ou invalide.');
        }

        // Idempotence : session Stripe existante et encore valide
        $existingPayment = $submission->submissionPayments()
            ->where('quote_id', $quote->id)
            ->whereIn('status', ['pending'])
            ->latest()
            ->first();

        if ($existingPayment) {
            // Vérifier que la session Stripe est encore valide
            try {
                $session = $this->stripe->checkout->sessions->retrieve($existingPayment->stripe_checkout_session_id);
                if ($session->status === 'open') {
                    return $existingPayment;
                }
            } catch (\Throwable) {
                // Session introuvable ou expirée, on en recrée une
            }

            $existingPayment->update(['status' => 'canceled']);
        }

        return DB::transaction(function () use ($submission, $quote, $author): SubmissionPayment {
            $idempotencyKey = (string) Str::uuid();

            $session = $this->stripe->checkout->sessions->create([
                'mode'                  => 'payment',
                'currency'              => $quote->currency,
                'line_items'            => [[
                    'price_data' => [
                        'currency'     => $quote->currency,
                        'unit_amount'  => $quote->amount_cents,
                        'product_data' => [
                            'name'        => 'Publication : ' . $submission->title,
                            'description' => 'Frais de publication de votre article sur Vivat',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'customer_email'        => $author->email,
                'success_url'           => route('contributor.submissions.payment.success', $submission) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'            => route('contributor.submissions.payment.cancel', $submission),
                'expires_at'            => now()->addMinutes(30)->timestamp,
                'metadata'              => [
                    'submission_id'  => $submission->id,
                    'quote_id'       => $quote->id,
                    'author_id'      => $author->id,
                    'idempotency_key'=> $idempotencyKey,
                ],
            ], [
                'idempotency_key' => 'session_' . $idempotencyKey,
            ]);

            $payment = SubmissionPayment::create([
                'submission_id'              => $submission->id,
                'quote_id'                   => $quote->id,
                'user_id'                    => $author->id,
                'stripe_checkout_session_id' => $session->id,
                'stripe_client_secret'       => $session->client_secret ?? null,
                'amount_cents'               => $quote->amount_cents,
                'currency'                   => $quote->currency,
                'status'                     => 'pending',
                'idempotency_key'            => $idempotencyKey,
            ]);

            // Passer en payment_pending
            $submission->transitionTo(
                newStatus: 'payment_pending',
                triggeredBy: $author->id,
                triggerSource: 'author',
                metadata: [
                    'submission_payment_id'     => $payment->id,
                    'stripe_checkout_session_id'=> $session->id,
                ],
            );

            return $payment;
        });
    }
}
