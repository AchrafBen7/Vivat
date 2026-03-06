<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    use HasUuids;

    protected $fillable = [
        'email',
        'name',
        'interests',
        'confirmed',
        'unsubscribe_token',
        'confirm_token',
        'confirmed_at',
        'unsubscribed_at',
    ];

    protected $casts = [
        'interests'       => 'array',
        'confirmed'       => 'boolean',
        'confirmed_at'    => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected $hidden = [
        'unsubscribe_token',
        'confirm_token',
    ];

    /* ------------------------------------------------------------------ */
    /*  Boot                                                              */
    /* ------------------------------------------------------------------ */

    protected static function booted(): void
    {
        static::creating(function (self $subscriber) {
            $subscriber->unsubscribe_token = $subscriber->unsubscribe_token ?: Str::random(64);
            $subscriber->confirm_token = $subscriber->confirm_token ?: Str::random(64);
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                            */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('confirmed', true)->whereNull('unsubscribed_at');
    }

    /* ------------------------------------------------------------------ */
    /*  Actions                                                           */
    /* ------------------------------------------------------------------ */

    public function confirm(): bool
    {
        return $this->update([
            'confirmed'    => true,
            'confirmed_at' => now(),
            'confirm_token' => null,
        ]);
    }

    public function unsubscribe(): bool
    {
        return $this->update([
            'unsubscribed_at' => now(),
        ]);
    }
}
