<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PublicCacheService
{
    public function remember(string $domain, string $locale, string $suffix, int $ttl, callable $callback): mixed
    {
        if ($this->isDisabled()) {
            return $callback();
        }

        $key = $this->fragmentKey($domain, $locale, $suffix);

        return Cache::remember($key, $ttl, $callback);
    }

    public function bumpDomain(string $domain, string $locale): void
    {
        if ($this->isDisabled()) {
            return;
        }

        $versionKey = $this->versionKey($domain, $locale);
        $current = Cache::get($versionKey, 1);
        Cache::forever($versionKey, (int) $current + 1);
    }

    public function bumpDomains(array $domains, array $locales): void
    {
        foreach ($domains as $domain) {
            foreach ($locales as $locale) {
                $this->bumpDomain((string) $domain, (string) $locale);
            }
        }
    }

    public function isDisabled(): bool
    {
        return (bool) config('vivat.disable_page_cache', false);
    }

    private function fragmentKey(string $domain, string $locale, string $suffix): string
    {
        $prefix = (string) config('vivat.public_cache_prefix', 'vivat.public');
        $version = $this->version($domain, $locale);

        return implode(':', array_filter([
            $prefix,
            $domain,
            $locale,
            'v' . $version,
            $suffix,
        ], static fn (mixed $part): bool => $part !== ''));
    }

    private function version(string $domain, string $locale): int
    {
        return (int) Cache::rememberForever(
            $this->versionKey($domain, $locale),
            static fn (): int => 1,
        );
    }

    private function versionKey(string $domain, string $locale): string
    {
        $prefix = (string) config('vivat.public_cache_prefix', 'vivat.public');

        return implode(':', [$prefix, 'version', $domain, $locale]);
    }
}
