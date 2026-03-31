<?php

namespace App\Filament\Pages;

use App\Models\PipelineJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class PipelineCronJobs extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Pipeline IA';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Cron jobs';

    protected static ?string $title = 'Cron jobs';

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
            'generate' => 'Génération IA',
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
            'fetch_rss' => trim(collect([
                $metadata['source'] ?? null,
                $metadata['category'] ?? null,
            ])->filter()->implode(' · ')) ?: 'Flux RSS',
            'enrich' => $metadata['title'] ?? 'Item RSS',
            'generate' => $metadata['article_slug'] ?? ($metadata['cluster_id'] ?? 'Brouillon IA'),
            'cleanup' => $metadata['command'] ?? 'Maintenance',
            default => ucfirst(str_replace('_', ' ', $job->job_type)),
        };
    }

    /**
     * @return array<int, string>
     */
    private function detailsForJob(PipelineJob $job): array
    {
        $metadata = $job->metadata ?? [];

        return match ($job->job_type) {
            'fetch_rss' => array_values(array_filter([
                isset($metadata['new_items']) ? 'Nouveaux items : '.$metadata['new_items'] : null,
                isset($metadata['discovered_items']) ? 'Items détectés : '.$metadata['discovered_items'] : null,
                ! empty($metadata['feed_url']) ? 'Flux : '.$metadata['feed_url'] : null,
            ])),
            'enrich' => array_values(array_filter([
                ! empty($metadata['primary_topic']) ? 'Sujet : '.$metadata['primary_topic'] : null,
                isset($metadata['quality_score']) ? 'Qualité : '.$metadata['quality_score'].'/100' : null,
                isset($metadata['seo_score']) ? 'SEO : '.$metadata['seo_score'].'/100' : null,
                isset($metadata['word_count']) ? 'Mots extraits : '.$metadata['word_count'] : null,
                ($metadata['retry_scheduled'] ?? false) === true ? 'Nouvelle tentative planifiée dans '.($metadata['retry_delay_seconds'] ?? 60).'s' : null,
            ])),
            'generate' => array_values(array_filter([
                isset($metadata['article_type']) ? 'Type : '.$metadata['article_type'] : null,
                isset($metadata['article_id']) ? 'Article : '.$metadata['article_id'] : null,
                isset($metadata['cluster_id']) ? 'Cluster : '.$metadata['cluster_id'] : null,
                isset($metadata['item_ids']) && is_array($metadata['item_ids']) ? 'Sources : '.count($metadata['item_ids']) : null,
            ])),
            'cleanup' => array_values(array_filter([
                ! empty($metadata['command']) ? 'Commande : '.$metadata['command'] : null,
                isset($metadata['hours']) ? 'Fenêtre : '.$metadata['hours'].'h' : null,
            ])),
            default => [],
        };
    }
}
