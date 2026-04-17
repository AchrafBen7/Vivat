<?php

namespace App\Console\Commands;

use App\Jobs\FetchMissingCoverImagesJob;
use Illuminate\Console\Command;

class BackfillArticleCoversCommand extends Command
{
    protected $signature = 'vivat:backfill-article-covers {--batches=1 : Number of jobs to dispatch} {--limit=20 : Number of articles per job} {--sync : Run immediately instead of queueing}';

    protected $description = 'Backfill missing article cover images using the configured image provider.';

    public function handle(): int
    {
        $batches = max(1, (int) $this->option('batches'));
        $limit = max(1, (int) $this->option('limit'));
        $sync = (bool) $this->option('sync');

        for ($i = 1; $i <= $batches; $i++) {
            $job = new FetchMissingCoverImagesJob($limit);

            if ($sync) {
                $job->handle(app(\App\Services\CoverImageService::class));
                $this->info("Batch {$i}/{$batches} processed synchronously.");
                continue;
            }

            dispatch($job);
            $this->info("Batch {$i}/{$batches} dispatched.");
        }

        return self::SUCCESS;
    }
}
