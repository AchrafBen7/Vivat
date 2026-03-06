<?php

namespace Tests\Unit;

use App\Services\RssParserService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RssParserServiceTest extends TestCase
{
    private RssParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new RssParserService;
    }

    #[Test]
    public function it_returns_empty_array_for_empty_xml(): void
    {
        $this->assertSame([], $this->parser->parse(''));
        $this->assertSame([], $this->parser->parse('   '));
    }

    #[Test]
    public function it_parses_rss20_feed(): void
    {
        $xml = <<<'XML'
<?xml version="1.0"?>
<rss version="2.0"><channel>
<title>Test</title>
<item><title>Item 1</title><link>https://example.com/1</link><description>Desc 1</description></item>
<item><title>Item 2</title><link>https://example.com/2</link><pubDate>Mon, 01 Jan 2024 12:00:00 GMT</pubDate><guid>guid-2</guid></item>
</channel></rss>
XML;
        $items = $this->parser->parse($xml);
        $this->assertCount(2, $items);
        $this->assertSame('Item 1', $items[0]['title']);
        $this->assertSame('https://example.com/1', $items[0]['link']);
        $this->assertSame('Desc 1', $items[0]['description']);
        $this->assertSame('Item 2', $items[1]['title']);
        $this->assertSame('guid-2', $items[1]['guid']);
    }

    #[Test]
    public function it_generates_dedup_hash(): void
    {
        $hash = $this->parser->generateDedupHash('my-guid', 'https://example.com', 'Title');
        $this->assertSame(32, strlen($hash));
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $hash);

        $hash2 = $this->parser->generateDedupHash(null, 'https://example.com', 'Title');
        $this->assertSame(32, strlen($hash2));
    }
}
