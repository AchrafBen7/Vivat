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

    public string $status = 'pending';

    public function getStats(): array
    {
        return [
            'pending' => Submission::where('status', 'pending')->count(),
            'approved' => Submission::where('status', 'approved')->count(),
            'rejected' => Submission::where('status', 'rejected')->count(),
            'today' => Submission::whereDate('created_at', today())->count(),
        ];
    }

    public function getSubmissions(): array
    {
        return Submission::query()
            ->with(['user', 'category', 'payment', 'reviewer'])
            ->whereIn('status', ['pending', 'approved', 'rejected'])
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
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
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
                    'payment_status' => $submission->payment?->status,
                    'payment_amount' => $submission->payment
                        ? number_format($submission->payment->amount / 100, 2, ',', ' ') . ' ' . strtoupper($submission->payment->currency)
                        : null,
                    'preview_url' => route('contributor.articles.show', ['submission' => $submission->slug]),
                    'moderate_url' => DashboardSubmissionView::getUrl(['record' => $submission]),
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
