<?php
$category = $category ?? [];
$description = $description ?? '';
$total_published = $total_published ?? 0;
$current_sub_category_slug = $current_sub_category_slug ?? null;
$sub_categories = $sub_categories ?? [];
$articles = $articles ?? [];
$category_name = $category['name'] ?? 'Rubrique';
$category_slug = $category['slug'] ?? '';
$category_image_url = $category['image_url'] ?? null;

// Styles des tags (comme home)
$tagStyles = [
    'vert'  => ['bg' => '#527E7E', 'color' => '#fff'],
    'jaune' => ['bg' => '#004241', 'color' => '#fff'],
    'glass' => ['bg' => '#787879', 'color' => '#fff'],
    'gris'  => ['bg' => '#ffffff', 'color' => '#004241'],
];
$tagClass = 'font-medium tracking-wide w-fit rounded-full inline-flex items-center';
$tagStyleBase = 'height: 30px; padding: 0 12px; font-size: 12px; box-sizing: border-box;';

$truncateGlassTitle = function (?string $t): string {
    $t = trim((string) $t);
    if ($t === '') return '';
    $w = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
    $minWords = 7;
    $maxWords = 8;
    if (count($w) <= $maxWords) return $t;
    $keep = max($minWords, min($maxWords, count($w)));
    return implode(' ', array_slice($w, 0, $keep)) . ' …';
};

