<?php

namespace App\Filament\Pages;

use App\Jobs\EnrichContentJob;
use App\Models\RssItem;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PipelineRssItems extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static string|\UnitEnum|null $navigationGroup = 'Brouillons IA manuels';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Articles repérés';

    protected static ?string $title = 'Articles repérés';

    protected string $view = 'filament.pages.pipeline-rss-items';

    public string $enrichmentFilter = '';

    public function getStats(): array
    {
        return [
            'new' => RssItem::where('status', 'new')->count(),
            'enriching' => RssItem::where('status', 'enriching')->count(),
            'enriched' => RssItem::where('status', 'enriched')->count(),
            'today' => RssItem::whereDate('created_at', today())->count(),
        ];
    }

    public function getItems(): array
    {
        return RssItem::query()
            ->with(['rssFeed.source', 'category', 'enrichedItem'])
            ->when($this->enrichmentFilter === 'enriched', fn ($query) => $query->where('status', 'enriched'))
            ->when($this->enrichmentFilter === 'not_enriched', fn ($query) => $query->whereIn('status', ['new', 'enriching', 'failed']))
            ->orderByRaw("FIELD(status, 'new', 'enriching', 'enriched', 'used', 'failed')")
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->limit(24)
            ->get()
            ->map(function (RssItem $item): array {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'source' => $item->rssFeed?->source?->name ?? 'Source inconnue',
                    'category' => $item->category?->name ?? 'Sans catégorie',
                    'status' => $item->status,
                    'topic' => $item->enrichedItem?->primary_topic ?: 'Pas encore enrichi',
                    'description' => (string) str($item->description ?: 'Aucune description.')
                        ->stripTags()
                        ->squish()
                        ->limit(150),
                    'published_at' => $item->published_at?->format('d/m/Y H:i') ?? 'Date inconnue',
                    'fetched_at' => $item->fetched_at?->diffForHumans() ?? 'Inconnu',
                    'url' => $item->url,
                    'can_enrich' => in_array($item->status, ['new', 'failed'], true),
                ];
            })
            ->toArray();
    }

    public function enrichItem(string $rssItemId): void
    {
        $item = RssItem::query()->find($rssItemId);

        if (! $item) {
            Notification::make()
                ->danger()
                ->title('Article introuvable')
                ->send();

            return;
        }

        EnrichContentJob::dispatch($item)->onQueue('enrichment');

        Notification::make()
            ->success()
            ->title('Enrichissement lancé')
            ->body("L'article a été ajouté à la queue IA.")
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enrichPending')
                ->label('Enrichir les nouveaux')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('primary')
                ->action(function (): void {
                    $limit = max(1, (int) config('pipeline_schedule.enrich_items.limit', 3));
                    $delay = max(1, (int) config('pipeline_schedule.enrich_items.delay_seconds', 10));
                    $items = RssItem::new()
                        ->orderByDesc('fetched_at')
                        ->orderByDesc('created_at')
                        ->limit($limit)
                        ->get();

                    foreach ($items as $index => $item) {
                        EnrichContentJob::dispatch($item)
                            ->onQueue('enrichment')
                            ->delay(now()->addSeconds($index * $delay));
                    }

                    Notification::make()
                        ->success()
                        ->title('Enrichissement lancé')
                        ->body($items->isEmpty()
                            ? 'Aucun item nouveau à enrichir.'
                            : $items->count().' item(s) ont été ajoutés à la queue IA.')
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
