<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\CoverImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchMissingCoverImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(private readonly int $limit = 10) {}

    public function handle(CoverImageService $coverImageService): void
    {
        $articles = Article::published()
            ->where(function ($query) {
                $query->whereNull('cover_image_url')
                    ->orWhere('cover_image_url', '')
                    ->orWhere('cover_image_url', 'like', '%picsum%');
            })
            ->inRandomOrder()
            ->limit($this->limit)
            ->get();

        if ($articles->isEmpty()) {
            return;
        }

        foreach ($articles as $article) {
            try {
                $url = $coverImageService->generate(
                    (string) $article->title,
                    (string) ($article->excerpt ?? ''),
                    $article->category_id,
                );

                if ($url !== null && $url !== '') {
                    $article->update(['cover_image_url' => $url]);
                }
            } catch (\Throwable $e) {
                Log::warning('FetchMissingCoverImagesJob: failed for article', [
                    'article_id' => $article->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Pause entre chaque article pour respecter les rate limits Pexels/OpenAI
            sleep(2);
        }
    }
}
