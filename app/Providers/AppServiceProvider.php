<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('openai', function (object $job) {
            $key = property_exists($job, 'item') && $job->item !== null
                ? $job->item->id
                : ($job->job ?? 'default');
            return Limit::perMinute(50)->by($key);
        });
    }
}
