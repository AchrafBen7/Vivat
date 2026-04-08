<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionPayment extends Model
{
    use HasUuids;

    protected $fillable = [
        'submission_id',
        'quote_id',
        'user_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_client_secret',
        'amount_cents',
        'currency',
        'status',
        'stripe_receipt_url',
        'failure_code',
        'failure_message',
        'idempotency_key',
        'paid_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'paid_at'      => 'datetime',
        'refunded_at'  => 'datetime',
    ];

    protected $hidden = [
        'stripe_client_secret',
        'stripe_payment_intent_id',
        'stripe_checkout_session_id',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relations                                                          */
    /* ------------------------------------------------------------------ */

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(PublicationQuote::class, 'quote_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeSucceeded($query)
    {
        return $query->where('status', 'succeeded');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2, ',', ' ') . ' ' . strtoupper($this->currency);
    }
}
