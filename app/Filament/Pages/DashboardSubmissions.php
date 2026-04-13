<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Submissions\SubmissionResource;
use App\Models\Submission;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class DashboardSubmissions extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Soumissions';

    protected static ?string $title = 'Soumissions';

    protected string $view = 'filament.pages.dashboard-submissions';

    public string $search = '';

    public string $status = '';

    public static function statusMeta(string $status): array
    {
        return match ($status) {
            'submitted', 'pending' => ['label' => 'Soumise', 'bg' => '#fffbeb', 'text' => '#92400e'],
            'under_review' => ['label' => 'En revue', 'bg' => '#eff6ff', 'text' => '#1d4ed8'],
            'changes_requested' => ['label' => 'Corrections demandées', 'bg' => '#fff7ed', 'text' => '#c2410c'],
            'price_proposed' => ['label' => 'Prix proposé', 'bg' => '#ecfeff', 'text' => '#0f766e'],
            'awaiting_payment' => ['label' => 'En attente de paiement', 'bg' => '#fef3c7', 'text' => '#92400e'],
            'payment_pending' => ['label' => 'Paiement en cours', 'bg' => '#fff7ed', 'text' => '#c2410c'],
            'payment_succeeded' => ['label' => 'Paiement reçu', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'payment_failed' => ['label' => 'Paiement échoué', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'payment_expired' => ['label' => 'Offre expirée', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'payment_canceled' => ['label' => 'Paiement annulé', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'payment_refunded' => ['label' => 'Paiement remboursé', 'bg' => '#f3f4f6', 'text' => '#374151'],
            'published', 'approved' => ['label' => 'Publiée', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'rejected' => ['label' => 'Rejetée', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            default => ['label' => ucfirst($status), 'bg' => '#EBF1EF', 'text' => '#004241'],
        };
    }

    public static function paymentMeta(?string $status): ?array
    {
        return match ($status) {
            'pending' => ['label' => 'Paiement créé', 'bg' => '#fff7ed', 'text' => '#c2410c'],
            'processing' => ['label' => 'Paiement en cours', 'bg' => '#fff7ed', 'text' => '#c2410c'],
            'succeeded' => ['label' => 'Paiement réussi', 'bg' => '#ecfdf5', 'text' => '#065f46'],
            'failed' => ['label' => 'Paiement échoué', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'canceled' => ['label' => 'Paiement annulé', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'expired' => ['label' => 'Paiement expiré', 'bg' => '#fef2f2', 'text' => '#991b1b'],
            'refunded' => ['label' => 'Remboursé', 'bg' => '#f3f4f6', 'text' => '#374151'],
            'disputed' => ['label' => 'Litige', 'bg' => '#fff7ed', 'text' => '#c2410c'],
            default => null,
        };
    }

    public function getStats(): array
    {
        return [
            'submitted' => Submission::whereIn('status', ['submitted', 'pending'])->count(),
            'review' => Submission::where('status', 'under_review')->count(),
            'changes_requested' => Submission::where('status', 'changes_requested')->count(),
            'total_to_review' => Submission::whereIn('status', ['submitted', 'pending', 'under_review', 'changes_requested'])->count(),
            'today' => Submission::whereDate('created_at', today())->count(),
        ];
    }

    public function getSubmissions(): array
    {
        return Submission::query()
            ->with(['user', 'category', 'reviewer', 'quote.preset', 'latestSubmissionPayment'])
            ->whereIn('status', ['submitted', 'pending', 'under_review', 'changes_requested'])
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', function ($query) {
                $search = trim($this->search);
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('title', 'like', '%' . $search . '%')
                        ->orWhere('excerpt', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                })->orWhereHas('user', function ($userQuery) use ($search): void {
                    $userQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw("FIELD(status, 'submitted', 'pending', 'under_review', 'changes_requested')")
            ->orderByDesc('created_at')
            ->limit(24)
            ->get()
            ->map(function (Submission $submission): array {
                return [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'cover' => $submission->cover_image_url,
                    'author' => $submission->user?->name ?? 'Auteur inconnu',
                    'author_email' => $submission->user?->email ?? '',
                    'category' => $submission->category?->name ?? 'Sans catégorie',
                    'status' => $submission->status,
                    'reading_time' => (int) ($submission->reading_time ?: 5),
                    'created_at' => $submission->created_at?->format('d/m/Y à H:i') ?? 'Date inconnue',
                    'reviewer' => $submission->reviewer?->name,
                    'reviewed_at' => $submission->reviewed_at?->format('d/m/Y à H:i'),
                    'excerpt' => (string) str($submission->excerpt ?: $submission->content ?: 'Aucun extrait disponible.')
                        ->stripTags()
                        ->squish()
                        ->limit(150),
                    'quote_amount' => $submission->quote?->formatted_amount,
                    'quote_expires_at' => $submission->quote?->expires_at?->format('d/m/Y à H:i'),
                    'quote_label' => $submission->quote?->preset?->label,
                    'payment_status' => $submission->latestSubmissionPayment?->status,
                    'payment_amount' => $submission->latestSubmissionPayment?->formatted_amount,
                    'preview_url' => route('contributor.articles.show', ['submission' => $submission->slug]),
                    'moderate_url' => SubmissionResource::getUrl('view', ['record' => $submission]),
                ];
            })
            ->toArray();
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
