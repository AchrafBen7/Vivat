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
