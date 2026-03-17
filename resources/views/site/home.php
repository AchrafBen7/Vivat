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
$writer_cta_description = $writer_cta_description ?? 'Vivat est aussi écrit par ses lecteurs. Partagez votre point de vue.';

// Grille highlight (collègue) : 5 emplacements depuis $highlight (hot_news puis featured)
$h0 = $highlight[0] ?? null;  // grande carte Top news
$h1 = $highlight[1] ?? null; // feature 1
$h2 = $highlight[2] ?? null; // standard 1 (vert)
$h3 = $highlight[3] ?? null; // feature 2
$h4 = $highlight[4] ?? null; // standard 2 (jaune)
$catChunks = array_chunk($categories, 3);

// Styles des tags par type de card (H 30px, padding 6px 12px, 14px font)
// vert = fond #004241 → tag #527E7E, texte blanc
// jaune = fond #FFF0D4/#FFEFD1 → tag #004241, texte blanc
// glass = image + overlay → tag #787879, texte blanc
// gris = fond #EBF1EF → tag blanc, texte #004241
$tagStyles = [
    'vert'  => ['bg' => '#527E7E', 'color' => '#fff'],
    'jaune' => ['bg' => '#004241', 'color' => '#fff'],
    'glass' => ['bg' => '#787879', 'color' => '#fff'],
    'gris'  => ['bg' => '#ffffff', 'color' => '#004241'],
];
$tagClass = 'vivat-tag';
$tagStyleBase = '';

// Titre min 8 mots / max 9 mots dans les carrés glass (position et padding 24px inchangés)
$truncateGlassTitle = function (?string $t): string {
    $t = trim((string) $t);
    if ($t === '') return '';
    $w = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
    $minWords = 8;
    $maxWords = 9;
    if (count($w) <= $maxWords) return $t;
    $keep = max($minWords, min($maxWords, count($w)));
    return implode(' ', array_slice($w, 0, $keep)) . ' …';
};

$h0CatSlug = $h0['category']['slug'] ?? null;
$h0ArtId = $h0['id'] ?? $h0['slug'] ?? null;
$h0Fallback = vivat_category_fallback_image($h0CatSlug, 800, 600, $h0ArtId, 'h0');
$h0Img = !empty($h0['cover_image_url']) ? $h0['cover_image_url'] : $h0Fallback;
$h0Img = $h0Img ?: $h0Fallback;

$h1CatSlug = $h1['category']['slug'] ?? null;
$h1ArtId = $h1['id'] ?? $h1['slug'] ?? null;
$h1Fallback = vivat_category_fallback_image($h1CatSlug, 411, 237, $h1ArtId, 'h1');
$h1Img = (!empty($h1['cover_image_url']) ? $h1['cover_image_url'] : $h1Fallback) ?: $h1Fallback;

$h3CatSlug = $h3['category']['slug'] ?? null;
$h3ArtId = $h3['id'] ?? $h3['slug'] ?? null;
$h3Fallback = vivat_category_fallback_image($h3CatSlug, 411, 237, $h3ArtId, 'h3');
$h3Img = (!empty($h3['cover_image_url']) ? $h3['cover_image_url'] : $h3Fallback) ?: $h3Fallback;

