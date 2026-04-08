<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricePreset extends Model
{
    use HasUuids;

    protected $fillable = [
        'label',
        'description',
        'amount_cents',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function quotes(): HasMany
    {
        return $this->hasMany(PublicationQuote::class, 'price_preset_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_cents / 100, 2, ',', ' ') . ' ' . strtoupper($this->currency);
    }
}
