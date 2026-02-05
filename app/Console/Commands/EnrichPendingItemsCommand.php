<?php

namespace App\Console\Commands;

use App\Jobs\EnrichContentJob;
use App\Models\RssItem;
use Illuminate\Console\Command;

class EnrichPendingItemsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'content:enrich
                            {--limit=50 : Nombre max d’items à envoyer en queue}
                            {--delay=3 : Délai en secondes entre chaque dispatch}';

    protected $description = 'Dispatch EnrichContentJob pour les RssItem en statut "new" (queue: enrichment).';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $delayStep = (int) $this->option('delay');

        $items = RssItem::new()->limit($limit)->get();
        if ($items->isEmpty()) {
            $this->info('Aucun item "new" à enrichir.');

            return self::SUCCESS;
        }

        foreach ($items as $index => $item) {
            EnrichContentJob::dispatch($item)
                ->onQueue('enrichment')
                ->delay(now()->addSeconds($index * $delayStep));
        }

        $this->info(sprintf('%d job(s) EnrichContentJob dispatchés (queue: enrichment).', $items->count()));

        return self::SUCCESS;
    }
}
