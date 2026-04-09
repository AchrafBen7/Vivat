<?php

namespace App\Filament\Pages;

use App\Jobs\FetchRssFeedJob;
use App\Models\RssFeed;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PipelineRssFeeds extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Flux automatiques';

    protected static ?string $title = 'Flux automatiques';

    protected string $view = 'filament.pages.pipeline-rss-feeds';

    public function getStats(): array
    {
        return [
            'active' => RssFeed::where('is_active', true)->count(),
            'inactive' => RssFeed::where('is_active', false)->count(),
            'items' => RssFeed::withCount('items')->get()->sum('items_count'),
            'fetched_today' => RssFeed::whereDate('last_fetched_at', today())->count(),
        ];
    }

    public function getFeeds(): array
    {
        return RssFeed::query()
            ->with(['source', 'category'])
            ->withCount('items')
            ->orderByDesc('is_active')
            ->orderByDesc('last_fetched_at')
            ->limit(24)
            ->get()
            ->map(function (RssFeed $feed): array {
                return [
                    'id' => $feed->id,
                    'url' => $feed->feed_url,
                    'source' => $feed->source?->name ?? 'Source inconnue',
                    'category' => $feed->category?->name ?? 'Sans catégorie',
                    'interval' => (int) ($feed->fetch_interval_minutes ?: 60),
                    'active' => (bool) $feed->is_active,
                    'items_count' => (int) ($feed->items_count ?? 0),
                    'last_fetched' => $feed->last_fetched_at?->diffForHumans() ?? 'Jamais',
                ];
            })
            ->toArray();
    }

    public function fetchFeed(string $feedId): void
    {
        $feed = RssFeed::query()->find($feedId);

        if (! $feed) {
            Notification::make()
                ->danger()
                ->title('Flux introuvable')
                ->send();

            return;
        }

        FetchRssFeedJob::dispatch($feed);

        Notification::make()
            ->success()
            ->title('Fetch lancé')
            ->body('Le flux a été ajouté à la queue RSS.')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetchDueFeeds')
                ->label('Lancer les fetchs dus')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->action(function (): void {
                    $feeds = RssFeed::dueForFetch()->get();

                    foreach ($feeds as $feed) {
                        FetchRssFeedJob::dispatch($feed);
                    }

                    Notification::make()
                        ->success()
                        ->title('Fetch lancé')
                        ->body($feeds->isEmpty()
                            ? "Aucun flux n'était à rafraîchir."
                            : $feeds->count().' flux ont été ajoutés à la queue RSS.')
                        ->send();
                }),
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
