<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        RateLimiter::for('auth-login', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email', ''));
            $key = ($email !== '' ? $email : 'guest') . '|' . $request->ip();

            return Limit::perMinute(5)
                ->by($key)
                ->response(fn (Request $request, array $headers): RedirectResponse => back()
                    ->withErrors([
                        'email' => 'Trop de tentatives de connexion. Réessayez dans une minute.',
                    ])
                    ->withInput($request->only('email')));
        });

        RateLimiter::for('auth-register', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email', ''));
            $key = ($email !== '' ? $email : 'guest') . '|' . $request->ip();

            return Limit::perHour(5)
                ->by($key)
                ->response(fn (Request $request, array $headers): RedirectResponse => back()
                    ->withErrors([
                        'email' => 'Trop de tentatives d’inscription. Réessayez un peu plus tard.',
                    ])
                    ->withInput($request->except('password', 'password_confirmation')));
        });

        RateLimiter::for('newsletter-subscribe', function (Request $request) {
            $email = mb_strtolower((string) $request->input('newsletter_email', ''));
            $key = ($email !== '' ? $email : 'guest') . '|' . $request->ip();

            return Limit::perMinute(5)
                ->by($key)
                ->response(fn (Request $request, array $headers): RedirectResponse => back()
                    ->withErrors([
                        'newsletter_email' => 'Trop de tentatives. Merci de patienter avant de réessayer.',
                    ])
                    ->withInput($request->only('newsletter_email')));
        });

        RateLimiter::for('password-reset-link', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email', ''));
            $key = ($email !== '' ? $email : 'guest') . '|' . $request->ip();

            return Limit::perHour(5)
                ->by($key)
                ->response(fn (Request $request, array $headers): RedirectResponse => back()
                    ->withErrors([
                        'email' => 'Trop de demandes de réinitialisation. Réessayez un peu plus tard.',
                    ])
                    ->withInput($request->only('email')));
        });

        RateLimiter::for('search-suggestions', function (Request $request) {
            return Limit::perMinute(30)->by((string) $request->ip());
        });

        RateLimiter::for('api-auth-login', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email', ''));
            $key = ($email !== '' ? $email : 'guest') . '|' . $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('api-auth-register', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email', ''));
            $key = ($email !== '' ? $email : 'guest') . '|' . $request->ip();

            return Limit::perHour(5)->by($key);
        });

        RateLimiter::for('api-newsletter-subscribe', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email', ''));
            $key = ($email !== '' ? $email : 'guest') . '|' . $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('api-newsletter-actions', function (Request $request) {
            return Limit::perMinute(20)->by((string) $request->ip());
        });

        RateLimiter::for('payment-actions', function (Request $request) {
            return Limit::perMinute(10)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('admin-pipeline-actions', function (Request $request) {
            return Limit::perMinute(12)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('admin-moderation-actions', function (Request $request) {
            return Limit::perMinute(20)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('admin-financial-actions', function (Request $request) {
            return Limit::perMinute(5)->by((string) ($request->user()?->id ?: $request->ip()));
        });

        // Rate limiter for OpenAI API calls via queues
        RateLimiter::for('openai', function (object $job) {
            $key = property_exists($job, 'item') && $job->item !== null
                ? $job->item->id
                : ($job->job ?? 'default');
            return Limit::perMinute(50)->by($key);
        });

        // Rate limiter for public API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
