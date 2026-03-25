<?php
$category = $category ?? [];
$description = $description ?? '';
$total_published = $total_published ?? 0;
$current_sub_category_slug = $current_sub_category_slug ?? null;
$current_sub_category_name = $current_sub_category_name ?? null;
$current_sub_category_slugs = array_values($current_sub_category_slugs ?? ($current_sub_category_slug ? [$current_sub_category_slug] : []));
$current_sub_category_names = array_values($current_sub_category_names ?? ($current_sub_category_name ? [$current_sub_category_name] : []));
$sub_categories = $sub_categories ?? [];
$articles = $articles ?? [];
$pagination = $pagination ?? null;
$category_name = $category['name'] ?? 'Rubrique';
$category_slug = $category['slug'] ?? '';
$category_image_url = $category['image_url'] ?? null;
// Badge des articles : sous-catégorie active si filtre sélectionné, sinon nom de la catégorie
$badgeLabel = count($current_sub_category_names) === 1 ? ($current_sub_category_names[0] ?? $category_name) : $category_name;

// Tags — mêmes classes que home pour cohérence visuelle
$tagBase = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';
$tagGlassTailwind = 'bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$tagGlassOnImage = $tagBase.' '.$tagGlassTailwind.' text-white';
$tagOnYellowCard = $tagBase.' bg-[#004241] text-white';
$tagOnGreenCard = $tagBase.' bg-[#527E7E] text-white';
$hubImageTitle = 'font-semibold text-white line-clamp-4 text-xl';
$hubImageTitleCompact = 'font-semibold text-white line-clamp-4 text-lg';
$hubImageMeta = 'text-white/80 text-xs';
$hubColorTitle = 'font-semibold leading-tight line-clamp-3 text-xl';
$hubColorExcerptLight = 'line-clamp-2 text-sm text-[#004241]/72';
$hubColorExcerptDark = 'line-clamp-2 text-sm text-white/75';
$hubColorMetaLight = 'text-[#004241]/70 text-xs';
$hubColorMetaDark = 'text-white/70 text-xs';
$articleType = static function (array $article): string {
    $type = (string) ($article['article_type'] ?? 'standard');

    return $type !== '' ? $type : 'standard';
};
$isImageArticle = static fn (array $article): bool => in_array($articleType($article), ['hot_news', 'long_form'], true)
    || ! empty($article['cover_image_url']);
$isEditorialArticle = static fn (array $article): bool => $articleType($article) === 'standard';
$pickHubArticle = static function (array &$pool, callable ...$predicates): ?array {
    foreach ($predicates as $predicate) {
        foreach ($pool as $index => $article) {
            if (! $predicate($article)) {
                continue;
            }

            unset($pool[$index]);

            return $article;
        }
    }

    return array_shift($pool) ?: null;
};
$buildSubCategoryUrl = static function (?string $clickedSlug) use ($category_slug, $current_sub_category_slugs): string {
    $baseUrl = '/categories/'.$category_slug;

    if ($clickedSlug === null || $clickedSlug === '') {
        return $baseUrl;
    }

    $selected = $current_sub_category_slugs;
    $isSelected = in_array($clickedSlug, $selected, true);

    if ($isSelected) {
        $next = array_values(array_filter($selected, static fn (string $slug): bool => $slug !== $clickedSlug));
    } else {
        $next = array_values(array_unique([...$selected, $clickedSlug]));
    }

    return $next === [] ? $baseUrl : $baseUrl.'?'.http_build_query(['sub_category' => $next]);
};

