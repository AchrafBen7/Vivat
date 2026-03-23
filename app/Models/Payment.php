<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'submission_id',
        'stripe_payment_intent_id',
        'amount',
        'currency',
        'status',
        'refund_reason',
        'stripe_refund_id',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    protected $hidden = [
        'stripe_payment_intent_id',
        'stripe_refund_id',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relations                                                         */
    /* ------------------------------------------------------------------ */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                           */
    /* ------------------------------------------------------------------ */

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isRefundable(): bool
    {
        return $this->status === 'paid' && $this->submission?->status === 'rejected';
    }

    public function markPaid(): bool
    {
        return $this->update(['status' => 'paid']);
    }

    public function markFailed(): bool
    {
        return $this->update(['status' => 'failed']);
    }

    public function markRefunded(string $refundId, ?string $reason = null): bool
    {
        return $this->update([
            'status'          => 'refunded',
            'stripe_refund_id' => $refundId,
            'refund_reason'   => $reason,
        ]);
    }
}
