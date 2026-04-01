<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Langue de contenu pour le site : fr ou nl.
 * Utilisé pour filtrer les articles et adapter l'affichage.
 * Détection : paramètre ?lang= (explicite) > cookie vivat_lang (choix utilisateur) > Accept-Language > défaut fr.
 * Ainsi le choix explicite dans l'interface reste prioritaire pendant la navigation.
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

        // 2) Cookie (choix utilisateur stocké)
        $cookie = $request->cookie(self::COOKIE_NAME);
        if (in_array($cookie, self::VALID_LOCALES, true)) {
            return $cookie;
        }

        // 3) Langue du système / navigateur
        $header = $request->header('Accept-Language');
        if ($header !== null && $header !== '') {
            $first = explode(',', $header)[0];
            $preferred = Str::lower(Str::substr(trim(explode(';', $first)[0]), 0, 2));
            if (in_array($preferred, self::VALID_LOCALES, true)) {
                return $preferred;
            }
        }

        return self::LOCALE_FR;
    }

    public function isValidLocale(string $locale): bool
    {
        return in_array($locale, self::VALID_LOCALES, true);
    }
}
