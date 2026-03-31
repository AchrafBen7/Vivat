<?php

namespace Tests\Feature;

use Tests\TestCase;

class AboutPageTest extends TestCase
{
    public function test_about_page_returns_successful_response(): void
    {
        $response = $this->get('/a-propos');

        $response->assertStatus(200);
        $response->assertSee('À propos', false);
        $response->assertSee('Vivat', false);
    }
}