$h4CatSlug = $h4['category']['slug'] ?? null;
$h4ArtId = $h4['id'] ?? $h4['slug'] ?? null;
$h4Fallback = vivat_category_fallback_image($h4CatSlug, 519, 280, $h4ArtId, 'h4');
$h4Img = (!empty($h4['cover_image_url']) ? $h4['cover_image_url'] : $h4Fallback) ?: $h4Fallback;
?>
<style>
    @media (max-width: 767px) {
        .home-mobile-highlight-grid {
            row-gap: 18px !important;
        }

        .home-mobile-highlight-grid .home-highlight-left-column,
        .home-mobile-highlight-grid .home-highlight-right-column {
            gap: 18px !important;
        }

        .home-highlight-primary {
            height: clamp(320px, 82vw, 420px) !important;
            max-width: none !important;
        }

        .home-highlight-primary-title {
            font-size: 24px !important;
            line-height: 1.2 !important;
        }

        .home-highlight-mobile-title {
            font-size: 24px !important;
            line-height: 1.2 !important;
        }

        .home-highlight-primary-description {
            font-size: 16px !important;
            line-height: 1.35 !important;
        }

        .home-highlight-secondary,
        .home-highlight-standard {
            max-width: none !important;
            min-height: 220px;
            height: auto !important;
        }

        .home-highlight-feature {
            max-width: none !important;
            height: 220px !important;
        }

        .home-highlight-rail {
            display: none !important;
        }

        .home-banner-ad {
            margin-top: 40px !important;
            height: 180px !important;
            padding: 24px !important;
        }

        .home-categories-section {
            margin-top: 40px !important;
        }

        .home-categories-hero {
            min-height: 360px !important;
            height: 360px !important;
        }

        .home-categories-hero-content {
            padding: 18px !important;
        }

        .home-categories-hero h2 {
            font-size: 24px !important;
            line-height: 1.15 !important;
            max-width: 82% !important;
        }

        .home-categories-hero p {
            font-size: 16px !important;
            line-height: 1.3 !important;
            max-width: 88% !important;
        }

        .home-categories-hero-title {
            font-size: 24px !important;
            line-height: 1.15 !important;
            max-width: 82% !important;
        }

        .home-categories-hero-description {
            font-size: 16px !important;
            line-height: 1.3 !important;
            max-width: 88% !important;
        }

        .home-categories-card,
        .home-categories-card-tall {
            height: 220px !important;
        }

        .home-categories-next {
            margin-left: auto !important;
        }

        .home-latest-section {
            margin-top: 40px !important;
        }

        .home-latest-mobile-item {
            height: 240px !important;
        }

        .home-latest-tall-card {
            height: 240px !important;
        }

        .home-latest-wide-card {
            height: 240px !important;
        }

        .home-latest-standard-card {
            min-height: 240px !important;
            height: 240px !important;
        }

        .home-latest-mobile-item .rounded-\[21px\].overflow-hidden {
            height: 104px !important;
        }

        .home-latest-mobile-item .vivat-glass {
            max-width: 78% !important;
        }

        .home-latest-mobile-split {
            flex-direction: row !important;
            align-items: center !important;
            gap: 18px !important;
            padding: 24px !important;
        }

        .home-latest-mobile-split .home-latest-mobile-split-copy {
            flex: 1 1 0 !important;
            justify-content: flex-start !important;
            min-width: 0 !important;
            padding: 8px 0 !important;
        }

        .home-latest-mobile-split .home-latest-mobile-split-image {
            flex: 0 0 182px !important;
            width: 182px !important;
            max-width: 182px !important;
            min-width: 182px !important;
            height: 182px !important;
            max-height: 182px !important;
            min-height: 182px !important;
            border-radius: 21px !important;
            overflow: hidden !important;
            flex-shrink: 0 !important;
        }

        .home-latest-mobile-split .home-latest-mobile-split-image img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            object-position: center !important;
        }

        .home-latest-mobile-hidden {
            display: none !important;
        }

        .home-latest-mobile-toggle {
            display: inline-flex !important;
        }

    }

    @media (min-width: 425px) and (max-width: 767px) {
        .home-categories-section {
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 0.9fr) !important;
            align-items: stretch !important;
        }

        .home-categories-hero {
            min-height: 420px !important;
            height: 420px !important;
        }

        .home-categories-hero-content {
            justify-content: center !important;
            padding: 18px !important;
        }

        .home-categories-hero-title {
            font-size: 24px !important;
            line-height: 1.15 !important;
            max-width: 72% !important;
        }

        .home-categories-hero-description {
            font-size: 16px !important;
            line-height: 1.3 !important;
            max-width: 82% !important;
        }

        .home-categories-side {
            flex-direction: row !important;
            align-items: stretch !important;
            gap: 0 !important;
        }

        .categories-carousel {
            width: 100%;
        }

        .categories-group {
            grid-template-columns: 1fr !important;
            grid-template-rows: none !important;
            height: 100%;
        }

        .categories-group > div {
            display: none !important;
        }

        .home-categories-card-tall {
            height: 420px !important;
        }

        .home-categories-card-tall span {
            font-size: 24px !important;
        }

        .home-categories-next {
            display: flex !important;
            margin-left: -21px !important;
            align-self: center !important;
        }

        .home-highlight-mobile-title,
        .home-latest-section h3:not(.home-highlight-primary-title),
        .home-highlight-standard h3,
        .home-highlight-feature h3:not(.home-highlight-primary-title),
        .home-highlight-secondary h3,
        .home-latest-tall-card h3,
        .home-latest-wide-card h3,
        .home-latest-standard-card h3 {
            font-size: 18px !important;
            line-height: 1.2 !important;
        }

        .home-highlight-mobile-pair {
            display: flex;
            flex-wrap: wrap;
            gap: 18px !important;
        }

        .home-highlight-mobile-pair-article,
        .home-highlight-mobile-pair-cta {
            width: calc(50% - 9px) !important;
            min-width: 0;
        }

        .home-highlight-mobile-pair-article {
            height: 320px !important;
            max-width: none !important;
        }

        .home-highlight-mobile-blur {
            min-height: 205px !important;
        }

        .home-highlight-mobile-pair-cta {
            height: 320px !important;
        }
    }

    @media (min-width: 768px) and (max-width: 1023px) {
        .home-highlight-primary {
            max-width: none !important;
            height: 420px !important;
        }

        .home-highlight-secondary {
            max-width: none !important;
            min-height: 204px;
            height: 204px !important;
        }

        .home-highlight-feature,
        .home-highlight-standard {
            max-width: none !important;
            height: 204px !important;
        }

        .home-categories-hero {
            min-height: 460px !important;
            height: 460px !important;
        }

        .home-categories-side {
            align-items: stretch !important;
            gap: 0 !important;
        }

        .categories-group {
            grid-template-columns: 1fr !important;
            grid-template-rows: none !important;
        }

        .categories-group > div {
            display: none !important;
        }

        .home-categories-card-tall {
            height: 460px !important;
        }

        .home-categories-card-tall span {
            font-size: 24px !important;
        }

        .home-categories-hero h2 {
            font-size: 24px !important;
            line-height: 1.15 !important;
            max-width: 80% !important;
        }

        .home-categories-hero p {
            font-size: 16px !important;
            line-height: 1.3 !important;
            max-width: 88% !important;
        }

        .home-categories-hero-title {
            font-size: 24px !important;
            line-height: 1.15 !important;
            max-width: 80% !important;
        }

        .home-categories-hero-description {
            font-size: 16px !important;
            line-height: 1.3 !important;
            max-width: 88% !important;
        }

        .home-categories-next {
            margin-left: -21px !important;
            align-self: center !important;
        }

        .home-latest-tall-card {
            height: 390px !important;
        }

        .home-latest-primary-column {
            grid-column: span 8 / span 8;
        }

        .home-latest-secondary-column {
            display: none !important;
        }

        .home-latest-section.is-tablet-expanded .home-latest-secondary-column {
            display: flex !important;
            grid-column: span 8 / span 8;
        }

        .home-latest-tablet-toggle {
            display: inline-flex !important;
            width: auto !important;
            min-width: 0 !important;
            padding: 12px 24px !important;
        }

        .home-latest-desktop-link {
            display: none !important;
        }
    }

    @media (min-width: 1024px) and (max-width: 1279px) {
        .home-highlight-left-column {
            height: 100%;
            justify-content: space-between;
        }

        .home-highlight-secondary-article {
            height: 423px !important;
        }

        .home-highlight-feature,
        .home-highlight-standard {
            max-width: none !important;
        }
    }

    .home-latest-tablet-toggle {
        display: none;
    }

</style>
<!-- Bandeau publicitaire header 728×90 : uniquement en version tablette (834px), masqué en mobile et desktop -->
<div class="hidden md:block lg:hidden w-full mb-6">
    <div class="rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm flex items-center justify-center mx-auto w-full max-w-[728px] h-[90px]">
        Publicité 728×90
    </div>
