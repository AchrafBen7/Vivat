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
        $defaultIds = config('vivat.unsplash_fallback_ids', ['1524758631624-e2822e304c36']);
        $ids = $map[$key] ?? $map['default'] ?? $defaultIds;
        if (! is_array($ids) || empty($ids)) {
            $ids = is_array($defaultIds) && ! empty($defaultIds) ? $defaultIds : ['1524758631624-e2822e304c36'];
        }
        // Un seed par article (+ contexte) pour varier l’image entre articles
        $seed = (string) ($articleIdentifier ?? '') . ($contextSeed !== null && $contextSeed !== '' ? '-' . $contextSeed : '');
        $index = $seed !== '' ? abs(crc32($seed)) % count($ids) : 0;
        $photoId = $ids[$index];
        $w = max(1, $width);
        $h = max(1, $height);

        return 'https://images.unsplash.com/photo-'.$photoId.'?w='.$w.'&h='.$h.'&fit=crop&q=80';
    }
}

if (! function_exists('vivat_category_public_media_url')) {
    /**
     * URL d’un média local (public/) pour une rubrique : même nom de fichier que le slug (ou alias config).
     * Cherche dans l’ordre : mp4, webm, mov, jpg, jpeg, png, webp.
     */
    function vivat_category_public_media_url(?string $categorySlug): ?string
    {
        if ($categorySlug === null || $categorySlug === '') {
            return null;
        }

        $map = config('vivat.category_media_slug_map', []);
        $base = is_array($map) && isset($map[$categorySlug]) ? (string) $map[$categorySlug] : $categorySlug;

        foreach (['mp4', 'webm', 'mov', 'jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $relative = $base.'.'.$ext;
            if (file_exists(public_path($relative))) {
                return '/'.$relative;
            }
        }

        return null;
    }
}

if (! function_exists('vivat_category_public_poster_url')) {
    /**
     * Image poster (jpg/png/webp) dans public/ pour une vidéo de rubrique, même base de nom que le slug.
     */
    function vivat_category_public_poster_url(?string $categorySlug): ?string
    {
        if ($categorySlug === null || $categorySlug === '') {
            return null;
        }

        $map = config('vivat.category_media_slug_map', []);
        $base = is_array($map) && isset($map[$categorySlug]) ? (string) $map[$categorySlug] : $categorySlug;

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $relative = $base.'.'.$ext;
            if (file_exists(public_path($relative))) {
                return '/'.$relative;
            }
        }

        return null;
    }
}

if (! function_exists('get_layout_categories')) {
    /**
     * Catégories pour le menu (hamburger, footer). Même logique que la home.
     *
     * @return array<int, array{name: string, slug: string}>
     */
    function get_layout_categories(): array
    {
        $limit = (int) config('vivat.home_categories_count', 9);
        $categories = \App\Models\Category::query()
            ->when(
                \App\Models\Category::whereNotNull('home_order')->exists(),
                fn ($q) => $q->whereNotNull('home_order')->orderBy('home_order'),
                fn ($q) => $q->orderBy('name')
            )
            ->limit($limit)
            ->get(['name', 'slug']);

        return $categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug])->all();
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
