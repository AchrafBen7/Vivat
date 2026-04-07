<?php

namespace App\Filament\Pages;

use App\Jobs\EnrichContentJob;
use App\Jobs\FetchRssFeedJob;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Models\Source;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PipelineStep1 extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedRss;

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Sources & repérage';

    protected static ?string $title = '';

    protected string $view = 'filament.pages.pipeline-step1';

    public function getStats(): array
    {
        return [
            'sources' => Source::count(),
            'feeds' => RssFeed::where('is_active', true)->count(),
            'items_total' => RssItem::count(),
            'items_new' => RssItem::where('status', 'new')->count(),
            'items_today' => RssItem::whereDate('created_at', today())->count(),
        ];
    }

    public function getRecentFeeds(): array
    {
        return RssFeed::with('source')
            ->where('is_active', true)
            ->orderByDesc('last_fetched_at')
            ->limit(5)
            ->get()
            ->map(fn (RssFeed $feed) => [
                'source' => $feed->source?->name ?? 'Inconnu',
                'url' => $feed->feed_url,
                'last_fetched' => $feed->last_fetched_at?->diffForHumans() ?? 'Jamais',
                'id' => $feed->id,
            ])
            ->toArray();
    }

    public function getRecentItems(): array
    {
        return RssItem::with('rssFeed.source')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (RssItem $item) => [
                'title' => $item->title,
                'source' => $item->rssFeed?->source?->name ?? 'Inconnu',
                'status' => $item->status,
                'created_at' => $item->created_at?->diffForHumans(),
            ])
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetch_all')
                ->label('1. Lancer un fetch')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->action(function (): void {
                    $feeds = RssFeed::where('is_active', true)->get();
                    $feeds->each(fn (RssFeed $feed) => FetchRssFeedJob::dispatch($feed));

                    Notification::make()
                        ->success()
                        ->title('Fetch lancé')
                        ->body($feeds->count().' flux envoyés en queue.')
                        ->send();
                }),
            Action::make('enrich_new')
                ->label('2. Enrichir les nouveaux items')
                ->icon(Heroicon::OutlinedCpuChip)
                ->color('gray')
                ->action(function (): void {
                    $limit = max(1, (int) config('pipeline_schedule.enrich_items.limit', 5));
                    $delaySeconds = max(1, (int) config('pipeline_schedule.enrich_items.delay_seconds', 5));

                    $items = RssItem::where('status', 'new')
                        ->orderByDesc('fetched_at')
                        ->orderByDesc('created_at')
                        ->limit($limit)
                        ->get();

                    $items->each(function (RssItem $item, int $index) use ($delaySeconds): void {
                        EnrichContentJob::dispatch($item)
                            ->onQueue('enrichment')
                            ->delay(now()->addSeconds($index * $delaySeconds));
                    });

                    Notification::make()
                        ->success()
                        ->title('Enrichissement lancé')
                        ->body($items->count().' nouveaux items envoyés en queue.')
                        ->send();
                }),
        ];
    }
}
