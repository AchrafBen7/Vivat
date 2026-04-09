<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Stripe\StripeClient;

class ContributorCheckoutController extends Controller
{
    public function __construct(
        private readonly StripeCheckoutService $checkoutService,
    ) {}

    /**
     * POST /contributor/submissions/{submission}/checkout
     * Crée ou récupère une Stripe Checkout Session et redirige vers Stripe.
     */
    public function redirectToStripe(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless(
            $request->user() && $request->user()->id === $submission->user_id,
            403
        );

        $quote = $submission->quote()->where('status', 'sent')->where('expires_at', '>', now())->first();

        if (! $quote) {
            return redirect()
                ->route('contributor.payments.history')
                ->with('error', 'Aucune proposition de prix active pour cet article. Elle a peut-être expiré.');
        }

        try {
            $payment = $this->checkoutService->getOrCreateSession($submission, $quote, $request->user());
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('contributor.payments.history')
                ->with('error', $e->getMessage());
        }

        // Récupérer l'URL de la session Stripe
        $stripeKey = config('services.stripe.secret');
        if (! $stripeKey) {
            return redirect()
                ->route('contributor.payments.history')
                ->with('error', 'Configuration Stripe manquante.');
        }

        $stripe = new StripeClient($stripeKey);
        $session = $stripe->checkout->sessions->retrieve($payment->stripe_checkout_session_id);

        return redirect()->away($session->url);
    }

    /**
     * GET /contributor/submissions/{submission}/payment/success
     * Page de retour après paiement Stripe.
     * Vérifie la session Stripe et publie immédiatement si le webhook n'est pas encore passé.
     */
    public function success(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless(
            $request->user() && $request->user()->id === $submission->user_id,
            403
        );

        $sessionId = $request->query('session_id');

        // Si déjà publié, rien à faire
        if ($submission->status === 'published') {
            return redirect()->route('contributor.dashboard')
                ->with('success', 'Votre article est déjà publié !');
        }

        if ($sessionId) {
            try {
                $stripeKey = config('services.stripe.secret');
                $stripe    = new StripeClient((string) $stripeKey);
                $session   = $stripe->checkout->sessions->retrieve((string) $sessionId);

                if ($session->payment_status === 'paid') {
                    $submission->refresh();

                    // Marquer le paiement comme réussi si pas encore fait
                    $submissionPayment = $submission->submissionPayments()
                        ->where('stripe_checkout_session_id', $sessionId)
                        ->first();

                    if ($submissionPayment && $submissionPayment->status !== 'succeeded') {
                        $submissionPayment->update(['status' => 'succeeded', 'paid_at' => now()]);
                    }

                    // Accepter la quote si pas encore fait
                    $submission->quotes()
                        ->whereIn('status', ['sent', 'pending'])
                        ->update(['status' => 'accepted', 'accepted_at' => now()]);

                    // Publier si pas encore publié
                    if (! in_array($submission->status, ['published'], true)) {
                        // Amener au bon statut intermédiaire si nécessaire
                        if (in_array($submission->status, ['payment_pending', 'awaiting_payment'], true)) {
                            $submission->transitionTo(
                                newStatus: 'payment_succeeded',
                                triggerSource: 'stripe_webhook',
                                metadata: ['stripe_session_id' => $sessionId, 'via' => 'success_url_fallback'],
                            );
                        }

                        app(\App\Services\SubmissionWorkflowService::class)->publishAfterPayment($submission->fresh());
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('success_url fallback failed', [
                    'submission_id' => $submission->id,
                    'error'         => $e->getMessage(),
                    'trace'         => $e->getTraceAsString(),
                ]);
            }
        }

        return redirect()
            ->route('contributor.dashboard')
            ->with('success', 'Paiement confirmé ! Votre article est maintenant publié.');
    }

    /**
     * GET /contributor/submissions/{submission}/payment/cancel
     * Annulation depuis la page Stripe.
     */
    public function cancel(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless(
            $request->user() && $request->user()->id === $submission->user_id,
            403
        );

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

        return redirect()
            ->route('contributor.payments.history')
            ->with('info', 'Paiement annulé. Vous pouvez réessayer quand vous le souhaitez.');
    }
}
