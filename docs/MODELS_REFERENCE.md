# Models Eloquent — Pipeline génération d’articles (Vivat)

Référence des 11 models pour la fonctionnalité de génération d’articles. **HasUuids** sur tous les models. Tables sans `updated_at` : `$timestamps = false` et `const CREATED_AT = 'created_at'` quand la migration a un `created_at`.

---

## Schéma des relations (résumé)

- **Source** → hasMany RssFeed, ArticleSource  
- **Category** → hasMany RssFeed, RssItem, Cluster, Article + hasOne CategoryTemplate  
- **RssFeed** → belongsTo Source, Category + hasMany RssItem (alias `items`)  
- **RssItem** → hasOne EnrichedItem + belongsToMany Cluster (via cluster_items)  
- **Cluster** → belongsToMany RssItem + hasOne Article  
- **Article** → belongsToMany Source via pivot ArticleSource (withPivot: rss_item_id, url, used_at)

---

## 1. Source

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'base_url',
        'language',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rssFeeds(): HasMany
    {
        return $this->hasMany(RssFeed::class);
    }

    public function articleSources(): HasMany
    {
        return $this->hasMany(ArticleSource::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

---

## 2. Category

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    use HasUuids;

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];

    public function rssFeeds(): HasMany
    {
        return $this->hasMany(RssFeed::class);
    }

    public function rssItems(): HasMany
    {
        return $this->hasMany(RssItem::class);
    }

    public function clusters(): HasMany
    {
        return $this->hasMany(Cluster::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function template(): HasOne
    {
        return $this->hasOne(CategoryTemplate::class);
    }
}
```

---

## 3. RssFeed

*Stack MySQL 8 : `scopeDueForFetch` utilise `DATE_SUB`. Pour PostgreSQL, utiliser `NOW() - (fetch_interval_minutes || ' minutes')::interval`.*

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RssFeed extends Model
{
    use HasUuids;

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'source_id',
        'category_id',
        'feed_url',
        'is_active',
        'last_fetched_at',
        'fetch_interval_minutes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_fetched_at' => 'datetime',
        'fetch_interval_minutes' => 'integer',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RssItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDueForFetch($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_fetched_at')
                  ->orWhereRaw('last_fetched_at < DATE_SUB(NOW(), INTERVAL fetch_interval_minutes MINUTE)');
            });
    }
}
```

---

## 4. RssItem

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RssItem extends Model
{
    use HasUuids;

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'rss_feed_id',
        'category_id',
        'guid',
        'title',
        'description',
        'url',
        'published_at',
        'fetched_at',
        'status',
        'dedup_hash',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];

    public function rssFeed(): BelongsTo
    {
        return $this->belongsTo(RssFeed::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function enrichedItem(): HasOne
    {
        return $this->hasOne(EnrichedItem::class);
    }

    public function clusterItems(): HasMany
    {
        return $this->hasMany(ClusterItem::class);
    }

    public function clusters()
    {
        return $this->belongsToMany(Cluster::class, 'cluster_items');
    }

    public function articleSources(): HasMany
    {
        return $this->hasMany(ArticleSource::class);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeEnriched($query)
    {
        return $query->where('status', 'enriched');
    }

    public function isEnriched(): bool
    {
        return $this->status === 'enriched' && $this->enrichedItem !== null;
    }
}
```

---

## 5. EnrichedItem

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrichedItem extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'rss_item_id',
        'lead',
        'headings',
        'key_points',
        'extracted_text',
        'extraction_method',
        'quality_score',
        'enriched_at',
    ];

    protected $casts = [
        'headings' => 'array',
        'key_points' => 'array',
        'quality_score' => 'integer',
        'enriched_at' => 'datetime',
    ];

    public function rssItem(): BelongsTo
    {
        return $this->belongsTo(RssItem::class);
    }

    public function getWordCount(): int
    {
        return str_word_count($this->extracted_text ?? '');
    }

    public function isHighQuality(): bool
    {
        return $this->quality_score >= 70;
    }
}
```

---

## 6. Cluster

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cluster extends Model
{
    use HasUuids;

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'category_id',
        'label',
        'keywords',
        'status',
    ];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function clusterItems(): HasMany
    {
        return $this->hasMany(ClusterItem::class);
    }

    public function rssItems()
    {
        return $this->belongsToMany(RssItem::class, 'cluster_items');
    }

    public function article(): HasOne
    {
        return $this->hasOne(Article::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeGenerated($query)
    {
        return $query->where('status', 'generated');
    }
}
```

---

## 7. ClusterItem

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClusterItem extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'cluster_id',
        'rss_item_id',
    ];

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function rssItem(): BelongsTo
    {
        return $this->belongsTo(RssItem::class);
    }
}
```

---

## 8. CategoryTemplate

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryTemplate extends Model
{
    use HasUuids;

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'category_id',
        'tone',
        'structure',
        'min_word_count',
        'max_word_count',
        'style_notes',
        'seo_rules',
    ];

    protected $casts = [
        'min_word_count' => 'integer',
        'max_word_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
```

---

## 9. Article

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'keywords',
        'category_id',
        'cluster_id',
        'reading_time',
        'status',
        'quality_score',
        'published_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'reading_time' => 'integer',
        'quality_score' => 'integer',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function articleSources(): HasMany
    {
        return $this->hasMany(ArticleSource::class);
    }

    public function sources()
    {
        return $this->belongsToMany(Source::class, 'article_sources')
            ->withPivot('rss_item_id', 'url', 'used_at');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isPublishable(): bool
    {
        return $this->quality_score >= 60 && in_array($this->status, ['draft', 'review']);
    }

    public function publish(): bool
    {
        if (!$this->isPublishable()) {
            return false;
        }
        return $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
```

---

## 10. ArticleSource

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleSource extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'article_id',
        'rss_item_id',
        'source_id',
        'url',
        'used_at',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function rssItem(): BelongsTo
    {
        return $this->belongsTo(RssItem::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
```

---

## 11. PipelineJob

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PipelineJob extends Model
{
    use HasUuids;

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'job_type',
        'status',
        'started_at',
        'completed_at',
        'error_message',
        'metadata',
        'retry_count',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('job_type', $type);
    }

    public function start(): void
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function fail(string $message): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $message,
            'retry_count' => $this->retry_count + 1,
        ]);
    }
}
```

---

## Récap relations

| Model | Relations |
|-------|-----------|
| Source | hasMany RssFeed, ArticleSource |
| Category | hasMany RssFeed, RssItem, Cluster, Article ; hasOne CategoryTemplate |
| RssFeed | belongsTo Source, Category ; hasMany RssItem (items) |
| RssItem | belongsTo RssFeed, Category ; hasOne EnrichedItem ; belongsToMany Cluster ; hasMany ArticleSource |
| EnrichedItem | belongsTo RssItem |
| Cluster | belongsTo Category ; hasMany ClusterItem ; belongsToMany RssItem ; hasOne Article |
| ClusterItem | belongsTo Cluster, RssItem |
| CategoryTemplate | belongsTo Category |
| Article | belongsTo Category, Cluster ; hasMany ArticleSource ; belongsToMany Source (article_sources, withPivot) |
| ArticleSource | belongsTo Article, RssItem, Source |
| PipelineJob | — |

---

*Référence : CONTEXTE_PROJET.md section 9. Voir aussi `docs/MIGRATIONS_REFERENCE.md`.*
