<?php

use App\Jobs\EnrichContentJob;
use App\Jobs\FetchRssFeedJob;
use App\Models\RssFeed;
use App\Models\RssItem;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->call(function () {
            RssFeed::dueForFetch()->get()->each(fn (RssFeed $feed) => FetchRssFeedJob::dispatch($feed));
        })->everyThirtyMinutes()->name('pipeline:fetch-rss');

        $schedule->call(function () {
            RssItem::new()->limit(50)->get()->each(function (RssItem $item, int $index) {
                EnrichContentJob::dispatch($item)
                    ->onQueue('enrichment')
                    ->delay(now()->addSeconds($index * 3));
            });
        })->hourly()->name('pipeline:enrich');

        $schedule->command('horizon:snapshot')->everyFiveMinutes();
        $schedule->command('queue:prune-failed', ['--hours' => 168])->daily();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