</div>
<!-- Grille articles tablette dédiée : bloc plus compact et carré -->
<div class="vivat-reveal-group hidden md:grid lg:hidden grid-cols-8 w-full" style="column-gap: 24px; row-gap: 24px;">
    <?php if ($h0): ?>
    <a href="/articles/<?= htmlspecialchars($h0['slug']) ?>" class="home-highlight-primary vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group col-span-5 block rounded-[30px] overflow-hidden relative w-full">
        <img src="<?= htmlspecialchars($h0Img) ?>" data-fallback-url="<?= htmlspecialchars($h0Fallback) ?>" alt="<?= htmlspecialchars($h0['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="eager">
        <div class="absolute inset-0" style="background: rgba(0,0,0,0.3);"></div>
        <div class="absolute flex items-end" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
            <div class="rounded-[21px] flex flex-col vivat-glass w-full max-w-[300px]" style="gap: 6px;">
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: #EBF1EF; color: #004241;">Top news</span>
                <h2 class="font-semibold text-white line-clamp-4" style="font-size: 24px; font-family: Figtree, sans-serif;"><?= htmlspecialchars($truncateGlassTitle($h0['title'] ?? '')) ?></h2>
                <?php if (!empty($h0['excerpt'])): ?>
                <p class="text-white/90 line-clamp-3" style="font-size: 14px;"><?= htmlspecialchars($h0['excerpt']) ?></p>
                <?php endif; ?>
                <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($h0['published_at'] ?? '') ?> • <?= (int) ($h0['reading_time'] ?? 0) ?> min</p>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <div class="col-span-3 flex flex-col" style="gap: 24px;">
        <?php if ($h2): ?>
        <a href="/articles/<?= htmlspecialchars($h2['slug']) ?>" class="home-highlight-standard vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-dark block rounded-[30px] overflow-hidden border border-[#004241]/20 flex flex-col justify-end w-full" style="padding: 24px; gap: 8px; background: #004241;">
            <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-white/25 text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
            <?php if (!empty($h2['category'])): ?>
            <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['vert']['bg'] ?>; color: <?= $tagStyles['vert']['color'] ?>;"><?= htmlspecialchars($h2['category']['name']) ?></span>
            <?php endif; ?>
            <h3 class="font-semibold text-white line-clamp-3" style="font-size: 18px;"><?= htmlspecialchars($h2['title']) ?></h3>
            <p class="text-white/70" style="font-size: 12px;"><?= htmlspecialchars($h2['published_at'] ?? '') ?> • <?= (int) ($h2['reading_time'] ?? 0) ?> min</p>
        </a>
        <?php endif; ?>

        <?php if ($h3): ?>
        <a href="/articles/<?= htmlspecialchars($h3['slug']) ?>" class="home-highlight-feature vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full">
            <img src="<?= htmlspecialchars($h3Img) ?>" data-fallback-url="<?= htmlspecialchars($h3Fallback) ?>" alt="<?= htmlspecialchars($h3['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div class="absolute flex items-end" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                    <?php if (!empty($h3['category'])): ?>
                    <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($h3['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-semibold text-white line-clamp-3" style="font-size: 18px;"><?= htmlspecialchars($truncateGlassTitle($h3['title'] ?? '')) ?></h3>
                    <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($h3['published_at'] ?? '') ?> • <?= (int) ($h3['reading_time'] ?? 0) ?> min</p>
                </div>
            </div>
        </a>
        <?php endif; ?>
    </div>

    <?php if ($h4): ?>
    <a href="/articles/<?= htmlspecialchars($h4['slug']) ?>" class="home-highlight-secondary vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune col-span-5 block rounded-[30px] overflow-hidden border border-gray-200/50 flex flex-col justify-end" style="padding: 24px; gap: 8px; background: #FFF0D4;">
        <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
        <?php if (!empty($h4['category'])): ?>
        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($h4['category']['name']) ?></span>
        <?php endif; ?>
        <h3 class="font-semibold text-[#004241] line-clamp-2" style="font-size: 20px;"><?= htmlspecialchars($h4['title']) ?></h3>
        <p class="text-[#004241]/70" style="font-size: 12px;"><?= htmlspecialchars($h4['published_at'] ?? '') ?> • <?= (int) ($h4['reading_time'] ?? 0) ?> min</p>
    </a>
    <?php endif; ?>

    <?php if ($h1): ?>
    <a href="/articles/<?= htmlspecialchars($h1['slug']) ?>" class="home-highlight-feature vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group col-span-3 block rounded-[30px] overflow-hidden relative w-full">
        <img src="<?= htmlspecialchars($h1Img) ?>" data-fallback-url="<?= htmlspecialchars($h1Fallback) ?>" alt="<?= htmlspecialchars($h1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
        <div class="absolute flex items-end" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
            <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                <?php if (!empty($h1['category'])): ?>
                <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($h1['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-semibold text-white line-clamp-3" style="font-size: 18px;"><?= htmlspecialchars($truncateGlassTitle($h1['title'] ?? '')) ?></h3>
                <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($h1['published_at'] ?? '') ?> • <?= (int) ($h1['reading_time'] ?? 0) ?> min</p>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune col-span-8 flex items-center rounded-[30px] overflow-hidden" style="min-height: 132px; padding: 24px; background: #FFF0D4;">
        <span class="absolute top-auto right-[18px] bottom-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
        <p class="text-[#004241] font-medium leading-snug pr-16" style="font-size: 18px;"><?= htmlspecialchars($writer_cta_description) ?></p>
    </a>

</div>
<!-- Grille articles - mobile 1 col, desktop lg 12 cols -->
<div class="flex flex-col w-full">
    <div class="home-mobile-highlight-grid vivat-reveal-group grid grid-cols-1 md:hidden lg:grid lg:grid-cols-12" style="column-gap: 24px; row-gap: 24px;">
        <!-- Colonne gauche: Top news 462×438 + Standard 2 + CTA | tablet largeur 462px, lg 5 cols -->
        <div class="home-highlight-left-column md:col-span-1 lg:col-span-5 flex flex-col" style="gap: 24px;">
            <?php if ($h0): ?>
            <?php $h0CatSlug = $h0['category']['slug'] ?? null; $h0ArtId = $h0['id'] ?? $h0['slug'] ?? null; $h0Fallback = vivat_category_fallback_image($h0CatSlug, 800, 600, $h0ArtId, 'h0'); $h0Img = !empty($h0['cover_image_url']) ? $h0['cover_image_url'] : $h0Fallback; $h0Img = $h0Img ?: $h0Fallback; ?>
            <!-- Highlight 0: grande carte 462×438 tablet, 519×438 lg -->
            <a href="/articles/<?= htmlspecialchars($h0['slug']) ?>" class="home-highlight-primary vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full lg:max-w-[519px]" style="height: 438px;">
                <img src="<?= htmlspecialchars($h0Img) ?>" data-fallback-url="<?= htmlspecialchars($h0Fallback) ?>" alt="<?= htmlspecialchars($h0['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="eager">
                <div class="absolute inset-0" style="background: rgba(0,0,0,0.3);"></div>
                <!-- Carré glass : 18px d’espace depuis le bord de la carte -->
                <div class="absolute flex items-end" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-full max-w-[300px]" style="gap: 6px;">
                    <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: #EBF1EF; color: #004241;">Top news</span>
                    <h2 class="home-highlight-primary-title font-semibold text-white line-clamp-4" style="font-size: 32px; font-family: Figtree, sans-serif;"><?= htmlspecialchars($truncateGlassTitle($h0['title'] ?? '')) ?></h2>
                    <?php if (!empty($h0['excerpt'])): ?>
                    <p class="home-highlight-primary-description text-white/90 line-clamp-3" style="font-size: 14px;"><?= htmlspecialchars($h0['excerpt']) ?></p>
                    <?php endif; ?>
                    <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($h0['published_at'] ?? '') ?> • <?= (int) ($h0['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($h4): ?>
            <!-- Highlight 4: Standard 2 - 519x280, #FFF0D4 -->
            <a href="/articles/<?= htmlspecialchars($h4['slug']) ?>" class="home-highlight-secondary-article home-highlight-secondary vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune block rounded-[30px] overflow-hidden border border-gray-200/50 flex flex-col justify-end" style="width: 100%; max-width: 519px; height: 280px; padding: 24px; gap: 8px; background: #FFF0D4;">
                <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <?php if (!empty($h4['category'])): ?>
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($h4['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="home-highlight-mobile-title font-semibold text-[#004241] line-clamp-2" style="font-size: 20px;"><?= htmlspecialchars($h4['title']) ?></h3>
                <p class="text-[#004241]/70" style="font-size: 12px;"><?= htmlspecialchars($h4['published_at'] ?? '') ?> • <?= (int) ($h4['reading_time'] ?? 0) ?> min</p>
    </a>
    <?php endif; ?>

</div>

        <!-- Colonne droite: Feature 1 + carte verte (270×221) + Feature 2 | tablet 1 col, lg 4 cols -->
        <div class="home-highlight-right-column md:col-span-1 lg:col-span-7 xl:col-span-4 flex flex-col" style="gap: 24px;">
            <?php if ($h1): ?>
            <?php $h1CatSlug = $h1['category']['slug'] ?? null; $h1ArtId = $h1['id'] ?? $h1['slug'] ?? null; $h1Fallback = vivat_category_fallback_image($h1CatSlug, 411, 237, $h1ArtId, 'h1'); $h1Img = (!empty($h1['cover_image_url']) ? $h1['cover_image_url'] : $h1Fallback) ?: $h1Fallback; ?>
            <!-- Highlight 1: Feature 411x237 -->
            <a href="/articles/<?= htmlspecialchars($h1['slug']) ?>" class="home-highlight-feature vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full" style="max-width: 411px; height: 237px;">
                <img src="<?= htmlspecialchars($h1Img) ?>" data-fallback-url="<?= htmlspecialchars($h1Fallback) ?>" alt="<?= htmlspecialchars($h1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                <div class="absolute flex items-end" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                    <div class="home-highlight-mobile-blur rounded-[21px] flex flex-col vivat-glass w-fit max-w-[60%]" style="gap: 6px; min-width: min(100%, 220px);">
                        <?php if (!empty($h1['category'])): ?>
                        <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($h1['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-semibold text-white line-clamp-3" style="font-size: 18px;"><?= htmlspecialchars($truncateGlassTitle($h1['title'] ?? '')) ?></h3>
                        <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($h1['published_at'] ?? '') ?> • <?= (int) ($h1['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($h2): ?>
            <!-- Highlight 2: Standard 1 (carte verte) - tablet 270×221, lg 413×221 -->
            <a href="/articles/<?= htmlspecialchars($h2['slug']) ?>" class="home-highlight-standard vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-dark block rounded-[30px] overflow-hidden border border-[#004241]/20 flex flex-col justify-end w-full md:max-w-[270px] lg:max-w-[413px] lg:w-full" style="height: 221px; padding: 24px; gap: 8px; background: #004241;">
                <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-white/25 text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <?php if (!empty($h2['category'])): ?>
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['vert']['bg'] ?>; color: <?= $tagStyles['vert']['color'] ?>;"><?= htmlspecialchars($h2['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-semibold text-white line-clamp-2" style="font-size: 20px;"><?= htmlspecialchars($h2['title']) ?></h3>
                <p class="text-white/70" style="font-size: 12px;"><?= htmlspecialchars($h2['published_at'] ?? '') ?> • <?= (int) ($h2['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php endif; ?>

            <div class="home-highlight-mobile-pair">
            <?php if ($h3): ?>
            <?php $h3CatSlug = $h3['category']['slug'] ?? null; $h3ArtId = $h3['id'] ?? $h3['slug'] ?? null; $h3Fallback = vivat_category_fallback_image($h3CatSlug, 411, 237, $h3ArtId, 'h3'); $h3Img = (!empty($h3['cover_image_url']) ? $h3['cover_image_url'] : $h3Fallback) ?: $h3Fallback; ?>
            <!-- Highlight 3: Feature 2 - 411x237 -->
            <a href="/articles/<?= htmlspecialchars($h3['slug']) ?>" class="home-highlight-mobile-pair-article home-highlight-feature vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full" style="max-width: 411px; height: 237px;">
                <img src="<?= htmlspecialchars($h3Img) ?>" data-fallback-url="<?= htmlspecialchars($h3Fallback) ?>" alt="<?= htmlspecialchars($h3['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                <div class="absolute flex items-end" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-[60%]" style="gap: 6px; min-width: min(100%, 220px);">
                        <?php if (!empty($h3['category'])): ?>
                        <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($h3['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="home-highlight-mobile-title font-semibold text-white line-clamp-3" style="font-size: 18px;"><?= htmlspecialchars($truncateGlassTitle($h3['title'] ?? '')) ?></h3>
                        <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($h3['published_at'] ?? '') ?> • <?= (int) ($h3['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="home-highlight-mobile-pair-cta home-highlight-secondary vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune relative flex flex-col rounded-[30px] overflow-hidden flex-shrink-0 w-full lg:hidden" style="height: 118px; background: #FFF0D4;">
                <span class="absolute top-auto right-[18px] bottom-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <p class="text-[#004241] font-medium text-sm leading-snug flex-1 z-10" style="padding: 18px 18px 0 18px;"><?= htmlspecialchars($writer_cta_description) ?></p>
            </a>
            </div>

            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="hidden lg:flex xl:hidden home-highlight-secondary vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune flex-col rounded-[30px] overflow-hidden w-full" style="min-height: 118px; background: #FFF0D4;">
                <span class="absolute top-auto right-[18px] bottom-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <p class="text-[#004241] font-medium leading-snug flex-1 z-10" style="padding: 24px 88px 24px 24px; font-size: 18px;"><?= htmlspecialchars($writer_cta_description) ?></p>
            </a>

        </div>

        <!-- Colonne droite desktop large: espace pub + CTA, réservée aux écrans xl+ -->
        <div class="home-highlight-rail hidden xl:flex xl:col-span-3 flex-col" style="gap: 24px;">
            <div class="flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm w-full xl:max-w-[300px]" style="height: 600px; padding-right: 48px; padding-bottom: 48px; gap: 8px;">
                <div class="flex-1 flex items-center justify-center">Espace publicitaire</div>
            </div>
            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune relative flex flex-col rounded-[30px] overflow-hidden flex-shrink-0 w-[301px]" style="height: 118px; background: #FFF0D4;">
                <span class="absolute top-auto right-[18px] bottom-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <p class="text-[#004241] font-medium text-sm leading-snug flex-1 z-10" style="padding: 18px 18px 0 18px;"><?= htmlspecialchars($writer_cta_description) ?></p>
            </a>
        </div>
    </div>

    <!-- 2ème pub : en dessous des articles, 65px de marge -->
    <div class="home-banner-ad flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm overflow-hidden" style="width: 100%; max-width: 970px; height: 250px; padding: 48px; gap: 8px; margin-top: 65px;">
        <div class="flex-1 flex items-center justify-center">Espace publicitaire (bannière)</div>
    </div>

    <?php if (count($categories) > 0): ?>
    <!-- Découvrez vos rubriques - tablet 8 cols (24px gap), lg 12 cols -->
    <section id="categories-section" class="home-categories-section grid grid-cols-1 md:grid-cols-8 lg:grid-cols-12 w-full" style="margin-top: 65px; column-gap: 24px; row-gap: 24px;">
            <!-- Grande carte gauche | tablet 4 cols, lg 7 cols -->
            <?php $firstCat = $categories[0] ?? null; ?>
            <a href="<?= $firstCat ? '/categories/'.htmlspecialchars($firstCat['slug']) : '/' ?>" class="home-categories-hero vivat-card-with-image group md:col-span-5 lg:col-span-7 rounded-[30px] overflow-hidden relative block w-full min-h-[523px]" style="height: 523px;">
                <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800" alt="Découvrez vos rubriques préférées sur Vivat" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                <div class="home-categories-hero-content absolute inset-0 flex flex-col items-start justify-center" style="padding: 32px;">
                    <h2 class="home-categories-hero-title font-semibold text-white text-left max-w-[85%]" style="font-family: Figtree, sans-serif; font-size: 48px; font-weight: 600;">Découvrez vos rubriques préférées</h2>
                    <p class="home-categories-hero-description text-white/95 mt-2 text-left max-w-[85%]" style="font-size: 24px; font-weight: 400;">Explorez dès maintenant les contenus qui vous correspondent.</p>
                </div>
            </a>

            <!-- Droite: 3 petites cartes + flèche | tablet 4 cols, lg 5 cols -->
            <div class="home-categories-side md:col-span-3 lg:col-span-5 flex items-center w-full min-w-0" style="gap: 24px;">
                <div class="categories-carousel flex items-stretch min-w-0 flex-1" style="gap: 24px;">
                    <?php foreach ($catChunks as $chunkIdx => $chunk):
                        $cat1 = $chunk[0] ?? null;
                        $cat2 = $chunk[1] ?? null;
                        $cat3 = $chunk[2] ?? null;
                    ?>
                    <div class="categories-group grid <?= $chunkIdx > 0 ? 'hidden' : '' ?>" data-group="<?= $chunkIdx ?>" style="grid-template-columns: 1fr 1fr; grid-template-rows: auto auto; gap: 24px; width: 100%; min-width: 0;">
                        <!-- 2 petites cartes à gauche (1 col, 2 rows) -->
                        <div class="flex flex-col min-w-0" style="gap: 24px; grid-row: span 2;">
                            <?php if ($cat1): ?>
                            <a href="/categories/<?= htmlspecialchars($cat1['slug']) ?>" class="home-categories-card vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full flex-shrink-0 min-h-0" style="height: 250px;">
                                <?php if (!empty($cat1['image_url'])): ?>
                                <img src="<?= htmlspecialchars($cat1['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat1['name']) ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                                <?php endif; ?>
                                <div class="absolute inset-0 transition-opacity duration-300" style="background: rgba(0,0,0,0.3);"></div>
                                <div class="absolute inset-0 bg-[#004241] opacity-0 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true"></div>
                                <div class="absolute inset-0 flex items-center justify-center" style="padding: 18px;">
                                    <span class="text-white font-semibold text-center" style="font-size: 20px;"><?= htmlspecialchars($cat1['name']) ?></span>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php if ($cat2): ?>
                            <a href="/categories/<?= htmlspecialchars($cat2['slug']) ?>" class="home-categories-card vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full flex-shrink-0 min-h-0" style="height: 250px;">
                                <?php if (!empty($cat2['image_url'])): ?>
                                <img src="<?= htmlspecialchars($cat2['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat2['name']) ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                                <?php endif; ?>
                                <div class="absolute inset-0 transition-opacity duration-300" style="background: rgba(0,0,0,0.3);"></div>
                                <div class="absolute inset-0 bg-[#004241] opacity-0 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true"></div>
                                <div class="absolute inset-0 flex items-center justify-center" style="padding: 18px;">
                                    <span class="text-white font-semibold text-center" style="font-size: 20px;"><?= htmlspecialchars($cat2['name']) ?></span>
                                </div>
    </a>
                            <?php endif; ?>
                        </div>

                        <!-- Grande carte à droite (1 col, span 2 rows) -->
                        <?php if ($cat3): ?>
                        <a href="/categories/<?= htmlspecialchars($cat3['slug']) ?>" class="home-categories-card-tall vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full min-w-0" style="grid-row: span 2; height: 523px;">
                            <?php if (!empty($cat3['image_url'])): ?>
                            <img src="<?= htmlspecialchars($cat3['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat3['name']) ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                            <?php endif; ?>
                            <div class="absolute inset-0 transition-opacity duration-300" style="background: rgba(0,0,0,0.3);"></div>
                            <div class="absolute inset-0 bg-[#004241] opacity-0 group-hover:opacity-100 transition-opacity duration-300" aria-hidden="true"></div>
                            <div class="absolute inset-0 flex items-center justify-center" style="padding: 18px;">
                                <span class="text-white font-semibold text-center" style="font-size: 20px;"><?= htmlspecialchars($cat3['name']) ?></span>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($catChunks) > 1): ?>
                <!-- Flèche: 42x42, sur le bord de la dernière carte, 80px zone blanche à droite -->
                <button type="button" id="categories-next" class="home-categories-next flex-shrink-0 flex items-center justify-center rounded-full bg-[#004241] text-white hover:bg-[#003535] transition relative z-10 -ml-[45px] box-border" style="width: 42px; height: 42px; border-radius: 29px; padding: 8px;" aria-label="Rubriques suivantes">
                    <svg class="w-6 h-6 flex-shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
                <?php endif; ?>
            </div>
    </section>
    <?php if (count($catChunks) > 1): ?>
    <script>
    (function() {
        var groups = document.querySelectorAll('.categories-group');
        var nextBtn = document.getElementById('categories-next');
        if (!nextBtn || groups.length < 2) return;
        var idx = 0;
        nextBtn.addEventListener('click', function() {
            groups[idx].classList.add('hidden');
            idx = (idx + 1) % groups.length;
            groups[idx].classList.remove('hidden');
        });
    })();
    </script>
    <?php endif; ?>
    <?php endif; ?>

    <?php
    $highlightIds = array_filter([
        ($h0 ?? [])['id'] ?? null,
        ($h1 ?? [])['id'] ?? null,
        ($h2 ?? [])['id'] ?? null,
        ($h3 ?? [])['id'] ?? null,
        ($h4 ?? [])['id'] ?? null,
    ]);
    // $latest est déjà ordonné du plus récent au moins récent (hors highlights) par le backend
    $restArticles = array_values(array_filter($latest, fn($a) => !in_array($a['id'] ?? null, $highlightIds)));
    // Une seule occurrence par article : déduplication stricte par id puis par slug (évite doublons même si 2 lignes en base)
    $byId = [];
    foreach ($restArticles as $a) {
        $id = $a['id'] ?? null;
        if ($id !== null && !isset($byId[$id])) {
            $byId[$id] = $a;
        }
    }
    $bySlug = [];
    foreach (array_values($byId) as $a) {
        $slug = $a['slug'] ?? null;
        if ($slug !== null && $slug !== '' && !isset($bySlug[$slug])) {
            $bySlug[$slug] = $a;
        }
    }
    $restArticles = array_values($bySlug);
    // Pas de duplication : chaque slot affiche un article différent.
    // Indices déjà réservés : 0,1 (première ligne), 2 (hot), 3,4 (ligne), 6 (artLeft2), 10 (artLeft), 11 (artRight).
    // Les 2 cartes "photo complète" prennent des articles uniquement parmi les indices 5, 7, 8, 9, … (jamais 6, 10, 11).
    $reservedIndices = [0, 1, 2, 3, 4, 6, 10, 11];
    $restForPhotos = [];
    foreach ($restArticles as $idx => $a) {
        if (! in_array($idx, $reservedIndices, true)) {
            $restForPhotos[] = $a;
        }
    }
    $withCover = array_values(array_filter($restForPhotos, fn($a) => !empty($a['cover_image_url'])));
    $artForFullPhoto1 = $withCover[0] ?? $restForPhotos[0] ?? $restArticles[5] ?? null;
    $artForFullPhoto2 = (count($withCover) > 1) ? $withCover[1] : ($restForPhotos[1] ?? $restArticles[7] ?? null);
    if ($artForFullPhoto2 !== null && $artForFullPhoto1 !== null && ($artForFullPhoto2['id'] ?? null) === ($artForFullPhoto1['id'] ?? null)) {
        $full1Id = $artForFullPhoto1['id'] ?? null;
        $artForFullPhoto2 = null;
        foreach ($restForPhotos as $a) {
            if (($a['id'] ?? null) !== $full1Id) { $artForFullPhoto2 = $a; break; }
        }
        $artForFullPhoto2 = $artForFullPhoto2 ?? $restArticles[7] ?? null;
    }
    ?>
    <?php if (count($restArticles) > 0): ?>
    <section class="home-latest-section vivat-reveal-group grid grid-cols-1 md:grid-cols-8 lg:grid-cols-12 w-full min-w-0" style="margin-top: 24px; column-gap: 24px; row-gap: 24px;">
        <!-- Titre: Figtree 32px Medium -->
        <h2 class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out font-medium text-[#004241] mb-6 md:col-span-8 lg:col-span-12" style="font-size: 32px;">Dernières actualités</h2>

        <!-- Colonne gauche | tablet 4 cols, lg 6 cols -->
        <div class="home-latest-primary-column md:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php
                $firstArt = $restArticles[0] ?? null;
                $secondArt = $restArticles[1] ?? null;
                ?>
                <?php if ($firstArt): ?>
                <?php $f0CatSlug = $firstArt['category']['slug'] ?? null; $f0ArtId = $firstArt['id'] ?? $firstArt['slug'] ?? null; $f0Fallback = vivat_category_fallback_image($f0CatSlug, 302, 419, $f0ArtId, 'card-0'); $f0Img = !empty($firstArt['cover_image_url']) ? $firstArt['cover_image_url'] : $f0Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($firstArt['slug']) ?>" class="home-latest-mobile-item home-latest-tall-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($f0Img) ?>" data-fallback-url="<?= htmlspecialchars($f0Fallback) ?>" alt="<?= htmlspecialchars($firstArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute flex items-end z-10" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-[60%] min-w-0" style="gap: 6px; min-width: 180px;">
                            <?php if (!empty($firstArt['category'])): ?>
                            <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($firstArt['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($firstArt['title'] ?? '')) ?></h3>
                            <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($firstArt['published_at'] ?? '') ?> • <?= (int) ($firstArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php endif; ?>
                <?php if ($secondArt): ?>
                <a href="/articles/<?= htmlspecialchars($secondArt['slug']) ?>" class="home-latest-mobile-split home-latest-mobile-item home-latest-tall-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #EBF1EF; padding: 24px; gap: 18px;">
                        <div class="home-latest-mobile-split-copy flex flex-col flex-1 min-h-0" style="gap: 8px;">
                            <?php if (!empty($secondArt['category'])): ?>
                            <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($secondArt['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($secondArt['title']) ?></h3>
                            <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($secondArt['published_at'] ?? '') ?> • <?= (int) ($secondArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                        <?php $artCatSlug = $secondArt['category']['slug'] ?? null; $artId = $secondArt['id'] ?? $secondArt['slug'] ?? null; $artFallback = vivat_category_fallback_image($artCatSlug, 254, 190, $artId, 'card-1'); $artImg = !empty($secondArt['cover_image_url']) ? $secondArt['cover_image_url'] : $artFallback; ?>
                    <div class="home-latest-mobile-split-image rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artImg) ?>" data-fallback-url="<?= htmlspecialchars($artFallback) ?>" alt="<?= htmlspecialchars($secondArt['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>
            </div>

            <?php $hotNewsArt = $restArticles[2] ?? null; if ($hotNewsArt): ?>
            <?php $hotCatSlug = $hotNewsArt['category']['slug'] ?? null; $hotArtId = $hotNewsArt['id'] ?? $hotNewsArt['slug'] ?? null; $hotFallback = vivat_category_fallback_image($hotCatSlug, 626, 240, $hotArtId, 'hot'); $hotNewsImg = !empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : $hotFallback; ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="home-latest-mobile-item home-latest-wide-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[32px] overflow-hidden relative min-w-0 w-full" style="height: 240px;">
                    <img src="<?= htmlspecialchars($hotNewsImg) ?>" data-fallback-url="<?= htmlspecialchars($hotFallback) ?>" alt="<?= htmlspecialchars($hotNewsArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute flex justify-end items-end" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-[60%]" style="width: 264px; min-width: min(100%, 240px); gap: 6px;">
                            <?php if (!empty($hotNewsArt['category'])): ?>
                            <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($hotNewsArt['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($hotNewsArt['title'] ?? '')) ?></h3>
                            <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
            </a>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php $artLeft = $restArticles[10] ?? null; if ($artLeft): ?>
                <!-- Carte jaune sans image (variante texte only) : tag + titre + date en bas -->
                <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="home-latest-mobile-item home-latest-tall-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #FFEFD1; padding: 24px; gap: 18px;">
                    <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true">
                        <svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                    <div class="flex flex-col min-h-0" style="gap: 8px;">
                        <?php if (!empty($artLeft['category'])): ?>
                        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($artLeft['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artLeft['title']) ?></h3>
                        <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($artLeft['published_at'] ?? '') ?> • <?= (int) ($artLeft['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php endif; ?>
                <?php $artLeft2 = $restArticles[6] ?? null; if ($artLeft2): ?>
                    <?php $left2CatSlug = $artLeft2['category']['slug'] ?? null; $left2ArtId = $artLeft2['id'] ?? $artLeft2['slug'] ?? null; $left2Fallback = vivat_category_fallback_image($left2CatSlug, 302, 419, $left2ArtId, 'left2'); $artLeft2Img = !empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : $left2Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="home-latest-mobile-item home-latest-tall-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                        <img src="<?= htmlspecialchars($artLeft2Img) ?>" data-fallback-url="<?= htmlspecialchars($left2Fallback) ?>" alt="<?= htmlspecialchars($artLeft2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                        <div class="absolute flex items-end z-10" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                            <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-[60%] min-w-0" style="gap: 6px; min-width: 180px;">
                                <?php if (!empty($artLeft2['category'])): ?>
                                <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($artLeft2['category']['name']) ?></span>
                                <?php endif; ?>
                                <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($artLeft2['title'] ?? '')) ?></h3>
                                <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                            </div>
                        </div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Colonne droite | tablet 4 cols, lg 6 cols -->
        <div class="home-latest-secondary-column md:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
                <?php
                $stdColors = ['#004241', '#FFEFD1'];
                foreach (array_slice($restArticles, 3, 2) as $i => $art):
                    $bg = $stdColors[$i % 2];
                    $isDark = ($bg === '#004241');
                ?>
                <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="home-latest-mobile-item home-latest-standard-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative <?= $isDark ? 'vivat-card-dark' : 'vivat-card-jaune' ?> flex flex-col justify-end rounded-[30px] overflow-hidden border relative min-w-0 w-full" style="height: 198px; padding: 24px; background: <?= $bg ?>; border: 1px solid rgba(255,255,255,0.1); gap: 8px;">
                    <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 <?= $isDark ? 'bg-white/25 text-white' : 'bg-[#004241] text-white' ?>" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                    <?php if (!empty($art['category'])): ?>
                    <?php $tagVariant = $isDark ? 'vert' : 'jaune'; ?>
                    <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles[$tagVariant]['bg'] ?>; color: <?= $tagStyles[$tagVariant]['color'] ?>;"><?= htmlspecialchars($art['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-medium line-clamp-2 <?= $isDark ? 'text-white' : 'text-[#004241]' ?>" style="font-size: 20px;"><?= htmlspecialchars($art['title']) ?></h3>
                    <p class="<?= $isDark ? 'text-white/70' : 'text-[#004241]/70' ?>" style="font-size: 12px;"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
                </a>
                <?php endforeach; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                    <?php $artRight = $restArticles[11] ?? null; if ($artRight): ?>
                    <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="home-latest-mobile-split home-latest-mobile-item home-latest-tall-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; padding: 24px; gap: 18px; background: #EBF1EF;">
                        <div class="home-latest-mobile-split-copy flex flex-col flex-1 min-h-0" style="gap: 8px;">
                            <?php if (!empty($artRight['category'])): ?>
                            <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($artRight['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artRight['title']) ?></h3>
                            <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                        </div>
                        <?php $rightCatSlug = $artRight['category']['slug'] ?? null; $rightArtId = $artRight['id'] ?? $artRight['slug'] ?? null; $rightFallback = vivat_category_fallback_image($rightCatSlug, 254, 190, $rightArtId, 'right'); $artRightImg = !empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : $rightFallback; ?>
                        <div class="home-latest-mobile-split-image rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                            <img src="<?= htmlspecialchars($artRightImg) ?>" data-fallback-url="<?= htmlspecialchars($rightFallback) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                        </div>
                    </a>
                    <?php endif; ?>
                    <?php if ($artForFullPhoto1): ?>
                    <?php $full1CatSlug = $artForFullPhoto1['category']['slug'] ?? null; $full1ArtId = $artForFullPhoto1['id'] ?? $artForFullPhoto1['slug'] ?? null; $full1Fallback = vivat_category_fallback_image($full1CatSlug, 302, 419, $full1ArtId, 'full1'); $fullPhoto1Img = !empty($artForFullPhoto1['cover_image_url']) ? $artForFullPhoto1['cover_image_url'] : $full1Fallback; ?>
                    <a href="/articles/<?= htmlspecialchars($artForFullPhoto1['slug']) ?>" class="home-latest-mobile-item home-latest-tall-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[25px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                        <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" data-fallback-url="<?= htmlspecialchars($full1Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                        <div class="absolute flex items-end z-10" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                            <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-[60%] min-w-0" style="gap: 6px; min-width: 180px;">
                                <?php if (!empty($artForFullPhoto1['category'])): ?>
                                <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($artForFullPhoto1['category']['name']) ?></span>
                                <?php endif; ?>
                                <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($artForFullPhoto1['title'] ?? '')) ?></h3>
                                <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($artForFullPhoto1['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto1['reading_time'] ?? 0) ?> min</p>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>

            <?php if ($artForFullPhoto2): ?>
            <?php $full2CatSlug = $artForFullPhoto2['category']['slug'] ?? null; $full2ArtId = $artForFullPhoto2['id'] ?? $artForFullPhoto2['slug'] ?? null; $full2Fallback = vivat_category_fallback_image($full2CatSlug, 629, 235, $full2ArtId, 'full2'); $fullPhoto2Img = !empty($artForFullPhoto2['cover_image_url']) ? $artForFullPhoto2['cover_image_url'] : $full2Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($artForFullPhoto2['slug']) ?>" class="home-latest-mobile-item home-latest-wide-card vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full min-w-0" style="height: 235px;">
                    <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" data-fallback-url="<?= htmlspecialchars($full2Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute flex items-end" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-[60%]" style="gap: 6px; min-width: min(100%, 220px);">
                            <?php if (!empty($artForFullPhoto2['category'])): ?>
                            <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($artForFullPhoto2['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($artForFullPhoto2['title'] ?? '')) ?></h3>
                            <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($artForFullPhoto2['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto2['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Bouton Autres actualités -->
        <div class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out flex justify-center md:col-span-8 lg:col-span-12">
            <button type="button" id="latest-tablet-toggle" class="home-latest-tablet-toggle items-center justify-center rounded-full font-medium text-white gap-3 leading-none transition box-border" style="height: 48px; background: #004241; padding: 12px 18px;">
                Autres articles
                <svg class="w-5 h-5 flex-shrink-0 align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 5v14m0 0-6-6m6 6 6-6"/></svg>
            </button>
            <a href="/articles" class="home-latest-desktop-link inline-flex items-center justify-center rounded-full font-medium text-white gap-2.5 transition box-border" style="width: 226px; height: 48px; background: #004241; padding: 12px 18px;">
                Autres actualités
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>
    <script>
    (function() {
        var latestSection = document.querySelector('.home-latest-section');
        var latestToggle = document.getElementById('latest-tablet-toggle');

        if (!latestSection) {
            return;
        }

        if (latestToggle) {
            latestToggle.addEventListener('click', function() {
                var isExpanded = latestSection.classList.toggle('is-tablet-expanded');
                latestToggle.innerHTML = isExpanded
                    ? 'Moins d\\'articles <svg class="w-5 h-5 flex-shrink-0 align-middle rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 5v14m0 0-6-6m6 6 6-6"/></svg>'
                    : 'Autres articles <svg class="w-5 h-5 flex-shrink-0 align-middle" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 5v14m0 0-6-6m6 6 6-6"/></svg>';
            });
        }
    })();
    </script>
    <?php endif; ?>

    <?php if ($pagination && $pagination->lastPage() > 1): ?>
    <?php
    $paginationView = $pagination->withQueryString();
    $pageWindowStart = max(1, $paginationView->currentPage() - 2);
    $pageWindowEnd = min($paginationView->lastPage(), $paginationView->currentPage() + 2);
    ?>
    <nav class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out mt-10 flex flex-wrap items-center justify-center gap-3" aria-label="Pagination des actualités">
        <?php if ($paginationView->onFirstPage()): ?>
        <span class="inline-flex h-12 min-w-12 items-center justify-center rounded-full px-5 text-sm font-medium text-[#004241]/35" style="background: #EBF1EF;">Précédent</span>
        <?php else: ?>
        <a href="<?= htmlspecialchars($paginationView->previousPageUrl()) ?>" class="inline-flex h-12 min-w-12 items-center justify-center rounded-full px-5 text-sm font-medium text-white transition hover:opacity-90" style="background: #004241;">Précédent</a>
        <?php endif; ?>

        <?php for ($page = $pageWindowStart; $page <= $pageWindowEnd; $page++): ?>
        <?php $isActivePage = $page === $paginationView->currentPage(); ?>
        <?php if ($isActivePage): ?>
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full text-sm font-semibold text-white" style="background: #004241;"><?= $page ?></span>
        <?php else: ?>
        <a href="<?= htmlspecialchars($paginationView->url($page)) ?>" class="inline-flex h-12 w-12 items-center justify-center rounded-full text-sm font-semibold text-[#004241] transition hover:bg-[#EBF1EF]" style="background: #FFF0D4;"><?= $page ?></a>
        <?php endif; ?>
        <?php endfor; ?>

        <?php if ($paginationView->hasMorePages()): ?>
        <a href="<?= htmlspecialchars($paginationView->nextPageUrl()) ?>" class="inline-flex h-12 min-w-12 items-center justify-center rounded-full px-5 text-sm font-medium text-white transition hover:opacity-90" style="background: #004241;">Suivant</a>
        <?php else: ?>
        <span class="inline-flex h-12 min-w-12 items-center justify-center rounded-full px-5 text-sm font-medium text-[#004241]/35" style="background: #EBF1EF;">Suivant</span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

</div>
