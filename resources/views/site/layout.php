<?php
$title = $title ?? 'Vivat';
$meta_description = $meta_description ?? 'Vivat — Actualités et articles. Découvrez nos rubriques et derniers articles.';
$canonical_url = $canonical_url ?? null;
$og_image = $og_image ?? null;
$og_article = $og_article ?? false;
$meta_description_safe = htmlspecialchars($meta_description);
$title_safe = htmlspecialchars($title);
?>
<!DOCTYPE html>
<html lang="fr">
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
    <meta property="og:locale" content="fr_FR">
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
        /* Glass effect matte, sans contour */
        .vivat-glass {
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: none;
        }
        body { font-family: 'Figtree', sans-serif; }
        .font-righteous { font-family: 'Righteous', cursive; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <!-- Navbar - Design System Figma -->
    <header class="bg-white border-b border-gray-100">
        <div class="max-w-[1400px] mx-auto px-5 lg:px-20 flex items-center h-[88px]" style="padding-top: 35px; padding-bottom: 35px;">
            <!-- Logo: 32px, #004241, Righteous 400, letter-spacing 3%, 612px space avant searchbar -->
            <h1 class="font-righteous text-[32px] font-normal flex-shrink-0" style="color: #004241; letter-spacing: 0.03em;"><a href="/" class="text-inherit no-underline hover:opacity-90">Vivat</a></h1>

            <!-- Espace logo - searchbar: 612px sur desktop -->
            <div class="hidden lg:block flex-shrink-0" style="width: 612px;"></div>
            <div class="flex-1 lg:hidden"></div>

            <!-- Search bar: 326x48, #EBF1EF, full radius -->
            <div class="flex items-center flex-shrink-0 rounded-full border border-gray-200 h-12 px-4 gap-2" style="width: 326px; min-width: 120px; background: #EBF1EF;">
                <input type="search" placeholder="Rechercher un article" class="flex-1 bg-transparent text-sm outline-none placeholder:opacity-80" style="color: #004241;">
                <svg class="w-5 h-5 flex-shrink-0" style="color: #004241;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
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
    <main class="max-w-[1400px] mx-auto px-5 lg:px-20 pb-8 overflow-x-hidden" style="padding-top: 24px;">
        <?= $content ?? '' ?>
    </main>
    <footer class="border-t border-gray-200 mt-12 py-8">
        <div class="max-w-[1400px] mx-auto px-5 lg:px-20 text-center text-gray-500 text-sm">
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
    })();
    </script>
</body>
</html>
