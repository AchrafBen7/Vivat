<?php

namespace App\Console\Commands;

use App\Jobs\FetchRssFeedJob;
use App\Models\RssFeed;
use Illuminate\Console\Command;

class FetchRssFeedsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'rss:fetch
                            {--due : Seulement les flux dus pour un fetch (défaut)}
                            {--all : Tous les flux actifs}
                            {--limit= : Nombre max de flux à traiter}';

    protected $description = 'Dispatch FetchRssFeedJob pour les flux RSS (due ou tous).';

    public function handle(): int
    {
        $query = $this->option('all')
            ? RssFeed::active()
            : RssFeed::dueForFetch();

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $feeds = $query->get();
        if ($feeds->isEmpty()) {
            $this->info('Aucun flux à traiter.');

            return self::SUCCESS;
        }

        foreach ($feeds as $feed) {
            FetchRssFeedJob::dispatch($feed);
        }

        $this->info(sprintf('%d job(s) FetchRssFeedJob dispatchés (queue: rss).', $feeds->count()));

        return self::SUCCESS;
    }
}
