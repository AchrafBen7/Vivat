<?php

namespace App\Console\Commands;

use App\Models\RssItem;
use App\Services\TrendsAnalysisService;
use Illuminate\Console\Command;

/**
 * Exporte les items en CSV (depuis la BDD ou un fichier), envoie le CSV à l'API OpenAI
 * avec le prompt prédéfini (config/trends_analysis.php), et affiche/sauvegarde l'analyse
 * (connexions, tendances, meilleur sujet, poids, hot news vs article de fond, fiche rédactionnelle).
 */
class AnalyzeTrendsCommand extends Command
{
    protected $signature = 'pipeline:analyze-trends
                            {--csv= : Fichier CSV à analyser (ex: storage/app/trends_mentor.csv). Si absent, génère le CSV depuis la BDD.}
                            {--limit=500 : Nombre max de lignes si CSV généré depuis la BDD}
                            {--per-source= : Max items par source (si pas --csv)}
                            {--sources=3 : Nombre de sources (si pas --csv)}
                            {--status= : Filtrer par statut (new, enriched, used) si CSV depuis BDD}
                            {--output= : Fichier de sortie pour l\'analyse (ex: storage/app/trends_analysis_YYYY-MM-DD.txt)}';

    protected $description = 'Analyse les tendances via OpenAI : lit le CSV (ou l\'export BDD), applique le prompt prédéfini, affiche et optionnellement sauvegarde l\'analyse.';

    public function handle(TrendsAnalysisService $service): int
    {
        $csvPath = $this->option('csv');
        $outputPath = $this->option('output') ?: 'trends_analysis_' . now()->format('Y-m-d') . '.txt';

        if ($csvPath !== null && $csvPath !== '') {
            $fullPath = str_starts_with($csvPath, '/') ? $csvPath : storage_path('app/' . ltrim($csvPath, '/'));
            if (! is_file($fullPath)) {
                $this->error("Fichier introuvable : {$fullPath}");

                return self::FAILURE;
            }
            $csvContent = file_get_contents($fullPath);
            $this->info("CSV lu : " . $fullPath . " (" . number_format(strlen($csvContent)) . " caractères).");
        } else {
            $csvContent = $this->buildCsvFromDb();
            if ($csvContent === '') {
                $this->warn('Aucun item en base pour générer le CSV.');

                return self::FAILURE;
            } 
            
            $this->info("CSV généré depuis la BDD (" . number_format(strlen($csvContent)) . " caractères).");
        }

        $this->line('Envoi à OpenAI avec le prompt prédéfini...');

        $result = $service->analyze($csvContent);

        if (! $result['success']) {
            $this->error($result['error'] ?? 'Erreur inconnue.');

            return self::FAILURE;
        }

        $analysis = $result['analysis'];

        $this->newLine();
        $this->line('--- Analyse des tendances ---');
        $this->newLine();
        $this->line($analysis);
        $this->newLine();

        $outFile = str_starts_with($outputPath, '/') ? $outputPath : storage_path('app/' . ltrim($outputPath, '/'));
        if (file_put_contents($outFile, $analysis) !== false) {
            $this->info("Analyse sauvegardée : {$outFile}");
        }

        return self::SUCCESS;
    }

    private function buildCsvFromDb(): string
    {
        $limit = (int) $this->option('limit');
        $perSource = $this->option('per-source') ? (int) $this->option('per-source') : null;
        $sourcesCount = (int) ($this->option('sources') ?? 3);
        $status = $this->option('status');

        $query = RssItem::query()
            ->with(['enrichedItem', 'rssFeed.source', 'category'])
            ->whereHas('rssFeed')
            ->orderByDesc('published_at')
            ->orderByDesc('fetched_at');

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $items = collect();

        if ($perSource !== null && $perSource > 0 && $sourcesCount > 0) {
            $sourceIds = RssItem::query()
                ->join('rss_feeds', 'rss_items.rss_feed_id', '=', 'rss_feeds.id')
                ->when($status !== null && $status !== '', fn ($q) => $q->where('rss_items.status', $status))
                ->select('rss_feeds.source_id')
                ->selectRaw('count(*) as total')
                ->groupBy('rss_feeds.source_id')
                ->orderByDesc('total')
                ->limit($sourcesCount)
                ->pluck('rss_feeds.source_id')
                ->filter();

            foreach ($sourceIds as $sourceId) {
                $sourceItems = (clone $query)
                    ->whereHas('rssFeed', fn ($q) => $q->where('source_id', $sourceId))
                    ->limit($perSource)
                    ->get();
                $items = $items->merge($sourceItems);
            }

            $items = $items->values();
        } else {
            $items = $query->limit($limit)->get();
        }

        if ($items->isEmpty()) {
            return '';
        }

        $buffer = "date;title;category;source;primary_topic;seo_keywords;quality_score;seo_score;url;status\n";

        foreach ($items as $item) {
            $enriched = $item->enrichedItem;
            $seoKeywords = $enriched && is_array($enriched->seo_keywords)
                ? implode(' | ', $enriched->seo_keywords)
                : '';
            $title = str_replace(["\r", "\n", ';'], ' ', (string) ($item->title ?? ''));
            $buffer .= sprintf(
                "%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                $item->published_at?->format('Y-m-d H:i') ?? $item->fetched_at?->format('Y-m-d H:i') ?? '',
                $title,
                $item->category?->name ?? '',
                $item->rssFeed?->source?->name ?? '',
                $enriched?->primary_topic ?? '',
                $seoKeywords,
                $enriched?->quality_score ?? '',
                $enriched?->seo_score ?? '',
                $item->url ?? '',
                $item->status ?? ''
            );
        }

        return $buffer;
    }
}
