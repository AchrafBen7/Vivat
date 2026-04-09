<?php

namespace App\Filament\Pages;

use App\Models\Payment;
use App\Models\Submission;
use App\Services\PaymentRefundService;
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
            'paid' => Payment::where('status', 'paid')->count(),
            'refunded' => Payment::where('status', 'refunded')->count(),
            'today' => Payment::whereDate('created_at', today())->count(),
        ];
    }

    public function getPayments(): array
    {
        return Submission::query()
            ->with([
                'user',
                'quote.preset',
                'latestSubmissionPayment',
                'payment',
            ])
            ->where('status', '!=', 'draft')
            ->when($this->status !== '', function ($query) {
                $status = $this->status;

                $query->where(function ($innerQuery) use ($status): void {
                    $innerQuery
                        ->whereHas('latestSubmissionPayment', fn ($paymentQuery) => $paymentQuery->where('status', $status))
                        ->orWhereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', $status));
                });
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
            ->orderByRaw("FIELD(status, 'awaiting_payment', 'payment_pending', 'payment_failed', 'payment_succeeded', 'published', 'rejected')")
            ->orderByDesc('created_at')
            ->limit(24)
            ->get()
            ->map(function (Submission $submission): array {
                $legacyPayment = $submission->payment;
                $workflowPayment = $submission->latestSubmissionPayment;
                $quote = $submission->quote;

                $paymentStatus = $workflowPayment?->status ?? $legacyPayment?->status ?? 'pending';

                $submissionStatus = match ($submission->status) {
                    'submitted' => 'Soumise',
                    'under_review' => 'En revue',
                    'changes_requested' => 'Corrections demandées',
                    'price_proposed' => 'Prix proposé',
                    'awaiting_payment' => 'En attente de paiement',
                    'payment_pending' => 'Paiement en cours',
                    'payment_failed' => 'Paiement échoué',
                    'payment_succeeded' => 'Paiement confirmé',
                    'published' => 'Publiée',
                    'rejected' => 'Rejetée',
                    'approved' => 'Approuvée',
                    'pending' => 'En attente',
                    default => $submission->status ?: 'Aucune soumission',
                };

                $displayAmount = $workflowPayment?->formatted_amount
                    ?? $quote?->formatted_amount
                    ?? ($legacyPayment ? number_format($legacyPayment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($legacyPayment->currency) : '—');

                return [
                    'id' => $workflowPayment?->id ?? $legacyPayment?->id ?? $submission->id,
                    'title' => $submission->title ?? 'Soumission sans titre',
                    'author' => $submission->user?->name ?? 'Utilisateur inconnu',
                    'author_email' => $submission->user?->email ?? '',
                    'amount' => $displayAmount,
                    'status' => $paymentStatus,
                    'submission_status' => $submissionStatus,
                    'refund_reason' => $legacyPayment?->refund_reason,
                    'created_at' => ($workflowPayment?->created_at ?? $quote?->created_at ?? $legacyPayment?->created_at ?? $submission->created_at)?->format('d/m/Y à H:i') ?? 'Date inconnue',
                    'submission_url' => \App\Filament\Resources\Submissions\SubmissionResource::getUrl('view', ['record' => $submission]),
                    'can_refund' => $legacyPayment?->isRefundable() ?? false,
                    'legacy_payment_id' => $legacyPayment?->id,
                ];
            })
            ->toArray();
    }

    public function refundPayment(string $paymentId): void
    {
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
