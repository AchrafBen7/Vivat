<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateArticleRequest;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Jobs\GenerateArticleJob;
use App\Models\Article;
use App\Models\RssItem;
use App\Services\ArticleGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
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

    public function store(StoreArticleRequest $request): JsonResponse|ArticleResource
    {
        $this->authorize('create', Article::class);
        $article = Article::create(array_merge($request->validated(), ['quality_score' => 0]));

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

        return new ArticleResource($article->fresh(['category', 'articleSources']));
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);
        $article->delete();

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

        $generator = app(ArticleGeneratorService::class);
        $article = $generator->generate(
            itemIds: $validated['item_ids'],
            categoryId: $validated['category_id'] ?? null,
            customPrompt: $validated['custom_prompt'] ?? null
        );

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
            $validated['custom_prompt'] ?? null
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

        return new ArticleResource($article->fresh(['category', 'articleSources']));
    }
}
