<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'cover_image_url',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
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
