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
        <div class="absolute left-0 top-0 flex flex-col justify-center" style="padding: 32px 0 32px 32px; max-width: 500px;">
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
    // Même système que "Dernières actualités" sur la home, mais avec les articles de la rubrique
    $byId = [];
    foreach (array_values($articles) as $a) {
        $id = $a['id'] ?? null;
        if ($id !== null && !isset($byId[$id])) {
            $byId[$id] = $a;
        }
    }
    $restArticles = array_values($byId);
    // Éviter les doublons : les cartes "pleine photo" doivent être des articles pas déjà affichés en 0,1,2,3,4,6,10,11
    $usedIndicesBlock1 = [0, 1, 2, 3, 4, 6, 10, 11];
    $usedIdsBlock1 = array_filter(array_map(fn($i) => $restArticles[$i]['id'] ?? null, $usedIndicesBlock1));
    $withCover = array_values(array_filter($restArticles, fn($a) => !empty($a['cover_image_url'])));
    $withCoverNotUsed1 = array_values(array_filter($withCover, fn($a) => !in_array($a['id'] ?? null, $usedIdsBlock1)));
    $artForFullPhoto1 = $withCoverNotUsed1[0] ?? $restArticles[5] ?? null;
    $artForFullPhoto2 = isset($withCoverNotUsed1[1]) ? $withCoverNotUsed1[1] : ($restArticles[7] ?? null);
    ?>
    <?php if (count($restArticles) > 0): ?>
    <section class="vivat-reveal-group mt-12 grid grid-cols-1 lg:grid-cols-12 w-full min-w-0" style="column-gap: 24px; row-gap: 24px;">
        <!-- Colonne gauche (6 cols = moitié) -->
        <div class="lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php foreach (array_slice($restArticles, 0, 2) as $art): ?>
                <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #EBF1EF; padding: 24px; gap: 18px;">
                    <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                        <?php if (!empty($art['category'])): ?>
                        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($art['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($art['title']) ?></h3>
                        <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <?php $artCatSlug = $art['category']['slug'] ?? null; $artFallback = vivat_category_fallback_image($artCatSlug, 254, 190, $art['id'] ?? $art['slug'] ?? null, 'hub-card-0'); $artImg = !empty($art['cover_image_url']) ? $art['cover_image_url'] : $artFallback; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artImg) ?>" data-fallback-url="<?= htmlspecialchars($artFallback) ?>" alt="<?= htmlspecialchars($art['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php $hotNewsArt = $restArticles[2] ?? null; if ($hotNewsArt): ?>
            <?php $hotCatSlug = $hotNewsArt['category']['slug'] ?? null; $hotFallback = vivat_category_fallback_image($hotCatSlug, 626, 240, $hotNewsArt['id'] ?? $hotNewsArt['slug'] ?? null, 'hot'); $hotNewsImg = !empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : $hotFallback; ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[32px] overflow-hidden relative min-w-0 w-full" style="height: 240px;">
                <img src="<?= htmlspecialchars($hotNewsImg) ?>" data-fallback-url="<?= htmlspecialchars($hotFallback) ?>" alt="<?= htmlspecialchars($hotNewsArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="absolute inset-0 flex justify-end items-end" style="padding: 18px;">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full" style="width: 264px; max-width: 60%; padding: 24px; gap: 8px;">
                        <?php if (!empty($hotNewsArt['category'])): ?>
                        <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($hotNewsArt['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($hotNewsArt['title'] ?? '')) ?></h3>
                        <p class="text-white/80 text-sm" style="font-size: 12px;"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php $artLeft = $restArticles[10] ?? null; if ($artLeft): ?>
                <!-- Carte jaune sans image (variante texte only) : tag + titre + date en bas -->
                <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative vivat-card-jaune flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #FFEFD1; padding: 24px; gap: 18px;">
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
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #EBF1EF; padding: 24px; gap: 18px;">
                    <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                        <?php if (!empty($artLeft2['category'])): ?>
                        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($artLeft2['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artLeft2['title']) ?></h3>
                        <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <?php $left2CatSlug = $artLeft2['category']['slug'] ?? null; $left2Fallback = vivat_category_fallback_image($left2CatSlug, 254, 190, $artLeft2['id'] ?? $artLeft2['slug'] ?? null, 'left2'); $artLeft2Img = !empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : $left2Fallback; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artLeft2Img) ?>" data-fallback-url="<?= htmlspecialchars($left2Fallback) ?>" alt="<?= htmlspecialchars($artLeft2['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Colonne droite (6 cols = moitié) -->
        <div class="lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <?php
            $stdColors = ['#004241', '#FFEFD1'];
            foreach (array_slice($restArticles, 3, 2) as $i => $art):
                $bg = $stdColors[$i % 2];
                $isDark = ($bg === '#004241');
            ?>
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
                <?php $artRight = $restArticles[11] ?? null; if ($artRight): ?>
                <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; padding: 24px; gap: 18px; background: #EBF1EF;">
                    <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                        <?php if (!empty($artRight['category'])): ?>
                        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($artRight['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artRight['title']) ?></h3>
                        <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <?php $rightCatSlug = $artRight['category']['slug'] ?? null; $rightFallback = vivat_category_fallback_image($rightCatSlug, 254, 190, $artRight['id'] ?? $artRight['slug'] ?? null, 'right'); $artRightImg = !empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : $rightFallback; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artRightImg) ?>" data-fallback-url="<?= htmlspecialchars($rightFallback) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>
                <?php if ($artForFullPhoto1): ?>
                <?php $full1CatSlug = $artForFullPhoto1['category']['slug'] ?? null; $full1Fallback = vivat_category_fallback_image($full1CatSlug, 302, 419, $artForFullPhoto1['id'] ?? $artForFullPhoto1['slug'] ?? null, 'full1'); $fullPhoto1Img = !empty($artForFullPhoto1['cover_image_url']) ? $artForFullPhoto1['cover_image_url'] : $full1Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artForFullPhoto1['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[25px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                    <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" data-fallback-url="<?= htmlspecialchars($full1Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 z-10" style="padding: 18px; max-width: 60%; min-width: 220px;">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full min-w-0" style="padding: 24px; gap: 8px; min-width: 180px;">
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
            <?php $full2CatSlug = $artForFullPhoto2['category']['slug'] ?? null; $full2Fallback = vivat_category_fallback_image($full2CatSlug, 629, 235, $artForFullPhoto2['id'] ?? $artForFullPhoto2['slug'] ?? null, 'full2'); $fullPhoto2Img = !empty($artForFullPhoto2['cover_image_url']) ? $artForFullPhoto2['cover_image_url'] : $full2Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($artForFullPhoto2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[30px] overflow-hidden relative w-full min-w-0" style="height: 235px;">
                <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" data-fallback-url="<?= htmlspecialchars($full2Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0" style="padding: 18px; max-width: 60%;">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full" style="padding: 24px; gap: 8px;">
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

    </section>
    <?php else: ?>
    <p class="text-[#004241]/70">Aucun article dans cette rubrique pour le moment.</p>
    <?php endif; ?>

    <!-- Bloc pub sous les articles : 24px marge, 970×250, padding 48px, fond #686868 -->
    <div class="flex flex-col rounded-[30px] overflow-hidden box-border" style="margin-top: 24px; width: 970px; max-width: 100%; height: 250px; padding: 48px; gap: 8px; background: #686868;">
        <div class="flex-1 flex items-center justify-center text-white/80 text-sm">Espace publicitaire</div>
    </div>

    <?php
    // Deuxième bloc de 12 articles (même design que les 12 premiers), 24px sous la pub
    $m = isset($restArticles) ? array_slice($restArticles, 12, 12) : [];
    $usedIndicesBlock2 = [0, 1, 2, 3, 4, 6, 10, 11];
    $usedIdsBlock2 = array_filter(array_map(fn($i) => isset($m[$i]) ? ($m[$i]['id'] ?? null) : null, $usedIndicesBlock2));
    $moreWithCover = array_values(array_filter($m, fn($a) => !empty($a['cover_image_url'])));
    $moreWithCoverNotUsed = array_values(array_filter($moreWithCover, fn($a) => !in_array($a['id'] ?? null, $usedIdsBlock2)));
    $moreFull1 = $moreWithCoverNotUsed[0] ?? $m[5] ?? null;
    $moreFull2 = isset($moreWithCoverNotUsed[1]) ? $moreWithCoverNotUsed[1] : ($m[7] ?? null);
    $stdColors = ['#004241', '#FFEFD1'];
    ?>
    <?php if (count($m) > 0): ?>
    <section class="vivat-reveal-group grid grid-cols-1 lg:grid-cols-12 w-full min-w-0" style="margin-top: 24px; column-gap: 24px; row-gap: 24px;">
        <!-- Colonne gauche (6 cols) -->
        <div class="lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php foreach (array_slice($m, 0, 2) as $art): ?>
                <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #EBF1EF; padding: 24px; gap: 18px;">
                    <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                        <?php if (!empty($art['category'])): ?>
                        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($art['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($art['title']) ?></h3>
                        <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <?php $artCatSlug = $art['category']['slug'] ?? null; $artFallback = vivat_category_fallback_image($artCatSlug, 254, 190, $art['id'] ?? $art['slug'] ?? null, 'hub-card-1'); $artImg = !empty($art['cover_image_url']) ? $art['cover_image_url'] : $artFallback; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artImg) ?>" data-fallback-url="<?= htmlspecialchars($artFallback) ?>" alt="<?= htmlspecialchars($art['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php $hotNewsArt = $m[2] ?? null; if ($hotNewsArt): ?>
            <?php $hotCatSlug = $hotNewsArt['category']['slug'] ?? null; $hotFallback = vivat_category_fallback_image($hotCatSlug, 626, 240, $hotNewsArt['id'] ?? $hotNewsArt['slug'] ?? null, 'hot'); $hotNewsImg = !empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : $hotFallback; ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group block rounded-[32px] overflow-hidden relative min-w-0 w-full" style="height: 240px;">
                <img src="<?= htmlspecialchars($hotNewsImg) ?>" data-fallback-url="<?= htmlspecialchars($hotFallback) ?>" alt="<?= htmlspecialchars($hotNewsArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                <div class="absolute inset-0 flex justify-end items-end" style="padding: 18px;">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full" style="width: 264px; max-width: 60%; padding: 24px; gap: 8px;">
                        <?php if (!empty($hotNewsArt['category'])): ?>
                        <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($hotNewsArt['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($hotNewsArt['title'] ?? '')) ?></h3>
                        <p class="text-white/80 text-sm" style="font-size: 12px;"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
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
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-with-image group flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #EBF1EF; padding: 24px; gap: 18px;">
                    <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                        <?php if (!empty($artLeft2['category'])): ?>
                        <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($artLeft2['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artLeft2['title']) ?></h3>
                        <p class="text-[#004241] font-light" style="font-size: 12px;"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <?php $left2CatSlug = $artLeft2['category']['slug'] ?? null; $left2Fallback = vivat_category_fallback_image($left2CatSlug, 254, 190, $artLeft2['id'] ?? $artLeft2['slug'] ?? null, 'left2'); $artLeft2Img = !empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : $left2Fallback; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artLeft2Img) ?>" data-fallback-url="<?= htmlspecialchars($left2Fallback) ?>" alt="<?= htmlspecialchars($artLeft2['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Colonne droite (6 cols) -->
        <div class="lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <?php foreach (array_slice($m, 3, 2) as $i => $art): $bg = $stdColors[$i % 2]; $isDark = ($bg === '#004241'); ?>
            <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="vivat-reveal opacity-0 translate-y-8 transition-all duration-[900ms] ease-out vivat-card-no-image group relative <?= $isDark ? 'vivat-card-dark' : 'vivat-card-jaune' ?> flex flex-col justify-end rounded-[30px] overflow-hidden border min-w-0 w-full" style="height: 198px; padding: 24px; background: <?= $bg ?>; border: 1px solid rgba(255,255,255,0.1); gap: 8px;">
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
                    <?php $rightCatSlug = $artRight['category']['slug'] ?? null; $rightFallback = vivat_category_fallback_image($rightCatSlug, 254, 190, $artRight['id'] ?? $artRight['slug'] ?? null, 'right'); $artRightImg = !empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : $rightFallback; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artRightImg) ?>" data-fallback-url="<?= htmlspecialchars($rightFallback) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="w-full h-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.06]" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>
                <?php if ($moreFull1): ?>
                <?php $full1CatSlug = $moreFull1['category']['slug'] ?? null; $full1Fallback = vivat_category_fallback_image($full1CatSlug, 302, 419, $moreFull1['id'] ?? $moreFull1['slug'] ?? null, 'more-full1'); $fullPhoto1Img = !empty($moreFull1['cover_image_url']) ? $moreFull1['cover_image_url'] : $full1Fallback; ?>
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
            <?php $full2CatSlug = $moreFull2['category']['slug'] ?? null; $full2Fallback = vivat_category_fallback_image($full2CatSlug, 629, 235, $moreFull2['id'] ?? $moreFull2['slug'] ?? null, 'more-full2'); $fullPhoto2Img = !empty($moreFull2['cover_image_url']) ? $moreFull2['cover_image_url'] : $full2Fallback; ?>
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
</div>
