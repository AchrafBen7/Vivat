<?php

namespace App\Jobs;

use App\Services\ArticleGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public array $backoff = [120, 300];

    public int $timeout = 180;

    /**
     * @param  array<int, string>  $itemIds  UUID des RssItem enrichis
     */
    public function __construct(
        public array $itemIds,
        public ?string $categoryId = null,
        public ?string $customPrompt = null,
        public ?string $articleType = null,
        public ?int $minWords = null,
        public ?int $maxWords = null,
        public ?string $contextPriority = null
    ) {
        $this->onQueue('generation');
    }

    public function handle(ArticleGeneratorService $generator): void
    {
        $lock = Cache::lock($this->lockKey(), 300);

        if (! $lock->get()) {
            Log::info('GenerateArticleJob skipped duplicate lock', ['item_ids' => $this->itemIds]);

            return;
        }

        try {
            Log::info('GenerateArticleJob started', ['item_ids' => $this->itemIds]);

            $article = $generator->generate(
                itemIds: $this->itemIds,
                categoryId: $this->categoryId,
                customPrompt: $this->customPrompt,
                articleType: $this->articleType,
                minWords: $this->minWords,
                maxWords: $this->maxWords,
                contextPriority: $this->contextPriority
            );

            Log::info("GenerateArticleJob completed: article {$article->id}");
        } finally {
            $lock->release();
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('GenerateArticleJob failed: ' . $e->getMessage(), [
            'item_ids' => $this->itemIds,
        ]);
    }

    private function lockKey(): string
    {
        $sortedItemIds = $this->itemIds;
        sort($sortedItemIds);

        return 'pipeline:generation:' . sha1(json_encode($sortedItemIds));
    }
}
