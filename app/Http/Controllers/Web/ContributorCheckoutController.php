<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionPayment;
use App\Services\StripeCheckoutService;
use App\Services\SubmissionWorkflowService;
use Illuminate\Support\Facades\DB;
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
     * Le webhook Stripe reste la source de vérité.
     */
    public function success(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless(
            $request->user() && $request->user()->id === $submission->user_id,
            403
        );

        $sessionId = $request->query('session_id');

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
                    $this->reconcilePaidSession($submission, (string) $session->id, $session->payment_intent ?? null);

                    return redirect()
                        ->route('contributor.dashboard')
                        ->with('success', 'Paiement reçu. La publication va être finalisée automatiquement dans quelques instants.');
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
            ->with('info', 'Retour de paiement enregistré. Si le paiement est validé par Stripe, la publication sera finalisée automatiquement.');
    }

    private function reconcilePaidSession(Submission $submission, string $sessionId, mixed $paymentIntentId): void
    {
        $submissionPayment = SubmissionPayment::query()
            ->where('stripe_checkout_session_id', $sessionId)
            ->first();

        if (! $submissionPayment || $submissionPayment->isSucceeded() || $submission->status === 'published') {
            return;
        }

        DB::transaction(function () use ($submission, $submissionPayment, $paymentIntentId): void {
            $submissionPayment->update([
                'status' => 'succeeded',
                'paid_at' => $submissionPayment->paid_at ?: now(),
                'stripe_payment_intent_id' => is_string($paymentIntentId) ? $paymentIntentId : $submissionPayment->stripe_payment_intent_id,
            ]);

            $lockedSubmission = $submission->newQuery()->lockForUpdate()->find($submission->id);

            if (! $lockedSubmission) {
                return;
            }

            if (in_array($lockedSubmission->status, ['payment_pending', 'awaiting_payment'], true)) {
                $lockedSubmission->transitionTo(
                    newStatus: 'payment_succeeded',
                    triggerSource: 'system',
                    reason: 'Paiement confirmé via success_url fallback',
                    metadata: ['stripe_checkout_session_id' => $submissionPayment->stripe_checkout_session_id],
                );
            }

            if ($submissionPayment->quote) {
                $submissionPayment->quote()->update([
                    'status' => 'accepted',
                    'accepted_at' => $submissionPayment->quote->accepted_at ?: now(),
                ]);
            }

            if ($lockedSubmission->status === 'payment_succeeded') {
                app(SubmissionWorkflowService::class)->publishAfterPayment($lockedSubmission);
            }
        });
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
