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
$sessionErrors = session()->get('errors');
$sessionErrorMessages = $sessionErrors ? $sessionErrors->getBag('default')->getMessages() : [];
$newsletterEmailError = $sessionErrorMessages['newsletter_email'][0] ?? null;
$newsletterOldEmail = old('newsletter_email', '');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($content_locale) ?>" class="scroll-smooth">
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
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $title_safe ?>">
    <meta name="twitter:description" content="<?= $meta_description_safe ?>">
    <?php if (! empty($og_image)) { ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($og_image) ?>">
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
        /* Menu mobile ouvert : fond header retiré pour que le voile noir soit uniforme (pas de bandeau clair au-dessus) */
        header.header-menu-open {
            background: transparent !important;
            box-shadow: none !important;
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
        /* Croix d’effacement custom (remplace le bouton natif type=search) */
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

    <header id="site-header" class="relative z-50 isolate bg-gradient-to-b from-white from-0% via-white via-[40%] to-[#EBF1EF]/32 to-100% shadow-[0_6px_20px_-4px_rgba(0,66,65,0.075),0_14px_36px_-18px_rgba(0,66,65,0.05)]">
        <!-- Voile noir uniforme (sous la barre et le panneau, z-40) : pas de dégradé clair→foncé sur la page -->
        <div id="mobile-nav-overlay"
             data-open="false"
             class="fixed inset-0 z-[40] bg-black/45 opacity-0 pointer-events-none transition-opacity duration-300 ease-out data-[open=true]:opacity-100 data-[open=true]:pointer-events-auto"
             aria-hidden="true"></div>
        <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 relative z-50">
            <div class="flex items-center gap-2 md:gap-3 h-[72px] md:h-[88px] py-[16px] md:py-[24px]">

                <!-- Logo -->
                <h1 class="font-righteous text-[32px] font-normal flex-shrink-0 text-[#004241] tracking-[0.03em]">
                    <a href="/" class="text-inherit no-underline">Vivat</a>
                </h1>

                <div class="hidden md:block flex-1 min-w-[16px]"></div>

                <!-- Barre de recherche (pastille → s’allonge au survol / focus) -->
                <form action="/search" method="get" id="header-search-form" class="<?= request()->filled('q') ? 'vivat-header-search--dirty' : '' ?>" role="search" aria-label="Recherche sur le site">
                    <input type="text" name="q" value="<?= htmlspecialchars(request()->get('q', '')) ?>" placeholder="Rechercher…" autocomplete="off" inputmode="search" enterkeyhint="search" aria-label="Mot-clé ou catégorie" aria-expanded="false" aria-controls="header-search-suggestions" aria-autocomplete="list">
                    <button type="submit" aria-label="Lancer la recherche">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <button type="button" id="header-search-clear" aria-label="Effacer la recherche">
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <div id="header-search-suggestions" role="listbox" aria-label="Suggestions de recherche"></div>
                </form>
                <script>
                (function () {
                    var form = document.getElementById('header-search-form');
                    var input = form && form.querySelector('input[name="q"]');
                    var clearBtn = document.getElementById('header-search-clear');
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
                                + '<div class="rounded-[1.25rem] px-4 py-4 text-[0.95rem] leading-[1.4rem] text-[#004241]/65">Aucune suggestion pour "' + query.replace(/"/g, '&quot;') + '"</div>'
                                + '<a href="' + searchUrl + '" class="header-search-view-all mt-1.5 flex items-center justify-center rounded-[1.25rem] bg-white/50 px-5 py-[1.1rem] text-center text-[0.95rem] font-semibold text-[#004241] no-underline transition-colors duration-200 hover:bg-white/75" data-suggestion-index="0">Voir tous les articles</a>';
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
                                ? '<span class="h-[4.25rem] w-[4.25rem] shrink-0 overflow-hidden rounded-2xl bg-[#E8F0ED]"><img src="' + item.thumbnail_url + '" alt="" loading="lazy" class="block h-full w-full object-cover"></span>'
                                : '';
                            return ''
                                + '<a href="' + item.url + '" class="header-search-suggestion flex items-start gap-4 rounded-[1.25rem] px-4 py-4 text-[#004241] no-underline transition-colors duration-200 hover:bg-white/60" role="option" data-suggestion-index="' + index + '">'
                                + thumb
                                + '<span class="flex min-w-0 flex-1 flex-col gap-1 pt-0.5">'
                                + '<span class="header-search-suggestion-label text-[1.1rem] font-semibold leading-[1.3rem]">' + item.label + '</span>'
                                + '<span class="text-[0.95rem] leading-[1.2rem] text-[#004241]/65">' + item.meta + '</span>'
                                + '</span>'
                                + '</a>';
                        }).join('') + '<a href="' + searchUrl + '" class="header-search-view-all mt-1.5 flex items-center justify-center rounded-[1.25rem] bg-white/50 px-5 py-[1.1rem] text-center text-[0.95rem] font-semibold text-[#004241] no-underline transition-colors duration-200 hover:bg-white/75" data-suggestion-index="' + items.length + '">Voir tous les articles</a>';
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
                    if (clearBtn) {
                        clearBtn.addEventListener('click', function (event) {
                            event.preventDefault();
                            input.value = '';
                            syncDirty();
                            closeSuggestions();
                            input.focus();
                        });
                    }
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

                <!-- Bouton Ecrire un article -->
                <a
                    href="<?= auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register') ?>"
                    class="hidden md:flex items-center justify-center rounded-full flex-shrink-0 h-12 font-medium text-base leading-none min-w-[164px] px-5 bg-[#004241] text-white transition-colors duration-200 hover:bg-[#003130] focus:outline-none focus:ring-2 focus:ring-[#004241] focus:ring-offset-2"
                >
                    Rédiger un article
                </a>

                <!-- Hamburger : 3 lignes avec plus d'espace ; croix = même centre de rotation -->
                <button type="button" id="hamburger-menu" class="group relative flex h-12 w-12 shrink-0 cursor-pointer items-center justify-center rounded-[30px] border-none bg-transparent" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobile-menu-panel">
                    <span class="relative block h-[20px] w-7 shrink-0" aria-hidden="true">
                        <span class="absolute left-0 top-0 h-[2px] w-full origin-center rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:top-1/2 group-[aria-expanded=true]:-translate-y-1/2 group-[aria-expanded=true]:rotate-45"></span>
                        <span class="absolute left-0 top-1/2 h-[2px] w-full -translate-y-1/2 rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:opacity-0 group-[aria-expanded=true]:scale-x-0"></span>
                        <span class="absolute left-0 top-[18px] h-[2px] w-full origin-center rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:top-1/2 group-[aria-expanded=true]:-translate-y-1/2 group-[aria-expanded=true]:-rotate-45"></span>
                    </span>
                </button>
            </div>

            <!-- Panneau menu -->
            <div id="mobile-menu-panel"
                 data-open="false"
                 class="absolute top-full left-0 right-0 z-50 mt-3 origin-top rounded-[34px] border border-white/10 bg-[linear-gradient(165deg,#004241_0%,#003836_52%,#002E2D_100%)] p-8 shadow-[0_20px_60px_rgba(0,40,38,0.35),0_0_0_1px_rgba(255,255,255,0.04)_inset] backdrop-blur-[24px] transition-[clip-path,opacity,max-height,visibility] duration-[650ms] ease-[cubic-bezier(0.22,1,0.36,1)] md:left-4 md:right-4 md:p-10 lg:left-auto lg:right-16 lg:mt-3 lg:w-[min(100%,780px)] lg:p-12 data-[open=false]:pointer-events-none data-[open=false]:invisible data-[open=false]:max-h-0 data-[open=false]:overflow-hidden data-[open=false]:opacity-0 data-[open=false]:[clip-path:inset(0_0_100%_0)] data-[open=true]:pointer-events-auto data-[open=true]:visible data-[open=true]:max-h-[min(88vh,960px)] data-[open=true]:overflow-y-auto data-[open=true]:overflow-x-hidden data-[open=true]:opacity-100 data-[open=true]:[clip-path:inset(0_0_0_0)]"
                 role="dialog" aria-label="Menu de navigation" aria-modal="true">

                <?php if (auth()->check()) { ?>
                <a href="<?= auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : url('/') ?>" class="mb-6 block rounded-[20px] border border-white/12 bg-white/8 p-5 transition-all duration-200 hover:border-white/25 hover:bg-white/15">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/15 text-base font-semibold text-white">
                            <?= strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-lg font-semibold text-white"><?= htmlspecialchars(auth()->user()->name ?? 'Mon compte') ?></p>
                            <p class="text-sm text-white/65"><?= auth()->user()->hasRole(['contributor', 'admin']) ? 'Espace rédacteur' : 'Mon profil' ?></p>
                        </div>
                        <svg class="h-5 w-5 flex-shrink-0 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
                <form action="<?= url('/logout') ?>" method="post" class="mb-6">
                    <?= csrf_field() ?>
                    <button type="submit" class="w-full rounded-[16px] px-4 py-2.5 text-left text-sm text-white/60 transition-colors duration-200 hover:bg-white/8 hover:text-white/90">Se déconnecter</button>
                </form>
                <?php } ?>

                <nav class="flex flex-col gap-1" aria-label="Navigation principale">
                    <a href="/" class="rounded-[16px] px-5 py-4 text-[18px] font-semibold text-white no-underline transition-colors duration-200 hover:bg-white/10 hover:text-[#FFF1B9]">Home</a>
                    <a href="/a-propos" class="rounded-[16px] px-5 py-4 text-[18px] font-semibold text-white no-underline transition-colors duration-200 hover:bg-white/10 hover:text-[#FFF1B9]">À propos</a>
                    <a
                        href="<?= auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register') ?>"
                        class="rounded-[16px] px-5 py-4 text-[18px] font-semibold text-white no-underline transition-colors duration-200 hover:bg-white/10 hover:text-[#FFF1B9]"
                    >
                        Rédiger un article
                    </a>
                    <a href="/faq" class="rounded-[16px] px-5 py-4 text-[18px] font-semibold text-white no-underline transition-colors duration-200 hover:bg-white/10 hover:text-[#FFF1B9]">FAQ</a>
                </nav>

                <p class="mb-4 mt-8 border-t border-white/10 pt-7 text-sm font-medium uppercase tracking-[0.12em] text-white/45">Rubriques</p>
                <nav class="grid grid-cols-2 gap-x-2 gap-y-1 sm:grid-cols-3 lg:grid-cols-3" aria-label="Rubriques">
                    <?php foreach ($categories as $cat) { ?>
                    <a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="rounded-[14px] px-4 py-3.5 text-[15px] font-medium text-white/85 no-underline transition-colors duration-200 hover:bg-white/10 hover:text-[#FFF1B9]"><?= htmlspecialchars($cat['name']) ?></a>
                    <?php } ?>
                </nav>
            </div>
        </div>
    </header>

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
    <section class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 mt-12 mb-6" aria-label="Contribuer à Vivat">
        <a
            href="<?= htmlspecialchars(auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register')) ?>"
            class="group block rounded-[30px] border border-[#D6E1DD] bg-[#EBF1EF] p-6 no-underline transition-colors duration-200 hover:bg-[#E3ECE9] focus:outline-none focus:ring-2 focus:ring-[#004241] focus:ring-offset-2 md:p-8"
        >
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between md:gap-8">
                <div class="min-w-0 max-w-[44rem]">
                    <span class="inline-flex items-center justify-center rounded-full bg-white px-4 py-2 text-sm font-medium text-[#004241]">
                        <?= auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? 'Espace rédacteur' : 'Contribuer' ?>
                    </span>
                    <h2 class="mt-4 text-[28px] font-semibold leading-[1.05] text-[#004241] md:text-[34px]">
                        <?php if (auth()->check() && auth()->user()->hasRole(['contributor', 'admin'])) { ?>
                        Accédez à votre espace rédacteur
                        <?php } else { ?>
                        Une idée, une histoire, un point de vue ?
                        <?php } ?>
                    </h2>
                    <p class="mt-3 max-w-[40rem] text-base leading-relaxed text-[#004241]/82 md:text-[17px]">
                        <?php if (auth()->check() && auth()->user()->hasRole(['contributor', 'admin'])) { ?>
                        Retrouvez vos brouillons, vos contenus en cours et le suivi de vos soumissions.
                        <?php } else { ?>
                        Vivat est ouvert aux nouvelles voix pour partager des contenus utiles, clairs et ancrés dans le quotidien.
                        <?php } ?>
                    </p>
                </div>
                <div class="flex w-full md:w-auto md:justify-end">
                    <span class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#004241] px-6 py-4 text-base font-semibold text-white transition-colors duration-200 group-hover:bg-[#003130] md:w-auto md:min-w-[240px]">
                        <?= auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? 'Accéder au bureau' : 'Rédigez un article' ?>
                        <svg class="h-5 w-5 flex-shrink-0 translate-x-0 transition-transform duration-300 ease-out group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>
            </div>
        </a>
    </section>
    <?php } ?>

    <?php if (empty($hide_footer)) { ?>
    <footer>
        <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 mb-6 w-full">
            <div class="rounded-[34px] bg-[#E7EFEC] p-6 md:p-8 shadow-[0_24px_64px_rgba(0,66,65,0.08)]">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                    <!-- Newsletter -->
                    <div class="flex flex-col justify-center rounded-[30px] bg-[#004241] p-6 md:p-8 text-white lg:col-span-7 gap-5">
                        <span class="inline-flex w-fit items-center justify-center rounded-full bg-white/[0.18] px-4 py-2 text-sm font-medium text-white backdrop-blur-[10px] border border-white/[0.14]">Newsletter</span>
                        <div class="flex flex-col gap-[10px]">
                            <h2 class="max-w-[13ch] font-medium text-white text-3xl sm:text-4xl md:text-2xl lg:text-5xl leading-[0.98]">Les articles à ne pas rater, directement dans votre boîte mail.</h2>
                            <p class="max-w-[44ch] text-white/[0.78] text-[17px] leading-[1.4]">Une sélection simple, claire, et utile pour suivre Vivat sans chercher partout.</p>
                        </div>
                        <form action="<?= htmlspecialchars(route('newsletter.subscribe.web')) ?>" method="post" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px]">
                            <?= csrf_field() ?>
                            <div class="flex flex-col gap-2">
                                <input
                                    type="email"
                                    name="newsletter_email"
                                    value="<?= htmlspecialchars($newsletterOldEmail) ?>"
                                    placeholder="you@example.com"
                                    class="h-12 rounded-full border-0 bg-white pl-5 pr-5 text-base text-gray-900 outline-none focus:ring-2 focus:ring-white/30 <?= $newsletterEmailError ? 'ring-2 ring-[#FFD2C9]' : '' ?>"
                                    required
                                >
                                <?php if ($newsletterEmailError) { ?>
                                <p class="pl-4 text-sm text-[#FFD2C9]"><?= htmlspecialchars($newsletterEmailError) ?></p>
                                <?php } ?>
                            </div>
                            <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-[#FFF0B6] px-8 font-semibold text-[#004241] transition-colors duration-200 hover:bg-[#FBE9A3]">
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
                                    <li><a href="/" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]">Accueil</a></li>
                                    <li><a href="/a-propos" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]">À propos</a></li>
                                    <li><a href="/contact" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]">Contact</a></li>
                                    <li><a href="/faq" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]">FAQ</a></li>
                                </ul>
                            </nav>
                            <nav class="flex flex-col gap-[14px]" aria-label="Légal">
                                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Légal</span>
                                <ul class="m-0 flex list-none flex-col gap-3 p-0">
                                    <li><a href="/mentions-legales" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]">Mentions légales</a></li>
                                    <li><a href="/politique-confidentialite" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]">Confidentialité</a></li>
                                    <li><a href="/politique-cookies" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]">Cookies</a></li>
                                </ul>
                            </nav>
                            <nav class="col-span-2 flex flex-col gap-[14px]" aria-label="Rubriques">
                                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Rubriques</span>
                                <ul class="m-0 grid list-none grid-cols-2 gap-x-6 gap-y-3 p-0">
                                    <?php foreach ($categories as $cat) { ?>
                                    <li><a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars($cat['name']) ?></a></li>
                                    <?php } ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3 border-t border-[#004241]/10 pt-5 text-sm text-[#004241]/60 md:flex-row md:items-center md:justify-between">
                    <p class="m-0">© <?= date('Y') ?> Vivat. Tous droits réservés.</p>
                    <a href="/contact" class="text-sm text-[#004241]/70 no-underline transition-colors duration-200 hover:text-[#004241]">Une question ? Contactez-nous</a>
                </div>
            </div>
        </div>
    </footer>
    <?php } ?>

    <script>
    (function() {
        // Menu hamburger : toggle visibilité du panneau
        function initHamburgerMenu() {
            var btn = document.getElementById('hamburger-menu');
            var panel = document.getElementById('mobile-menu-panel');
            var overlay = document.getElementById('mobile-nav-overlay');
            if (!btn || !panel) {
                return;
            }

            // Robustifie l'animation de la croix : si les variantes Tailwind basées sur aria-expanded
            // ne sont pas appliquées, on pilote directement les 3 barres en JS.
            function applyHamburgerCross(isOpen) {
                var lines = btn.querySelectorAll('span.absolute');
                if (!lines || lines.length < 3) {
                    return;
                }

                var top = lines[0];
                var middle = lines[1];
                var bottom = lines[2];

                if (isOpen) {
                    top.style.top = '50%';
                    top.style.transform = 'translateY(-50%) rotate(45deg)';
                    top.style.opacity = '1';

                    middle.style.opacity = '0';
                    middle.style.transform = 'translateY(-50%) scaleX(0)';

                    bottom.style.top = '50%';
                    bottom.style.transform = 'translateY(-50%) rotate(-45deg)';
                    bottom.style.opacity = '1';
                } else {
                    top.style.top = '';
                    top.style.transform = '';
                    top.style.opacity = '';

                    middle.style.opacity = '';
                    middle.style.transform = '';

                    bottom.style.top = '';
                    bottom.style.transform = '';
                    bottom.style.opacity = '';
                }
            }

            function setOpen(isOpen) {
                panel.setAttribute('data-open', isOpen ? 'true' : 'false');
                btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                btn.setAttribute('aria-label', isOpen ? 'Fermer le menu' : 'Ouvrir le menu');
                applyHamburgerCross(!!isOpen);
                var headerEl = btn.closest('header');
                if (headerEl) {
                    headerEl.classList.toggle('header-menu-open', isOpen);
                }
                if (overlay) {
                    overlay.setAttribute('data-open', isOpen ? 'true' : 'false');
                    overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                }
            }
            btn.addEventListener('click', function () {
                setOpen(panel.getAttribute('data-open') !== 'true');
            });
            if (overlay) {
                overlay.addEventListener('click', function () {
                    setOpen(false);
                });
            }
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
 
