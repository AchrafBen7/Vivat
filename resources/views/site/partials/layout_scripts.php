<script>
(function() {
    function initHamburgerMenu() {
        var btn = document.getElementById('hamburger-menu');
        var panel = document.getElementById('mobile-menu-panel');
        var overlay = document.getElementById('mobile-nav-overlay');
        if (!btn || !panel) {
            return;
        }

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
            btn.setAttribute('aria-label', isOpen ? <?= json_encode(__('site.close_menu')) ?> : <?= json_encode(__('site.open_menu')) ?>);
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

    window._vivatInitHamburger = initHamburgerMenu;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHamburgerMenu);
    } else {
        initHamburgerMenu();
    }

    function initLanguageSwitches() {
        if (!document.querySelector('[data-language-switch]')) {
            return;
        }

        function getSwitches() {
            return Array.prototype.slice.call(document.querySelectorAll('[data-language-switch]'));
        }

        function syncSwitch(switchEl, lang) {
            switchEl.setAttribute('data-active', lang);
            switchEl.querySelectorAll('[data-lang-option]').forEach(function(button) {
                button.setAttribute('aria-pressed', button.getAttribute('data-lang-option') === lang ? 'true' : 'false');
            });
        }

        function applyLanguage(lang) {
            getSwitches().forEach(function(switchEl) {
                syncSwitch(switchEl, lang);
            });

            try {
                window.sessionStorage.setItem('vivat-language-visual', lang);
            } catch (error) {
            }
        }

        function runPageScripts(container) {
            container.querySelectorAll('script').forEach(function(old) {
                var s = document.createElement('script');
                Array.from(old.attributes).forEach(function(a) { s.setAttribute(a.name, a.value); });
                s.textContent = old.textContent;
                old.parentNode.replaceChild(s, old);
            });
        }

        var prefetchCache = {};
        var activeCtrl   = null;
        var lastNavTime  = 0;
        var NAV_COOLDOWN = 300; // ms minimum entre deux navigations

        function buildLangUrl(lang) {
            var u = new URL(window.location.href);
            u.searchParams.set('lang', lang);
            return u.toString();
        }

        function prefetchLang(lang) {
            var s0 = document.querySelector('[data-language-switch]');
            var cur = s0 ? s0.getAttribute('data-active') || 'fr' : 'fr';
            if (lang === cur || prefetchCache[lang]) return;
            prefetchCache[lang] = fetch(buildLangUrl(lang), { headers: { 'X-Vivat-Ajax': '1' } })
                .then(function(r) { if (!r.ok) throw 0; return r.text(); })
                .catch(function() { delete prefetchCache[lang]; });
        }

        function showMain(mainEl) {
            mainEl.style.transition = 'opacity 0.15s ease';
            mainEl.style.opacity    = '1';
            mainEl.style.pointerEvents = '';
        }

        function navigateToLanguage(lang) {
            // ── Rate limit ──────────────────────────────────────────────
            var now = Date.now();
            if (now - lastNavTime < NAV_COOLDOWN) return;

            var s0 = document.querySelector('[data-language-switch]');
            var currentLang = s0 ? s0.getAttribute('data-active') || 'fr' : 'fr';
            if (lang !== 'fr' && lang !== 'nl') return;
            if (lang === currentLang) return;

            lastNavTime = now;

            // Annuler la navigation en cours
            if (activeCtrl) { activeCtrl.abort(); activeCtrl = null; }
            var ctrl  = new AbortController();
            activeCtrl = ctrl;

            applyLanguage(lang);
            try {
                document.cookie = 'vivat_lang=' + encodeURIComponent(lang) + '; path=/; max-age=2592000; SameSite=Lax';
            } catch(e) {}

            // La home a beaucoup de blocs et de scripts réinjectés.
            // Un rechargement complet est plus fiable que le swap AJAX partiel.
            if (window.location.pathname === '/') {
                location.href = buildLangUrl(lang);
                return;
            }

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                location.href = buildLangUrl(lang); return;
            }

            var mainEl = document.querySelector('main');
            if (!mainEl || !window.fetch) { location.href = buildLangUrl(lang); return; }

            // ── Nettoyage GSAP complet ───────────────────────────────────
            document.documentElement.classList.remove('js-anim');
            if (window.gsap) {
                // Tuer tous les tweens actifs sur mainEl ET tous ses enfants
                gsap.killTweensOf(mainEl);
                var children = mainEl.querySelectorAll('*');
                for (var i = 0; i < children.length; i++) gsap.killTweensOf(children[i]);
            }
            // Tuer les ScrollTriggers pour qu'ils ne s'accumulent pas
            if (window.ScrollTrigger) {
                ScrollTrigger.getAll().forEach(function(t) { t.kill(); });
            }

            // ── Fetch (prefetch cache ou nouvelle requête) ───────────────
            var fetchP;
            if (prefetchCache[lang]) {
                fetchP = prefetchCache[lang];
                delete prefetchCache[lang];
            } else {
                fetchP = fetch(buildLangUrl(lang), {
                    headers: { 'X-Vivat-Ajax': '1' },
                    signal: ctrl.signal,
                }).then(function(r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); });
            }

            // ── Garder la page visible pendant le fetch : on évite l'écran blanc
            // en atténuant légèrement le contenu au lieu de le masquer.
            mainEl.style.transition = 'opacity 0.12s ease';
            mainEl.style.opacity    = '0.68';
            mainEl.style.pointerEvents = 'none';

            // ── Safety : force la visibilité après 1.5 s quoi qu'il arrive ─
            var safetyTimer = setTimeout(function() { showMain(mainEl); }, 1500);

            fetchP.then(function(html) {
                if (ctrl !== activeCtrl || ctrl.signal.aborted) return;
                activeCtrl = null;
                clearTimeout(safetyTimer);

                if (!html) throw new Error('empty');
                var parser  = new DOMParser();
                var newDoc  = parser.parseFromString(html, 'text/html');
                var newMain = newDoc.querySelector('main');
                if (!newMain) throw new Error('no main');

                mainEl.className  = newMain.className;
                mainEl.innerHTML  = newMain.innerHTML;
                window._vivatLangSwap = true;
                runPageScripts(mainEl);
                window._vivatLangSwap = false;

                // ── Swap header (traductions nav) ────────────────────────
                var newHeaderPayload = newDoc.getElementById('ajax-header-payload');
                if (newHeaderPayload) {
                    var currentHeader = document.querySelector('header#site-header');
                    var tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newHeaderPayload.innerHTML;
                    var newHeader = tempDiv.querySelector('header#site-header');
                    if (currentHeader && newHeader) {
                        currentHeader.innerHTML = newHeader.innerHTML;
                        runPageScripts(currentHeader);
                        // Ré-initialiser le hamburger (ses listeners sont sur les anciens éléments)
                        if (typeof window._vivatInitHamburger === 'function') {
                            window._vivatInitHamburger();
                        }
                    }
                }

                document.title = newDoc.title;
                document.documentElement.lang = lang;
                history.pushState({ lang: lang }, '', buildLangUrl(lang));
                if (typeof attachFallbackToImages === 'function') attachFallbackToImages();

                // ── Fade in ──────────────────────────────────────────────
                requestAnimationFrame(function() { showMain(mainEl); });

            }).catch(function(err) {
                clearTimeout(safetyTimer);
                if (ctrl.signal.aborted || (err && err.name === 'AbortError')) return;
                showMain(mainEl);
                location.href = buildLangUrl(lang);
            });
        }

        // Toujours aligner l’UI sur le serveur (cookie / ?lang=), pas sur sessionStorage : sinon le switch
        // pouvait afficher NL alors que le HTML servi était encore en FR (cookie illisible côté PHP).
        var initialLanguage = (document.querySelector('[data-language-switch]') || {getAttribute: function(){return null;}}).getAttribute('data-active') || 'fr';
        applyLanguage(initialLanguage);
        try {
            window.sessionStorage.setItem('vivat-language-visual', initialLanguage);
        } catch (error) {
        }

        // Warm prefetch : lancer le fetch de l'autre langue en arrière-plan
        // dès que la page est idle, pour que le clic soit quasi-instantané
        var _warmLang = initialLanguage === 'fr' ? 'nl' : 'fr';
        if (window.requestIdleCallback) {
            requestIdleCallback(function() { prefetchLang(_warmLang); }, { timeout: 2000 });
        } else {
            setTimeout(function() { prefetchLang(_warmLang); }, 800);
        }

        // Event delegation au niveau document — survit au swap du header
        document.addEventListener('mouseover', function(event) {
            var btn = event.target.closest('[data-lang-option]');
            if (btn) prefetchLang(btn.getAttribute('data-lang-option'));
        });
        document.addEventListener('focusin', function(event) {
            var btn = event.target.closest('[data-lang-option]');
            if (btn) prefetchLang(btn.getAttribute('data-lang-option'));
        });
        document.addEventListener('click', function(event) {
            var btn = event.target.closest('[data-lang-option]');
            if (!btn) return;
            event.preventDefault();
            navigateToLanguage(btn.getAttribute('data-lang-option'));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLanguageSwitches);
    } else {
        initLanguageSwitches();
    }

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
