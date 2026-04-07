<?php

namespace App\Filament\Pages;

use App\Models\PipelineJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PipelineCronJobs extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Assistant IA';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Historique automatique';

    protected static ?string $title = 'Historique automatique';

    protected string $view = 'filament.pages.pipeline-cron-jobs';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecentJobsByDay(): array
    {
        $jobs = PipelineJob::query()
            ->orderByDesc('created_at')
            ->limit(120)
            ->get()
            ->groupBy(fn (PipelineJob $job): string => $job->created_at?->format('d/m/Y') ?? 'Date inconnue');

        return $jobs
            ->map(function (Collection $items, string $date): array {
                return [
                    'date' => $date,
                    'jobs' => $items->map(function (PipelineJob $job): array {
                        return [
                            'label' => $this->labelForJobType($job->job_type, $job->metadata ?? []),
                            'status' => $job->status,
                            'status_label' => $this->statusLabel($job),
                            'started_at' => $job->started_at?->format('H:i:s'),
                            'completed_at' => $job->completed_at?->format('H:i:s'),
                            'error_message' => $job->error_message,
                            'metadata' => $job->metadata ?? [],
                            'summary' => $this->summaryForJob($job),
                            'details' => $this->detailsForJob($job),
                        ];
                    })->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function getOverview(): array
    {
        $jobs = PipelineJob::query()
            ->orderByDesc('created_at')
            ->limit(120)
            ->get();

        $completed = $jobs->where('status', 'completed')->count();
        $failed = $jobs->where('status', 'failed')->count();
        $running = $jobs->where('status', 'running')->count();
        $retryScheduled = $jobs->filter(fn (PipelineJob $job): bool => ($job->metadata['retry_scheduled'] ?? false) === true)->count();
        $lastJob = $jobs->first();

        return [
            'total' => $jobs->count(),
            'completed' => $completed,
            'failed' => $failed,
            'running' => $running,
            'retry_scheduled' => $retryScheduled,
            'days' => count($this->getRecentJobsByDay()),
            'last_label' => $lastJob ? $this->labelForJobType($lastJob->job_type, $lastJob->metadata ?? []) : null,
            'last_time' => $lastJob?->created_at?->format('d/m à H:i'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('daily')
                ->label('Suivi du jour')
                ->icon(Heroicon::OutlinedBolt)
                ->color('gray')
                ->url(PipelineDailyAutomation::getUrl()),
            Action::make('refresh')
                ->label('Rafraîchir')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    Notification::make()
                        ->success()
                        ->title('Historique actualisé')
                        ->body('La liste des cron jobs a été rechargée.')
                        ->send();
                }),
        ];
    }

    private function labelForJobType(string $jobType, array $metadata = []): string
    {
        if (($metadata['command'] ?? null) === 'horizon:snapshot') {
            return 'Snapshot Horizon';
        }

        if (($metadata['command'] ?? null) === 'queue:prune-failed') {
            return 'Nettoyage des failed jobs';
        }

        return match ($jobType) {
            'fetch_rss' => 'Fetch des flux RSS',
            'enrich' => 'Enrichissement IA',
            'selection' => 'Recalcul des idées',
            'generate' => 'Génération IA',
            'manual_flow' => 'Relance complète du flux',
            'cleanup' => 'Maintenance',
            default => ucfirst(str_replace('_', ' ', $jobType)),
        };
    }

    private function statusLabel(PipelineJob $job): string
    {
        if (($job->metadata['retry_scheduled'] ?? false) === true) {
            return 'Relance planifiée';
        }

        return match ($job->status) {
            'completed' => 'Réussi',
            'failed' => 'Échec',
            'running' => 'En cours',
            default => 'En attente',
        };
    }

    private function summaryForJob(PipelineJob $job): string
    {
        $metadata = $job->metadata ?? [];

        return match ($job->job_type) {
            'fetch_rss' => (($metadata['manual_dispatch'] ?? false) === true)
                ? 'Relance manuelle de la collecte'
                : (trim(collect([
                    $metadata['source'] ?? null,
                    $metadata['category'] ?? null,
                ])->filter()->implode(' · ')) ?: 'Flux RSS'),
            'enrich' => (($metadata['manual_dispatch'] ?? false) === true)
                ? 'Relance manuelle de l’analyse IA'
                : ($metadata['title'] ?? 'Item RSS'),
            'selection' => 'Recalcul manuel des idées d’articles',
            'generate' => (($metadata['manual_dispatch'] ?? false) === true && ($metadata['outcome'] ?? null) === 'queued')
                ? 'Relance manuelle de la génération'
                : ($metadata['article_slug'] ?? ($metadata['cluster_id'] ?? 'Brouillon IA')),
            'manual_flow' => 'Relance manuelle de tout le pipeline',
            'cleanup' => $metadata['command'] ?? 'Maintenance',
            default => ucfirst(str_replace('_', ' ', $job->job_type)),
        };
    }

    public function shortError(?string $message): ?string
    {
        if ($message === null || trim($message) === '') {
            return null;
        }

        return Str::limit(trim(preg_replace('/\s+/', ' ', $message) ?? $message), 180);
    }

    /**
     * @return array<int, string>
     */
    private function detailsForJob(PipelineJob $job): array
    {
        $metadata = $job->metadata ?? [];

        return match ($job->job_type) {
            'fetch_rss' => array_values(array_filter([
                ($metadata['manual_dispatch'] ?? false) === true && isset($metadata['dispatched_feeds']) ? 'Flux relancés : '.$metadata['dispatched_feeds'] : null,
                isset($metadata['new_items']) ? 'Nouveaux items : '.$metadata['new_items'] : null,
                isset($metadata['discovered_items']) ? 'Items détectés : '.$metadata['discovered_items'] : null,
                ! empty($metadata['feed_url']) ? 'Flux : '.$metadata['feed_url'] : null,
            ])),
            'enrich' => array_values(array_filter([
                ($metadata['manual_dispatch'] ?? false) === true && isset($metadata['dispatched_items']) ? 'Items relancés : '.$metadata['dispatched_items'] : null,
                ! empty($metadata['primary_topic']) ? 'Sujet : '.$metadata['primary_topic'] : null,
                isset($metadata['quality_score']) ? 'Qualité : '.$metadata['quality_score'].'/100' : null,
                isset($metadata['seo_score']) ? 'SEO : '.$metadata['seo_score'].'/100' : null,
                isset($metadata['word_count']) ? 'Mots extraits : '.$metadata['word_count'] : null,
                ($metadata['retry_scheduled'] ?? false) === true ? 'Nouvelle tentative planifiée dans '.($metadata['retry_delay_seconds'] ?? 60).'s' : null,
            ])),
            'selection' => array_values(array_filter([
                isset($metadata['proposal_count']) ? 'Idées disponibles : '.$metadata['proposal_count'] : null,
                ($metadata['manual_dispatch'] ?? false) === true ? 'Lancement manuel' : null,
            ])),
            'generate' => array_values(array_filter([
                ($metadata['manual_dispatch'] ?? false) === true ? 'Lancement manuel' : null,
                isset($metadata['article_type']) ? 'Type : '.$metadata['article_type'] : null,
                isset($metadata['article_id']) ? 'Article : '.$metadata['article_id'] : null,
                isset($metadata['cluster_id']) ? 'Cluster : '.$metadata['cluster_id'] : null,
                isset($metadata['item_ids']) && is_array($metadata['item_ids']) ? 'Sources : '.count($metadata['item_ids']) : null,
            ])),
            'manual_flow' => array_values(array_filter([
                ($metadata['manual_dispatch'] ?? false) === true ? 'Relance globale' : null,
                isset($metadata['dispatched_feeds']) ? 'Flux : '.$metadata['dispatched_feeds'] : null,
                isset($metadata['dispatched_items']) ? 'Items : '.$metadata['dispatched_items'] : null,
                isset($metadata['proposal_count']) ? 'Idées : '.$metadata['proposal_count'] : null,
                isset($metadata['generation_dispatched']) ? 'Génération : '.($metadata['generation_dispatched'] ? 'lancée' : 'impossible') : null,
                ($metadata['waiting_for_enrichment'] ?? false) === true ? 'En attente des enrichissements' : null,
                isset($metadata['cluster_id']) && ! empty($metadata['cluster_id']) ? 'Cluster : '.$metadata['cluster_id'] : null,
            ])),
            'cleanup' => array_values(array_filter([
                ! empty($metadata['command']) ? 'Commande : '.$metadata['command'] : null,
                isset($metadata['hours']) ? 'Fenêtre : '.$metadata['hours'].'h' : null,
            ])),
            default => [],
        };
    }
}
