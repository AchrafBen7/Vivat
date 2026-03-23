<?php
$highlight = $highlight ?? [];
$featured = $featured ?? [];
$latest = $latest ?? [];
$pagination = $pagination ?? null;
$categories = $categories ?? [];
$writer_signup_url = $writer_signup_url ?? '#';
$writer_dashboard_url = $writer_dashboard_url ?? '#';
$writer_cta_url = $writer_cta_url ?? $writer_signup_url;
$writer_cta_label = $writer_cta_label ?? 'Rédigez un article';
$writer_cta_description = $writer_cta_description ?? 'Écrivez sur Vivat. Votre voix compte.';
$writerCtaLines = preg_split('/\.\s+/', trim($writer_cta_description), 2);
$rubriquesHeroVideoUrl = 'https://res.cloudinary.com/dfcy6isdu/video/upload/v1774257142/rubriques_h5dyvo.mp4';
$rubriquesHeroPosterUrl = vivat_cloudinary_video_poster_url($rubriquesHeroVideoUrl) ?? '/technologie.jpg';

/**
 * CTA rédacteur — specs Figma : 301×114, gap 18px, typo 16px (text-base).
 * Arrondi rounded-[30px] comme les cartes du hero. Ligne : texte | bouton (centrés verticalement).
 */
$writerCtaBanner = 'group relative flex min-h-[114px] h-auto w-full flex-row items-start justify-between gap-[18px] overflow-hidden rounded-[30px] bg-[#EBF1EF] p-[18px]';
$writerCtaBannerSidebar = $writerCtaBanner.' w-[301px] max-w-full shrink-0';
$writerCtaText = 'min-w-0 flex-1 pr-16 text-left font-semibold leading-snug text-[#004241] text-base';
$writerCtaIconBtn = 'absolute bottom-[18px] right-[18px] inline-flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#004241] text-[#EBF1EF] shadow-sm opacity-0 translate-y-2 transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:opacity-100 group-hover:translate-y-0';

// —— Design tokens & blocs Tailwind réutilisables (Vivat)
$cardOverlay = 'absolute inset-0 box-border p-[18px] min-h-0 min-w-0';
$glassBox = 'rounded-[21px] flex w-full min-w-0 max-w-full shrink-0 flex-col gap-1.5 box-border p-[18px] bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$glassTagTailwind = 'bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$articleImageZoom = 'group min-h-[112px] min-w-[128px] overflow-hidden';
$articleImageZoomImg = 'transition-transform duration-[650ms] ease-[cubic-bezier(0.22,1,0.36,1)] will-change-transform group-hover:scale-[1.045]';

$cardGreenSurface = 'bg-[#004241] transition-colors duration-200 hover:bg-[#003130]';
$cardYellowSurface = 'bg-[#FFF0B6] transition-colors duration-200 hover:bg-[#FBE9A3]';
$overlayImagePhoto = 'absolute inset-0 bg-[linear-gradient(180deg,rgba(0,0,0,0.08)_0%,rgba(0,0,0,0.18)_44%,rgba(0,0,0,0.56)_100%)]';
$overlayImageSoft = 'absolute inset-0 bg-gradient-to-t from-black/30 to-transparent';
$overlayRubriqueHero = 'pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/45 to-black/20';

$cardArrowIcon = 'pointer-events-none absolute right-[18px] top-[18px] inline-flex h-12 w-12 items-center justify-center rounded-full opacity-0 translate-x-2 -translate-y-2 transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:translate-x-0 group-hover:translate-y-0 group-hover:opacity-100';
$cardArrowOnGreen = $cardArrowIcon.' bg-[#527E7E] text-white';
$cardArrowOnYellow = $cardArrowIcon.' bg-[#004241] text-white';

$carouselSlideWidth = 'flex-[0_0_100%] box-border min-w-0';
$carouselNavBtn = 'absolute top-1/2 z-[60] flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-[#004241] text-white shadow-none transition-colors duration-200 hover:bg-[#003130] focus:outline-none focus:ring-2 focus:ring-[#004241] focus:ring-offset-2';

$rubriqueTileSm = 'group relative block h-[200px] w-full min-h-0 flex-shrink-0 overflow-hidden rounded-[30px] bg-black/20 lg:h-[250px]';
$rubriqueTileTall = 'group relative row-span-2 block h-[416px] min-h-0 min-w-0 w-full overflow-hidden rounded-[30px] bg-black/20 lg:h-[524px]';
$rubriqueDim = 'absolute inset-0 z-[1] bg-black/30 pointer-events-none';
$rubriqueHoverTint = 'pointer-events-none absolute inset-0 z-[1] bg-[#004241]/50 opacity-0 transition-opacity duration-300 ease-out group-hover:opacity-100';
$rubriqueTitleWrap = 'pointer-events-none absolute inset-0 z-[2] flex items-center justify-center p-4 md:p-[18px]';
$rubriqueTitle = 'text-center text-base font-semibold leading-snug text-white lg:text-xl';

// Grille highlight : 5 emplacements depuis $highlight
$h0 = $highlight[0] ?? null;
$h1 = $highlight[1] ?? null;
$h2 = $highlight[2] ?? null;
$h3 = $highlight[3] ?? null;
$h4 = $highlight[4] ?? null;
$catChunks = array_chunk($categories, 3);

// Tags pill & variantes (couleurs en utilitaires Tailwind)
$tagClass = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';
$tagCategoryTw = [
    'vert' => 'bg-[#527E7E] text-white',
    'jaune' => 'bg-[#004241] text-white',
];
$tagTopNews = 'bg-[#FFF1B9] text-[#004241]';
$tagGlass = $tagClass.' '.$glassTagTailwind;
$tagGlassOnImage = $tagGlass.' text-white';
/** Ligne méta (date • durée) sous les titres sur images glass */
$articleMetaOnImage = 'text-white/80 text-xs';

