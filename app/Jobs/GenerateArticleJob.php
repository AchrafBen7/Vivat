<?php

namespace App\Jobs;

use App\Models\Cluster;
use App\Models\PipelineJob;
use App\Services\ArticleGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
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
        public ?string $contextPriority = null,
        public ?string $clusterId = null
    ) {
        $this->onQueue('generation');
    }

    public function middleware(): array
    {
        return [new RateLimited('openai')];
    }

    public function handle(ArticleGeneratorService $generator): void
    {
        $lock = Cache::lock($this->lockKey(), 300);

        if (! $lock->get()) {
            Log::info('GenerateArticleJob skipped duplicate lock', ['item_ids' => $this->itemIds]);

            return;
        }

        $pipelineJob = PipelineJob::create([
            'job_type' => 'generate',
            'status' => 'running',
            'started_at' => now(),
            'metadata' => [
                'item_ids' => $this->itemIds,
                'category_id' => $this->categoryId,
                'cluster_id' => $this->clusterId,
                'article_type' => $this->articleType,
                'min_words' => $this->minWords,
                'max_words' => $this->maxWords,
                'context_priority' => $this->contextPriority,
            ],
            'retry_count' => max(0, $this->attempts() - 1),
        ]);

        try {
            Log::info('GenerateArticleJob started', ['item_ids' => $this->itemIds]);

            if ($this->clusterId) {
                Cluster::query()
                    ->whereKey($this->clusterId)
                    ->update(['status' => 'processing']);
            }

            $article = $generator->generate(
                itemIds: $this->itemIds,
                categoryId: $this->categoryId,
                customPrompt: $this->customPrompt,
                articleType: $this->articleType,
                minWords: $this->minWords,
                maxWords: $this->maxWords,
                contextPriority: $this->contextPriority,
                clusterId: $this->clusterId,
            );

            if ($this->clusterId) {
                Cluster::query()
                    ->whereKey($this->clusterId)
                    ->update(['status' => 'generated']);
            }

            $pipelineJob->update([
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => array_merge($pipelineJob->metadata ?? [], [
                    'article_id' => $article->id,
                    'article_slug' => $article->slug,
                    'article_status' => $article->status,
                ]),
            ]);

            Log::info("GenerateArticleJob completed: article {$article->id}");
        } catch (Throwable $e) {
            if ($this->clusterId) {
                Cluster::query()
                    ->whereKey($this->clusterId)
                    ->update(['status' => 'failed']);
            }

            $pipelineJob->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
                'retry_count' => max(0, $this->attempts() - 1),
            ]);

            throw $e;
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
