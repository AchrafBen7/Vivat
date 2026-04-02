<?php
$article = $article ?? [];
$title = $article['title'] ?? 'Article';
$slug = $article['slug'] ?? '';
$published_at = $article['published_at'] ?? null;
$published_at_display = $article['published_at_display'] ?? null;
$published_at_iso = $article['published_at_iso'] ?? null;
$reading_time = $article['reading_time'] ?? null;
$category = $article['category'] ?? null;
$cover_image_url = $article['cover_image_url'] ?? null;
$content = $article['content'] ?? '';
$excerpt = $article['excerpt'] ?? '';
$relatedCategoryName = $category['name'] ?? 'À la une';
$relatedCategorySlug = $category['slug'] ?? null;
$relatedBaseId = $article['id'] ?? $slug ?? 'article';
$catSlug = ($category ?? [])['slug'] ?? null;
$coverFallback = vivat_category_fallback_image($catSlug, 1282, 444, $relatedBaseId, 'cover');
$coverSrc = ! empty($cover_image_url) ? $cover_image_url : $coverFallback;
$backHref = $relatedCategorySlug ? '/categories/'.htmlspecialchars($relatedCategorySlug) : '/';
$relatedItems = ! empty($related_articles) ? array_map(fn (array $a) => [
    'title'        => $a['title'],
    'slug'         => $a['slug'],
    'date'         => $a['published_at_display'] ?? '',
    'reading_time' => $a['reading_time'] ?? 4,
    'category'     => $a['category'] ?? $relatedCategoryName,
    'image'        => $a['image'] ?? vivat_category_fallback_image($relatedCategorySlug, 760, 520, (string) $relatedBaseId, 'also-1'),
    'fallback'     => $a['fallback'] ?? $a['image'] ?? vivat_category_fallback_image($relatedCategorySlug, 760, 520, (string) $relatedBaseId, 'also-1b'),
], $related_articles) : [];
$alsoCarouselItems = array_merge(
    [[
        'type' => 'ad',
        'label' => 'Publicité',
        'size' => '400×400',
    ]],
    array_map(fn (array $item): array => ['type' => 'article'] + $item, $relatedItems)
);
$tagClass = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';
$glassTagTailwind = 'bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$tagGlassOnImage = $tagClass.' '.$glassTagTailwind.' text-white';
$tagCarousel = $tagClass.' bg-white text-[#004241] shadow-sm';
$metaLine = trim(implode(' • ', array_filter([
    $published_at_display,
    $reading_time ? (int) $reading_time.' min' : null,
])));
$shareUrl = url('/articles/'.$slug);
$shareTitle = $title;
$isPreview = (bool) ($article['is_preview'] ?? false);

// Si le contenu est du texte brut saisi par un rédacteur, convertir les lignes vides en paragraphes.
// On conserve le HTML existant pour ne pas casser les anciens articles déjà formatés.
if (is_string($content) && trim($content) !== '' && ! preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $content)) {
    $paragraphs = preg_split("/(?:\r\n|\r|\n)\s*(?:\r\n|\r|\n)+/", trim($content)) ?: [];
    $content = implode('', array_map(static function (string $paragraph): string {
        $paragraph = trim($paragraph);

        if ($paragraph === '') {
            return '';
        }

        return '<p>'.nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')).'</p>';
    }, $paragraphs));
}

