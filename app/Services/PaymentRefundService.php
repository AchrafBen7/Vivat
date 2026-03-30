<?php

namespace App\Services;

use App\Models\Payment;
use Stripe\Refund;
use Stripe\Stripe;

class PaymentRefundService
{
    public function __construct(
        private readonly EditorialDecisionMailService $editorialDecisionMailService,
    ) {}

    public function refund(Payment $payment, ?string $reason = null): Payment
    {
        if (! $payment->isRefundable()) {
            throw new \RuntimeException(
                'Ce paiement n\'est pas remboursable (statut: ' . $payment->status . ', soumission: ' . ($payment->submission?->status ?? 'N/A') . ').'
            );
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $refund = Refund::create([
            'payment_intent' => $payment->stripe_payment_intent_id,
            'reason' => 'requested_by_customer',
        ]);

        $payment->markRefunded(
            refundId: $refund->id,
            reason: $reason ?: 'Article refusé'
        );

        $payment->loadMissing('submission.user');
        $this->editorialDecisionMailService->sendRefunded($payment);

        return $payment->fresh(['submission.user']);
    }
}
