<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_suggestions_return_at_most_four_article_results(): void
    {
        $category = Category::query()->create([
            'name' => 'Energie',
            'slug' => 'energie',
            'description' => 'Energie du quotidien',
        ]);

        for ($index = 1; $index <= 5; $index++) {
            Article::query()->create([
                'title' => 'Design durable '.$index,
                'slug' => 'design-durable-'.$index,
                'excerpt' => 'Un article sur le design durable '.$index,
                'content' => 'Contenu de test pour la recherche.',
                'category_id' => $category->id,
                'language' => 'fr',
                'reading_time' => 5,
                'status' => 'published',
                'article_type' => 'standard',
                'published_at' => now()->subDays($index),
                'quality_score' => 80,
            ]);
        }

        $response = $this->getJson('/search/suggestions?q=design');

        $response->assertOk();

        $suggestions = $response->json('suggestions');

        $this->assertCount(4, $suggestions);
        $this->assertContainsOnly('array', $suggestions);
        $this->assertSame(
            ['article', 'article', 'article', 'article'],
            array_column($suggestions, 'type')
        );
    }

    public function test_search_suggestions_return_no_result_for_too_short_queries(): void
    {
        $response = $this->getJson('/search/suggestions?q=a');

        $response->assertOk();
        $response->assertJson([
            'query' => 'a',
            'suggestions' => [],
        ]);
    }
}
