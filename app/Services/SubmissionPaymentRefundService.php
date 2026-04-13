<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\SubmissionPayment;
use App\Models\SubmissionStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\InvalidRequestException;
use Stripe\Refund;
use Stripe\Stripe;

class SubmissionPaymentRefundService
{
    public function refund(SubmissionPayment $payment, ?string $reason = null, ?User $actor = null): SubmissionPayment
    {
        if (! $payment->isRefundable()) {
            throw new \RuntimeException('Ce paiement n\'est pas remboursable.');
        }

        Stripe::setApiKey((string) config('services.stripe.secret'));

        try {
            $refund = Refund::create([
                'payment_intent' => $payment->stripe_payment_intent_id,
                'reason' => 'requested_by_customer',
            ]);
        } catch (InvalidRequestException $e) {
            if (str_contains(strtolower($e->getMessage()), 'already been refunded')) {
                $this->markRefunded(
                    payment: $payment->fresh(['submission.publishedArticle', 'quote']),
                    refundId: $payment->stripe_refund_id ?: 'already_refunded_in_stripe',
                    reason: $reason ?: 'Remboursement déjà effectué côté Stripe',
                    actor: $actor,
                    source: 'moderator',
                );

                return $payment->fresh(['submission', 'quote', 'user']);
            }

            throw $e;
        }

        $this->markRefunded(
            payment: $payment->fresh(['submission.publishedArticle', 'quote']),
            refundId: $refund->id,
            reason: $reason ?: 'Remboursement manuel',
            actor: $actor,
            source: 'moderator',
        );

        return $payment->fresh(['submission', 'quote', 'user']);
    }

    public function markRefunded(
        SubmissionPayment $payment,
        string $refundId,
        ?string $reason = null,
        ?User $actor = null,
        string $source = 'stripe_webhook'
    ): void {
        if ($payment->status === 'refunded') {
            return;
        }

        DB::transaction(function () use ($payment, $refundId, $reason, $actor, $source): void {
            $payment->update([
                'status' => 'refunded',
                'stripe_refund_id' => $refundId,
                'refund_reason' => $reason,
                'refunded_by' => $actor?->id,
                'refunded_at' => now(),
            ]);

            $submission = $payment->submission()->lockForUpdate()->with(['publishedArticle', 'quote'])->first();
            if (! $submission) {
                return;
            }

            if ($submission->status === 'payment_succeeded') {
                $submission->transitionTo(
                    newStatus: 'payment_refunded',
                    triggeredBy: $actor?->id,
                    triggerSource: $source,
                    reason: $reason,
                    metadata: [
                        'submission_payment_id' => $payment->id,
                        'stripe_refund_id' => $refundId,
                    ],
                );
            } elseif ($submission->status === 'published') {
                $policy = (string) config('payments.published_refund_policy', 'depublish');

                if ($policy === 'depublish' && $submission->publishedArticle) {
                    $submission->publishedArticle->update([
                        'status' => 'draft',
                        'published_at' => null,
                    ]);

                    $submission->transitionTo(
                        newStatus: 'payment_refunded',
                        triggeredBy: $actor?->id,
                        triggerSource: $source,
                        reason: $reason,
                        metadata: [
                            'submission_payment_id' => $payment->id,
                            'stripe_refund_id' => $refundId,
                            'depublished_article_id' => $submission->publishedArticle->id,
                        ],
                    );
                } else {
                    SubmissionStatusLog::create([
                        'submission_id' => $submission->id,
                        'from_status' => $submission->status,
                        'to_status' => $submission->status,
                        'triggered_by' => $actor?->id,
                        'trigger_source' => $source,
                        'reason' => $reason ?: 'Paiement remboursé après publication',
                        'metadata' => [
                            'submission_payment_id' => $payment->id,
                            'stripe_refund_id' => $refundId,
                            'policy' => 'keep_published',
                        ],
                        'created_at' => now(),
                    ]);
                }
            }

            if ($payment->quote && in_array($payment->quote->status, ['sent', 'accepted'], true)) {
                $payment->quote->update(['status' => 'canceled']);
            }
        });
    }

    public function markDisputed(SubmissionPayment $payment, ?string $reason = null): void
    {
        if ($payment->status === 'disputed') {
            return;
        }

        DB::transaction(function () use ($payment, $reason): void {
            $payment->update([
                'status' => 'disputed',
                'disputed_at' => now(),
                'dispute_reason' => $reason,
            ]);

            $submission = $payment->submission;
            if (! $submission) {
                return;
            }

            SubmissionStatusLog::create([
                'submission_id' => $submission->id,
                'from_status' => $submission->status,
                'to_status' => $submission->status,
                'triggered_by' => null,
                'trigger_source' => 'stripe_webhook',
                'reason' => $reason ?: 'Litige Stripe créé',
                'metadata' => [
                    'submission_payment_id' => $payment->id,
                    'payment_status' => 'disputed',
                ],
                'created_at' => now(),
            ]);
        });
    }
}
