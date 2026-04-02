<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\CategoryResource;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * GET /api/public/home
     *
     * Données pour la page d'accueil :
     * - top_news : 1 article hot_news (carré grand, image bg, label "Top news")
     * - featured : articles importants avec image (carrés moyens)
     * - latest : section "Dernières actualités" avec display_type par article (featured | standard | secondary)
     * - categories : les 9 rubriques pour "Découvrez vos rubriques préférées"
     * - writer_cta : URLs pour le bouton "Rédiger un article"
     */
    public function index(Request $request): JsonResponse
    {
        $locale = content_locale($request);
        $cacheKey = config('vivat.home_cache_key_prefix', 'vivat.home.v2') . '.' . $locale;
        $cacheTtl = (int) config('vivat.home_cache_ttl', 300); // 5 min
        $closure = function () use ($locale) {
            $topNews = Article::published()
                ->forLocale($locale)
                ->where('article_type', 'hot_news')
                ->with('category')
                ->orderByDesc('published_at')
                ->first();

            $featuredLimit = (int) config('vivat.home_featured_count', 4);
            $latestLimit = (int) config('vivat.home_latest_count', 12);
            $categoriesLimit = (int) config('vivat.home_categories_count', 9);

            $baseQuery = Article::published()->forLocale($locale)->with('category')->orderByDesc('published_at');
            $excludeId = $topNews?->id;

            $featuredQuery = clone $baseQuery;
            if ($excludeId) {
                $featuredQuery->where('id', '!=', $excludeId);
            }
            $featuredQuery->where(function ($q) {
                $q->where('article_type', 'hot_news')->orWhereNotNull('cover_image_url');
            });
            $featured = $featuredQuery->limit($featuredLimit)->get();

            $featuredIds = $featured->pluck('id')->push($excludeId)->filter()->unique()->values()->all();
            $latestQuery = Article::published()->forLocale($locale)->with('category')->orderByDesc('published_at');
            if (count($featuredIds) > 0) {
                $latestQuery->whereNotIn('id', $featuredIds);
            }
            $latestCollection = $latestQuery->limit($latestLimit + 30)->get()->unique('id')->values();
            $latest = $this->dedupeLatestByTitle($latestCollection, $latestLimit);

            $categories = Category::query()
                ->withCount(['articles as published_articles_count' => fn ($q) => $q->where('status', 'published')->where('language', $locale)])
                ->orderedForHome()
                ->limit($categoriesLimit)
                ->get();

            return [
                'top_news' => $topNews,
                'featured' => $featured,
                'latest' => $latest,
                'categories' => $categories,
            ];
        };
        $data = config('vivat.disable_page_cache') ? $closure() : Cache::remember($cacheKey, $cacheTtl, $closure);

        $topNewsResource = $data['top_news']
            ? array_merge(
                (new ArticleResource($data['top_news']))->toArray($request),
                ['display_type' => 'top_news']
            )
            : null;

        $featuredResources = $data['featured']->map(fn ($a) => array_merge(
            (new ArticleResource($a))->toArray($request),
            ['display_type' => 'featured']
        ));

        $latestWithDisplayType = $data['latest']->map(function ($article) use ($request) {
            $arr = (new ArticleResource($article))->toArray($request);
            $arr['display_type'] = $article->cover_image_url ? 'standard' : 'secondary';
            return $arr;
        });

        return response()->json([
            'top_news' => $topNewsResource,
            'featured' => $featuredResources,
            'latest' => [
                'label' => 'Dernières actualités',
                'articles' => $latestWithDisplayType,
            ],
            'categories' => CategoryResource::collection($data['categories']),
            'writer_cta' => $this->writerCta($request),
        ]);
    }

    /**
     * Garde une seule occurrence par titre normalisé dans "Dernières actualités", puis limite au nombre demandé.
     *
     * @param  Collection<int, Article>  $articles
     * @return Collection<int, Article>
     */
    private function dedupeLatestByTitle(Collection $articles, int $limit): Collection
    {
        $seenTitle = [];
        $out = [];
        foreach ($articles as $article) {
            $key = mb_strtolower(trim((string) ($article->title ?? '')));
            if ($key === '' || isset($seenTitle[$key])) {
                continue;
            }
            $seenTitle[$key] = true;
            $out[] = $article;
            if (count($out) >= $limit) {
                break;
            }
        }
        return collect($out);
    }

    /**
     * URLs pour le bouton "Rédiger un article" :
     * - non connecté → signup_url (création compte rédacteur)
     * - connecté contributor/admin → dashboard_url (profil rédacteur / liste soumissions)
     */
    private function writerCta(Request $request): array
    {
        $user = $request->user();
        $isContributor = $user && $user->hasRole(['contributor', 'admin']);

        return [
            'signup_url' => config('vivat.writer_signup_url', '/register'),
            'dashboard_url' => config('vivat.writer_dashboard_url', '/contributor/submissions'),
            'is_authenticated_as_contributor' => $isContributor,
        ];
    }
}