$h0CatSlug = $h0['category']['slug'] ?? null;
$h0ArtId = $h0['id'] ?? $h0['slug'] ?? null;
$h0Fallback = vivat_category_fallback_image($h0CatSlug, 800, 600, $h0ArtId, 'h0');
$h0Img = (! empty($h0['cover_image_url']) ? $h0['cover_image_url'] : $h0Fallback) ?: $h0Fallback;

$h1CatSlug = $h1['category']['slug'] ?? null;
$h1ArtId = $h1['id'] ?? $h1['slug'] ?? null;
$h1Fallback = vivat_category_fallback_image($h1CatSlug, 411, 237, $h1ArtId, 'h1');
$h1Img = (! empty($h1['cover_image_url']) ? $h1['cover_image_url'] : $h1Fallback) ?: $h1Fallback;

$h3CatSlug = $h3['category']['slug'] ?? null;
$h3ArtId = $h3['id'] ?? $h3['slug'] ?? null;
$h3Fallback = vivat_category_fallback_image($h3CatSlug, 411, 237, $h3ArtId, 'h3');
$h3Img = (! empty($h3['cover_image_url']) ? $h3['cover_image_url'] : $h3Fallback) ?: $h3Fallback;

$h4CatSlug = $h4['category']['slug'] ?? null;
$h4ArtId = $h4['id'] ?? $h4['slug'] ?? null;
$h4Fallback = vivat_category_fallback_image($h4CatSlug, 519, 280, $h4ArtId, 'h4');
$h4Img = (! empty($h4['cover_image_url']) ? $h4['cover_image_url'] : $h4Fallback) ?: $h4Fallback;
?>

<!-- Bandeau pub tablette uniquement (md) -->
<div class="hidden md:block lg:hidden w-full mb-6">
    <div class="rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm flex items-center justify-center mx-auto w-full max-w-[728px] h-[90px]">
        Publicité 728×90
    </div>
</div>

