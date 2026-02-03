# Exemples de code — Référence d’implémentation (Vivat)

Ce fichier contient les exemples de code à suivre pour le pipeline (fetch RSS, enrichissement, génération, Horizon, etc.). À utiliser en complément de `CONTEXTE_PROJET.md`.

---

## 1. Fetch RSS — Job + Service

### 1.1 `App\Jobs\FetchRssFeedJob`

- **Queue** : `rss` (voir Horizon).
- **Retry** : 3 tentatives, backoff 30s, 60s, 120s ; timeout 60s.
- **Comportement** : HTTP GET du `feed_url`, parse via `RssParserService`, déduplication par `dedup_hash` (sha256 de link+title, 32 car.), création `RssItem` avec `status = 'new'`, mise à jour `last_fetched_at` sur le feed.
- **Failed** : log + optionnel PipelineJob en échec.
- **retryUntil** : `now()->addHours(1)`.

```php
<?php
namespace App\Jobs;
use App\Models\RssFeed;
use App\Models\RssItem;
use App\Services\RssParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchRssFeedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 60, 120];
    public int $timeout = 60;
    public string $queue = 'rss';

    public function __construct(public RssFeed $feed) {}

    public function handle(RssParserService $parser): void
    {
        Log::info("Fetching RSS feed: {$this->feed->feed_url}");
        $response = Http::timeout(30)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; ContentBot/1.0)',
                'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
            ])
            ->get($this->feed->feed_url);

        if ($response->failed()) {
            throw new \Exception("HTTP {$response->status()} for {$this->feed->feed_url}");
        }

        $items = $parser->parse($response->body());
        $newCount = 0;
        foreach ($items as $item) {
            $hash = hash('sha256', $item['link'] . $item['title']);
            if (RssItem::where('dedup_hash', substr($hash, 0, 32))->exists()) continue;
            RssItem::create([
                'rss_feed_id' => $this->feed->id,
                'category_id' => $this->feed->category_id,
                'title' => $item['title'],
                'url' => $item['link'],
                'description' => substr($item['description'] ?? '', 0, 1000),
                'guid' => $item['guid'],
                'dedup_hash' => substr($hash, 0, 32),
                'published_at' => $item['pubDate'] ? now()->parse($item['pubDate']) : null,
                'status' => 'new',
            ]);
            $newCount++;
        }
        $this->feed->update(['last_fetched_at' => now()]);
        Log::info("Fetched {$newCount} new items from {$this->feed->feed_url}");
    }

    public function failed(Throwable $e): void
    {
        Log::error("FetchRssFeedJob failed for feed {$this->feed->id}: {$e->getMessage()}");
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(1);
    }
}
```

### 1.2 `App\Services\RssParserService`

- **Méthode** : `parse(string $xml): array` — RSS 2.0 puis fallback Atom.
- **Format retour** : liste d’items avec `title`, `link`, `description`, `pubDate`, `guid`.
- **Helpers** : `extractTag`, `extractAtomLink`, `generateDedupHash(guid|link+title)`.

*(Code complet fourni par l’utilisateur : parseRss / parseAtom / extractItem / extractTag / extractAtomLink / generateDedupHash.)*

---

## 2. Enrichissement — Job + Rate limit

### 2.1 `App\Jobs\EnrichContentJob`

- **Queue** : `enrichment`.
- **Middleware** : `RateLimited('openai')`.
- **Étapes** : 1) `ContentExtractorService::extract($url)` 2) si texte < 200 car. → status `failed` et return 3) `callOpenAI()` pour lead, headings, key_points, quality_score 4) `EnrichedItem::updateOrCreate` 5) `RssItem` status `enriched`.
- **OpenAI** : model `gpt-4o`, `response_format: json_object`, retry avec backoff sur 429, release(60) si 429, exception si 402.

### 2.2 Rate limiter (`AppServiceProvider::boot`)

```php
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

RateLimiter::for('openai', function (object $job) {
    return Limit::perMinute(50)->by($job->item->id);
});
```

### 2.3 Dispatch avec throttling

```php
RssItem::where('status', 'new')
    ->limit(100)
    ->get()
    ->each(function ($item, $index) {
        EnrichContentJob::dispatch($item)
            ->onQueue('enrichment')
            ->delay(now()->addSeconds($index * 2));
    });
```

---

## 3. Extraction de contenu web — `App\Services\ContentExtractorService`

- **Méthode** : `extract(string $url): ?array`.
- **Retour** : `['title', 'headings', 'text', 'html', 'internal_links', 'word_count', 'deep_scraped'?]`.
- **Comportement** : fetch HTTP, clean HTML (removeSelectors), extraction titre (h1 puis title), headings, main content (contentSelectors puis plus gros bloc), deep scrape si contenu insuffisant (< 5 paragraphes ou < 300 mots).
- **Sans dépendance payante** (pas Firecrawl).

*(Code complet fourni par l’utilisateur.)*

---

## 4. Génération d’articles — `App\Services\ArticleGeneratorService`

