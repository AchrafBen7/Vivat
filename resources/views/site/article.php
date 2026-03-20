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
$coverSrc = !empty($cover_image_url) ? $cover_image_url : $coverFallback;
$backHref = $relatedCategorySlug ? '/categories/'.htmlspecialchars($relatedCategorySlug) : '/';
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
$metaLine = trim(implode(' • ', array_filter([
    $published_at_display,
    $reading_time ? (int) $reading_time . ' min' : null,
])));
$shareUrl = url('/articles/'.$slug);
$shareTitle = $title;

// Insérer la pub au milieu du contenu (après le paragraphe du milieu)
$adMidContent = '<div class="flex items-center justify-center p-12 gap-2 my-6"><div class="flex items-center justify-center rounded-[30px] text-white/90 w-full max-w-[970px] h-[250px] bg-[#686868]"><span class="text-sm">Espace publicitaire 970×250</span></div></div>';
$paraCount = preg_match_all('/<\/p>\s*/i', $content);
$insertAfterPara = $paraCount >= 2 ? (int) floor($paraCount / 2) : 1;
$count = 0;
$content = preg_replace_callback('/(<\/p>\s*)/i', function ($m) use ($adMidContent, $insertAfterPara, &$count) {
    $count++;
    return $count === $insertAfterPara ? $m[1] . $adMidContent : $m[1];
}, $content);
?>
<div class="w-full max-w-[1400px] mx-auto">
    <!-- Bannière pub 728×90 -->
    <div class="flex items-center justify-center rounded-[30px] overflow-hidden mb-6 pr-12 pb-12 pl-12">
        <div class="flex items-center justify-center text-[#686868] border-2 border-dashed border-[#686868]/40 rounded-[30px] w-full max-w-[728px] h-[90px] gap-2">
            <span class="text-sm">Espace publicitaire 728×90</span>
        </div>
    </div>

    <!-- Grand carré hero : photo + overlay + bouton retour, titre, date -->
    <div class="relative w-full mx-auto rounded-[30px] overflow-hidden mb-[54px] max-w-[1282px] h-[444px] min-h-[280px] bg-black/30">
        <img src="<?= htmlspecialchars($coverSrc) ?>" data-fallback-url="<?= htmlspecialchars($coverFallback) ?>" alt="<?= htmlspecialchars($title) ?>" class="absolute inset-0 w-full h-full object-cover" loading="eager" onerror="this.onerror=null;this.src=this.dataset.fallbackUrl||'';">
        <div class="absolute inset-0 bg-black/30" aria-hidden="true"></div>
        <div class="absolute inset-0 flex flex-col p-8 top-0 left-0">
            <a href="<?= htmlspecialchars($backHref) ?>" class="inline-flex items-center gap-2.5 self-start rounded-full font-normal transition hover:opacity-90 w-[127px] h-12 py-3 px-[18px] bg-[#EBF1EF] border border-white/10 font-sans text-xl leading-none text-[#004241] mb-[85px]">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
                <span>Retour</span>
            </a>
            <h1 class="text-white font-semibold leading-none max-w-[947px] text-5xl mb-[9px] font-sans"><?= htmlspecialchars($title) ?></h1>
            <?php if ($metaLine): ?>
            <p class="text-white font-light leading-none text-xl opacity-95 font-sans">
                <time datetime="<?= htmlspecialchars($published_at_iso ?? '') ?>"><?= htmlspecialchars($metaLine) ?></time>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Grid : texte article + partage vertical à droite (aligné avec le hero) -->
