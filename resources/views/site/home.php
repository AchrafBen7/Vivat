<?php
$t = fn (string $key, ?string $fallback = null) => __($key) !== $key ? __($key) : ($fallback ?? $key);
$highlight = $highlight ?? [];
$featured = $featured ?? [];
$latest = $latest ?? [];
$pagination = $pagination ?? null;
$categories = $categories ?? [];
$writer_signup_url = $writer_signup_url ?? '#';
$writer_dashboard_url = $writer_dashboard_url ?? '#';
$writer_cta_url = $writer_cta_url ?? $writer_signup_url;
$writer_cta_label = $writer_cta_label ?? $t('site.writer_cta_guest_label', 'Rédigez un article');
$writer_cta_description = $writer_cta_description ?? $t('site.writer_cta_guest_description', 'Écrivez sur Vivat. Votre voix compte.');
$writerCtaLines = preg_split('/\.\s+/', trim($writer_cta_description), 2);
$writer_cta_title = $writer_cta_title ?? $t('site.writer_cta_guest_title', (count($writerCtaLines) === 2 ? $writerCtaLines[0].'.' : $writer_cta_description));
$writer_cta_subtitle = $writer_cta_subtitle ?? (count($writerCtaLines) === 2 ? $writerCtaLines[1] : '');
$writer_cta_tag_1 = $writer_cta_tag_1 ?? $t('site.writer_cta_tag_1', 'Rédaction');
$writer_cta_tag_2 = $writer_cta_tag_2 ?? $t('site.writer_cta_tag_2', 'Actualités');
$rubriquesHeroVideoUrl = '/quotidien2.mp4';
$rubriquesHeroPosterUrl = '/technologie.jpg';

/**
 * CTA rédacteur specs Figma : 301×114, gap 18px, typo 16px (text-base).
 * Arrondi rounded-[30px] comme les cartes du hero. Ligne : texte | bouton (centrés verticalement).
 */
$writerCtaBanner = 'group relative flex min-h-[114px] h-auto w-full flex-row items-start justify-between gap-[18px] overflow-hidden rounded-[30px] bg-[#EBF1EF] p-[18px] transition-colors duration-200 hover:bg-[#DEE7E4]';
$writerCtaBannerSidebar = $writerCtaBanner.' w-[301px] max-w-full shrink-0';
$writerCtaTextWrap = 'min-w-0 flex-1 pr-16 text-left';
$writerCtaTitle = 'font-semibold leading-snug text-[#004241] text-base';
$writerCtaSubtitle = 'mt-0.5 text-sm font-normal leading-snug text-[#004241]/80';
$writerCtaTagPill = 'inline-flex max-w-full shrink-0 items-center rounded-full bg-[#004241]/10 px-2.5 py-0.5 text-[11px] font-medium leading-none text-[#004241]';
/** Coin bas-droit du bandeau (padding 18px aligné sur le bloc) z au-dessus du lien plein écran */
$writerCtaIconBtn = 'pointer-events-none absolute bottom-[18px] right-[18px] z-[3] inline-flex h-12 w-12 flex-shrink-0 items-center justify-center text-[#004241]';
/** CTA pleine largeur (mobile → lg) : sans tags pastilles, typo un peu au-dessus du base */
$writerCtaTitleLarge = $writerCtaTitle.' text-lg leading-snug sm:text-xl sm:leading-snug';
$writerCtaSubtitleLarge = $writerCtaSubtitle.' mt-1 text-sm leading-snug sm:text-base';
$writerCtaFocusReset = 'vivat-writer-cta-link outline-none focus:outline-none focus:ring-0 focus:ring-offset-0 focus-visible:outline-none focus-visible:ring-0 focus-visible:ring-offset-0';

// -- Design tokens & blocs Tailwind réutilisables (Vivat)
$cardOverlay = 'absolute inset-0 box-border p-[18px] min-h-0 min-w-0';
$glassBox = 'rounded-[21px] flex w-full min-w-0 max-w-full shrink-0 flex-col gap-1.5 box-border p-[18px] bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$glassTagTailwind = 'bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$articleImageZoom = 'group min-h-[112px] min-w-[128px] overflow-hidden';
$articleImageZoomImg = 'transition-transform duration-[500ms] ease-out will-change-transform group-hover:scale-[1.03]';