<!-- Grille tablette dédiée (visible md uniquement) — items-stretch + h-full évite le « trou » sous h0 quand la colonne droite est plus haute -->
<div class="hidden md:grid lg:hidden grid-cols-8 items-stretch gap-6 [grid-auto-rows:minmax(0,auto)]">
    <?php if ($h0) { ?>
    <a href="/articles/<?= htmlspecialchars($h0['slug']) ?>" class="<?= $articleImageZoom ?> relative col-span-5 block min-h-[420px] h-full w-full overflow-hidden rounded-[30px]">
        <img src="<?= htmlspecialchars($h0Img) ?>" data-fallback-url="<?= htmlspecialchars($h0Fallback) ?>" alt="<?= htmlspecialchars($h0['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="eager">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="<?= $cardOverlay ?> flex items-end">
            <div class="<?= $glassBox ?> w-full">
                <span class="<?= $tagClass ?> <?= $tagTopNews ?>">Top news</span>
                <h2 class="font-semibold text-white line-clamp-6 text-2xl"><?= htmlspecialchars($h0['title'] ?? '') ?></h2>
                <?php if (! empty($h0['excerpt'])) { ?>
                <p class="text-white/90 line-clamp-5 text-sm"><?= htmlspecialchars($h0['excerpt']) ?></p>
                <?php } ?>
                <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($h0['published_at'] ?? '') ?> • <?= (int) ($h0['reading_time'] ?? 0) ?> min</p>
            </div>
        </div>
    </a>
    <?php } ?>

    <div class="col-span-3 flex h-full min-h-0 min-w-0 flex-col gap-4 self-stretch">
        <?php if ($h2) { ?>
        <a href="/articles/<?= htmlspecialchars($h2['slug']) ?>" class="group relative flex min-h-[200px] w-full flex-1 flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 <?= $cardGreenSurface ?>">
            <span class="<?= $cardArrowOnGreen ?>">
                <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 17L17 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h8v8"/></svg>
            </span>
            <?php if (! empty($h2['category'])) { ?>
            <span class="<?= $tagClass ?> <?= $tagCategoryTw['vert'] ?>"><?= htmlspecialchars($h2['category']['name']) ?></span>
            <?php } ?>
            <h3 class="font-semibold text-white line-clamp-5 text-lg"><?= htmlspecialchars($h2['title']) ?></h3>
            <p class="text-white/70 text-xs"><?= htmlspecialchars($h2['published_at'] ?? '') ?> • <?= (int) ($h2['reading_time'] ?? 0) ?> min</p>
        </a>
        <?php } ?>

        <?php if ($h3) { ?>
        <a href="/articles/<?= htmlspecialchars($h3['slug']) ?>" class="<?= $articleImageZoom ?> relative block min-h-[200px] w-full flex-1 overflow-hidden rounded-[30px]">
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
    <a href="/articles/<?= htmlspecialchars($h4['slug']) ?>" class="group relative col-span-5 flex min-h-[280px] h-full w-full flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 <?= $cardYellowSurface ?>">
        <span class="<?= $cardArrowOnYellow ?>">
            <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 17L17 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h8v8"/></svg>
        </span>
        <?php if (! empty($h4['category'])) { ?>
        <span class="<?= $tagClass ?> <?= $tagCategoryTw['jaune'] ?>"><?= htmlspecialchars($h4['category']['name']) ?></span>
        <?php } ?>
        <h3 class="font-semibold text-[#004241] line-clamp-2 text-xl"><?= htmlspecialchars($h4['title']) ?></h3>
        <p class="text-[#004241]/70 text-xs"><?= htmlspecialchars($h4['published_at'] ?? '') ?> • <?= (int) ($h4['reading_time'] ?? 0) ?> min</p>
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

    <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="col-span-8 <?= $writerCtaBanner ?>">
        <p class="<?= $writerCtaText ?>">
            <?php if (count($writerCtaLines) === 2) { ?>
            <?= htmlspecialchars($writerCtaLines[0]) ?>.<br>
            <?= htmlspecialchars($writerCtaLines[1]) ?>
            <?php } else { ?>
            <?= htmlspecialchars($writer_cta_description) ?>
            <?php } ?>
        </p>
            <span class="<?= $writerCtaIconBtn ?>" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </span>
    </a>
</div>

<!-- Grille principale : mobile 1 col, desktop lg 12 cols -->
<div class="flex flex-col w-full">
    <div class="grid grid-cols-1 md:hidden lg:grid lg:grid-cols-12 lg:gap-6 lg:items-stretch">

        <!-- Colonne gauche: Top news + Standard 2 | lg: enfants directement sur la grille ; xl: colonne empilée -->
        <div class="flex flex-col gap-4 lg:contents xl:col-span-5 xl:flex xl:flex-col xl:gap-6">
            <?php if ($h0) { ?>
            <a href="/articles/<?= htmlspecialchars($h0['slug']) ?>" class="<?= $articleImageZoom ?> block h-[438px] w-full overflow-hidden rounded-[30px] relative lg:col-span-5 lg:row-start-1 lg:max-w-none">
                <img src="<?= htmlspecialchars($h0Img) ?>" data-fallback-url="<?= htmlspecialchars($h0Fallback) ?>" alt="<?= htmlspecialchars($h0['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="eager">
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="<?= $cardOverlay ?> flex items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <span class="<?= $tagClass ?> <?= $tagTopNews ?>">Top news</span>
                        <h2 class="font-semibold text-white line-clamp-6 text-[32px] max-sm:text-2xl"><?= htmlspecialchars($h0['title'] ?? '') ?></h2>
                        <?php if (! empty($h0['excerpt'])) { ?>
                        <p class="text-white/90 line-clamp-5 text-sm"><?= htmlspecialchars($h0['excerpt']) ?></p>
                        <?php } ?>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($h0['published_at'] ?? '') ?> • <?= (int) ($h0['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php } ?>

            <?php if ($h4) { ?>
            <a href="/articles/<?= htmlspecialchars($h4['slug']) ?>" class="group relative flex h-[280px] w-full flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 lg:col-span-5 lg:row-start-2 lg:max-w-none <?= $cardYellowSurface ?>">
                <span class="<?= $cardArrowOnYellow ?>">
                    <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 17L17 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h8v8"/></svg>
                </span>
                <?php if (! empty($h4['category'])) { ?>
                <span class="<?= $tagClass ?> <?= $tagCategoryTw['jaune'] ?>"><?= htmlspecialchars($h4['category']['name']) ?></span>
                <?php } ?>
                <h3 class="font-semibold text-[#004241] line-clamp-2 text-xl"><?= htmlspecialchars($h4['title']) ?></h3>
                <p class="text-[#004241]/70 text-xs"><?= htmlspecialchars($h4['published_at'] ?? '') ?> • <?= (int) ($h4['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php } ?>
        </div>

        <!-- Colonne droite: 3 cartes équivalentes (h1, h2, h3) — grille 3 lignes égales, gap 24px -->
        <div class="flex flex-col gap-6 lg:col-span-7 lg:row-span-2 lg:grid lg:grid-rows-3 lg:min-h-0 lg:self-stretch xl:col-span-4 xl:row-auto xl:row-span-1">
            <?php if ($h1) { ?>
            <a href="/articles/<?= htmlspecialchars($h1['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[200px] w-full min-h-0 overflow-hidden rounded-[30px] lg:h-full lg:min-h-0">
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

            <?php if ($h2) { ?>
            <a href="/articles/<?= htmlspecialchars($h2['slug']) ?>" class="group relative flex h-[200px] w-full min-h-0 flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 lg:h-full <?= $cardGreenSurface ?>">
                <span class="<?= $cardArrowOnGreen ?>">
                    <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 17L17 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h8v8"/></svg>
                </span>
                <?php if (! empty($h2['category'])) { ?>
                <span class="<?= $tagClass ?> <?= $tagCategoryTw['vert'] ?>"><?= htmlspecialchars($h2['category']['name']) ?></span>
                <?php } ?>
                <h3 class="font-semibold text-white line-clamp-2 text-xl"><?= htmlspecialchars($h2['title']) ?></h3>
                <p class="text-white/70 text-xs"><?= htmlspecialchars($h2['published_at'] ?? '') ?> • <?= (int) ($h2['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php } ?>

            <?php if ($h3) { ?>
            <a href="/articles/<?= htmlspecialchars($h3['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[200px] w-full min-h-0 overflow-hidden rounded-[30px] lg:h-full">
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

        <!-- CTA rédacteur : pleine largeur sous le bloc hero (lg seulement) -->
        <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="<?= $writerCtaBanner ?> hidden w-full lg:col-span-12 lg:row-start-3 lg:flex xl:hidden">
            <p class="<?= $writerCtaText ?>">
                <?php if (count($writerCtaLines) === 2) { ?>
                <?= htmlspecialchars($writerCtaLines[0]) ?>.<br>
                <?= htmlspecialchars($writerCtaLines[1]) ?>
                <?php } else { ?>
                <?= htmlspecialchars($writer_cta_description) ?>
                <?php } ?>
            </p>
            <span class="<?= $writerCtaIconBtn ?>" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </span>
        </a>

        <!-- Colonne pub + CTA : visible xl+ seulement -->
        <div class="hidden xl:flex xl:col-span-3 flex-col gap-6">
            <div class="flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm w-full xl:max-w-[300px] h-[600px] items-center justify-center">
                Espace publicitaire
            </div>
            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="<?= $writerCtaBannerSidebar ?> relative">
                <p class="<?= $writerCtaText ?>">
                    <?php if (count($writerCtaLines) === 2) { ?>
                    <?= htmlspecialchars($writerCtaLines[0]) ?>.<br>
                    <?= htmlspecialchars($writerCtaLines[1]) ?>
                    <?php } else { ?>
                    <?= htmlspecialchars($writer_cta_description) ?>
                    <?php } ?>
                </p>
                <span class="<?= $writerCtaIconBtn ?>" aria-hidden="true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
            </a>
        </div>

        <!-- CTA rédacteur mobile uniquement -->
        <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="<?= $writerCtaBanner ?> relative w-full lg:hidden">
            <p class="<?= $writerCtaText ?>">
                <?php if (count($writerCtaLines) === 2) { ?>
                <?= htmlspecialchars($writerCtaLines[0]) ?>.<br>
                <?= htmlspecialchars($writerCtaLines[1]) ?>
                <?php } else { ?>
                <?= htmlspecialchars($writer_cta_description) ?>
                <?php } ?>
            </p>
            <span class="<?= $writerCtaIconBtn ?>" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </span>
        </a>
    </div>

    <!-- Bannière pub -->
    <div class="flex rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm overflow-hidden items-center justify-center w-[970px] max-w-full h-[250px] mt-[65px] mx-auto">
        Espace publicitaire (bannière)
    </div>

    <?php if (count($categories) > 0) { ?>
    <?php
    $numSlides = max(1, count($catChunks));
        $hasLoop = ($numSlides > 1);
        $totalSlides = $hasLoop ? $numSlides + 1 : $numSlides;
        $isVideoMedia = function (?string $url): bool {
            if (! $url) {
                return false;
            }
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

            return in_array($ext, ['mp4', 'webm', 'mov'], true);
        };
        ?>
    <!-- Section Rubriques - Carrousel -->
    <section id="categories-section" class="relative z-10 w-full mt-[65px] overflow-visible">
        <div id="categories-carousel-viewport" class="overflow-hidden w-full min-w-0">
            <div id="categories-carousel-track" class="flex transition-transform duration-[1100ms] ease-out will-change-transform">
                <?php foreach ($catChunks as $slideIdx => $chunk) {
                        $cat1 = $chunk[0] ?? null;
                        $cat2 = $chunk[1] ?? null;
                        $cat3 = $chunk[2] ?? null;
                    $isFirstSlide = ($slideIdx === 0);
                    ?>
                <div class="categories-carousel-slide flex min-h-0 min-w-0 flex-shrink-0 items-stretch gap-4 px-4 [contain:layout] md:gap-5 md:px-6 lg:gap-6 <?= $carouselSlideWidth ?>">
                    <?php if ($isFirstSlide) { ?>
                    <a href="/" class="relative block min-h-0 min-w-0 flex-[7] overflow-hidden rounded-[30px] h-[300px] md:h-[420px] lg:h-[524px]">
                        <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="metadata" poster="<?= htmlspecialchars($rubriquesHeroPosterUrl) ?>">
                            <source src="<?= htmlspecialchars($rubriquesHeroVideoUrl) ?>" type="video/mp4">
                        </video>
                        <div class="<?= $overlayRubriqueHero ?>"></div>
                        <div class="pointer-events-none absolute inset-0 z-[2] flex flex-col items-start justify-center p-6 md:justify-end md:pb-8 md:pt-6 lg:p-8 lg:justify-center lg:pb-8">
                            <h2 class="max-w-[90%] text-left text-3xl font-semibold text-white sm:text-4xl md:max-w-[92%] md:text-2xl lg:max-w-[85%] lg:text-5xl">Découvrez vos rubriques préférées</h2>
                            <p class="mt-2 max-w-[90%] text-left text-lg text-white/95 sm:text-xl md:mt-2 md:max-w-[92%] md:text-base lg:max-w-[85%] lg:text-2xl">Explorez dès maintenant les contenus qui vous correspondent.</p>
                        </div>
                    </a>
                    <?php } ?>
                    <div class="grid min-h-0 min-w-0 flex-[5] grid-cols-2 gap-4 [grid-template-rows:repeat(2,200px)] md:gap-5 lg:gap-6 lg:[grid-template-rows:repeat(2,250px)]">
                        <div class="row-span-2 flex min-h-0 flex-col gap-4 md:gap-5 lg:gap-6">
                        <?php if ($cat1) { ?>
                        <?php $cat1Poster = vivat_cloudinary_video_poster_url($cat1['image_url'] ?? null) ?? vivat_category_public_poster_url($cat1['slug'] ?? null); ?>
                        <a href="/categories/<?= htmlspecialchars($cat1['slug']) ?>" class="<?= $rubriqueTileSm ?>">
                            <?php if (! empty($cat1['image_url'])) { ?>
                            <?php if ($isVideoMedia($cat1['image_url'])) { ?>
                            <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="none"<?= $cat1Poster ? ' poster="'.htmlspecialchars($cat1Poster).'"' : '' ?>>
                                <source src="<?= htmlspecialchars($cat1['image_url']) ?>" type="video/mp4">
                            </video>
                            <?php } else { ?>
                            <img src="<?= htmlspecialchars($cat1['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat1['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" loading="lazy" decoding="async">
                            <?php } ?>
                            <?php } ?>
                            <div class="<?= $rubriqueDim ?>"></div>
                            <div class="<?= $rubriqueHoverTint ?>"></div>
                            <div class="<?= $rubriqueTitleWrap ?>">
                                <span class="<?= $rubriqueTitle ?>"><?= htmlspecialchars($cat1['name']) ?></span>
                            </div>
                        </a>
                        <?php } ?>
                        <?php if ($cat2) { ?>
                        <?php $cat2Poster = vivat_cloudinary_video_poster_url($cat2['image_url'] ?? null) ?? vivat_category_public_poster_url($cat2['slug'] ?? null); ?>
                        <a href="/categories/<?= htmlspecialchars($cat2['slug']) ?>" class="<?= $rubriqueTileSm ?>">
                            <?php if (! empty($cat2['image_url'])) { ?>
                            <?php if ($isVideoMedia($cat2['image_url'])) { ?>
                            <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="none"<?= $cat2Poster ? ' poster="'.htmlspecialchars($cat2Poster).'"' : '' ?>>
                                <source src="<?= htmlspecialchars($cat2['image_url']) ?>" type="video/mp4">
                            </video>
                            <?php } else { ?>
                            <img src="<?= htmlspecialchars($cat2['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat2['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" loading="lazy" decoding="async">
                            <?php } ?>
                            <?php } ?>
                            <div class="<?= $rubriqueDim ?>"></div>
                            <div class="<?= $rubriqueHoverTint ?>"></div>
                            <div class="<?= $rubriqueTitleWrap ?>">
                                <span class="<?= $rubriqueTitle ?>"><?= htmlspecialchars($cat2['name']) ?></span>
                            </div>
                        </a>
                        <?php } ?>
                    </div>

                    <?php if ($cat3) { ?>
                    <?php $cat3Poster = vivat_cloudinary_video_poster_url($cat3['image_url'] ?? null) ?? vivat_category_public_poster_url($cat3['slug'] ?? null); ?>
                    <a href="/categories/<?= htmlspecialchars($cat3['slug']) ?>" class="group relative row-span-2 block h-[416px] min-h-0 min-w-0 w-full overflow-hidden rounded-[30px] bg-black/20 lg:h-[524px]">
                        <?php if (! empty($cat3['image_url'])) { ?>
                        <?php if ($isVideoMedia($cat3['image_url'])) { ?>
                        <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="none"<?= $cat3Poster ? ' poster="'.htmlspecialchars($cat3Poster).'"' : '' ?>>
                            <source src="<?= htmlspecialchars($cat3['image_url']) ?>" type="video/mp4">
                        </video>
                        <?php } else { ?>
                        <img src="<?= htmlspecialchars($cat3['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat3['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" loading="lazy" decoding="async">
                        <?php } ?>
                        <?php } ?>
                        <div class="<?= $rubriqueDim ?>"></div>
                        <div class="<?= $rubriqueHoverTint ?>"></div>
                        <div class="<?= $rubriqueTitleWrap ?>">
                            <span class="<?= $rubriqueTitle ?>"><?= htmlspecialchars($cat3['name']) ?></span>
                        </div>
                    </a>
                    <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <?php if ($hasLoop && count($catChunks) > 0) {
                    $c = $catChunks[0];
                    $c1 = $c[0] ?? null;
                    $c2 = $c[1] ?? null;
                    $c3 = $c[2] ?? null; ?>
                <div class="categories-carousel-slide categories-carousel-clone flex min-h-0 min-w-0 flex-shrink-0 items-stretch gap-4 px-4 [contain:layout] md:gap-5 md:px-6 lg:gap-6 <?= $carouselSlideWidth ?>" aria-hidden="true">
                    <a href="/" class="relative block min-h-0 min-w-0 flex-[7] overflow-hidden rounded-[30px] h-[300px] md:h-[420px] lg:h-[524px]">
                        <video class="absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline preload="metadata" poster="<?= htmlspecialchars($rubriquesHeroPosterUrl) ?>">
                            <source src="<?= htmlspecialchars($rubriquesHeroVideoUrl) ?>" type="video/mp4">
                        </video>
                        <div class="<?= $overlayRubriqueHero ?>"></div>
                        <div class="pointer-events-none absolute inset-0 z-[2] flex flex-col items-start justify-center p-6 md:justify-end md:pb-8 md:pt-6 lg:p-8 lg:justify-center lg:pb-8">
                            <h2 class="max-w-[90%] text-left text-3xl font-semibold text-white sm:text-4xl md:max-w-[92%] md:text-2xl lg:max-w-[85%] lg:text-5xl">Découvrez vos rubriques préférées</h2>
                            <p class="mt-2 max-w-[90%] text-left text-lg text-white/95 sm:text-xl md:mt-2 md:max-w-[92%] md:text-base lg:max-w-[85%] lg:text-2xl">Explorez dès maintenant les contenus qui vous correspondent.</p>
                        </div>
                    </a>
                    <div class="grid min-h-0 min-w-0 flex-[5] grid-cols-2 gap-4 [grid-template-rows:repeat(2,200px)] md:gap-5 lg:gap-6 lg:[grid-template-rows:repeat(2,250px)]">
                        <div class="row-span-2 flex min-h-0 flex-col gap-4 md:gap-5 lg:gap-6">
                        <?php if ($c1) { ?>
                        <?php $c1Poster = vivat_cloudinary_video_poster_url($c1['image_url'] ?? null) ?? vivat_category_public_poster_url($c1['slug'] ?? null); ?>
                        <a href="/categories/<?= htmlspecialchars($c1['slug']) ?>" class="<?= $rubriqueTileSm ?>">
                            <?php if (! empty($c1['image_url'])) { ?>
                            <?php if ($isVideoMedia($c1['image_url'])) { ?>
                            <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="none"<?= $c1Poster ? ' poster="'.htmlspecialchars($c1Poster).'"' : '' ?>>
                                <source src="<?= htmlspecialchars($c1['image_url']) ?>" type="video/mp4">
                            </video>
                            <?php } else { ?>
                            <img src="<?= htmlspecialchars($c1['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($c1['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" loading="lazy" decoding="async">
                            <?php } ?>
                            <?php } ?>
                            <div class="<?= $rubriqueDim ?>"></div>
                            <div class="<?= $rubriqueHoverTint ?>"></div>
                            <div class="<?= $rubriqueTitleWrap ?>">
                                <span class="<?= $rubriqueTitle ?>"><?= htmlspecialchars($c1['name']) ?></span>
                                </div>
                            </a>
                        <?php } ?>
                        <?php if ($c2) { ?>
                        <?php $c2Poster = vivat_cloudinary_video_poster_url($c2['image_url'] ?? null) ?? vivat_category_public_poster_url($c2['slug'] ?? null); ?>
                        <a href="/categories/<?= htmlspecialchars($c2['slug']) ?>" class="<?= $rubriqueTileSm ?>">
                            <?php if (! empty($c2['image_url'])) { ?>
                            <?php if ($isVideoMedia($c2['image_url'])) { ?>
                            <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="none"<?= $c2Poster ? ' poster="'.htmlspecialchars($c2Poster).'"' : '' ?>>
                                <source src="<?= htmlspecialchars($c2['image_url']) ?>" type="video/mp4">
                            </video>
                            <?php } else { ?>
                            <img src="<?= htmlspecialchars($c2['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($c2['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" loading="lazy" decoding="async">
                            <?php } ?>
                            <?php } ?>
                            <div class="<?= $rubriqueDim ?>"></div>
                            <div class="<?= $rubriqueHoverTint ?>"></div>
                            <div class="<?= $rubriqueTitleWrap ?>">
                                <span class="<?= $rubriqueTitle ?>"><?= htmlspecialchars($c2['name']) ?></span>
                                </div>
                            </a>
                        <?php } ?>
                        </div>
                        <?php if ($c3) { ?>
                        <?php $c3Poster = vivat_cloudinary_video_poster_url($c3['image_url'] ?? null) ?? vivat_category_public_poster_url($c3['slug'] ?? null); ?>
                        <a href="/categories/<?= htmlspecialchars($c3['slug']) ?>" class="group relative row-span-2 block h-[416px] min-h-0 min-w-0 w-full overflow-hidden rounded-[30px] bg-black/20 lg:h-[524px]">
                            <?php if (! empty($c3['image_url'])) { ?>
                            <?php if ($isVideoMedia($c3['image_url'])) { ?>
                            <video class="categories-rubrique-video absolute inset-0 z-0 w-full h-full object-cover" muted loop playsinline preload="none"<?= $c3Poster ? ' poster="'.htmlspecialchars($c3Poster).'"' : '' ?>>
                                <source src="<?= htmlspecialchars($c3['image_url']) ?>" type="video/mp4">
                            </video>
                            <?php } else { ?>
                            <img src="<?= htmlspecialchars($c3['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($c3['name']) ?>" class="absolute inset-0 z-0 w-full h-full object-cover" loading="lazy" decoding="async">
                            <?php } ?>
                            <?php } ?>
                            <div class="<?= $rubriqueDim ?>"></div>
                            <div class="<?= $rubriqueHoverTint ?>"></div>
                            <div class="<?= $rubriqueTitleWrap ?>">
                                <span class="<?= $rubriqueTitle ?>"><?= htmlspecialchars($c3['name']) ?></span>
                            </div>
                        </a>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php if ($numSlides > 1) { ?>
        <button type="button" id="categories-carousel-prev" class="<?= $carouselNavBtn ?> left-0" aria-label="Rubriques précédentes">
            <svg class="h-6 w-6 flex-shrink-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
        <button type="button" id="categories-carousel-next" class="<?= $carouselNavBtn ?> right-0" aria-label="Rubriques suivantes">
            <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>
        <?php } ?>
    </section>
    <script>
    (function() {
        var section = document.getElementById('categories-section');
        var viewport = document.getElementById('categories-carousel-viewport');
        if (!section || !viewport) {
            return;
        }
        var videos = section.querySelectorAll('video.categories-rubrique-video');
        if (!videos.length) {
            return;
        }
        var transitionActive = false;

        function pauseRubriqueVideos() {
            videos.forEach(function (v) {
                v.pause();
            });
        }

        function syncRubriqueVideos() {
            var vr = viewport.getBoundingClientRect();
            videos.forEach(function (v) {
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
        videos.forEach(function (v) {
            io.observe(v);
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
        var slides = document.querySelectorAll('.categories-carousel-slide');
        if (!track || !viewport || !nextBtn || slides.length < 2) return;
        var total = slides.length;
        var realCount = total - (document.querySelector('.categories-carousel-clone') ? 1 : 0);
        var idx = 0;
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

        function slideOffset(i) {
            return Math.round(i * viewport.getBoundingClientRect().width);
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
            if (idx === total - 1) {
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
            if (isAnimating) return;
            beforeSlideAnimation();
            isAnimating = true;
            idx++;
            goTo(idx, false);
        });
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (isAnimating) return;
                beforeSlideAnimation();
                isAnimating = true;
                if (idx === 0) {
                    idx = total - 1;
                    goTo(idx, true);
                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            idx = realCount - 1;
                            goTo(idx, false);
                        });
                    });
                } else {
                    idx--;
                    goTo(idx, false);
                }
            });
        }
    })();
    </script>
    <?php } ?>
    <?php } ?>

    <?php
    $highlightIds = array_filter([
        ($h0 ?? [])['id'] ?? null,
        ($h1 ?? [])['id'] ?? null,
        ($h2 ?? [])['id'] ?? null,
        ($h3 ?? [])['id'] ?? null,
        ($h4 ?? [])['id'] ?? null,
    ]);
$restArticles = array_values(array_filter($latest, fn ($a) => ! in_array($a['id'] ?? null, $highlightIds)));
// Déduplification par id puis slug
$byId = [];
foreach ($restArticles as $a) {
    $id = $a['id'] ?? null;
    if ($id !== null && ! isset($byId[$id])) {
        $byId[$id] = $a;
    }
}
$bySlug = [];
foreach (array_values($byId) as $a) {
    $slug = $a['slug'] ?? null;
    if ($slug !== null && $slug !== '' && ! isset($bySlug[$slug])) {
        $bySlug[$slug] = $a;
    }
}
$restArticles = array_values($bySlug);
// Sélection des articles pour cartes "photo complète"
$reservedIndices = [0, 1, 2, 3, 4, 6, 10, 11];
$restForPhotos = [];
foreach ($restArticles as $idx => $a) {
    if (! in_array($idx, $reservedIndices, true)) {
        $restForPhotos[] = $a;
    }
}
$withCover = array_values(array_filter($restForPhotos, fn ($a) => ! empty($a['cover_image_url'])));
$artForFullPhoto1 = $withCover[0] ?? $restForPhotos[0] ?? $restArticles[5] ?? null;
$artForFullPhoto2 = (count($withCover) > 1) ? $withCover[1] : ($restForPhotos[1] ?? $restArticles[7] ?? null);
if ($artForFullPhoto2 !== null && $artForFullPhoto1 !== null && ($artForFullPhoto2['id'] ?? null) === ($artForFullPhoto1['id'] ?? null)) {
    $full1Id = $artForFullPhoto1['id'] ?? null;
    $artForFullPhoto2 = null;
    foreach ($restForPhotos as $a) {
        if (($a['id'] ?? null) !== $full1Id) {
            $artForFullPhoto2 = $a;
            break;
        }
    }
    $artForFullPhoto2 = $artForFullPhoto2 ?? $restArticles[7] ?? null;
}
?>

    <?php if (count($restArticles) > 0) { ?>
    <section class="mt-12 grid w-full min-w-0 grid-cols-1 gap-6 md:grid-cols-8 md:mt-16 lg:grid-cols-12">
        <h2 class="mb-0 text-[32px] font-medium text-[#004241] md:col-span-8 lg:col-span-12">Dernières actualités</h2>

        <!-- Colonne gauche | md 4 cols, lg 6 cols -->
        <div class="md:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php
            $firstArt = $restArticles[0] ?? null;
        $secondArt = $restArticles[1] ?? null;
        ?>
                <?php if ($firstArt) { ?>
                <?php $f0CatSlug = $firstArt['category']['slug'] ?? null;
                    $f0ArtId = $firstArt['id'] ?? $firstArt['slug'] ?? null;
                    $f0Fallback = vivat_category_fallback_image($f0CatSlug, 302, 419, $f0ArtId, 'card-0');
                    $f0Img = ! empty($firstArt['cover_image_url']) ? $firstArt['cover_image_url'] : $f0Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($firstArt['slug']) ?>" class="<?= $articleImageZoom ?> block rounded-[30px] overflow-hidden relative min-w-0 w-full h-[419px]">
                    <img src="<?= htmlspecialchars($f0Img) ?>" data-fallback-url="<?= htmlspecialchars($f0Fallback) ?>" alt="<?= htmlspecialchars($firstArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImageSoft ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($firstArt['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($firstArt['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="font-medium text-white line-clamp-5 text-xl"><?= htmlspecialchars($firstArt['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($firstArt['published_at'] ?? '') ?> • <?= (int) ($firstArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                        </div>
                </a>
                <?php } ?>

                <?php if ($secondArt) { ?>
                <?php $artCatSlug = $secondArt['category']['slug'] ?? null;
                    $artId = $secondArt['id'] ?? $secondArt['slug'] ?? null;
                    $artFallback = vivat_category_fallback_image($artCatSlug, 254, 190, $artId, 'card-1');
                    $artImg = ! empty($secondArt['cover_image_url']) ? $secondArt['cover_image_url'] : $artFallback; ?>
                <a href="/articles/<?= htmlspecialchars($secondArt['slug']) ?>" class="<?= $articleImageZoom ?> block rounded-[30px] overflow-hidden relative min-w-0 w-full h-[419px]">
                    <img src="<?= htmlspecialchars($artImg) ?>" data-fallback-url="<?= htmlspecialchars($artFallback) ?>" alt="<?= htmlspecialchars($secondArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImagePhoto ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($secondArt['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($secondArt['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="font-medium text-white line-clamp-5 text-xl"><?= htmlspecialchars($secondArt['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($secondArt['published_at'] ?? '') ?> • <?= (int) ($secondArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>

            <?php $hotNewsArt = $restArticles[2] ?? null;
        if ($hotNewsArt) { ?>
            <?php $hotCatSlug = $hotNewsArt['category']['slug'] ?? null;
            $hotArtId = $hotNewsArt['id'] ?? $hotNewsArt['slug'] ?? null;
            $hotFallback = vivat_category_fallback_image($hotCatSlug, 626, 240, $hotArtId, 'hot');
            $hotNewsImg = ! empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : $hotFallback; ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="<?= $articleImageZoom ?> block rounded-[32px] overflow-hidden relative min-w-0 w-full h-60">
                <img src="<?= htmlspecialchars($hotNewsImg) ?>" data-fallback-url="<?= htmlspecialchars($hotFallback) ?>" alt="<?= htmlspecialchars($hotNewsArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                <div class="<?= $cardOverlay ?> flex justify-end items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <?php if (! empty($hotNewsArt['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($hotNewsArt['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="font-medium text-white line-clamp-5 text-xl"><?= htmlspecialchars($hotNewsArt['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                        </div>
            </a>
            <?php } ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php $artLeft = $restArticles[10] ?? null;
        if ($artLeft) { ?>
                <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="group relative flex flex-col justify-end overflow-hidden rounded-[30px] min-w-0 w-full h-[419px] gap-[18px] p-6 <?= $cardYellowSurface ?>">
                    <span class="<?= $cardArrowOnYellow ?>">
                        <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 17L17 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h8v8"/></svg>
                    </span>
                    <div class="flex flex-col min-h-0 gap-2">
                        <?php if (! empty($artLeft['category'])) { ?>
                        <span class="<?= $tagClass ?> <?= $tagCategoryTw['jaune'] ?>"><?= htmlspecialchars($artLeft['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="font-medium text-[#004241] line-clamp-5 text-xl"><?= htmlspecialchars($artLeft['title']) ?></h3>
                        <p class="text-[#004241] font-light text-xs"><?= htmlspecialchars($artLeft['published_at'] ?? '') ?> • <?= (int) ($artLeft['reading_time'] ?? 0) ?> min</p>
                        </div>
                </a>
                <?php } ?>

                <?php $artLeft2 = $restArticles[6] ?? null;
        if ($artLeft2) { ?>
                <?php $left2CatSlug = $artLeft2['category']['slug'] ?? null;
            $left2ArtId = $artLeft2['id'] ?? $artLeft2['slug'] ?? null;
            $left2Fallback = vivat_category_fallback_image($left2CatSlug, 302, 419, $left2ArtId, 'left2');
            $artLeft2Img = ! empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : $left2Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="<?= $articleImageZoom ?> block rounded-[30px] overflow-hidden relative min-w-0 w-full h-[419px]">
                    <img src="<?= htmlspecialchars($artLeft2Img) ?>" data-fallback-url="<?= htmlspecialchars($left2Fallback) ?>" alt="<?= htmlspecialchars($artLeft2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImageSoft ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($artLeft2['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($artLeft2['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="font-medium text-white line-clamp-5 text-xl"><?= htmlspecialchars($artLeft2['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                        </div>
                        </div>
                    </a>
                <?php } ?>
                </div>
            </div>

        <!-- Colonne droite | md 4 cols, lg 6 cols -->
        <div class="md:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full gap-6">
            <?php foreach (array_slice($restArticles, 3, 2) as $i => $art) {
                $isDark = ($i % 2 === 0);
                ?>
            <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="group relative flex min-w-0 w-full flex-col justify-end gap-2 overflow-hidden rounded-[30px] p-6 h-[198px] <?= $isDark ? $cardGreenSurface : $cardYellowSurface ?>">
                <span class="<?= $isDark ? $cardArrowOnGreen : $cardArrowOnYellow ?>">
                    <svg class="w-7 h-7 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 17L17 7"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 7h8v8"/></svg>
                </span>
                <?php if (! empty($art['category'])) { ?>
                <?php $tagVariant = $isDark ? 'vert' : 'jaune'; ?>
                <span class="<?= $tagClass ?> <?= $tagCategoryTw[$tagVariant] ?>"><?= htmlspecialchars($art['category']['name']) ?></span>
                <?php } ?>
                <h3 class="font-medium line-clamp-2 text-xl <?= $isDark ? 'text-white' : 'text-[#004241]' ?>"><?= htmlspecialchars($art['title']) ?></h3>
                <p class="text-xs <?= $isDark ? 'text-white/70' : 'text-[#004241]/70' ?>"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php } ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php $artRight = $restArticles[11] ?? null;
        if ($artRight) { ?>
                <?php $rightCatSlug = $artRight['category']['slug'] ?? null;
            $rightArtId = $artRight['id'] ?? $artRight['slug'] ?? null;
            $rightFallback = vivat_category_fallback_image($rightCatSlug, 254, 190, $rightArtId, 'right');
            $artRightImg = ! empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : $rightFallback; ?>
                <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="<?= $articleImageZoom ?> block rounded-[30px] overflow-hidden relative min-w-0 w-full h-[419px]">
                    <img src="<?= htmlspecialchars($artRightImg) ?>" data-fallback-url="<?= htmlspecialchars($rightFallback) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImagePhoto ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($artRight['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($artRight['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="font-medium text-white line-clamp-5 text-xl"><?= htmlspecialchars($artRight['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                        </div>
                        </div>
                </a>
                <?php } ?>

                <?php if ($artForFullPhoto1) { ?>
                <?php $full1CatSlug = $artForFullPhoto1['category']['slug'] ?? null;
                    $full1ArtId = $artForFullPhoto1['id'] ?? $artForFullPhoto1['slug'] ?? null;
                    $full1Fallback = vivat_category_fallback_image($full1CatSlug, 302, 419, $full1ArtId, 'full1');
                    $fullPhoto1Img = ! empty($artForFullPhoto1['cover_image_url']) ? $artForFullPhoto1['cover_image_url'] : $full1Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artForFullPhoto1['slug']) ?>" class="<?= $articleImageZoom ?> block rounded-[25px] overflow-hidden relative min-w-0 w-full h-[419px]">
                    <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" data-fallback-url="<?= htmlspecialchars($full1Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                    <div class="<?= $overlayImageSoft ?>"></div>
                    <div class="<?= $cardOverlay ?> flex items-end z-10">
                        <div class="<?= $glassBox ?> w-full">
                            <?php if (! empty($artForFullPhoto1['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($artForFullPhoto1['category']['name']) ?></span>
                            <?php } ?>
                            <h3 class="font-medium text-white line-clamp-5 text-xl"><?= htmlspecialchars($artForFullPhoto1['title'] ?? '') ?></h3>
                            <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($artForFullPhoto1['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto1['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>

            <?php if ($artForFullPhoto2) { ?>
            <?php $full2CatSlug = $artForFullPhoto2['category']['slug'] ?? null;
                $full2ArtId = $artForFullPhoto2['id'] ?? $artForFullPhoto2['slug'] ?? null;
                $full2Fallback = vivat_category_fallback_image($full2CatSlug, 629, 235, $full2ArtId, 'full2');
                $fullPhoto2Img = ! empty($artForFullPhoto2['cover_image_url']) ? $artForFullPhoto2['cover_image_url'] : $full2Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($artForFullPhoto2['slug']) ?>" class="<?= $articleImageZoom ?> block rounded-[30px] overflow-hidden relative w-full min-w-0 h-[235px]">
                <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" data-fallback-url="<?= htmlspecialchars($full2Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                <div class="<?= $overlayImageSoft ?>"></div>
                <div class="<?= $cardOverlay ?> flex items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <?php if (! empty($artForFullPhoto2['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($artForFullPhoto2['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="font-medium text-white line-clamp-5 text-xl"><?= htmlspecialchars($artForFullPhoto2['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($artForFullPhoto2['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto2['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php } ?>
        </div>

        <!-- Bouton Autres actualités -->
        <div class="flex justify-center md:col-span-8 lg:col-span-12">
            <a href="/articles" class="inline-flex items-center justify-center rounded-full font-medium text-white gap-2.5 h-12 w-[226px] bg-[#004241] px-[18px] transition-colors duration-200 hover:bg-[#003130]">
                Autres actualités
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>
    <?php } ?>

</div>
