<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Submission;
use App\Services\PaymentRefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Throwable;

class PaymentController extends Controller
{
    private const STALE_PENDING_MINUTES = 30;

    public function __construct(
        private readonly PaymentRefundService $paymentRefundService,
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * POST /api/payments/create-intent
     * Create a Stripe PaymentIntent for one-time article publication.
     */
    public function createIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'submission_id' => ['required', 'uuid', 'exists:submissions,id'],
        ]);

        $submission = Submission::findOrFail($validated['submission_id']);

        // Verify ownership
        if ($submission->user_id !== $request->user()->id) {
            $this->logSecurityEvent('warning', 'Payment intent creation denied: submission ownership mismatch.', [
                'submission_id' => $submission->id,
                'submission_user_id' => $submission->user_id,
                'actor_user_id' => $request->user()->id,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Check submission is in right state
        if (! in_array($submission->status, ['draft', 'pending'])) {
            $this->logSecurityEvent('notice', 'Payment intent creation blocked for invalid submission status.', [
                'submission_id' => $submission->id,
                'status' => $submission->status,
                'actor_user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Cette soumission ne peut pas être payée (statut: ' . $submission->status . ').',
            ], 422);
        }

        // Check if already paid
        $existingPayment = Payment::where('submission_id', $submission->id)
            ->where('status', 'paid')
            ->first();

        if ($existingPayment) {
            $this->logSecurityEvent('notice', 'Duplicate paid payment creation attempt blocked.', [
                'submission_id' => $submission->id,
                'payment_id' => $existingPayment->id,
                'actor_user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Cette soumission a déjà été payée.',
            ], 422);
        }

        $activePendingPayment = Payment::query()
            ->where('submission_id', $submission->id)
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->latest('created_at')
            ->first();

        if ($activePendingPayment) {
            $activeIntent = $this->syncPendingPaymentState($activePendingPayment);

            if ($activePendingPayment->fresh()?->status === 'paid') {
                return response()->json([
                    'message' => 'Cette soumission a déjà été payée.',
                ], 422);
            }

            if ($activePendingPayment->fresh()?->status === 'pending' && $activeIntent) {
                return response()->json([
                    'client_secret' => $activeIntent->client_secret,
                    'payment_id' => $activePendingPayment->id,
                    'amount' => $activePendingPayment->amount,
                    'currency' => $activePendingPayment->currency,
                    'reused' => true,
                    'message' => 'Un paiement en cours existe déjà pour cette soumission.',
                ]);
            }
        }

        $this->expireOldPendingPayments($submission->id, $request->user()->id);

        $amount = (int) config('services.stripe.publication_price', 1500); // 15.00 EUR

        try {
            $intent = PaymentIntent::create([
                'amount'   => $amount,
                'currency' => 'eur',
                'metadata' => [
                    'submission_id' => $submission->id,
                    'user_id'       => $request->user()->id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur Stripe.',
                'error'   => $e->getMessage(),
            ], 502);
        }

        $payment = Payment::create([
            'user_id'                 => $request->user()->id,
            'submission_id'           => $submission->id,
            'stripe_payment_intent_id' => $intent->id,
            'amount'                  => $amount,
            'currency'                => 'eur',
            'status'                  => 'pending',
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret,
            'payment_id'    => $payment->id,
            'amount'        => $amount,
            'currency'      => 'eur',
        ]);
    }

    /**
     * POST /api/payments/confirm
     * Confirm payment after Stripe frontend confirmation.
     */
    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_id' => ['required', 'uuid', 'exists:payments,id'],
        ]);

        $payment = Payment::findOrFail($validated['payment_id']);

        if ($payment->user_id !== $request->user()->id) {
            $this->logSecurityEvent('warning', 'Payment confirmation denied: payment ownership mismatch.', [
                'payment_id' => $payment->id,
                'payment_user_id' => $payment->user_id,
                'actor_user_id' => $request->user()->id,
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        if ($payment->status === 'paid') {
            $this->logSecurityEvent('notice', 'Duplicate payment confirmation blocked for already paid payment.', [
                'payment_id' => $payment->id,
                'actor_user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Ce paiement a déjà été confirmé.',
                'status' => 'paid',
            ], 422);
        }

        if ($payment->status === 'refunded') {
            $this->logSecurityEvent('notice', 'Payment confirmation blocked for refunded payment.', [
                'payment_id' => $payment->id,
                'actor_user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Ce paiement a déjà été remboursé et ne peut plus être confirmé.',
            ], 422);
        }

        if ($payment->status === 'abandoned') {
            $this->logSecurityEvent('notice', 'Payment confirmation blocked for abandoned payment.', [
                'payment_id' => $payment->id,
                'actor_user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Cette tentative de paiement a expiré. Merci de relancer un nouveau paiement.',
            ], 422);
        }

        if ($payment->status === 'failed') {
            $this->logSecurityEvent('notice', 'Payment confirmation blocked for failed payment.', [
                'payment_id' => $payment->id,
                'actor_user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Cette tentative de paiement a échoué. Merci de relancer un nouveau paiement.',
            ], 422);
        }

        // Verify with Stripe
        try {
            $intent = PaymentIntent::retrieve($payment->stripe_payment_intent_id);

            if ($intent->status === 'succeeded') {
                $payment->markPaid();

                // Auto-submit the submission for review
                if ($payment->submission && $payment->submission->status === 'draft') {
                    $payment->submission->update([
                        'status'     => 'pending',
                        'payment_id' => $payment->id,
                    ]);
                }

                return response()->json([
                    'message' => 'Paiement confirmé. Votre article est soumis pour validation.',
                    'status'  => 'paid',
                ]);
            }

            if ($intent->status === 'canceled') {
                $payment->markAbandoned();

                return response()->json([
                    'message' => 'Cette tentative de paiement a été annulée. Merci de relancer un nouveau paiement.',
                    'status' => 'abandoned',
                ], 422);
            }

            if ($intent->status === 'requires_payment_method') {
                $payment->markFailed();

                return response()->json([
                    'message' => "Le paiement n'a pas abouti. Vérifiez vos informations et réessayez.",
                    'status' => 'failed',
                ], 402);
            }

            return response()->json([
                'message' => 'Le paiement n\'est pas encore confirmé par Stripe.',
                'stripe_status' => $intent->status,
            ], 402);
        } catch (\Exception $e) {
            $this->logSecurityEvent('error', 'Stripe payment confirmation verification failed.', [
                'payment_id' => $payment->id,
                'actor_user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de la vérification du paiement.',
                'error'   => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * POST /api/payments/{payment}/refund
     * Refund a payment (admin only) when submission is rejected.
     */
    public function refund(Request $request, Payment $payment): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $payment = $this->paymentRefundService->refund(
                $payment->loadMissing('submission'),
                $validated['reason'] ?? 'Article refusé',
            );

            return response()->json([
                'message' => 'Remboursement effectué.',
                'refund_id' => $payment->stripe_refund_id,
                'status' => $payment->status,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du remboursement.',
                'error'   => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * GET /api/payments/my historique paiements du contributeur
     */
    public function myPayments(Request $request): JsonResponse
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->with('submission:id,title,status')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => $payments->map(fn ($p) => [
                'id'         => $p->id,
                'amount'     => $p->amount,
                'currency'   => $p->currency,
                'status'     => $p->status,
                'submission' => $p->submission ? [
                    'id'     => $p->submission->id,
                    'title'  => $p->submission->title,
                    'status' => $p->submission->status,
                ] : null,
                'created_at' => $p->created_at?->toIso8601String(),
            ]),
            'meta' => [
                'total'        => $payments->total(),
                'per_page'     => $payments->perPage(),
                'current_page' => $payments->currentPage(),
            ],
        ]);
    }

    private function syncPendingPaymentState(Payment $payment): ?PaymentIntent
    {
        if (! $payment->isPending()) {
            return null;
        }

        try {
            $intent = PaymentIntent::retrieve($payment->stripe_payment_intent_id);
        } catch (Throwable) {
            return null;
        }

        if ($intent->status === 'succeeded') {
            $payment->markPaid();

            if ($payment->submission && $payment->submission->status === 'draft') {
                $payment->submission->update([
                    'status' => 'pending',
                    'payment_id' => $payment->id,
                ]);
            }

            return $intent;
        }

        if ($intent->status === 'canceled') {
            $payment->markAbandoned();

            return null;
        }

        if ($this->isStalePendingPayment($payment)) {
            try {
                if (in_array($intent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'processing'], true)) {
                    PaymentIntent::cancel($payment->stripe_payment_intent_id);
                }
            } catch (Throwable) {
                // Best effort: even if Stripe cancel fails, we still stop reusing this local payment.
            }

            $payment->markAbandoned();

            return null;
        }

        return $intent;
    }

    private function logSecurityEvent(string $level, string $message, array $context = []): void
    {
        Log::channel('security')->log($level, $message, $context);
    }

    private function expireOldPendingPayments(string $submissionId, string $userId): void
    {
        Payment::query()
            ->where('submission_id', $submissionId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(self::STALE_PENDING_MINUTES))
            ->get()
            ->each(function (Payment $payment): void {
                try {
                    PaymentIntent::cancel($payment->stripe_payment_intent_id);
                } catch (Throwable) {
                    // Ignore cancellation failures for stale local payments.
                }

                $payment->markAbandoned();
            });
    }

    private function isStalePendingPayment(Payment $payment): bool
    {
        return $payment->created_at !== null
            && $payment->created_at->lt(now()->subMinutes(self::STALE_PENDING_MINUTES));
    }
}
