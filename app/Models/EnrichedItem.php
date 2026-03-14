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
        'seo_keywords',
        'primary_topic',
        'extracted_text',
        'extraction_method',
        'quality_score',
        'seo_score',
        'enriched_at',
    ];

    protected $casts = [
        'headings' => 'array',
        'key_points' => 'array',
        'seo_keywords' => 'array',
        'quality_score' => 'integer',
        'seo_score' => 'integer',
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
}
