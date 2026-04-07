<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PipelineAutomationState
{
    private const CACHE_KEY = 'pipeline:automation:paused';

    public function isPaused(): bool
    {
        return Cache::get(self::CACHE_KEY, false) === true;
    }

    public function pause(): void
    {
        Cache::forever(self::CACHE_KEY, true);
    }

    public function resume(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
