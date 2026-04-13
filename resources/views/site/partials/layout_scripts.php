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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHamburgerMenu);
    } else {
        initHamburgerMenu();
    }

    function initLanguageSwitches() {
        var switches = Array.prototype.slice.call(document.querySelectorAll('[data-language-switch]'));
        if (!switches.length) {
            return;
        }

        function syncSwitch(switchEl, lang) {
            switchEl.setAttribute('data-active', lang);
            switchEl.querySelectorAll('[data-lang-option]').forEach(function(button) {
                button.setAttribute('aria-pressed', button.getAttribute('data-lang-option') === lang ? 'true' : 'false');
            });
        }

        function applyLanguage(lang) {
            switches.forEach(function(switchEl) {
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

        function navigateToLanguage(lang) {
            var currentLanguage = switches[0].getAttribute('data-active') || 'fr';
            if (lang !== 'fr' && lang !== 'nl') return;
            if (lang === currentLanguage) return;

            applyLanguage(lang);

            try {
                document.cookie = 'vivat_lang=' + encodeURIComponent(lang) + '; path=/; max-age=' + (60 * 60 * 24 * 30) + '; SameSite=Lax';
            } catch (e) {}

            var url = new URL(window.location.href);
            url.searchParams.set('lang', lang);

            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                window.location.href = url.toString();
                return;
            }

            var mainEl = document.querySelector('main');
            if (!mainEl || !window.fetch) {
                window.location.href = url.toString();
                return;
            }

            mainEl.style.opacity = '0';

            fetch(url.toString(), { headers: { 'X-Vivat-Ajax': '1' } })
                .then(function(res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.text();
                })
                .then(function(html) {
                    var parser = new DOMParser();
                    var newDoc = parser.parseFromString(html, 'text/html');
                    var newMain = newDoc.querySelector('main');
                    if (!newMain) throw new Error('no main');

                    mainEl.className = newMain.className;
                    mainEl.innerHTML = newMain.innerHTML;

                    runPageScripts(mainEl);

                    document.title = newDoc.title;
                    document.documentElement.lang = lang;

                    history.pushState({ lang: lang }, '', url.toString());

                    if (typeof attachFallbackToImages === 'function') {
                        attachFallbackToImages();
                    }

                    requestAnimationFrame(function() {
                        mainEl.style.opacity = '1';
                    });
                })
                .catch(function() {
                    window.location.href = url.toString();
                });
        }

        var initialLanguage = switches[0].getAttribute('data-active') || 'fr';
        try {
            var storedLanguage = window.sessionStorage.getItem('vivat-language-visual');
            if (storedLanguage === 'fr' || storedLanguage === 'nl') {
                initialLanguage = storedLanguage;
            }
        } catch (error) {
        }

        applyLanguage(initialLanguage);

        switches.forEach(function(switchEl) {
            switchEl.addEventListener('click', function(event) {
                var button = event.target.closest('[data-lang-option]');
                if (!button) {
                    return;
                }

                event.preventDefault();
                navigateToLanguage(button.getAttribute('data-lang-option'));
            });
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.gsap !== 'undefined') {
        var header = document.getElementById('site-header');
        if (header) window.gsap.set(header, { autoAlpha: 0, y: -10 });
    }
});
window.addEventListener('load', function () {
    if (typeof window.gsap !== 'undefined') {
        var header = document.getElementById('site-header');
        if (header) {
            window.gsap.to(header, {
                autoAlpha: 1,
                y: 0,
                duration: 1.8,
                ease: 'power3.out',
                delay: 0.15,
            });
        }
    }
});
</script>
