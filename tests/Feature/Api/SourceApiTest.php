<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sources_index_returns_200_and_json(): void
    {
        $response = $this->getJson('/api/sources');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_sources_store_creates_source_and_returns_201(): void
    {
        $response = $this->postJson('/api/sources', [
            'name' => 'Le Monde',
            'base_url' => 'https://lemonde.fr',
            'language' => 'fr',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Le Monde')
            ->assertJsonPath('data.base_url', 'https://lemonde.fr')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('sources', [
            'name' => 'Le Monde',
            'base_url' => 'https://lemonde.fr',
        ]);
    }

    public function test_sources_store_validates_required_fields(): void
    {
        $response = $this->postJson('/api/sources', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'base_url']);
    }

    public function test_sources_show_returns_200(): void
    {
        $source = \App\Models\Source::create([
            'name' => 'Test',
            'base_url' => 'https://test.com',
            'language' => 'fr',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/sources/'.$source->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $source->id)
            ->assertJsonPath('data.name', 'Test');
    }
}
