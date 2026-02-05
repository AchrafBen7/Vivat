<?php

namespace App\Jobs;

use App\Services\ArticleGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public array $backoff = [120, 300];

    public int $timeout = 180;

    public string $queue = 'generation';

    /**
     * @param  array<int, string>  $itemIds  UUID des RssItem enrichis
     */
    public function __construct(
        public array $itemIds,
        public ?string $categoryId = null,
        public ?string $customPrompt = null
    ) {}

    public function handle(ArticleGeneratorService $generator): void
    {
        Log::info('GenerateArticleJob started', ['item_ids' => $this->itemIds]);

        $article = $generator->generate(
            itemIds: $this->itemIds,
            categoryId: $this->categoryId,
            customPrompt: $this->customPrompt
        );

        Log::info("GenerateArticleJob completed: article {$article->id}");
    }

    public function failed(Throwable $e): void
    {
        Log::error('GenerateArticleJob failed: ' . $e->getMessage(), [
            'item_ids' => $this->itemIds,
        ]);
    }
}
