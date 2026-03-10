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
        'language',
        'sub_category_id',
        'cluster_id',
        'reading_time',
        'status',
        'article_type',
        'cover_image_url',
        'cover_video_url',
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

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
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

    /** Filtre par langue de contenu (fr ou nl). Les articles sans langue (NULL) sont considérés français. */
    public function scopeForLocale($query, string $locale)
    {
        $lang = strtolower($locale);
        if (! in_array($lang, ['fr', 'nl'], true)) {
            $lang = 'fr';
        }
        if ($lang === 'fr') {
            return $query->where(fn ($q) => $q->where('language', 'fr')->orWhereNull('language'));
        }
        return $query->where('language', 'nl');
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
