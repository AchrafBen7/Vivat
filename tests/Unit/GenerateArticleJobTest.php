<?php

namespace Tests\Unit;

use App\Jobs\GenerateArticleJob;
use Illuminate\Queue\Middleware\RateLimited;
use Tests\TestCase;

class GenerateArticleJobTest extends TestCase
{
    public function test_it_uses_the_openai_rate_limiter(): void
    {
        $job = new GenerateArticleJob(itemIds: ['item-1']);
        $middlewares = $job->middleware();

        $this->assertCount(1, $middlewares);
        $this->assertInstanceOf(RateLimited::class, $middlewares[0]);
    }
}
