<?php
$top_news = $top_news ?? null;
$featured = $featured ?? [];
$latest = $latest ?? [];
$categories = $categories ?? [];
$writer_signup_url = $writer_signup_url ?? '#';
$writer_dashboard_url = $writer_dashboard_url ?? '#';

// On prend les premiers articles pour la grille (hot news, 2 features, 2 standards)
$feature1 = $featured[0] ?? null;
$standard1 = $featured[1] ?? $latest[0] ?? null;
$feature2 = $featured[2] ?? $latest[1] ?? null;
$standard2 = $latest[0] ?? ($featured[3] ?? null);
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
$tagClass = 'font-medium tracking-wide w-fit rounded-full inline-flex items-center';
$tagStyleBase = 'height: 30px; padding: 0 12px; font-size: 12px; box-sizing: border-box;';

// Titre min 7 mots / max 8 mots dans les carrés glass (position et padding 24px inchangés)
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
?>
<!-- Grille articles - Design System Figma (12 cols, gutter 24px ; margin 80px via layout lg:px-20) -->
<div class="flex flex-col w-full">
    <div class="vivat-reveal-group grid grid-cols-1 lg:grid-cols-12 gap-6" style="column-gap: 24px; row-gap: 24px;">
        <!-- Colonne gauche: Hot news + Standard 2 -->
        <div class="lg:col-span-5 flex flex-col" style="gap: 24px;">
            <?php if ($top_news): ?>
            <!-- Hot news: 519x438, radius 30, overlay 20%, inner card glass -->
            <a href="/articles/<?= htmlspecialchars($top_news['slug']) ?>" class="vivat-reveal vivat-card-with-image block rounded-[30px] overflow-hidden relative" style="width: 100%; max-width: 519px; height: 438px;">
                <?php if (!empty($top_news['cover_image_url'])): ?>
                <img src="<?= htmlspecialchars($top_news['cover_image_url']) ?>" alt="<?= htmlspecialchars($top_news['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover" loading="eager">
                <?php endif; ?>
                <div class="absolute inset-0" style="background: rgba(0,0,0,0.2);"></div>
                <div class="absolute rounded-[21px] flex flex-col vivat-glass" style="width: 300px; max-width: 60%; bottom: 18px; left: 18px; padding: 24px; gap: 8px;">
                    <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: #EBF1EF; color: #004241;">Top news</span>
                    <h2 class="font-semibold text-white line-clamp-4" style="font-size: 32px; font-family: Figtree, sans-serif;"><?= htmlspecialchars($truncateGlassTitle($top_news['title'] ?? '')) ?></h2>
                    <?php if (!empty($top_news['excerpt'])): ?>
                    <p class="text-white/90 line-clamp-4" style="font-size: 16px;"><?= htmlspecialchars($top_news['excerpt']) ?></p>
                    <?php endif; ?>
                    <p class="text-white/80" style="font-size: 14px;"><?= htmlspecialchars($top_news['published_at'] ?? '') ?> • <?= (int) ($top_news['reading_time'] ?? 0) ?> min</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($standard2): ?>
            <!-- Standard 2: 519x280, #FFF0D4, radius 30, pas de photo -->
            <a href="/articles/<?= htmlspecialchars($standard2['slug']) ?>" class="vivat-reveal vivat-card-no-image vivat-card-jaune block rounded-[30px] overflow-hidden border border-gray-200/50 flex flex-col justify-end" style="width: 100%; max-width: 519px; height: 280px; padding: 24px; gap: 8px; background: #FFF0D4;">
                <span class="vivat-card-arrow" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <?php if (!empty($standard2['category'])): ?>
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($standard2['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-semibold text-[#004241] line-clamp-2" style="font-size: 20px;"><?= htmlspecialchars($standard2['title']) ?></h3>
                <p class="text-[#004241]/70" style="font-size: 14px;"><?= htmlspecialchars($standard2['published_at'] ?? '') ?> • <?= (int) ($standard2['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php endif; ?>
        </div>

        <!-- Colonne milieu: Feature + Standard 1 (21px marge avec hot news) -->
        <div class="lg:col-span-4 flex flex-col" style="gap: 24px;">
            <?php if ($feature1): ?>
            <!-- Feature: 411x237, image + titre, pas de description -->
            <a href="/articles/<?= htmlspecialchars($feature1['slug']) ?>" class="vivat-reveal vivat-card-with-image block rounded-[30px] overflow-hidden relative" style="width: 100%; max-width: 411px; height: 237px;">
                <?php if (!empty($feature1['cover_image_url'])): ?>
                <img src="<?= htmlspecialchars($feature1['cover_image_url']) ?>" alt="<?= htmlspecialchars($feature1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0" style="padding: 18px; max-width: 60%;">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full" style="padding: 24px; gap: 8px;">
                        <?php if (!empty($feature1['category'])): ?>
                        <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($feature1['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-semibold text-white line-clamp-3" style="font-size: 18px;"><?= htmlspecialchars($truncateGlassTitle($feature1['title'] ?? '')) ?></h3>
                        <p class="text-white/80 text-sm" style="font-size: 14px;"><?= htmlspecialchars($feature1['published_at'] ?? '') ?> • <?= (int) ($feature1['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($standard1): ?>
            <!-- Standard 1: 413x221, #004241, pas de photo -->
            <a href="/articles/<?= htmlspecialchars($standard1['slug']) ?>" class="vivat-reveal vivat-card-no-image vivat-card-dark block rounded-[30px] overflow-hidden border border-[#004241]/20 flex flex-col justify-end" style="width: 100%; max-width: 413px; height: 221px; padding: 24px; gap: 8px; background: #004241;">
                <span class="vivat-card-arrow" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <?php if (!empty($standard1['category'])): ?>
                <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['vert']['bg'] ?>; color: <?= $tagStyles['vert']['color'] ?>;"><?= htmlspecialchars($standard1['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-semibold text-white line-clamp-2" style="font-size: 20px;"><?= htmlspecialchars($standard1['title']) ?></h3>
                <p class="text-white/70" style="font-size: 14px;"><?= htmlspecialchars($standard1['published_at'] ?? '') ?> • <?= (int) ($standard1['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php endif; ?>

            <?php if ($feature2): ?>
            <!-- Feature 2: 411x237, image + titre, pas de description (en dessous du standard vert) -->
            <a href="/articles/<?= htmlspecialchars($feature2['slug']) ?>" class="vivat-reveal vivat-card-with-image block rounded-[30px] overflow-hidden relative" style="width: 100%; max-width: 411px; height: 237px;">
                <?php if (!empty($feature2['cover_image_url'])): ?>
                <img src="<?= htmlspecialchars($feature2['cover_image_url']) ?>" alt="<?= htmlspecialchars($feature2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0" style="padding: 18px; max-width: 60%;">
                    <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full" style="padding: 24px; gap: 8px;">
                        <?php if (!empty($feature2['category'])): ?>
                        <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($feature2['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-semibold text-white line-clamp-3" style="font-size: 18px;"><?= htmlspecialchars($truncateGlassTitle($feature2['title'] ?? '')) ?></h3>
                        <p class="text-white/80 text-sm" style="font-size: 14px;"><?= htmlspecialchars($feature2['published_at'] ?? '') ?> • <?= (int) ($feature2['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Colonne droite: Espace pub + CTA aligné avec featured -->
        <div class="lg:col-span-3 flex flex-col" style="gap: 24px;">
            <div class="flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm" style="width: 300px; height: 600px; padding-right: 48px; padding-bottom: 48px; gap: 8px;">
                <div class="flex-1 flex items-center justify-center">Espace publicitaire</div>
            </div>
            <!-- CTA: hauteur 118px pour aligner la colonne droite avec la gauche (438+24+280 = 742 ; 600+24+118 = 742) -->
            <a href="<?= htmlspecialchars($writer_signup_url) ?>" class="vivat-reveal vivat-card-no-image vivat-card-jaune relative flex flex-col rounded-[30px] overflow-hidden flex-shrink-0" style="width: 301px; height: 118px; background: #FFF0D4;">
                <span class="vivat-card-arrow vivat-card-arrow-bottom" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                <p class="text-[#004241] font-medium text-sm leading-snug flex-1 z-10" style="padding: 18px 18px 0 18px;">Vivat est aussi écrit par ses lecteurs. Partagez votre point de vue.</p>
            </a>
        </div>
    </div>

    <!-- 2ème pub : en dessous des articles, 65px de marge -->
    <div class="flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm overflow-hidden" style="width: 100%; max-width: 970px; height: 250px; padding: 48px; gap: 8px; margin-top: 65px;">
        <div class="flex-1 flex items-center justify-center">Espace publicitaire (bannière)</div>
    </div>

    <?php if (count($categories) > 0): ?>
    <!-- Découvrez vos rubriques préférées - même layout grid 12 cols, 24px gap, 80px via main -->
    <section id="categories-section" class="grid grid-cols-1 lg:grid-cols-12 w-full" style="margin-top: 65px; column-gap: 24px; row-gap: 24px;">
            <!-- Grande carte gauche: 7 colonnes, titre 48px Figtree 600, description 24px 400 -->
            <a href="/categories" class="vivat-card-with-image lg:col-span-7 rounded-[30px] overflow-hidden relative block w-full min-h-[523px]" style="height: 523px;">
                <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800" alt="Découvrez vos rubriques préférées sur Vivat" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute inset-0 flex flex-col items-start justify-center" style="padding: 32px;">
                    <h2 class="font-semibold text-white text-left max-w-[85%]" style="font-family: Figtree, sans-serif; font-size: 48px; font-weight: 600;">Découvrez vos rubriques préférées</h2>
                    <p class="text-white/95 mt-2 text-left max-w-[85%]" style="font-size: 24px; font-weight: 400;">Explorez dès maintenant les contenus qui vous correspondent.</p>
                </div>
            </a>

            <!-- Droite: 5 colonnes, 2 petites + 1 grande + flèche, tout dans la grille -->
            <div class="lg:col-span-5 flex items-center w-full min-w-0" style="gap: 24px;">
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
                            <a href="/categories/<?= htmlspecialchars($cat1['slug']) ?>" class="vivat-card-with-image block rounded-[30px] overflow-hidden relative w-full flex-shrink-0 min-h-0" style="height: 250px;">
                                <?php if (!empty($cat1['image_url'])): ?>
                                <img src="<?= htmlspecialchars($cat1['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat1['name']) ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                <?php endif; ?>
                                <div class="absolute inset-0" style="background: #00000040;"></div>
                                <div class="absolute inset-0 flex items-center justify-center" style="padding: 18px;">
                                    <span class="text-white font-semibold text-center" style="font-size: 20px;"><?= htmlspecialchars($cat1['name']) ?></span>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php if ($cat2): ?>
                            <a href="/categories/<?= htmlspecialchars($cat2['slug']) ?>" class="vivat-card-with-image block rounded-[30px] overflow-hidden relative w-full flex-shrink-0 min-h-0" style="height: 250px;">
                                <?php if (!empty($cat2['image_url'])): ?>
                                <img src="<?= htmlspecialchars($cat2['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat2['name']) ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                                <?php endif; ?>
                                <div class="absolute inset-0" style="background: #00000040;"></div>
                                <div class="absolute inset-0 flex items-center justify-center" style="padding: 18px;">
                                    <span class="text-white font-semibold text-center" style="font-size: 20px;"><?= htmlspecialchars($cat2['name']) ?></span>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                        <!-- Grande carte à droite (1 col, span 2 rows) -->
                        <?php if ($cat3): ?>
                        <a href="/categories/<?= htmlspecialchars($cat3['slug']) ?>" class="vivat-card-with-image block rounded-[30px] overflow-hidden relative w-full min-w-0" style="grid-row: span 2; height: 523px;">
                            <?php if (!empty($cat3['image_url'])): ?>
                            <img src="<?= htmlspecialchars($cat3['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat3['name']) ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                            <?php endif; ?>
                            <div class="absolute inset-0" style="background: #00000033;"></div>
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
                <button type="button" id="categories-next" class="flex-shrink-0 flex items-center justify-center rounded-full bg-[#004241] text-white hover:bg-[#003535] transition relative z-10 -ml-[45px] box-border" style="width: 42px; height: 42px; border-radius: 29px; padding: 8px;" aria-label="Rubriques suivantes">
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
    $shown = [($top_news ?? [])['id'] ?? null, ($feature1 ?? [])['id'] ?? null, ($standard1 ?? [])['id'] ?? null, ($feature2 ?? [])['id'] ?? null, ($standard2 ?? [])['id'] ?? null];
    $restArticles = array_values(array_filter(array_merge($featured, $latest), fn($a) => !in_array($a['id'] ?? null, $shown)));
    // Padd pour afficher 12 cartes : si moins de 12 articles, on répète pour remplir les slots
    if (count($restArticles) > 0 && count($restArticles) < 12) {
        $pad = [];
        for ($i = 0; $i < 12; $i++) {
            $pad[] = $restArticles[$i % count($restArticles)];
        }
        $restArticles = $pad;
    }
    // Pour les 2 cartes "photo complète" (slots 5 et 7), on privilégie des articles avec cover_image_url
    $withCover = array_values(array_filter($restArticles, fn($a) => !empty($a['cover_image_url'])));
    $artForFullPhoto1 = $withCover[0] ?? $restArticles[5] ?? null;
    $artForFullPhoto2 = (count($withCover) > 1) ? $withCover[1] : ($restArticles[7] ?? null);
    ?>
    <?php if (count($restArticles) > 0): ?>
    <section class="vivat-reveal-group mt-12 grid grid-cols-1 lg:grid-cols-12 w-full min-w-0" style="column-gap: 24px; row-gap: 24px;">
        <!-- Titre: Figtree 32px Medium -->
        <h2 class="vivat-reveal font-medium text-[#004241] mb-6 lg:col-span-12" style="font-size: 32px;">Dernières actualités</h2>

        <!-- Colonne gauche (6 cols = moitié) -->
        <div class="lg:col-span-6 flex flex-col min-w-0 w-full" style="gap: 24px;">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php foreach (array_slice($restArticles, 0, 2) as $art): ?>
                <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="vivat-reveal vivat-card-with-image flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #EBF1EF; padding: 24px; gap: 18px;">
                        <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                            <?php if (!empty($art['category'])): ?>
                            <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($art['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($art['title']) ?></h3>
                            <p class="text-[#004241] text-sm font-light"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
                        </div>
                        <?php $artImg = !empty($art['cover_image_url']) ? $art['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($art['slug'] ?? '') . '/254/190'; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artImg) ?>" alt="<?= htmlspecialchars($art['title'] ?? 'Article') ?>" class="w-full h-full object-cover" loading="lazy">
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php $hotNewsArt = $restArticles[2] ?? null; if ($hotNewsArt): ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="vivat-reveal vivat-card-with-image block rounded-[32px] overflow-hidden relative min-w-0 w-full" style="height: 240px;">
                    <?php
                    $hotNewsImg = !empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($hotNewsArt['slug'] ?? '') . '/626/240';
                    ?>
                    <img src="<?= htmlspecialchars($hotNewsImg) ?>" alt="<?= htmlspecialchars($hotNewsArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 flex justify-end items-end" style="padding: 18px;">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full" style="width: 264px; max-width: 60%; padding: 24px; gap: 8px;">
                            <?php if (!empty($hotNewsArt['category'])): ?>
                            <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($hotNewsArt['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($hotNewsArt['title'] ?? '')) ?></h3>
                            <p class="text-white/80 text-sm" style="font-size: 14px;"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
            </a>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                <?php $artLeft = $restArticles[10] ?? null; if ($artLeft): ?>
                <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="vivat-reveal vivat-card-with-image flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #FFEFD1; padding: 24px; gap: 18px;">
                        <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                            <?php if (!empty($artLeft['category'])): ?>
                            <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($artLeft['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artLeft['title']) ?></h3>
                            <p class="text-[#004241] text-sm font-light"><?= htmlspecialchars($artLeft['published_at'] ?? '') ?> • <?= (int) ($artLeft['reading_time'] ?? 0) ?> min</p>
                        </div>
                    <?php $artLeftImg = !empty($artLeft['cover_image_url']) ? $artLeft['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($artLeft['slug'] ?? '') . '/254/190'; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artLeftImg) ?>" alt="<?= htmlspecialchars($artLeft['title'] ?? 'Article') ?>" class="w-full h-full object-cover" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>
                <?php $artLeft2 = $restArticles[6] ?? null; if ($artLeft2): ?>
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="vivat-reveal vivat-card-with-image flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; background: #EBF1EF; padding: 24px; gap: 18px;">
                        <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                            <?php if (!empty($artLeft2['category'])): ?>
                            <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($artLeft2['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artLeft2['title']) ?></h3>
                            <p class="text-[#004241] text-sm font-light"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                        </div>
                    <?php $artLeft2Img = !empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($artLeft2['slug'] ?? '') . '/254/190'; ?>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                        <img src="<?= htmlspecialchars($artLeft2Img) ?>" alt="<?= htmlspecialchars($artLeft2['title'] ?? 'Article') ?>" class="w-full h-full object-cover" loading="lazy">
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
                <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="vivat-reveal vivat-card-no-image <?= $isDark ? 'vivat-card-dark' : 'vivat-card-jaune' ?> flex flex-col rounded-[30px] overflow-hidden border relative min-w-0 w-full" style="height: 198px; padding: 24px; background: <?= $bg ?>; border: 1px solid rgba(255,255,255,0.1); gap: 8px;">
                    <span class="vivat-card-arrow" aria-hidden="true"><svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
                    <?php if (!empty($art['category'])): ?>
                    <?php $tagVariant = $isDark ? 'vert' : 'jaune'; ?>
                    <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles[$tagVariant]['bg'] ?>; color: <?= $tagStyles[$tagVariant]['color'] ?>;"><?= htmlspecialchars($art['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-medium line-clamp-2 flex-1 <?= $isDark ? 'text-white' : 'text-[#004241]' ?>" style="font-size: 20px;"><?= htmlspecialchars($art['title']) ?></h3>
                    <p class="<?= $isDark ? 'text-white/70' : 'text-[#004241]/70' ?>" style="font-size: 14px;"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
                </a>
                <?php endforeach; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0" style="gap: 24px;">
                    <?php $artRight = $restArticles[11] ?? null; if ($artRight): ?>
                    <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="vivat-reveal vivat-card-with-image flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full" style="height: 419px; padding: 24px; gap: 18px; background: #EBF1EF;">
                        <div class="flex flex-col flex-1 min-h-0" style="gap: 8px;">
                            <?php if (!empty($artRight['category'])): ?>
                            <span class="<?= $tagClass ?>" style="<?= $tagStyleBase ?> background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($artRight['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artRight['title']) ?></h3>
                            <p class="text-[#004241] text-sm font-light"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                        </div>
                        <?php $artRightImg = !empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($artRight['slug'] ?? '') . '/254/190'; ?>
                        <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full" style="height: 190px;">
                            <img src="<?= htmlspecialchars($artRightImg) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="w-full h-full object-cover" loading="lazy">
                        </div>
                    </a>
                    <?php endif; ?>
                    <?php if ($artForFullPhoto1): ?>
                    <?php $fullPhoto1Img = !empty($artForFullPhoto1['cover_image_url']) ? $artForFullPhoto1['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($artForFullPhoto1['slug'] ?? '') . '/302/419'; ?>
                    <a href="/articles/<?= htmlspecialchars($artForFullPhoto1['slug']) ?>" class="vivat-reveal vivat-card-with-image block rounded-[25px] overflow-hidden relative min-w-0 w-full" style="height: 419px;">
                        <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" alt="<?= htmlspecialchars($artForFullPhoto1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 z-10" style="padding: 18px; max-width: 60%; min-width: 220px;">
                            <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full min-w-0" style="padding: 24px; gap: 8px; min-width: 180px;">
                                <?php if (!empty($artForFullPhoto1['category'])): ?>
                                <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($artForFullPhoto1['category']['name']) ?></span>
                                <?php endif; ?>
                                <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($artForFullPhoto1['title'] ?? '')) ?></h3>
                                <p class="text-white/80 text-sm" style="font-size: 14px;"><?= htmlspecialchars($artForFullPhoto1['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto1['reading_time'] ?? 0) ?> min</p>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>

            <?php if ($artForFullPhoto2): ?>
            <?php $fullPhoto2Img = !empty($artForFullPhoto2['cover_image_url']) ? $artForFullPhoto2['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($artForFullPhoto2['slug'] ?? '') . '/629/235'; ?>
            <a href="/articles/<?= htmlspecialchars($artForFullPhoto2['slug']) ?>" class="vivat-reveal vivat-card-with-image block rounded-[30px] overflow-hidden relative w-full min-w-0" style="height: 235px;">
                    <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" alt="<?= htmlspecialchars($artForFullPhoto2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0" style="padding: 18px; max-width: 60%;">
                        <div class="rounded-[21px] flex flex-col vivat-glass w-fit max-w-full" style="padding: 24px; gap: 8px;">
                            <?php if (!empty($artForFullPhoto2['category'])): ?>
                            <span class="<?= $tagClass ?> vivat-glass" style="<?= $tagStyleBase ?> color: #fff;"><?= htmlspecialchars($artForFullPhoto2['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($truncateGlassTitle($artForFullPhoto2['title'] ?? '')) ?></h3>
                            <p class="text-white/80 text-sm" style="font-size: 14px;"><?= htmlspecialchars($artForFullPhoto2['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto2['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Bouton Autres actualités - 24px au-dessus (row-gap de la section) -->
        <div class="vivat-reveal flex justify-center lg:col-span-12">
            <a href="/articles" class="inline-flex items-center justify-center rounded-full font-medium text-white gap-2.5 transition box-border" style="width: 226px; height: 48px; background: #004241; padding: 12px 18px;">
                Autres actualités
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>
    <?php endif; ?>
</div>