// Template séquentiel : on remplit les emplacements 0,1,2,... avec les articles dispo (sans espaces vides).
// Une seule occurrence par article : déduplication stricte par id (évite le même article en 2 designs)
$byId = [];
foreach (array_values($articles) as $a) {
    $id = $a['id'] ?? null;
    if ($id !== null && ! isset($byId[$id])) {
        $byId[$id] = $a;
    }
}
$restArticles = array_values($byId);
?>
<style>
    .vivat-reveal {
        opacity: 1 !important;
        transform: none !important;
    }

    .vivat-card-with-image,
    .vivat-card-no-image {
        box-shadow: 0 18px 40px rgba(0, 66, 65, 0.06);
        transition: transform 220ms ease, box-shadow 220ms ease;
    }

    .vivat-card-with-image:hover,
    .vivat-card-no-image:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 48px rgba(0, 66, 65, 0.08);
    }
    .vivat-card-jaune {
        background: #FFF0B6 !important;
    }

    .vivat-card-dark {
        background: #004241 !important;
    }

    .category-filter-chip--selected {
        padding-right: 18px !important;
        transition: padding-right 200ms ease;
    }

    .category-filter-chip--selected:hover {
        padding-right: 42px !important;
    }

</style>
<div class="flex flex-col w-full">
    <!-- 1) Marge 24px sous la navbar déjà gérée par le main -->

    <!-- 2) Pub 728x90 - flow vertical, border 1px, padding right/bottom/left 48px, gap 8 -->
    <div class="flex flex-col rounded-[30px] bg-gray-100 border border-gray-300 text-gray-400 text-sm overflow-hidden box-border pr-12 pb-12 pl-12 gap-2 mb-6">
        <div class="flex items-center justify-center rounded-[30px] border-2 border-dashed border-gray-300 w-full max-w-[728px] h-[90px]">Publicité</div>
    </div>

    <!-- 3) Grand carré hero catégorie: 1280x443, titre + description + filtres dans le carré. Image fixe pour ne jamais changer au filtre/rechargement. -->
    <div class="rounded-[30px] overflow-hidden relative w-full max-w-[1280px] mx-auto h-[443px] mb-6">
        <?php
        // Image hero stable (même URL toujours) pour que la grande photo ne change pas au changement de filtre ou rechargement
        $heroImg = 'https://images.pexels.com/photos/34950/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=1280&h=443&fit=crop';
