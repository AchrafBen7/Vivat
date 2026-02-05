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
