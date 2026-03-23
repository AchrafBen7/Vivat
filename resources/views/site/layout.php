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
        /* Barre recherche header : pastille + loupe → s’étire au hover / focus / texte saisi */
        #header-search-form {
            display: none;
            position: relative;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            flex-shrink: 0;
            border-radius: 9999px;
            background: #e5edeb;
            overflow: hidden;
            gap: 0;
            padding: 0 0.125rem;
            box-shadow: none;
            transition:
                width 0.4s cubic-bezier(0.34, 1.2, 0.64, 1),
                box-shadow 0.35s ease,
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
            width: min(100%, 15rem);
            justify-content: flex-start;
            gap: 0.375rem;
            padding-left: 0.375rem;
            padding-right: 0.75rem;
            box-shadow: 0 4px 20px rgba(34, 110, 101, 0.14);
            overflow: visible;
        }
        /* Loupe à gauche à l’ouverture ; ordre Tab = ordre DOM (champ puis bouton) */
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
            color: #226e65;
            outline: none;
            transition: opacity 0.25s ease 0.06s, flex 0.4s cubic-bezier(0.34, 1.2, 0.64, 1);
        }
        #header-search-form input[name="q"]::placeholder {
            color: rgba(34, 110, 101, 0.65);
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
            color: #226e65;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            order: 1;
        }
        #header-search-form button[type="submit"]:hover {
            background: rgba(0, 66, 65, 0.06);
        }
        #header-search-form button[type="submit"]:active {
            transform: scale(0.94);
        }
        #header-search-suggestions {
            position: absolute;
            top: calc(100% - 1px);
            left: 0;
            right: 0;
            display: none;
            flex-direction: column;
            gap: 0.125rem;
            padding: 0.65rem 0.5rem 0.5rem;
            border-radius: 0 0 1.75rem 1.75rem;
            background: #e5edeb;
            border: 1px solid rgba(34, 110, 101, 0.08);
            border-top: none;
            box-shadow: 0 12px 28px rgba(34, 110, 101, 0.12);
            z-index: 70;
        }
        #header-search-suggestions.vivat-search-suggestions--open {
            display: flex;
        }
        #header-search-form.vivat-search-suggestions-host {
            border-radius: 1.75rem 1.75rem 0 0;
            box-shadow: 0 12px 28px rgba(34, 110, 101, 0.12);
            z-index: 71;
        }
        #header-search-form.vivat-search-suggestions-host:hover,
        #header-search-form.vivat-search-suggestions-host:focus-within,
        #header-search-form.vivat-search-suggestions-host.vivat-header-search--dirty {
            border-radius: 1.75rem 1.75rem 0 0;
            box-shadow: 0 12px 28px rgba(34, 110, 101, 0.12);
        }
        .header-search-suggestion {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0.875rem;
            border-radius: 1rem;
            color: #004241;
            text-decoration: none;
            transition: background-color 0.18s ease;
        }
        .header-search-suggestion:hover,
        .header-search-suggestion.is-active {
            background: rgba(255, 255, 255, 0.46);
        }
        .header-search-suggestion-thumb {
            width: 3rem;
            height: 3rem;
            flex-shrink: 0;
            border-radius: 1rem;
            overflow: hidden;
            background: #dfe9e6;
        }
        .header-search-suggestion-thumb img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .header-search-suggestion-copy {
            display: flex;
            min-width: 0;
            flex: 1 1 auto;
            flex-direction: column;
            gap: 0.125rem;
        }
        .header-search-suggestion-label {
            font-size: 0.92rem;
            line-height: 1.25rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .header-search-suggestion-meta {
            font-size: 0.75rem;
            line-height: 1rem;
            color: rgba(0, 66, 65, 0.66);
        }
        .header-search-suggestion-empty {
            padding: 0.875rem;
            border-radius: 1rem;
            font-size: 0.82rem;
            color: rgba(0, 66, 65, 0.62);
        }
        .header-search-view-all {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.25rem;
            padding: 0.9rem 1rem;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.46);
            color: #004241;
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.18s ease, transform 0.18s ease;
        }
        .header-search-view-all:hover,
        .header-search-view-all.is-active {
            background: rgba(255, 255, 255, 0.72);
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased font-sans">

    <header class="bg-white border-b border-[#004241]/8">
        <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 relative z-50">
            <div class="flex items-center gap-2 md:gap-3 lg:gap-4 h-[72px] md:h-[88px] py-[16px] md:py-[24px]">

                <!-- Logo -->
                <h1 class="font-righteous text-[32px] font-normal flex-shrink-0 text-[#004241] tracking-[0.03em]">
                    <a href="/" class="text-inherit no-underline">Vivat</a>
                </h1>

                <div class="hidden md:block flex-1 min-w-[16px]"></div>

                <!-- Barre de recherche (pastille → s’allonge au survol / focus) -->
                <form action="/search" method="get" id="header-search-form" class="<?= request()->filled('q') ? 'vivat-header-search--dirty' : '' ?>" role="search" aria-label="Recherche sur le site">
                    <input type="search" name="q" value="<?= htmlspecialchars(request()->get('q', '')) ?>" placeholder="Rechercher…" autocomplete="off" aria-label="Mot-clé ou catégorie" aria-expanded="false" aria-controls="header-search-suggestions" aria-autocomplete="list">
                    <button type="submit" aria-label="Lancer la recherche">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <div id="header-search-suggestions" role="listbox" aria-label="Suggestions de recherche"></div>
                </form>
                <script>
                (function () {
                    var form = document.getElementById('header-search-form');
                    var input = form && form.querySelector('input[name="q"]');
                    var suggestionBox = document.getElementById('header-search-suggestions');
                    var debounceTimer = null;
                    var activeIndex = -1;
                    var abortController = null;
                    var currentItems = [];

                    if (!form || !input || !suggestionBox) {
                        return;
                    }

                    function syncDirty() {
                        form.classList.toggle('vivat-header-search--dirty', input.value.trim() !== '');
                    }

                    function closeSuggestions() {
                        suggestionBox.innerHTML = '';
                        suggestionBox.classList.remove('vivat-search-suggestions--open');
                        form.classList.remove('vivat-search-suggestions-host');
                        input.setAttribute('aria-expanded', 'false');
                        activeIndex = -1;
                        currentItems = [];
                    }

                    function renderSuggestions(items, query) {
                        activeIndex = -1;
                        var searchUrl = '/search?q=' + encodeURIComponent(query);
                        currentItems = items.slice();

                        if (!items.length) {
                            suggestionBox.innerHTML = ''
                                + '<div class="header-search-suggestion-empty">Aucune suggestion pour "' + query.replace(/"/g, '&quot;') + '"</div>'
                                + '<a href="' + searchUrl + '" class="header-search-view-all" data-suggestion-index="0">Voir tous les articles</a>';
                            currentItems = [{
                                url: searchUrl
                            }];
                            suggestionBox.classList.add('vivat-search-suggestions--open');
                            form.classList.add('vivat-search-suggestions-host');
                            input.setAttribute('aria-expanded', 'true');
                            return;
                        }

                        suggestionBox.innerHTML = items.map(function(item, index) {
                            var thumb = item.thumbnail_url
                                ? '<span class="header-search-suggestion-thumb"><img src="' + item.thumbnail_url + '" alt="" loading="lazy"></span>'
                                : '';
                            return ''
                                + '<a href="' + item.url + '" class="header-search-suggestion" role="option" data-suggestion-index="' + index + '">'
                                + thumb
                                + '<span class="header-search-suggestion-copy">'
                                + '<span class="header-search-suggestion-label">' + item.label + '</span>'
                                + '<span class="header-search-suggestion-meta">' + item.meta + '</span>'
                                + '</span>'
                                + '</a>';
                        }).join('') + '<a href="' + searchUrl + '" class="header-search-view-all" data-suggestion-index="' + items.length + '">Voir tous les articles</a>';
                        currentItems.push({
                            url: searchUrl
                        });
                        suggestionBox.classList.add('vivat-search-suggestions--open');
                        form.classList.add('vivat-search-suggestions-host');
                        input.setAttribute('aria-expanded', 'true');
                    }

                    function setActiveSuggestion(nextIndex) {
                        var links = suggestionBox.querySelectorAll('.header-search-suggestion, .header-search-view-all');

                        if (!links.length) {
                            activeIndex = -1;
                            return;
                        }

                        activeIndex = nextIndex;
                        links.forEach(function(link, index) {
                            link.classList.toggle('is-active', index === activeIndex);
                        });
                    }

                    function fetchSuggestions() {
                        var query = input.value.trim();

                        if (query.length < 2) {
                            closeSuggestions();
                            return;
                        }

                        if (abortController) {
                            abortController.abort();
                        }

                        abortController = new AbortController();

                        fetch('/search/suggestions?q=' + encodeURIComponent(query), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            signal: abortController.signal
                        })
                            .then(function(response) {
                                if (!response.ok) {
                                    throw new Error('Suggestion request failed');
                                }

                                return response.json();
                            })
                            .then(function(payload) {
                                if (input.value.trim() !== query) {
                                    return;
                                }

                                renderSuggestions(Array.isArray(payload.suggestions) ? payload.suggestions : [], query);
                            })
                            .catch(function(error) {
                                if (error.name !== 'AbortError') {
                                    closeSuggestions();
                                }
                            });
                    }

                    input.addEventListener('input', syncDirty);
                    input.addEventListener('change', syncDirty);
                    input.addEventListener('input', function() {
                        window.clearTimeout(debounceTimer);
                        debounceTimer = window.setTimeout(fetchSuggestions, 220);
                    });
                    input.addEventListener('focus', function() {
                        if (input.value.trim().length >= 2 && suggestionBox.innerHTML.trim() !== '') {
                            suggestionBox.classList.add('vivat-search-suggestions--open');
                            input.setAttribute('aria-expanded', 'true');
                        }
                    });
                    input.addEventListener('keydown', function(event) {
                        if (!currentItems.length) {
                            return;
                        }

                        if (event.key === 'ArrowDown') {
                            event.preventDefault();
                            setActiveSuggestion(activeIndex < currentItems.length - 1 ? activeIndex + 1 : 0);
                            return;
                        }

                        if (event.key === 'ArrowUp') {
                            event.preventDefault();
                            setActiveSuggestion(activeIndex > 0 ? activeIndex - 1 : currentItems.length - 1);
                            return;
                        }

                        if (event.key === 'Enter' && activeIndex >= 0 && currentItems[activeIndex]) {
                            event.preventDefault();
                            window.location.href = currentItems[activeIndex].url;
                            return;
                        }

                        if (event.key === 'Escape') {
                            closeSuggestions();
                        }
                    });
                    document.addEventListener('click', function(event) {
                        if (!form.contains(event.target)) {
                            closeSuggestions();
                        }
                    });
                    suggestionBox.addEventListener('mousedown', function(event) {
                        var link = event.target.closest('.header-search-suggestion');
                        if (!link) {
                            return;
                        }
                        event.preventDefault();
                        window.location.href = link.getAttribute('href');
                    });
                    syncDirty();
                })();
                </script>

                <!-- Bouton Contact -->
                <a href="/contact" class="hidden md:flex items-center justify-center rounded-full flex-shrink-0 h-12 font-medium text-base leading-none min-w-[164px] px-5 bg-[#004241] text-white">
                    Contactez-nous
                </a>

                <!-- Hamburger : épaisseur type stroke 1 (1px) ; croix = même centre de rotation -->
                <button type="button" id="hamburger-menu" class="group relative flex h-12 w-12 shrink-0 cursor-pointer items-center justify-center rounded-[30px] border-none bg-transparent" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobile-menu-panel">
                    <span class="relative block h-[15px] w-7 shrink-0" aria-hidden="true">
                        <span class="absolute left-0 top-0 h-px w-full origin-center rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:top-1/2 group-[aria-expanded=true]:-translate-y-1/2 group-[aria-expanded=true]:rotate-45"></span>
                        <span class="absolute left-0 top-1/2 h-px w-full -translate-y-1/2 rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:opacity-0 group-[aria-expanded=true]:scale-x-0"></span>
                        <span class="absolute left-0 top-[14px] h-px w-full origin-center rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:top-1/2 group-[aria-expanded=true]:-translate-y-1/2 group-[aria-expanded=true]:-rotate-45"></span>
                    </span>
                </button>
            </div>

            <!-- Panneau menu (data-open : JS) -->
            <div id="mobile-menu-panel"
                 data-open="false"
                 class="absolute top-full left-0 right-0 z-50 mt-2 origin-top rounded-[30px] border border-[rgba(230,230,230,0.18)] bg-[#004241] p-6 shadow-[0_10px_40px_rgba(0,66,65,0.15)] backdrop-blur-[18px] transition-[clip-path,opacity,max-height,visibility] duration-[650ms] ease-[cubic-bezier(0.22,1,0.36,1)] tablet:p-8 md:left-6 md:right-6 lg:left-auto lg:right-20 lg:mt-2 lg:w-[min(100%,715px)] data-[open=false]:pointer-events-none data-[open=false]:invisible data-[open=false]:max-h-0 data-[open=false]:overflow-hidden data-[open=false]:opacity-0 data-[open=false]:[clip-path:inset(0_0_100%_0)] data-[open=true]:pointer-events-auto data-[open=true]:visible data-[open=true]:max-h-[min(85vh,900px)] data-[open=true]:overflow-y-auto data-[open=true]:overflow-x-hidden data-[open=true]:opacity-100 data-[open=true]:[clip-path:inset(0_0_0_0)]"
                 role="dialog" aria-label="Menu de navigation" aria-modal="true">

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
                        <svg class="w-5 h-5 text-white/70 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                <form action="<?= url('/logout') ?>" method="post" class="mb-4">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full py-2 px-3 rounded-2xl text-white/80 text-sm text-left">Se déconnecter</button>
                </form>
                <?php endif; ?>

                <nav class="flex flex-col gap-2" aria-label="Navigation principale">
                    <a href="/" class="py-3.5 px-4 rounded-2xl text-white font-medium text-base no-underline">Home</a>
                    <a href="/a-propos" class="py-3.5 px-4 rounded-2xl text-white font-medium text-base no-underline">À propos</a>
                    <a href="/contact" class="py-3.5 px-4 rounded-2xl text-white font-medium text-base no-underline">Contact</a>
                    <a href="/faq" class="py-3.5 px-4 rounded-2xl text-white font-medium text-base no-underline">FAQ</a>
                </nav>

                <p class="font-semibold text-white text-base mt-6 mb-3 pt-5 border-t border-white/20">Rubriques</p>
                <nav class="grid grid-cols-3 md:grid-cols-2 lg:grid-cols-3 gap-x-3 gap-y-2" aria-label="Rubriques">
                    <?php foreach ($categories as $cat): ?>
                    <a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="py-3 px-4 rounded-2xl text-white font-medium text-base no-underline"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="max-w-[1400px] mx-auto mt-6 px-[18px] md:px-8 lg:px-10 xl:px-20 pb-8 overflow-x-hidden">
        <?php if (session('success')): ?>
        <div class="mb-6 rounded-[20px] bg-[#004241] text-white px-6 py-4 flex items-center gap-3" role="alert">
            <svg class="w-6 h-6 flex-shrink-0 text-[#7DD3C1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-medium"><?= htmlspecialchars(session('success')) ?></p>
        </div>
        <?php endif; ?>
        <?= $content ?? '' ?>
    </main>

    <?php if (empty($hide_cta_section)): ?>
    <section class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 mt-12 mb-6" aria-label="Contribuer à Vivat">
        <a href="<?= htmlspecialchars(auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register')) ?>" class="block rounded-[30px] overflow-hidden relative min-h-[340px] lg:min-h-[380px] bg-cover bg-center focus:outline-none focus:ring-2 focus:ring-[#004241] focus:ring-offset-2 bg-[url('https://images.pexels.com/photos/34950/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1200&h=380&fit=crop')]">
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
            if (!btn || !panel) {
                return;
            }
            function setOpen(isOpen) {
                panel.setAttribute('data-open', isOpen ? 'true' : 'false');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                btn.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
            }
            btn.addEventListener('click', function () {
                setOpen(panel.getAttribute('data-open') !== 'true');
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && panel.getAttribute('data-open') === 'true') {
                    setOpen(false);
                }
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
 
