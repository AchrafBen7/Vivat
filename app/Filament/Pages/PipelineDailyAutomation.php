<?php

namespace App\Filament\Pages;

use App\Jobs\EnrichContentJob;
use App\Jobs\FetchRssFeedJob;
use App\Jobs\GenerateArticleJob;
use App\Models\Article;
use App\Models\Cluster;
use App\Models\ClusterItem;
use App\Models\EnrichedItem;
use App\Models\PipelineJob;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Services\ArticleSelectionService;
use App\Services\PipelineAutomationState;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class PipelineDailyAutomation extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Suivi du jour';

    protected static ?string $title = 'Suivi du jour';

    protected string $view = 'filament.pages.pipeline-daily-automation';

    public bool $showProgressOverlay = false;

    public ?string $generationTrackingClusterId = null;

    public ?string $progressMode = null;

    public ?string $progressStartedAt = null;

    public int $progressTargetCount = 0;

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
                    ? $todayFetchJobs->count() . ' fetch(s) lancés aujourd’hui, ' . $newItemsToday . ' article(s) repérés.'
                    : 'Aucun fetch effectué aujourd’hui pour le moment.',
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
                    ? $enrichedToday . ' contenu(s) enrichi(s) aujourd’hui.'
                    : 'Aucun enrichissement terminé aujourd’hui pour le moment.',
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
                    ? $proposalCount . ' idée(s) d’article actuellement disponible(s).'
                    : 'Aucune idée d’article prête pour le moment.',
                'status' => $proposalCount > 0 ? 'done' : 'idle',
                'action' => 'rerunSelectionStage',
                'action_label' => 'Recalculer',
            ],
            [
                'key' => 'generate',
                'label' => 'Génération du brouillon',
                'description' => $generatedArticle
                    ? 'Un brouillon a déjà été créé aujourd’hui : ' . $generatedArticle->title
                    : ($latestGenerateJob?->status === 'failed'
                        ? 'La dernière génération a échoué aujourd’hui.'
                        : 'Aucun brouillon généré aujourd’hui pour le moment.'),
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('history')
                ->label('Voir l’historique')
                ->icon(Heroicon::OutlinedClock)
                ->color('gray')
                ->url(PipelineCronJobs::getUrl()),
            Action::make('refresh')
                ->label('Rafraîchir')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    Notification::make()
                        ->success()
                        ->title('Suivi actualisé')
                        ->send();
                }),
        ];
    }

    public function rerunFetchStage(): void
    {
        $feeds = $this->dispatchFetchJobs();
        $this->recordManualAction('fetch_rss', [
            'manual_dispatch' => true,
            'dispatched_feeds' => $feeds->count(),
        ]);
        $this->startProgressOverlay('fetch', $feeds->count());
    }

    public function rerunEnrichmentStage(): void
    {
        $items = $this->dispatchEnrichmentJobs();
        $this->recordManualAction('enrich', [
            'manual_dispatch' => true,
            'dispatched_items' => $items->count(),
        ]);
        $this->startProgressOverlay('enrich', $items->count());
    }

    public function rerunSelectionStage(): void
    {
        $count = $this->countAvailableProposals();
        $this->recordManualAction('cleanup', [
            'manual_dispatch' => true,
            'manual_action' => 'selection',
            'proposal_count' => $count,
        ]);
        $this->startProgressOverlay('select', $count);
    }

    public function rerunGenerationStage(): void
    {
        if (! $this->dispatchGenerationFromBestProposal()) {
            $this->recordManualAction(
                'generate',
                ['manual_dispatch' => true, 'outcome' => 'no_proposal'],
                'failed',
                'Aucune idée d’article exploitable n’est disponible pour le moment.'
            );
            Notification::make()
                ->warning()
                ->title('Génération impossible')
                ->body('Aucune idée d’article exploitable n’est disponible pour le moment.')
                ->send();

            return;
        }

        $this->recordManualAction('generate', [
            'manual_dispatch' => true,
            'cluster_id' => $this->generationTrackingClusterId,
            'outcome' => 'queued',
        ]);
    }

    public function rerunFullFlow(): void
    {
        $feeds = $this->dispatchFetchJobs();
        $items = $this->dispatchEnrichmentJobs();
        $proposalCount = $this->countAvailableProposals();
        $this->startProgressOverlay('full', $items->count());
        $generated = $proposalCount > 0 ? $this->dispatchGenerationFromBestProposal() : false;
        $this->recordManualAction('cleanup', [
            'manual_dispatch' => true,
            'manual_action' => 'full_flow',
            'dispatched_feeds' => $feeds->count(),
            'dispatched_items' => $items->count(),
            'proposal_count' => $proposalCount,
            'generation_dispatched' => $generated,
            'cluster_id' => $this->generationTrackingClusterId,
            'waiting_for_enrichment' => ! $generated,
        ], 'completed');
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

    public function closeGenerationOverlay(): void
    {
        $this->showProgressOverlay = false;
    }

    public function getGenerationOverlayState(): array
    {
        if (! $this->showProgressOverlay || ! $this->progressMode) {
            return [
                'visible' => false,
                'eyebrow' => '',
                'progress' => 0,
                'label' => '',
                'headline' => '',
                'article_preview_url' => null,
                'is_finished' => false,
                'is_failed' => false,
            ];
        }

        return match ($this->progressMode) {
            'fetch' => $this->buildFetchOverlayState(),
            'enrich' => $this->buildEnrichmentOverlayState(),
            'select' => $this->buildSelectionOverlayState(),
            'full' => $this->buildFullFlowOverlayState(),
            default => $this->buildGenerationOverlayState(),
        };
    }

    private function buildGenerationOverlayState(): array
    {
        if (! $this->generationTrackingClusterId) {
            return $this->emptyOverlayState();
        }

        $cluster = Cluster::query()->find($this->generationTrackingClusterId);
        $article = Article::query()
            ->where('cluster_id', $this->generationTrackingClusterId)
            ->latest('created_at')
            ->first();

        if ($article) {
            return [
                'visible' => true,
                'eyebrow' => 'Création du brouillon',
                'progress' => 100,
                'label' => '100%',
                'headline' => 'Le brouillon est prêt.',
                'article_preview_url' => $article->status === 'published'
                    ? url('/articles/' . $article->slug)
                    : url('/admin-preview/articles/' . $article->slug),
                'is_finished' => true,
                'is_failed' => false,
            ];
        }

        $status = $cluster?->status ?? 'pending';

        return match ($status) {
            'processing' => [
                'visible' => true,
                'eyebrow' => 'Création du brouillon',
                'progress' => 68,
                'label' => '68%',
                'headline' => 'L’article est en cours de rédaction.',
                'article_preview_url' => null,
                'is_finished' => false,
                'is_failed' => false,
            ],
            'failed' => [
                'visible' => true,
                'eyebrow' => 'Création du brouillon',
                'progress' => 100,
                'label' => 'Échec',
                'headline' => 'La génération a échoué.',
                'article_preview_url' => null,
                'is_finished' => true,
                'is_failed' => true,
            ],
            'generated' => [
                'visible' => true,
                'eyebrow' => 'Création du brouillon',
                'progress' => 100,
                'label' => '100%',
                'headline' => 'Le brouillon est prêt.',
                'article_preview_url' => null,
                'is_finished' => true,
                'is_failed' => false,
            ],
            default => [
                'visible' => true,
                'eyebrow' => 'Création du brouillon',
                'progress' => 18,
                'label' => '18%',
                'headline' => 'Préparation de la génération en cours.',
                'article_preview_url' => null,
                'is_finished' => false,
                'is_failed' => false,
            ],
        };
    }

    private function buildFetchOverlayState(): array
    {
        $startedAt = $this->parseProgressStartedAt();
        $completed = PipelineJob::query()
            ->where('job_type', 'fetch_rss')
            ->when($startedAt, fn ($query) => $query->where('created_at', '>=', $startedAt))
            ->count();

        $target = max(1, $this->progressTargetCount);
        $progress = min(100, max($completed > 0 ? 22 : 8, (int) round(($completed / $target) * 100)));
        $finished = $completed >= $target;

        return [
            'visible' => true,
            'eyebrow' => 'Collecte des sources',
            'progress' => $finished ? 100 : $progress,
            'label' => ($finished ? 100 : $progress) . '%',
            'headline' => $finished
                ? 'La collecte des sources est terminée.'
                : 'Collecte des sources en cours.',
            'article_preview_url' => null,
            'is_finished' => $finished,
            'is_failed' => false,
        ];
    }

    private function buildEnrichmentOverlayState(): array
    {
        $startedAt = $this->parseProgressStartedAt();
        $completed = PipelineJob::query()
            ->where('job_type', 'enrich')
            ->when($startedAt, fn ($query) => $query->where('created_at', '>=', $startedAt))
            ->count();

        $target = max(1, $this->progressTargetCount);
        $progress = min(100, max($completed > 0 ? 28 : 10, (int) round(($completed / $target) * 100)));
        $finished = $completed >= $target;

        return [
            'visible' => true,
            'eyebrow' => 'Analyse IA',
            'progress' => $finished ? 100 : $progress,
            'label' => ($finished ? 100 : $progress) . '%',
            'headline' => $finished
                ? 'Les analyses IA sont terminées.'
                : 'Les contenus sont en cours d’analyse.',
            'article_preview_url' => null,
            'is_finished' => $finished,
            'is_failed' => false,
        ];
    }

    private function buildSelectionOverlayState(): array
    {
        return [
            'visible' => true,
            'eyebrow' => 'Sélection des sujets',
            'progress' => 100,
            'label' => '100%',
            'headline' => 'La sélection des sujets est prête.',
            'article_preview_url' => null,
            'is_finished' => true,
            'is_failed' => false,
        ];
    }

    private function buildFullFlowOverlayState(): array
    {
        $startedAt = $this->parseProgressStartedAt();
        $fetchCount = PipelineJob::query()
            ->where('job_type', 'fetch_rss')
            ->when($startedAt, fn ($query) => $query->where('created_at', '>=', $startedAt))
            ->count();
        $enrichCount = PipelineJob::query()
            ->where('job_type', 'enrich')
            ->when($startedAt, fn ($query) => $query->where('created_at', '>=', $startedAt))
            ->count();

        $fetchDone = $fetchCount >= max(1, RssFeed::query()->where('is_active', true)->count());
        $enrichDone = $enrichCount >= max(1, $this->progressTargetCount);
        $proposalReady = $this->countAvailableProposals() > 0;
        $generationState = $this->buildGenerationOverlayState();

        $progress = 10
            + ($fetchDone ? 20 : 0)
            + ($enrichDone ? 25 : 0)
            + ($proposalReady ? 15 : 0)
            + (int) round(($generationState['progress'] ?? 0) * 0.3);

        $progress = min(100, $progress);
        $finished = (bool) ($generationState['is_finished'] ?? false) && ! ($generationState['is_failed'] ?? false);

        $headline = match (true) {
            $finished => 'Le flux complet a terminé son cycle.',
            ! $fetchDone => 'Collecte des sources en cours.',
            ! $enrichDone => 'Analyse IA en cours.',
            ! $proposalReady => 'Sélection des sujets en cours.',
            default => $generationState['headline'] ?? 'Génération du brouillon en cours.',
        };

        return [
            'visible' => true,
            'eyebrow' => 'Relance complète du flux',
            'progress' => $finished ? 100 : $progress,
            'label' => ($finished ? 100 : $progress) . '%',
            'headline' => $headline,
            'article_preview_url' => $generationState['article_preview_url'] ?? null,
            'is_finished' => $finished,
            'is_failed' => (bool) ($generationState['is_failed'] ?? false),
        ];
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

    private function dispatchFetchJobs()
    {
        $feeds = RssFeed::query()->where('is_active', true)->get();
        $feeds->each(fn (RssFeed $feed) => FetchRssFeedJob::dispatch($feed));

        return $feeds;
    }

    private function dispatchEnrichmentJobs()
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

        return $items;
    }

    private function countAvailableProposals(): int
    {
        return count(app(ArticleSelectionService::class)->selectBestTopics(4));
    }

    private function dispatchGenerationFromBestProposal(): bool
    {
        $proposal = app(ArticleSelectionService::class)->selectBestTopics(1)[0] ?? null;

        if (! is_array($proposal) || empty($proposal['items'])) {
            return false;
        }

        $itemIds = collect($proposal['items'])->pluck('id')->filter()->values()->all();

        if ($itemIds === []) {
            return false;
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

        $this->generationTrackingClusterId = (string) $cluster->id;

        if ($this->progressMode === null || $this->progressMode === 'generate') {
            $this->startProgressOverlay('generate', 1);
        }

        return true;
    }

    private function startProgressOverlay(string $mode, int $targetCount = 0): void
    {
        $this->progressMode = $mode;
        $this->progressTargetCount = max(0, $targetCount);
        $this->progressStartedAt = now()->toIso8601String();
        $this->showProgressOverlay = true;

        if ($mode !== 'generate' && $mode !== 'full') {
            $this->generationTrackingClusterId = null;
        }
    }

    private function parseProgressStartedAt(): ?Carbon
    {
        return $this->progressStartedAt ? Carbon::parse($this->progressStartedAt) : null;
    }

    private function emptyOverlayState(): array
    {
        return [
            'visible' => false,
            'eyebrow' => '',
            'progress' => 0,
            'label' => '',
            'headline' => '',
            'article_preview_url' => null,
            'is_finished' => false,
            'is_failed' => false,
        ];
    }

    private function recordManualAction(string $jobType, array $metadata = [], string $status = 'completed', ?string $errorMessage = null): void
    {
        PipelineJob::create([
            'job_type' => $jobType,
            'status' => $status,
            'started_at' => now(),
            'completed_at' => now(),
            'error_message' => $errorMessage,
            'metadata' => array_merge($metadata, [
                'origin' => 'assistant_ia_manual',
            ]),
            'retry_count' => 0,
        ]);
    }
}
