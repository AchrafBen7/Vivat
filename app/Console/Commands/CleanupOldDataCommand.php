<?php

namespace App\Console\Commands;

use App\Models\RssItem;
use Illuminate\Console\Command;

class CleanupOldDataCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'cleanup:old
                            {--days=90 : Supprimer les rss_items (used/failed) plus anciens que N jours}
                            {--prune-failed=168 : Heures pour queue:prune-failed (défaut 168 = 7 jours)}
                            {--dry-run : Afficher sans supprimer}';

    protected $description = 'Nettoie les jobs échoués et optionnellement les anciens rss_items.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $pruneHours = (int) $this->option('prune-failed');
        $dryRun = $this->option('dry-run');

        $this->info('Nettoyage des jobs échoués (queue)...');
        if (! $dryRun) {
            $this->call('queue:prune-failed', ['--hours' => $pruneHours]);
        } else {
            $this->line('(dry-run: queue:prune-failed non exécuté)');
        }

        $cutoff = now()->subDays($days);
        $query = RssItem::query()
            ->whereIn('status', ['used', 'failed', 'ignored'])
            ->where('fetched_at', '<', $cutoff);

        $count = $query->count();
        if ($count === 0) {
            $this->info(sprintf('Aucun rss_item à supprimer (plus vieux que %d jours).', $days));

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info(sprintf('[dry-run] %d rss_item(s) seraient supprimés.', $count));

            return self::SUCCESS;
        }

        if (! $this->confirm(sprintf('Supprimer %d rss_item(s) (status used/failed/ignored, > %d jours) ?', $count, $days))) {
            return self::SUCCESS;
        }

        $query->delete();
        $this->info(sprintf('%d rss_item(s) supprimés.', $count));

        return self::SUCCESS;
    }
}
