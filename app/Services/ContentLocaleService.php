<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Langue de contenu pour le site : fr ou nl.
 * Utilisé pour filtrer les articles et adapter l'affichage.
 * Détection : paramètre ?lang= (explicite) > Accept-Language (système) > cookie vivat_lang > défaut fr.
 * Ainsi un système en français affiche toujours le contenu FR sauf si l'utilisateur force ?lang=nl.
 */
class ContentLocaleService
{
    public const LOCALE_FR = 'fr';
    public const LOCALE_NL = 'nl';
    public const VALID_LOCALES = [self::LOCALE_FR, self::LOCALE_NL];
    public const COOKIE_NAME = 'vivat_lang';
    public const COOKIE_TTL_MINUTES = 43200; // 30 jours

    public function getLocale(?Request $request = null): string
    {
        $request = $request ?? request();
        if (! $request) {
            return config('app.locale', self::LOCALE_FR) === self::LOCALE_NL ? self::LOCALE_NL : self::LOCALE_FR;
        }

        // 1) Paramètre explicite (ex. ?lang=nl pour forcer le néerlandais)
        if ($request->filled('lang')) {
            $lang = Str::lower(Str::substr((string) $request->input('lang'), 0, 2));
            if (in_array($lang, self::VALID_LOCALES, true)) {
                return $lang;
            }
        }

        // 2) Langue du système / navigateur (prioritaire sur le cookie pour respecter la langue système)
        $header = $request->header('Accept-Language');
        if ($header !== null && $header !== '') {
            $first = explode(',', $header)[0];
            $preferred = Str::lower(Str::substr(trim(explode(';', $first)[0]), 0, 2));
            if (in_array($preferred, self::VALID_LOCALES, true)) {
                return $preferred;
            }
        }

        // 3) Cookie (choix utilisateur stocké)
        $cookie = $request->cookie(self::COOKIE_NAME);
        if (in_array($cookie, self::VALID_LOCALES, true)) {
            return $cookie;
        }

        return self::LOCALE_FR;
    }

    public function isValidLocale(string $locale): bool
    {
        return in_array($locale, self::VALID_LOCALES, true);
    }
}
