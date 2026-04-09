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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('history')
                ->label("Voir l'historique")
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
                "Aucune idée d'article exploitable n'est disponible pour le moment."
            );
            Notification::make()
                ->warning()
                ->title('Génération impossible')
                ->body("Aucune idée d'article exploitable n'est disponible pour le moment.")
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

        $generationSteps = [
            ['key' => 'queue',   'label' => "Job en file d'attente",               'detail' => "Le job de generation attend un worker disponible."],
            ['key' => 'select',  'label' => "Selection des sources",               'detail' => "Chargement des articles RSS enrichis a synthetiser."],
            ['key' => 'prompt',  'label' => "Construction du prompt editorial",    'detail' => "Assemblage des sources, mots-cles SEO et instructions redactionnelles."],
            ['key' => 'openai',  'label' => "Redaction par GPT-4o",                'detail' => "Appel OpenAI en cours — cela prend generalement 30 a 90 secondes."],
            ['key' => 'process', 'label' => "Traitement du contenu",               'detail' => "Nettoyage HTML, calcul du temps de lecture, score qualite."],
            ['key' => 'cover',   'label' => "Generation de l'image de couverture", 'detail' => "Creation de l'image via BFL / DALL-E 3 et upload sur Cloudinary."],
            ['key' => 'done',    'label' => "Brouillon cree",                      'detail' => "L'article est pret a etre relu dans l'espace editorial."],
        ];

        if ($article) {
            $stepsWithStatus = array_map(fn ($s) => array_merge($s, ['status' => 'done']), $generationSteps);

            return [
                'visible' => true,
                'eyebrow' => 'Génération du brouillon',
                'progress' => 100,
                'label' => '100%',
                'headline' => 'Le brouillon est prêt.',
                'current_detail' => 'Article créé : « ' . $article->title . ' »',
                'article_preview_url' => $article->status === 'published'
                    ? url('/articles/' . $article->slug)
                    : url('/admin-preview/articles/' . $article->slug),
                'is_finished' => true,
                'is_failed' => false,
                'steps' => $stepsWithStatus,
            ];
        }

        if ($cluster?->status === 'failed') {
            $stepsWithStatus = array_map(fn ($s) => array_merge($s, ['status' => 'failed']), $generationSteps);

            return [
                'visible' => true,
                'eyebrow' => 'Génération du brouillon',
                'progress' => 100,
                'label' => 'Échec',
                'headline' => 'La génération a échoué.',
                'current_detail' => 'Vérifie les logs Horizon pour plus de détails. Tu peux relancer depuis l\'étape 4.',
                'article_preview_url' => null,
                'is_finished' => true,
                'is_failed' => true,
                'steps' => $stepsWithStatus,
            ];
        }

        // Estimer l'étape actuelle par temps écoulé depuis le dispatch
        $startedAt = $this->parseProgressStartedAt();
        $elapsedSeconds = $startedAt ? now()->diffInSeconds($startedAt) : 0;

        [$currentStepIndex, $progress] = match (true) {
            $elapsedSeconds < 10  => [0, 8],
            $elapsedSeconds < 25  => [1, 18],
            $elapsedSeconds < 45  => [2, 30],
            $elapsedSeconds < 120 => [3, max(40, min(75, 30 + (int) round(($elapsedSeconds - 45) * 0.58)))],
            $elapsedSeconds < 150 => [4, 80],
            $elapsedSeconds < 180 => [5, 90],
            default               => [5, 95],
        };

        $stepsWithStatus = array_map(function (array $step, int $idx) use ($currentStepIndex): array {
            return array_merge($step, [
                'status' => $idx < $currentStepIndex ? 'done' : ($idx === $currentStepIndex ? 'active' : 'waiting'),
            ]);
        }, $generationSteps, array_keys($generationSteps));

        $currentStep = $generationSteps[$currentStepIndex] ?? $generationSteps[3];

        return [
            'visible' => true,
            'eyebrow' => 'Génération du brouillon',
            'progress' => $progress,
            'label' => $progress . '%',
            'headline' => $currentStep['label'] . '…',
            'current_detail' => $currentStep['detail'],
            'article_preview_url' => null,
            'is_finished' => false,
            'is_failed' => false,
            'steps' => $stepsWithStatus,
        ];
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
            'headline' => $finished ? 'La collecte des sources est terminée.' : 'Collecte des sources en cours.',
            'current_detail' => $finished
                ? "{$completed} feed(s) traité(s). Les nouveaux articles sont en base."
                : "Récupération des flux RSS actifs ({$completed} / {$target} terminés).",
            'article_preview_url' => null,
            'is_finished' => $finished,
            'is_failed' => false,
            'steps' => [
                ['key' => 'fetch', 'label' => 'Récupération des flux RSS', 'detail' => "{$completed} / {$target} feeds traités", 'status' => $finished ? 'done' : 'active'],
                ['key' => 'parse', 'label' => 'Parsing et déduplication des articles', 'detail' => 'Filtrage des doublons et mise en base.', 'status' => $finished ? 'done' : 'waiting'],
            ],
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
            'headline' => $finished ? 'Les analyses IA sont terminées.' : 'Enrichissement des articles en cours…',
            'current_detail' => $finished
                ? "{$completed} article(s) analysé(s) — lead, points clés, mots-clés SEO extraits."
                : "Appels OpenAI en cours ({$completed} / {$target} articles traités). Chaque appel prend ~10 secondes.",
            'article_preview_url' => null,
            'is_finished' => $finished,
            'is_failed' => false,
            'steps' => [
                ['key' => 'extract', 'label' => 'Extraction du texte brut', 'detail' => 'Récupération du contenu depuis les URLs RSS.', 'status' => $completed > 0 ? 'done' : 'active'],
                ['key' => 'openai',  'label' => 'Analyse par OpenAI (GPT-4o)', 'detail' => "{$completed} / {$target} analysés — lead, key_points, seo_keywords.", 'status' => $finished ? 'done' : 'active'],
                ['key' => 'store',   'label' => 'Sauvegarde des enrichissements', 'detail' => 'Mise à jour du statut des RssItems → enriched.', 'status' => $finished ? 'done' : 'waiting'],
            ],
        ];
    }

    private function buildSelectionOverlayState(): array
    {
        $count = $this->countAvailableProposals();

        return [
            'visible' => true,
            'eyebrow' => 'Sélection des sujets',
            'progress' => 100,
            'label' => '100%',
            'headline' => $count > 0 ? "{$count} idée(s) d'article disponible(s)." : 'Aucune idée disponible — enrichissement requis.',
            'current_detail' => $count > 0
                ? "Les sujets ont été clustérisés par similarité thématique. Le meilleur sera sélectionné pour la génération."
                : "Pas assez d'articles enrichis pour former un cluster de 2 sources minimum.",
            'article_preview_url' => null,
            'is_finished' => true,
            'is_failed' => false,
            'steps' => [
                ['key' => 'cluster', 'label' => 'Clustering thématique (Jaccard)', 'detail' => 'Regroupement des articles par similarité de mots-clés (seuil 8%).', 'status' => 'done'],
                ['key' => 'score',   'label' => 'Scoring des clusters', 'detail' => 'Qualité, fraîcheur, SEO, diversité des sources.', 'status' => 'done'],
                ['key' => 'select',  'label' => 'Sélection finale', 'detail' => $count > 0 ? "{$count} idée(s) retenue(s)." : 'Aucun cluster valide (min. 2 sources requises).', 'status' => $count > 0 ? 'done' : 'failed'],
            ],
        ];
    }

    private function buildFullFlowOverlayState(): array
    {
        $startedAt = $this->parseProgressStartedAt();
        $activeFeedCount = max(1, RssFeed::query()->where('is_active', true)->count());
        $fetchCount = PipelineJob::query()
            ->where('job_type', 'fetch_rss')
            ->when($startedAt, fn ($query) => $query->where('created_at', '>=', $startedAt))
            ->count();
        $enrichCount = PipelineJob::query()
            ->where('job_type', 'enrich')
            ->when($startedAt, fn ($query) => $query->where('created_at', '>=', $startedAt))
            ->count();

        $fetchDone = $fetchCount >= $activeFeedCount;
        $enrichDone = $enrichCount >= max(1, $this->progressTargetCount);
        $proposalCount = $this->countAvailableProposals();
        $proposalReady = $proposalCount > 0;
        $generationState = $this->buildGenerationOverlayState();
        $generationFinished = (bool) ($generationState['is_finished'] ?? false);
        $generationFailed = (bool) ($generationState['is_failed'] ?? false);
        $finished = $generationFinished && ! $generationFailed;

        $progress = 5
            + ($fetchDone ? 15 : min(14, (int) round($fetchCount / $activeFeedCount * 15)))
            + ($enrichDone ? 20 : 0)
            + ($proposalReady ? 10 : 0)
            + (int) round(($generationState['progress'] ?? 0) * 0.5);
        $progress = min(100, $progress);

        $headline = match (true) {
            $finished        => 'Le flux complet a terminé son cycle.',
            $generationFailed => 'La génération a échoué.',
            ! $fetchDone     => 'Collecte des sources RSS en cours…',
            ! $enrichDone    => 'Analyse IA des articles en cours…',
            ! $proposalReady => 'Sélection du meilleur sujet…',
            default          => $generationState['headline'] ?? 'Génération du brouillon en cours…',
        };

        $flowSteps = [
            [
                'key'    => 'fetch',
                'label'  => 'Collecte RSS',
                'detail' => $fetchDone
                    ? "{$fetchCount} feed(s) collecté(s)"
                    : "{$fetchCount} / {$activeFeedCount} feeds lancés",
                'status' => $fetchDone ? 'done' : (! $fetchDone && $fetchCount > 0 ? 'active' : 'active'),
            ],
            [
                'key'    => 'enrich',
                'label'  => 'Analyse IA',
                'detail' => $enrichDone
                    ? "{$enrichCount} article(s) enrichi(s)"
                    : ($enrichCount > 0 ? "{$enrichCount} enrichi(s), traitement en cours…" : 'En attente des fetches…'),
                'status' => $enrichDone ? 'done' : ($fetchDone ? 'active' : 'waiting'),
            ],
            [
                'key'    => 'select',
                'label'  => 'Sélection du sujet',
                'detail' => $proposalReady
                    ? "{$proposalCount} idée(s) disponible(s), meilleure sélectionnée"
                    : 'Calcul des clusters thématiques…',
                'status' => $proposalReady ? 'done' : ($enrichDone ? 'active' : 'waiting'),
            ],
            [
                'key'    => 'generate',
                'label'  => 'Génération du brouillon',
                'detail' => $generationState['current_detail'] ?? ($proposalReady ? 'En file d\'attente…' : 'En attente de la sélection…'),
                'status' => $finished ? 'done' : ($generationFailed ? 'failed' : ($proposalReady ? 'active' : 'waiting')),
                'sub_steps' => $generationState['steps'] ?? [],
            ],
        ];

        return [
            'visible'            => true,
            'eyebrow'            => 'Relance complète du flux',
            'progress'           => $finished ? 100 : $progress,
            'label'              => ($finished ? 100 : $progress) . '%',
            'headline'           => $headline,
            'current_detail'     => $generationState['current_detail'] ?? null,
            'article_preview_url'=> $generationState['article_preview_url'] ?? null,
            'is_finished'        => $finished,
            'is_failed'          => $generationFailed,
            'steps'              => $flowSteps,
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
            'current_detail' => null,
            'article_preview_url' => null,
            'is_finished' => false,
            'is_failed' => false,
            'steps' => [],
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
