<?php

namespace App\Console\Commands;

use App\Models\RssItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Exporte les items RSS (et enrichis) en CSV pour analyse de tendances.
 *
 * Usage : volume important (500-1000 articles par source, 3 sources) →
 * exporter en CSV → utiliser avec ChatGPT (prompt fort) pour identifier :
 * - connexions entre articles, corrélations, tendances
 * - meilleur sujet pour écrire un article
 * - poids à attribuer, hot news vs articles de fond (longueur en mots)
 *
 * Voir docs/WORKFLOW_MENTOR_CSV_ET_IA.md pour le workflow complet et le prompt ChatGPT.
 */
class ExportTrendsCsvCommand extends Command
{
    protected $signature = 'pipeline:export-trends-csv
                            {--limit=1000 : Nombre max total (ignoré si per-source)}
                            {--per-source= : Max items par source (ex: 1000)}
                            {--sources=3 : Nombre de sources}
                            {--status= : Filtrer par statut (new, enriched, used)}
                            {--output= : Fichier de sortie (défaut: storage/app/trends_export_YYYY-MM-DD.csv)}';

    protected $description = 'Exporte les items RSS en CSV pour analyse tendances (workflow mentor : 500-1000 par source × 3 sources).';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $perSource = $this->option('per-source') ? (int) $this->option('per-source') : null;
        $sourcesCount = (int) ($this->option('sources') ?? 3);
        $status = $this->option('status');
        $output = $this->option('output') ?: 'trends_export_' . now()->format('Y-m-d') . '.csv';

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
            $this->warn('Aucun item à exporter.');

            $counts = RssItem::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->all();

            if (! empty($counts)) {
                $this->line('Répartition des statuts en base :');
                foreach ($counts as $s => $n) {
                    $this->line("  - {$s} : {$n}");
                }
                if (($status === 'enriched' || $status === null) && empty($counts['enriched'])) {
                    $this->newLine();
                    $this->line('Conseil : si aucun item n\'est "enriched", les jobs d\'enrichissement ont peut-être échoué (scraping ou OpenAI).');
                    $this->line('Exportez sans filtre pour voir les données brutes :');
                    $this->line('  php artisan pipeline:export-trends-csv --output=trends_mentor.csv');
                }
            }

            return self::FAILURE;
        }

        $path = str_starts_with($output, '/') ? $output : storage_path('app/' . ltrim($output, '/'));

        $handle = fopen($path, 'w');
        if (! $handle) {
            $this->error("Impossible de créer le fichier : {$path}");

            return self::FAILURE;
        }

        // BOM UTF-8 pour Excel
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'date',
            'title',
            'category',
            'source',
            'primary_topic',
            'seo_keywords',
            'quality_score',
            'seo_score',
            'url',
            'status',
        ], ';');

        foreach ($items as $item) {
            $enriched = $item->enrichedItem;
            $seoKeywords = $enriched && is_array($enriched->seo_keywords)
                ? implode(' | ', $enriched->seo_keywords)
                : '';

            fputcsv($handle, [
                $item->published_at?->format('Y-m-d H:i') ?? $item->fetched_at?->format('Y-m-d H:i') ?? '',
                $this->escapeCsvField((string) ($item->title ?? '')),
                $item->category?->name ?? '',
                $item->rssFeed?->source?->name ?? '',
                $enriched?->primary_topic ?? '',
                $seoKeywords,
                $enriched?->quality_score ?? '',
                $enriched?->seo_score ?? '',
                $item->url ?? '',
                $item->status ?? '',
            ], ';');
        }

        fclose($handle);

        $this->info("Export terminé : {$path} ({$items->count()} lignes).");
        $this->line('Utilisation : docs/WORKFLOW_MENTOR_CSV_ET_IA.md — ouvrir avec Excel, puis analyser avec ChatGPT (prompt fort : connexions, tendances, hot news vs article de fond).');

        return self::SUCCESS;
    }

    private function escapeCsvField(string $value): string
    {
        $value = str_replace(["\r", "\n"], ' ', $value);

        return $value;
    }
}
