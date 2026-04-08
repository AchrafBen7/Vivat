<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\EnrichedItem;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Models\Source;
use App\Services\TopicScorerService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TopicScorerServiceTest extends TestCase
{
    #[Test]
    public function it_scores_recent_high_quality_items_higher_than_old_low_quality_items(): void
    {
        Carbon::setTestNow('2026-04-08 12:00:00');
        $service = new TopicScorerService(
            weightsOverride: [
                'freshness' => 50,
                'quality' => 50,
                'seo' => 0,
                'diversity' => 0,
                'topic_frequency' => 0,
            ],
            freshnessDecayDaysOverride: 7,
        );

        $recentItem = $this->makeRssItem(
            title: 'AA',
            publishedAt: now()->subDay(),
            categorySlug: 'divers',
            qualityScore: 88,
            extractedTextWords: 1100,
            headings: [],
            keyPoints: [],
        );

        $oldItem = $this->makeRssItem(
            title: 'BB',
            publishedAt: now()->subDays(21),
            categorySlug: 'divers',
            qualityScore: 10,
            extractedTextWords: 120,
            headings: [],
            keyPoints: [],
        );

        $recentScore = $service->scoreItem($recentItem);
        $oldScore = $service->scoreItem($oldItem);

        $this->assertTrue(
            $recentScore > $oldScore,
            sprintf('Expected recent score (%d) to be greater than old score (%d).', $recentScore, $oldScore)
        );
    }

    #[Test]
    public function it_clusters_items_that_share_a_topic(): void
    {
        $service = new TopicScorerService;

        $clusters = $service->clusterByTopic(collect([
            [
                'item' => $this->makeRssItem(title: 'Pompe à chaleur et isolation', publishedAt: now()),
                'score' => 70,
                'keywords' => [
                    ['word' => 'énergie', 'frequency' => 5, 'seo_weight' => 80],
                    ['word' => 'isolation', 'frequency' => 4, 'seo_weight' => 76],
                    ['word' => 'chauffage', 'frequency' => 3, 'seo_weight' => 71],
                ],
            ],
            [
                'item' => $this->makeRssItem(title: 'Isolation et chauffage du logement', publishedAt: now()),
                'score' => 69,
                'keywords' => [
                    ['word' => 'isolation', 'frequency' => 4, 'seo_weight' => 76],
                    ['word' => 'chauffage', 'frequency' => 4, 'seo_weight' => 71],
                    ['word' => 'maison', 'frequency' => 2, 'seo_weight' => 63],
                ],
            ],
            [
                'item' => $this->makeRssItem(title: 'Budget retraite et épargne', publishedAt: now()),
                'score' => 55,
                'keywords' => [
                    ['word' => 'budget', 'frequency' => 3, 'seo_weight' => 66],
                    ['word' => 'épargne', 'frequency' => 3, 'seo_weight' => 68],
                    ['word' => 'finance', 'frequency' => 2, 'seo_weight' => 64],
                ],
            ],
        ]));

        $clusterSizes = collect($clusters)->map(fn (Collection $group): int => $group->count())->sort()->values()->all();

        $this->assertSame([1, 2], $clusterSizes);
    }

    #[Test]
    public function it_infers_category_from_keywords_with_in_memory_resolver(): void
    {
        $categories = collect([
            new Category(['id' => 'cat-1', 'name' => 'Énergie', 'slug' => 'energie']),
            new Category(['id' => 'cat-2', 'name' => 'Technologie', 'slug' => 'technologie']),
        ]);

        $service = new TopicScorerService(
            categoryResolver: fn (string $slug): ?Category => $categories->firstWhere('slug', $slug)
        );

        $category = $service->inferCategoryFromKeywords([
            ['word' => 'photovoltaïque', 'frequency' => 4, 'seo_weight' => 84],
            ['word' => 'énergie', 'frequency' => 6, 'seo_weight' => 88],
            ['word' => 'solaire', 'frequency' => 3, 'seo_weight' => 74],
        ]);

        $this->assertNotNull($category);
        $this->assertSame('energie', $category?->slug);
    }

    #[Test]
    public function it_filters_corrupted_keywords_from_extraction(): void
    {
        $service = new TopicScorerService;

        $item = $this->makeRssItem(
            title: 'Location d’objets low-tech durable',
            publishedAt: now(),
            headings: ['Objets low-tech', 'Location responsable'],
            keyPoints: [
                'lowbjethèque',
                'location durable',
                ['text' => 'économie circulaire'],
            ],
            extractedTextWords: 500,
        );

        $keywords = collect($service->extractKeywords($item))->pluck('word')->all();

        $this->assertContains('location', $keywords);
        $this->assertContains('durable', $keywords);
        $this->assertNotContains('lowbjethèque', $keywords);
    }

    private function makeRssItem(
        string $title,
        ?Carbon $publishedAt,
        string $categorySlug = 'energie',
        int $qualityScore = 70,
        int $extractedTextWords = 600,
        array $headings = [],
        array $keyPoints = [],
    ): RssItem {
        $category = new Category([
            'id' => 'cat-' . $categorySlug,
            'name' => ucfirst($categorySlug),
            'slug' => $categorySlug,
        ]);

        $source = new Source([
            'id' => 'source-' . md5($title),
            'name' => 'Source ' . $title,
            'base_url' => 'https://example.test',
            'language' => 'fr',
            'is_active' => true,
        ]);

        $feed = new RssFeed([
            'id' => 'feed-' . md5($title),
            'source_id' => $source->id,
            'category_id' => $category->id,
            'feed_url' => 'https://example.test/feed.xml',
            'is_active' => true,
        ]);
        $feed->setRelation('source', $source);
        $feed->setRelation('category', $category);

        $item = new RssItem([
            'id' => 'item-' . md5($title),
            'rss_feed_id' => $feed->id,
            'category_id' => $category->id,
            'guid' => md5($title),
            'title' => $title,
            'url' => 'https://example.test/articles/' . md5($title),
            'published_at' => $publishedAt,
            'fetched_at' => $publishedAt ?? now(),
            'status' => 'enriched',
            'created_at' => $publishedAt ?? now(),
        ]);

        $enriched = new EnrichedItem([
            'id' => 'enriched-' . md5($title),
            'rss_item_id' => $item->id,
            'lead' => $title . ' en détail',
            'headings' => $headings,
            'key_points' => $keyPoints,
            'quality_score' => $qualityScore,
            'seo_score' => 70,
            'extracted_text' => implode(' ', array_fill(0, $extractedTextWords, 'mot')),
            'enriched_at' => now(),
        ]);

        $item->setRelation('category', $category);
        $item->setRelation('rssFeed', $feed);
        $item->setRelation('enrichedItem', $enriched);

        return $item;
    }
}
