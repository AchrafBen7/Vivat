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
     * URL d'image de repli par catégorie (pour cartes hot_news / featured quand pas d'image ou image cassée).
     * Image stable et cohérente avec la rubrique (seed basé sur le slug).
     *
     * @param  string|null  $categorySlug  Slug ou nom de la catégorie (ex: finance, energie)
     * @param  int  $width  Largeur
     * @param  int  $height  Hauteur
     * @return string URL
     */
    function vivat_category_fallback_image(?string $categorySlug, int $width = 800, int $height = 600, ?string $articleIdentifier = null, ?string $contextSeed = null): string
    {
        $localPoster = vivat_category_public_poster_url($categorySlug);
        if (is_string($localPoster) && $localPoster !== '') {
            return $localPoster;
        }

        $localMedia = vivat_category_public_media_url($categorySlug);
        if (is_string($localMedia) && $localMedia !== '' && ! preg_match('/\.(mp4|webm|mov)(\?|$)/i', $localMedia)) {
            return $localMedia;
        }

        $map = config('vivat.pexels_fallback_urls', []);
        $categoryKey = strtolower(trim((string) $categorySlug));
        $categoryUrls = $map[$categoryKey] ?? null;
        $candidateUrls = [];

        if (is_array($categoryUrls)) {
            foreach ($categoryUrls as $u) {
                $base = preg_replace('#\?.*$#', '', (string) $u);
                if ($base !== '' && ! in_array($base, $candidateUrls, true)) {
                    $candidateUrls[] = $base;
                }
            }
        }

        if (empty($candidateUrls)) {
            foreach ($map as $fallbackCategoryUrls) {
                if (! is_array($fallbackCategoryUrls)) {
                    continue;
                }

                foreach ($fallbackCategoryUrls as $u) {
                    $base = preg_replace('#\?.*$#', '', (string) $u);
                    if ($base !== '' && ! in_array($base, $candidateUrls, true)) {
                        $candidateUrls[] = $base;
                    }
                }
            }
        }

        if (empty($candidateUrls)) {
            $candidateUrls = ['https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg'];
        }

        $seed = (string) ($articleIdentifier ?? '') . ($contextSeed ? '-' . $contextSeed : '');
        $seed = $seed !== '' ? $seed : ($categoryKey !== '' ? $categoryKey : 'vivat-fallback');
        $baseUrl = $candidateUrls[abs(crc32($seed)) % count($candidateUrls)];
        $w = max(1, $width);
        $h = max(1, $height);

        return $baseUrl.'?auto=compress&cs=tinysrgb&fm=jpg&q=80&dpr=2&w='.$w.'&h='.$h.'&fit=crop';
    }
}

if (! function_exists('vivat_filter_label_case')) {
    /**
     * Libellé de filtre / sous-rubrique : première lettre de chaque mot en majuscule (cohérence UI).
     */
    function vivat_filter_label_case(string $label): string
    {
        $label = trim($label);
        if ($label === '') {
            return '';
        }
        $words = preg_split('/\s+/u', $label, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $out = [];
        foreach ($words as $word) {
            $lower = mb_strtolower($word, 'UTF-8');
            $out[] = mb_strtoupper(mb_substr($lower, 0, 1, 'UTF-8')).mb_substr($lower, 1, null, 'UTF-8');
        }

        return implode(' ', $out);
    }
}

if (! function_exists('vivat_hub_article_subcategory_badge_label')) {
    /**
     * Libellé du tag sur une carte du hub rubrique : une sous-rubrique (terme), alignée sur la logique
     * de filtrage (LIKE sur title, content, excerpt, meta_*, keywords). Permet d'afficher "Argent" vs
     * "Finance" quand plusieurs filtres sont actifs et les articles sont mélangés.
     *
     * @param  array<string, mixed>  $article
     * @param  array<int, array{name?: string, slug?: string}>  $subCategories  Termes de la rubrique (description + mots fréquents)
     * @param  array<int, string>  $activeSubCategorySlugs  Filtres actifs (vide = « Tous »)
     */
    function vivat_hub_article_subcategory_badge_label(
        array $article,
        array $subCategories,
        array $activeSubCategorySlugs,
        string $categoryName
    ): string {
        $bySlug = [];
        foreach ($subCategories as $row) {
            if (! is_array($row)) {
                continue;
            }
            $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            if ($slug !== '' && $name !== '') {
                $bySlug[$slug] = $name;
            }
        }

        $keywords = $article['keywords'] ?? [];
        $keywordsPart = '';
        if (is_array($keywords)) {
            $keywordsPart = implode(' ', array_map(static fn (mixed $k): string => trim((string) $k), $keywords));
        }

        $haystack = mb_strtolower(
            trim(
                implode(' ', array_filter([
                    (string) ($article['title'] ?? ''),
                    (string) ($article['excerpt'] ?? ''),
                    (string) ($article['content'] ?? ''),
                    (string) ($article['meta_title'] ?? ''),
                    (string) ($article['meta_description'] ?? ''),
                    $keywordsPart,
                ]))
            ),
            'UTF-8'
        );

        $termMatches = static function (string $termName) use ($haystack): bool {
            $termName = trim($termName);
            if ($termName === '') {
                return false;
            }

            return mb_stripos($haystack, mb_strtolower($termName, 'UTF-8'), 0, 'UTF-8') !== false;
        };

        $sub = $article['sub_category'] ?? null;
        $articleSubSlug = is_array($sub) && ! empty($sub['slug']) ? (string) $sub['slug'] : '';

        $activeSlugs = array_values(array_unique(array_filter(
            array_map(static fn (mixed $s): string => trim((string) $s), $activeSubCategorySlugs),
            static fn (string $s): bool => $s !== '' && isset($bySlug[$s])
        )));

        if ($activeSlugs !== []) {
            if ($articleSubSlug !== '' && in_array($articleSubSlug, $activeSlugs, true)) {
                return vivat_filter_label_case($bySlug[$articleSubSlug]);
            }

            foreach ($activeSlugs as $slug) {
                $name = $bySlug[$slug] ?? '';
                if ($name !== '' && $termMatches($name)) {
                    return vivat_filter_label_case($name);
                }
            }

            $dbName = is_array($sub) && isset($sub['name']) ? trim((string) $sub['name']) : '';
            if ($dbName !== '') {
                return vivat_filter_label_case($dbName);
            }

            return vivat_filter_label_case($categoryName);
        }

        if ($articleSubSlug !== '' && isset($bySlug[$articleSubSlug])) {
            return vivat_filter_label_case($bySlug[$articleSubSlug]);
        }

        if (is_array($sub) && isset($sub['name']) && trim((string) $sub['name']) !== '') {
            return vivat_filter_label_case((string) $sub['name']);
        }

        foreach ($subCategories as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            if ($name !== '' && $termMatches($name)) {
                return vivat_filter_label_case($name);
            }
        }

        return vivat_filter_label_case($categoryName);
    }
}

if (! function_exists('vivat_category_public_media_url')) {
    /**
     * URL d'un média local (public/) pour une rubrique : même nom de fichier que le slug (ou alias config).
     * Cherche dans l'ordre : mp4, webm, mov, jpg, jpeg, png, webp.
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
        if (! \Illuminate\Support\Facades\Schema::hasTable('categories')) {
            return [];
        }

        $limit = (int) config('vivat.home_categories_count', 9);
        $categories = \App\Models\Category::query()
            ->orderedForHome()
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
        $locale = app(\App\Services\ContentLocaleService::class)->getLocale($request);
        app()->setLocale($locale);

        return $locale;
    }
}
