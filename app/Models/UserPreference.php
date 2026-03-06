<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use HasUuids;

    protected $fillable = [
        'session_id',
        'interests',
        'language',
    ];

    protected $casts = [
        'interests' => 'array',
    ];
}
