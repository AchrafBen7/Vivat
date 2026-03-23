<?php
$articles = array_values($articles ?? []);
$pagination = $pagination ?? null;
$search_term = $search_term ?? '';
$matched_category = $matched_category ?? null;
$search_mosaic_padded = $search_mosaic_padded ?? false;
$continue_reading_articles = array_values($continue_reading_articles ?? []);

$tagBase = 'inline-flex w-fit items-center justify-center rounded-full px-[14px] py-[7px] text-sm font-medium';
$tagCategoryTw = [
    'vert' => 'bg-[#527E7E] text-white',
    'jaune' => 'bg-[#004241] text-white',
    'gris' => 'bg-white text-[#004241]',
];
$articleImageZoom = 'group block relative min-w-0 w-full transition-[transform] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1';
$articleImageZoomImg = 'absolute inset-0 h-full w-full object-cover';
$searchPhotoClip30 = 'absolute inset-0 z-0 overflow-hidden rounded-[30px]';
$searchPhotoClip32 = 'absolute inset-0 z-0 overflow-hidden rounded-[32px]';
$searchPhotoClip25 = 'absolute inset-0 z-0 overflow-hidden rounded-[25px]';
$cardOverlay = 'absolute inset-[18px] box-border';
$glassBox = 'rounded-[21px] flex flex-col gap-2 bg-[rgba(190,190,190,0.10)] border border-[rgba(230,230,230,0.20)] backdrop-blur-[15px] p-[18px]';
$tagGlassOnImage = $tagBase.' bg-[rgba(190,190,190,0.10)] text-white border border-[rgba(230,230,230,0.20)] backdrop-blur-[15px]';
$articleMetaOnImage = 'text-xs text-white/80';
$overlayImageSoft = 'absolute inset-0 bg-gradient-to-t from-black/35 via-black/10 to-transparent';
$overlayImagePhoto = 'absolute inset-0 bg-gradient-to-t from-black/55 via-black/18 to-transparent';
$cardYellowSurface = 'bg-[#FFF0B6]';
$cardGreenSurface = 'bg-[#004241]';
$cardArrowOnYellow = 'absolute top-[18px] right-[18px] flex h-12 w-12 items-center justify-center rounded-full bg-[#004241] text-white opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100';
$cardArrowOnGreen = 'absolute top-[18px] right-[18px] flex h-12 w-12 items-center justify-center rounded-full bg-white/25 text-white opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100';
$adSurface = 'relative overflow-hidden rounded-[30px] border border-[#D6E3E1] bg-[linear-gradient(135deg,#F4F8F7_0%,#E6F0ED_45%,#D8E8E3_100%)] p-6 shadow-[0_12px_24px_rgba(0,66,65,0.08)]';
/** Même style que le carrousel « Rubriques » (home.php) */
$continueCarouselNavBtn = 'absolute top-1/2 z-[60] flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-[#004241] text-white shadow-none transition-colors duration-200 hover:bg-[#003130] focus:outline-none focus:ring-2 focus:ring-[#004241] focus:ring-offset-2';
?>

