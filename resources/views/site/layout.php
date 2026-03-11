<?php
$content_locale = $content_locale ?? content_locale();
$title = $title ?? 'Vivat';
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
                    screens: { 'tablet': '834px' }
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
        }
        html {
            scroll-behavior: smooth;
        }
        /* Glass effect matte : padding intérieur 24px fixe (ne jamais modifier en responsive) */
        .vivat-glass {
            background: rgba(190, 190, 190, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(1px);
            border: 1px solid rgba(230, 230, 230, 0.2);
            padding: 24px;
            box-sizing: border-box;
        }
        body { font-family: 'Figtree', sans-serif; }
        .font-righteous { font-family: 'Righteous', cursive; }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">
    <!-- Navbar - Design System Figma -->
    <header class="bg-white">
        <div class="max-w-[1400px] mx-auto px-5 tablet:px-10 lg:px-20 flex items-center h-[88px]" style="padding-top: 35px; padding-bottom: 35px;">
            <!-- Logo: 32px, #004241 ; tablet: margin 40px -->
            <h1 class="font-righteous text-[32px] font-normal flex-shrink-0" style="color: #004241; letter-spacing: 0.03em;"><a href="/" class="text-inherit no-underline hover:opacity-90">Vivat</a></h1>

            <!-- Espace logo - searchbar: visible tablet+ -->
            <div class="hidden tablet:block flex-shrink-0 flex-1 min-w-4"></div>
            <div class="flex-1 tablet:flex-none tablet:flex-shrink-0"></div>

            <!-- Search: mobile & tablet = pastille ronde, desktop = barre complète -->
            <div class="flex items-center justify-center lg:justify-start flex-shrink-0 rounded-full border border-gray-200 h-9 w-9 tablet:h-9 tablet:w-9 lg:h-12 lg:w-[326px] lg:px-4" style="background: #EBF1EF;">
                <input type="search" placeholder="Rechercher un article" class="hidden lg:block flex-1 min-w-0 bg-transparent text-sm outline-none placeholder:opacity-80 mr-2" style="color: #004241;">
                <svg class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" style="color: #004241;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- 9px espace -->
            <div class="w-[9px] flex-shrink-0"></div>

            <!-- Contactez-nous: 162x48, #004241, Figtree 500 16px, text #FFFFFF -->
            <a href="/contact" class="flex items-center justify-center rounded-full flex-shrink-0 h-12 font-medium text-base leading-none" style="width: 162px; background: #004241; color: #FFFFFF; padding: 12px 20px;">
                Contactez-nous
            </a>

            <!-- 19px espace -->
            <div class="w-[19px] flex-shrink-0"></div>

            <!-- Hamburger: 48x48, radius 30px - 3 barres pill-shaped #004241, pas de background -->
            <button type="button" id="hamburger-menu" class="flex flex-col items-center justify-center gap-1.5 rounded-full flex-shrink-0 w-12 h-12 bg-transparent" style="border-radius: 30px;" aria-label="Menu">
                <span class="block rounded-full" style="width: 28px; height: 3px; background: #004241;"></span>
                <span class="block rounded-full" style="width: 28px; height: 3px; background: #004241;"></span>
                <span class="block rounded-full" style="width: 28px; height: 3px; background: #004241;"></span>
            </button>
        </div>
    </header>
    <main class="max-w-[1400px] mx-auto px-5 tablet:px-10 lg:px-20 pb-8 overflow-x-hidden">
        <?= $content ?? '' ?>
    </main>
    <!-- CTA contribution : juste au-dessus du footer, 24px de marge avant le footer -->
    <section class="max-w-[1400px] mx-auto px-5 tablet:px-10 lg:px-20 mt-12 mb-6" aria-label="Contribuer à Vivat">
        <a href="<?= url('/contribution') ?>" class="block rounded-[30px] overflow-hidden relative min-h-[340px] lg:min-h-[380px] bg-cover bg-center focus:outline-none focus:ring-2 focus:ring-[#004241] focus:ring-offset-2" style="background-image: url('https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1200&q=80');">
            <span class="absolute inset-0 bg-black/40" aria-hidden="true"></span>
            <span class="absolute inset-0 flex flex-col items-center justify-center gap-6 px-6 py-12 text-center">
                <span class="text-white font-semibold text-xl lg:text-2xl leading-tight">
                    Une idée, une histoire, un point de vue ?<br>
                    Vivat est ouvert aux nouvelles voix
                </span>
                <span class="inline-flex items-center justify-center h-12 px-6 rounded-full font-medium whitespace-nowrap transition hover:opacity-90" style="background: #FFEFD1; color: #004241; font-size: 16px;">
                    Rédigez un article
                </span>
            </span>
        </a>
    </section>
    <footer>
        <!-- 2 carrés avec 24px d'espace entre eux -->
        <div class="max-w-[1400px] mx-auto px-5 tablet:px-10 lg:px-20 mb-6 flex flex-col lg:flex-row gap-6 w-full">
            <!-- Carré 1 : Newsletter (2/5 de l'espace) -->
            <div class="flex-[2] min-w-0 flex flex-col justify-center items-center gap-5 rounded-[30px] p-8 shadow-sm text-center min-h-[200px]" style="background: #EBF1EF;">
                <div class="flex flex-col gap-1">
                    <span class="font-semibold" style="color: #004241; font-size: 16px;">Newsletter</span>
                    <p class="text-gray-900 font-normal leading-snug" style="font-size: 16px;">Recevez une sélection d'articles chaque semaine.</p>
                </div>
                <form action="#" method="post" class="flex flex-col sm:flex-row gap-3 flex-wrap justify-center w-full max-w-md">
                    <input type="email" name="email" placeholder="you@example.com" class="flex-1 min-w-[220px] h-12 pl-5 pr-5 rounded-full border-0 bg-white text-gray-900 placeholder:text-gray-400 shadow-sm outline-none transition focus:ring-2 focus:ring-[#004241]/25 focus:shadow-md text-left" style="font-size: 16px;">
                    <button type="submit" class="flex items-center justify-center h-12 px-8 rounded-full font-semibold text-white whitespace-nowrap shadow-sm transition hover:shadow-md hover:brightness-105 active:scale-[0.98]" style="background: var(--vivat-teal, #004241); font-size: 16px;">
                        S'abonner
                    </button>
                </form>
            </div>
            <!-- Carré 2 : Informations, Rubriques, Légal (3/5, 48px vertical, centré et équilibré) -->
            <div class="flex-[3] min-w-0 flex flex-wrap justify-center items-start gap-12 rounded-[30px] p-10" style="background: #EBF1EF;">
                <nav class="flex flex-col gap-2 text-left min-w-[140px]" aria-label="Informations">
                    <h3 class="font-semibold text-gray-900 text-base">Informations</h3>
                    <ul class="flex flex-col list-none p-0 m-0 gap-2">
                        <li><a href="/" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Home</a></li>
                        <li><a href="/a-propos" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">À propos</a></li>
                        <li><a href="/contact" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Contact</a></li>
                        <li><a href="/faq" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">FAQ</a></li>
                    </ul>
                </nav>
                <nav class="flex flex-col gap-2 text-left min-w-[140px]" aria-label="Rubriques">
                    <h3 class="font-semibold text-gray-900 text-base">Rubriques</h3>
                    <ul class="flex flex-col list-none p-0 m-0 gap-2">
                        <li><a href="/categories" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Actualités</a></li>
                        <li><a href="/categories" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Durabilités</a></li>
                        <li><a href="/categories" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Economie</a></li>
                        <li><a href="/categories" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Ecologie</a></li>
                        <li><a href="/categories" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Lifestyle</a></li>
                    </ul>
                </nav>
                <nav class="flex flex-col gap-2 text-left min-w-[200px]" aria-label="Légal">
                    <h3 class="font-semibold text-gray-900 text-base">Légal</h3>
                    <ul class="flex flex-col list-none p-0 m-0 gap-2">
                        <li><a href="/mentions-legales" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Mentions légales</a></li>
                        <li><a href="/politique-confidentialite" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Politique de confidentialité</a></li>
                        <li><a href="/conditions-generales" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Conditions générales</a></li>
                        <li><a href="/politique-cookies" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Politique de cookies</a></li>
                        <li><a href="/accessibilite" class="text-gray-700 hover:text-[#004241] transition no-underline text-base">Accessibilité</a></li>
                    </ul>
                </nav>
            </div>
        </div>
        <div class="max-w-[1400px] mx-auto px-5 tablet:px-10 lg:px-20 pb-8 text-center text-gray-500 text-sm">
            © <?= date('Y') ?> Vivat. Tous droits réservés.
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
                    var items = group.querySelectorAll('.vivat-reveal');
                    var delay = 180;
                    items.forEach(function(el, i) {
                        el.style.transitionDelay = (i * delay) + 'ms';
                        el.classList.add('opacity-100', 'translate-y-0');
                    });
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0 });
            groups.forEach(function(g) { observer.observe(g); });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initScrollReveal);
        } else {
            initScrollReveal();
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
