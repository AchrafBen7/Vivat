<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_stats_index_returns_200_and_structure(): void
    {
        $response = $this->getJson('/api/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'sources',
                'rss_feeds_active',
                'rss_items_by_status',
                'articles_by_status',
                'articles_published',
            ]);
    }
}
