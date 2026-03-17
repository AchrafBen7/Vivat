<?php
$content_locale = $content_locale ?? content_locale();
$title = $title ?? 'Vivat';
$categories = $categories ?? get_layout_categories();
$meta_description = $meta_description ?? 'Vivat — Actualités et articles. Découvrez nos rubriques et derniers articles.';
$canonical_url = $canonical_url ?? null;
$og_image = $og_image ?? null;
$og_article = $og_article ?? false;
$meta_description_safe = htmlspecialchars($meta_description);
$title_safe = htmlspecialchars($title);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($content_locale) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title_safe ?></title>
    <meta name="description" content="<?= $meta_description_safe ?>">
    <?php if (!empty($canonical_url)): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">
    <?php endif; ?>
    <!-- Open Graph -->
    <meta property="og:type" content="<?= isset($og_article) && $og_article ? 'article' : 'website' ?>">
    <meta property="og:title" content="<?= $title_safe ?>">
    <meta property="og:description" content="<?= $meta_description_safe ?>">
    <?php if (!empty($canonical_url)): ?>
    <meta property="og:url" content="<?= htmlspecialchars($canonical_url) ?>">
    <?php endif; ?>
    <meta property="og:locale" content="<?= $content_locale === 'nl' ? 'nl_BE' : 'fr_FR' ?>">
    <?php if (!empty($og_image)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <?php endif; ?>
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title_safe ?>">
    <meta name="twitter:description" content="<?= $meta_description_safe ?>">
    <?php if (!empty($og_image)): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
    <?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: { 'tablet': '834px' },
                    fontFamily: { sans: ['Figtree', 'sans-serif'] }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Righteous&display=swap" rel="stylesheet">
    <style>
        :root {
            --vivat-teal: #004241;
            --vivat-bg-search: #EBF1EF;
            --vivat-cream: #FFF0D4;
            --vivat-overlay-dark: rgba(0, 0, 0, 0.2);
            --vivat-card-glass: rgba(255, 255, 255, 0.11);
            --vivat-card-border: rgba(255, 255, 255, 0.15);
            --vivat-nav-glass: #004241;
            --vivat-tag-height: 30px;
            --vivat-tag-padding-x: 12px;
            --vivat-tag-font-size: 12px;
            --vivat-tag-letter-spacing: 0.02em;
        }
        html {
            scroll-behavior: smooth;
        }
        /* Glass effect matte : 18px padding intérieur pour 18px d’espace visuel depuis la card */
        .vivat-glass {
            background: rgba(190, 190, 190, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(1px);
            border: 1px solid rgba(230, 230, 230, 0.2);
            padding: 18px;
            box-sizing: border-box;
        }
        .vivat-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            max-width: 100%;
            min-height: var(--vivat-tag-height);
            padding: 0 var(--vivat-tag-padding-x);
            border-radius: 9999px;
            box-sizing: border-box;
            font-size: var(--vivat-tag-font-size);
            line-height: 1;
            font-weight: 500;
            letter-spacing: var(--vivat-tag-letter-spacing);
            white-space: nowrap;
            flex-shrink: 0;
        }
        .font-righteous { font-family: 'Righteous', cursive; }
        .nav-contact-btn {
            background: #004241;
            color: #FFFFFF;
            transition: background-color 200ms ease, color 200ms ease;
        }
        .nav-contact-btn:hover {
            background: #527E7E;
            color: #FFFFFF;
        }
        .vivat-btn-teal-hover {
            background: #004241;
            color: #FFFFFF;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .vivat-btn-teal-hover:hover {
            background: #527E7E;
            color: #FFFFFF;
        }
        /* Menu mobile hamburger : panneau en popup au-dessus du contenu */
        .header-nav-wrap {
            position: relative;
            z-index: 50;
        }
        .mobile-menu-panel {
            position: absolute;
            right: 80px;
            top: calc(100%);
            width: min(100%, 715px);
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 66, 65, 0.15);
            transition: max-height 0.35s ease-out, opacity 0.25s ease;
        }
        .mobile-menu-panel.vivat-glass {
            background: var(--vivat-nav-glass);
            border: 1px solid rgba(230, 230, 230, 0.18);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
        .mobile-menu-panel.is-open {
            max-height: 980px;
            opacity: 1;
            transition: max-height 0.4s ease-in, opacity 0.25s ease;
        }
        @media (min-width: 768px) and (max-width: 1023px) {
            .mobile-menu-panel {
                left: 24px !important;
                right: 24px !important;
                width: auto !important;
                max-width: none !important;
                box-sizing: border-box;
            }

            .mobile-menu-categories {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
        @media (max-width: 833px) {
            .mobile-menu-panel {
                left: 0;
                right: 0;
                top: 100%;
                width: auto;
            }
            .mobile-menu-panel.is-open {
                max-height: 650px;
            }
        }
        /* Croix X : les 3 barres se rejoignent au centre (centre du groupe = 10.5px) */
        .hamburger-line {
            transform-origin: center center;
            transition: transform 0.25s ease, opacity 0.2s ease;
        }
        .hamburger-btn[aria-expanded="true"] .hamburger-line:nth-child(1) {
            transform: translateY(9px) rotate(45deg);
        }
        .hamburger-btn[aria-expanded="true"] .hamburger-line:nth-child(2) {
            opacity: 0;
        }
        .hamburger-btn[aria-expanded="true"] .hamburger-line:nth-child(3) {
            transform: translateY(-9px) rotate(-45deg);
        }
        .nav-search-bar {
            width: 48px;
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            transition: width 0.7s ease, padding 0.7s ease;
        }
        .nav-search-bar:hover {
            width: 326px;
            justify-content: flex-end;
            padding-left: 12px;
            padding-right: 12px;
        }
        .nav-search-bar .nav-search-input {
            flex: 0 0 0;
            width: 0;
            min-width: 0;
            margin-right: 0;
            opacity: 0;
            pointer-events: none;
            overflow: hidden;
            transition: opacity 0.6s ease;
        }
        .nav-search-bar:hover .nav-search-input {
            flex: 1 1 0;
            min-width: 120px;
            margin-right: 8px;
            opacity: 1;
            pointer-events: auto;
        }
        @media (max-width: 767px) {
            .site-header-row {
                height: 72px;
                padding-top: 18px;
                padding-bottom: 18px;
            }
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased font-sans">
    <!-- Navbar - Design System Figma -->
    <header class="bg-white">
        <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 header-nav-wrap">
            <div class="site-header-row flex items-center h-[88px] pt-[35px] pb-[35px]">
            <!-- Logo: 32px, #004241 ; tablet: margin 40px -->
            <h1 class="font-righteous text-[32px] font-normal flex-shrink-0 text-[#004241] tracking-[0.03em]"><a href="/" class="text-inherit no-underline hover:opacity-90">Vivat</a></h1>

            <!-- Espace logo - searchbar: visible tablet+ -->
            <div class="hidden md:block flex-shrink-0 flex-1 min-w-4"></div>
            <div class="flex-1 md:flex-none md:flex-shrink-0"></div>

            <!-- Search: par défaut rond avec icône, au hover s’étend en barre avec placeholder -->
            <form action="/search" method="get" class="nav-search-bar hidden md:flex items-center flex-shrink-0 rounded-full h-12 overflow-hidden bg-[#E5EDEB]">
                <input type="search" name="q" value="<?= htmlspecialchars(request()->get('q', '')) ?>" placeholder="Rechercher un article ou une catégorie" class="nav-search-input flex-1 min-w-0 bg-transparent text-sm outline-none border-none text-[#226E65] placeholder:text-[#226E65]">
                <button type="submit" class="flex items-center justify-center flex-shrink-0 p-2 text-[#226E65] hover:text-[#004241] transition" aria-label="Rechercher">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>

            <!-- 9px espace -->
            <div class="w-[9px] flex-shrink-0"></div>

            <!-- Contactez-nous: 162x48, #004241, Figtree 500 16px, text #FFFFFF -->
            <a href="/contact" class="nav-contact-btn hidden md:flex items-center justify-center rounded-full flex-shrink-0 h-12 font-medium text-base leading-none w-[162px] py-3 px-5">
                Contactez-nous
            </a>

            <!-- 19px espace -->
            <div class="hidden md:block w-[19px] flex-shrink-0"></div>

            <!-- Hamburger: simple, 48x48, border-radius 30px, visible mobile uniquement -->
            <button type="button" id="hamburger-menu" class="hamburger-btn flex flex-col items-center justify-center gap-1.5 flex-shrink-0 w-12 h-12 bg-transparent border-none cursor-pointer rounded-[30px]" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobile-menu-panel">
                <span class="hamburger-line block rounded-full w-7 h-[3px] bg-[#004241]"></span>
                <span class="hamburger-line block rounded-full w-7 h-[3px] bg-[#004241]"></span>
                <span class="hamburger-line block rounded-full w-7 h-[3px] bg-[#004241]"></span>
            </button>
            </div>

            <!-- Panneau mobile bento : popup blanc, aligné grille (pleine largeur), plus grand -->
            <div id="mobile-menu-panel" class="mobile-menu-panel vivat-glass rounded-[30px] p-6 tablet:p-8" role="dialog" aria-label="Menu de navigation">
                <?php if (auth()->check()): ?>
                <a href="<?= auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : url('/') ?>" class="block rounded-2xl bg-white/15 border border-white/20 p-4 mb-4 hover:bg-white/20 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#004241] flex items-center justify-center text-white font-semibold text-sm">
                            <?= strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-white text-base truncate"><?= htmlspecialchars(auth()->user()->name ?? 'Mon compte') ?></p>
                            <p class="text-white/80 text-sm"><?= auth()->user()->hasRole(['contributor', 'admin']) ? 'Espace rédacteur' : 'Mon profil' ?></p>
                        </div>
                        <svg class="w-5 h-5 text-white/70 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                <form action="<?= url('/logout') ?>" method="post" class="mb-4">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full py-2 px-3 rounded-2xl text-white/80 text-sm hover:bg-white/10 transition text-left">Se déconnecter</button>
                </form>
                <?php endif; ?>
                <nav class="flex flex-col gap-1" aria-label="Navigation principale">
                    <a href="/" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline hover:bg-white/10 transition">Home</a>
                    <a href="/a-propos" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline hover:bg-white/10 transition">À propos</a>
                    <a href="/contact" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline hover:bg-white/10 transition">Contact</a>
                    <a href="/faq" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline hover:bg-white/10 transition">FAQ</a>
                </nav>
                <p class="font-semibold text-white text-base mt-5 mb-2 pt-4 border-t border-white/20">Rubriques</p>
                <nav class="mobile-menu-categories grid grid-cols-3 gap-x-3 gap-y-1" aria-label="Rubriques">
                    <?php foreach ($categories as $cat): ?>
                    <a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline hover:bg-white/10 transition"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    </header>
    <main class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 pb-8 overflow-x-hidden">
        <?php if (session('success')): ?>
        <div class="mb-6 rounded-[20px] bg-[#004241] text-white px-6 py-4 flex items-center gap-3" role="alert">
            <svg class="w-6 h-6 flex-shrink-0 text-[#7DD3C1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-medium"><?= htmlspecialchars(session('success')) ?></p>
        </div>
        <?php endif; ?>
        <?= $content ?? '' ?>
    </main>
    <?php if (empty($hide_cta_section)): ?>
    <!-- CTA contribution : juste au-dessus du footer, 24px de marge avant le footer -->
    <section class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 mt-12 mb-6" aria-label="Contribuer à Vivat">
        <a href="<?= htmlspecialchars(auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register')) ?>" class="block rounded-[30px] overflow-hidden relative min-h-[340px] lg:min-h-[380px] bg-cover bg-center focus:outline-none focus:ring-2 focus:ring-[#004241] focus:ring-offset-2 bg-[url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&q=80')]">
            <span class="absolute inset-0 bg-black/30" aria-hidden="true"></span>
            <span class="absolute inset-0 flex flex-col items-center justify-center gap-6 px-6 py-12 text-center">
                <span class="text-white font-semibold text-xl lg:text-2xl leading-tight">
                    <?php if (auth()->check() && auth()->user()->hasRole(['contributor', 'admin'])): ?>
                    Accédez à votre espace rédacteur
                    <?php else: ?>
                    Une idée, une histoire, un point de vue ?<br>
                    Vivat est ouvert aux nouvelles voix
                    <?php endif; ?>
                </span>
                <span class="inline-flex items-center justify-center h-12 px-6 rounded-full font-medium whitespace-nowrap transition hover:opacity-90 bg-[#FFEFD1] text-[#004241] text-base">
                    <?= auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? 'Accéder au bureau' : 'Rédigez un article' ?>
                </span>
            </span>
        </a>
    </section>
    <?php endif; ?>
    <footer>
        <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 mb-6 w-full">
            <div class="rounded-[34px] bg-[#E7EFEC] p-6 md:p-8" style="box-shadow: 0 24px 64px rgba(0, 66, 65, 0.08);">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <div class="flex flex-col justify-center rounded-[30px] bg-[#004241] p-6 md:p-8 text-white lg:col-span-7" style="gap: 20px; min-height: 100%;">
                        <span class="inline-flex w-fit items-center justify-center rounded-full bg-white/18 px-[16px] py-[8px] text-sm font-medium text-white" style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.14);">Newsletter</span>
                        <div class="flex flex-col" style="gap: 10px;">
                            <h2 class="max-w-[13ch] font-medium text-white" style="font-size: clamp(28px, 4vw, 46px); line-height: 0.98;">Les articles à ne pas rater, directement dans votre boîte mail.</h2>
                            <p class="max-w-[44ch] text-white/78" style="font-size: 17px; line-height: 1.4;">Une sélection simple, claire, et utile pour suivre Vivat sans chercher partout.</p>
                        </div>
                        <form action="#" method="post" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px]">
                            <input type="email" name="email" placeholder="you@example.com" class="h-12 rounded-full border-0 bg-white pl-5 pr-5 text-base text-gray-900 outline-none transition focus:ring-2 focus:ring-white/30">
                            <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-[#FFF0D4] px-8 font-semibold text-[#004241] transition hover:opacity-90">
                                S'abonner
                            </button>
                        </form>
                    </div>

                    <div class="flex flex-col rounded-[30px] bg-white p-6 md:p-8 lg:col-span-5" style="gap: 18px;">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-6">
                            <nav class="flex flex-col" style="gap: 14px;" aria-label="Découvrir">
                                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Découvrir</span>
                                <ul class="m-0 flex list-none flex-col gap-3 p-0">
                                    <li><a href="/" class="text-base text-[#004241]/85 no-underline transition hover:text-[#004241]">Accueil</a></li>
                                    <li><a href="/a-propos" class="text-base text-[#004241]/85 no-underline transition hover:text-[#004241]">À propos</a></li>
                                    <li><a href="/contact" class="text-base text-[#004241]/85 no-underline transition hover:text-[#004241]">Contact</a></li>
                                    <li><a href="/faq" class="text-base text-[#004241]/85 no-underline transition hover:text-[#004241]">FAQ</a></li>
                                </ul>
                            </nav>
                            <nav class="flex flex-col" style="gap: 14px;" aria-label="Légal">
                                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Légal</span>
                                <ul class="m-0 flex list-none flex-col gap-3 p-0">
                                    <li><a href="/mentions-legales" class="text-base text-[#004241]/85 no-underline transition hover:text-[#004241]">Mentions légales</a></li>
                                    <li><a href="/politique-confidentialite" class="text-base text-[#004241]/85 no-underline transition hover:text-[#004241]">Confidentialité</a></li>
                                    <li><a href="/politique-cookies" class="text-base text-[#004241]/85 no-underline transition hover:text-[#004241]">Cookies</a></li>
                                </ul>
                            </nav>
                            <nav class="col-span-2 flex flex-col" style="gap: 14px;" aria-label="Rubriques">
                                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Rubriques</span>
                                <ul class="m-0 grid list-none grid-cols-2 gap-x-6 gap-y-3 p-0">
                                    <?php foreach ($categories as $cat): ?>
                                    <li><a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="text-base text-[#004241]/85 no-underline transition hover:text-[#004241]"><?= htmlspecialchars($cat['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex flex-col gap-3 border-t border-[#004241]/10 pt-5 text-sm text-[#004241]/60 md:flex-row md:items-center md:justify-between">
                    <p class="m-0">© <?= date('Y') ?> Vivat. Tous droits réservés.</p>
                    <a href="/contact" class="text-sm text-[#004241]/70 no-underline transition hover:text-[#004241]">Une question ? Contactez-nous</a>
                </div>
            </div>
        </div>
    </footer>
    <script>
    (function() {
        function initScrollReveal() {
            var groups = document.querySelectorAll('.vivat-reveal-group');
            if (!groups.length) return;
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (!entry.isIntersecting) return;
                    var group = entry.target;
                    if (group.classList.contains('vivat-reveal-done')) return;
                    group.classList.add('vivat-reveal-done');
                    var items = Array.from(group.querySelectorAll('.vivat-reveal'));
                    var delay = 180;
                    // Trier par position verticale (haut → bas) puis horizontale (gauche → droite) pour un reveal top-to-bottom
                    items.sort(function(a, b) {
                        var ra = a.getBoundingClientRect();
                        var rb = b.getBoundingClientRect();
                        var dy = ra.top - rb.top;
                        if (Math.abs(dy) > 2) return dy;
                        return ra.left - rb.left;
                    });
                    items.forEach(function(el, i) {
                        el.style.transitionDelay = (i * delay) + 'ms';
                        el.classList.add('opacity-100', 'translate-y-0');
                    });
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0 });
            groups.forEach(function(g) { observer.observe(g); });
        }
        function initHamburgerMenu() {
            var btn = document.getElementById('hamburger-menu');
            var panel = document.getElementById('mobile-menu-panel');
            if (!btn || !panel) return;
            btn.addEventListener('click', function() {
                var open = panel.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                btn.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initScrollReveal();
                initHamburgerMenu();
            });
        } else {
            initScrollReveal();
            initHamburgerMenu();
        }
        // Fallback image : si une image a data-fallback-url et échoue au chargement, utiliser l’URL de repli (par catégorie)
        function applyFallback(img) {
            var fallback = img.getAttribute('data-fallback-url');
            if (fallback && img.src !== fallback) {
                img.removeAttribute('data-fallback-url');
                img.src = fallback;
            }
        }
        function onImageError(e) {
            var img = e.target;
            if (img.tagName !== 'IMG') return;
            applyFallback(img);
        }
        function attachFallbackToImages() {
            document.querySelectorAll('img[data-fallback-url]').forEach(function(img) {
                if (img.dataset.fallbackAttached) return;
                img.dataset.fallbackAttached = '1';
                if (img.complete && img.naturalWidth === 0) {
                    applyFallback(img);
                }
                img.addEventListener('error', onImageError);
            });
        }
        function repairBrokenFallbacks() {
            document.querySelectorAll('img[data-fallback-url]').forEach(function(img) {
                if (img.naturalWidth === 0) applyFallback(img);
            });
        }
        attachFallbackToImages();
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attachFallbackToImages);
        }
        setTimeout(repairBrokenFallbacks, 1500);
        setTimeout(repairBrokenFallbacks, 4000);
        window.addEventListener('load', repairBrokenFallbacks);
        document.body.addEventListener('error', onImageError, true);
    })();
    </script>
</body>
</html>
