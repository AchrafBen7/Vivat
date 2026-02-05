<?php

namespace App\Console\Commands;

use App\Models\RssItem;
use Illuminate\Console\Command;

class GenerateDailyArticlesCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'articles:generate
                            {--count=5 : Nombre d’articles à suggérer (ne dispatch pas automatiquement)}
                            {--dispatch : Dispatch réel de GenerateArticleJob (nécessite item_ids manuels)}';

    protected $description = 'Affiche des groupes d’items enrichis prêts pour la génération ; pour lancer une génération, utiliser l’API POST /api/articles/generate ou generate-async.';

    public function handle(): int
    {
        $count = (int) $this->option('count');

        $items = RssItem::query()
            ->where('status', 'enriched')
            ->whereHas('enrichedItem')
            ->with(['enrichedItem', 'category'])
            ->orderBy('fetched_at', 'desc')
            ->limit($count * 5)
            ->get();

        if ($items->isEmpty()) {
            $this->warn('Aucun item enrichi trouvé. Lancez d’abord content:enrich puis attendez les jobs.');

            return self::SUCCESS;
        }

        $this->info(sprintf('%d item(s) enrichi(s) disponibles. Pour générer un article :', $items->count()));
        $this->line('  • API : POST /api/articles/generate avec body {"item_ids": ["uuid1", "uuid2"], "category_id": "uuid?"}');
        $this->line('  • Ou POST /api/articles/generate-async pour traitement en queue.');
        $this->newLine();
        $this->table(
            ['UUID', 'Titre', 'Catégorie', 'Quality'],
            $items->take(20)->map(fn ($i) => [
                $i->id,
                \Illuminate\Support\Str::limit($i->title, 50),
                $i->category?->name ?? '-',
                $i->enrichedItem?->quality_score ?? '-',
            ])
        );

        return self::SUCCESS;
    }
}
