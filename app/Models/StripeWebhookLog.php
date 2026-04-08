<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StripeWebhookLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'stripe_event_id',
        'type',
        'payload',
        'processed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'processed_at' => 'datetime',
    ];
}