- **Méthode** : `generate(array $itemIds, ?string $categoryId = null, ?string $customPrompt = null): Article`.
- **Étapes** : charger RssItems avec enrichedItem + rssFeed.source ; template catégorie optionnel ; build system/user prompts ; call OpenAI ; créer Article (slug avec random 6) ; ArticleSource pour chaque item ; RssItem status `used`.
- **OpenAI** : gpt-4o, json_object, temperature 0.7, max_tokens 4000 ; sanitizeContent (em dash, guillemets), calculateReadingTime (200 mots/min), assessQuality (titre, longueur, H2/H3, keywords).
- **Usage dans Job** : `$article = $generator->generate(itemIds: $this->itemIds, categoryId: $this->categoryId, customPrompt: $this->customPrompt);`

*(Code complet fourni par l’utilisateur.)*

---

## 5. Horizon — Configuration

- **Queues** : `rss`, `enrichment`, `generation`, `default`, `notifications`.
- **Supervisors** : supervisor-rss (queue rss), supervisor-enrichment, supervisor-generation (1 process pour rate limit), supervisor-default.
- **Waits** : redis:rss 120, redis:enrichment 180, redis:generation 300.
- **Environnements** : `local` (moins de processes), `production` (plus).
- **Middleware Horizon** : `App\Http\Middleware\AuthorizeHorizon` — en prod, `$request->user()?->is_admin` sinon 403.

### Jobs avec queue

- `FetchRssFeedJob` → `$queue = 'rss'`
- `EnrichContentJob` → `$queue = 'enrichment'`
- `GenerateArticleJob` → `$queue = 'generation'`

### Scheduler (`app/Console/Kernel.php`)

- Toutes les 30 min : dispatch `FetchRssFeedJob` pour chaque RssFeed actif.
- Toutes les heures : dispatch `EnrichContentJob` pour 50 RssItems `status = new` avec delay 3s entre chaque.
- `horizon:snapshot` everyFiveMinutes ; `queue:prune-failed --hours=168` daily ; `horizon:status` everyMinute → log.

### Supervisor / Docker

- Supervisor : `php artisan horizon`, user www-data, stopwaitsecs=3600.
- Docker : service `horizon` (php artisan horizon), service `scheduler` (schedule:run toutes les 60s).

*(Config Horizon complète fournie par l’utilisateur.)*

---

## 6. Génération d’articles — Controller + Request + Policy + Routes

### 6.1 `GenerateArticleRequest`

- **Règles** : `item_ids` required array 1–10, `item_ids.*` uuid exists rss_items ; `category_id` nullable uuid exists categories ; `custom_prompt` nullable string max 1000 ; `tone` in professional|casual|formal|engaging ; `min_words` 300–3000 ; `max_words` 500–5000 gte min_words.
- **prepareForValidation** : strip_tags custom_prompt.
- **validated()** : defaults tone professional, min_words 800, max_words 1200.

### 6.2 `ArticleGenerationController`

- **index** : GET page génération, items enrichis/new paginés 50.
- **generate** : POST synchrone, vérif items enrichis, puis `ArticleGeneratorService::generate()`, retour JSON article + redirect edit.
- **generateAsync** : POST dispatch `GenerateArticleJob` onQueue('generation') avec userId.
- **preview** : POST retourne sources + estimated_quality + estimated_words.
- **edit** : GET article avec category, sources.
- **update** : PUT validation title, excerpt, content, meta_title, meta_description, status ; sanitize content ; retour JSON.
- **publish** : POST si quality_score >= 50 alors status published, published_at.

### 6.3 `ArticlePolicy`

- **update** : is_admin ou created_by.
- **publish** : is_admin et quality_score >= 50.
- **delete** : is_admin.

### 6.4 Routes (`routes/web.php`)

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/generate', [ArticleGenerationController::class, 'index'])->name('generate.index');
    Route::post('/generate', [ArticleGenerationController::class, 'generate'])->name('generate.store');
    Route::post('/generate/async', [ArticleGenerationController::class, 'generateAsync'])->name('generate.async');
    Route::post('/generate/preview', [ArticleGenerationController::class, 'preview'])->name('generate.preview');
    Route::get('/articles/{article}/edit', [ArticleGenerationController::class, 'edit'])->name('articles.edit');
    Route::put('/articles/{article}', [ArticleGenerationController::class, 'update'])->name('articles.update');
    Route::post('/articles/{article}/publish', [ArticleGenerationController::class, 'publish'])->name('articles.publish');
});
```

---

## 7. Note sur les noms de services

- **ContentExtractorService** : extraction du contenu depuis une URL (fetch + parse HTML). Utilisé par `EnrichContentJob`.
- **ContentEnrichmentService** (liste section 9) : peut désigner le même rôle ou un service qui orchestre extraction + appel IA ; dans les exemples, l’enrichissement IA est dans le Job (`callOpenAI`). À aligner au choix : tout dans le Job ou déléguer à un service dédié.

---

*Référence : CONTEXTE_PROJET.md section 9 + section 10.*
