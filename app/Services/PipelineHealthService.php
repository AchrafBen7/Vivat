<?php

namespace App\Services;

use App\Models\EnrichedItem;
use App\Models\PipelineJob;
use App\Models\RssItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

class PipelineHealthService
{
    public function snapshot(): array
    {
        $now = now();
        $silenceHours = max(1, (int) config('pipeline_schedule.monitoring.enrichment_silence_hours', 4));
        $horizonStaleMinutes = max(1, (int) config('pipeline_schedule.monitoring.horizon_stale_minutes', 15));

        $newItemsCount = $this->tableExists('rss_items')
            ? RssItem::query()->where('status', 'new')->count()
            : 0;
        $hasPipelineJobsTable = $this->tableExists('pipeline_jobs');

        $latestEnrichedAt = $this->tableExists('enriched_items')
            ? EnrichedItem::query()->max('enriched_at')
            : null;

        $latestPipelineActivityAt = $hasPipelineJobsTable
            ? PipelineJob::query()->max('created_at')
            : null;

        $latestHorizonSnapshotAt = $hasPipelineJobsTable
            ? PipelineJob::query()
                ->where('job_type', 'cleanup')
                ->where('status', 'completed')
                ->where('metadata->command', 'horizon:snapshot')
                ->max('completed_at')
            : null;

        $failedJobsCount = $this->tableExists('failed_jobs')
            ? (int) DB::table('failed_jobs')->count()
            : 0;

        $enrichmentStale = $newItemsCount > 0 && $this->isOlderThanHours($latestEnrichedAt, $silenceHours, $now);
        $horizonStale = $hasPipelineJobsTable && $this->isOlderThanMinutes($latestHorizonSnapshotAt, $horizonStaleMinutes, $now);

        $issues = array_values(array_filter([
            $enrichmentStale ? sprintf(
                'Aucun item enrichi depuis %dh alors que %d item(s) sont encore au statut new.',
                $silenceHours,
                $newItemsCount
            ) : null,
            $horizonStale ? sprintf(
                'Aucun snapshot Horizon récent depuis %d minute(s).',
                $horizonStaleMinutes
            ) : null,
            $failedJobsCount > 0 ? sprintf('%d job(s) sont présents dans failed_jobs.', $failedJobsCount) : null,
        ]));

        return [
            'status' => $issues === [] ? 'healthy' : 'degraded',
            'checked_at' => $now->toIso8601String(),
            'pipeline' => [
                'latest_pipeline_activity_at' => $this->serializeDate($latestPipelineActivityAt),
                'latest_enriched_at' => $this->serializeDate($latestEnrichedAt),
                'new_items_count' => $newItemsCount,
                'enrichment_stale' => $enrichmentStale,
                'failed_jobs_count' => $failedJobsCount,
            ],
            'horizon' => [
                'latest_snapshot_at' => $this->serializeDate($latestHorizonSnapshotAt),
                'snapshot_fresh' => ! $horizonStale,
                'stale_minutes_threshold' => $horizonStaleMinutes,
            ],
            'queues' => [
                'rss' => $this->queueSize('rss'),
                'enrichment' => $this->queueSize('enrichment'),
                'default' => $this->queueSize('default'),
            ],
            'issues' => $issues,
        ];
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    private function queueSize(string $queue): ?int
    {
        try {
            return Queue::size($queue);
        } catch (\Throwable) {
            return null;
        }
    }

    private function isOlderThanHours(mixed $date, int $hours, Carbon $now): bool
    {
        if (! $date) {
            return true;
        }

        return Carbon::parse($date)->lt($now->copy()->subHours($hours));
    }

    private function isOlderThanMinutes(mixed $date, int $minutes, Carbon $now): bool
    {
        if (! $date) {
            return true;
        }

        return Carbon::parse($date)->lt($now->copy()->subMinutes($minutes));
    }

    private function serializeDate(mixed $date): ?string
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date)->toIso8601String();
    }
}
