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
        if (! $this->isPublishable()) {
            return false;
        }

        return $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}
