<?php

namespace Tests\Unit;

use App\Services\ArticleContentProcessor;
use App\Services\GeneratedArticlePayloadValidator;
use Tests\TestCase;

class GeneratedArticlePayloadValidatorTest extends TestCase
{
    public function test_it_rejects_a_payload_with_empty_title_and_too_short_content(): void
    {
        $validator = new GeneratedArticlePayloadValidator(new ArticleContentProcessor);

        $this->expectExceptionMessage('Payload article IA invalide');

        $validator->validateAndNormalize([
            'title' => '',
            'content' => '<p>Trop court.</p>',
            'keywords' => ['eco'],
        ]);
    }

    public function test_it_sanitizes_html_and_normalizes_keywords(): void
    {
        $validator = new GeneratedArticlePayloadValidator(new ArticleContentProcessor);

        $payload = $validator->validateAndNormalize([
            'title' => 'Sobriété énergétique : 5 gestes simples pour la maison',
            'content' => '<script>alert(1)</script><p onclick="evil()">Un contenu de test suffisamment long pour dépasser le seuil minimal. '.str_repeat('Texte utile ', 30).'</p><a href="javascript:alert(1)" style="color:red">bad</a><a href="https://example.com" target="_blank">good</a>',
            'keywords' => [' énergie ', 'énergie', 'sobriété', 2026],
        ]);

        $this->assertStringNotContainsString('<script', $payload['content']);
        $this->assertStringNotContainsString('onclick=', $payload['content']);
        $this->assertStringNotContainsString('javascript:', $payload['content']);
        $this->assertStringContainsString('https://example.com', $payload['content']);
        $this->assertSame(['énergie', 'sobriété', '2026'], $payload['keywords']);
        $this->assertNotEmpty($payload['excerpt']);
    }
}
