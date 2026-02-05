<?php

namespace App\Jobs;

use App\Models\EnrichedItem;
use App\Models\RssItem;
use App\Services\ContentExtractorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnrichContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 120, 300];

    public int $timeout = 90;

    public string $queue = 'enrichment';

    private const MIN_TEXT_LENGTH = 200;

    public function __construct(
        public RssItem $item
    ) {}

    public function middleware(): array
    {
        return [new RateLimited('openai')];
    }

    public function handle(ContentExtractorService $extractor): void
    {
        $this->item->update(['status' => 'enriching']);

        $data = $extractor->extract($this->item->url);
        if ($data === null) {
            $this->item->update(['status' => 'failed']);
            Log::warning("EnrichContentJob: extraction failed for item {$this->item->id}");
            return;
        }

        $text = $data['text'] ?? '';
        if (mb_strlen($text) < self::MIN_TEXT_LENGTH) {
            $this->item->update(['status' => 'failed']);
            Log::warning("EnrichContentJob: text too short ({$this->item->id})");
            return;
        }

        $enrichment = $this->callOpenAI($data);
        if ($enrichment === null) {
            $this->item->update(['status' => 'new']);
            return;
        }

        EnrichedItem::updateOrCreate(
            ['rss_item_id' => $this->item->id],
            [
                'lead' => $enrichment['lead'] ?? null,
                'headings' => $enrichment['headings'] ?? [],
                'key_points' => $enrichment['key_points'] ?? [],
                'extracted_text' => $data['text'],
                'extraction_method' => 'readability',
                'quality_score' => (int) ($enrichment['quality_score'] ?? 0),
            ]
        );

        $this->item->update(['status' => 'enriched']);
        Log::info("EnrichContentJob: enriched item {$this->item->id}");
    }

    /**
     * @return array{lead?: string, headings?: array, key_points?: array, quality_score?: int}|null
     */
    private function callOpenAI(array $extractedData): ?array
    {
        $apiKey = config('services.openai.api_key');
        if (! $apiKey) {
            Log::warning('EnrichContentJob: OPENAI_API_KEY not set');
            return null;
        }

        $text = mb_substr($extractedData['text'] ?? '', 0, 6000);
        $userContent = "Extrait d'article (titre: " . ($extractedData['title'] ?? '') . "):\n\n" . $text;
        $userContent .= "\n\nGénère un JSON avec : lead (résumé 1-2 phrases), headings (tableau des titres H2/H3), key_points (tableau de 3-7 points clés), quality_score (0-100).";

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu analyses du contenu et produis un JSON avec lead, headings, key_points, quality_score. Réponds uniquement en JSON.'],
                        ['role' => 'user', 'content' => $userContent],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.3,
                    'max_tokens' => 1500,
                ]);
        } catch (Throwable $e) {
            Log::error("EnrichContentJob OpenAI request failed: {$e->getMessage()}");
            $this->release(60);
            return null;
        }

        if ($response->status() === 429) {
            $this->release(60);
            return null;
        }

        if ($response->status() === 402) {
            throw new \RuntimeException('OpenAI: quota exceeded (402).');
        }

        if ($response->failed()) {
            Log::error('EnrichContentJob OpenAI error: ' . $response->body());
            return null;
        }

        $content = $response->json('choices.0.message.content');
        if (! is_string($content)) {
            return null;
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function failed(Throwable $e): void
    {
        Log::error("EnrichContentJob failed for item {$this->item->id}: {$e->getMessage()}");
        $this->item->update(['status' => 'failed']);
    }
}
