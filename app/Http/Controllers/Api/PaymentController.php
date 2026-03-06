<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function __construct()
    {
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
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        // Check submission is in right state
        if (! in_array($submission->status, ['draft', 'pending'])) {
            return response()->json([
                'message' => 'Cette soumission ne peut pas être payée (statut: ' . $submission->status . ').',
            ], 422);
        }

        // Check if already paid
        $existingPayment = Payment::where('submission_id', $submission->id)
            ->where('status', 'paid')
            ->first();

        if ($existingPayment) {
            return response()->json([
                'message' => 'Cette soumission a déjà été payée.',
            ], 422);
        }

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
            return response()->json(['message' => 'Non autorisé.'], 403);
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

            return response()->json([
                'message' => 'Le paiement n\'est pas encore confirmé par Stripe.',
                'stripe_status' => $intent->status,
            ], 402);
        } catch (\Exception $e) {
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
        if (! $payment->isRefundable()) {
            return response()->json([
                'message' => 'Ce paiement n\'est pas remboursable (statut: ' . $payment->status . ', soumission: ' . ($payment->submission?->status ?? 'N/A') . ').',
            ], 422);
        }

        try {
            $refund = Refund::create([
                'payment_intent' => $payment->stripe_payment_intent_id,
                'reason'         => 'requested_by_customer',
            ]);

            $payment->markRefunded(
                refundId: $refund->id,
                reason: $request->input('reason', 'Article refusé')
            );

            return response()->json([
                'message' => 'Remboursement effectué.',
                'refund_id' => $refund->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du remboursement.',
                'error'   => $e->getMessage(),
            ], 502);
        }
    }

    /**
     * GET /api/payments/my — historique paiements du contributeur
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
}
