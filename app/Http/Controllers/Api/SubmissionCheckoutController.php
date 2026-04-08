<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentFailedMail;
use App\Models\Submission;
use App\Services\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SubmissionCheckoutController extends Controller
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService,
    ) {}

    /**
     * POST /api/contributor/submissions/{submission}/checkout
     * Crée ou retourne une Stripe Checkout Session.
     */
    public function createSession(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('pay', $submission);

        $quote = $submission->quote()->where('status', 'sent')->where('expires_at', '>', now())->first();

        if (! $quote) {
            return response()->json([
                'message' => 'Aucune proposition de prix active pour cette soumission.',
            ], 422);
        }

        try {
            $payment = $this->checkoutService->getOrCreateSession($submission, $quote, $request->user());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'checkout_url'     => route('contributor.submissions.checkout.redirect', [
                'submission'   => $submission->slug,
                'payment'      => $payment->id,
            ]),
            'stripe_session_id' => $payment->stripe_checkout_session_id,
        ]);
    }

    /**
     * GET /api/contributor/submissions/{submission}/payment/cancel
     * Rédacteur annule depuis la page Stripe.
     */
    public function cancel(Submission $submission): JsonResponse
    {
        $this->authorize('view', $submission);

        // On remet en awaiting_payment pour permettre un retry
        if ($submission->status === 'payment_pending') {
            $pendingPayment = $submission->submissionPayments()
                ->whereIn('status', ['pending'])
                ->latest()
                ->first();

            if ($pendingPayment) {
                $pendingPayment->update(['status' => 'canceled']);
            }

            $submission->transitionTo(
                newStatus: 'awaiting_payment',
                triggeredBy: $submission->user_id,
                triggerSource: 'author',
                reason: 'Annulé depuis la page de paiement Stripe',
            );
        }

        return response()->json(['message' => 'Paiement annulé. Vous pouvez réessayer quand vous voulez.']);
    }

    /**
     * GET /api/contributor/submissions/{submission}/payment/success
     * Page de retour Stripe après paiement. NE PAS publier ici — le webhook le fait.
     */
    public function success(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('view', $submission);

        return response()->json([
            'message' => 'Paiement en cours de traitement. Votre article sera publié automatiquement dès confirmation.',
            'status'  => $submission->fresh()->status,
        ]);
    }
}
