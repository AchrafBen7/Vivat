<?php
$logoPath = public_path('logo_vivat.png');
$logoUrl = '/logo_vivat.png'.(file_exists($logoPath) ? '?v='.filemtime($logoPath) : '');
?>
<div id="mobile-nav-overlay"
     data-open="false"
     class="fixed inset-0 z-[45] bg-black/45 opacity-0 pointer-events-none transition-opacity duration-300 ease-out data-[open=true]:opacity-100 data-[open=true]:pointer-events-auto"
     aria-hidden="true"></div>

<header id="site-header" class="relative z-50 isolate bg-gradient-to-b from-white from-0% via-white via-[40%] to-[#EBF1EF]/32 to-100% shadow-[0_6px_20px_-4px_rgba(0,66,65,0.075),0_14px_36px_-18px_rgba(0,66,65,0.05)]">
    <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 relative z-50">
        <div class="flex items-center gap-2 md:gap-3 h-[72px] md:h-[88px] py-[16px] md:py-[24px]">
            <h1 class="flex-shrink-0">
                <a href="/" class="block no-underline">
                    <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Vivat" class="block w-[64px] md:w-[88px] h-auto" loading="eager">
                </a>
            </h1>

            <div class="flex-1 min-w-[16px]"></div>

            <form action="/search" method="get" id="header-search-form" class="<?= request()->filled('q') ? 'vivat-header-search--dirty' : '' ?>" role="search" aria-label="Recherche sur le site">
                <input type="text" name="q" value="<?= htmlspecialchars(request()->get('q', '')) ?>" placeholder="<?= htmlspecialchars(__('site.search_placeholder')) ?>" autocomplete="off" inputmode="search" enterkeyhint="search" aria-label="<?= htmlspecialchars(__('site.search_keyword_label')) ?>" aria-expanded="false" aria-controls="header-search-suggestions" aria-autocomplete="list">
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
                var emptySuggestionsLabel = <?= json_encode(__('site.search_no_suggestion')) ?>;
                var viewMoreArticlesLabel = <?= json_encode(__('site.search_view_more_articles')) ?>;
                var viewOtherArticlesLabel = <?= json_encode(__('site.search_view_other_articles')) ?>;
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

                function searchFooterLabel(itemCount) {
                    return itemCount >= 4 ? viewMoreArticlesLabel : viewOtherArticlesLabel;
                }

                function renderSuggestions(items, query) {
                    activeIndex = -1;
                    var searchUrl = '/search?q=' + encodeURIComponent(query);
                    var footerLabel = searchFooterLabel(items.length);
                    currentItems = items.slice();

                    if (!items.length) {
                        suggestionBox.innerHTML = ''
                            + '<div class="rounded-[1.25rem] px-4 py-4 text-[0.95rem] leading-[1.4rem] text-[#004241]/65">' + emptySuggestionsLabel.replace(':query', '"' + query.replace(/"/g, '&quot;') + '"') + '</div>'
                            + '<a href="' + searchUrl + '" class="header-search-view-all mt-1.5 flex items-center justify-center rounded-[1.25rem] bg-white/50 px-5 py-[1.1rem] text-center text-[0.95rem] font-semibold text-[#004241] no-underline transition-colors duration-200 hover:bg-white/75" data-suggestion-index="0">' + footerLabel + '</a>';
                        currentItems = [{ url: searchUrl }];
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
                    }).join('') + '<a href="' + searchUrl + '" class="header-search-view-all mt-1.5 flex items-center justify-center rounded-[1.25rem] bg-white/50 px-5 py-[1.1rem] text-center text-[0.95rem] font-semibold text-[#004241] no-underline transition-colors duration-200 hover:bg-white/75" data-suggestion-index="' + items.length + '">' + footerLabel + '</a>';
                    currentItems.push({ url: searchUrl });
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

            <a
                href="<?= auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register') ?>"
                class="hidden xl:flex items-center justify-center rounded-full flex-shrink-0 h-12 font-medium text-base leading-none min-w-[164px] px-5 bg-[#004241] text-white transition-colors duration-200 hover:bg-[#003130] focus:outline-none focus:ring-0 focus:ring-offset-0"
            >
                <?= htmlspecialchars(__('site.write_article')) ?>
            </a>

            <div
                class="vivat-lang-switch inline-grid shrink-0 bg-[#EBF1EF]"
                data-language-switch
                data-active="<?= $isDutchLocale ? 'nl' : 'fr' ?>"
                role="group"
                aria-label="<?= htmlspecialchars(__('site.site_language')) ?>"
            >
                <span class="vivat-lang-switch__indicator" aria-hidden="true"></span>
                <button type="button" class="vivat-lang-switch__button" data-lang-option="fr" aria-pressed="<?= $isDutchLocale ? 'false' : 'true' ?>">
                    FR
                </button>
                <button type="button" class="vivat-lang-switch__button" data-lang-option="nl" aria-pressed="<?= $isDutchLocale ? 'true' : 'false' ?>">
                    NL
                </button>
            </div>

            <button type="button" id="hamburger-menu" class="group relative flex h-12 w-12 shrink-0 cursor-pointer items-center justify-center rounded-[30px] border-none bg-transparent" aria-label="<?= htmlspecialchars(__('site.open_menu')) ?>" aria-expanded="false" aria-controls="mobile-menu-panel">
                <span class="relative block h-[20px] w-7 shrink-0" aria-hidden="true">
                    <span class="absolute left-0 top-0 h-[2px] w-full origin-center rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:top-1/2 group-[aria-expanded=true]:-translate-y-1/2 group-[aria-expanded=true]:rotate-45"></span>
                    <span class="absolute left-0 top-1/2 h-[2px] w-full -translate-y-1/2 rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:opacity-0 group-[aria-expanded=true]:scale-x-0"></span>
                    <span class="absolute left-0 top-[18px] h-[2px] w-full origin-center rounded-full bg-[#004241] transition-all duration-[600ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-[aria-expanded=true]:top-1/2 group-[aria-expanded=true]:-translate-y-1/2 group-[aria-expanded=true]:-rotate-45"></span>
                </span>
            </button>
        </div>

        <?= render_php_view('site.partials.layout_mobile_menu', get_defined_vars()) ?>
    </div>
</header>
