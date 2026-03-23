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
        static $usedBaseUrls = [];
        $map = config('vivat.pexels_fallback_urls', []);
        $allUrls = [];
        foreach ($map as $categoryUrls) {
            if (is_array($categoryUrls)) {
                foreach ($categoryUrls as $u) {
                    $base = preg_replace('#\?.*$#', '', (string) $u);
                    if ($base !== '' && ! in_array($base, $allUrls, true)) {
                        $allUrls[] = $base;
                    }
                }
            }
        }
        if (empty($allUrls)) {
            $allUrls = ['https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg'];
        }
        $available = array_values(array_diff($allUrls, $usedBaseUrls));
        if (empty($available)) {
            $usedBaseUrls = [];
            $available = $allUrls;
        }
        $seed = (string) ($articleIdentifier ?? '') . ($contextSeed ? '-' . $contextSeed : '');
        $baseUrl = $available[abs(crc32($seed)) % count($available)];
        $usedBaseUrls[] = $baseUrl;
        $w = max(1, $width);
        $h = max(1, $height);

        return $baseUrl.'?auto=compress&cs=tinysrgb&fm=jpg&q=80&dpr=2&w='.$w.'&h='.$h.'&fit=crop';
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

if (! function_exists('vivat_cloudinary_video_poster_url')) {
    /**
     * Génère un poster JPG depuis une URL vidéo Cloudinary.
     * Ex.: /video/upload/v123/foo.mp4 => /video/upload/so_0/v123/foo.jpg
     */
    function vivat_cloudinary_video_poster_url(?string $videoUrl): ?string
    {
        if (! is_string($videoUrl) || trim($videoUrl) === '') {
            return null;
        }

        $trimmed = trim($videoUrl);

        if (! str_contains($trimmed, 'res.cloudinary.com/') || ! str_contains($trimmed, '/video/upload/')) {
            return null;
        }

        $posterUrl = preg_replace(
            '#/video/upload/#',
            '/video/upload/so_0/',
            $trimmed,
            1
        );

        if (! is_string($posterUrl)) {
            return null;
        }

        return preg_replace('#\.(mp4|webm|mov)(\?.*)?$#i', '.jpg$2', $posterUrl) ?: null;
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
