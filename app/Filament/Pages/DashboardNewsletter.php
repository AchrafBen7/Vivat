<?php

namespace App\Filament\Pages;

use App\Models\NewsletterSubscriber;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class DashboardNewsletter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Editorial';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Newsletter';

    protected static ?string $title = 'Newsletter';

    protected string $view = 'filament.pages.dashboard-newsletter';

    public string $search = '';

    public string $state = '';

    public function getStats(): array
    {
        return [
            'active' => NewsletterSubscriber::where('confirmed', true)->whereNull('unsubscribed_at')->count(),
            'pending' => NewsletterSubscriber::where('confirmed', false)->whereNull('unsubscribed_at')->count(),
            'unsubscribed' => NewsletterSubscriber::whereNotNull('unsubscribed_at')->count(),
            'today' => NewsletterSubscriber::whereDate('created_at', today())->count(),
        ];
    }

    public function getSubscribers(): array
    {
        return NewsletterSubscriber::query()
            ->when($this->state !== '', function ($query) {
                match ($this->state) {
                    'active' => $query->where('confirmed', true)->whereNull('unsubscribed_at'),
                    'pending' => $query->where('confirmed', false)->whereNull('unsubscribed_at'),
                    'unsubscribed' => $query->whereNotNull('unsubscribed_at'),
                    default => $query,
                };
            })
            ->when($this->search !== '', function ($query) {
                $search = trim($this->search);
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery
                        ->where('email', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%');
                });
            })
            ->orderByRaw('CASE WHEN unsubscribed_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('confirmed')
            ->orderByDesc('created_at')
            ->limit(24)
            ->get()
            ->map(function (NewsletterSubscriber $subscriber): array {
                $interests = collect($subscriber->interests ?? [])
                    ->filter()
                    ->map(fn (string $interest): string => str($interest)->replace('-', ' ')->title())
                    ->take(3)
                    ->values()
                    ->all();

                return [
                    'id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'name' => trim((string) ($subscriber->name ?? '')),
                    'status' => $subscriber->unsubscribed_at
                        ? 'unsubscribed'
                        : ($subscriber->confirmed ? 'active' : 'pending'),
                    'joined_at' => $subscriber->created_at?->format('d/m/Y') ?? 'Date inconnue',
                    'confirmed_at' => $subscriber->confirmed_at?->format('d/m/Y à H:i'),
                    'unsubscribed_at' => $subscriber->unsubscribed_at?->format('d/m/Y à H:i'),
                    'interests' => $interests,
                    'can_unsubscribe' => $subscriber->unsubscribed_at === null,
                ];
            })
            ->toArray();
    }

    public function unsubscribeSubscriber(string $subscriberId): void
    {
        $subscriber = NewsletterSubscriber::query()->find($subscriberId);

        if (! $subscriber) {
            Notification::make()
                ->danger()
                ->title('Abonné introuvable')
                ->send();

            return;
        }

        if ($subscriber->unsubscribed_at !== null) {
            Notification::make()
                ->warning()
                ->title('Déjà désinscrit')
                ->send();

            return;
        }

        $subscriber->unsubscribe();

        Notification::make()
            ->success()
            ->title('Abonné désinscrit')
            ->body('La désinscription a bien été prise en compte.')
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
