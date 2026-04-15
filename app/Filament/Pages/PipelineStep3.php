<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Jobs\EnrichContentJob;
use App\Jobs\FetchRssFeedJob;
use App\Jobs\GenerateArticleJob;
use App\Models\Article;
use App\Models\Category;
use App\Models\Cluster;
use App\Models\ClusterItem;
use App\Models\EnrichedItem;
use App\Models\PipelineJob;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Services\ArticleSelectionService;
use App\Services\PipelineAutomationState;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

class PipelineStep3 extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Brouillons IA manuels';

    protected static ?int $navigationSort = 12;

    protected static ?string $navigationLabel = 'Brouillons IA';

    protected static ?string $title = '';

    public static function getNavigationBadge(): ?string
    {
        $count = Article::where('status', 'draft')
            ->whereNotNull('cluster_id')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    protected string $view = 'filament.pages.pipeline-step3';

    public bool $publishModalOpen = false;

    public ?string $publishArticleId = null;

    public ?string $publishCategoryId = null;

    public string $publishArticleType = 'standard';

    public function getStats(): array
    {
        return [
            'drafts' => Article::where('status', 'draft')->count(),
            'published' => Article::where('status', 'published')->count(),
            'total' => Article::count(),
            'today' => Article::whereDate('created_at', today())->count(),
        ];
    }

    public function getDraftArticles(): array
    {
        return Article::where('status', 'draft')
            ->with('category')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Article $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'slug' => $a->slug,
                'category' => $a->category?->name ?? 'Non classé',
                'cover' => $a->cover_image_url,
                'word_count' => str_word_count(strip_tags($a->content ?? '')),
                'created_at' => $a->created_at?->diffForHumans(),
                'edit_url' => ArticleResource::getUrl('edit', ['record' => $a]),
                'preview_url' => route('articles.preview.admin', [
                    'article' => $a->slug,
                    'back' => static::getUrl(),
                    'back_label' => 'Retour à Brouillons AI',
                ]),
                'is_publishable' => $a->isPublishable(),
            ])
            ->toArray();
    }

    public function getCategoryOptions(): array
    {
        return Category::query()
            ->orderedForHome()
            ->pluck('name', 'id')
            ->all();
    }

    public function getArticleTypeOptions(): array
    {
        return [
            'standard' => 'Article standard',
            'hot_news' => 'Hot news',
            'long_form' => 'Long format',
        ];
    }

    public function deleteDraft(string $articleId): void
    {
        $article = Article::query()->find($articleId);

        if (! $article || $article->status !== 'draft') {
            Notification::make()
                ->danger()
                ->title('Suppression impossible')
                ->body('Seuls les brouillons peuvent être supprimés depuis cette page.')
                ->send();

            return;
        }

        $article->articleSources()->delete();
        $article->delete();

        Notification::make()
            ->success()
            ->title('Brouillon supprimé')
            ->body("Le brouillon a bien été retiré.")
            ->send();
    }

    public function getRecentPublished(): array
    {
        return Article::where('status', 'published')
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get()
            ->map(fn (Article $a) => [
                'title' => $a->title,
                'slug' => $a->slug,
                'category' => $a->category?->name ?? '',
                'cover' => $a->cover_image_url,
                'published_at' => $a->published_at?->diffForHumans(),
                'url' => url('/articles/'.$a->slug),
            ])
            ->toArray();
    }

    public function getRecentActivity(): array
    {
        return PipelineJob::orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (PipelineJob $job) => [
                'type' => match ($job->job_type) {
                    'fetch_rss' => 'Fetch RSS',
                    'enrich' => 'Enrichissement',
                    'generate' => 'Génération',
                    'cleanup' => 'Maintenance',
                    default => $job->job_type,
                },
                'status' => $job->status,
                'time' => $job->created_at?->diffForHumans(),
            ])
            ->toArray();
    }

    public function getTodayAutomation(): array
    {
        $todayFetchJobs = PipelineJob::query()
            ->whereDate('created_at', today())
            ->where('job_type', 'fetch_rss')
            ->get();

        $todayEnrichJobs = PipelineJob::query()
            ->whereDate('created_at', today())
            ->where('job_type', 'enrich')
            ->get();

        $todayGenerateJobs = PipelineJob::query()
            ->whereDate('created_at', today())
            ->where('job_type', 'generate')
            ->get();

        $newItemsToday = RssItem::query()
            ->whereDate('fetched_at', today())
            ->count();

        $enrichedToday = EnrichedItem::query()
            ->whereDate('enriched_at', today())
            ->count();

        $proposalCount = count(app(ArticleSelectionService::class)->selectBestTopics(3));

        $generatedArticle = Article::query()
            ->whereDate('created_at', today())
            ->latest('created_at')
            ->first();

        $latestGenerateJob = $todayGenerateJobs->sortByDesc('created_at')->first();

        $steps = [
            [
                'key' => 'fetch',
                'label' => 'Collecte des sources',
                'description' => $todayFetchJobs->count() > 0
                    ? $todayFetchJobs->count() . " fetch(s) lancés aujourd'hui, " . $newItemsToday . " article(s) repérés."
                    : "Aucun fetch effectué aujourd'hui pour le moment.",
                'status' => $this->resolveStageStatus(
                    hasSuccess: $todayFetchJobs->where('status', 'completed')->isNotEmpty(),
                    hasRunning: $todayFetchJobs->where('status', 'running')->isNotEmpty(),
                    hasFailure: $todayFetchJobs->where('status', 'failed')->isNotEmpty(),
                ),
                'action' => 'rerunFetchStage',
                'action_label' => 'Relancer',
            ],
            [
                'key' => 'enrich',
                'label' => 'Analyse IA',
                'description' => $enrichedToday > 0
                    ? $enrichedToday . " contenu(s) enrichi(s) aujourd'hui."
                    : "Aucun enrichissement terminé aujourd'hui pour le moment.",
                'status' => $this->resolveStageStatus(
                    hasSuccess: $todayEnrichJobs->where('status', 'completed')->isNotEmpty(),
                    hasRunning: $todayEnrichJobs->where('status', 'running')->isNotEmpty(),
                    hasFailure: $todayEnrichJobs->where('status', 'failed')->isNotEmpty(),
                ),
                'action' => 'rerunEnrichmentStage',
                'action_label' => 'Relancer',
            ],
            [
                'key' => 'select',
                'label' => 'Sélection du sujet',
                'description' => $proposalCount > 0
                    ? $proposalCount . " idée(s) d'article actuellement disponible(s)."
                    : "Aucune idée d'article prête pour le moment.",
                'status' => $proposalCount > 0 ? 'done' : 'idle',
                'action' => 'rerunSelectionStage',
                'action_label' => 'Recalculer',
            ],
            [
                'key' => 'generate',
                'label' => 'Génération du brouillon',
                'description' => $generatedArticle
                    ? "Un brouillon a déjà été créé aujourd'hui : " . $generatedArticle->title
                    : ($latestGenerateJob?->status === 'failed'
                        ? "La dernière génération a échoué aujourd'hui."
                        : "Aucun brouillon généré aujourd'hui pour le moment."),
                'status' => $this->resolveStageStatus(
                    hasSuccess: $generatedArticle !== null || $todayGenerateJobs->where('status', 'completed')->isNotEmpty(),
                    hasRunning: $todayGenerateJobs->where('status', 'running')->isNotEmpty(),
                    hasFailure: $todayGenerateJobs->where('status', 'failed')->isNotEmpty(),
                ),
                'action' => 'rerunGenerationStage',
                'action_label' => 'Relancer',
            ],
        ];

        $globalStatus = collect($steps)->contains(fn (array $step): bool => $step['status'] === 'failed')
            ? 'failed'
            : (collect($steps)->contains(fn (array $step): bool => $step['status'] === 'running')
                ? 'running'
                : ($generatedArticle !== null ? 'done' : 'idle'));

        $automationPaused = app(PipelineAutomationState::class)->isPaused();

        if ($automationPaused) {
            $globalStatus = 'paused';
        }

        return [
            'summary' => [
                'fetch_runs' => $todayFetchJobs->count(),
                'new_items' => $newItemsToday,
                'enriched_items' => $enrichedToday,
                'proposal_count' => $proposalCount,
                'generate_runs' => $todayGenerateJobs->count(),
                'article_generated' => $generatedArticle !== null,
                'article_title' => $generatedArticle?->title,
                'article_status' => $generatedArticle?->status,
                'article_preview_url' => $generatedArticle
                    ? ($generatedArticle->status === 'published'
                        ? url('/articles/' . $generatedArticle->slug)
                        : url('/admin-preview/articles/' . $generatedArticle->slug))
                    : null,
                'last_update' => collect([
                    $todayFetchJobs->max('created_at'),
                    $todayEnrichJobs->max('created_at'),
                    $todayGenerateJobs->max('created_at'),
                ])->filter()->max()?->diffForHumans(),
                'global_status' => $globalStatus,
                'automation_paused' => $automationPaused,
            ],
            'steps' => $steps,
        ];
    }

    public function rerunFetchStage(): void
    {
        $feeds = RssFeed::query()->where('is_active', true)->get();
        $feeds->each(fn (RssFeed $feed) => FetchRssFeedJob::dispatch($feed));

        Notification::make()
            ->success()
            ->title('Collecte relancée')
            ->body($feeds->count() . ' flux ont été relancés.')
            ->send();
    }

    public function rerunEnrichmentStage(): void
    {
        $limit = max(1, (int) config('pipeline_schedule.enrich_items.limit', 3));
        $delaySeconds = max(1, (int) config('pipeline_schedule.enrich_items.delay_seconds', 10));

        $items = RssItem::query()
            ->where('status', 'new')
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
            ->title('Analyse IA relancée')
            ->body($items->count() . ' item(s) ont été envoyés en enrichissement.')
            ->send();
    }

    public function rerunSelectionStage(): void
    {
        $count = count(app(ArticleSelectionService::class)->selectBestTopics(4));

        Notification::make()
            ->success()
            ->title('Sélection recalculée')
            ->body($count . " idée(s) d'article sont disponibles après recalcul.")
            ->send();
    }

    public function rerunGenerationStage(): void
    {
        $proposal = app(ArticleSelectionService::class)->selectBestTopics(1)[0] ?? null;

        if (! is_array($proposal) || empty($proposal['items'])) {
            Notification::make()
                ->warning()
                ->title('Génération impossible')
                ->body("Aucune idée d'article exploitable n'est disponible pour le moment.")
                ->send();

            return;
        }

        $itemIds = collect($proposal['items'])->pluck('id')->filter()->values()->all();

        if ($itemIds === []) {
            Notification::make()
                ->warning()
                ->title('Génération impossible')
                ->body('La proposition du moment ne contient aucun item valide.')
                ->send();

            return;
        }

        $cluster = Cluster::create([
            'category_id' => $proposal['category']['id'] ?? null,
            'label' => (string) ($proposal['topic'] ?? 'Sujet IA'),
            'keywords' => collect($proposal['seo_keywords'] ?? [])
                ->map(fn ($keyword) => is_array($keyword) ? ($keyword['word'] ?? null) : $keyword)
                ->filter()
                ->values()
                ->all(),
            'status' => 'pending',
        ]);

        foreach ($itemIds as $itemId) {
            ClusterItem::create([
                'cluster_id' => $cluster->id,
                'rss_item_id' => $itemId,
            ]);
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
            ->title('Génération relancée')
            ->body('Une nouvelle génération de brouillon a été envoyée.')
            ->send();
    }

    public function pauseAutomation(): void
    {
        app(PipelineAutomationState::class)->pause();

        Notification::make()
            ->success()
            ->title('Automatisation en pause')
            ->body('Les tâches automatiques fetch, analyse IA et génération quotidienne sont maintenant suspendues.')
            ->send();
    }

    public function resumeAutomation(): void
    {
        app(PipelineAutomationState::class)->resume();

        Notification::make()
            ->success()
            ->title('Automatisation relancée')
            ->body('Les tâches automatiques du pipeline peuvent à nouveau se lancer.')
            ->send();
    }

    private function resolveStageStatus(bool $hasSuccess, bool $hasRunning, bool $hasFailure): string
    {
        if ($hasRunning) {
            return 'running';
        }

        if ($hasSuccess) {
            return 'done';
        }

        if ($hasFailure) {
            return 'failed';
        }

        return 'idle';
    }

    public function openPublishModal(string $articleId): void
    {
        $article = Article::query()->findOrFail($articleId);

        $this->publishArticleId = $article->id;
        $this->publishCategoryId = $article->category_id;
        $this->publishArticleType = in_array($article->article_type, ['standard', 'hot_news', 'long_form'], true)
            ? $article->article_type
            : 'standard';
        $this->publishModalOpen = true;
    }

    public function closePublishModal(): void
    {
        $this->publishModalOpen = false;
        $this->publishArticleId = null;
        $this->publishCategoryId = null;
        $this->publishArticleType = 'standard';
    }

    public function confirmPublishDraft(): void
    {
        if (! $this->publishArticleId) {
            $this->closePublishModal();

            return;
        }

        $article = Article::query()->findOrFail($this->publishArticleId);

        if (! $article->category_id && ! $this->publishCategoryId) {
            Notification::make()
                ->warning()
                ->title('Catégorie requise')
                ->body("Choisis une catégorie avant de publier ce brouillon.")
                ->send();

            return;
        }

        if (! in_array($this->publishArticleType, ['standard', 'hot_news', 'long_form'], true)) {
            Notification::make()
                ->warning()
                ->title("Type d'article requis")
                ->body("Choisis un type d'article valide avant de publier ce brouillon.")
                ->send();

            return;
        }

        $article->update([
            'category_id' => $this->publishCategoryId ?: $article->category_id,
            'article_type' => $this->publishArticleType,
        ]);
        $article->refresh();

        if (! $article->publish()) {
            Notification::make()
                ->danger()
                ->title('Publication impossible')
                ->body("Le brouillon ne remplit pas encore les conditions nécessaires pour être publié.")
                ->send();

            $this->closePublishModal();

            return;
        }

        Notification::make()
            ->success()
            ->title('Article publié')
            ->body("Le brouillon a été publié et est maintenant visible sur le site.")
            ->send();

        $this->closePublishModal();
    }
}
