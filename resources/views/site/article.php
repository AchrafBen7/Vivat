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
];
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
    <h2 class="text-[#004241] font-medium mb-6" style="font-size: 32px;">À lire aussi</h2>
    <div class="relative min-w-0">
        <div id="also-carousel-track" class="overflow-hidden">
            <div id="also-carousel-slides" class="flex transition-transform duration-500 ease-out">
                <?php foreach ($relatedItems as $item): ?>
                <article class="w-full flex-shrink-0">
                    <div class="grid grid-cols-1 lg:grid-cols-[240px_minmax(0,1fr)_minmax(0,1fr)] gap-6 items-stretch">
                        <aside class="rounded-[30px] flex items-center justify-center text-center text-white/90" style="background: #4B4B4B; height: 400px;">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <span class="font-semibold" style="font-size: 22px;">Publicité</span>
                                <span style="font-size: 18px;">240×400</span>
                            </div>
                        </aside>

                        <?php $itemCategory = $item['category'] ?? 'À la une'; ?>
                        <a href="#" class="group block rounded-[30px] overflow-hidden relative h-[400px]">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent"></div>
                            <div class="absolute left-[18px] right-[18px] bottom-[18px]">
                                <div class="rounded-[21px] vivat-glass flex flex-col" style="padding: 24px; gap: 8px;">
                                    <span class="font-medium tracking-wide w-fit rounded-full inline-flex items-center" style="height: 30px; padding: 0 12px; font-size: 12px; box-sizing: border-box; background: rgba(255,255,255,0.18); color: #fff;"><?= htmlspecialchars($itemCategory) ?></span>
                                    <h3 class="font-medium text-white leading-tight" style="font-size: 20px;"><?= htmlspecialchars($item['title']) ?></h3>
                                    <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($item['date']) ?> • <?= (int) $item['reading_time'] ?> min</p>
                                </div>
                            </div>
                        </a>

                        <a href="#" class="group block rounded-[30px] overflow-hidden relative h-[400px]">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/35 via-transparent to-transparent"></div>
                            <div class="absolute left-[18px] right-[18px] bottom-[18px]">
                                <div class="rounded-[21px] vivat-glass flex flex-col" style="padding: 24px; gap: 8px;">
                                    <span class="font-medium tracking-wide w-fit rounded-full inline-flex items-center" style="height: 30px; padding: 0 12px; font-size: 12px; box-sizing: border-box; background: rgba(255,255,255,0.18); color: #fff;"><?= htmlspecialchars($itemCategory) ?></span>
                                    <h3 class="font-medium text-white leading-tight" style="font-size: 20px;"><?= htmlspecialchars($item['title']) ?></h3>
                                    <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($item['date']) ?> • <?= (int) $item['reading_time'] ?> min</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (count($relatedItems) > 1): ?>
        <button
            type="button"
            id="also-carousel-next"
            class="absolute right-[-24px] top-1/2 -translate-y-1/2 z-10 flex items-center justify-center rounded-full text-[#004241] shadow-sm hover:scale-[1.03] transition"
            style="width: 58px; height: 58px; background: #F2E8D2;"
            aria-label="Article suivant"
        >
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
        <script>
        (function() {
            var nextBtn = document.getElementById('also-carousel-next');
            var slides = document.getElementById('also-carousel-slides');
            if (!nextBtn || !slides) {
                return;
            }
            var total = slides.children.length;
            var index = 0;
            nextBtn.addEventListener('click', function() {
                index = (index + 1) % total;
                slides.style.transform = 'translateX(' + (-100 * index) + '%)';
            });
        })();
        </script>
        <?php endif; ?>
    </div>
</section>
