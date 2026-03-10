<?php

if (! function_exists('render_php_view')) {
    /**
     * Rend une vue PHP (fichier .php) avec les données fournies.
     * Pas de Blade : HTML + logique PHP directe.
     *
     * @param  string  $template  Nom du template (ex: 'site.home') → resources/views/site/home.php
     * @param  array<string, mixed>  $data  Données à extraire dans la vue
     * @return string HTML rendu
     */
    function render_php_view(string $template, array $data = []): string
    {
        $path = resource_path('views/' . str_replace('.', '/', $template) . '.php');

        if (! file_exists($path)) {
            throw new InvalidArgumentException("Vue PHP introuvable: {$path}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $path;

        return (string) ob_get_clean();
    }
}

if (! function_exists('vivat_category_fallback_image')) {
    /**
     * URL d’image de repli par catégorie (pour cartes hot_news / featured quand pas d’image ou image cassée).
     * Image stable et cohérente avec la rubrique (seed basé sur le slug).
     *
     * @param  string|null  $categorySlug  Slug ou nom de la catégorie (ex: finance, energie)
     * @param  int  $width  Largeur
     * @param  int  $height  Hauteur
     * @return string URL
     */
    function vivat_category_fallback_image(?string $categorySlug, int $width = 800, int $height = 600, ?string $articleIdentifier = null, ?string $contextSeed = null): string
    {
        $key = $categorySlug !== null && $categorySlug !== '' ? strtolower(trim($categorySlug)) : 'default';
        $map = config('vivat.unsplash_fallback_by_category', []);
        $ids = $map[$key] ?? $map['default'] ?? ['1524758631624-e2822e304c36'];
        if (! is_array($ids) || empty($ids)) {
            $ids = ['1524758631624-e2822e304c36'];
        }
        $seed = (string) ($articleIdentifier ?? '') . ($contextSeed !== null && $contextSeed !== '' ? '-' . $contextSeed : '');
        $index = $seed !== '' ? abs(crc32($seed)) % count($ids) : 0;
        $photoId = $ids[$index];
        $w = max(1, $width);
        $h = max(1, $height);

        return 'https://images.unsplash.com/photo-'.$photoId.'?w='.$w.'&h='.$h.'&fit=crop&q=80';
    }
}

if (! function_exists('content_locale')) {
    /**
     * Langue de contenu courante (fr ou nl) pour filtrer les articles.
     * Dépend du cookie vivat_lang, du paramètre lang, de Accept-Language, ou défaut fr.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return string 'fr' ou 'nl'
     */
    function content_locale(?\Illuminate\Http\Request $request = null): string
    {
        return app(\App\Services\ContentLocaleService::class)->getLocale($request);
    }
}
