<?php

namespace App\Console\Commands;

use App\Mail\PipelineHealthAlertMail;
use App\Models\PipelineJob;
use App\Services\PipelineHealthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class CheckPipelineHealthCommand extends Command
{
    protected $signature = 'pipeline:check-health';

    protected $description = 'Vérifie le silence du pipeline et envoie une alerte admin si nécessaire.';

    public function handle(PipelineHealthService $healthService): int
    {
        $job = $this->createMonitoringJob();

        $job?->start();

        try {
            $snapshot = $healthService->snapshot();
            $issues = $snapshot['issues'] ?? [];
            $alertSent = false;

            if ($issues !== [] && $this->shouldSendAlert($issues)) {
                $recipient = (string) config('vivat.admin_alert_email');

                if ($recipient !== '') {
                    Mail::to($recipient)->queue(new PipelineHealthAlertMail($snapshot));
                    $alertSent = true;
                }
            }

            $job?->update([
                'metadata' => array_merge($job->metadata ?? [], [
                    'kind' => 'health_check',
                    'snapshot' => $snapshot,
                    'alert_sent' => $alertSent,
                ]),
            ]);
            $job?->complete();

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $job?->fail($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function shouldSendAlert(array $issues): bool
    {
        $cooldownHours = max(1, (int) config('pipeline_schedule.monitoring.alert_cooldown_hours', 4));
        $signature = sha1(json_encode($issues));
        $cacheKey = 'pipeline:health-alert:'.$signature;

        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, true, now()->addHours($cooldownHours));

        return true;
    }

    private function createMonitoringJob(): ?PipelineJob
    {
        if (! Schema::hasTable('pipeline_jobs')) {
            return null;
        }

        return PipelineJob::create([
            'job_type' => 'cleanup',
            'status' => 'pending',
            'metadata' => [
                'kind' => 'health_check',
            ],
            'retry_count' => 0,
        ]);
    }
}
