<?php

namespace App\Filament\Pages;

use App\Models\Payment;
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
            'pending' => Payment::where('status', 'pending')->count(),
            'today' => Payment::whereDate('created_at', today())->count(),
        ];
    }

    public function getPayments(): array
    {
        return Payment::query()
            ->with(['user', 'submission'])
            ->where(function ($innerQuery) {
                $innerQuery
                    ->where('status', '!=', 'pending')
                    ->orWhereHas('submission', fn ($submissionQuery) => $submissionQuery->where('status', '!=', 'draft'));
            })
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', function ($query) {
                $search = trim($this->search);

                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->whereHas('submission', fn ($submissionQuery) => $submissionQuery->where('title', 'like', '%' . $search . '%'))
                        ->orWhereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%'));
                });
            })
            ->orderByRaw('CASE WHEN submission_id IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw("FIELD(status, 'pending', 'paid', 'refunded', 'failed', 'abandoned')")
            ->orderByDesc('created_at')
            ->limit(24)
            ->get()
            ->map(function (Payment $payment): array {
                $submissionStatus = match ($payment->submission?->status) {
                    'draft' => 'Brouillon',
                    'pending' => 'En attente',
                    'approved' => 'Approuvée',
                    'rejected' => 'Rejetée',
                    default => $payment->submission?->status ?? 'Aucune soumission',
                };

                return [
                    'id' => $payment->id,
                    'title' => $payment->submission?->title ?? 'Paiement abandonné ou non relié',
                    'author' => $payment->user?->name ?? 'Utilisateur inconnu',
                    'author_email' => $payment->user?->email ?? '',
                    'amount' => number_format($payment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($payment->currency),
                    'status' => $payment->status,
                    'submission_status' => $submissionStatus,
                    'refund_reason' => $payment->refund_reason,
                    'created_at' => $payment->created_at?->format('d/m/Y à H:i') ?? 'Date inconnue',
                    'submission_url' => $payment->submission
                        ? DashboardSubmissionView::getUrl(['record' => $payment->submission])
                        : null,
                    'can_refund' => $payment->isRefundable(),
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
