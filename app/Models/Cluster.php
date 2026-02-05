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
