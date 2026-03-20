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
<html lang="<?= htmlspecialchars($content_locale) ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title_safe ?></title>
    <meta name="description" content="<?= $meta_description_safe ?>">
    <?php if (!empty($canonical_url)): ?>
    <link rel="canonical" href="<?= htmlspecialchars($canonical_url) ?>">
    <?php endif; ?>
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
        /* Espace FIXE 18px entre bordure carte et panel glass — toujours en haut, bas, gauche, droite */
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
        /* Tag pill : effet glass sans padding supplémentaire */
        .vivat-glass-tag {
            background: rgba(190, 190, 190, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(230, 230, 230, 0.2);
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased font-sans">

    <header class="bg-white">
        <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 relative z-50">
            <div class="flex items-center h-[72px] md:h-[88px] py-[18px] md:py-[35px]">

                <!-- Logo -->
                <h1 class="font-righteous text-[32px] font-normal flex-shrink-0 text-[#004241] tracking-[0.03em]">
                    <a href="/" class="text-inherit no-underline">Vivat</a>
                </h1>

                <div class="hidden md:block flex-shrink-0 flex-1 min-w-4"></div>
                <div class="flex-1 md:flex-none md:flex-shrink-0"></div>

                <!-- Barre de recherche -->
                <form action="/search" method="get" class="hidden md:flex items-center flex-shrink-0 rounded-full h-12 overflow-hidden bg-[#E5EDEB] px-3 w-52">
                    <input type="search" name="q" value="<?= htmlspecialchars(request()->get('q', '')) ?>" placeholder="Rechercher…" class="flex-1 min-w-0 bg-transparent text-sm outline-none border-none text-[#226E65] placeholder:text-[#226E65]">
                    <button type="submit" class="flex items-center justify-center flex-shrink-0 p-2 text-[#226E65]" aria-label="Rechercher">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                </form>

                <div class="w-[9px] flex-shrink-0"></div>

                <!-- Bouton Contact -->
                <a href="/contact" class="hidden md:flex items-center justify-center rounded-full flex-shrink-0 h-12 font-medium text-base leading-none w-[162px] py-3 px-5 bg-[#004241] text-white">
                    Contactez-nous
                </a>

                <div class="hidden md:block w-[19px] flex-shrink-0"></div>

                <!-- Hamburger -->
                <button type="button" id="hamburger-menu" class="flex flex-col items-center justify-center gap-1.5 flex-shrink-0 w-12 h-12 bg-transparent border-none cursor-pointer rounded-[30px]" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobile-menu-panel">
                    <span class="block rounded-full w-7 h-[3px] bg-[#004241]"></span>
                    <span class="block rounded-full w-7 h-[3px] bg-[#004241]"></span>
                    <span class="block rounded-full w-7 h-[3px] bg-[#004241]"></span>
                </button>
            </div>

            <!-- Panneau menu -->
            <div id="mobile-menu-panel"
                 class="hidden absolute top-full left-0 right-0 md:left-6 md:right-6 lg:left-auto lg:right-20 lg:w-[min(100%,715px)] rounded-[30px] p-6 tablet:p-8 bg-[#004241] backdrop-blur-[18px] border border-[rgba(230,230,230,0.18)] shadow-[0_10px_40px_rgba(0,66,65,0.15)] overflow-hidden z-50"
                 role="dialog" aria-label="Menu de navigation">

                <?php if (auth()->check()): ?>
                <a href="<?= auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : url('/') ?>" class="block rounded-2xl bg-white/15 border border-white/20 p-4 mb-4">
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
                    <button type="submit" class="w-full py-2 px-3 rounded-2xl text-white/80 text-sm text-left">Se déconnecter</button>
                </form>
                <?php endif; ?>

                <nav class="flex flex-col gap-1" aria-label="Navigation principale">
                    <a href="/" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline">Home</a>
                    <a href="/a-propos" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline">À propos</a>
                    <a href="/contact" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline">Contact</a>
                    <a href="/faq" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline">FAQ</a>
                </nav>

                <p class="font-semibold text-white text-base mt-5 mb-2 pt-4 border-t border-white/20">Rubriques</p>
                <nav class="grid grid-cols-3 md:grid-cols-2 lg:grid-cols-3 gap-x-3 gap-y-1" aria-label="Rubriques">
                    <?php foreach ($categories as $cat): ?>
                    <a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="py-3 px-3 rounded-2xl text-white font-medium text-base no-underline"><?= htmlspecialchars($cat['name']) ?></a>
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
                <span class="inline-flex items-center justify-center h-12 px-6 rounded-full font-medium whitespace-nowrap bg-[#FFEFD1] text-[#004241] text-base">
                    <?= auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? 'Accéder au bureau' : 'Rédigez un article' ?>
                </span>
            </span>
        </a>
    </section>
    <?php endif; ?>

    <footer>
        <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 mb-6 w-full">
            <div class="rounded-[34px] bg-[#E7EFEC] p-6 md:p-8 shadow-[0_24px_64px_rgba(0,66,65,0.08)]">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                    <!-- Newsletter -->
                    <div class="flex flex-col justify-center rounded-[30px] bg-[#004241] p-6 md:p-8 text-white lg:col-span-7 gap-5">
                        <span class="inline-flex w-fit items-center justify-center rounded-full bg-white/[0.18] px-4 py-2 text-sm font-medium text-white backdrop-blur-[10px] border border-white/[0.14]">Newsletter</span>
                        <div class="flex flex-col gap-[10px]">
                            <h2 class="max-w-[13ch] font-medium text-white text-[clamp(28px,4vw,46px)] leading-[0.98]">Les articles à ne pas rater, directement dans votre boîte mail.</h2>
                            <p class="max-w-[44ch] text-white/[0.78] text-[17px] leading-[1.4]">Une sélection simple, claire, et utile pour suivre Vivat sans chercher partout.</p>
                        </div>
                        <form action="#" method="post" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px]">
                            <input type="email" name="email" placeholder="you@example.com" class="h-12 rounded-full border-0 bg-white pl-5 pr-5 text-base text-gray-900 outline-none focus:ring-2 focus:ring-white/30">
                            <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-[#FFF0D4] px-8 font-semibold text-[#004241]">
                                S'abonner
                            </button>
                        </form>
                    </div>

                    <!-- Liens -->
                    <div class="flex flex-col rounded-[30px] bg-white p-6 md:p-8 lg:col-span-5 gap-[18px]">
                        <div class="grid grid-cols-2 gap-x-8 gap-y-6">
                            <nav class="flex flex-col gap-[14px]" aria-label="Découvrir">
                                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Découvrir</span>
                                <ul class="m-0 flex list-none flex-col gap-3 p-0">
                                    <li><a href="/" class="text-base text-[#004241]/85 no-underline">Accueil</a></li>
                                    <li><a href="/a-propos" class="text-base text-[#004241]/85 no-underline">À propos</a></li>
                                    <li><a href="/contact" class="text-base text-[#004241]/85 no-underline">Contact</a></li>
                                    <li><a href="/faq" class="text-base text-[#004241]/85 no-underline">FAQ</a></li>
                                </ul>
                            </nav>
                            <nav class="flex flex-col gap-[14px]" aria-label="Légal">
                                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Légal</span>
                                <ul class="m-0 flex list-none flex-col gap-3 p-0">
                                    <li><a href="/mentions-legales" class="text-base text-[#004241]/85 no-underline">Mentions légales</a></li>
                                    <li><a href="/politique-confidentialite" class="text-base text-[#004241]/85 no-underline">Confidentialité</a></li>
                                    <li><a href="/politique-cookies" class="text-base text-[#004241]/85 no-underline">Cookies</a></li>
                                </ul>
                            </nav>
                            <nav class="col-span-2 flex flex-col gap-[14px]" aria-label="Rubriques">
                                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Rubriques</span>
                                <ul class="m-0 grid list-none grid-cols-2 gap-x-6 gap-y-3 p-0">
                                    <?php foreach ($categories as $cat): ?>
                                    <li><a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="text-base text-[#004241]/85 no-underline"><?= htmlspecialchars($cat['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t border-[#004241]/10 pt-5 text-sm text-[#004241]/60 md:flex-row md:items-center md:justify-between">
                    <p class="m-0">© <?= date('Y') ?> Vivat. Tous droits réservés.</p>
                    <a href="/contact" class="text-sm text-[#004241]/70 no-underline">Une question ? Contactez-nous</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
    (function() {
        // Menu hamburger : toggle visibilité du panneau
        function initHamburgerMenu() {
            var btn = document.getElementById('hamburger-menu');
            var panel = document.getElementById('mobile-menu-panel');
            if (!btn || !panel) return;
            btn.addEventListener('click', function() {
                panel.classList.toggle('hidden');
                var isOpen = !panel.classList.contains('hidden');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                btn.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHamburgerMenu);
        } else {
            initHamburgerMenu();
        }

        // Fallback image : utiliser l'URL de repli si l'image principale échoue
        function applyFallback(img) {
            var fallback = img.getAttribute('data-fallback-url');
            if (fallback && img.src !== fallback) {
                img.removeAttribute('data-fallback-url');
                img.src = fallback;
            }
        }
        function onImageError(e) {
            if (e.target.tagName === 'IMG') applyFallback(e.target);
        }
        function attachFallbackToImages() {
            document.querySelectorAll('img[data-fallback-url]').forEach(function(img) {
                if (img.dataset.fallbackAttached) return;
                img.dataset.fallbackAttached = '1';
                if (img.complete && img.naturalWidth === 0) applyFallback(img);
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
 