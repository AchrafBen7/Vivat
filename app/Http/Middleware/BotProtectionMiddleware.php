<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BotProtectionMiddleware
{
    public function handle(Request $request, Closure $next, string $surface = 'web'): Response
    {
        if (! $this->isEnabled()) {
            return $next($request);
        }

        $userAgent = trim((string) $request->userAgent());

        if (! $this->isBlockedUserAgent($userAgent)) {
            return $next($request);
        }

        Log::channel('security')->warning('Blocked suspicious user agent on public endpoint.', [
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $userAgent,
            'surface' => $surface,
        ]);

        if ($surface === 'api' || $request->expectsJson()) {
            return new JsonResponse([
                'message' => data_get(config('security.bot_protection.messages'), 'api', 'Forbidden'),
            ], 403);
        }

        return new RedirectResponse(
            url()->previous() ?: url('/'),
            302,
            []
        )->with('error', data_get(config('security.bot_protection.messages'), 'web', 'Votre requête ne peut pas être traitée pour le moment.'));
    }

    private function isEnabled(): bool
    {
        if (app()->environment('production')) {
            return (bool) config('security.bot_protection.enabled_in_production', true);
        }

        return (bool) config('security.bot_protection.enabled_in_local', false);
    }

    private function isBlockedUserAgent(string $userAgent): bool
    {
        $patterns = config('security.bot_protection.blocked_user_agents', []);

        foreach ($patterns as $pattern) {
            if (@preg_match($pattern, $userAgent) === 1) {
                return true;
            }
        }

        return false;
    }
}
