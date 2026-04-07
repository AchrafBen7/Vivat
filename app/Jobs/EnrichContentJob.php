<?php

namespace App\Jobs;

use App\Models\EnrichedItem;
use App\Models\PipelineJob;
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

    private const MIN_TEXT_LENGTH = 200;
    private const OPENAI_TEXT_LIMIT = 3500;

    public function __construct(
        public RssItem $item
    ) {
        $this->onQueue('enrichment');
    }

    public function middleware(): array
    {
        return [new RateLimited('openai')];
    }

    public function handle(ContentExtractorService $extractor): void
    {
        if (! $this->claimItemForEnrichment()) {
            Log::info("EnrichContentJob skipped duplicate for item {$this->item->id}");
            return;
        }

        $this->item->refresh();

        $pipelineJob = PipelineJob::create([
            'job_type' => 'enrich',
            'status' => 'running',
            'started_at' => now(),
            'metadata' => [
                'item_id' => $this->item->id,
                'rss_feed_id' => $this->item->rss_feed_id,
                'title' => $this->item->title,
                'url' => $this->item->url,
                'attempt' => $this->attempts(),
            ],
            'retry_count' => max(0, $this->attempts() - 1),
        ]);

        $data = $extractor->extract($this->item->url);
        if ($data === null) {
            $this->item->update(['status' => 'failed']);
            $pipelineJob->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => 'Extraction impossible ou contenu inaccessible.',
            ]);
            Log::warning("EnrichContentJob: extraction failed for item {$this->item->id}");
            return;
        }

        $text = $data['text'] ?? '';
        if (mb_strlen($text) < self::MIN_TEXT_LENGTH) {
            $this->item->update(['status' => 'failed']);
            $pipelineJob->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => 'Texte extrait trop court pour enrichissement IA.',
                'metadata' => array_merge($pipelineJob->metadata ?? [], [
                    'word_count' => str_word_count($text),
                ]),
            ]);
            Log::warning("EnrichContentJob: text too short ({$this->item->id})");
            return;
        }

        $enrichment = $this->callOpenAI($data);
        if (($enrichment['__retry'] ?? false) === true) {
            $pipelineJob->update([
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => array_merge($pipelineJob->metadata ?? [], [
                    'retry_scheduled' => true,
                    'retry_delay_seconds' => (int) ($enrichment['__retry_delay'] ?? 60),
                    'outcome' => 'retry_scheduled',
                ]),
            ]);
            return;
        }

        if ($enrichment === null) {
            $this->item->update(['status' => 'failed']);
            $pipelineJob->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => 'Réponse IA invalide ou enrichissement impossible.',
                'metadata' => array_merge($pipelineJob->metadata ?? [], [
                    'word_count' => str_word_count($text),
                ]),
            ]);
            Log::warning("EnrichContentJob: enrichment failed for item {$this->item->id}");
            return;
        }

        EnrichedItem::updateOrCreate(
            ['rss_item_id' => $this->item->id],
            [
                'lead' => $enrichment['lead'] ?? null,
                'headings' => $enrichment['headings'] ?? [],
                'key_points' => $enrichment['key_points'] ?? [],
                'seo_keywords' => $enrichment['seo_keywords'] ?? [],
                'primary_topic' => $enrichment['primary_topic'] ?? null,
                'extracted_text' => $data['text'],
                'extraction_method' => 'readability',
                'quality_score' => (int) ($enrichment['quality_score'] ?? 0),
                'seo_score' => (int) ($enrichment['seo_score'] ?? 0),
                'enriched_at' => now(),
            ]
        );

        $this->item->update(['status' => 'enriched']);
        $pipelineJob->update([
            'status' => 'completed',
            'completed_at' => now(),
            'metadata' => array_merge($pipelineJob->metadata ?? [], [
                'primary_topic' => $enrichment['primary_topic'] ?? null,
                'quality_score' => (int) ($enrichment['quality_score'] ?? 0),
                'seo_score' => (int) ($enrichment['seo_score'] ?? 0),
                'word_count' => str_word_count($text),
                'outcome' => 'enriched',
            ]),
        ]);
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

        $text = mb_substr($extractedData['text'] ?? '', 0, self::OPENAI_TEXT_LIMIT);
        $headings = implode(', ', array_slice($extractedData['headings'] ?? [], 0, 10));
        $userContent = "Titre: " . ($extractedData['title'] ?? '') . "\nTitres de sections: " . $headings . "\n\nContenu:\n" . $text;
        $userContent .= "\n\nAnalyse ce contenu et génère un JSON avec :\n"
            . "- lead: résumé 1-2 phrases\n"
            . "- headings: tableau des titres H2/H3\n"
            . "- key_points: tableau de 3-7 points clés\n"
            . "- seo_keywords: tableau de 5-10 mots-clés SEO pertinents (termes spécifiques, pas génériques, longue traîne si possible)\n"
            . "- primary_topic: le sujet principal en 2-4 mots (ex: 'transition énergétique', 'biodiversité marine')\n"
            . "- quality_score: 0-100 (qualité rédactionnelle et informative)\n"
            . "- seo_score: 0-100 (potentiel SEO estimé : originalité du sujet, spécificité des mots-clés, intérêt de recherche)";

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o'),
                    'messages' => [
                        ['role' => 'system', 'content' => "Tu es un analyste de contenu SEO expert. Tu analyses des articles et produis une analyse structurée avec des mots-clés SEO ciblés (longue traîne, spécifiques, faible concurrence). Privilégie les termes recherchés par les utilisateurs mais peu concurrentiels. Réponds uniquement en JSON."],
                        ['role' => 'user', 'content' => $userContent],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.3,
                    'max_tokens' => 2000,
                ]);
        } catch (Throwable $e) {
            Log::error("EnrichContentJob OpenAI request failed: {$e->getMessage()}");
            $this->release(60);
            return ['__retry' => true, '__retry_delay' => 60];
        }

        if ($response->status() === 429) {
            $this->release(60);
            return ['__retry' => true, '__retry_delay' => 60];
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

    private function claimItemForEnrichment(): bool
    {
        if ($this->attempts() > 1) {
            $status = $this->item->fresh()?->status;

            if ($status === 'enriching') {
                return true;
            }

            if (in_array($status, ['new', 'failed'], true)) {
                return RssItem::query()
                    ->whereKey($this->item->id)
                    ->where('status', $status)
                    ->update(['status' => 'enriching']) === 1;
            }

            return false;
        }

        return RssItem::query()
            ->whereKey($this->item->id)
            ->whereIn('status', ['new', 'failed'])
            ->update(['status' => 'enriching']) === 1;
    }

    public function failed(Throwable $e): void
    {
        Log::error("EnrichContentJob failed for item {$this->item->id}: {$e->getMessage()}");
        $this->item->update(['status' => 'failed']);
    }
}