// Insérer la pub au milieu du contenu (après le paragraphe du milieu)
$adMidContent = '<div class="my-6 flex items-center justify-center"><div class="flex h-[250px] w-full max-w-[970px] items-center justify-center rounded-[30px] border-2 border-dashed border-gray-300 bg-gray-100 text-sm text-gray-400"><span>Espace publicitaire 970×250</span></div></div>';
$paraCount = preg_match_all('/<\/p>\s*/i', $content);
$insertAfterPara = $paraCount >= 2 ? (int) floor($paraCount / 2) : 1;
$count = 0;
$content = preg_replace_callback('/(<\/p>\s*)/i', function ($m) use ($adMidContent, $insertAfterPara, &$count) {
    $count++;

    return $count === $insertAfterPara ? $m[1].$adMidContent : $m[1];
}, $content);
?>
<div class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
    <?php if ($isPreview) { ?>
    <div class="mb-6 flex items-center justify-center">
        <div class="inline-flex items-center gap-3 rounded-full border border-[#D6E3E1] bg-[#F4F8F7] px-5 py-3 text-sm font-medium text-[#004241] shadow-[0_10px_24px_rgba(0,66,65,0.05)]">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-[#004241] text-white">i</span>
            <span>Ceci est un aperçu de votre article. Il n’est pas encore affiché publiquement comme version finale.</span>
        </div>
    </div>
    <?php } ?>

    <!-- Bannière pub 728×90 -->
    <div class="mb-6 flex items-center justify-center">
        <div class="flex h-[90px] w-full max-w-[728px] items-center justify-center rounded-[30px] border-2 border-dashed border-gray-300 bg-gray-100 text-sm text-gray-400">
            <span class="text-sm">Espace publicitaire 728×90</span>
        </div>
    </div>

    <!-- Grand carré hero : photo + overlay + bouton retour, titre, date -->
    <div class="relative w-full mx-auto rounded-[30px] overflow-hidden mb-[54px] max-w-[1282px] h-[444px] min-h-[280px] bg-black/30">
        <img src="<?= htmlspecialchars($coverSrc) ?>" data-fallback-url="<?= htmlspecialchars($coverFallback) ?>" alt="<?= htmlspecialchars($title) ?>" class="absolute inset-0 w-full h-full object-cover" loading="eager" onerror="this.onerror=null;this.src=this.dataset.fallbackUrl||'';">
        <div class="absolute inset-0 bg-black/30" aria-hidden="true"></div>
        <div class="absolute inset-0 flex flex-col p-8 top-0 left-0">
            <a href="<?= htmlspecialchars($backHref) ?>" class="inline-flex items-center justify-center gap-2 self-start rounded-full bg-white/95 px-4 py-2.5 text-sm font-medium text-[#004241] shadow-md transition hover:bg-white mb-[85px]" aria-label="Retour">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" transform="matrix(-1 0 0 1 24 0)" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                Retour
            </a>
            <h1 class="text-white font-semibold leading-none max-w-[947px] text-5xl mb-[9px] font-sans"><?= htmlspecialchars($title) ?></h1>
            <?php if ($metaLine) { ?>
            <p class="text-white font-light leading-none text-xl opacity-95 font-sans">
                <time datetime="<?= htmlspecialchars($published_at_iso ?? '') ?>"><?= htmlspecialchars($metaLine) ?></time>
            </p>
            <?php } ?>
        </div>
    </div>
</div>

<?php
$shareLinks = [
    ['Facebook', 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($shareUrl), 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
    ['X', 'https://twitter.com/intent/tweet?url='.rawurlencode($shareUrl).'&text='.rawurlencode($shareTitle), 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
    ['LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($shareUrl), 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'],
    ['WhatsApp', 'https://wa.me/?text='.rawurlencode($shareTitle.' '.$shareUrl), 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z'],
];
?>
<style>
.article-body p { margin-bottom: 1.25rem; line-height: 1.75; }
.article-body p:last-child { margin-bottom: 0; }
.article-body h2 { font-size: 1.5rem; font-weight: 600; color: #004241; margin-top: 2rem; margin-bottom: 0.75rem; line-height: 1.3; }
.article-body h3 { font-size: 1.25rem; font-weight: 600; color: #004241; margin-top: 1.75rem; margin-bottom: 0.5rem; }
.article-body a { color: #004241; text-decoration: underline; text-underline-offset: 3px; }
.article-body a:hover { opacity: 0.8; }
.article-body ul, .article-body ol { margin: 1rem 0; padding-left: 1.5rem; }
.article-body li { margin-bottom: 0.5rem; line-height: 1.65; }
</style>
<div class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col items-center">
        <article class="w-full max-w-[680px]">
            <?php if ($excerpt) { ?>
            <p class="text-lg text-[#004241]/80 leading-relaxed mb-10 font-sans" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($excerpt)) ?></p>
            <?php } ?>
            <div class="article-body text-[#004241] font-sans text-[18px]" style="line-height: 1.75;">
        <?= $content ?>
            </div>

            <aside class="flex flex-col items-center mt-14 pt-10 border-t border-[#004241]/10" aria-label="Partager l'article">
                <span class="text-sm font-medium text-[#004241]/60 uppercase tracking-wider mb-5">Partager</span>
                <div class="flex items-center justify-center gap-5">
                    <?php foreach ($shareLinks as $share) { ?>
                    <a href="<?= htmlspecialchars($share[1]) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-11 h-11 rounded-full bg-[#EBF1EF] text-[#004241] hover:bg-[#004241] hover:text-white transition-colors duration-200" aria-label="Partager sur <?= htmlspecialchars($share[0]) ?>">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= htmlspecialchars($share[2]) ?>"/></svg>
                    </a>
                    <?php } ?>
                </div>
            </aside>
        </article>
    </div>
</div>

<section class="mx-auto mb-0 mt-16 max-w-[1400px] px-[18px] md:px-8 lg:px-10 xl:px-20" aria-label="À lire aussi">
    <h2 class="text-[#004241] font-medium mb-6 text-3xl font-sans">À lire aussi</h2>
    <div id="also-carousel-frame" class="relative min-w-0 w-full">
        <div
            id="also-carousel-track"
            class="w-full overflow-hidden"
        >
            <div id="also-carousel-rail" class="flex w-max gap-6 transition-transform duration-500 ease-out will-change-transform">
                <?php foreach ($alsoCarouselItems as $item) { ?>
                <?php $isAd = ($item['type'] ?? 'article') === 'ad'; ?>
                <?php if ($isAd) { ?>
                <aside
                    class="hidden lg:flex lg:w-[calc((100%-48px)/3)] flex-shrink-0 rounded-[30px] items-center justify-center text-center text-white/90 bg-[#4B4B4B] h-[380px]"
                    data-carousel-item="1"
                >
                    <div class="flex flex-col items-center justify-center gap-3">
                        <span class="font-semibold text-[22px]"><?= htmlspecialchars($item['label']) ?></span>
                        <span class="text-lg">380×380</span>
                    </div>
                </aside>
                <?php } else { ?>
                <?php $itemCategory = $item['category'] ?? 'À la une'; ?>
                <a
                    href="<?= ! empty($item['slug']) ? '/articles/'.htmlspecialchars($item['slug']) : '#' ?>"
                    class="group relative block h-[380px] w-[240px] flex-shrink-0 overflow-hidden rounded-[30px] sm:w-[320px] lg:w-[calc((100%-48px)/3)]"
                    data-cycle-item="1"
                >
                    <img src="<?= htmlspecialchars($item['image']) ?>" data-fallback-url="<?= htmlspecialchars($item['fallback'] ?? $item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy" onerror="this.onerror=null;this.src=this.dataset.fallbackUrl||'';">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent"></div>
                    <div class="vivat-card-overlay flex flex-col justify-end">
                        <div class="rounded-[21px] vivat-glass flex flex-col w-full gap-2">
                            <span class="<?= $tagCarousel ?>"><?= htmlspecialchars($itemCategory) ?></span>
                            <h3 class="font-medium text-white leading-tight text-xl"><?= htmlspecialchars($item['title']) ?></h3>
                            <p class="text-white/80 text-xs"><?= htmlspecialchars($item['date']) ?> • <?= (int) $item['reading_time'] ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>
                <?php } ?>
            </div>
        </div>

        <?php if (count($relatedItems) > 1) { ?>
        <button
            type="button"
            id="also-carousel-prev"
            class="absolute top-1/2 -translate-y-1/2 z-10 flex items-center justify-center rounded-full text-[#004241] shadow-sm hover:scale-[1.03] transition -left-[24px] w-12 h-12 bg-[#F2E8D2]"
            aria-label="Article précédent"
        >
            <svg class="w-6 h-6 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
        <button
            type="button"
            id="also-carousel-next"
            class="absolute top-1/2 -translate-y-1/2 z-10 flex items-center justify-center rounded-full text-[#004241] shadow-sm hover:scale-[1.03] transition -right-[24px] w-12 h-12 bg-[#F2E8D2]"
            aria-label="Article suivant"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
        <script>
        (function() {
            var prevBtn = document.getElementById('also-carousel-prev');
            var nextBtn = document.getElementById('also-carousel-next');
            var frame = document.getElementById('also-carousel-frame');
            var rail = document.getElementById('also-carousel-rail');
            if (!prevBtn || !nextBtn || !frame || !rail) {
                return;
            }

            var currentIndex = 0;

            function getGap() {
                return 24;
            }

            function getItems() {
                return Array.prototype.slice.call(rail.querySelectorAll('[data-carousel-item="1"]')).filter(function(item) {
                    return window.getComputedStyle(item).display !== 'none';
                });
            }

            function getVisibleCount() {
                if (window.matchMedia('(min-width: 1024px)').matches) {
                    return 3;
                }

                if (window.matchMedia('(min-width: 640px)').matches) {
                    return 2;
                }

                return 1;
            }

            function syncCardWidths() {
                var items = getItems();
                if (!items.length) {
                    return;
                }

                var visibleCount = getVisibleCount();
                var width = Math.floor((frame.getBoundingClientRect().width - (getGap() * (visibleCount - 1))) / visibleCount);

                items.forEach(function(item) {
                    item.style.width = width + 'px';
                    item.style.minWidth = width + 'px';
                });
            }

            function getMaxIndex() {
                return Math.max(0, getItems().length - getVisibleCount());
            }

            function getOffsets() {
                return getItems().map(function(item) {
                    return item.offsetLeft;
                });
            }

            function updateButtons() {
                var maxIndex = getMaxIndex();
                var atStart = currentIndex <= 0;
                var atEnd = currentIndex >= maxIndex;

                prevBtn.disabled = atStart;
                nextBtn.disabled = atEnd;
                prevBtn.classList.toggle('hidden', atStart);
                nextBtn.classList.toggle('hidden', atEnd);
            }

            function scrollToIndex(index, behavior) {
                var offsets = getOffsets();
                if (!offsets.length) {
                    return;
                }

                currentIndex = Math.max(0, Math.min(index, getMaxIndex()));
                if (behavior === 'auto') {
                    rail.classList.remove('duration-500');
                } else {
                    rail.classList.add('duration-500');
                }

                rail.style.transform = 'translateX(' + (-offsets[currentIndex]) + 'px)';
                updateButtons();
            }

            syncCardWidths();
            scrollToIndex(0, 'auto');

            prevBtn.addEventListener('click', function() {
                scrollToIndex(currentIndex - 1);
            });
            nextBtn.addEventListener('click', function() {
                scrollToIndex(currentIndex + 1);
            });

            window.addEventListener('resize', function() {
                syncCardWidths();
                scrollToIndex(Math.min(currentIndex, getMaxIndex()), 'auto');
            });

            window.setTimeout(function() {
                syncCardWidths();
                scrollToIndex(0, 'auto');
            }, 0);
        })();
        </script>
        <?php } ?>
    </div>
</section>
