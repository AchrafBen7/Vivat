<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
