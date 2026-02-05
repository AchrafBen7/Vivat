<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Models\Source;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    /**
     * Statistiques globales du pipeline (pour dashboard ou monitoring).
     */
    public function index(): JsonResponse
    {
        $sourcesCount = Source::count();
        $feedsCount = RssFeed::active()->count();
        $itemsByStatus = RssItem::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();
        $articlesByStatus = Article::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();
        $articlesPublished = Article::published()->count();

        return response()->json([
            'sources' => $sourcesCount,
            'rss_feeds_active' => $feedsCount,
            'rss_items_by_status' => $itemsByStatus,
            'articles_by_status' => $articlesByStatus,
            'articles_published' => $articlesPublished,
        ]);
    }
}
