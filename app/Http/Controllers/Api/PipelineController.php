<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\EnrichContentJob;
use App\Jobs\FetchRssFeedJob;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Services\ArticleSelectionService;
use App\Services\TrendsAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PipelineController extends Controller
{
    /**
     * Déclenche le fetch de tous les flux RSS actifs (ou un seul via feed_id).
     */
    public function fetchRss(Request $request): JsonResponse
    {
        $feedId = $request->input('feed_id');

        if ($feedId) {
            $feed = RssFeed::findOrFail($feedId);
            FetchRssFeedJob::dispatch($feed);

            return response()->json([
                'message' => "Fetch dispatché pour le flux : {$feed->feed_url}",
                'feed_id' => $feed->id,
            ]);
        }

        $all = $request->boolean('all');
        $feeds = $all ? RssFeed::active()->get() : RssFeed::dueForFetch()->get();

        if ($feeds->isEmpty()) {
            return response()->json(['message' => 'Aucun flux à traiter.', 'count' => 0]);
        }

        foreach ($feeds as $feed) {
            FetchRssFeedJob::dispatch($feed);
        }

        return response()->json([
            'message' => sprintf('%d job(s) FetchRssFeedJob dispatché(s).', $feeds->count()),
            'count' => $feeds->count(),
            'mode' => $all ? 'all_active' : 'due_only',
        ]);
    }

    /**
     * Déclenche l'enrichissement des items "new".
     */
    public function enrich(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 50), 200);
        $delayStep = (int) $request->input('delay', 3);

        $items = RssItem::new()->limit($limit)->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Aucun item "new" à enrichir.', 'count' => 0]);
        }

        foreach ($items as $index => $item) {
            EnrichContentJob::dispatch($item)
                ->onQueue('enrichment')
                ->delay(now()->addSeconds($index * $delayStep));
        }

        return response()->json([
            'message' => sprintf('%d job(s) EnrichContentJob dispatché(s).', $items->count()),
            'count' => $items->count(),
        ]);
    }

    /**
     * Sélection intelligente : propose les meilleurs topics à générer.
     *
     * Répond à : "Pourquoi générer CET article et pas un autre ?"
     */
    public function selectItems(Request $request, ArticleSelectionService $selector): JsonResponse
    {
        $count = min((int) $request->input('count', 3), 10);
        $categoryId = $request->input('category_id');

        $proposals = $selector->selectBestTopics($count, $categoryId);

        if (empty($proposals)) {
            return response()->json([
                'message' => 'Aucun item enrichi disponible. Lancez d\'abord le fetch RSS puis l\'enrichissement.',
                'proposals' => [],
            ]);
        }

        return response()->json([
            'message' => sprintf('%d proposition(s) d\'article classées par pertinence.', count($proposals)),
            'strategy' => [
                'scoring' => 'Fraîcheur (25%) + Qualité contenu (25%) + Potentiel SEO (30%) + Diversité sources (20%)',
                'grouping' => 'Items regroupés par similarité de mots-clés (Jaccard >= 20%)',
                'priority' => 'Multi-sources > mono-source. Mots-clés longue traîne > génériques.',
            ],
            'proposals' => $proposals,
        ]);
    }

    /**
     * Statut global du pipeline.
     */
    public function status(): JsonResponse
    {
        $feedsTotal = RssFeed::count();
        $feedsActive = RssFeed::active()->count();
        $feedsDue = RssFeed::dueForFetch()->count();

        $itemsByStatus = RssItem::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        return response()->json([
            'rss_feeds' => [
                'total' => $feedsTotal,
                'active' => $feedsActive,
                'due_for_fetch' => $feedsDue,
            ],
            'rss_items_by_status' => $itemsByStatus,
            'total_rss_items' => array_sum($itemsByStatus),
        ]);
    }

    /**
     * Exporte les items RSS (et enrichis) en CSV pour analyse tendances.
     * Télécharge le fichier CSV (séparateur ;, UTF-8 BOM pour Excel).
     *
     * Query params : limit (défaut 1000), per_source, sources (défaut 3), status (new|enriched|used).
     * Ex. : GET /api/pipeline/export-trends-csv?limit=500&sources=3
     */
    public function exportTrendsCsv(Request $request): StreamedResponse|JsonResponse
    {
        $limit = (int) $request->input('limit', 1000);
        $perSource = $request->input('per_source') ? (int) $request->input('per_source') : null;
        $sourcesCount = (int) $request->input('sources', 3);
        $status = $request->input('status');

        $csvContent = $this->buildCsvForTrends($limit, $perSource, $sourcesCount, $status);

        if ($csvContent === null || $csvContent === '') {
            return response()->json([
                'message' => 'Aucun item à exporter. Vérifiez que des rss_items existent (fetch RSS puis enrichissement).',
            ], 422);
        }

        $filename = 'trends_export_' . now()->format('Y-m-d') . '.csv';
        $bom = "\xEF\xBB\xBF";

        return response()->streamDownload(function () use ($bom, $csvContent) {
            echo $bom . $csvContent;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Analyse des tendances via OpenAI : envoie le CSV (fichier uploadé ou généré depuis la BDD)
     * avec le prompt prédéfini (config/trends_analysis.php), retourne l'analyse (connexions,
     * tendances, meilleur sujet, poids, hot news vs article de fond, fiche rédactionnelle).
     *
     * Body (JSON) optionnel : limit, per_source, sources, status (pour génération CSV depuis BDD).
     * Ou envoie un fichier CSV en multipart : csv_file
     */
    public function analyzeTrends(Request $request, TrendsAnalysisService $service): JsonResponse
    {
        $csvContent = null;

        if ($request->hasFile('csv_file')) {
            $file = $request->file('csv_file');
            if (! $file->isValid()) {
                return response()->json(['success' => false, 'error' => 'Fichier CSV invalide.'], 422);
            }
            $csvContent = file_get_contents($file->getRealPath());
        } else {
            $csvContent = $this->buildCsvForTrends(
                (int) $request->input('limit', 500),
                $request->input('per_source') ? (int) $request->input('per_source') : null,
                (int) $request->input('sources', 3),
                $request->input('status')
            );
        }

        if ($csvContent === null || $csvContent === '') {
            return response()->json([
                'success' => false,
                'error' => 'Aucune donnée à analyser. Envoyez un fichier CSV (csv_file) ou assurez-vous que la BDD contient des rss_items.',
            ], 422);
        }

        $result = $service->analyze($csvContent);

        if (! $result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Erreur lors de l\'analyse.',
            ], 422);
        }

        $payload = [
            'success' => true,
            'analysis' => $result['analysis'],
        ];
        if (! empty($result['truncated'])) {
            $payload['truncated'] = true;
            $payload['truncated_at_chars'] = $result['truncated_at_chars'] ?? null;
            $payload['note'] = 'Le CSV a été tronqué pour tenir dans le contexte OpenAI. L\'IA n\'a pas besoin de tout lire pour identifier tendances et connexions.';
        }

        return response()->json($payload);
    }

    /**
     * Construit le contenu CSV depuis la BDD (même logique que ExportTrendsCsvCommand).
     */
    private function buildCsvForTrends(int $limit, ?int $perSource, int $sourcesCount, ?string $status): ?string
    {
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
            return null;
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