$cardGreenSurface = 'bg-[#004241] transition-colors duration-200 hover:bg-[#003130]';
$cardYellowSurface = 'bg-[#FFF0B6] transition-colors duration-200 hover:bg-[#FBE9A3]';
$cardSoftSurface = 'bg-[#EBF1EF] transition-colors duration-200 hover:bg-[#DEE7E4]';
$cardWhiteSurface = 'border border-[#D6E1DD] bg-white transition-colors duration-200 hover:bg-[#F7FAF9]';
$cardGradientSurface = 'bg-[linear-gradient(135deg,#004241_0%,#185B58_58%,#4C807C_100%)]';
$overlayImagePhoto = 'absolute inset-0 bg-[linear-gradient(180deg,rgba(0,0,0,0.08)_0%,rgba(0,0,0,0.18)_44%,rgba(0,0,0,0.56)_100%)]';
$overlayImageSoft = 'absolute inset-0 bg-gradient-to-t from-black/30 to-transparent';
$overlayRubriqueHero = 'pointer-events-none absolute inset-0 z-[1] bg-gradient-to-t from-black/45 to-black/20';
$topNewsContentMotion = 'w-full min-h-0 overflow-hidden transition-transform duration-[400ms] ease-out md:group-hover:-translate-y-1';
$topNewsExcerptReveal = 'line-clamp-2 text-white/90 text-sm max-md:mt-3 max-md:mb-3 max-md:max-h-[3rem] max-md:opacity-100 md:mt-1 md:mb-0 md:max-h-0 md:overflow-hidden md:translate-y-1 md:opacity-0 md:transition-[max-height,opacity,transform,margin] md:duration-[400ms] md:ease-out md:group-hover:mt-3 md:group-hover:mb-4 md:group-hover:max-h-[3rem] md:group-hover:translate-y-0 md:group-hover:opacity-100';
$heroColorCardContentMotion = 'w-full min-h-0 overflow-hidden transition-transform duration-[400ms] ease-out md:group-hover:-translate-y-1';
$heroColorCardExcerptRevealOnLight = 'line-clamp-2 text-sm text-[#004241]/72 max-md:mt-3 max-md:mb-3 max-md:max-h-[3rem] max-md:opacity-100 md:mt-1 md:mb-0 md:max-h-0 md:overflow-hidden md:translate-y-1 md:opacity-0 md:transition-[max-height,opacity,transform,margin] md:duration-[400ms] md:ease-out md:group-hover:mt-3 md:group-hover:mb-4 md:group-hover:max-h-[3rem] md:group-hover:translate-y-0 md:group-hover:opacity-100';
$heroColorCardExcerptRevealOnDark = 'line-clamp-2 text-sm text-white/75 max-md:mt-3 max-md:mb-3 max-md:max-h-[3rem] max-md:opacity-100 md:mt-1 md:mb-0 md:max-h-0 md:overflow-hidden md:translate-y-1 md:opacity-0 md:transition-[max-height,opacity,transform,margin] md:duration-[400ms] md:ease-out md:group-hover:mt-3 md:group-hover:mb-4 md:group-hover:max-h-[3rem] md:group-hover:translate-y-0 md:group-hover:opacity-100';

$cardArrowIcon = 'pointer-events-none absolute right-[18px] top-[18px] inline-flex h-12 w-12 items-center justify-center rounded-[30px] transition-[background-color,color] duration-300 ease-out';
$cardArrowOnGreen = $cardArrowIcon.' text-white group-hover:bg-[#527E7E]';
$cardArrowOnYellow = $cardArrowIcon.' text-[#004241] group-hover:bg-[#004241] group-hover:text-white';

$carouselSlideWidth = 'flex-[0_0_100%] box-border min-w-0';
$carouselNavBtn = 'absolute top-1/2 z-[60] flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border-0 bg-[#004241] text-white shadow-none outline-none ring-0 transition-colors duration-200 hover:bg-[#003130] focus:border-0 focus:outline-none focus:ring-0 focus:ring-offset-0 active:border-0 active:outline-none active:ring-0 [-webkit-tap-highlight-color:transparent]';
/** Centré sur le bord gauche / droit du bloc cartes (moitié du bouton sur les cartes). */
$carouselNavBtnEdgeLeft = $carouselNavBtn.' left-0 -translate-x-1/2';
$carouselNavBtnEdgeRight = $carouselNavBtn.' right-0 translate-x-1/2';

