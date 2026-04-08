<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_endpoint_returns_json_snapshot(): void
    {
        $response = $this->getJson('/health');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'checked_at',
            'pipeline' => [
                'latest_pipeline_activity_at',
                'latest_enriched_at',
                'new_items_count',
                'enrichment_stale',
                'failed_jobs_count',
            ],
            'horizon' => [
                'latest_snapshot_at',
                'snapshot_fresh',
                'stale_minutes_threshold',
            ],
            'queues' => [
                'rss',
                'enrichment',
                'default',
            ],
            'issues',
        ]);
    }
}
