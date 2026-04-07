<?php

use App\Jobs\EnrichContentJob;
use App\Jobs\FetchRssFeedJob;
use App\Jobs\GenerateArticleJob;
use App\Models\PipelineJob;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Services\ArticleSelectionService;
use App\Services\PipelineAutomationState;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Artisan;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'bot.protect' => \App\Http\Middleware\BotProtectionMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $pipelineSchedule = config('pipeline_schedule');
        $automationState = app(PipelineAutomationState::class);
        $trackPipelineRun = function (string $jobType, callable $callback, array $metadata = []): void {
            $job = PipelineJob::create([
                'job_type' => $jobType,
                'status' => 'pending',
                'metadata' => $metadata,
                'retry_count' => 0,
            ]);

            $job->start();

            try {
                $result = $callback();

                $job->update([
                    'metadata' => array_merge($job->metadata ?? [], is_array($result) ? $result : []),
                ]);
                $job->complete();
            } catch (\Throwable $exception) {
                $job->fail($exception->getMessage());
            }
        };

        if (data_get($pipelineSchedule, 'fetch_rss.enabled', true)) {
            $schedule->call(function () use ($trackPipelineRun) {
                $trackPipelineRun('fetch_rss', function (): array {
                    $feeds = RssFeed::dueForFetch()->get();
                    $feeds->each(fn (RssFeed $feed) => FetchRssFeedJob::dispatch($feed));

                    return [
                        'dispatched_feeds' => $feeds->count(),
                    ];
                });
            })->everySixHours()
                ->when(fn (): bool => ! $automationState->isPaused())
                ->name('pipeline:fetch-rss');
        }

        if (data_get($pipelineSchedule, 'enrich_items.enabled', true)) {
            $schedule->call(function () use ($pipelineSchedule, $trackPipelineRun) {
                $trackPipelineRun('enrich', function () use ($pipelineSchedule): array {
                    $limit = (int) data_get($pipelineSchedule, 'enrich_items.limit', 50);
                    $delaySeconds = (int) data_get($pipelineSchedule, 'enrich_items.delay_seconds', 3);
                    $items = RssItem::new()
                        ->orderByDesc('fetched_at')
                        ->orderByDesc('created_at')
                        ->limit($limit)
                        ->get();

                    $items->each(function (RssItem $item, int $index) use ($delaySeconds) {
                        EnrichContentJob::dispatch($item)
                            ->onQueue('enrichment')
                            ->delay(now()->addSeconds($index * $delaySeconds));
                    });

                    return [
                        'dispatched_items' => $items->count(),
                        'limit' => $limit,
                    ];
                });
            })->dailyAt((string) data_get($pipelineSchedule, 'enrich_items.time', '06:30'))
                ->when(fn (): bool => ! $automationState->isPaused())
                ->name('pipeline:enrich');
        }

        if (data_get($pipelineSchedule, 'generate_daily_article.enabled', true)) {
            $schedule->call(function () use ($pipelineSchedule, $trackPipelineRun) {
                $trackPipelineRun('generate', function () use ($pipelineSchedule): array {
                    /** @var ArticleSelectionService $selector */
                    $selector = app(ArticleSelectionService::class);
                    $count = max(1, (int) data_get($pipelineSchedule, 'generate_daily_article.count', 1));
                    $proposals = $selector->selectBestTopics($count);
                    $dispatched = 0;

                    foreach ($proposals as $proposal) {
                        $itemIds = collect($proposal['items'] ?? [])->pluck('id')->filter()->values()->all();

                        if ($itemIds === []) {
                            continue;
                        }

                        GenerateArticleJob::dispatch(
                            $itemIds,
                            data_get($proposal, 'category.id'),
                            null,
                            data_get($proposal, 'suggested_article_type', 'standard'),
                            data_get($proposal, 'suggested_min_words'),
                            data_get($proposal, 'suggested_max_words'),
                            data_get($proposal, 'context_priority'),
                        );
                        $dispatched++;
                    }

                    return [
                        'selected_proposals' => count($proposals),
                        'generated_articles' => $dispatched,
                    ];
                });
            })->dailyAt((string) data_get($pipelineSchedule, 'generate_daily_article.time', '08:00'))
                ->when(fn (): bool => ! $automationState->isPaused())
                ->name('pipeline:generate-daily');
        }

        if (data_get($pipelineSchedule, 'horizon_snapshot.enabled', true)) {
            $schedule->call(function () use ($trackPipelineRun): void {
                $trackPipelineRun('cleanup', function (): array {
                    Artisan::call('horizon:snapshot');

                    return [
                        'command' => 'horizon:snapshot',
                    ];
                });
            })->everyFiveMinutes()->name('pipeline:horizon-snapshot');
        }

        // Digest newsletter hebdomadaire — chaque lundi à 8h00
        $schedule->command('newsletter:send-digest')
            ->weeklyOn(1, '08:00')
            ->name('newsletter:weekly-digest')
            ->withoutOverlapping();

        if (data_get($pipelineSchedule, 'prune_failed_jobs.enabled', true)) {
            $schedule->call(function () use ($pipelineSchedule, $trackPipelineRun): void {
                $hours = (int) data_get($pipelineSchedule, 'prune_failed_jobs.hours', 168);

                $trackPipelineRun('cleanup', function () use ($hours): array {
                    Artisan::call('queue:prune-failed', ['--hours' => $hours]);

                    return [
                        'command' => 'queue:prune-failed',
                        'hours' => $hours,
                    ];
                });
            })->daily()->name('pipeline:prune-failed');
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
