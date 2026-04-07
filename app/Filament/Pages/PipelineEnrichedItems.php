<?php

namespace App\Filament\Pages;

use App\Jobs\GenerateArticleJob;
use App\Models\EnrichedItem;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PipelineEnrichedItems extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Analyses IA';

    protected static ?string $title = 'Analyses IA';

    protected string $view = 'filament.pages.pipeline-enriched-items';

    public function getStats(): array
    {
        return [
            'total' => EnrichedItem::count(),
            'avg_quality' => (int) round(EnrichedItem::avg('quality_score') ?? 0),
            'avg_seo' => (int) round(EnrichedItem::avg('seo_score') ?? 0),
            'today' => EnrichedItem::whereDate('enriched_at', today())->count(),
        ];
    }

    public function getItems(): array
    {
        return EnrichedItem::query()
            ->with(['rssItem.rssFeed.source', 'rssItem.category'])
            ->orderByDesc('enriched_at')
            ->limit(24)
            ->get()
            ->map(function (EnrichedItem $item): array {
                return [
                    'id' => $item->id,
                    'rss_item_id' => $item->rss_item_id,
                    'title' => $item->rssItem?->title ?? 'Sans titre',
                    'source' => $item->rssItem?->rssFeed?->source?->name ?? 'Source inconnue',
                    'category' => $item->rssItem?->category?->name ?? 'Sans catégorie',
                    'topic' => $item->primary_topic ?: 'Sujet non détecté',
                    'lead' => (string) str($item->lead ?: 'Aucun résumé disponible.')
                        ->stripTags()
                        ->squish()
                        ->limit(150),
                    'quality' => (int) ($item->quality_score ?? 0),
                    'seo' => (int) ($item->seo_score ?? 0),
                    'enriched_at' => $item->enriched_at?->diffForHumans() ?? 'Date inconnue',
                    'url' => $item->rssItem?->url,
                ];
            })
            ->toArray();
    }

    public function generateDraft(string $enrichedItemId): void
    {
        $item = EnrichedItem::query()->find($enrichedItemId);

        if (! $item || ! $item->rss_item_id) {
            Notification::make()
                ->danger()
                ->title('Analyse introuvable')
                ->send();

            return;
        }

        GenerateArticleJob::dispatch([$item->rss_item_id], null, null, 'standard');

        Notification::make()
            ->success()
            ->title('Génération lancée')
            ->body('Un brouillon IA va être créé à partir de cette analyse.')
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
                        ->title('Analyses actualisées')
                        ->send();
                }),
        ];
    }
}