<div class="w-full max-w-[1400px] mx-auto grid grid-cols-1 lg:grid-cols-[1fr_auto] lg:gap-[130px]">
    <article class="min-w-0 max-w-3xl mx-auto">
    <?php if ($excerpt): ?>
    <p class="text-xl text-gray-600 mb-8"><?= nl2br(htmlspecialchars($excerpt)) ?></p>
    <?php endif; ?>
    <div class="prose prose-lg prose-gray max-w-none">
        <?= $content ?>
    </div>

    <!-- Partager horizontal (fin d'article, mobile + desktop) -->
    <aside class="flex flex-col mt-12" aria-label="Partager l'article">
        <span class="font-normal leading-none mb-6 text-xl text-[#004241] font-sans">Partager</span>
        <div class="flex flex-row gap-6">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur Facebook">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($shareUrl) ?>&text=<?= rawurlencode($shareTitle) ?>" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur X">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur Instagram">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.766 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur LinkedIn">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
            <a href="https://wa.me/?text=<?= rawurlencode($shareTitle . ' ' . $shareUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur WhatsApp">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
        </div>
    </aside>
    </article>

    <!-- Partager vertical (à droite du texte, aligné avec le grid du hero) -->
    <aside class="hidden lg:flex flex-col items-start flex-shrink-0 pt-0" aria-label="Partager l'article">
        <span class="font-normal leading-none mb-6 text-xl text-[#004241] font-sans">Partager</span>
        <div class="flex flex-col gap-6">
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur Facebook">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            <a href="https://twitter.com/intent/tweet?url=<?= rawurlencode($shareUrl) ?>&text=<?= rawurlencode($shareTitle) ?>" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur X">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur Instagram">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.766 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur LinkedIn">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
            </a>
            <a href="https://wa.me/?text=<?= rawurlencode($shareTitle . ' ' . $shareUrl) ?>" target="_blank" rel="noopener noreferrer" class="text-[#004241] hover:opacity-80 transition w-[22px] h-[22px]" aria-label="Partager sur WhatsApp">
                <svg class="w-[22px] h-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            </a>
        </div>
    </aside>
</div>

<section class="max-w-[1400px] mx-auto mt-16" aria-label="À lire aussi">
    <style>#also-carousel-track::-webkit-scrollbar{display:none}</style>
    <h2 class="text-[#004241] font-medium mb-6 text-3xl font-sans">À lire aussi</h2>
    <div id="also-carousel-frame" class="relative min-w-0 lg:mx-auto lg:max-w-[1200px]">
        <div
            id="also-carousel-track"
            class="overflow-x-auto overflow-y-hidden [scrollbar-width:none] [-ms-overflow-style:none]"
        >
            <div id="also-carousel-rail" class="flex gap-6 w-max pr-6">
                <?php for ($copy = 0; $copy < 3; $copy++): ?>
                <?php foreach ($alsoCarouselItems as $idx => $item): ?>
                <?php $isAd = ($item['type'] ?? 'article') === 'ad'; ?>
                <?php $isMiddleSequence = $copy === 1; ?>
                <?php if ($isAd): ?>
                <aside
                    class="hidden lg:flex flex-shrink-0 rounded-[30px] items-center justify-center text-center text-white/90 bg-[#4B4B4B] w-[380px] h-[380px]"
                    <?= $copy === 0 ? 'data-cycle-item="1"' : '' ?>
                >
                    <div class="flex flex-col items-center justify-center gap-3">
                        <span class="font-semibold text-[22px]"><?= htmlspecialchars($item['label']) ?></span>
                        <span class="text-lg">380×380</span>
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
                    <div class="vivat-card-overlay flex flex-col justify-end">
                        <div class="rounded-[21px] vivat-glass flex flex-col w-full gap-2">
                            <span class="<?= $tagClass ?> bg-white/20 text-white"><?= htmlspecialchars($itemCategory) ?></span>
                            <h3 class="font-medium text-white leading-tight text-xl"><?= htmlspecialchars($item['title']) ?></h3>
                            <p class="text-white/80 text-xs"><?= htmlspecialchars($item['date']) ?> • <?= (int) $item['reading_time'] ?> min</p>
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
            class="absolute top-1/2 -translate-y-1/2 z-10 flex items-center justify-center rounded-full text-[#004241] shadow-sm hover:scale-[1.03] transition -left-[29px] w-[58px] h-[58px] bg-[#F2E8D2]"
            aria-label="Article précédent"
        >
            <svg class="w-7 h-7 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </button>
        <button
            type="button"
            id="also-carousel-next"
            class="absolute top-1/2 -translate-y-1/2 z-10 flex items-center justify-center rounded-full text-[#004241] shadow-sm hover:scale-[1.03] transition -right-[29px] w-[58px] h-[58px] bg-[#F2E8D2]"
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
