<?php

namespace Tests\Unit;

use App\Models\Article;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    #[Test]
    public function is_publishable_returns_true_when_score_ge_60_and_status_draft_or_review(): void
    {
        $article = new Article;
        $article->quality_score = 60;
        $article->status = 'draft';
        $this->assertTrue($article->isPublishable());

        $article->status = 'review';
        $this->assertTrue($article->isPublishable());

        $article->quality_score = 59;
        $this->assertFalse($article->isPublishable());

        $article->quality_score = 70;
        $article->status = 'published';
        $this->assertFalse($article->isPublishable());
    }

    #[Test]
    public function is_publishable_returns_false_for_archived_or_rejected(): void
    {
        $article = new Article;
        $article->quality_score = 80;
        $article->status = 'archived';
        $this->assertFalse($article->isPublishable());

        $article->status = 'rejected';
        $this->assertFalse($article->isPublishable());
    }
}
