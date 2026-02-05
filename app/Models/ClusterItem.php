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
