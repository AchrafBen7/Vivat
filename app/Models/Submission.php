<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Submission extends Model
{
    use HasUuids, HasSlug;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'category_id',
        'reading_time',
        'status',
        'reviewer_notes',
        'reviewed_by',
        'reviewed_at',
        'payment_id',
        'published_article_id',
        'depublication_requested_at',
        'depublication_reason',
        'cover_image_url',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'depublication_requested_at' => 'datetime',
        'reading_time' => 'integer',
    ];

    /* ------------------------------------------------------------------ */
    /*  Slug                                                              */
    /* ------------------------------------------------------------------ */

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    /* ------------------------------------------------------------------ */
    /*  Relations                                                         */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    public function publishedArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'published_article_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                            */
    /* ------------------------------------------------------------------ */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /* ------------------------------------------------------------------ */
    /*  Actions                                                           */
    /* ------------------------------------------------------------------ */

    public function approve(?string $reviewerId = null, ?string $notes = null, mixed $reviewedAt = null): bool
    {
        return $this->update([
            'status'         => 'approved',
            'reviewed_by'    => $reviewerId,
            'reviewer_notes' => $notes,
            'reviewed_at'    => $reviewedAt ?: now(),
        ]);
    }

    public function reject(?string $reviewerId = null, ?string $notes = null, mixed $reviewedAt = null): bool
    {
        return $this->update([
            'status'         => 'rejected',
            'reviewed_by'    => $reviewerId,
            'reviewer_notes' => $notes,
            'reviewed_at'    => $reviewedAt ?: now(),
        ]);
    }

    public function getCoverImagePathAttribute(): ?string
    {
        return $this->attributes['cover_image_url'] ?? null;
    }

    public function setCoverImagePathAttribute(?string $value): void
    {
        $this->attributes['cover_image_url'] = $value;
    }
}
