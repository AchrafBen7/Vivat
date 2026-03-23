<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $webhookSecret = (string) config('services.stripe.webhook_secret');

        if ($webhookSecret === '') {
            return response()->json(['message' => 'Stripe webhook secret is not configured.'], 500);
        }

        $payload = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (UnexpectedValueException|SignatureVerificationException $exception) {
            return response()->json([
                'message' => 'Invalid Stripe webhook payload.',
            ], 400);
        }

        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($event->data->object),
            'charge.refunded' => $this->handleChargeRefunded($event->data->object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function handlePaymentIntentSucceeded(object $intent): void
    {
        $payment = Payment::query()
            ->with('submission')
            ->where('stripe_payment_intent_id', $intent->id)
            ->first();

        if (! $payment) {
            return;
        }

        if ($payment->status !== 'paid') {
            $payment->markPaid();
        }

        if ($payment->submission) {
            $updates = ['payment_id' => $payment->id];

            if ($payment->submission->status === 'draft') {
                $updates['status'] = 'pending';
            }

            $payment->submission->update($updates);
        }
    }

    private function handlePaymentIntentFailed(object $intent): void
    {
        $payment = Payment::query()
            ->where('stripe_payment_intent_id', $intent->id)
            ->first();

        if (! $payment || $payment->status === 'paid') {
            return;
        }

        $payment->markFailed();
    }

    private function handleChargeRefunded(object $charge): void
    {
        $paymentIntentId = $charge->payment_intent ?? null;

        if (! is_string($paymentIntentId) || $paymentIntentId === '') {
            return;
        }

        $payment = Payment::query()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->first();

        if (! $payment || $payment->status === 'refunded') {
            return;
        }

        $payment->markRefunded(
            refundId: (string) ($charge->refunds->data[0]->id ?? 'stripe-refund'),
            reason: 'Refund confirmed by Stripe webhook',
        );
    }
}
