<?php

namespace App\Filament\Pages;

use App\Jobs\EnrichContentJob;
use App\Jobs\GenerateArticleJob;
use App\Models\Cluster;
use App\Models\ClusterItem;
use App\Models\EnrichedItem;
use App\Models\RssItem;
use App\Services\ArticleSelectionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PipelineStep2 extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Analyse des sujets';

    protected static ?string $title = '';

    protected string $view = 'filament.pages.pipeline-step2';

    public array $proposals = [];

    public function mount(ArticleSelectionService $selector): void
    {
        $this->proposals = $selector->selectBestTopics(4);
    }

    public function getStats(): array
    {
        return [
            'enriched' => EnrichedItem::count(),
            'pending' => RssItem::where('status', 'new')->count(),
            'avg_quality' => round(EnrichedItem::avg('quality_score') ?? 0),
            'clusters' => Cluster::count(),
        ];
    }

    public function getRecentEnriched(): array
    {
        return EnrichedItem::with('rssItem')
            ->orderByDesc('enriched_at')
            ->limit(5)
            ->get()
            ->map(fn (EnrichedItem $item) => [
                'title' => $item->rssItem?->title ?? 'Sans titre',
                'quality' => $item->quality_score,
                'lead' => \Illuminate\Support\Str::limit($item->lead ?? '', 80),
                'enriched_at' => $item->enriched_at?->diffForHumans(),
            ])
            ->toArray();
    }

    public function refreshProposals(): void
    {
        $this->proposals = app(ArticleSelectionService::class)->selectBestTopics(4);

        Notification::make()
            ->success()
            ->title('Propositions actualisées')
            ->send();
    }

    public function launchEnrichment(): void
    {
        $items = RssItem::where('status', 'new')->limit(5)->get();
        $items->each(function (RssItem $item, int $index) {
            EnrichContentJob::dispatch($item)
                ->onQueue('enrichment')
                ->delay(now()->addSeconds($index * 5));
        });

        Notification::make()
            ->success()
            ->title('Enrichissement lancé')
            ->body($items->count().' items envoyés en queue.')
            ->send();
    }

    public function generateProposal(int $index): void
    {
        $proposal = $this->proposals[$index] ?? null;

        if (! is_array($proposal) || empty($proposal['items'])) {
            Notification::make()->danger()->title('Proposition introuvable')->send();

            return;
        }

        $itemIds = collect($proposal['items'])->pluck('id')->filter()->values()->all();

        if ($itemIds === []) {
            Notification::make()->danger()->title('Aucun item exploitable')->send();

            return;
        }

        $cluster = Cluster::create([
            'category_id' => $proposal['category']['id'] ?? null,
            'label' => (string) ($proposal['topic'] ?? 'Sujet IA'),
            'keywords' => collect($proposal['seo_keywords'] ?? [])
                ->map(fn ($kw) => is_array($kw) ? ($kw['word'] ?? null) : $kw)
                ->filter()->values()->all(),
            'status' => 'pending',
        ]);

        foreach ($itemIds as $itemId) {
            ClusterItem::create(['cluster_id' => $cluster->id, 'rss_item_id' => $itemId]);
        }

        GenerateArticleJob::dispatch(
            $itemIds,
            $proposal['category']['id'] ?? null,
            null,
            $proposal['suggested_article_type'] ?? 'standard',
            isset($proposal['suggested_min_words']) ? (int) $proposal['suggested_min_words'] : null,
            isset($proposal['suggested_max_words']) ? (int) $proposal['suggested_max_words'] : null,
            $proposal['context_priority'] ?? null,
            $cluster->id,
        );

        Notification::make()
            ->success()
            ->title('Génération lancée')
            ->body('L\'article est en cours de création par l\'IA.')
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enrich')
                ->label('Enrichir 5 items')
                ->icon(Heroicon::OutlinedCpuChip)
                ->color('gray')
                ->action(fn () => $this->launchEnrichment()),
            Action::make('refresh')
                ->label('Rafraîchir idées')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->action(fn () => $this->refreshProposals()),
        ];
    }
}
