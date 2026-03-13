<?php
$article = $article ?? [];
$title = $article['title'] ?? 'Article';
$slug = $article['slug'] ?? '';
$published_at = $article['published_at'] ?? null;
$published_at_iso = $article['published_at_iso'] ?? null;
$reading_time = $article['reading_time'] ?? null;
$category = $article['category'] ?? null;
$cover_image_url = $article['cover_image_url'] ?? null;
$content = $article['content'] ?? '';
$excerpt = $article['excerpt'] ?? '';
$relatedCategoryName = $category['name'] ?? 'À la une';
$relatedCategorySlug = $category['slug'] ?? null;
$relatedBaseId = $article['id'] ?? $slug ?? 'article';
$relatedItems = [
    [
        'title' => 'Deux squelettes enlacés livrent leurs secrets 12.000 ans après leur mort',
        'date' => '12 février 2026',
        'reading_time' => 4,
        'category' => $relatedCategoryName,
        'image' => vivat_category_fallback_image($relatedCategorySlug, 760, 520, (string) $relatedBaseId, 'also-1'),
    ],
    [
        'title' => 'A Bruxelles, un accord budgétaire aux contours encore imprécis',
        'date' => '12 février 2026',
        'reading_time' => 4,
        'category' => $relatedCategoryName,
        'image' => vivat_category_fallback_image($relatedCategorySlug, 760, 520, (string) $relatedBaseId, 'also-2'),
    ],
    [
        'title' => 'L’autosuffisance alimentaire est possible mais à certaines conditions',
        'date' => '12 février 2026',
        'reading_time' => 4,
        'category' => $relatedCategoryName,
        'image' => vivat_category_fallback_image($relatedCategorySlug, 760, 520, (string) $relatedBaseId, 'also-3'),
    ],
    [
        'title' => 'Des fouilles révèlent de nouvelles pistes sur les premiers peuples européens',
        'date' => '12 février 2026',
        'reading_time' => 4,
        'category' => $relatedCategoryName,
        'image' => vivat_category_fallback_image($relatedCategorySlug, 760, 520, (string) $relatedBaseId, 'also-4'),
    ],
];
$alsoCarouselItems = array_merge(
    [[
        'type' => 'ad',
        'label' => 'Publicité',
        'size' => '400×400',
    ]],
    array_map(fn (array $item): array => ['type' => 'article'] + $item, $relatedItems)
);
$tagClass = 'vivat-tag';
?>
<article class="max-w-3xl mx-auto">
    <header class="mb-8">
        <?php if ($category): ?>
        <a href="/categories/<?= htmlspecialchars($category['slug']) ?>" class="text-sm font-medium text-amber-600 hover:text-amber-700"><?= htmlspecialchars($category['name']) ?></a>
        <?php endif; ?>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2"><?= htmlspecialchars($title) ?></h1>
        <div class="flex gap-4 mt-4 text-gray-500" style="font-size: 12px;">
            <?php if ($published_at): ?>
            <time datetime="<?= htmlspecialchars($published_at_iso ?? $published_at) ?>"><?= htmlspecialchars($published_at) ?></time>
            <?php endif; ?>
            <?php if ($reading_time): ?>
            <span><?= (int) $reading_time ?> min de lecture</span>
            <?php endif; ?>
        </div>
    </header>
    <?php
    $catSlug = ($category ?? [])['slug'] ?? null;
    $artId = $article['id'] ?? $slug ?? null;
    $coverFallback = vivat_category_fallback_image($catSlug, 800, 450, $artId, 'cover');
    $coverSrc = !empty($cover_image_url) ? $cover_image_url : $coverFallback;
    ?>
    <figure class="rounded-xl overflow-hidden mb-8">
        <img src="<?= htmlspecialchars($coverSrc) ?>" data-fallback-url="<?= htmlspecialchars($coverFallback) ?>" alt="<?= htmlspecialchars($title) ?>" class="w-full aspect-video object-cover" loading="eager">
    </figure>
    <?php if ($excerpt): ?>
    <p class="text-xl text-gray-600 mb-8"><?= nl2br(htmlspecialchars($excerpt)) ?></p>
    <?php endif; ?>
    <div class="prose prose-lg prose-gray max-w-none">
        <?= $content ?>
    </div>
</article>

