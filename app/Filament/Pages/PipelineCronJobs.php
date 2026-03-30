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
            ->limit(80)
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
                            'started_at' => $job->started_at?->format('H:i:s'),
                            'completed_at' => $job->completed_at?->format('H:i:s'),
                            'error_message' => $job->error_message,
                            'metadata' => $job->metadata ?? [],
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
            'generate' => 'Génération quotidienne',
            'cleanup' => 'Maintenance',
            default => ucfirst(str_replace('_', ' ', $jobType)),
        };
    }
}