?>
        <img src="<?= htmlspecialchars($heroImg) ?>" alt="<?= htmlspecialchars($category_name) ?>" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        <div class="absolute left-0 top-0 flex flex-col p-8 max-w-[500px]">
            <h1 class="font-semibold text-white leading-none text-5xl font-sans"><?= htmlspecialchars($category_name) ?></h1>
            <?php if ($description) { ?>
            <p class="font-light text-white/95 mt-4 text-xl font-sans"><?= htmlspecialchars($description) ?></p>
            <?php } ?>
        </div>
        <!-- Filtres dans le carré: left 32px, bottom, marge 11px entre les filtres -->
        <nav id="category-hub-filters" class="absolute flex items-center flex-wrap left-8 bottom-8 gap-[11px]" aria-label="Filtrer par sous-rubrique">
            <?php $allSelected = $current_sub_category_slugs === []; ?>
            <a href="/categories/<?= htmlspecialchars($category_slug) ?>" class="group inline-flex items-center justify-center gap-1 rounded-full font-normal transition box-border shrink-0 <?= $allSelected ? 'relative category-filter-chip--selected' : '' ?>" style="min-width: 66px; height: 42px; padding: 8px 18px; font-family: Figtree, sans-serif; font-size: 16px; line-height: 100%; backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); <?= $allSelected ? 'background: #EBF1EF; color: #004241; border: 1px solid rgba(255,255,255,0.30);' : 'background: rgba(255,255,255,0.44); color: #fff; border: 1px solid rgba(255,255,255,0.12);' ?>">
                Tous
                <?php if ($allSelected) { ?><span class="pointer-events-none absolute right-[18px] top-1/2 inline-flex h-4 w-4 -translate-y-1/2 flex-shrink-0 opacity-0 transition-opacity duration-200 group-hover:opacity-100"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 4L4 12M4 4l8 8" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg></span><?php } ?>
            </a>
            <?php foreach ($sub_categories as $sub) { ?>
            <?php $subSlug = (string) ($sub['slug'] ?? ''); ?>
            <?php $isSelected = in_array($subSlug, $current_sub_category_slugs, true); ?>
            <a href="<?= htmlspecialchars($buildSubCategoryUrl($subSlug)) ?>" class="group inline-flex items-center justify-center gap-1 rounded-full font-normal transition box-border shrink-0 <?= $isSelected ? 'relative category-filter-chip--selected' : '' ?>" style="min-width: 66px; height: 42px; padding: 8px 18px; font-family: Figtree, sans-serif; font-size: 16px; line-height: 100%; backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); <?= $isSelected ? 'background: #EBF1EF; color: #004241; border: 1px solid rgba(255,255,255,0.30);' : 'background: rgba(255,255,255,0.44); color: #fff; border: 1px solid rgba(255,255,255,0.12);' ?>">
                <?= htmlspecialchars($sub['name'] ?? '') ?>
                <?php if ($isSelected) { ?><span class="pointer-events-none absolute right-[18px] top-1/2 inline-flex h-4 w-4 -translate-y-1/2 flex-shrink-0 opacity-0 transition-opacity duration-200 group-hover:opacity-100"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 4L4 12M4 4l8 8" stroke="currentColor" stroke-width="1" stroke-linecap="round"/></svg></span><?php } ?>
            </a>
            <?php } ?>
        </nav>
    </div>

    <?php
    // Premier bloc sous le hero : 6 cartes fixes
    $featuredPool = array_values($restArticles);
$featured0 = $pickHubArticle($featuredPool, $isImageArticle);
$featured1 = $pickHubArticle($featuredPool, $isEditorialArticle);
$featured2 = $pickHubArticle($featuredPool, $isImageArticle);
$featured3 = $pickHubArticle($featuredPool, $isImageArticle);
$featured4 = $pickHubArticle($featuredPool, $isEditorialArticle);
$featured5 = $pickHubArticle($featuredPool, $isImageArticle);
$remainingArticles = array_values($featuredPool);
?>
    <div id="category-hub-results">
    <?php if (count($restArticles) > 0) { ?>
    <section class="vivat-reveal-group grid grid-cols-1 tablet:grid-cols-8 lg:grid-cols-12 w-full min-w-0" style="column-gap: 24px; row-gap: 24px;">
        <div class="tablet:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php if ($featured0) { ?>
                <?php $featured0Slug = $featured0['category']['slug'] ?? null;
                    $featured0Fallback = vivat_category_fallback_image($featured0Slug, 302, 419, $featured0['id'] ?? $featured0['slug'] ?? null, 'hub-first-0');
                    $featured0Img = ! empty($featured0['cover_image_url']) ? $featured0['cover_image_url'] : $featured0Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($featured0['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($featured0Img) ?>" data-fallback-url="<?= htmlspecialchars($featured0Fallback) ?>" alt="<?= htmlspecialchars($featured0['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="vivat-card-overlay flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                            <?php if (! empty($featured0['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                            <?php } ?>
                            <h3 class="<?= $hubImageTitleCompact ?>"><?= htmlspecialchars($featured0['title'] ?? '') ?></h3>
                            <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($featured0['published_at'] ?? '') ?> • <?= (int) ($featured0['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>

                <?php if ($featured1) { ?>
                <a href="/articles/<?= htmlspecialchars($featured1['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #FFF0B6; padding: 24px; gap: 18px;">
                    <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                    <div class="flex flex-col min-h-0" style="gap: 8px;">
                        <?php if (! empty($featured1['category'])) { ?>
                        <span class="<?= $tagOnYellowCard ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        <?php } ?>
                        <h3 class="<?= $hubColorTitle ?> text-[#004241]"><?= htmlspecialchars($featured1['title']) ?></h3>
                        <?php if (! empty($featured1['excerpt'])) { ?>
                        <p class="<?= $hubColorExcerptLight ?>"><?= htmlspecialchars($featured1['excerpt']) ?></p>
                        <?php } ?>
                        <p class="<?= $hubColorMetaLight ?>"><?= htmlspecialchars($featured1['published_at'] ?? '') ?> • <?= (int) ($featured1['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php } ?>
            </div>

            <?php if ($featured2) { ?>
            <?php $featured2Slug = $featured2['category']['slug'] ?? null;
                $featured2Fallback = vivat_category_fallback_image($featured2Slug, 626, 240, $featured2['id'] ?? $featured2['slug'] ?? null, 'hub-first-2');
                $featured2Img = ! empty($featured2['cover_image_url']) ? $featured2['cover_image_url'] : $featured2Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($featured2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[32px] overflow-hidden relative min-w-0 w-full" style="height: 240px;">
                <img src="<?= htmlspecialchars($featured2Img) ?>" data-fallback-url="<?= htmlspecialchars($featured2Fallback) ?>" alt="<?= htmlspecialchars($featured2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="vivat-card-overlay flex justify-end items-end">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                        <?php if (! empty($featured2['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        <?php } ?>
                        <h3 class="<?= $hubImageTitle ?>"><?= htmlspecialchars($featured2['title'] ?? '') ?></h3>
                        <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($featured2['published_at'] ?? '') ?> • <?= (int) ($featured2['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php } ?>
        </div>

        <div class="tablet:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <?php if ($featured3) { ?>
            <?php $featured3Slug = $featured3['category']['slug'] ?? null;
                $featured3Fallback = vivat_category_fallback_image($featured3Slug, 626, 240, $featured3['id'] ?? $featured3['slug'] ?? null, 'hub-first-3');
                $featured3Img = ! empty($featured3['cover_image_url']) ? $featured3['cover_image_url'] : $featured3Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($featured3['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[32px] overflow-hidden relative min-w-0 w-full" style="height: 240px;">
                <img src="<?= htmlspecialchars($featured3Img) ?>" data-fallback-url="<?= htmlspecialchars($featured3Fallback) ?>" alt="<?= htmlspecialchars($featured3['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="vivat-card-overlay flex items-end">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                        <?php if (! empty($featured3['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        <?php } ?>
                        <h3 class="<?= $hubImageTitle ?>"><?= htmlspecialchars($featured3['title'] ?? '') ?></h3>
                        <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($featured3['published_at'] ?? '') ?> • <?= (int) ($featured3['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php } ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php if ($featured4) { ?>
                <a href="/articles/<?= htmlspecialchars($featured4['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-dark flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #004241; padding: 24px; gap: 18px;">
                    <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-white/25 text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                    <div class="flex flex-col min-h-0" style="gap: 8px;">
                        <?php if (! empty($featured4['category'])) { ?>
                        <span class="<?= $tagOnGreenCard ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        <?php } ?>
                        <h3 class="<?= $hubColorTitle ?> text-white"><?= htmlspecialchars($featured4['title']) ?></h3>
                        <?php if (! empty($featured4['excerpt'])) { ?>
                        <p class="<?= $hubColorExcerptDark ?>"><?= htmlspecialchars($featured4['excerpt']) ?></p>
                        <?php } ?>
                        <p class="<?= $hubColorMetaDark ?>"><?= htmlspecialchars($featured4['published_at'] ?? '') ?> • <?= (int) ($featured4['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php } ?>

                <?php if ($featured5) { ?>
                <?php $featured5Slug = $featured5['category']['slug'] ?? null;
                    $featured5Fallback = vivat_category_fallback_image($featured5Slug, 302, 419, $featured5['id'] ?? $featured5['slug'] ?? null, 'hub-first-5');
                    $featured5Img = ! empty($featured5['cover_image_url']) ? $featured5['cover_image_url'] : $featured5Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($featured5['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($featured5Img) ?>" data-fallback-url="<?= htmlspecialchars($featured5Fallback) ?>" alt="<?= htmlspecialchars($featured5['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="vivat-card-overlay flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                            <?php if (! empty($featured5['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                            <?php } ?>
                            <h3 class="<?= $hubImageTitleCompact ?>"><?= htmlspecialchars($featured5['title'] ?? '') ?></h3>
                            <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($featured5['published_at'] ?? '') ?> • <?= (int) ($featured5['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>
        </div>
    </section>
    <?php } else { ?>
    <p class="text-[#004241]/70">Aucun article dans cette rubrique pour le moment.</p>
    <?php } ?>

    <!-- Bloc pub sous les articles : 24px marge, 970×250, padding 48px, fond #686868 -->
    <div class="flex flex-col rounded-[30px] overflow-hidden box-border" style="margin-top: 24px; width: 970px; max-width: 100%; height: 250px; padding: 48px; gap: 8px; background: #686868;">
        <div class="flex-1 flex items-center justify-center text-white/80 text-sm">Espace publicitaire</div>
    </div>

    <?php
    // Après la pub : reste des articles, avec la même structure que la home
    $m = array_values($remainingArticles ?? []);
$firstArt = $pickHubArticle($m, $isImageArticle);
$secondArt = $pickHubArticle($m, $isEditorialArticle, $isImageArticle);
$hotNewsArt = $pickHubArticle($m, $isImageArticle);
$artLeft = $pickHubArticle($m, $isEditorialArticle);
$artLeft2 = $pickHubArticle($m, $isImageArticle);
$compactColor0 = $pickHubArticle($m, $isEditorialArticle);
$compactColor1 = $pickHubArticle($m, $isEditorialArticle);
$compactColorCards = array_values(array_filter([$compactColor0, $compactColor1]));
$artRight = $pickHubArticle($m, $isImageArticle);
$moreFull1 = $pickHubArticle($m, $isImageArticle);
$moreFull2 = $pickHubArticle($m, $isEditorialArticle, $isImageArticle);
$stdColors = ['#004241', '#FFF0B6'];
$hasSecondBlockCards = (bool) array_filter([
    $firstArt,
    $secondArt,
    $hotNewsArt,
    $artLeft,
    $artLeft2,
    $artRight,
    $moreFull1,
    $moreFull2,
    ...$compactColorCards,
]);
?>
    <?php if ($hasSecondBlockCards) { ?>
    <section class="vivat-reveal-group grid grid-cols-1 tablet:grid-cols-8 lg:grid-cols-12 w-full min-w-0" style="margin-top: 24px; column-gap: 24px; row-gap: 24px;">
        <div class="tablet:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php if ($firstArt) { ?>
                <?php $f0CatSlug = $firstArt['category']['slug'] ?? null;
                    $f0ArtId = $firstArt['id'] ?? $firstArt['slug'] ?? null;
                    $f0Fallback = vivat_category_fallback_image($f0CatSlug, 302, 419, $f0ArtId, 'hub-rest-0');
                    $f0Img = ! empty($firstArt['cover_image_url']) ? $firstArt['cover_image_url'] : $f0Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($firstArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($f0Img) ?>" data-fallback-url="<?= htmlspecialchars($f0Fallback) ?>" alt="<?= htmlspecialchars($firstArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="vivat-card-overlay flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                            <?php if (! empty($firstArt['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                            <?php } ?>
                            <h3 class="<?= $hubImageTitleCompact ?>"><?= htmlspecialchars($firstArt['title'] ?? '') ?></h3>
                            <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($firstArt['published_at'] ?? '') ?> • <?= (int) ($firstArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>

                <?php if ($secondArt && $isEditorialArticle($secondArt)) { ?>
                <a href="/articles/<?= htmlspecialchars($secondArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-dark flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #004241; padding: 24px; gap: 18px;">
                    <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-white/25 text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                    <div class="flex flex-col min-h-0" style="gap: 8px;">
                        <?php if (! empty($secondArt['category'])) { ?>
                        <span class="<?= $tagOnGreenCard ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        <?php } ?>
                        <h3 class="<?= $hubColorTitle ?> text-white"><?= htmlspecialchars($secondArt['title']) ?></h3>
                        <?php if (! empty($secondArt['excerpt'])) { ?>
                        <p class="<?= $hubColorExcerptDark ?>"><?= htmlspecialchars($secondArt['excerpt']) ?></p>
                        <?php } ?>
                        <p class="<?= $hubColorMetaDark ?>"><?= htmlspecialchars($secondArt['published_at'] ?? '') ?> • <?= (int) ($secondArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php } elseif ($secondArt) { ?>
                <?php $f1CatSlug = $secondArt['category']['slug'] ?? null;
                    $f1ArtId = $secondArt['id'] ?? $secondArt['slug'] ?? null;
                    $f1Fallback = vivat_category_fallback_image($f1CatSlug, 302, 419, $f1ArtId, 'hub-rest-1');
                    $f1Img = ! empty($secondArt['cover_image_url']) ? $secondArt['cover_image_url'] : $f1Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($secondArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($f1Img) ?>" data-fallback-url="<?= htmlspecialchars($f1Fallback) ?>" alt="<?= htmlspecialchars($secondArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="vivat-card-overlay flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                            <?php if (! empty($secondArt['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                            <?php } ?>
                            <h3 class="<?= $hubImageTitleCompact ?>"><?= htmlspecialchars($secondArt['title'] ?? '') ?></h3>
                            <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($secondArt['published_at'] ?? '') ?> • <?= (int) ($secondArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>

            <?php if ($hotNewsArt) { ?>
            <?php $hotCatSlug = $hotNewsArt['category']['slug'] ?? null;
            $hotArtId = $hotNewsArt['id'] ?? $hotNewsArt['slug'] ?? null;
            $hotFallback = vivat_category_fallback_image($hotCatSlug, 626, 240, $hotArtId, 'hub-rest-hot');
            $hotNewsImg = ! empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : $hotFallback; ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[32px] overflow-hidden relative min-w-0 w-full" style="height: 240px;">
                <img src="<?= htmlspecialchars($hotNewsImg) ?>" data-fallback-url="<?= htmlspecialchars($hotFallback) ?>" alt="<?= htmlspecialchars($hotNewsArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="vivat-card-overlay flex justify-end items-end">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                        <?php if (! empty($hotNewsArt['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        <?php } ?>
                        <h3 class="<?= $hubImageTitle ?>"><?= htmlspecialchars($hotNewsArt['title'] ?? '') ?></h3>
                        <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php } ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php if ($artLeft) { ?>
                <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #FFF0B6; padding: 24px; gap: 18px;">
                    <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                    <div class="flex flex-col min-h-0" style="gap: 8px;">
                        <?php if (! empty($artLeft['category'])) { ?>
                        <span class="<?= $tagOnYellowCard ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        <?php } ?>
                        <h3 class="<?= $hubColorTitle ?> text-[#004241]"><?= htmlspecialchars($artLeft['title']) ?></h3>
                        <?php if (! empty($artLeft['excerpt'])) { ?>
                        <p class="<?= $hubColorExcerptLight ?>"><?= htmlspecialchars($artLeft['excerpt']) ?></p>
                        <?php } ?>
                        <p class="<?= $hubColorMetaLight ?>"><?= htmlspecialchars($artLeft['published_at'] ?? '') ?> • <?= (int) ($artLeft['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php } ?>

                <?php if ($artLeft2) { ?>
                <?php $left2CatSlug = $artLeft2['category']['slug'] ?? null;
            $left2ArtId = $artLeft2['id'] ?? $artLeft2['slug'] ?? null;
            $left2Fallback = vivat_category_fallback_image($left2CatSlug, 302, 419, $left2ArtId, 'hub-rest-left2');
            $artLeft2Img = ! empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : $left2Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($artLeft2Img) ?>" data-fallback-url="<?= htmlspecialchars($left2Fallback) ?>" alt="<?= htmlspecialchars($artLeft2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="vivat-card-overlay flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                            <?php if (! empty($artLeft2['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                            <?php } ?>
                            <h3 class="<?= $hubImageTitleCompact ?>"><?= htmlspecialchars($artLeft2['title'] ?? '') ?></h3>
                            <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>
        </div>

        <div class="tablet:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <?php foreach ($compactColorCards as $i => $art) {
                $bg = $stdColors[$i % 2];
                $isDark = ($bg === '#004241'); ?>
            <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative <?= $isDark ? 'vivat-card-dark' : 'vivat-card-jaune' ?> flex flex-col justify-end rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 198px; padding: 24px; background: <?= $bg ?>; gap: 8px;">
                <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 <?= $isDark ? 'bg-white/25 text-white' : 'bg-[#004241] text-white' ?>" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <?php if (! empty($art['category'])) { ?>
                <span class="<?= $isDark ? $tagOnGreenCard : $tagOnYellowCard ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                <?php } ?>
                <h3 class="<?= $hubColorTitle ?> <?= $isDark ? 'text-white' : 'text-[#004241]' ?>"><?= htmlspecialchars($art['title']) ?></h3>
                <?php if (! empty($art['excerpt'])) { ?>
                <p class="<?= $isDark ? $hubColorExcerptDark : $hubColorExcerptLight ?>"><?= htmlspecialchars($art['excerpt']) ?></p>
                <?php } ?>
                <p class="<?= $isDark ? $hubColorMetaDark : $hubColorMetaLight ?>"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php } ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php if ($artRight) { ?>
                <?php $rightCatSlug = $artRight['category']['slug'] ?? null;
            $rightFallback = vivat_category_fallback_image($rightCatSlug, 302, 419, $artRight['id'] ?? $artRight['slug'] ?? null, 'hub-rest-right');
            $artRightImg = ! empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : $rightFallback; ?>
                <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($artRightImg) ?>" data-fallback-url="<?= htmlspecialchars($rightFallback) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    <div class="vivat-card-overlay flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 6px;">
                            <?php if (! empty($artRight['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                            <?php } ?>
                            <h3 class="<?= $hubImageTitleCompact ?>"><?= htmlspecialchars($artRight['title']) ?></h3>
                            <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>

                <?php if ($moreFull1) { ?>
                <?php $full1CatSlug = $moreFull1['category']['slug'] ?? null;
                    $full1Fallback = vivat_category_fallback_image($full1CatSlug, 302, 419, $moreFull1['id'] ?? $moreFull1['slug'] ?? null, 'hub-rest-full1');
                    $fullPhoto1Img = ! empty($moreFull1['cover_image_url']) ? $moreFull1['cover_image_url'] : $full1Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($moreFull1['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[25px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" data-fallback-url="<?= htmlspecialchars($full1Fallback) ?>" alt="<?= htmlspecialchars($moreFull1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="vivat-card-overlay flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 8px;">
                            <?php if (! empty($moreFull1['category'])) { ?>
                            <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                            <?php } ?>
                            <h3 class="<?= $hubImageTitleCompact ?>"><?= htmlspecialchars($moreFull1['title'] ?? '') ?></h3>
                            <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($moreFull1['published_at'] ?? '') ?> • <?= (int) ($moreFull1['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>
            </div>

            <?php if ($moreFull2 && $isEditorialArticle($moreFull2)) { ?>
            <a href="/articles/<?= htmlspecialchars($moreFull2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-dark flex flex-col justify-end rounded-[30px] overflow-hidden w-full min-w-0" style="height: 235px; background: #004241; padding: 24px; gap: 10px;">
                <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-white/25 text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <?php if (! empty($moreFull2['category'])) { ?>
                <span class="<?= $tagOnGreenCard ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                <?php } ?>
                <h3 class="<?= $hubColorTitle ?> text-white"><?= htmlspecialchars($moreFull2['title'] ?? '') ?></h3>
                <?php if (! empty($moreFull2['excerpt'])) { ?>
                <p class="<?= $hubColorExcerptDark ?>"><?= htmlspecialchars($moreFull2['excerpt']) ?></p>
                <?php } ?>
                <p class="<?= $hubColorMetaDark ?>"><?= htmlspecialchars($moreFull2['published_at'] ?? '') ?> • <?= (int) ($moreFull2['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php } elseif ($moreFull2) { ?>
            <?php $full2CatSlug = $moreFull2['category']['slug'] ?? null;
                $full2Fallback = vivat_category_fallback_image($full2CatSlug, 629, 235, $moreFull2['id'] ?? $moreFull2['slug'] ?? null, 'hub-rest-full2');
                $fullPhoto2Img = ! empty($moreFull2['cover_image_url']) ? $moreFull2['cover_image_url'] : $full2Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($moreFull2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full min-w-0" style="height: 235px;">
                <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" data-fallback-url="<?= htmlspecialchars($full2Fallback) ?>" alt="<?= htmlspecialchars($moreFull2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="vivat-card-overlay flex items-end" >
                    <div class="rounded-[21px] flex flex-col vivat-glass w-full" style="gap: 8px;">
                        <?php if (! empty($moreFull2['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($badgeLabel) ?></span>
                        <?php } ?>
                        <h3 class="<?= $hubImageTitle ?>"><?= htmlspecialchars($moreFull2['title'] ?? '') ?></h3>
                        <p class="<?= $hubImageMeta ?>"><?= htmlspecialchars($moreFull2['published_at'] ?? '') ?> • <?= (int) ($moreFull2['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php } ?>
        </div>
    </section>
    <?php } ?>

    <?php $paginationView = $pagination ? $pagination->withQueryString() : null; ?>
    <?php if ($paginationView && $paginationView->hasPages()) { ?>
    <nav class="flex flex-wrap items-center justify-center gap-3 mt-9" aria-label="Pagination des articles de la rubrique">
        <?php if ($paginationView->onFirstPage()) { ?>
        <span class="inline-flex h-10 items-center justify-center rounded-full bg-[#EBF1EF] px-4 text-sm font-medium text-[#004241]/40">Précédent</span>
        <?php } else { ?>
        <a href="<?= htmlspecialchars($paginationView->previousPageUrl()) ?>" class="inline-flex h-10 items-center justify-center rounded-full bg-[#004241] px-4 text-sm font-medium text-white transition hover:opacity-90">Précédent</a>
        <?php } ?>

        <span class="text-sm font-medium text-[#004241]/80">Page <?= $paginationView->currentPage() ?> sur <?= $paginationView->lastPage() ?></span>

        <?php if ($paginationView->hasMorePages()) { ?>
        <a href="<?= htmlspecialchars($paginationView->nextPageUrl()) ?>" class="inline-flex h-10 items-center justify-center rounded-full bg-[#004241] px-4 text-sm font-medium text-white transition hover:opacity-90">Suivant</a>
        <?php } else { ?>
        <span class="inline-flex h-10 items-center justify-center rounded-full bg-[#EBF1EF] px-4 text-sm font-medium text-[#004241]/40">Suivant</span>
        <?php } ?>
    </nav>
    <?php } ?>
    </div>
</div>

<script>
(() => {
    const filters = document.getElementById('category-hub-filters');
    const results = document.getElementById('category-hub-results');

    if (!filters || !results || !window.fetch || !window.DOMParser) {
        return;
    }

    let pendingRequest = null;

    function setLoadingState(isLoading) {
        results.style.opacity = isLoading ? '0.55' : '1';
        results.style.pointerEvents = isLoading ? 'none' : 'auto';
        filters.style.pointerEvents = isLoading ? 'none' : 'auto';
    }

    async function loadCategoryHub(url, pushState = true) {
        if (!url) {
            return;
        }

        if (pendingRequest) {
            pendingRequest.abort();
        }

        pendingRequest = new AbortController();
        setLoadingState(true);

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: pendingRequest.signal,
            });

            if (!response.ok) {
                window.location.href = url;
                return;
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const nextFilters = doc.getElementById('category-hub-filters');
            const nextResults = doc.getElementById('category-hub-results');

            if (!nextFilters || !nextResults) {
                window.location.href = url;
                return;
            }

            filters.innerHTML = nextFilters.innerHTML;
            results.innerHTML = nextResults.innerHTML;

            if (pushState) {
                window.history.pushState({ url }, '', url);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = url;
            }
        } finally {
            if (pendingRequest && !pendingRequest.signal.aborted) {
                pendingRequest = null;
            }
            setLoadingState(false);
        }
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('#category-hub-filters a, #category-hub-results nav a');

        if (!link) {
            return;
        }

        const url = link.getAttribute('href');

        if (!url || link.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        loadCategoryHub(url, true);
    });

    window.addEventListener('popstate', () => {
        loadCategoryHub(window.location.href, false);
    });
})();
</script>
