    <?php if (count($categories) > 0) { ?>
    <?php
        $desktopFeaturedCategorySlugs = ['mode'];
        $desktopTailSkipSlugs = ['famille', 'sante', 'voyage'];
        $categoriesBySlug = [];
        foreach ($categories as $c) {
            if (! empty($c['slug'])) {
                $categoriesBySlug[strtolower((string) $c['slug'])] = $c;
            }
        }
        $desktopFamilleCategory = $categoriesBySlug['famille'] ?? null;
        $desktopSanteCategory = $categoriesBySlug['sante'] ?? null;
        $desktopVoyageCategory = $categoriesBySlug['voyage'] ?? null;
        $desktopCategoryPanels = [];
        $desktopCategoryBuffer = [];
        foreach ($categories as $category) {
            if (empty($category)) {
                continue;
            }
            $categorySlug = strtolower((string) ($category['slug'] ?? ''));
            if (in_array($categorySlug, $desktopTailSkipSlugs, true)) {
                continue;
            }
            if (in_array($categorySlug, $desktopFeaturedCategorySlugs, true)) {
                if (! empty($desktopCategoryBuffer)) {
                    $desktopCategoryPanels[] = [
                        'type' => 'group',
                        'categories' => $desktopCategoryBuffer,
                    ];
                    $desktopCategoryBuffer = [];
                }
                $desktopCategoryPanels[] = [
                    'type' => 'single',
                    'category' => $category,
                ];

                continue;
            }
            $desktopCategoryBuffer[] = $category;
            if (count($desktopCategoryBuffer) === 3) {
                $desktopCategoryPanels[] = [
                    'type' => 'group',
                    'categories' => $desktopCategoryBuffer,
                ];
                $desktopCategoryBuffer = [];
            }
        }
        if (! empty($desktopCategoryBuffer)) {
            $desktopCategoryPanels[] = [
                'type' => 'group',
                'categories' => $desktopCategoryBuffer,
            ];
        }
        $desktopPairSmallCategories = array_values(array_filter(
            [$desktopFamilleCategory, $desktopSanteCategory],
            static fn ($c): bool => $c !== null && is_array($c)
        ));
        if (count($desktopPairSmallCategories) > 0) {
            $desktopCategoryPanels[] = [
                'type' => 'pair_small',
                'categories' => $desktopPairSmallCategories,
            ];
        }
        if ($desktopVoyageCategory !== null && is_array($desktopVoyageCategory)) {
            $desktopCategoryPanels[] = [
                'type' => 'single',
                'category' => $desktopVoyageCategory,
            ];
        }
        $numSlides = count($desktopCategoryPanels) + 1;
        $tabletNumSlides = count($tabletCarouselSlides);
        $tabletHasLoop = ($tabletNumSlides > 1);
        $isVideoMedia = function (?string $url): bool {
            if (! $url) {
                return false;
            }
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

            return in_array($ext, ['mp4', 'webm', 'mov'], true);
        };
        $mediaAttrs = static function (bool $eager = false): string {
            if (! $eager) {
                return 'loading="lazy" decoding="async"';
            }

            return 'loading="eager" fetchpriority="high" decoding="async"';
        };
        $videoPreload = static function (bool $eager = false): string {
            return $eager ? 'metadata' : 'none';
        };
        ?>
    <!-- Section Rubriques Carrousel -->
    <section id="categories-section" data-categories-section class="relative z-10 mt-[65px] hidden w-full overflow-visible lg:block">
        <div class="relative w-full min-w-0">
        <div id="categories-carousel-viewport" class="w-full min-w-0 overflow-hidden">
            <div id="categories-carousel-track" class="flex gap-6 transition-transform duration-[1100ms] ease-out will-change-transform">
                <div class="categories-carousel-panel relative block min-h-0 min-w-0 flex-[0_0_calc((100%-1.5rem)/2)] overflow-hidden rounded-[30px] h-[524px]">
                    <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="metadata" poster="<?= htmlspecialchars($rubriquesHeroPosterUrl) ?>">
                        <source src="<?= htmlspecialchars($rubriquesHeroVideoUrl) ?>" type="video/mp4">
                    </video>
                    <div class="<?= $overlayRubriqueHero ?>"></div>
                    <div class="pointer-events-none absolute inset-0 z-[2] flex flex-col items-start justify-center p-8">
                        <h2 class="max-w-[85%] text-left text-5xl font-semibold text-white">Découvrez vos rubriques préférées</h2>
                        <p class="mt-2 max-w-[85%] text-left text-2xl text-white/95">Explorez dès maintenant les contenus qui vous correspondent.</p>
                    </div>
                </div>
                <?php foreach ($desktopCategoryPanels as $panelIdx => $panel) {
                    if (($panel['type'] ?? 'group') === 'single') {
                        $desktopSoloCategory = $panel['category'] ?? null;
                        $desktopSoloPoster = $desktopSoloCategory ? (vivat_cloudinary_video_poster_url($desktopSoloCategory['image_url'] ?? null) ?? vivat_category_public_poster_url($desktopSoloCategory['slug'] ?? null)) : null;
                        ?>
                <?php if ($desktopSoloCategory) { ?>
                <a href="/categories/<?= htmlspecialchars($desktopSoloCategory['slug']) ?>" class="categories-carousel-panel group relative block min-h-0 min-w-0 flex-[0_0_calc((100%-1.5rem)/2)] overflow-hidden rounded-[30px] h-[524px] bg-black/20">
                    <?php if (! empty($desktopSoloCategory['image_url'])) { ?>
                    <?php if ($isVideoMedia($desktopSoloCategory['image_url'])) { ?>
                    <video class="categories-rubrique-video absolute inset-0 z-0 h-full w-full object-cover" muted loop playsinline preload="<?= htmlspecialchars($videoPreload($panelIdx === 0)) ?>"<?= $desktopSoloPoster ? ' poster="'.htmlspecialchars($desktopSoloPoster).'"' : '' ?>>
                        <source src="<?= htmlspecialchars($desktopSoloCategory['image_url']) ?>" type="video/mp4">
                    </video>
                    <?php } else { ?>
                    <img src="<?= htmlspecialchars($desktopSoloCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($desktopSoloCategory['name']) ?>" class="absolute inset-0 z-0 h-full w-full object-cover" <?= $mediaAttrs($panelIdx === 0) ?>>
                    <?php } ?>
                    <?php } ?>
                    <div class="<?= $rubriqueDim ?>"></div>
                    <div class="<?= $rubriqueHoverTint ?>"></div>
                    <div class="<?= $rubriqueTitleWrap ?> p-8">
                        <?php $renderRubriqueCategoryLabel($desktopSoloCategory, 'text-center text-4xl font-semibold leading-tight text-white'); ?>
                    </div>
                </a>
                <?php } ?>
                <?php continue;
                    }
                    if (($panel['type'] ?? '') === 'pair_small') {
                        $pairSmallList = $panel['categories'] ?? [];
                        ?>
                <div class="categories-carousel-panel flex min-h-0 min-w-0 flex-[0_0_calc((100%-1.5rem)/2)] flex-col gap-6 h-[524px] justify-center">
                        <?php foreach ($pairSmallList as $pairCat) {
                            if (empty($pairCat['slug'] ?? null)) {
                                continue;
                            }
                            $pairPoster = vivat_cloudinary_video_poster_url($pairCat['image_url'] ?? null) ?? vivat_category_public_poster_url($pairCat['slug'] ?? null);
                            ?>
                    <a href="/categories/<?= htmlspecialchars($pairCat['slug']) ?>" class="group relative block h-[250px] w-full min-h-0 shrink-0 overflow-hidden rounded-[30px] bg-black/20">
                        <?php if (! empty($pairCat['image_url'])) { ?>
                        <?php if ($isVideoMedia($pairCat['image_url'])) { ?>
                        <video class="categories-rubrique-video absolute inset-0 z-0 h-full w-full object-cover" muted loop playsinline preload="<?= htmlspecialchars($videoPreload($panelIdx === 0)) ?>"<?= $pairPoster ? ' poster="'.htmlspecialchars($pairPoster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($pairCat['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($pairCat['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($pairCat['name']) ?>" class="absolute inset-0 z-0 h-full w-full object-cover" <?= $mediaAttrs($panelIdx === 0) ?>>
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <?php $renderRubriqueCategoryLabel($pairCat); ?>
                        </div>
                    </a>
                        <?php } ?>
                </div>
                <?php continue;
                    }
                    $chunk = $panel['categories'] ?? [];
                    $cat1 = $chunk[0] ?? null;
                    $cat2 = $chunk[1] ?? null;
                    $cat3 = $chunk[2] ?? null;
                    $cat1Poster = $cat1 ? (vivat_cloudinary_video_poster_url($cat1['image_url'] ?? null) ?? vivat_category_public_poster_url($cat1['slug'] ?? null)) : null;
                    $cat2Poster = $cat2 ? (vivat_cloudinary_video_poster_url($cat2['image_url'] ?? null) ?? vivat_category_public_poster_url($cat2['slug'] ?? null)) : null;
                    $cat3Poster = $cat3 ? (vivat_cloudinary_video_poster_url($cat3['image_url'] ?? null) ?? vivat_category_public_poster_url($cat3['slug'] ?? null)) : null;
                    $isMirroredPanel = $panelIdx % 2 === 1;
                    $desktopCategoryCards = array_values(array_filter([
                        ['category' => $cat1, 'poster' => $cat1Poster],
                        ['category' => $cat2, 'poster' => $cat2Poster],
                        ['category' => $cat3, 'poster' => $cat3Poster],
                    ], static fn (array $card): bool => ! empty($card['category'])));
                    $desktopCardCount = count($desktopCategoryCards);
                    $tallCard = array_pop($desktopCategoryCards);
                    $smallCards = $desktopCategoryCards;
                    ?>
                <?php if ($desktopCardCount === 2) { ?>
                <div class="categories-carousel-panel min-h-0 min-w-0 flex-[0_0_calc((100%-1.5rem)/2)] flex flex-col gap-6">
                    <?php foreach ([$cat1 ? ['category' => $cat1, 'poster' => $cat1Poster] : null, $cat2 ? ['category' => $cat2, 'poster' => $cat2Poster] : null, $cat3 ? ['category' => $cat3, 'poster' => $cat3Poster] : null] as $stackedCard) { ?>
                    <?php if (! empty($stackedCard['category'])) { ?>
                    <?php $stackedCategory = $stackedCard['category']; ?>
                    <?php $stackedPoster = $stackedCard['poster']; ?>
                    <a href="/categories/<?= htmlspecialchars($stackedCategory['slug']) ?>" class="group relative block h-[250px] w-full min-h-0 overflow-hidden rounded-[30px] bg-black/20">
                        <?php if (! empty($stackedCategory['image_url'])) { ?>
                        <?php if ($isVideoMedia($stackedCategory['image_url'])) { ?>
                        <video class="categories-rubrique-video absolute inset-0 z-0 h-full w-full object-cover" muted loop playsinline preload="<?= htmlspecialchars($videoPreload($panelIdx === 0)) ?>"<?= $stackedPoster ? ' poster="'.htmlspecialchars($stackedPoster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($stackedCategory['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($stackedCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($stackedCategory['name']) ?>" class="absolute inset-0 z-0 h-full w-full object-cover" <?= $mediaAttrs($panelIdx === 0) ?>>
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <?php $renderRubriqueCategoryLabel($stackedCategory); ?>
                                </div>
                            </a>
                    <?php } ?>
                    <?php } ?>
                </div>
                <?php } else { ?>
                <div class="categories-carousel-panel min-h-0 min-w-0 flex-[0_0_calc((100%-1.5rem)/2)] grid grid-cols-2 gap-6 [grid-template-rows:repeat(2,250px)]">
                    <?php if ($isMirroredPanel && ! empty($tallCard['category'])) { ?>
                    <?php $tallCategory = $tallCard['category']; ?>
                    <?php $tallPoster = $tallCard['poster']; ?>
                    <a href="/categories/<?= htmlspecialchars($tallCategory['slug']) ?>" class="<?= $rubriqueTileTall ?>">
                        <?php if (! empty($tallCategory['image_url'])) { ?>
                        <?php if ($isVideoMedia($tallCategory['image_url'])) { ?>
                        <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="<?= htmlspecialchars($videoPreload($panelIdx === 0)) ?>"<?= $tallPoster ? ' poster="'.htmlspecialchars($tallPoster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($tallCategory['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($tallCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($tallCategory['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" <?= $mediaAttrs($panelIdx === 0) ?>>
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <?php $renderRubriqueCategoryLabel($tallCategory); ?>
                                </div>
                            </a>
                    <?php } ?>
                    <div class="row-span-2 flex min-h-0 flex-col gap-6">
                        <?php foreach ($smallCards as $smallCard) { ?>
                        <?php $smallCategory = $smallCard['category']; ?>
                        <?php $smallPoster = $smallCard['poster']; ?>
                        <a href="/categories/<?= htmlspecialchars($smallCategory['slug']) ?>" class="<?= $rubriqueTileSm ?>">
                            <?php if (! empty($smallCategory['image_url'])) { ?>
                            <?php if ($isVideoMedia($smallCategory['image_url'])) { ?>
                            <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="<?= htmlspecialchars($videoPreload($panelIdx === 0)) ?>"<?= $smallPoster ? ' poster="'.htmlspecialchars($smallPoster).'"' : '' ?>>
                                <source src="<?= htmlspecialchars($smallCategory['image_url']) ?>" type="video/mp4">
                            </video>
                            <?php } else { ?>
                            <img src="<?= htmlspecialchars($smallCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($smallCategory['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" <?= $mediaAttrs($panelIdx === 0) ?>>
                            <?php } ?>
                            <?php } ?>
                            <div class="<?= $rubriqueDim ?>"></div>
                            <div class="<?= $rubriqueHoverTint ?>"></div>
                            <div class="<?= $rubriqueTitleWrap ?>">
                                <?php $renderRubriqueCategoryLabel($smallCategory); ?>
                        </div>
                        </a>
                        <?php } ?>
                    </div>
                    <?php if (! $isMirroredPanel && ! empty($tallCard['category'])) { ?>
                    <?php $tallCategory = $tallCard['category']; ?>
                    <?php $tallPoster = $tallCard['poster']; ?>
                    <a href="/categories/<?= htmlspecialchars($tallCategory['slug']) ?>" class="<?= $rubriqueTileTall ?>">
                        <?php if (! empty($tallCategory['image_url'])) { ?>
                        <?php if ($isVideoMedia($tallCategory['image_url'])) { ?>
                        <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="<?= htmlspecialchars($videoPreload($panelIdx === 0)) ?>"<?= $tallPoster ? ' poster="'.htmlspecialchars($tallPoster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($tallCategory['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($tallCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($tallCategory['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" <?= $mediaAttrs($panelIdx === 0) ?>>
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <?php $renderRubriqueCategoryLabel($tallCategory); ?>
                            </div>
                        </a>
                    <?php } ?>
                    </div>
                <?php } ?>
                <?php } ?>
                </div>
        </div>
        <?php if (count($desktopCategoryPanels) > 1) { ?>
        <button type="button" id="categories-carousel-prev" class="<?= $carouselNavBtnEdgeLeft ?>" aria-label="Rubriques précédentes">
            <svg class="h-[26px] w-[26px] flex-shrink-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
        <button type="button" id="categories-carousel-next" class="<?= $carouselNavBtnEdgeRight ?>" aria-label="Rubriques suivantes">
            <svg class="h-[26px] w-[26px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
        <?php } ?>
            </div>
    </section>
    <section id="categories-section-tablet" data-categories-section class="relative z-10 mt-[65px] hidden w-full overflow-visible md:block lg:hidden">
        <div class="relative w-full min-w-0">
        <div id="categories-carousel-tablet-viewport" class="w-full overflow-hidden">
            <div id="categories-carousel-tablet-track" class="flex transition-transform duration-[1100ms] ease-out will-change-transform">
                <?php foreach ($tabletCarouselSlides as $tabletSlide) { ?>
                <div class="categories-carousel-tablet-slide flex min-h-0 min-w-0 flex-shrink-0 items-stretch gap-5 px-6 [contain:layout] <?= $carouselSlideWidth ?>">
                    <?php if ($tabletSlide['type'] === 'hero') { ?>
                    <?php $tabletCategory = $tabletSlide['categories'][0] ?? null; ?>
                    <?php $tabletPoster = $tabletCategory ? (vivat_cloudinary_video_poster_url($tabletCategory['image_url'] ?? null) ?? vivat_category_public_poster_url($tabletCategory['slug'] ?? null)) : null; ?>
                    <div class="relative block min-h-0 min-w-0 flex-[7] overflow-hidden rounded-[30px] h-[420px]">
                        <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="metadata" poster="<?= htmlspecialchars($rubriquesHeroPosterUrl) ?>">
                            <source src="<?= htmlspecialchars($rubriquesHeroVideoUrl) ?>" type="video/mp4">
                        </video>
                        <div class="<?= $overlayRubriqueHero ?>"></div>
                        <div class="pointer-events-none absolute inset-0 z-[2] flex flex-col items-start justify-center p-8">
                            <h2 class="max-w-[92%] text-left text-[2.75rem] font-semibold leading-[1.06] text-white">Découvrez vos rubriques préférées</h2>
                            <p class="mt-3 max-w-[84%] text-left text-[1.05rem] leading-[1.45] text-white/95">Explorez dès maintenant les contenus qui vous correspondent.</p>
                        </div>
                    </div>
                    <?php if ($tabletCategory) { ?>
                    <a href="/categories/<?= htmlspecialchars($tabletCategory['slug']) ?>" class="group relative block h-[420px] min-h-0 min-w-0 flex-[0_0_32%] overflow-hidden rounded-[30px] bg-black/20">
                        <?php if (! empty($tabletCategory['image_url'])) { ?>
                        <?php if ($isVideoMedia($tabletCategory['image_url'])) { ?>
                        <video class="categories-rubrique-tablet-video absolute inset-0 z-0 h-full w-full object-cover" muted loop playsinline preload="metadata"<?= $tabletPoster ? ' poster="'.htmlspecialchars($tabletPoster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($tabletCategory['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($tabletCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($tabletCategory['name']) ?>" class="absolute inset-0 z-0 h-full w-full object-cover" loading="eager" fetchpriority="high" decoding="async">
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <?php $renderRubriqueCategoryLabel($tabletCategory, $rubriqueTitle.' md:text-[17px]'); ?>
                        </div>
                    </a>
                    <?php } ?>
                    <?php } else { ?>
                    <?php foreach ($tabletSlide['categories'] as $tabletCategory) { ?>
                    <?php $tabletPoster = vivat_cloudinary_video_poster_url($tabletCategory['image_url'] ?? null) ?? vivat_category_public_poster_url($tabletCategory['slug'] ?? null); ?>
                    <a href="/categories/<?= htmlspecialchars($tabletCategory['slug']) ?>" class="group relative block h-[420px] min-h-0 min-w-0 flex-1 overflow-hidden rounded-[30px] bg-black/20">
                        <?php if (! empty($tabletCategory['image_url'])) { ?>
                        <?php if ($isVideoMedia($tabletCategory['image_url'])) { ?>
                        <video class="categories-rubrique-tablet-video absolute inset-0 z-0 h-full w-full object-cover" muted loop playsinline preload="<?= htmlspecialchars($videoPreload($tabletHasLoop ? false : true)) ?>"<?= $tabletPoster ? ' poster="'.htmlspecialchars($tabletPoster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($tabletCategory['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($tabletCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($tabletCategory['name']) ?>" class="absolute inset-0 z-0 h-full w-full object-cover" <?= $mediaAttrs(false) ?>>
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <?php $renderRubriqueCategoryLabel($tabletCategory, $rubriqueTitle.' md:text-[17px]'); ?>
                        </div>
                    </a>
                    <?php } ?>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php if ($tabletHasLoop && count($tabletCarouselSlides) > 0) { ?>
                <?php $tabletCloneSlide = $tabletCarouselSlides[0]; ?>
                <div class="categories-carousel-tablet-slide categories-carousel-tablet-clone flex min-h-0 min-w-0 flex-shrink-0 items-stretch gap-5 px-6 [contain:layout] <?= $carouselSlideWidth ?>" aria-hidden="true">
                    <?php if ($tabletCloneSlide['type'] === 'hero') { ?>
                    <?php $tabletCloneCategory = $tabletCloneSlide['categories'][0] ?? null; ?>
                    <?php $tabletClonePoster = $tabletCloneCategory ? (vivat_cloudinary_video_poster_url($tabletCloneCategory['image_url'] ?? null) ?? vivat_category_public_poster_url($tabletCloneCategory['slug'] ?? null)) : null; ?>
                    <div class="relative block min-h-0 min-w-0 flex-[7] overflow-hidden rounded-[30px] h-[420px]">
                        <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="metadata" poster="<?= htmlspecialchars($rubriquesHeroPosterUrl) ?>">
                            <source src="<?= htmlspecialchars($rubriquesHeroVideoUrl) ?>" type="video/mp4">
                        </video>
                        <div class="<?= $overlayRubriqueHero ?>"></div>
                        <div class="pointer-events-none absolute inset-0 z-[2] flex flex-col items-start justify-center p-8">
                            <h2 class="max-w-[92%] text-left text-[2.75rem] font-semibold leading-[1.06] text-white">Découvrez vos rubriques préférées</h2>
                            <p class="mt-3 max-w-[84%] text-left text-[1.05rem] leading-[1.45] text-white/95">Explorez dès maintenant les contenus qui vous correspondent.</p>
                        </div>
                    </div>
                    <?php if ($tabletCloneCategory) { ?>
                    <a href="/categories/<?= htmlspecialchars($tabletCloneCategory['slug']) ?>" class="group relative block h-[420px] min-h-0 min-w-0 flex-[0_0_32%] overflow-hidden rounded-[30px] bg-black/20">
                        <?php if (! empty($tabletCloneCategory['image_url'])) { ?>
                        <?php if ($isVideoMedia($tabletCloneCategory['image_url'])) { ?>
                        <video class="categories-rubrique-tablet-video absolute inset-0 z-0 h-full w-full object-cover" muted loop playsinline preload="none"<?= $tabletClonePoster ? ' poster="'.htmlspecialchars($tabletClonePoster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($tabletCloneCategory['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($tabletCloneCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($tabletCloneCategory['name']) ?>" class="absolute inset-0 z-0 h-full w-full object-cover" loading="lazy" decoding="async">
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <?php $renderRubriqueCategoryLabel($tabletCloneCategory, $rubriqueTitle.' md:text-[17px]'); ?>
                        </div>
                    </a>
                    <?php } ?>
                    <?php } else { ?>
                    <?php foreach ($tabletCloneSlide['categories'] as $tabletCloneCategory) { ?>
                    <?php $tabletClonePoster = vivat_cloudinary_video_poster_url($tabletCloneCategory['image_url'] ?? null) ?? vivat_category_public_poster_url($tabletCloneCategory['slug'] ?? null); ?>
                    <a href="/categories/<?= htmlspecialchars($tabletCloneCategory['slug']) ?>" class="group relative block h-[420px] min-h-0 min-w-0 flex-1 overflow-hidden rounded-[30px] bg-black/20">
                        <?php if (! empty($tabletCloneCategory['image_url'])) { ?>
                        <?php if ($isVideoMedia($tabletCloneCategory['image_url'])) { ?>
                        <video class="categories-rubrique-tablet-video absolute inset-0 z-0 h-full w-full object-cover" muted loop playsinline preload="none"<?= $tabletClonePoster ? ' poster="'.htmlspecialchars($tabletClonePoster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($tabletCloneCategory['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($tabletCloneCategory['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($tabletCloneCategory['name']) ?>" class="absolute inset-0 z-0 h-full w-full object-cover" loading="lazy" decoding="async">
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <?php $renderRubriqueCategoryLabel($tabletCloneCategory, $rubriqueTitle.' md:text-[17px]'); ?>
                        </div>
                    </a>
                    <?php } ?>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php if ($tabletNumSlides > 1) { ?>
        <button type="button" id="categories-carousel-tablet-prev" class="<?= $carouselNavBtnEdgeLeft ?>" aria-label="Rubriques précédentes">
            <svg class="h-[26px] w-[26px] flex-shrink-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
        <button type="button" id="categories-carousel-tablet-next" class="<?= $carouselNavBtnEdgeRight ?>" aria-label="Rubriques suivantes">
            <svg class="h-[26px] w-[26px] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
        <?php } ?>
        </div>
    </section>
    <script>
    (function() {
        var section = document.getElementById('categories-section');
        var viewport = document.getElementById('categories-carousel-viewport');
        if (!section || !viewport) {
            return;
        }
        function getVideos() {
            return Array.prototype.slice.call(section.querySelectorAll('video.categories-rubrique-video'));
        }
        if (!getVideos().length) {
            return;
        }
        var transitionActive = false;

        function pauseRubriqueVideos() {
            getVideos().forEach(function (v) {
                v.pause();
            });
        }

        function syncRubriqueVideos() {
            var vr = viewport.getBoundingClientRect();
            getVideos().forEach(function (v) {
                var r = v.getBoundingClientRect();
                if (r.width < 1 || r.height < 1) {
                    v.pause();

                    return;
                }
                var ix = Math.max(0, Math.min(r.right, vr.right) - Math.max(r.left, vr.left));
                var iy = Math.max(0, Math.min(r.bottom, vr.bottom) - Math.max(r.top, vr.top));
                var interArea = ix * iy;
                var ratio = interArea / (r.width * r.height);
                if (ratio >= 0.08) {
                    v.play().catch(function () {});
                } else {
                    v.pause();
                }
            });
        }

        window.VivatCategoryCarouselMedia = {
            setTransitionActive: function (on) {
                transitionActive = !!on;
            },
            pauseRubriqueVideos: pauseRubriqueVideos,
            syncRubriqueVideos: syncRubriqueVideos,
        };

        if (!('IntersectionObserver' in window)) {
            syncRubriqueVideos();

            return;
        }
        var io = new IntersectionObserver(function (entries) {
            if (transitionActive) {
                return;
            }
            entries.forEach(function (entry) {
                var v = entry.target;
                if (entry.isIntersecting && entry.intersectionRatio >= 0.08) {
                    v.play().catch(function () {});
                } else {
                    v.pause();
                }
            });
        }, { root: viewport, threshold: [0, 0.08, 0.2] });
        getVideos().forEach(function (v) {
            io.observe(v);
        });
    })();
    </script>
    <script>
    (function() {
        var section = document.getElementById('categories-section-tablet');
        var viewport = document.getElementById('categories-carousel-tablet-viewport');
        if (!section || !viewport) {
            return;
        }
        var videos = section.querySelectorAll('video.categories-rubrique-tablet-video');
        if (!videos.length) {
            return;
        }
        var transitionActive = false;

        function pauseRubriqueVideos() {
            videos.forEach(function (video) {
                video.pause();
            });
        }

        function syncRubriqueVideos() {
            var viewportRect = viewport.getBoundingClientRect();
            videos.forEach(function (video) {
                var videoRect = video.getBoundingClientRect();
                if (videoRect.width < 1 || videoRect.height < 1) {
                    video.pause();

                    return;
                }
                var intersectionX = Math.max(0, Math.min(videoRect.right, viewportRect.right) - Math.max(videoRect.left, viewportRect.left));
                var intersectionY = Math.max(0, Math.min(videoRect.bottom, viewportRect.bottom) - Math.max(videoRect.top, viewportRect.top));
                var ratio = (intersectionX * intersectionY) / (videoRect.width * videoRect.height);

                if (ratio >= 0.08) {
                    video.play().catch(function () {});
                } else {
                    video.pause();
                }
            });
        }

        window.VivatCategoryCarouselTabletMedia = {
            setTransitionActive: function (active) {
                transitionActive = !!active;
            },
            pauseRubriqueVideos: pauseRubriqueVideos,
            syncRubriqueVideos: syncRubriqueVideos,
        };

        if (!('IntersectionObserver' in window)) {
            syncRubriqueVideos();

            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            if (transitionActive) {
                return;
            }
            entries.forEach(function (entry) {
                var video = entry.target;
                if (entry.isIntersecting && entry.intersectionRatio >= 0.08) {
                    video.play().catch(function () {});
                } else {
                    video.pause();
                }
            });
        }, { root: viewport, threshold: [0, 0.08, 0.2] });

        videos.forEach(function (video) {
            observer.observe(video);
        });
    })();
    </script>
    <?php if ($numSlides > 1) { ?>
    <script>
    (function() {
        var track = document.getElementById('categories-carousel-track');
        var viewport = document.getElementById('categories-carousel-viewport');
        var nextBtn = document.getElementById('categories-carousel-next');
        var prevBtn = document.getElementById('categories-carousel-prev');
        if (!track || !viewport || !nextBtn || !prevBtn) return;
        var originalPanels = Array.prototype.slice.call(track.querySelectorAll('.categories-carousel-panel'));
        if (originalPanels.length < 2) return;
        var firstClone = originalPanels[0].cloneNode(true);
        var lastClone = originalPanels[originalPanels.length - 1].cloneNode(true);
        firstClone.classList.add('categories-carousel-panel-clone');
        firstClone.setAttribute('aria-hidden', 'true');
        lastClone.classList.add('categories-carousel-panel-clone');
        lastClone.setAttribute('aria-hidden', 'true');
        track.insertBefore(lastClone, track.firstChild);
        track.appendChild(firstClone);
        if (originalPanels.length >= 2) {
            var secondClone = originalPanels[1].cloneNode(true);
            secondClone.classList.add('categories-carousel-panel-clone');
            secondClone.setAttribute('aria-hidden', 'true');
            track.appendChild(secondClone);
        }
        var panels = Array.prototype.slice.call(track.querySelectorAll('.categories-carousel-panel'));
        var realCount = originalPanels.length;
        var idx = 1;
        var isAnimating = false;
        var mediaApi = window.VivatCategoryCarouselMedia;

        function beforeSlideAnimation() {
            if (mediaApi) {
                mediaApi.setTransitionActive(true);
                mediaApi.pauseRubriqueVideos();
            }
        }

        function afterSlideSettled() {
            if (mediaApi) {
                mediaApi.setTransitionActive(false);
                mediaApi.syncRubriqueVideos();
            }
        }

        function panelOffset(i) {
            var panel = panels[i];

            return panel ? Math.round(panel.offsetLeft) : 0;
        }

        function goTo(i, noTransition) {
            if (noTransition) {
                track.style.transition = 'none';
            }
            track.style.transform = 'translate3d(-' + panelOffset(i) + 'px, 0, 0)';
            if (noTransition) {
                track.offsetHeight;
                track.style.transition = '';
            }
        }
        goTo(idx, true);
        window.addEventListener('resize', function() {
            goTo(idx, true);
            requestAnimationFrame(function () {
                if (mediaApi) {
                    mediaApi.syncRubriqueVideos();
                }
            });
        });
        track.addEventListener('transitionend', function (e) {
            if (e.target !== track || e.propertyName !== 'transform') {
                return;
            }
            if (idx === 0) {
                idx = realCount;
                goTo(idx, true);
            } else if (idx === realCount + 1) {
                idx = 1;
                goTo(idx, true);
            }
            isAnimating = false;
            afterSlideSettled();
        });
        nextBtn.addEventListener('click', function() {
            if (isAnimating) return;
            beforeSlideAnimation();
            isAnimating = true;
            idx++;
            goTo(idx, false);
        });
        prevBtn.addEventListener('click', function() {
            if (isAnimating) return;
            beforeSlideAnimation();
            isAnimating = true;
            idx--;
            goTo(idx, false);
        });
    })();
    </script>
    <?php } ?>
    <?php if ($tabletNumSlides > 1) { ?>
    <script>
    (function() {
        var track = document.getElementById('categories-carousel-tablet-track');
        var viewport = document.getElementById('categories-carousel-tablet-viewport');
        var nextBtn = document.getElementById('categories-carousel-tablet-next');
        var prevBtn = document.getElementById('categories-carousel-tablet-prev');
        var slides = document.querySelectorAll('.categories-carousel-tablet-slide');
        if (!track || !viewport || !nextBtn || !prevBtn || slides.length < 2) {
            return;
        }

        var total = slides.length;
        var realCount = total - (document.querySelector('.categories-carousel-tablet-clone') ? 1 : 0);
        var idx = 0;
        var isAnimating = false;
        var mediaApi = window.VivatCategoryCarouselTabletMedia;

        function beforeSlideAnimation() {
            if (mediaApi) {
                mediaApi.setTransitionActive(true);
                mediaApi.pauseRubriqueVideos();
            }
        }

        function afterSlideSettled() {
            if (mediaApi) {
                mediaApi.setTransitionActive(false);
                mediaApi.syncRubriqueVideos();
            }
        }

        function slideOffset(index) {
            return Math.round(index * viewport.getBoundingClientRect().width);
        }

        function goTo(index, noTransition) {
            if (noTransition) {
                track.style.transition = 'none';
            }
            track.style.transform = 'translate3d(-' + slideOffset(index) + 'px, 0, 0)';
            if (noTransition) {
                track.offsetHeight;
                track.style.transition = '';
            }
        }

        window.addEventListener('resize', function() {
            goTo(idx, true);
            requestAnimationFrame(function () {
                if (mediaApi) {
                    mediaApi.syncRubriqueVideos();
                }
            });
        });

        track.addEventListener('transitionend', function (event) {
            if (event.target !== track || event.propertyName !== 'transform') {
                return;
            }

            if (idx === total 1) {
                isAnimating = false;
                idx = 0;
                goTo(0, true);
                afterSlideSettled();
            } else {
                isAnimating = false;
                afterSlideSettled();
            }
        });

        nextBtn.addEventListener('click', function() {
            if (isAnimating) {
                return;
            }
            beforeSlideAnimation();
            isAnimating = true;
            idx++;
            goTo(idx, false);
        });

        prevBtn.addEventListener('click', function() {
            if (isAnimating) {
                return;
            }
            beforeSlideAnimation();
            isAnimating = true;
            if (idx === 0) {
                idx = total 1;
                goTo(idx, true);
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        idx = realCount 1;
                        goTo(idx, false);
                    });
                });
            } else {
                idx--;
                goTo(idx, false);
            }
        });
    })();
    </script>

    </script>
    <?php } ?>
    <?php } ?>
