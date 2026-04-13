<?php

namespace App\Models;

use App\Exceptions\InvalidStatusTransitionException;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Submission extends Model
{
    use HasUuids, HasSlug;

    /**
     * Transitions autorisées : [from => [to, ...]]
     */
    private const ALLOWED_TRANSITIONS = [
        'draft'              => ['submitted'],
        'pending'            => ['submitted', 'under_review'],  // compat ancien workflow
        'submitted'          => ['under_review'],
        'under_review'       => ['changes_requested', 'rejected', 'price_proposed'],
        'changes_requested'  => ['submitted'],
        'price_proposed'     => ['awaiting_payment', 'rejected'],
        'awaiting_payment'   => ['payment_pending', 'payment_expired', 'payment_canceled'],
        'payment_pending'    => ['payment_succeeded', 'payment_failed', 'payment_expired', 'awaiting_payment'],
        'payment_failed'     => ['awaiting_payment'],
        'payment_succeeded'  => ['published', 'payment_refunded'],
        'payment_expired'    => ['awaiting_payment'],
        'published'          => ['payment_refunded'],
        // Terminaux (aucune transition)
        'rejected'           => [],
        'payment_canceled'   => [],
        'payment_refunded'   => [],
        // Legacy
        'approved'           => ['published'],
    ];

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'category_id',
        'language',
        'reading_time',
        'status',
        'submitted_at',
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
        'reviewed_at'                => 'datetime',
        'submitted_at'               => 'datetime',
        'depublication_requested_at' => 'datetime',
        'reading_time'               => 'integer',
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

    /** @deprecated Ancien système de paiement avant review */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /* ─── Nouveau workflow ─── */

    public function quote(): HasOne
    {
        return $this->hasOne(PublicationQuote::class)->latestOfMany();
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(PublicationQuote::class);
    }

    public function submissionPayments(): HasMany
    {
        return $this->hasMany(SubmissionPayment::class);
    }

    public function activeSubmissionPayment(): HasOne
    {
        return $this->hasOne(SubmissionPayment::class)
            ->whereIn('status', ['pending', 'processing', 'succeeded'])
            ->latestOfMany();
    }

    public function latestSubmissionPayment(): HasOne
    {
        return $this->hasOne(SubmissionPayment::class)->latestOfMany();
    }

    public function latestRefundedSubmissionPayment(): HasOne
    {
        return $this->hasOne(SubmissionPayment::class)
            ->where('status', 'refunded')
            ->latestOfMany();
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(SubmissionStatusLog::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                            */
    /* ------------------------------------------------------------------ */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'pending']);
    }

    public function scopeAwaitingReview($query)
    {
        return $query->whereIn('status', ['submitted', 'pending', 'under_review']);
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /* ------------------------------------------------------------------ */
    /*  Machine à états                                                   */
    /* ------------------------------------------------------------------ */

    /**
     * Transite vers un nouveau statut avec validation et log d'audit.
     *
     * @throws InvalidStatusTransitionException
     */
    public function transitionTo(
        string $newStatus,
        ?string $triggeredBy = null,
        string $triggerSource = 'system',
        ?string $reason = null,
        array $metadata = []
    ): void {
        $allowed = self::ALLOWED_TRANSITIONS[$this->status] ?? [];

        if (! in_array($newStatus, $allowed, true)) {
            throw new InvalidStatusTransitionException(
                "Transition interdite : [{$this->status}] → [{$newStatus}]"
            );
        }

        $oldStatus = $this->status;

        DB::transaction(function () use ($newStatus, $oldStatus, $triggeredBy, $triggerSource, $reason, $metadata): void {
            $updates = ['status' => $newStatus];

            if ($newStatus === 'submitted') {
                $updates['submitted_at'] = now();
            }

            if (in_array($newStatus, ['under_review'], true)) {
                $updates['reviewed_by'] = $triggeredBy ?? $this->reviewed_by;
                $updates['reviewed_at'] = now();
            }

            $this->update($updates);

            SubmissionStatusLog::create([
                'submission_id'  => $this->id,
                'from_status'    => $oldStatus,
                'to_status'      => $newStatus,
                'triggered_by'   => $triggeredBy,
                'trigger_source' => $triggerSource,
                'reason'         => $reason,
                'metadata'       => $metadata ?: null,
                'created_at'     => now(),
            ]);
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Actions legacy (compat ancien workflow)                           */
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

    /* ------------------------------------------------------------------ */
    /*  Compat attributs                                                  */
    /* ------------------------------------------------------------------ */

    public function getCoverImagePathAttribute(): ?string
    {
        return $this->attributes['cover_image_url'] ?? null;
    }

    public function setCoverImagePathAttribute(?string $value): void
    {
        $this->attributes['cover_image_url'] = $value;
    }
}