// Template séquentiel : on remplit les emplacements 0,1,2,... avec les articles dispo (sans espaces vides).
// Une seule occurrence par article : déduplication stricte par id (évite le même article en 2 designs)
$byId = [];
foreach (array_values($articles) as $a) {
    $id = $a['id'] ?? null;
    if ($id !== null && !isset($byId[$id])) {
        $byId[$id] = $a;
    }
}
$restArticles = array_values($byId);
?>
<div class="flex flex-col w-full">
    <!-- 1) Marge 24px sous la navbar déjà gérée par le main -->

    <!-- 2) Pub 728x90 - flow vertical, border 1px, padding right/bottom/left 48px, gap 8 -->
    <div class="flex flex-col rounded-[30px] bg-gray-100 border border-gray-300 text-gray-400 text-sm overflow-hidden box-border" style="padding-right: 48px; padding-bottom: 48px; padding-left: 48px; gap: 8px; margin-bottom: 24px;">
        <div class="flex items-center justify-center rounded-[30px] border-2 border-dashed border-gray-300" style="width: 728px; max-width: 100%; height: 90px;">Publicité</div>
    </div>

    <!-- 3) Grand carré hero catégorie: 1280x443, titre + description + filtres dans le carré. Image fixe pour ne jamais changer au filtre/rechargement. -->
    <div class="rounded-[30px] overflow-hidden relative w-full max-w-[1280px] mx-auto" style="height: 443px; margin-bottom: 24px;">
        <?php
        // Image hero stable (même URL toujours) pour que la grande photo ne change pas au changement de filtre ou rechargement
        $heroImg = 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1280';
        ?>
        <img src="<?= htmlspecialchars($heroImg) ?>" alt="<?= htmlspecialchars($category_name) ?>" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        <div class="absolute left-0 top-0 flex flex-col" style="padding: 32px; max-width: 500px;">
            <h1 class="font-semibold text-white leading-none" style="font-family: Figtree, sans-serif; font-size: 48px; line-height: 100%;"><?= htmlspecialchars($category_name) ?></h1>
            <?php if ($description): ?>
            <p class="font-light text-white/95 mt-4" style="font-family: Figtree, sans-serif; font-size: 20px;"><?= htmlspecialchars($description) ?></p>
            <?php endif; ?>
        </div>
        <!-- Filtres dans le carré: left 32px, bottom, marge 11px entre les filtres -->
        <nav class="absolute flex items-center flex-wrap" style="left: 32px; bottom: 32px; gap: 11px;" aria-label="Filtrer par sous-rubrique">
            <?php $allSelected = !$current_sub_category_slug; ?>
            <a href="/categories/<?= htmlspecialchars($category_slug) ?>" class="inline-flex items-center justify-center gap-1 rounded-full font-normal transition box-border shrink-0" style="min-width: 66px; height: 42px; padding: 8px 18px; font-family: Figtree, sans-serif; font-size: 16px; line-height: 100%; <?= $allSelected ? 'background: #EBF1EF; color: #004241; border: 1px solid rgba(255,255,255,0.30);' : 'background: rgba(255,255,255,0.32); color: #fff; border: 2px solid rgba(255,255,255,0.03);' ?>">
                Tous
                <?php if ($allSelected): ?><span class="inline-flex flex-shrink-0" style="width: 16px; height: 16px;"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 4L4 12M4 4l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span><?php endif; ?>
            </a>
            <?php foreach ($sub_categories as $sub): ?>
            <?php $isSelected = $current_sub_category_slug === ($sub['slug'] ?? ''); ?>
            <a href="<?= $isSelected ? '/categories/'.htmlspecialchars($category_slug) : '/categories/'.htmlspecialchars($category_slug).'?sub_category='.rawurlencode($sub['slug'] ?? '') ?>" class="inline-flex items-center justify-center gap-1 rounded-full font-normal transition box-border shrink-0" style="min-width: 66px; height: 42px; padding: 8px 18px; font-family: Figtree, sans-serif; font-size: 16px; line-height: 100%; <?= $isSelected ? 'background: #EBF1EF; color: #004241; border: 1px solid rgba(255,255,255,0.30);' : 'background: rgba(255,255,255,0.32); color: #fff; border: 2px solid rgba(255,255,255,0.03);' ?>">
                <?= htmlspecialchars($sub['name'] ?? '') ?>
                <?php if ($isSelected): ?><span class="inline-flex flex-shrink-0" style="width: 16px; height: 16px;"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 4L4 12M4 4l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <?php
    // Premier bloc sous le hero : 6 cartes fixes
    $byId = [];
    foreach (array_values($articles) as $a) {
        $id = $a['id'] ?? null;
        if ($id !== null && !isset($byId[$id])) {
            $byId[$id] = $a;
        }
    }
    $restArticles = array_values($byId);
    $featuredCards = array_slice($restArticles, 0, 6);
    $featured0 = $featuredCards[0] ?? null;
    $featured1 = $featuredCards[1] ?? null;
    $featured2 = $featuredCards[2] ?? null;
    $featured3 = $featuredCards[3] ?? null;
    $featured4 = $featuredCards[4] ?? null;
    $featured5 = $featuredCards[5] ?? null;
    ?>
    <?php if (count($restArticles) > 0): ?>
    <section class="vivat-reveal-group grid grid-cols-1 lg:grid-cols-12 w-full min-w-0" style="column-gap: 24px; row-gap: 24px;">
        <?php if ($featured0): ?>
        <?php $featured0Slug = $featured0['category']['slug'] ?? null; $featured0Fallback = vivat_category_fallback_image($featured0Slug, 800, 420, $featured0['id'] ?? $featured0['slug'] ?? null, 'hub-first-0'); $featured0Img = !empty($featured0['cover_image_url']) ? $featured0['cover_image_url'] : $featured0Fallback; ?>
        <a href="/articles/<?= htmlspecialchars($featured0['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out lg:col-span-6 group block rounded-[30px] overflow-hidden relative w-full min-w-0" style="height: 388px;">
            <img src="<?= htmlspecialchars($featured0Img) ?>" data-fallback-url="<?= htmlspecialchars($featured0Fallback) ?>" alt="<?= htmlspecialchars($featured0['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-r from-black/35 via-black/10 to-transparent"></div>
            <div class="absolute left-0 top-0 bottom-0 flex items-center" style="padding: 18px;">
                <div class="rounded-[21px] flex flex-col vivat-glass w-full max-w-[278px]" style="padding: 24px; gap: 8px;">
                    <?php if (!empty($featured0['category'])): ?>
                    <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($featured0['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-medium text-white line-clamp-5" style="font-size: 20px;"><?= htmlspecialchars($featured0['title']) ?></h3>
                    <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($featured0['published_at'] ?? '') ?> • <?= (int) ($featured0['reading_time'] ?? 0) ?> min</p>
                </div>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($featured1): ?>
        <?php $featured1Slug = $featured1['category']['slug'] ?? null; $featured1Fallback = vivat_category_fallback_image($featured1Slug, 420, 210, $featured1['id'] ?? $featured1['slug'] ?? null, 'hub-first-1'); $featured1Img = !empty($featured1['cover_image_url']) ? $featured1['cover_image_url'] : $featured1Fallback; ?>
        <a href="/articles/<?= htmlspecialchars($featured1['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out lg:col-span-3 group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 388px; background: #EBF1EF; padding: 24px; gap: 18px;">
            <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                <?php if (!empty($featured1['category'])): ?>
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($featured1['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-medium text-[#004241] line-clamp-4" style="font-size: 20px;"><?= htmlspecialchars($featured1['title']) ?></h3>
                <p class="text-[#004241]/80" style="font-size: 12px;"><?= htmlspecialchars($featured1['published_at'] ?? '') ?> • <?= (int) ($featured1['reading_time'] ?? 0) ?> min</p>
            </div>
            <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 210px;">
                <img src="<?= htmlspecialchars($featured1Img) ?>" data-fallback-url="<?= htmlspecialchars($featured1Fallback) ?>" alt="<?= htmlspecialchars($featured1['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
            </div>
        </a>
        <?php endif; ?>

        <?php if ($featured2): ?>
        <a href="/articles/<?= htmlspecialchars($featured2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out lg:col-span-3 vivat-card-no-image group relative flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 388px; background: #FFEFD1; padding: 24px; gap: 18px;">
            <div class="flex flex-col min-h-0" style="gap: 8px;">
                <?php if (!empty($featured2['category'])): ?>
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($featured2['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-medium text-[#004241] line-clamp-4" style="font-size: 20px;"><?= htmlspecialchars($featured2['title']) ?></h3>
                <p class="text-[#004241]/80" style="font-size: 12px;"><?= htmlspecialchars($featured2['published_at'] ?? '') ?> • <?= (int) ($featured2['reading_time'] ?? 0) ?> min</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($featured3): ?>
        <?php $featured3Slug = $featured3['category']['slug'] ?? null; $featured3Fallback = vivat_category_fallback_image($featured3Slug, 420, 210, $featured3['id'] ?? $featured3['slug'] ?? null, 'hub-first-3'); $featured3Img = !empty($featured3['cover_image_url']) ? $featured3['cover_image_url'] : $featured3Fallback; ?>
        <a href="/articles/<?= htmlspecialchars($featured3['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out lg:col-span-3 group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 388px; background: #EBF1EF; padding: 24px; gap: 18px;">
            <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                <?php if (!empty($featured3['category'])): ?>
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($featured3['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-medium text-[#004241] line-clamp-4" style="font-size: 20px;"><?= htmlspecialchars($featured3['title']) ?></h3>
                <p class="text-[#004241]/80" style="font-size: 12px;"><?= htmlspecialchars($featured3['published_at'] ?? '') ?> • <?= (int) ($featured3['reading_time'] ?? 0) ?> min</p>
            </div>
            <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 210px;">
                <img src="<?= htmlspecialchars($featured3Img) ?>" data-fallback-url="<?= htmlspecialchars($featured3Fallback) ?>" alt="<?= htmlspecialchars($featured3['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
            </div>
        </a>
        <?php endif; ?>

        <?php if ($featured4): ?>
        <a href="/articles/<?= htmlspecialchars($featured4['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out lg:col-span-3 group flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 388px; background: #004241; padding: 24px; gap: 18px;">
            <div class="flex flex-col min-h-0" style="gap: 8px;">
                <?php if (!empty($featured4['category'])): ?>
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['vert']['bg'] ?>; color: <?= $tagStyles['vert']['color'] ?>;"><?= htmlspecialchars($featured4['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-medium text-white line-clamp-4" style="font-size: 20px;"><?= htmlspecialchars($featured4['title']) ?></h3>
                <p class="text-white/75" style="font-size: 12px;"><?= htmlspecialchars($featured4['published_at'] ?? '') ?> • <?= (int) ($featured4['reading_time'] ?? 0) ?> min</p>
            </div>
        </a>
        <?php endif; ?>

        <?php if ($featured5): ?>
        <?php $featured5Slug = $featured5['category']['slug'] ?? null; $featured5Fallback = vivat_category_fallback_image($featured5Slug, 800, 388, $featured5['id'] ?? $featured5['slug'] ?? null, 'hub-first-5'); $featured5Img = !empty($featured5['cover_image_url']) ? $featured5['cover_image_url'] : $featured5Fallback; ?>
        <a href="/articles/<?= htmlspecialchars($featured5['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out lg:col-span-6 group block rounded-[30px] overflow-hidden relative w-full min-w-0" style="height: 388px;">
            <img src="<?= htmlspecialchars($featured5Img) ?>" data-fallback-url="<?= htmlspecialchars($featured5Fallback) ?>" alt="<?= htmlspecialchars($featured5['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-l from-black/55 via-transparent to-transparent"></div>
            <div class="absolute right-0 top-0 bottom-0 flex items-center justify-end" style="padding: 18px;">
                <div class="rounded-[21px] flex flex-col vivat-glass w-full max-w-[320px]" style="padding: 24px; gap: 8px;">
                    <?php if (!empty($featured5['category'])): ?>
                    <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($featured5['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-medium text-white line-clamp-4" style="font-size: 20px;"><?= htmlspecialchars($featured5['title']) ?></h3>
                    <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($featured5['published_at'] ?? '') ?> • <?= (int) ($featured5['reading_time'] ?? 0) ?> min</p>
                </div>
            </div>
        </a>
        <?php endif; ?>
    </section>
    <?php else: ?>
    <p class="text-[#004241]/70">Aucun article dans cette rubrique pour le moment.</p>
    <?php endif; ?>

    <!-- Bloc pub sous les articles : 24px marge, 970×250, padding 48px, fond #686868 -->
    <div class="flex flex-col rounded-[30px] overflow-hidden box-border" style="margin-top: 24px; width: 970px; max-width: 100%; height: 250px; padding: 48px; gap: 8px; background: #686868;">
        <div class="flex-1 flex items-center justify-center text-white/80 text-sm">Espace publicitaire</div>
    </div>

    <?php
    // Après la pub : reste des articles, avec la même structure que la home
    $m = isset($restArticles) ? array_slice($restArticles, 6) : [];
    $usedIndicesBlock2 = [0, 1, 2, 3, 4, 6, 10, 11];
    $usedIdsBlock2 = array_filter(array_map(fn ($i) => isset($m[$i]) ? ($m[$i]['id'] ?? null) : null, $usedIndicesBlock2));
    $moreWithCover = array_values(array_filter($m, fn ($a) => !empty($a['cover_image_url'])));
    $moreWithCoverNotUsed = array_values(array_filter($moreWithCover, fn ($a) => !in_array($a['id'] ?? null, $usedIdsBlock2)));
    $moreFull1 = $moreWithCoverNotUsed[0] ?? $m[5] ?? null;
    $moreFull2 = isset($moreWithCoverNotUsed[1]) ? $moreWithCoverNotUsed[1] : ($m[7] ?? null);
    $stdColors = ['#004241', '#FFEFD1'];
    ?>
    <?php if (count($m) > 0): ?>
    <section class="vivat-reveal-group grid grid-cols-1 tablet:grid-cols-8 lg:grid-cols-12 w-full min-w-0" style="margin-top: 24px; column-gap: 24px; row-gap: 24px;">
        <div class="tablet:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php
                $firstArt = $m[0] ?? null;
                $secondArt = $m[1] ?? null;
                ?>
                <?php if ($firstArt): ?>
                <?php $f0CatSlug = $firstArt['category']['slug'] ?? null; $f0ArtId = $firstArt['id'] ?? $firstArt['slug'] ?? null; $f0Fallback = vivat_category_fallback_image($f0CatSlug, 302, 419, $f0ArtId, 'hub-rest-0'); $f0Img = !empty($firstArt['cover_image_url']) ? $firstArt['cover_image_url'] : $f0Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($firstArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
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
                <?php $f1CatSlug = $secondArt['category']['slug'] ?? null; $f1ArtId = $secondArt['id'] ?? $secondArt['slug'] ?? null; $f1Fallback = vivat_category_fallback_image($f1CatSlug, 302, 419, $f1ArtId, 'hub-rest-1'); $f1Img = !empty($secondArt['cover_image_url']) ? $secondArt['cover_image_url'] : $f1Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($secondArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($f1Img) ?>" data-fallback-url="<?= htmlspecialchars($f1Fallback) ?>" alt="<?= htmlspecialchars($secondArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute flex items-end z-10" style="top: 18px; right: 18px; bottom: 18px; left: 18px;">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-[60%] min-w-0" style="gap: 6px; min-width: 180px;">
                            <?php if (!empty($secondArt['category'])): ?>
                            <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($secondArt['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($secondArt['title'] ?? '')) ?></h3>
                            <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($secondArt['published_at'] ?? '') ?> • <?= (int) ($secondArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php endif; ?>
            </div>

            <?php $hotNewsArt = $m[2] ?? null; if ($hotNewsArt): ?>
            <?php $hotCatSlug = $hotNewsArt['category']['slug'] ?? null; $hotArtId = $hotNewsArt['id'] ?? $hotNewsArt['slug'] ?? null; $hotFallback = vivat_category_fallback_image($hotCatSlug, 626, 240, $hotArtId, 'hub-rest-hot'); $hotNewsImg = !empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : $hotFallback; ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[32px] overflow-hidden relative min-w-0 w-full" style="height: 240px;">
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
                <?php $artLeft = $m[10] ?? null; if ($artLeft): ?>
                <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #FFEFD1; padding: 24px; gap: 18px;">
                    <span class="absolute top-[18px] right-[18px] w-12 h-12 rounded-full flex items-center justify-center opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100 bg-[#004241] text-white" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                    <div class="flex flex-col min-h-0" style="gap: 8px;">
                        <?php if (!empty($artLeft['category'])): ?>
                        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($artLeft['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artLeft['title']) ?></h3>
                        <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($artLeft['published_at'] ?? '') ?> • <?= (int) ($artLeft['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php endif; ?>

                <?php $artLeft2 = $m[6] ?? null; if ($artLeft2): ?>
                <?php $left2CatSlug = $artLeft2['category']['slug'] ?? null; $left2ArtId = $artLeft2['id'] ?? $artLeft2['slug'] ?? null; $left2Fallback = vivat_category_fallback_image($left2CatSlug, 302, 419, $left2ArtId, 'hub-rest-left2'); $artLeft2Img = !empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : $left2Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
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

        <div class="tablet:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <?php foreach (array_slice($m, 3, 2) as $i => $art): $bg = $stdColors[$i % 2]; $isDark = ($bg === '#004241'); ?>
            <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative <?= $isDark ? 'vivat-card-dark' : 'vivat-card-jaune' ?> flex flex-col justify-end rounded-[30px] overflow-hidden border relative min-w-0 w-full" style="height: 198px; padding: 24px; background: <?= $bg ?>; border: 1px solid rgba(255,255,255,0.1); gap: 8px;">
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
                <?php $artRight = $m[11] ?? null; if ($artRight): ?>
                <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; padding: 24px; gap: 18px; background: #EBF1EF;">
                    <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                        <?php if (!empty($artRight['category'])): ?>
                        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($artRight['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artRight['title']) ?></h3>
                        <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <?php $rightCatSlug = $artRight['category']['slug'] ?? null; $rightFallback = vivat_category_fallback_image($rightCatSlug, 254, 190, $artRight['id'] ?? $artRight['slug'] ?? null, 'hub-rest-right'); $artRightImg = !empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : $rightFallback; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artRightImg) ?>" data-fallback-url="<?= htmlspecialchars($rightFallback) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($moreFull1): ?>
                <?php $full1CatSlug = $moreFull1['category']['slug'] ?? null; $full1Fallback = vivat_category_fallback_image($full1CatSlug, 302, 419, $moreFull1['id'] ?? $moreFull1['slug'] ?? null, 'hub-rest-full1'); $fullPhoto1Img = !empty($moreFull1['cover_image_url']) ? $moreFull1['cover_image_url'] : $full1Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($moreFull1['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[25px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" data-fallback-url="<?= htmlspecialchars($full1Fallback) ?>" alt="<?= htmlspecialchars($moreFull1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 z-10" style="padding: 18px; max-width: 60%; min-width: 220px;">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full min-w-0" style="padding: 24px; gap: 8px; min-width: 180px;">
                            <?php if (!empty($moreFull1['category'])): ?>
                            <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($moreFull1['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($moreFull1['title'] ?? '')) ?></h3>
                            <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($moreFull1['published_at'] ?? '') ?> • <?= (int) ($moreFull1['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php endif; ?>
            </div>

            <?php if ($moreFull2): ?>
            <?php $full2CatSlug = $moreFull2['category']['slug'] ?? null; $full2Fallback = vivat_category_fallback_image($full2CatSlug, 629, 235, $moreFull2['id'] ?? $moreFull2['slug'] ?? null, 'hub-rest-full2'); $fullPhoto2Img = !empty($moreFull2['cover_image_url']) ? $moreFull2['cover_image_url'] : $full2Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($moreFull2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full min-w-0" style="height: 235px;">
                <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" data-fallback-url="<?= htmlspecialchars($full2Fallback) ?>" alt="<?= htmlspecialchars($moreFull2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0" style="padding: 18px; max-width: 60%;">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full" style="padding: 24px; gap: 8px;">
                        <?php if (!empty($moreFull2['category'])): ?>
                        <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($moreFull2['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($moreFull2['title'] ?? '')) ?></h3>
                        <p class="text-white/80" style="font-size: 12px;"><?= htmlspecialchars($moreFull2['published_at'] ?? '') ?> • <?= (int) ($moreFull2['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php
    $perPage = 24;
    $pageCount = max(1, (int) ceil(((int) $total_published) / $perPage));
    ?>
    <?php if ($pageCount > 1): ?>
    <nav class="flex items-center justify-center gap-6 mt-9" aria-label="Pagination des articles de la rubrique">
        <span class="inline-flex items-center justify-center rounded-[10px] bg-[#004241] text-white font-medium" style="width: 36px; height: 36px; font-size: 16px;">1</span>
        <?php for ($page = 2; $page <= min($pageCount, 3); $page++): ?>
        <span class="text-[#1F2937]" style="font-size: 16px;"><?= $page ?></span>
        <?php endfor; ?>
        <?php if ($pageCount > 4): ?>
        <span class="text-[#1F2937]" style="font-size: 16px;">...</span>
        <?php endif; ?>
        <?php if ($pageCount > 3): ?>
        <span class="text-[#1F2937]" style="font-size: 16px;"><?= max(4, $pageCount - 1) ?></span>
        <?php endif; ?>
        <?php if ($pageCount > 4): ?>
        <span class="text-[#1F2937]" style="font-size: 16px;"><?= $pageCount ?></span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</div>
