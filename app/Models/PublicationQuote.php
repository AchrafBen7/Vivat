<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicationQuote extends Model
{
    use HasUuids;

    protected $fillable = [
        'submission_id',
        'proposed_by',
        'price_preset_id',
        'amount_cents',
        'currency',
        'article_type',
        'status',
        'note_to_author',
        'expires_at',
        'sent_at',
        'accepted_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'expires_at'   => 'datetime',
        'sent_at'      => 'datetime',
        'accepted_at'  => 'datetime',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relations                                                          */
    /* ------------------------------------------------------------------ */

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(PricePreset::class, 'price_preset_id');
    }

    public function submissionPayments(): HasMany
    {
        return $this->hasMany(SubmissionPayment::class, 'quote_id');
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() && $this->status === 'sent';
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2, ',', ' ') . ' ' . strtoupper($this->currency);
    }

    public function getFormattedArticleTypeAttribute(): string
    {
        return match ($this->article_type) {
            'hot_news' => 'Hot news',
            'long_form' => 'Long format',
            default => 'Standard',
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'sent')->where('expires_at', '>', now());
    }
}
