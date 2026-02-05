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
