<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ArticlePublishedMail;
use App\Mail\PaymentFailedMail;
use App\Mail\PaymentSucceededMail;
use App\Models\Payment;
use App\Models\StripeWebhookLog;
use App\Models\SubmissionPayment;
use App\Notifications\StripeDisputeAlertNotification;
use App\Services\SubmissionWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $webhookSecret = (string) config('services.stripe.webhook_secret');

        if ($webhookSecret === '') {
            Log::channel('security')->error('Stripe webhook rejected: secret not configured.', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Stripe webhook secret is not configured.'], 500);
        }

        $payload   = $request->getContent();
        $signature = (string) $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
        } catch (UnexpectedValueException | SignatureVerificationException $e) {
            Log::channel('security')->warning('Invalid Stripe webhook rejected.', [
                'ip'                => $request->ip(),
                'error'             => $e->getMessage(),
                'signature_present' => $signature !== '',
            ]);
            return response()->json(['message' => 'Invalid Stripe webhook payload.'], 400);
        }

        // ── Idempotence ──────────────────────────────────────────────────
        if (StripeWebhookLog::where('stripe_event_id', $event->id)->exists()) {
            return response()->json(['status' => 'already_processed']);
        }

        StripeWebhookLog::create([
            'stripe_event_id' => $event->id,
            'type'            => $event->type,
        ]);

        // ── Dispatch selon type ──────────────────────────────────────────
        match ($event->type) {
            // Nouveau workflow (Checkout)
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'checkout.session.expired'   => $this->handleCheckoutExpired($event->data->object),

            // Ancien workflow (PaymentIntent direct) + legacy
            'payment_intent.succeeded'       => $this->handlePaymentIntentSucceeded($event->data->object),
            'payment_intent.payment_failed'  => $this->handlePaymentIntentFailed($event->data->object),

            // Remboursements et litiges
            'charge.refunded'        => $this->handleChargeRefunded($event->data->object),
            'charge.dispute.created' => $this->handleDisputeCreated($event->data->object),

            default => null,
        };

        return response()->json(['received' => true]);
    }

    /* ================================================================== */
    /*  Nouveau workflow Checkout Session                               */
    /* ================================================================== */

    private function handleCheckoutCompleted(object $session): void
    {
        $submissionId = $session->metadata['submission_id'] ?? null;
        $quoteId      = $session->metadata['quote_id'] ?? null;

        if (! $submissionId) {
            // Peut appartenir à un autre workflow ne pas traiter
            return;
        }

        $submissionPayment = SubmissionPayment::where('stripe_checkout_session_id', $session->id)->first();

        if (! $submissionPayment) {
            Log::error('checkout.session.completed: SubmissionPayment introuvable', ['session_id' => $session->id]);
            return;
        }

        if ($submissionPayment->isSucceeded()) {
            return; // idempotent
        }

        DB::transaction(function () use ($session, $submissionPayment): void {
            $submissionPayment->update([
                'status'      => 'succeeded',
                'paid_at'     => now(),
                'stripe_receipt_url' => null, // le receipt est dans l'email Stripe
            ]);

            $submission = $submissionPayment->submission()->lockForUpdate()->first();

            if (! $submission) {
                return;
            }

            if (! in_array($submission->status, ['payment_pending', 'awaiting_payment'], true)) {
                Log::warning('checkout.session.completed: statut inattendu', [
                    'submission_id' => $submission->id,
                    'status'        => $submission->status,
                ]);
                return;
            }

            $submission->transitionTo(
                newStatus: 'payment_succeeded',
                triggerSource: 'stripe_webhook',
                metadata: ['stripe_event' => $session->id],
            );

            // Accepter la quote
            $submissionPayment->quote()->update(['status' => 'accepted', 'accepted_at' => now()]);

            // Publier l'article
            app(SubmissionWorkflowService::class)->publishAfterPayment($submission);
        });

        // Emails hors transaction
        $submissionPayment->refresh();
        $submission = $submissionPayment->submission()->with('user')->first();

        if ($submission?->user) {
            Mail::to($submission->user->email)->queue(new PaymentSucceededMail($submission));
            Mail::to($submission->user->email)->queue(new ArticlePublishedMail($submission));
        }
    }

    private function handleCheckoutExpired(object $session): void
    {
        $submissionId = $session->metadata['submission_id'] ?? null;
        if (! $submissionId) {
            return;
        }

        $submissionPayment = SubmissionPayment::where('stripe_checkout_session_id', $session->id)->first();
        if (! $submissionPayment) {
            return;
        }

        DB::transaction(function () use ($submissionPayment): void {
            $submissionPayment->update(['status' => 'canceled']);

            $submission = $submissionPayment->submission()->lockForUpdate()->first();
            if ($submission && $submission->status === 'payment_pending') {
                $submission->transitionTo(
                    newStatus: 'awaiting_payment', // peut réessayer
                    triggerSource: 'stripe_webhook',
                );
            }
        });
    }

    /* ================================================================== */
    /*  Ancien workflow PaymentIntent direct (compat legacy)            */
    /* ================================================================== */

    private function handlePaymentIntentSucceeded(object $intent): void
    {
        // Vérifier d'abord si c'est un payment du nouveau workflow
        $submissionPayment = SubmissionPayment::where('stripe_payment_intent_id', $intent->id)->first();
        if ($submissionPayment) {
            // Géré via checkout.session.completed normalement
            return;
        }

        // Ancien workflow
        $payment = Payment::query()->with('submission')->where('stripe_payment_intent_id', $intent->id)->first();
        if (! $payment) {
            Log::channel('security')->notice('payment_intent.succeeded: Payment introuvable', ['intent_id' => $intent->id]);
            return;
        }

        if (in_array($payment->status, ['refunded', 'abandoned'], true)) {
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
        // Nouveau workflow
        $submissionPayment = SubmissionPayment::where('stripe_payment_intent_id', $intent->id)->first();
        if ($submissionPayment) {
            DB::transaction(function () use ($intent, $submissionPayment): void {
                $submissionPayment->update([
                    'status'          => 'failed',
                    'failure_code'    => $intent->last_payment_error?->code ?? null,
                    'failure_message' => $intent->last_payment_error?->message ?? null,
                ]);

                $submission = $submissionPayment->submission()->lockForUpdate()->first();
                if ($submission && $submission->status === 'payment_pending') {
                    $submission->transitionTo(
                        newStatus: 'awaiting_payment',
                        triggerSource: 'stripe_webhook',
                    );
                }
            });

            $submission = $submissionPayment->submission()->with('user')->first();
            if ($submission?->user) {
                Mail::to($submission->user->email)->queue(new PaymentFailedMail($submission));
            }
            return;
        }

        // Ancien workflow
        $payment = Payment::query()->where('stripe_payment_intent_id', $intent->id)->first();
        if (! $payment || in_array($payment->status, ['paid', 'refunded', 'abandoned'], true)) {
            return;
        }
        $payment->markFailed();
    }

    /* ================================================================== */
    /*  Remboursements & litiges                                          */
    /* ================================================================== */

    private function handleChargeRefunded(object $charge): void
    {
        $paymentIntentId = $charge->payment_intent ?? null;
        if (! is_string($paymentIntentId) || $paymentIntentId === '') {
            return;
        }

        // Nouveau workflow
        $submissionPayment = SubmissionPayment::where('stripe_payment_intent_id', $paymentIntentId)->first();
        if ($submissionPayment) {
            $submissionPayment->update(['status' => 'refunded', 'refunded_at' => now()]);
            return;
        }

        // Ancien workflow
        $payment = Payment::query()->where('stripe_payment_intent_id', $paymentIntentId)->first();
        if (! $payment || $payment->status === 'refunded') {
            return;
        }
        $payment->markRefunded(
            refundId: (string) ($charge->refunds->data[0]->id ?? 'stripe-refund'),
            reason: 'Refund confirmed by Stripe webhook',
        );
    }

    private function handleDisputeCreated(object $charge): void
    {
        Log::critical('Stripe dispute créée', [
            'charge_id'  => $charge->id ?? null,
            'amount'     => $charge->amount ?? null,
            'reason'     => $charge->dispute?->reason ?? null,
        ]);

        // Alerte admin immédiate
        $adminEmail = config('mail.admin_address');
        if ($adminEmail) {
            Notification::route('mail', $adminEmail)
                ->notify(new StripeDisputeAlertNotification($charge));
        }
    }
}
