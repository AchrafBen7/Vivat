<?php

namespace App\Http\Controllers\Web;

use App\Models\Payment;
use App\Models\PublicationQuote;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContributorDashboardController extends ContributorBaseController
{
    public function dashboard(Request $request): Response
    {
        $user = $request->user();

        $pendingQuotesCount = PublicationQuote::query()
            ->whereHas('submission', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereNotIn('status', ['published', 'payment_succeeded'])
                    ->whereDoesntHave('submissionPayments', fn ($paymentQuery) => $paymentQuery->where('status', 'succeeded'));
            })
            ->whereIn('status', ['pending', 'sent'])
            ->count();

        $submissionsPaginator = Submission::where('user_id', $user->id)
            ->with(['category', 'reviewer', 'payment', 'latestSubmissionPayment', 'publishedArticle'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $submissions = $submissionsPaginator->getCollection()
            ->map(function ($submission) {
                $payment = $submission->latestSubmissionPayment ?? $submission->payment;

                return [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'slug' => $submission->slug,
                    'status' => $submission->status,
                    'status_label' => match ($submission->status) {
                        'draft' => 'Brouillon',
                        'submitted' => 'En vérification',
                        'pending' => 'En attente',
                        'under_review' => 'En relecture',
                        'changes_requested' => 'Corrections demandées',
                        'price_proposed', 'awaiting_payment' => 'Paiement requis',
                        'payment_pending' => 'Paiement en cours',
                        'payment_succeeded', 'published' => 'Publié',
                        'payment_failed' => 'Paiement échoué',
                        'payment_expired' => 'Offre expirée',
                        'payment_canceled' => 'Paiement annulé',
                        'payment_refunded' => 'Paiement remboursé',
                        'approved' => 'Publié',
                        'rejected' => 'Refusé',
                        default => ucfirst((string) $submission->status),
                    },
                    'created_at' => $submission->created_at?->format('d/m/Y'),
                    'reading_time' => $submission->reading_time,
                    'cover_image_url' => $submission->cover_image_url,
                    'excerpt' => $submission->excerpt,
                    'reviewer_notes' => $submission->reviewer_notes,
                    'reviewed_at' => $submission->reviewed_at?->format('d/m/Y H:i'),
                    'reviewer_name' => $submission->reviewer?->name,
                    'category' => $submission->category ? ['name' => $submission->category->name] : null,
                    'payment_status' => $payment?->status,
                    'payment_amount' => $payment?->amount ?? $payment?->amount_cents,
                    'payment_amount_label' => $payment?->formatted_amount
                        ?? ($payment
                            ? number_format($payment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($payment->currency ?: 'EUR')
                            : null),
                    'language' => $submission->language ?? 'fr',
                    'refund_reason' => $payment?->refund_reason,
                    'refunded_at' => $payment?->status === 'refunded' ? ($payment->refunded_at?->format('d/m/Y H:i') ?? $payment->updated_at?->format('d/m/Y H:i')) : null,
                    'refund_receipt_url' => $payment instanceof Payment && $payment->status === 'refunded'
                        ? route('contributor.payments.refund-receipt', ['payment' => $payment->id])
                        : null,
                    'preview_url' => route('contributor.articles.show', ['submission' => $submission->slug]),
                    'published_article_url' => $submission->publishedArticle?->slug ? url('/articles/'.$submission->publishedArticle->slug) : null,
                    'depublication_requested_at' => $submission->depublication_requested_at?->format('d/m/Y H:i'),
                    'edit_url' => route('contributor.articles.edit', ['submission' => $submission->slug]),
                    'can_delete' => $submission->status === 'draft' || $submission->status === 'rejected',
                    'delete_url' => ($submission->status === 'draft' || $submission->status === 'rejected')
                        ? route('contributor.articles.destroy', ['submission' => $submission->slug])
                        : null,
                    'request_unpublish_url' => in_array($submission->status, ['approved', 'published', 'payment_succeeded'], true) && $submission->published_article_id
                        ? route('contributor.articles.request-unpublish', ['submission' => $submission->slug])
                        : null,
                ];
            })
            ->all();

        $submissionsPaginator->setCollection(collect($submissions));

        return $this->renderContributorPage('articles', 'site.contributor.articles', [
            'user' => $user,
            'submissions' => $submissionsPaginator->items(),
            'pagination' => $submissionsPaginator,
            'pending_quotes_count' => $pendingQuotesCount,
        ]);
    }

    public function refundReceipt(Request $request, Payment $payment): Response
    {
        abort_unless(
            $request->user()
                && ($request->user()->id === $payment->user_id || $request->user()->hasRole('admin')),
            403
        );

        abort_unless($payment->status === 'refunded', 404);

        $payment->loadMissing('submission.category');

        return $this->renderContributorPage('payments', 'site.contributor.refund_receipt', [
            'payment' => $payment,
            'submission' => $payment->submission,
        ]);
    }

    public function paymentsHistory(Request $request): Response
    {
        $user = $request->user();

        $pendingQuotes = PublicationQuote::query()
            ->whereHas('submission', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereNotIn('status', ['published', 'payment_succeeded'])
                    ->whereDoesntHave('submissionPayments', fn ($paymentQuery) => $paymentQuery->where('status', 'succeeded'));
            })
            ->with(['submission.category'])
            ->whereIn('status', ['pending', 'sent'])
            ->orderByDesc('created_at')
            ->get();

        $pendingQuoteItems = $pendingQuotes->map(function (PublicationQuote $quote): array {
            $submission = $quote->submission;

            return [
                'type' => 'quote',
                'id' => $quote->id,
                'title' => $submission?->title ?: 'Article sans titre',
                'amount_label' => number_format($quote->amount_cents / 100, 2, ',', ' ').' '.strtoupper($quote->currency ?? 'EUR'),
                'status' => 'awaiting_payment',
                'status_label' => 'Paiement requis',
                'status_color' => 'amber',
                'status_description' => 'Notre équipe a évalué votre article et vous propose ce montant. Procédez au paiement pour déclencher la publication.',
                'note_to_author' => $quote->note_to_author,
                'expires_at' => $quote->expires_at?->format('d/m/Y à H:i'),
                'created_at' => $quote->created_at?->format('d/m/Y à H:i'),
                'category_name' => $submission?->category?->name,
                'submission_preview_url' => $submission?->slug ? route('contributor.articles.show', ['submission' => $submission->slug]) : null,
                'checkout_url' => $submission ? route('contributor.checkout.create', ['submission' => $submission->id]) : null,
                'submission_status_label' => 'En attente de paiement',
                'refund_reason' => null,
                'refund_receipt_url' => null,
                'submission_edit_url' => null,
                'published_article_url' => null,
            ];
        })->all();

        $paymentsPaginator = Payment::query()
            ->where('user_id', $user->id)
            ->with(['submission.category', 'submission.publishedArticle'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $payments = $paymentsPaginator->getCollection()
            ->map(function (Payment $payment) {
                $submission = $payment->submission;
                $statusMeta = $this->contributorPaymentStatusMeta($payment);

                return [
                    'id' => $payment->id,
                    'title' => $submission?->title ?: 'Paiement sans soumission active',
                    'amount_label' => number_format($payment->amount / 100, 2, ',', ' ').' '.strtoupper($payment->currency ?: 'EUR'),
                    'status' => $payment->status,
                    'status_label' => $statusMeta['label'],
                    'status_color' => $statusMeta['color'],
                    'status_description' => $statusMeta['description'],
                    'created_at' => $payment->created_at?->format('d/m/Y à H:i'),
                    'submission_status_label' => $this->contributorSubmissionStatusLabel($submission?->status),
                    'category_name' => $submission?->category?->name,
                    'refund_reason' => $payment->refund_reason,
                    'refund_receipt_url' => $payment->status === 'refunded'
                        ? route('contributor.payments.refund-receipt', ['payment' => $payment->id])
                        : null,
                    'submission_preview_url' => $submission?->slug
                        ? route('contributor.articles.show', ['submission' => $submission->slug])
                        : null,
                    'submission_edit_url' => $submission?->slug
                        ? route('contributor.articles.edit', ['submission' => $submission->slug])
                        : null,
                    'published_article_url' => $submission?->publishedArticle?->slug
                        ? url('/articles/'.$submission->publishedArticle->slug)
                        : null,
                ];
            })
            ->all();

        $paymentsPaginator->setCollection(collect($payments));

        return $this->renderContributorPage('payments', 'site.contributor.payments', [
            'user' => $user,
            'payments' => array_merge($pendingQuoteItems, $paymentsPaginator->items()),
            'pending_quotes_count' => count($pendingQuoteItems),
            'pagination' => $paymentsPaginator,
        ]);
    }
}
