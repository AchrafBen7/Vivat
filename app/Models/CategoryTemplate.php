<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryTemplate extends Model
{
    use HasUuids;

    public $timestamps = false;

    const CREATED_AT = 'created_at';

    protected $fillable = [
        'category_id',
        'tone',
        'structure',
        'min_word_count',
        'max_word_count',
        'style_notes',
        'seo_rules',
    ];

    protected $casts = [
        'min_word_count' => 'integer',
        'max_word_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
