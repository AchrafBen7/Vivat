<?php

namespace App\Jobs;

use App\Models\RssFeed;
use App\Models\RssItem;
use App\Services\RssParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchRssFeedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 60, 120];

    public int $timeout = 60;

    public string $queue = 'rss';

    public function __construct(
        public RssFeed $feed
    ) {}

    public function handle(RssParserService $parser): void
    {
        Log::info("Fetching RSS feed: {$this->feed->feed_url}");

        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; ContentBot/1.0)',
                'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
            ])
            ->get($this->feed->feed_url);

        if ($response->failed()) {
            throw new \Exception("HTTP {$response->status()} for {$this->feed->feed_url}");
        }

        $items = $parser->parse($response->body());
        $newCount = 0;

        foreach ($items as $item) {
            $hash = $parser->generateDedupHash(
                $item['guid'] ?? null,
                $item['link'],
                $item['title']
            );
            if (RssItem::where('dedup_hash', $hash)->exists()) {
                continue;
            }
            RssItem::create([
                'rss_feed_id' => $this->feed->id,
                'category_id' => $this->feed->category_id,
                'title' => $item['title'],
                'url' => $item['link'],
                'description' => mb_substr($item['description'] ?? '', 0, 1000),
                'guid' => $item['guid'] ?? null,
                'dedup_hash' => $hash,
                'published_at' => isset($item['pubDate']) && $item['pubDate'] ? now()->parse($item['pubDate']) : null,
                'status' => 'new',
            ]);
            $newCount++;
        }

        $this->feed->update(['last_fetched_at' => now()]);
        Log::info("Fetched {$newCount} new items from {$this->feed->feed_url}");
    }

    public function failed(Throwable $e): void
    {
        Log::error("FetchRssFeedJob failed for feed {$this->feed->id}: {$e->getMessage()}");
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(1);
    }
}