<div class="max-w-[1400px] mx-auto pt-6 pb-12">
    <div class="mb-10 flex flex-col gap-4">
        <h1 class="text-3xl font-medium text-[#004241] font-sans">Résultats</h1>

        <?php if ($search_term !== '') { ?>
        <div class="max-w-3xl space-y-2">
            <p class="text-base text-[#004241]/80">
                <?php if ($matched_category) { ?>
        Articles dans la catégorie <strong><?= htmlspecialchars($matched_category['name']) ?></strong>
                <?php } else { ?>
        Résultats pour «&nbsp;<strong><?= htmlspecialchars($search_term) ?></strong>&nbsp;»
                <?php } ?>
            </p>
            <?php if ($pagination && $pagination->total() > 0) { ?>
            <p class="text-sm font-medium text-[#004241]/70">
                <?php if ($matched_category) { ?>
                <?= (int) $pagination->total() ?> article<?= $pagination->total() > 1 ? 's' : '' ?>
                <?php } else { ?>
                <?= (int) $pagination->total() ?> résultat<?= $pagination->total() > 1 ? 's' : '' ?> correspondant<?= $pagination->total() > 1 ? 's' : '' ?>
                <?php } ?>
            </p>
            <?php } ?>
            <?php if ($search_mosaic_padded) { ?>
            <p class="text-sm text-[#004241]/60">
                La grille est complétée avec des articles récents pour conserver la même mise en page qu’en page d’accueil.
            </p>
            <?php } ?>
        </div>
        <?php } else { ?>
        <p class="max-w-3xl text-base text-[#004241]/80">Saisissez un mot-clé ou le nom d&apos;une catégorie pour rechercher.</p>
        <?php } ?>
    </div>

    <?php if ($search_term !== '' && count($articles) > 0) { ?>
        <?php
    $grid = array_values($articles);
        $firstArt = $grid[0] ?? null;
        $secondArt = $grid[1] ?? null;
        $hotNewsArt = $grid[2] ?? null;
        $artRight = $grid[5] ?? null;
        $artForFullPhoto1 = $grid[6] ?? null;
        $artForFullPhoto2 = $grid[7] ?? null;
        $artLeft = $grid[8] ?? null;
        $artLeft2 = $grid[9] ?? null;
        $searchPair = array_slice($grid, 3, 2);
        ?>
    <section class="grid w-full min-w-0 grid-cols-1 gap-6 md:grid-cols-8 lg:grid-cols-12">
        <div class="md:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php if ($firstArt) { ?>
                <?php $f0CatSlug = $firstArt['category']['slug'] ?? null;
                    $f0ArtId = $firstArt['id'] ?? $firstArt['slug'] ?? null;
                    $f0Fallback = vivat_category_fallback_image($f0CatSlug, 302, 419, $f0ArtId, 'search-0');
                    $f0Img = ! empty($firstArt['cover_image_url']) ? $firstArt['cover_image_url'] : $f0Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($firstArt['slug']) ?>" class="<?= $articleImageZoom ?> rounded-[30px] min-w-0 w-full h-[419px]<?= $secondArt ? '' : ' sm:col-span-2' ?>">
                    <div class="<?= $searchPhotoClip30 ?>">
                    <img src="<?= htmlspecialchars($f0Img) ?>" data-fallback-url="<?= htmlspecialchars($f0Fallback) ?>" alt="<?= htmlspecialchars($firstArt['title'] ?? 'Article') ?>" class="<?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImageSoft ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($firstArt['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($firstArt['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="line-clamp-5 text-xl font-medium text-white"><?= htmlspecialchars($firstArt['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($firstArt['published_at'] ?? '') ?> • <?= (int) ($firstArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                    </div>
                </a>
                <?php } ?>

                <?php if ($secondArt) { ?>
                <?php $secondCatSlug = $secondArt['category']['slug'] ?? null;
                    $secondArtId = $secondArt['id'] ?? $secondArt['slug'] ?? null;
                    $secondFallback = vivat_category_fallback_image($secondCatSlug, 254, 190, $secondArtId, 'search-1');
                    $secondImg = ! empty($secondArt['cover_image_url']) ? $secondArt['cover_image_url'] : $secondFallback; ?>
                <a href="/articles/<?= htmlspecialchars($secondArt['slug']) ?>" class="group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full h-[419px] bg-[#EBF1EF] p-6 gap-[18px] shadow-[0_12px_24px_rgba(0,66,65,0.08)] transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1<?= $firstArt ? '' : ' sm:col-span-2' ?>">
                    <div class="flex flex-col flex-1 min-h-0 gap-2">
                        <?php if (! empty($secondArt['category'])) { ?>
                        <span class="<?= $tagBase ?> <?= $tagCategoryTw['gris'] ?>"><?= htmlspecialchars($secondArt['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="line-clamp-5 text-xl font-medium text-[#004241]"><?= htmlspecialchars($secondArt['title']) ?></h3>
                        <p class="text-xs font-light text-[#004241]"><?= htmlspecialchars($secondArt['published_at'] ?? '') ?> • <?= (int) ($secondArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <div class="relative h-[190px] w-full flex-shrink-0 overflow-hidden rounded-[21px] isolate">
                        <img src="<?= htmlspecialchars($secondImg) ?>" data-fallback-url="<?= htmlspecialchars($secondFallback) ?>" alt="<?= htmlspecialchars($secondArt['title'] ?? 'Article') ?>" class="h-full w-full object-cover rounded-[21px]" loading="lazy">
                    </div>
                </a>
                <?php } ?>
            </div>

            <?php if ($hotNewsArt) { ?>
            <?php $hotCatSlug = $hotNewsArt['category']['slug'] ?? null;
                $hotArtId = $hotNewsArt['id'] ?? $hotNewsArt['slug'] ?? null;
                $hotFallback = vivat_category_fallback_image($hotCatSlug, 626, 240, $hotArtId, 'search-2');
                $hotNewsImg = ! empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : $hotFallback; ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="<?= $articleImageZoom ?> rounded-[32px] min-w-0 w-full h-60">
                <div class="<?= $searchPhotoClip32 ?>">
                <img src="<?= htmlspecialchars($hotNewsImg) ?>" data-fallback-url="<?= htmlspecialchars($hotFallback) ?>" alt="<?= htmlspecialchars($hotNewsArt['title'] ?? 'Article') ?>" class="<?= $articleImageZoomImg ?>" loading="lazy">
                <div class="<?= $cardOverlay ?> flex justify-end items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <?php if (! empty($hotNewsArt['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($hotNewsArt['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="line-clamp-5 text-xl font-medium text-white"><?= htmlspecialchars($hotNewsArt['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
                </div>
            </a>
            <?php } ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php if ($artLeft) { ?>
                <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="group relative flex min-w-0 w-full flex-col justify-end gap-[18px] overflow-hidden rounded-[30px] p-6 h-[419px] <?= $cardYellowSurface ?><?= $artLeft2 ? '' : ' sm:col-span-2' ?>">
                    <span class="<?= $cardArrowOnYellow ?>">
                        <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 17L17 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h8v8"/></svg>
                    </span>
                    <div class="flex flex-col min-h-0 gap-2">
                        <?php if (! empty($artLeft['category'])) { ?>
                        <span class="<?= $tagBase ?> <?= $tagCategoryTw['jaune'] ?>"><?= htmlspecialchars($artLeft['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="line-clamp-5 text-xl font-medium text-[#004241]"><?= htmlspecialchars($artLeft['title']) ?></h3>
                        <p class="text-xs font-light text-[#004241]"><?= htmlspecialchars($artLeft['published_at'] ?? '') ?> • <?= (int) ($artLeft['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php } ?>

                <?php if ($artLeft2) { ?>
                <?php $left2CatSlug = $artLeft2['category']['slug'] ?? null;
                    $left2ArtId = $artLeft2['id'] ?? $artLeft2['slug'] ?? null;
                    $left2Fallback = vivat_category_fallback_image($left2CatSlug, 302, 419, $left2ArtId, 'search-3');
                    $artLeft2Img = ! empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : $left2Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="<?= $articleImageZoom ?> rounded-[30px] min-w-0 w-full h-[419px]<?= $artLeft ? '' : ' sm:col-span-2' ?>">
                    <div class="<?= $searchPhotoClip30 ?>">
                    <img src="<?= htmlspecialchars($artLeft2Img) ?>" data-fallback-url="<?= htmlspecialchars($left2Fallback) ?>" alt="<?= htmlspecialchars($artLeft2['title'] ?? 'Article') ?>" class="<?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImageSoft ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($artLeft2['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($artLeft2['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="line-clamp-5 text-xl font-medium text-white"><?= htmlspecialchars($artLeft2['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                    </div>
                </a>
                <?php } ?>
            </div>
        </div>

        <div class="md:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full gap-6">
            <?php foreach ($searchPair as $i => $art) {
                $isDark = ($i % 2 === 0); ?>
            <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="group relative flex min-w-0 w-full flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 h-[198px] <?= $isDark ? $cardGreenSurface : $cardYellowSurface ?>">
                <span class="<?= $isDark ? $cardArrowOnGreen : $cardArrowOnYellow ?>">
                    <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 17L17 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h8v8"/></svg>
                </span>
                <?php if (! empty($art['category'])) { ?>
                <?php $tagVariant = $isDark ? 'vert' : 'jaune'; ?>
                <span class="<?= $tagBase ?> <?= $tagCategoryTw[$tagVariant] ?>"><?= htmlspecialchars($art['category']['name']) ?></span>
                <?php } ?>
                <h3 class="line-clamp-2 text-xl font-medium <?= $isDark ? 'text-white' : 'text-[#004241]' ?>"><?= htmlspecialchars($art['title']) ?></h3>
                <p class="text-xs <?= $isDark ? 'text-white/70' : 'text-[#004241]/70' ?>"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php } ?>

            <div class="<?= $adSurface ?> min-h-[190px]">
                <div class="absolute right-[-32px] top-[-32px] h-36 w-36 rounded-full bg-[#B9D2CC]/60 blur-2xl"></div>
                <div class="absolute bottom-[-44px] left-[-22px] h-32 w-32 rounded-full bg-white/60 blur-2xl"></div>
                <div class="relative z-10 flex h-full min-h-[190px] flex-col justify-between gap-6">
                    <div class="flex items-center justify-between gap-4">
                        <span class="inline-flex w-fit items-center rounded-full border border-white/70 bg-white/55 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-[#004241]/70">
                            Publicite
                        </span>
                        <span class="text-[11px] font-medium uppercase tracking-[0.18em] text-[#004241]/45">Sponsorise</span>
                    </div>

                    <div class="max-w-[420px] space-y-3">
                        <p class="text-sm font-medium uppercase tracking-[0.18em] text-[#004241]/55">Espace partenaire</p>
                        <h3 class="text-[28px] leading-[1.08] font-medium text-[#004241]">
                            Boostez votre visibilite avec un emplacement premium sur Vivat.
                        </h3>
                        <p class="max-w-[360px] text-sm leading-6 text-[#004241]/72">
                            Touchez des lecteurs engages avec une annonce native integree au flux editorial.
                        </p>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <span class="inline-flex items-center rounded-full bg-[#004241] px-5 py-3 text-sm font-medium text-white">
                            Voir l'offre media
                        </span>
                        <span class="text-sm font-medium text-[#004241]/55">vivat.be/ads</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php if ($artRight) { ?>
                <?php $rightCatSlug = $artRight['category']['slug'] ?? null;
                    $rightArtId = $artRight['id'] ?? $artRight['slug'] ?? null;
                    $rightFallback = vivat_category_fallback_image($rightCatSlug, 254, 190, $rightArtId, 'search-4');
                    $artRightImg = ! empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : $rightFallback; ?>
                <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="<?= $articleImageZoom ?> rounded-[30px] min-w-0 w-full h-[419px]<?= $artForFullPhoto1 ? '' : ' sm:col-span-2' ?>">
                    <div class="<?= $searchPhotoClip30 ?>">
                    <img src="<?= htmlspecialchars($artRightImg) ?>" data-fallback-url="<?= htmlspecialchars($rightFallback) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="<?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImagePhoto ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($artRight['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($artRight['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="line-clamp-5 text-xl font-medium text-white"><?= htmlspecialchars($artRight['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                    </div>
                </a>
                <?php } ?>

                <?php if ($artForFullPhoto1) { ?>
                <?php $full1CatSlug = $artForFullPhoto1['category']['slug'] ?? null;
                    $full1ArtId = $artForFullPhoto1['id'] ?? $artForFullPhoto1['slug'] ?? null;
                    $full1Fallback = vivat_category_fallback_image($full1CatSlug, 302, 419, $full1ArtId, 'search-5');
                    $fullPhoto1Img = ! empty($artForFullPhoto1['cover_image_url']) ? $artForFullPhoto1['cover_image_url'] : $full1Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artForFullPhoto1['slug']) ?>" class="<?= $articleImageZoom ?> rounded-[25px] min-w-0 w-full h-[419px]<?= $artRight ? '' : ' sm:col-span-2' ?>">
                    <div class="<?= $searchPhotoClip25 ?>">
                    <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" data-fallback-url="<?= htmlspecialchars($full1Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto1['title'] ?? 'Article') ?>" class="<?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImageSoft ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($artForFullPhoto1['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($artForFullPhoto1['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="line-clamp-5 text-xl font-medium text-white"><?= htmlspecialchars($artForFullPhoto1['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($artForFullPhoto1['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto1['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
            </div>
        </a>
                <?php } ?>
    </div>

            <?php if ($artForFullPhoto2) { ?>
            <?php $full2CatSlug = $artForFullPhoto2['category']['slug'] ?? null;
                $full2ArtId = $artForFullPhoto2['id'] ?? $artForFullPhoto2['slug'] ?? null;
                $full2Fallback = vivat_category_fallback_image($full2CatSlug, 629, 235, $full2ArtId, 'search-6');
                $fullPhoto2Img = ! empty($artForFullPhoto2['cover_image_url']) ? $artForFullPhoto2['cover_image_url'] : $full2Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($artForFullPhoto2['slug']) ?>" class="<?= $articleImageZoom ?> w-full min-w-0 h-[235px] rounded-[30px]">
                <div class="<?= $searchPhotoClip30 ?>">
                <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" data-fallback-url="<?= htmlspecialchars($full2Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto2['title'] ?? 'Article') ?>" class="<?= $articleImageZoomImg ?>" loading="lazy">
                <div class="<?= $overlayImageSoft ?>"></div>
                <div class="<?= $cardOverlay ?> flex items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <?php if (! empty($artForFullPhoto2['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($artForFullPhoto2['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="line-clamp-5 text-xl font-medium text-white"><?= htmlspecialchars($artForFullPhoto2['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($artForFullPhoto2['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto2['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
                </div>
            </a>
            <?php } ?>
        </div>
    </section>
    <?php } elseif ($search_term !== '') { ?>
    <p class="py-12 text-center text-gray-500">Aucun article trouvé pour cette recherche.</p>
    <?php } else { ?>
    <p class="py-12 text-center text-gray-500">Saisissez un terme pour lancer la recherche.</p>
    <?php } ?>

    <?php if ($pagination && $pagination->hasPages()) { ?>
    <nav class="mt-10 flex flex-wrap items-center justify-center gap-3" aria-label="Pagination des résultats de recherche">
        <?php if ($pagination->onFirstPage()) { ?>
        <span class="inline-flex h-12 min-w-12 items-center justify-center rounded-full bg-[#EBF1EF] px-5 text-sm font-medium text-[#004241]/35">Précédent</span>
        <?php } else { ?>
        <a href="<?= htmlspecialchars($pagination->withQueryString()->previousPageUrl()) ?>" class="inline-flex h-12 min-w-12 items-center justify-center rounded-full bg-[#004241] px-5 text-sm font-medium text-white transition hover:opacity-90">Précédent</a>
        <?php } ?>

        <span class="inline-flex h-12 items-center justify-center rounded-full bg-[#FFF0D4] px-5 text-sm font-semibold text-[#004241]">
            Page <?= $pagination->currentPage() ?> sur <?= $pagination->lastPage() ?>
        </span>

        <?php if ($pagination->hasMorePages()) { ?>
        <a href="<?= htmlspecialchars($pagination->withQueryString()->nextPageUrl()) ?>" class="inline-flex h-12 min-w-12 items-center justify-center rounded-full bg-[#004241] px-5 text-sm font-medium text-white transition hover:opacity-90">Suivant</a>
        <?php } else { ?>
        <span class="inline-flex h-12 min-w-12 items-center justify-center rounded-full bg-[#EBF1EF] px-5 text-sm font-medium text-[#004241]/35">Suivant</span>
        <?php } ?>
    </nav>
    <?php } ?>

    <?php if ($search_term !== '' && count($continue_reading_articles) > 0) { ?>
    <?php
    /** Une slide = toujours 4 emplacements (grille) pour remplir la largeur du conteneur — comme le carrousel rubriques. */
    $continueChunks = array_chunk($continue_reading_articles, 4);
        $continueSlidesCount = count($continueChunks);
        $continueSlideWidth = 'flex-[0_0_100%] box-border min-w-0';
        ?>
    <section id="search-continue-section" class="relative z-10 mt-16 min-w-0 w-full overflow-visible" aria-labelledby="search-continue-heading">
        <h2 id="search-continue-heading" class="mb-6 text-3xl font-medium text-[#004241] font-sans">Continuer à lire</h2>
        <div id="search-continue-viewport" class="relative min-w-0 w-full overflow-visible">
            <div class="overflow-hidden">
                <div
                    id="search-continue-track"
                    class="flex will-change-transform transition-transform duration-500 ease-out"
                >
                <?php foreach ($continueChunks as $chunk) { ?>
                <?php
                    $cells = array_values($chunk);
                    $cells = array_pad($cells, 4, null);
                    $cells = array_slice($cells, 0, 4);
                    ?>
                <div class="search-continue-slide grid min-h-0 w-full shrink-0 grid-cols-1 gap-[24px] sm:grid-cols-2 lg:grid-cols-4 <?= $continueSlideWidth ?>">
                    <?php foreach ($cells as $cArt) { ?>
                    <?php if ($cArt === null) { ?>
                    <div class="hidden min-h-0 min-w-0 lg:block" aria-hidden="true"></div>
                    <?php } else { ?>
                    <?php
                        $cCatSlug = $cArt['category']['slug'] ?? null;
                        $cArtId = $cArt['id'] ?? $cArt['slug'] ?? null;
                        $cFallback = vivat_category_fallback_image($cCatSlug, 640, 360, $cArtId, 'search-continue');
                        $cImg = ! empty($cArt['cover_image_url']) ? $cArt['cover_image_url'] : $cFallback;
                        ?>
                    <a
                        href="/articles/<?= htmlspecialchars($cArt['slug']) ?>"
                        class="group relative block min-h-0 min-w-0 h-[360px] overflow-hidden rounded-[30px]"
                    >
                        <img src="<?= htmlspecialchars($cImg) ?>" data-fallback-url="<?= htmlspecialchars($cFallback) ?>" alt="<?= htmlspecialchars($cArt['title'] ?? 'Article') ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent" aria-hidden="true"></div>
                        <div class="vivat-card-overlay z-10 flex flex-col justify-end">
                            <div class="vivat-glass flex w-full flex-col gap-2 rounded-[21px]">
                                <?php if (! empty($cArt['category'])) { ?>
                                <span class="inline-flex w-fit items-center justify-center rounded-full px-[14px] py-[7px] text-sm font-medium font-sans vivat-glass-tag text-white"><?= htmlspecialchars($cArt['category']['name']) ?></span>
                                <?php } ?>
                                <h3 class="line-clamp-4 font-sans text-xl font-medium leading-tight text-white"><?= htmlspecialchars($cArt['title'] ?? '') ?></h3>
                                <p class="font-sans text-xs text-white/80"><?= htmlspecialchars($cArt['published_at'] ?? '') ?> • <?= (int) ($cArt['reading_time'] ?? 0) ?> min</p>
                            </div>
                        </div>
                    </a>
                    <?php } ?>
                    <?php } ?>
                </div>
                <?php } ?>
                </div>
            </div>

            <?php if ($continueSlidesCount > 1) { ?>
            <button type="button" id="search-continue-prev" class="<?= $continueCarouselNavBtn ?> left-0 -translate-x-1/2" aria-label="Articles précédents">
                <svg class="h-6 w-6 flex-shrink-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
            <button type="button" id="search-continue-next" class="<?= $continueCarouselNavBtn ?> right-0 translate-x-1/2" aria-label="Articles suivants">
                <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
            <?php } ?>
        </div>

        <?php if ($continueSlidesCount > 1) { ?>
        <script>
        (function() {
            var track = document.getElementById('search-continue-track');
            var viewport = document.getElementById('search-continue-viewport');
            var nextBtn = document.getElementById('search-continue-next');
            var prevBtn = document.getElementById('search-continue-prev');
            if (!track || !viewport || !nextBtn || !prevBtn) {
                return;
            }

            var slides = document.querySelectorAll('.search-continue-slide');
            var numSlides = slides.length;
            if (numSlides < 2) {
                return;
            }

            var idx = 0;
            var isAnimating = false;
            // Espace à garder entre la dernière carte d'une slide et la première carte de la slide suivante.
            // (les cartes internes ont déjà `gap-[24px]` dans la grille)
            var slideGap = 24;

            function slideOffset(i) {
                return Math.round(i * (viewport.getBoundingClientRect().width + slideGap));
            }

            function goTo(i, noTransition) {
                if (noTransition) {
                    track.style.transition = 'none';
                }
                track.style.transform = 'translate3d(-' + slideOffset(i) + 'px, 0, 0)';
                if (noTransition) {
                    track.offsetHeight;
                    track.style.transition = '';
                }
            }

            function updateButtons() {
                // On garde les boutons visibles comme sur le carrousel "Rubriques".
                // Le fait de les rendre non cliquables aux extrêmes est géré via les gardes `idx <= 0` / `idx >= ...`.
                prevBtn.style.visibility = 'visible';
                nextBtn.style.visibility = 'visible';
            }

            nextBtn.addEventListener('click', function () {
                if (isAnimating || idx >= numSlides - 1) {
                    return;
                }
                isAnimating = true;
                idx++;
                goTo(idx, false);
                updateButtons();
            });

            prevBtn.addEventListener('click', function () {
                if (isAnimating || idx <= 0) {
                    return;
                }
                isAnimating = true;
                idx--;
                goTo(idx, false);
                updateButtons();
            });

            track.addEventListener('transitionend', function (e) {
                if (e.target !== track || e.propertyName !== 'transform') {
                    return;
                }
                isAnimating = false;
            });

            window.addEventListener('resize', function () {
                goTo(idx, true);
                updateButtons();
            });

            goTo(0, true);
            updateButtons();
        })();
        </script>
        <?php } ?>
    </section>
    <?php } ?>

</div>
