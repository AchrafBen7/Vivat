<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use App\Models\Submission;
use App\Services\PaymentRefundService;
use App\Services\SubmissionPaymentRefundService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class DashboardPayments extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Paiements';

    protected static ?string $title = 'Paiements';

    protected string $view = 'filament.pages.dashboard-payments';

    public string $search = '';

    public string $status = '';

    public function getStats(): array
    {
        return [
            'paid' => Submission::whereIn('status', ['payment_succeeded', 'published', 'approved'])->count(),
            'published' => Submission::whereIn('status', ['published', 'approved'])->count(),
            'today' => Submission::whereDate('updated_at', today())
                ->whereIn('status', ['payment_succeeded', 'published', 'approved'])
                ->count(),
        ];
    }

    public function getPayments(): array
    {
        return Submission::query()
            ->with([
                'user',
                'category',
                'publishedArticle',
                'quote.preset',
                'latestSubmissionPayment',
                'payment',
            ])
            ->whereIn('status', ['payment_succeeded', 'published', 'approved'])
            ->when($this->status !== '', function ($query) {
                $status = $this->status;
                if ($status === 'published') {
                    $query->whereIn('status', ['published', 'approved']);
                    return;
                }
                if ($status === 'payment_succeeded') {
                    $query->where('status', 'payment_succeeded');
                    return;
                }
            })
            ->when($this->search !== '', function ($query) {
                $search = trim($this->search);

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%'));
                });
            })
            ->orderByRaw("FIELD(status, 'payment_succeeded', 'published', 'approved')")
            ->orderByDesc('updated_at')
            ->limit(24)
            ->get()
            ->map(function (Submission $submission): array {
                $legacyPayment = $submission->payment;
                $workflowPayment = $submission->latestSubmissionPayment;
                $quote = $submission->quote;

                $paymentStatus = $workflowPayment?->status ?? $legacyPayment?->status ?? 'succeeded';

                $submissionStatus = match ($submission->status) {
                    'payment_succeeded' => 'Payé, publication en cours',
                    'published', 'approved' => 'Publié',
                    default => $submission->status ?: 'Aucune soumission',
                };

                $displayAmount = $workflowPayment?->formatted_amount
                    ?? $quote?->formatted_amount
                    ?? ($legacyPayment ? number_format($legacyPayment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($legacyPayment->currency) : '');

                return [
                    'id' => $workflowPayment?->id ?? $legacyPayment?->id ?? $submission->id,
                    'title' => $submission->title ?? 'Soumission sans titre',
                    'author' => $submission->user?->name ?? 'Utilisateur inconnu',
                    'author_email' => $submission->user?->email ?? '',
                    'category' => $submission->category?->name ?? 'Sans catégorie',
                    'amount' => $displayAmount,
                    'status' => $paymentStatus,
                    'submission_status' => $submissionStatus,
                    'refund_reason' => $workflowPayment?->refund_reason ?? $legacyPayment?->refund_reason,
                    'created_at' => ($workflowPayment?->updated_at ?? $workflowPayment?->created_at ?? $quote?->created_at ?? $legacyPayment?->updated_at ?? $legacyPayment?->created_at ?? $submission->updated_at ?? $submission->created_at)?->format('d/m/Y à H:i') ?? 'Date inconnue',
                    'submission_url' => \App\Filament\Resources\Submissions\SubmissionResource::getUrl('view', ['record' => $submission]),
                    'article_url' => $submission->publishedArticle?->slug ? url('/articles/' . $submission->publishedArticle->slug) : null,
                    'can_refund' => $workflowPayment?->isRefundable() || ($legacyPayment?->isRefundable() ?? false),
                    'submission_payment_id' => $workflowPayment?->id,
                    'legacy_payment_id' => $legacyPayment?->id,
                ];
            })
            ->toArray();
    }

    public function refundPayment(string $paymentId, ?string $submissionPaymentId = null): void
    {
        if ($submissionPaymentId) {
            $submissionPayment = \App\Models\SubmissionPayment::query()->with('submission.user')->find($submissionPaymentId);

            if (! $submissionPayment) {
                Notification::make()
                    ->danger()
                    ->title('Paiement introuvable')
                    ->send();

                return;
            }

            if (! $submissionPayment->isRefundable()) {
                Notification::make()
                    ->warning()
                    ->title('Remboursement impossible')
                    ->body('Ce paiement ne peut pas être remboursé dans son état actuel.')
                    ->send();

                return;
            }

            app(SubmissionPaymentRefundService::class)->refund($submissionPayment, 'Article refusé', auth()->user());

            Notification::make()
                ->success()
                ->title('Paiement remboursé')
                ->body("Le remboursement a été traité. L'article lié a été dépublié s'il était déjà publié.")
                ->send();

            return;
        }

        $payment = Payment::query()->with('submission.user')->find($paymentId);

        if (! $payment) {
            Notification::make()
                ->danger()
                ->title('Paiement introuvable')
                ->send();

            return;
        }

        if (! $payment->isRefundable()) {
            Notification::make()
                ->warning()
                ->title('Remboursement impossible')
                ->body('Ce paiement ne peut pas être remboursé dans son état actuel.')
                ->send();

            return;
        }

        app(PaymentRefundService::class)->refund($payment, 'Article refusé');

        Notification::make()
            ->success()
            ->title('Paiement remboursé')
            ->body('Le remboursement a été traité.')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Rafraîchir')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    Notification::make()
                        ->success()
                        ->title('Liste actualisée')
                        ->send();
                }),
        ];
    }
}
