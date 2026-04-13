<style>
    .vivat-writer-cta-link,
    .vivat-writer-cta-link:focus,
    .vivat-writer-cta-link:focus-visible,
    .vivat-writer-cta-link:active {
        outline: none !important;
        box-shadow: none !important;
        border: 0 !important;
        -webkit-tap-highlight-color: transparent;
    }
</style>

<!-- Bandeau pub tablette uniquement (md) -->
<div class="hidden md:block lg:hidden w-full mb-6">
    <div class="rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm flex items-center justify-center mx-auto w-full max-w-[728px] h-[90px]">
        Publicité 728×90
    </div>
</div>

<!-- Grille tablette dédiée (visible md uniquement) — items-stretch + h-full évite le « trou » sous h0 quand la colonne droite est plus haute -->
<div data-home-hero class="hidden md:grid lg:hidden grid-cols-8 items-stretch gap-6 [grid-auto-rows:minmax(0,auto)]">
    <?php if ($h0) { ?>
    <a href="/articles/<?= htmlspecialchars($h0['slug']) ?>" class="<?= $articleImageZoom ?> relative col-span-5 block min-h-[420px] h-full w-full overflow-hidden rounded-[30px]">
        <img src="<?= htmlspecialchars($h0Img) ?>" data-fallback-url="<?= htmlspecialchars($h0Fallback) ?>" alt="<?= htmlspecialchars($h0['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="eager">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="<?= $cardOverlay ?> flex items-end">
            <div class="<?= $glassBox ?> w-full">
                <div class="<?= $topNewsContentMotion ?>">
                <span class="<?= $tagClass ?> <?= $tagTopNews ?>">Top news</span>
                <h2 class="font-semibold text-white line-clamp-6 text-2xl"><?= htmlspecialchars($h0['title'] ?? '') ?></h2>
                <?php if (! empty($h0['excerpt'])) { ?>
                <p class="<?= $topNewsExcerptReveal ?>"><?= htmlspecialchars($h0['excerpt']) ?></p>
                <?php } ?>
                <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($h0['published_at'] ?? '') ?> • <?= (int) ($h0['reading_time'] ?? 0) ?> min</p>
                </div>
            </div>
                </div>
            </a>
    <?php } ?>

    <div class="col-span-3 flex h-full min-h-0 min-w-0 flex-col gap-4 self-stretch">
        <?php if ($h2) { ?>
        <a href="/articles/<?= htmlspecialchars($h2['slug']) ?>" class="group relative flex h-[200px] max-h-[200px] w-full flex-none flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 <?= $cardGreenSurface ?>">
            <span class="<?= $cardArrowOnGreen ?>">
                <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </span>
            <div class="<?= $heroColorCardContentMotion ?>">
                <?php if (! empty($h2['category'])) { ?>
                <span class="<?= $tagClass ?> <?= $tagOnGreenCard ?>"><?= htmlspecialchars($h2['category']['name']) ?></span>
                <?php } ?>
                <h3 class="<?= $heroColorCardTitleCompact ?> text-white line-clamp-3"><?= htmlspecialchars($h2['title']) ?></h3>
                <?php if (! empty($h2['excerpt'])) { ?>
                <p class="<?= $heroColorCardExcerptRevealOnDark ?>"><?= htmlspecialchars($h2['excerpt']) ?></p>
                <?php } ?>
                <p class="text-white/70 text-xs"><?= htmlspecialchars($h2['published_at'] ?? '') ?> • <?= (int) ($h2['reading_time'] ?? 0) ?> min</p>
            </div>
        </a>
        <?php } ?>

        <?php if ($h3) { ?>
        <a href="/articles/<?= htmlspecialchars($h3['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[200px] max-h-[200px] w-full flex-none overflow-hidden rounded-[30px]">
            <img src="<?= htmlspecialchars($h3Img) ?>" data-fallback-url="<?= htmlspecialchars($h3Fallback) ?>" alt="<?= htmlspecialchars($h3['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
            <div class="<?= $overlayImageSoft ?>"></div>
            <div class="<?= $cardOverlay ?> flex items-end">
                <div class="<?= $glassBox ?> w-full">
                    <?php if (! empty($h3['category'])) { ?>
                    <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($h3['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="font-semibold text-white line-clamp-5 text-lg"><?= htmlspecialchars($h3['title'] ?? '') ?></h3>
                    <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($h3['published_at'] ?? '') ?> • <?= (int) ($h3['reading_time'] ?? 0) ?> min</p>
                </div>
                </div>
            </a>
        <?php } ?>
    </div>

    <?php if ($h4) { ?>
    <a href="/articles/<?= htmlspecialchars($h4['slug']) ?>" class="group relative col-span-5 flex h-[280px] max-h-[280px] w-full flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 <?= $cardYellowSurface ?>">
        <span class="<?= $cardArrowOnYellow ?>">
            <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </span>
        <div class="<?= $heroColorCardContentMotion ?>">
            <?php if (! empty($h4['category'])) { ?>
            <span class="<?= $tagClass ?> <?= $tagOnYellowCard ?>"><?= htmlspecialchars($h4['category']['name']) ?></span>
            <?php } ?>
            <h3 class="<?= $heroColorCardTitleWide ?> text-[#004241] line-clamp-3"><?= htmlspecialchars($h4['title']) ?></h3>
            <?php if (! empty($h4['excerpt'])) { ?>
            <p class="<?= $heroColorCardExcerptRevealOnLight ?>"><?= htmlspecialchars($h4['excerpt']) ?></p>
            <?php } ?>
            <p class="text-[#004241]/70 text-xs"><?= htmlspecialchars($h4['published_at'] ?? '') ?> • <?= (int) ($h4['reading_time'] ?? 0) ?> min</p>
        </div>
    </a>
    <?php } ?>

    <?php if ($h1) { ?>
    <a href="/articles/<?= htmlspecialchars($h1['slug']) ?>" class="<?= $articleImageZoom ?> relative col-span-3 block min-h-[280px] h-full w-full overflow-hidden rounded-[30px]">
        <img src="<?= htmlspecialchars($h1Img) ?>" data-fallback-url="<?= htmlspecialchars($h1Fallback) ?>" alt="<?= htmlspecialchars($h1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
        <div class="<?= $overlayImageSoft ?>"></div>
        <div class="<?= $cardOverlay ?> flex items-end">
            <div class="<?= $glassBox ?> w-full">
                <?php if (! empty($h1['category'])) { ?>
                <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($h1['category']['name']) ?></span>
                <?php } ?>
                <h3 class="font-semibold text-white line-clamp-5 text-lg"><?= htmlspecialchars($h1['title'] ?? '') ?></h3>
                <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($h1['published_at'] ?? '') ?> • <?= (int) ($h1['reading_time'] ?? 0) ?> min</p>
            </div>
                </div>
            </a>
    <?php } ?>

    <div class="col-span-8 <?= $writerCtaBanner ?>">
        <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="absolute inset-0 z-[1] rounded-[30px]" aria-label="<?= htmlspecialchars($writer_cta_title) ?> <?= htmlspecialchars($writer_cta_label) ?>"></a>
        <div class="<?= $writerCtaTextWrap ?> pointer-events-none relative z-[2]">
            <p class="<?= $writerCtaTitleLarge ?>"><?= htmlspecialchars($writer_cta_title) ?></p>
            <?php if (trim((string) $writer_cta_subtitle) !== '') { ?>
            <p class="<?= $writerCtaSubtitleLarge ?>"><?= htmlspecialchars($writer_cta_subtitle) ?></p>
            <?php } ?>
        </div>
        <span class="<?= $writerCtaIconBtn ?>" aria-hidden="true">
            <span class="absolute inset-0 rounded-[30px] bg-[#004241] opacity-0 transition-opacity duration-300 ease-out group-hover:opacity-100"></span>
            <svg class="relative z-[1] h-[26px] w-[26px] transition-colors duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:text-[#EBF1EF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </span>
    </div>
</div>

<!-- Grille principale : mobile 1 col, desktop lg 12 cols -->
<div class="flex w-full flex-col">
    <div data-home-hero class="grid grid-cols-1 gap-[18px] md:hidden lg:grid lg:grid-cols-12 lg:gap-6 lg:items-stretch">

        <!-- Colonne gauche: Top news + Standard 2 | lg: enfants directement sur la grille ; xl: colonne empilée -->
        <div class="flex flex-col gap-[18px] lg:contents xl:col-span-5 xl:flex xl:flex-col xl:gap-6">
            <?php if ($h0) { ?>
            <a href="/articles/<?= htmlspecialchars($h0['slug']) ?>" class="<?= $articleImageZoom ?> block h-[438px] w-full overflow-hidden rounded-[30px] relative lg:col-span-5 lg:row-start-1 lg:max-w-none">
                <img src="<?= htmlspecialchars($h0Img) ?>" data-fallback-url="<?= htmlspecialchars($h0Fallback) ?>" alt="<?= htmlspecialchars($h0['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="eager">
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="<?= $cardOverlay ?> flex items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <div class="<?= $topNewsContentMotion ?>">
                        <span class="<?= $tagClass ?> <?= $tagTopNews ?>">Top news</span>
                        <h2 class="font-semibold text-white line-clamp-4 text-[32px] max-sm:text-2xl sm:line-clamp-6"><?= htmlspecialchars($h0['title'] ?? '') ?></h2>
                        <?php if (! empty($h0['excerpt'])) { ?>
                        <p class="<?= $topNewsExcerptReveal ?>"><?= htmlspecialchars($h0['excerpt']) ?></p>
                        <?php } ?>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($h0['published_at'] ?? '') ?> • <?= (int) ($h0['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </div>
            </a>
            <?php } ?>

            <?php if ($h4) { ?>
            <a href="/articles/<?= htmlspecialchars($h4['slug']) ?>" class="group relative flex h-[280px] max-h-[280px] w-full flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 lg:col-span-5 lg:row-start-2 lg:max-w-none lg:max-h-[280px] <?= $cardYellowSurface ?>">
                <span class="<?= $cardArrowOnYellow ?>">
                    <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
                <div class="<?= $heroColorCardContentMotion ?>">
                    <?php if (! empty($h4['category'])) { ?>
                    <span class="<?= $tagClass ?> <?= $tagOnYellowCard ?>"><?= htmlspecialchars($h4['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="<?= $heroColorCardTitleWide ?> text-[#004241] line-clamp-3"><?= htmlspecialchars($h4['title']) ?></h3>
                    <?php if (! empty($h4['excerpt'])) { ?>
                    <p class="<?= $heroColorCardExcerptRevealOnLight ?>"><?= htmlspecialchars($h4['excerpt']) ?></p>
                    <?php } ?>
                    <p class="text-[#004241]/70 text-xs"><?= htmlspecialchars($h4['published_at'] ?? '') ?> • <?= (int) ($h4['reading_time'] ?? 0) ?> min</p>
                </div>
            </a>
            <?php } ?>
        </div>

        <!-- Colonne droite: 3 cartes équivalentes (h1, h2, h3) — grille 3 lignes égales, gap 24px -->
        <div class="flex flex-col gap-[18px] lg:col-span-7 lg:row-span-2 lg:grid lg:grid-rows-3 lg:min-h-0 lg:self-stretch lg:h-full lg:gap-6 xl:col-span-4 xl:row-auto xl:row-span-1">
            <?php if ($h1) { ?>
            <a href="/articles/<?= htmlspecialchars($h1['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[248px] w-full min-h-0 overflow-hidden rounded-[30px] lg:h-full lg:min-h-0">
                <img src="<?= htmlspecialchars($h1Img) ?>" data-fallback-url="<?= htmlspecialchars($h1Fallback) ?>" alt="<?= htmlspecialchars($h1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                <div class="<?= $overlayImageSoft ?>"></div>
                <div class="<?= $cardOverlay ?> flex items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <?php if (! empty($h1['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($h1['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="font-semibold text-white line-clamp-4 text-lg sm:line-clamp-5"><?= htmlspecialchars($h1['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($h1['published_at'] ?? '') ?> • <?= (int) ($h1['reading_time'] ?? 0) ?> min</p>
            </div>
                </div>
            </a>
            <?php } ?>

            <?php if ($h2) { ?>
            <a href="/articles/<?= htmlspecialchars($h2['slug']) ?>" class="group relative flex h-[248px] max-h-[248px] w-full min-h-0 flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 lg:h-full lg:max-h-[248px] <?= $cardGreenSurface ?>">
                <span class="<?= $cardArrowOnGreen ?>">
                    <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                <div class="<?= $heroColorCardContentMotion ?>">
                    <?php if (! empty($h2['category'])) { ?>
                    <span class="<?= $tagClass ?> <?= $tagOnGreenCard ?>"><?= htmlspecialchars($h2['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="<?= $heroColorCardTitleCompact ?> text-white line-clamp-3"><?= htmlspecialchars($h2['title']) ?></h3>
                    <?php if (! empty($h2['excerpt'])) { ?>
                    <p class="<?= $heroColorCardExcerptRevealOnDark ?>"><?= htmlspecialchars($h2['excerpt']) ?></p>
                    <?php } ?>
                    <p class="text-white/70 text-xs"><?= htmlspecialchars($h2['published_at'] ?? '') ?> • <?= (int) ($h2['reading_time'] ?? 0) ?> min</p>
                </div>
            </a>
            <?php } ?>

            <?php if ($h3) { ?>
            <a href="/articles/<?= htmlspecialchars($h3['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[248px] w-full min-h-0 overflow-hidden rounded-[30px] lg:h-full">
                <img src="<?= htmlspecialchars($h3Img) ?>" data-fallback-url="<?= htmlspecialchars($h3Fallback) ?>" alt="<?= htmlspecialchars($h3['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                <div class="<?= $overlayImageSoft ?>"></div>
                <div class="<?= $cardOverlay ?> flex items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <?php if (! empty($h3['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($h3['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="font-semibold text-white line-clamp-4 text-lg sm:line-clamp-5"><?= htmlspecialchars($h3['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($h3['published_at'] ?? '') ?> • <?= (int) ($h3['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php } ?>
        </div>

        <!-- CTA rédacteur : pleine largeur sous le bloc hero (lg / 1024px seulement même variante que mobile / tablette) -->
        <div class="<?= $writerCtaBanner ?> hidden w-full items-center lg:col-span-12 lg:row-start-3 lg:flex xl:hidden">
            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="absolute inset-0 z-[1] rounded-[30px] <?= $writerCtaFocusReset ?>" aria-label="<?= htmlspecialchars($writer_cta_title) ?> <?= htmlspecialchars($writer_cta_label) ?>"></a>
            <div class="<?= $writerCtaTextWrap ?> pointer-events-none relative z-[2]">
                <p class="<?= $writerCtaTitleLarge ?>"><?= htmlspecialchars($writer_cta_title) ?></p>
                <?php if (trim((string) $writer_cta_subtitle) !== '') { ?>
                <p class="<?= $writerCtaSubtitleLarge ?>"><?= htmlspecialchars($writer_cta_subtitle) ?></p>
                <?php } ?>
            </div>
            <span class="<?= $writerCtaIconBtn ?>" aria-hidden="true">
                <span class="absolute inset-0 rounded-[30px] bg-[#004241] opacity-0 transition-opacity duration-300 ease-out group-hover:opacity-100"></span>
                <svg class="relative z-[1] h-[26px] w-[26px] transition-colors duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:text-[#EBF1EF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </span>
        </div>

        <!-- Colonne pub + CTA : visible xl+ seulement -->
        <div class="hidden xl:flex xl:col-span-3 flex-col gap-6">
            <div class="flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm w-full xl:max-w-[300px] h-[600px] items-center justify-center">
                Espace publicitaire
            </div>
            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="<?= $writerCtaBannerSidebar ?> relative <?= $writerCtaFocusReset ?>">
                <div class="<?= $writerCtaTextWrap ?>">
                    <p class="<?= $writerCtaTitle ?>"><?= htmlspecialchars($writer_cta_title) ?></p>
                    <?php if (trim((string) $writer_cta_subtitle) !== '') { ?>
                    <p class="<?= $writerCtaSubtitle ?>"><?= htmlspecialchars($writer_cta_subtitle) ?></p>
                    <?php } ?>
                </div>
                <span class="<?= $writerCtaIconBtn ?>" aria-hidden="true">
                    <span class="absolute inset-0 rounded-[30px] bg-[#004241] opacity-0 transition-opacity duration-300 ease-out group-hover:opacity-100"></span>
                    <svg class="relative z-[1] h-[26px] w-[26px] transition-colors duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:text-[#EBF1EF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
            </a>
    </div>

        <!-- CTA rédacteur mobile (même variante typo que tablette / lg) -->
        <div class="<?= $writerCtaBanner ?> relative w-full lg:hidden">
            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="absolute inset-0 z-[1] rounded-[30px] <?= $writerCtaFocusReset ?>" aria-label="<?= htmlspecialchars($writer_cta_title) ?> <?= htmlspecialchars($writer_cta_label) ?>"></a>
            <div class="<?= $writerCtaTextWrap ?> pointer-events-none relative z-[2]">
                <p class="<?= $writerCtaTitleLarge ?>"><?= htmlspecialchars($writer_cta_title) ?></p>
                <?php if (trim((string) $writer_cta_subtitle) !== '') { ?>
                <p class="<?= $writerCtaSubtitleLarge ?>"><?= htmlspecialchars($writer_cta_subtitle) ?></p>
                <?php } ?>
            </div>
            <span class="<?= $writerCtaIconBtn ?>" aria-hidden="true">
                <span class="absolute inset-0 rounded-[30px] bg-[#004241] opacity-0 transition-opacity duration-300 ease-out group-hover:opacity-100"></span>
                <svg class="relative z-[1] h-[26px] w-[26px] transition-colors duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:text-[#EBF1EF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </span>
        </div>
    </div>

    <!-- Bannière pub -->
    <div class="flex rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm overflow-hidden items-center justify-center w-[970px] max-w-full h-[250px] mt-[65px] mx-auto">
        Espace publicitaire (bannière)
                </div>
