<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateArticleRequest;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Jobs\GenerateArticleJob;
use App\Models\Article;
use App\Models\Category;
use App\Models\RssItem;
use App\Services\ArticleGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /* ================================================================== */
    /*  ADMIN — all articles (any status)                                 */
    /* ================================================================== */

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Article::query()->with('category');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->string('category_id'));
        }

        $perPage = min((int) $request->get('per_page', 15), 50);
        $articles = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ArticleResource::collection($articles);
    }

    /* ================================================================== */
    /*  PUBLIC — published only                                           */
    /* ================================================================== */

    /**
     * GET /api/public/articles — liste des articles publiés
     */
    public function published(Request $request): AnonymousResourceCollection
    {
        $query = Article::published()->with('category');

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
        }
        if ($request->filled('reading_time_max')) {
            $query->where('reading_time', '<=', (int) $request->input('reading_time_max'));
        }

        $sortBy = $request->input('sort', 'published_at');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['published_at', 'reading_time', 'quality_score', 'title'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->get('per_page', 12), 50);

        return ArticleResource::collection($query->paginate($perPage));
    }

    /**
     * GET /api/public/articles/{slug} — article par slug
     */
    public function showBySlug(Article $article): ArticleResource
    {
        $article->load(['category', 'articleSources']);

        return new ArticleResource($article);
    }

    /**
     * GET /api/public/search?q=...&category=...&reading_time_max=...&date_from=...
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $query = Article::published()->with('category');

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            if (DB::getDriverName() === 'mysql' && strlen($q) >= 2) {
                $query->where(function ($builder) use ($q) {
                    $builder->whereFullText(['title', 'excerpt'], $q)
                        ->orWhere('meta_description', 'LIKE', '%'.addcslashes($q, '%_\\').'%');
                });
            } else {
                $query->where(function ($builder) use ($q) {
                    $esc = addcslashes($q, '%_\\');
                    $builder->where('title', 'LIKE', "%{$esc}%")
                        ->orWhere('excerpt', 'LIKE', "%{$esc}%")
                        ->orWhere('meta_description', 'LIKE', "%{$esc}%");
                });
            }
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
        }

        if ($request->filled('reading_time_max')) {
            $query->where('reading_time', '<=', (int) $request->input('reading_time_max'));
        }

        if ($request->filled('date_from')) {
            $query->where('published_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('published_at', '<=', $request->input('date_to'));
        }

        $perPage = min((int) $request->get('per_page', 12), 50);

        return ArticleResource::collection(
            $query->orderBy('published_at', 'desc')->paginate($perPage)
        );
    }

    /**
     * GET /api/public/recommendations?interests=env,sante&session_id=xxx&limit=6
     */
    public function recommendations(Request $request): AnonymousResourceCollection
    {
        $limit = min((int) $request->input('limit', 6), 20);
        $interests = $request->input('interests', []);
        $sessionId = $request->input('session_id');
        $userId = $request->user()?->id;

        if (is_string($interests)) {
            $interests = array_filter(explode(',', $interests));
        }

        $service = app(\App\Services\RecommendationService::class);
        $articles = $service->recommend(
            interests: $interests,
            userId: $userId,
            sessionId: $sessionId,
            limit: $limit
        );

        return ArticleResource::collection($articles);
    }

    public function store(StoreArticleRequest $request): JsonResponse|ArticleResource
    {
        $this->authorize('create', Article::class);
        $article = Article::create(array_merge($request->validated(), [
            'quality_score' => $request->input('quality_score', 0),
        ]));

        return (new ArticleResource($article))->response()->setStatusCode(201);
    }

    public function show(Article $article): ArticleResource
    {
        $article->load(['category', 'articleSources']);

        return new ArticleResource($article);
    }

    public function update(UpdateArticleRequest $request, Article $article): ArticleResource
    {
        $this->authorize('update', $article);
        $article->update($request->validated());

        if ($article->status === 'published') {
            Cache::forget('vivat.home');
            if ($article->category) {
                Cache::forget('vivat.hub.' . $article->category->slug);
            }
            Cache::forget('vivat.categories.index');
        }

        return new ArticleResource($article->fresh(['category', 'articleSources']));
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);
        $categorySlug = $article->category?->slug;
        $article->delete();

        // Invalider le cache pour que le site public et l'API reflètent la suppression tout de suite
        Cache::forget('vivat.home');
        Cache::forget('vivat.categories.index');
        if ($categorySlug) {
            Cache::forget('vivat.hub.' . $categorySlug);
        }

        return response()->json(null, 204);
    }

    /**
     * Génération synchrone d'un article à partir d'items enrichis.
     */
    public function generate(GenerateArticleRequest $request): JsonResponse|ArticleResource
    {
        $validated = $request->validated();

        $items = RssItem::with('enrichedItem')->whereIn('id', $validated['item_ids'])->get();
        foreach ($items as $item) {
            if (! $item->enrichedItem) {
                return response()->json(['message' => "L'item {$item->id} n'est pas enrichi."], 422);
            }
        }

        try {
            $generator = app(ArticleGeneratorService::class);
            $article = $generator->generate(
                itemIds: $validated['item_ids'],
                categoryId: $validated['category_id'] ?? null,
                customPrompt: $validated['custom_prompt'] ?? null,
                articleType: $validated['article_type'] ?? null,
                minWords: isset($validated['suggested_min_words']) ? (int) $validated['suggested_min_words'] : null,
                maxWords: isset($validated['suggested_max_words']) ? (int) $validated['suggested_max_words'] : null,
                contextPriority: $validated['context_priority'] ?? null
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => 'Erreur lors de la génération de l\'article.',
                'error' => $e->getMessage(),
            ], 502);
        }

        return (new ArticleResource($article->load(['category', 'articleSources'])))->response()->setStatusCode(201);
    }

    /**
     * Génération asynchrone : dispatch du job, retour immédiat.
     */
    public function generateAsync(GenerateArticleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        GenerateArticleJob::dispatch(
            $validated['item_ids'],
            $validated['category_id'] ?? null,
            $validated['custom_prompt'] ?? null,
            $validated['article_type'] ?? null,
            isset($validated['suggested_min_words']) ? (int) $validated['suggested_min_words'] : null,
            isset($validated['suggested_max_words']) ? (int) $validated['suggested_max_words'] : null,
            $validated['context_priority'] ?? null
        );

        return response()->json([
            'message' => 'Génération d\'article en cours (queue generation).',
            'item_ids' => $validated['item_ids'],
        ], 202);
    }

    /**
     * Publier un article (status published, published_at = now).
     */
    public function publish(Article $article): JsonResponse|ArticleResource
    {
        $this->authorize('publish', $article);
        if (! $article->isPublishable()) {
            return response()->json([
                'message' => 'Article non publiable (quality_score >= 60 et status draft ou review).',
            ], 422);
        }

        $article->publish();
        $article->load('category');
        if ($article->category) {
            Cache::forget('vivat.hub.'.$article->category->slug);
            Cache::forget('vivat.categories.index');
        }
        Cache::forget('vivat.home');

        return new ArticleResource($article->fresh(['category', 'articleSources']));
    }
}