<section class="max-w-[1400px] mx-auto mt-16" aria-label="À lire aussi">
    <style>
        #also-carousel-track::-webkit-scrollbar {
            display: none;
        }

        @media (min-width: 1024px) {
            #also-carousel-frame {
                max-width: 1200px;
            }

            #also-carousel-rail [data-carousel-card] {
                width: 380px;
                min-width: 380px;
                flex-basis: 380px;
            }
        }
    </style>
    <h2 class="text-[#004241] font-medium mb-6" style="font-size: 32px;">À lire aussi</h2>
    <div id="also-carousel-frame" class="relative min-w-0 lg:mx-auto">
        <div
            id="also-carousel-track"
            class="overflow-x-auto overflow-y-hidden"
            style="scrollbar-width: none; -ms-overflow-style: none;"
        >
            <div id="also-carousel-rail" class="flex gap-6 w-max pr-6">
                <?php for ($copy = 0; $copy < 3; $copy++): ?>
                <?php foreach ($alsoCarouselItems as $idx => $item): ?>
                <?php $isAd = ($item['type'] ?? 'article') === 'ad'; ?>
                <?php $isMiddleSequence = $copy === 1; ?>
                <?php if ($isAd): ?>
                <aside
                    class="hidden lg:flex flex-shrink-0 rounded-[30px] items-center justify-center text-center text-white/90"
                    style="background: #4B4B4B; width: 380px; height: 380px;"
                    <?= $copy === 0 ? 'data-cycle-item="1"' : '' ?>
                >
                    <div class="flex flex-col items-center justify-center gap-3">
                        <span class="font-semibold" style="font-size: 22px;"><?= htmlspecialchars($item['label']) ?></span>
                        <span style="font-size: 18px;">380×380</span>
                    </div>
                </aside>
                <?php else: ?>
                <?php $itemCategory = $item['category'] ?? 'À la une'; ?>
                <a
                    href="#"
                    class="group block flex-shrink-0 rounded-[30px] overflow-hidden relative w-[240px] sm:w-[320px] lg:w-[380px] h-[380px]"
                    data-carousel-card="<?= ($isMiddleSequence ? $idx : '') ?>"
                    <?= $copy === 0 ? 'data-cycle-item="1"' : '' ?>
                >
                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent"></div>
                    <div class="absolute left-[18px] right-[18px] bottom-[18px]">
                        <div class="rounded-[21px] vivat-glass flex flex-col" style="padding: 24px; gap: 8px;">
                            <span class="<?= $tagClass ?>" style="background: rgba(255,255,255,0.18); color: #fff;"><?= htmlspecialchars($itemCategory) ?></span>
                            <h3 class="font-medium text-white leading-tight" style="font-size: 20px;"><?= htmlspecialchars($item['title']) ?></h3>
                            <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($item['date']) ?> • <?= (int) $item['reading_time'] ?> min</p>
                        </div>
                    </div>
                </a>
                <?php endif; ?>
                <?php endforeach; ?>
                <?php endfor; ?>
            </div>
        </div>

        <?php if (count($relatedItems) > 1): ?>
        <button
            type="button"
            id="also-carousel-prev"
            class="absolute top-1/2 -translate-y-1/2 z-10 flex items-center justify-center rounded-full text-[#004241] shadow-sm hover:scale-[1.03] transition"
            style="left: -29px; width: 58px; height: 58px; background: #F2E8D2;"
            aria-label="Article précédent"
        >
            <svg class="w-7 h-7 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
        <button
            type="button"
            id="also-carousel-next"
            class="absolute top-1/2 -translate-y-1/2 z-10 flex items-center justify-center rounded-full text-[#004241] shadow-sm hover:scale-[1.03] transition"
            style="right: -29px; width: 58px; height: 58px; background: #F2E8D2;"
            aria-label="Article suivant"
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
        <script>
        (function() {
            var prevBtn = document.getElementById('also-carousel-prev');
            var nextBtn = document.getElementById('also-carousel-next');
            var track = document.getElementById('also-carousel-track');
            var rail = document.getElementById('also-carousel-rail');
            if (!prevBtn || !nextBtn || !track || !rail) {
                return;
            }

            var normalizeTimer = null;

            function getGap() {
                return 24;
            }

            function getCycleWidth() {
                var cycleItems = rail.querySelectorAll('[data-cycle-item="1"]');
                if (!cycleItems.length) {
                    return 0;
                }
                var width = 0;
                cycleItems.forEach(function(item, index) {
                    width += item.getBoundingClientRect().width;
                    if (index < cycleItems.length - 1) {
                        width += getGap();
                    }
                });
                return width;
            }

            function getStep() {
                var firstCard = rail.querySelector('[data-carousel-card]');
                return firstCard ? firstCard.getBoundingClientRect().width + getGap() : 0;
            }

            function getSequenceSpan() {
                var cycleWidth = getCycleWidth();
                return cycleWidth ? cycleWidth + getGap() : 0;
            }

            function getViewportNudge() {
                return 4;
            }

            function getStartOffset() {
                var sequenceSpan = getSequenceSpan();
                return sequenceSpan ? sequenceSpan - getViewportNudge() : 0;
            }

            function normalizePosition() {
                var sequenceSpan = getSequenceSpan();
                if (!sequenceSpan) {
                    return;
                }

                if (track.scrollLeft <= sequenceSpan * 0.5) {
                    track.scrollLeft += sequenceSpan;
                } else if (track.scrollLeft >= sequenceSpan * 1.5) {
                    track.scrollLeft -= sequenceSpan;
                }
            }

            function scheduleNormalize() {
                if (normalizeTimer) {
                    window.clearTimeout(normalizeTimer);
                }

                normalizeTimer = window.setTimeout(function() {
                    normalizePosition();
                }, 180);
            }

            function setInitialPosition() {
                var startOffset = getStartOffset();
                if (startOffset) {
                    track.scrollLeft = startOffset;
                }
            }

            function move(direction) {
                var step = getStep();
                if (!step) {
                    return;
                }

                track.scrollBy({ left: direction * step, behavior: 'smooth' });
                window.setTimeout(normalizePosition, 420);
            }

            setInitialPosition();

            prevBtn.addEventListener('click', function() {
                move(-1);
            });
            nextBtn.addEventListener('click', function() {
                move(1);
            });

            track.addEventListener('scroll', function() {
                scheduleNormalize();
            }, { passive: true });

            window.addEventListener('resize', function() {
                var sequenceSpan = getSequenceSpan();
                if (!sequenceSpan) {
                    return;
                }

                var relativeOffset = (track.scrollLeft + getViewportNudge()) % sequenceSpan;
                track.scrollLeft = sequenceSpan + relativeOffset - getViewportNudge();
            });

            window.setTimeout(function() {
                setInitialPosition();
                scheduleNormalize();
            }, 0);
        })();
        </script>
        <?php endif; ?>
    </div>
</section>
