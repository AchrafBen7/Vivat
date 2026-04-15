<?php
$content_locale = $content_locale ?? content_locale();
$isDutchLocale = $content_locale === 'nl';
$title = $title ?? 'Vivat';
$categories = $categories ?? get_layout_categories();
$meta_description = $meta_description ?? 'Vivat Actualités et articles. Découvrez nos rubriques et derniers articles.';
$canonical_url = $canonical_url ?? null;
$og_image = $og_image ?? null;
$og_article = $og_article ?? false;
$json_ld = $json_ld ?? null;
$meta_description_safe = htmlspecialchars($meta_description);
$title_safe = htmlspecialchars($title);
$sessionErrors = session()->get('errors');
$sessionErrorMessages = $sessionErrors ? $sessionErrors->getBag('default')->getMessages() : [];
$newsletterEmailError = $sessionErrorMessages['newsletter_email'][0] ?? null;
$newsletterOldEmail = old('newsletter_email', '');
$viteManifestPath = public_path('build/manifest.json');
$viteHotPath = public_path('hot');
$canLoadViteAssets = file_exists($viteManifestPath) || file_exists($viteHotPath);

// ── AJAX lang-switch : renvoyer uniquement le <main>, pas la page entière ─────
if (app('request')->header('X-Vivat-Ajax')) {
    $mainPb    = empty($trim_main_bottom) ? 'pb-8' : 'pb-0';
    $mainClass = 'max-w-[1400px] mx-auto mt-6 px-[18px] md:px-8 lg:px-10 xl:px-20 ' . $mainPb . ' overflow-x-hidden';
    echo '<!DOCTYPE html><html lang="' . htmlspecialchars($content_locale) . '"><head><title>' . $title_safe . '</title></head><body>';
    echo '<main class="' . htmlspecialchars($mainClass) . '">' . ($content ?? '') . '</main>';
    echo '</body></html>';
    return;
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($content_locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title_safe ?></title>
    <meta name="description" content="<?= $meta_description_safe ?>">
    <?php if (! empty($canonical_url)) { ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">
    <?php } ?>
    <meta property="og:type" content="<?= isset($og_article) && $og_article ? 'article' : 'website' ?>">
    <meta property="og:title" content="<?= $title_safe ?>">
    <meta property="og:description" content="<?= $meta_description_safe ?>">
    <?php if (! empty($canonical_url)) { ?>
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url) ?>">
    <?php } ?>
    <meta property="og:locale" content="<?= $content_locale === 'nl' ? 'nl_BE' : 'fr_FR' ?>">
    <?php if (! empty($og_image)) { ?>
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <?php } ?>
    <?php if (! empty($json_ld)) { ?>
    <script type="application/ld+json"><?= json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <?php } ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title_safe ?>">
    <meta name="twitter:description" content="<?= $meta_description_safe ?>">
    <?php if (! empty($og_image)) { ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
    <?php } ?>
    <?php if ($canLoadViteAssets) { ?>
    <?= app(\Illuminate\Foundation\Vite::class)(['resources/js/app.js']) ?>
    <?php } ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: { 'tablet': '834px' },
                    fontFamily: {
                        sans: ['Figtree', 'sans-serif'],
                        righteous: ['Righteous', 'cursive']
                    }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Righteous&display=swap" rel="stylesheet">
    <style>
        /* Espace FIXE 18px entre bordure carte et panel glass toujours en haut, bas, gauche, droite */
        .vivat-card-overlay {
            position: absolute;
            top: 18px;
            right: 18px;
            bottom: 18px;
            left: 18px;
            box-sizing: border-box;
        }
        /* Boîtes glass : 18px padding FIXE entre bordure glass et texte (haut, bas, gauche, droite) */
        .vivat-glass {
            background: rgba(190, 190, 190, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(230, 230, 230, 0.2);
            padding-top: 18px;
            padding-right: 18px;
            padding-bottom: 18px;
            padding-left: 18px;
            box-sizing: border-box;
        }
        /* Menu mobile ouvert : fond header retiré pour que le voile noir soit uniforme (pas de bandeau clair au-dessus) */
        header.header-menu-open {
            background: transparent !important;
            box-shadow: none !important;
        }

        /*
         * clip-path: inset() sans « round » = rectangle aux coins vifs : le voile (z-40) réapparaît en gris dans les courbes du panneau vert.
         * Même rayon que rounded-[34px] sur le panneau.
         */
        #mobile-menu-panel[data-open="true"] {
            -webkit-clip-path: inset(0 0 0 0 round 34px);
            clip-path: inset(0 0 0 0 round 34px);
        }

        #mobile-menu-panel[data-open="false"] {
            -webkit-clip-path: inset(0 0 100% 0 round 34px);
            clip-path: inset(0 0 100% 0 round 34px);
        }
        main {
            transition: none;
        }
        /* Pré-cache les cards hero avant le premier paint — évite le flash GSAP */
        .js-anim [data-home-hero] > * {
            opacity: 0;
            transform: translateY(20px) scale(0.98);
            will-change: opacity, transform;
        }

        /* Tag pill : effet glass sans padding supplémentaire */
        .vivat-glass-tag {
            background: rgba(190, 190, 190, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(230, 230, 230, 0.2);
        }
.vivat-lang-switch {
            --vivat-lang-active-bg: #004241;
            --vivat-lang-active-text: #ffffff;
            --vivat-lang-inactive-text: rgba(0, 66, 65, 0.68);
            position: relative;
            display: inline-grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem;
            border-radius: 9999px;
            isolation: isolate;
        }
        .vivat-lang-switch__indicator {
            position: absolute;
            top: 0.25rem;
            bottom: 0.25rem;
            left: 0.25rem;
            width: calc(50% - 0.375rem);
            border-radius: 9999px;
            background: var(--vivat-lang-active-bg);
            transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1), background-color 0.24s ease;
            will-change: transform;
            z-index: 0;
        }
        .vivat-lang-switch[data-active="nl"] .vivat-lang-switch__indicator {
            transform: translateX(calc(100% + 0.25rem));
        }
        .vivat-lang-switch__button {
            position: relative;
            z-index: 1;
            display: inline-flex;
            min-width: 3.25rem;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 9999px;
            background: transparent;
            color: var(--vivat-lang-inactive-text);
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            cursor: pointer;
            transition: color 0.22s ease, transform 0.22s ease;
        }
        .vivat-lang-switch__button[aria-pressed="true"] {
            color: var(--vivat-lang-active-text);
        }
        .vivat-lang-switch__button:active {
            transform: scale(0.97);
        }
        /* Barre recherche header : pastille + loupe → s'étire au hover / focus / texte saisi */
        #header-search-form {
            display: none;
            position: relative;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            flex-shrink: 0;
            border-radius: 9999px;
            background: #EBF1EF;
            overflow: hidden;
            gap: 0;
            padding: 0 0.125rem;
            box-shadow: none;
            transition:
                width 0.4s cubic-bezier(0.34, 1.2, 0.64, 1),
                gap 0.35s ease,
                padding 0.35s ease,
                justify-content 0s linear 0s;
        }
        @media (min-width: 768px) {
            #header-search-form {
                display: flex;
            }
        }
        #header-search-form:hover,
        #header-search-form:focus-within,
        #header-search-form.vivat-header-search--dirty {
            width: min(calc(100vw - 2.5rem), 22rem);
            justify-content: flex-start;
            gap: 0.375rem;
            padding-left: 0.375rem;
            padding-right: 0.75rem;
            overflow: visible;
        }
        /* Loupe à gauche à l'ouverture ; ordre Tab = ordre DOM (champ puis bouton) */
        #header-search-form input[name="q"] {
            order: 2;
            flex: 0 0 0;
            width: 0;
            min-width: 0;
            overflow: hidden;
            opacity: 0;
            border: none;
            background: transparent;
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: #004241;
            outline: none;
            transition: opacity 0.25s ease 0.06s, flex 0.4s cubic-bezier(0.34, 1.2, 0.64, 1);
        }
        #header-search-form input[name="q"]::placeholder {
            color: rgba(0, 66, 65, 0.55);
        }
        #header-search-form:hover input[name="q"],
        #header-search-form:focus-within input[name="q"],
        #header-search-form.vivat-header-search--dirty input[name="q"] {
            flex: 1 1 0%;
            min-width: 0;
            opacity: 1;
        }
        #header-search-form button[type="submit"] {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            border: none;
            border-radius: 9999px;
            background: transparent;
            color: #004241;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            order: 1;
        }
        #header-search-form button[type="submit"]:hover {
            background: rgba(0, 66, 65, 0.08);
        }
        #header-search-form button[type="submit"]:active {
            transform: scale(0.94);
        }
        /* Croix d'effacement custom (remplace le bouton natif type=search) */
        #header-search-form input[name="q"]::-webkit-search-cancel-button,
        #header-search-form input[name="q"]::-webkit-search-decoration {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }
        #header-search-clear {
            display: none;
            order: 3;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            padding: 0;
            border: none;
            border-radius: 9999px;
            background: transparent;
            color: #004241;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        #header-search-form.vivat-header-search--dirty #header-search-clear {
            display: flex;
        }
        #header-search-clear:hover {
            background: rgba(0, 66, 65, 0.08);
        }
        #header-search-clear:active {
            transform: scale(0.94);
        }
        #header-search-suggestions {
            position: absolute;
            top: calc(100% - 1px);
            left: 0;
            right: 0;
            display: none;
            flex-direction: column;
            gap: 0.375rem;
            padding: 0.85rem 0.75rem 0.75rem;
            border-radius: 0 0 1.75rem 1.75rem;
            background: #EBF1EF;
            border: 1px solid rgba(0, 66, 65, 0.08);
            border-top: none;
            box-shadow: 0 12px 28px rgba(0, 66, 65, 0.1);
            z-index: 70;
        }
        #header-search-suggestions.vivat-search-suggestions--open {
            display: flex;
        }
        #header-search-form.vivat-search-suggestions-host {
            border-radius: 1.75rem 1.75rem 0 0;
            z-index: 71;
        }
        #header-search-form.vivat-search-suggestions-host:hover,
        #header-search-form.vivat-search-suggestions-host:focus-within,
        #header-search-form.vivat-search-suggestions-host.vivat-header-search--dirty {
            border-radius: 1.75rem 1.75rem 0 0;
        }
        .header-search-suggestion-label {
            display: -webkit-box;
            overflow: hidden;
            text-wrap: balance;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .header-search-suggestion.is-active,
        .header-search-view-all.is-active {
            background: rgba(255, 255, 255, 0.72);
        }
        @media (min-width: 1024px) {
            #header-search-form:hover,
            #header-search-form:focus-within,
            #header-search-form.vivat-header-search--dirty {
                width: min(calc(100vw - 6rem), 24rem);
            }
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased font-sans">
<script>document.documentElement.classList.add('js-anim')</script>
    <?= render_php_view('site.partials.layout_header', get_defined_vars()) ?>

    <main class="max-w-[1400px] mx-auto mt-6 px-[18px] md:px-8 lg:px-10 xl:px-20 <?= ! empty($trim_main_bottom) ? 'pb-0' : 'pb-8' ?> overflow-x-hidden">
        <?php if (session('success')) { ?>
        <div class="mb-6 rounded-[20px] bg-[#004241] text-white px-6 py-4 flex items-center gap-3" role="alert">
            <svg class="w-6 h-6 flex-shrink-0 text-[#7DD3C1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-medium"><?= htmlspecialchars(session('success')) ?></p>
        </div>
        <?php } ?>
        <?php if (session('error')) { ?>
        <div class="mb-6 rounded-[20px] bg-[#AE422E] text-white px-6 py-4 flex items-center gap-3" role="alert">
            <svg class="w-6 h-6 flex-shrink-0 text-[#FFD2C9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M10.29 3.86l-7.5 13A2 2 0 004.53 20h14.94a2 2 0 001.74-3l-7.5-13a2 2 0 00-3.42 0z"/></svg>
            <p class="font-medium"><?= htmlspecialchars(session('error')) ?></p>
        </div>
        <?php } ?>
        <?= $content ?? '' ?>
    </main>

    <?php if (empty($hide_cta_section)) { ?>
    <section class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 <?= ! empty($compact_cta_spacing) ? 'mt-6' : 'mt-12' ?> mb-6" aria-label="Contribuer à Vivat">
        <a
            href="<?= htmlspecialchars(auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register')) ?>"
            class="group block rounded-[30px] bg-[#EBF1EF] p-6 no-underline transition-colors duration-200 hover:bg-[#E3ECE9] focus:outline-none focus:ring-2 focus:ring-[#004241] focus:ring-offset-2 md:p-8"
        >
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between md:gap-8">
                <div class="min-w-0 max-w-[44rem]">
                    <span class="inline-flex items-center justify-center rounded-full bg-white px-4 py-2 text-sm font-medium text-[#004241]">
                        <?= htmlspecialchars(auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? __('site.writer_space') : __('site.contribute')) ?>
                    </span>
                    <h2 class="mt-4 text-[28px] font-semibold leading-[1.05] text-[#004241] md:text-[34px]">
                        <?php if (auth()->check() && auth()->user()->hasRole(['contributor', 'admin'])) { ?>
                        <?= htmlspecialchars(__('site.writer_space')) ?>
                        <?php } else { ?>
                        <?= htmlspecialchars(__('site.global_contribute_title_prefix')) ?><span class="hidden lg:inline xl:hidden"><br></span><span class="lg:hidden xl:inline"> </span><?= htmlspecialchars(__('site.global_contribute_title_suffix')) ?>
                        <?php } ?>
                    </h2>
                    <p class="mt-3 max-w-[40rem] text-base leading-relaxed text-[#004241]/82 md:text-[17px]">
                        <?php if (auth()->check() && auth()->user()->hasRole(['contributor', 'admin'])) { ?>
                        <?= htmlspecialchars(__('site.global_writer_space_text')) ?>
                        <?php } else { ?>
                        <?= htmlspecialchars(__('site.global_contribute_text')) ?>
                        <?php } ?>
                    </p>
                </div>
                <div class="flex w-full md:w-auto md:justify-end">
                    <span class="inline-flex w-full items-center justify-between rounded-full bg-[#004241] px-6 py-4 text-base font-semibold text-white transition-colors duration-200 group-hover:bg-[#003130] md:w-auto md:min-w-[208px]">
                        <?= htmlspecialchars(auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? __('site.writer_space') : __('site.write_article')) ?>
                        <svg class="h-5 w-5 flex-shrink-0 translate-x-0 transition-transform duration-300 ease-out group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>
            </div>
        </a>
    </section>
    <?php } ?>

    <?= render_php_view('site.partials.layout_footer', get_defined_vars()) ?>
    <?= render_php_view('site.partials.layout_scripts', get_defined_vars()) ?>
</body>
</html>