$rubriqueTileTabletSplit = 'group relative hidden h-[420px] min-h-0 min-w-0 flex-[0_0_32%] overflow-hidden rounded-[30px] bg-black/20 md:block lg:hidden';
$rubriqueTileTabletFull = 'group relative hidden h-[420px] min-h-0 min-w-0 w-full overflow-hidden rounded-[30px] bg-black/20 md:block lg:hidden';
$rubriqueTileSm = 'group relative block h-[200px] w-full min-h-0 flex-shrink-0 overflow-hidden rounded-[30px] bg-black/20 lg:h-[250px]';
$rubriqueTileTall = 'group relative row-span-2 block h-[416px] min-h-0 min-w-0 w-full overflow-hidden rounded-[30px] bg-black/20 lg:h-[524px]';
$rubriqueDim = 'absolute inset-0 z-[1] bg-black/30 pointer-events-none';
$rubriqueHoverTint = 'pointer-events-none absolute inset-0 z-[1] bg-black/40 opacity-0 transition-opacity duration-300 ease-out group-hover:opacity-100';
$rubriqueTitleWrap = 'pointer-events-none absolute inset-0 z-[2] flex flex-col items-center justify-center gap-0 p-4 md:p-[18px]';
$rubriqueTitle = 'text-center text-base font-semibold leading-snug text-white lg:text-xl';
$rubriqueDescHover = 'mt-0 max-h-0 w-full max-w-[min(100%,40ch)] shrink-0 overflow-hidden px-2 text-center text-xs font-normal leading-snug text-white/90 opacity-0 transition-all duration-300 ease-out [text-wrap:balance] group-hover:mt-2 group-hover:max-h-[7rem] group-hover:opacity-100 sm:text-sm lg:line-clamp-4';
$renderRubriqueCategoryLabel = static function (array $cat, string $titleClass = '') use ($rubriqueTitle, $rubriqueDescHover): void {
    $titleClasses = $titleClass !== '' ? $titleClass : $rubriqueTitle;
    $name = htmlspecialchars($cat['name'] ?? '');
    $desc = trim((string) ($cat['description'] ?? ''));
    echo '<span class="'.$titleClasses.'">'.$name.'</span>';
    if ($desc !== '') {
        echo '<p class="'.$rubriqueDescHover.'">'.htmlspecialchars($desc).'</p>';
    }
};

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
    'vert' => 'bg-[#004241] text-white',
    'creme' => 'bg-[#FFF1B9] text-[#004241]',
    'gris' => 'bg-[#EBF1EF] text-[#004241]',
];
$tagTopNews = 'bg-[#FFF1B9] text-[#004241]';
$tagGlass = $tagClass.' '.$glassTagTailwind;
$tagGlassOnImage = $tagGlass.' text-white';
$tagOnYellowCard = 'bg-[#004241] text-white';
$tagOnGreenCard = 'bg-[#527E7E] text-white';
$tagOnSoftCard = 'bg-white text-[#004241]';
$tagOnWhiteCard = 'bg-[#EBF1EF] text-[#004241]';
$heroColorCardTitleWide = 'font-semibold leading-tight text-[18px] md:text-xl lg:text-2xl';
$heroColorCardTitleCompact = 'font-semibold leading-tight text-[18px] md:text-xl';
$heroColorCardExcerptOnLight = 'line-clamp-2 text-sm text-[#004241]/72';
$heroColorCardExcerptOnDark = 'line-clamp-2 text-sm text-white/75';
/** Ligne méta (date • durée) sous les titres sur images glass */
$articleMetaOnImage = 'text-white/80 text-xs';
$resolveCategoryTagTw = static function (?array $category) use ($tagCategoryTw): string {
    $slug = (string) ($category['slug'] ?? '');

    return match ($slug) {
        'finance', 'technologie', 'mode', 'energie', 'sante', 'voyage' => $tagCategoryTw['vert'],
        'famille', 'au-quotidien', 'chez-soi', 'chezsoi' => $tagCategoryTw['creme'],
        default => $tagCategoryTw['gris'],
    };
};

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
$tabletCategorySlides = array_values($categories);
$tabletPrimaryCategory = array_shift($tabletCategorySlides);
$tabletCategoryPairs = array_chunk($tabletCategorySlides, 2);
$tabletCarouselSlides = [];
if ($tabletPrimaryCategory) {
    $tabletCarouselSlides[] = [
        'type' => 'hero',
        'categories' => [$tabletPrimaryCategory],
    ];
}
foreach ($tabletCategoryPairs as $pair) {
    $tabletCarouselSlides[] = [
        'type' => 'pair',
        'categories' => $pair,
    ];
}
?>
<?= render_php_view('site.partials.home.hero', get_defined_vars()) ?>
<?= render_php_view('site.partials.home.categories', get_defined_vars()) ?>
<?= render_php_view('site.partials.home.latest', get_defined_vars()) ?>
