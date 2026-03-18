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

// Grille highlight : 5 emplacements depuis $highlight
$h0 = $highlight[0] ?? null;
$h1 = $highlight[1] ?? null;
$h2 = $highlight[2] ?? null;
$h3 = $highlight[3] ?? null;
$h4 = $highlight[4] ?? null;
$catChunks = array_chunk($categories, 3);

// Styles des tags par type de card (couleurs de fond et texte)
$tagStyles = [
    'vert'  => ['bg' => '#527E7E', 'color' => '#fff'],
    'jaune' => ['bg' => '#004241', 'color' => '#fff'],
    'glass' => ['bg' => '#787879', 'color' => '#fff'],
    'gris'  => ['bg' => '#ffffff', 'color' => '#004241'],
];

// Classes Tailwind pour les tags pill
$tagClass = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';

// Classes Tailwind pour les tags glass (fond semi-transparent + blur, sans padding supplémentaire)
$tagGlass = $tagClass . ' bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';

// Classes Tailwind pour les boîtes glass (conteneur overlay sur images)
$glassBox = 'bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)] p-[18px]';

// Tronque le titre à 9 mots max pour les cartes glass
$truncateGlassTitle = function (?string $t): string {
    $t = trim((string) $t);
    if ($t === '') return '';
    $w = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);
    if (count($w) <= 9) return $t;
    return implode(' ', array_slice($w, 0, 9)) . ' …';
};

$h0CatSlug = $h0['category']['slug'] ?? null;
$h0ArtId   = $h0['id'] ?? $h0['slug'] ?? null;
$h0Fallback = vivat_category_fallback_image($h0CatSlug, 800, 600, $h0ArtId, 'h0');
$h0Img = (!empty($h0['cover_image_url']) ? $h0['cover_image_url'] : $h0Fallback) ?: $h0Fallback;

$h1CatSlug = $h1['category']['slug'] ?? null;
$h1ArtId   = $h1['id'] ?? $h1['slug'] ?? null;
$h1Fallback = vivat_category_fallback_image($h1CatSlug, 411, 237, $h1ArtId, 'h1');
$h1Img = (!empty($h1['cover_image_url']) ? $h1['cover_image_url'] : $h1Fallback) ?: $h1Fallback;

$h3CatSlug = $h3['category']['slug'] ?? null;
$h3ArtId   = $h3['id'] ?? $h3['slug'] ?? null;
$h3Fallback = vivat_category_fallback_image($h3CatSlug, 411, 237, $h3ArtId, 'h3');
$h3Img = (!empty($h3['cover_image_url']) ? $h3['cover_image_url'] : $h3Fallback) ?: $h3Fallback;

$h4CatSlug = $h4['category']['slug'] ?? null;
$h4ArtId   = $h4['id'] ?? $h4['slug'] ?? null;
$h4Fallback = vivat_category_fallback_image($h4CatSlug, 519, 280, $h4ArtId, 'h4');
$h4Img = (!empty($h4['cover_image_url']) ? $h4['cover_image_url'] : $h4Fallback) ?: $h4Fallback;
?>

<!-- Bandeau pub tablette uniquement (md) -->
<div class="hidden md:block lg:hidden w-full mb-6">
    <div class="rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm flex items-center justify-center mx-auto w-full max-w-[728px] h-[90px]">
        Publicité 728×90
    </div>
</div>

<!-- Grille tablette dédiée (visible md uniquement) -->
<div class="hidden md:grid lg:hidden grid-cols-8 w-full gap-6">
    <?php if ($h0): ?>
    <a href="/articles/<?= htmlspecialchars($h0['slug']) ?>" class="col-span-5 block rounded-[30px] overflow-hidden relative w-full h-[420px]">
        <img src="<?= htmlspecialchars($h0Img) ?>" data-fallback-url="<?= htmlspecialchars($h0Fallback) ?>" alt="<?= htmlspecialchars($h0['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover" loading="eager">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="absolute inset-[18px] flex items-end">
            <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[300px] gap-1.5">
                <span class="<?= $tagClass ?>" style="background: #EBF1EF; color: #004241;">Top news</span>
                <h2 class="font-semibold text-white line-clamp-4 text-2xl"><?= htmlspecialchars($truncateGlassTitle($h0['title'] ?? '')) ?></h2>
                <?php if (!empty($h0['excerpt'])): ?>
                <p class="text-white/90 line-clamp-3 text-sm"><?= htmlspecialchars($h0['excerpt']) ?></p>
                <?php endif; ?>
                <p class="text-white/80 text-xs"><?= htmlspecialchars($h0['published_at'] ?? '') ?> • <?= (int) ($h0['reading_time'] ?? 0) ?> min</p>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <div class="col-span-3 flex flex-col gap-6">
        <?php if ($h2): ?>
        <a href="/articles/<?= htmlspecialchars($h2['slug']) ?>" class="relative block rounded-[30px] overflow-hidden border border-[#004241]/20 flex flex-col justify-end w-full bg-[#004241] p-6 gap-2">
            <?php if (!empty($h2['category'])): ?>
            <span class="<?= $tagClass ?>" style="background: <?= $tagStyles['vert']['bg'] ?>; color: <?= $tagStyles['vert']['color'] ?>;"><?= htmlspecialchars($h2['category']['name']) ?></span>
            <?php endif; ?>
            <h3 class="font-semibold text-white line-clamp-3 text-lg"><?= htmlspecialchars($h2['title']) ?></h3>
            <p class="text-white/70 text-xs"><?= htmlspecialchars($h2['published_at'] ?? '') ?> • <?= (int) ($h2['reading_time'] ?? 0) ?> min</p>
        </a>
        <?php endif; ?>

        <?php if ($h3): ?>
        <a href="/articles/<?= htmlspecialchars($h3['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full h-[200px]">
            <img src="<?= htmlspecialchars($h3Img) ?>" data-fallback-url="<?= htmlspecialchars($h3Fallback) ?>" alt="<?= htmlspecialchars($h3['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div class="absolute inset-[18px] flex items-end">
                <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[60%] gap-1.5 min-w-[min(100%,220px)]">
                    <?php if (!empty($h3['category'])): ?>
                    <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($h3['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-semibold text-white line-clamp-3 text-lg"><?= htmlspecialchars($truncateGlassTitle($h3['title'] ?? '')) ?></h3>
                    <p class="text-white/80 text-xs"><?= htmlspecialchars($h3['published_at'] ?? '') ?> • <?= (int) ($h3['reading_time'] ?? 0) ?> min</p>
                </div>
            </div>
        </a>
        <?php endif; ?>
    </div>

    <?php if ($h4): ?>
    <a href="/articles/<?= htmlspecialchars($h4['slug']) ?>" class="relative col-span-5 block rounded-[30px] overflow-hidden border border-gray-200/50 flex flex-col justify-end bg-[#FFF0D4] p-6 gap-2">
        <?php if (!empty($h4['category'])): ?>
        <span class="<?= $tagClass ?>" style="background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($h4['category']['name']) ?></span>
        <?php endif; ?>
        <h3 class="font-semibold text-[#004241] line-clamp-2 text-xl"><?= htmlspecialchars($h4['title']) ?></h3>
        <p class="text-[#004241]/70 text-xs"><?= htmlspecialchars($h4['published_at'] ?? '') ?> • <?= (int) ($h4['reading_time'] ?? 0) ?> min</p>
    </a>
    <?php endif; ?>

    <?php if ($h1): ?>
    <a href="/articles/<?= htmlspecialchars($h1['slug']) ?>" class="col-span-3 block rounded-[30px] overflow-hidden relative w-full h-[200px]">
        <img src="<?= htmlspecialchars($h1Img) ?>" data-fallback-url="<?= htmlspecialchars($h1Fallback) ?>" alt="<?= htmlspecialchars($h1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
        <div class="absolute inset-[18px] flex items-end">
            <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[60%] gap-1.5 min-w-[min(100%,220px)]">
                <?php if (!empty($h1['category'])): ?>
                <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($h1['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-semibold text-white line-clamp-3 text-lg"><?= htmlspecialchars($truncateGlassTitle($h1['title'] ?? '')) ?></h3>
                <p class="text-white/80 text-xs"><?= htmlspecialchars($h1['published_at'] ?? '') ?> • <?= (int) ($h1['reading_time'] ?? 0) ?> min</p>
            </div>
        </div>
    </a>
    <?php endif; ?>

    <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="col-span-8 flex items-center rounded-[30px] overflow-hidden bg-[#FFF0D4] min-h-[132px] p-6">
        <p class="text-[#004241] font-medium leading-snug pr-16 text-lg"><?= htmlspecialchars($writer_cta_description) ?></p>
    </a>
</div>

<!-- Grille principale : mobile 1 col, desktop lg 12 cols -->
<div class="flex flex-col w-full">
    <div class="grid grid-cols-1 md:hidden lg:grid lg:grid-cols-12 gap-6">

        <!-- Colonne gauche: Top news + Standard 2 | lg 5 cols -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            <?php if ($h0): ?>
            <a href="/articles/<?= htmlspecialchars($h0['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full lg:max-w-[519px] h-[438px]">
                <img src="<?= htmlspecialchars($h0Img) ?>" data-fallback-url="<?= htmlspecialchars($h0Fallback) ?>" alt="<?= htmlspecialchars($h0['title'] ?? 'Article à la une') ?>" class="absolute inset-0 w-full h-full object-cover" loading="eager">
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="absolute inset-[18px] flex items-end">
                    <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-full max-w-[300px] gap-1.5">
                        <span class="<?= $tagClass ?>" style="background: #EBF1EF; color: #004241;">Top news</span>
                        <h2 class="font-semibold text-white line-clamp-4 text-[32px] max-sm:text-2xl"><?= htmlspecialchars($truncateGlassTitle($h0['title'] ?? '')) ?></h2>
                        <?php if (!empty($h0['excerpt'])): ?>
                        <p class="text-white/90 line-clamp-3 text-sm"><?= htmlspecialchars($h0['excerpt']) ?></p>
                        <?php endif; ?>
                        <p class="text-white/80 text-xs"><?= htmlspecialchars($h0['published_at'] ?? '') ?> • <?= (int) ($h0['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($h4): ?>
            <a href="/articles/<?= htmlspecialchars($h4['slug']) ?>" class="relative block rounded-[30px] overflow-hidden border border-gray-200/50 flex flex-col justify-end w-full lg:max-w-[519px] h-[280px] bg-[#FFF0D4] p-6 gap-2">
                <?php if (!empty($h4['category'])): ?>
                <span class="<?= $tagClass ?>" style="background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($h4['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-semibold text-[#004241] line-clamp-2 text-xl"><?= htmlspecialchars($h4['title']) ?></h3>
                <p class="text-[#004241]/70 text-xs"><?= htmlspecialchars($h4['published_at'] ?? '') ?> • <?= (int) ($h4['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php endif; ?>
        </div>

        <!-- Colonne droite: Feature 1 + carte verte + Feature 2 | lg 4 cols, xl 4 cols -->
        <div class="lg:col-span-7 xl:col-span-4 flex flex-col gap-6">
            <?php if ($h1): ?>
            <a href="/articles/<?= htmlspecialchars($h1['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full lg:max-w-[411px] h-[237px]">
                <img src="<?= htmlspecialchars($h1Img) ?>" data-fallback-url="<?= htmlspecialchars($h1Fallback) ?>" alt="<?= htmlspecialchars($h1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                <div class="absolute inset-[18px] flex items-end">
                    <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[60%] gap-1.5 min-w-[min(100%,220px)]">
                        <?php if (!empty($h1['category'])): ?>
                        <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($h1['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-semibold text-white line-clamp-3 text-lg"><?= htmlspecialchars($truncateGlassTitle($h1['title'] ?? '')) ?></h3>
                        <p class="text-white/80 text-xs"><?= htmlspecialchars($h1['published_at'] ?? '') ?> • <?= (int) ($h1['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($h2): ?>
            <a href="/articles/<?= htmlspecialchars($h2['slug']) ?>" class="relative block rounded-[30px] overflow-hidden border border-[#004241]/20 flex flex-col justify-end w-full lg:max-w-[413px] h-[221px] bg-[#004241] p-6 gap-2">
                <?php if (!empty($h2['category'])): ?>
                <span class="<?= $tagClass ?>" style="background: <?= $tagStyles['vert']['bg'] ?>; color: <?= $tagStyles['vert']['color'] ?>;"><?= htmlspecialchars($h2['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-semibold text-white line-clamp-2 text-xl"><?= htmlspecialchars($h2['title']) ?></h3>
                <p class="text-white/70 text-xs"><?= htmlspecialchars($h2['published_at'] ?? '') ?> • <?= (int) ($h2['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php endif; ?>

            <?php if ($h3): ?>
            <a href="/articles/<?= htmlspecialchars($h3['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full lg:max-w-[411px] h-[237px]">
                <img src="<?= htmlspecialchars($h3Img) ?>" data-fallback-url="<?= htmlspecialchars($h3Fallback) ?>" alt="<?= htmlspecialchars($h3['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                <div class="absolute inset-[18px] flex items-end">
                    <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[60%] gap-1.5 min-w-[min(100%,220px)]">
                        <?php if (!empty($h3['category'])): ?>
                        <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($h3['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-semibold text-white line-clamp-3 text-lg"><?= htmlspecialchars($truncateGlassTitle($h3['title'] ?? '')) ?></h3>
                        <p class="text-white/80 text-xs"><?= htmlspecialchars($h3['published_at'] ?? '') ?> • <?= (int) ($h3['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <!-- CTA rédacteur lg uniquement -->
            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="hidden lg:flex xl:hidden flex-col rounded-[30px] overflow-hidden w-full bg-[#FFF0D4] min-h-[118px] p-6">
                <p class="text-[#004241] font-medium leading-snug flex-1 z-10 text-lg"><?= htmlspecialchars($writer_cta_description) ?></p>
            </a>
        </div>

        <!-- Colonne pub + CTA : visible xl+ seulement -->
        <div class="hidden xl:flex xl:col-span-3 flex-col gap-6">
            <div class="flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm w-full xl:max-w-[300px] h-[600px] items-center justify-center">
                Espace publicitaire
            </div>
            <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="relative flex flex-col rounded-[30px] overflow-hidden flex-shrink-0 w-[301px] h-[118px] bg-[#FFF0D4] p-[18px]">
                <p class="text-[#004241] font-medium text-sm leading-snug flex-1 z-10"><?= htmlspecialchars($writer_cta_description) ?></p>
            </a>
        </div>

        <!-- CTA rédacteur mobile uniquement -->
        <a href="<?= htmlspecialchars($writer_cta_url) ?>" class="lg:hidden relative flex flex-col rounded-[30px] overflow-hidden w-full bg-[#FFF0D4] min-h-[118px] p-[18px]">
            <p class="text-[#004241] font-medium text-sm leading-snug flex-1 z-10"><?= htmlspecialchars($writer_cta_description) ?></p>
        </a>
    </div>

    <!-- Bannière pub -->
    <div class="flex rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm overflow-hidden items-center justify-center w-full max-w-[970px] h-[250px] mt-[65px]">
        Espace publicitaire (bannière)
    </div>

    <?php if (count($categories) > 0): ?>
    <!-- Section Rubriques -->
    <section id="categories-section" class="grid grid-cols-1 md:grid-cols-8 lg:grid-cols-12 w-full gap-6 mt-[65px]">
        <?php $firstCat = $categories[0] ?? null; ?>
        <a href="<?= $firstCat ? '/categories/'.htmlspecialchars($firstCat['slug']) : '/' ?>" class="md:col-span-5 lg:col-span-7 rounded-[30px] overflow-hidden relative block w-full min-h-[523px]">
            <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800" alt="Découvrez vos rubriques préférées sur Vivat" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div class="absolute inset-0 flex flex-col items-start justify-center p-8">
                <h2 class="font-semibold text-white text-left max-w-[85%] text-5xl md:text-2xl lg:text-5xl">Découvrez vos rubriques préférées</h2>
                <p class="text-white/95 mt-2 text-left max-w-[85%] text-2xl md:text-base lg:text-2xl">Explorez dès maintenant les contenus qui vous correspondent.</p>
            </div>
        </a>

        <!-- Droite: grille de rubriques + bouton suivant -->
        <div class="md:col-span-3 lg:col-span-5 flex items-center w-full min-w-0 gap-6">
            <div class="flex items-stretch min-w-0 flex-1 gap-6">
                <?php foreach ($catChunks as $chunkIdx => $chunk):
                    $cat1 = $chunk[0] ?? null;
                    $cat2 = $chunk[1] ?? null;
                    $cat3 = $chunk[2] ?? null;
                ?>
                <div class="categories-group grid-cols-2 gap-6 w-full min-w-0 <?= $chunkIdx > 0 ? 'hidden' : 'grid' ?>" data-group="<?= $chunkIdx ?>">
                    <div class="flex flex-col min-w-0 gap-6 row-span-2">
                        <?php if ($cat1): ?>
                        <a href="/categories/<?= htmlspecialchars($cat1['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full flex-shrink-0 h-[250px]">
                            <?php if (!empty($cat1['image_url'])): ?>
                            <img src="<?= htmlspecialchars($cat1['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat1['name']) ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-black/30"></div>
                            <div class="absolute inset-0 flex items-center justify-center p-[18px]">
                                <span class="text-white font-semibold text-center text-xl"><?= htmlspecialchars($cat1['name']) ?></span>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if ($cat2): ?>
                        <a href="/categories/<?= htmlspecialchars($cat2['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full flex-shrink-0 h-[250px]">
                            <?php if (!empty($cat2['image_url'])): ?>
                            <img src="<?= htmlspecialchars($cat2['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat2['name']) ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-black/30"></div>
                            <div class="absolute inset-0 flex items-center justify-center p-[18px]">
                                <span class="text-white font-semibold text-center text-xl"><?= htmlspecialchars($cat2['name']) ?></span>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>

                    <?php if ($cat3): ?>
                    <a href="/categories/<?= htmlspecialchars($cat3['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full min-w-0 h-[523px] row-span-2">
                        <?php if (!empty($cat3['image_url'])): ?>
                        <img src="<?= htmlspecialchars($cat3['image_url']) ?>" alt="Rubrique <?= htmlspecialchars($cat3['name']) ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-black/30"></div>
                        <div class="absolute inset-0 flex items-center justify-center p-[18px]">
                            <span class="text-white font-semibold text-center text-xl"><?= htmlspecialchars($cat3['name']) ?></span>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($catChunks) > 1): ?>
            <button type="button" id="categories-next" class="flex-shrink-0 flex items-center justify-center rounded-full bg-[#004241] text-white relative z-10 -ml-[45px] w-[42px] h-[42px]" aria-label="Rubriques suivantes">
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
            groups[idx].classList.remove('grid');
            groups[idx].classList.add('hidden');
            idx = (idx + 1) % groups.length;
            groups[idx].classList.remove('hidden');
            groups[idx].classList.add('grid');
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
    $restArticles = array_values(array_filter($latest, fn($a) => !in_array($a['id'] ?? null, $highlightIds)));
    // Déduplification par id puis slug
    $byId = [];
    foreach ($restArticles as $a) {
        $id = $a['id'] ?? null;
        if ($id !== null && !isset($byId[$id])) $byId[$id] = $a;
    }
    $bySlug = [];
    foreach (array_values($byId) as $a) {
        $slug = $a['slug'] ?? null;
        if ($slug !== null && $slug !== '' && !isset($bySlug[$slug])) $bySlug[$slug] = $a;
    }
    $restArticles = array_values($bySlug);
    // Sélection des articles pour cartes "photo complète"
    $reservedIndices = [0, 1, 2, 3, 4, 6, 10, 11];
    $restForPhotos = [];
    foreach ($restArticles as $idx => $a) {
        if (!in_array($idx, $reservedIndices, true)) $restForPhotos[] = $a;
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
    <section class="grid grid-cols-1 md:grid-cols-8 lg:grid-cols-12 w-full min-w-0 mt-6 gap-6">
        <h2 class="font-medium text-[#004241] mb-6 md:col-span-8 lg:col-span-12 text-[32px]">Dernières actualités</h2>

        <!-- Colonne gauche | md 4 cols, lg 6 cols -->
        <div class="md:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php
                $firstArt  = $restArticles[0] ?? null;
                $secondArt = $restArticles[1] ?? null;
                ?>
                <?php if ($firstArt): ?>
                <?php $f0CatSlug = $firstArt['category']['slug'] ?? null; $f0ArtId = $firstArt['id'] ?? $firstArt['slug'] ?? null; $f0Fallback = vivat_category_fallback_image($f0CatSlug, 302, 419, $f0ArtId, 'card-0'); $f0Img = !empty($firstArt['cover_image_url']) ? $firstArt['cover_image_url'] : $f0Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($firstArt['slug']) ?>" class="block rounded-[30px] overflow-hidden relative min-w-0 w-full h-[419px]">
                    <img src="<?= htmlspecialchars($f0Img) ?>" data-fallback-url="<?= htmlspecialchars($f0Fallback) ?>" alt="<?= htmlspecialchars($firstArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute inset-[18px] flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[60%] min-w-0 gap-1.5 min-w-[180px]">
                            <?php if (!empty($firstArt['category'])): ?>
                            <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($firstArt['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3 text-xl"><?= htmlspecialchars($truncateGlassTitle($firstArt['title'] ?? '')) ?></h3>
                            <p class="text-white/80 text-xs"><?= htmlspecialchars($firstArt['published_at'] ?? '') ?> • <?= (int) ($firstArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($secondArt): ?>
                <?php $artCatSlug = $secondArt['category']['slug'] ?? null; $artId = $secondArt['id'] ?? $secondArt['slug'] ?? null; $artFallback = vivat_category_fallback_image($artCatSlug, 254, 190, $artId, 'card-1'); $artImg = !empty($secondArt['cover_image_url']) ? $secondArt['cover_image_url'] : $artFallback; ?>
                <a href="/articles/<?= htmlspecialchars($secondArt['slug']) ?>" class="flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full h-[419px] bg-[#EBF1EF] p-6 gap-[18px]">
                    <div class="flex flex-col flex-1 min-h-0 gap-2">
                        <?php if (!empty($secondArt['category'])): ?>
                        <span class="<?= $tagClass ?>" style="background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($secondArt['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3 text-xl"><?= htmlspecialchars($secondArt['title']) ?></h3>
                        <p class="text-[#004241] font-light text-xs"><?= htmlspecialchars($secondArt['published_at'] ?? '') ?> • <?= (int) ($secondArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full h-[190px]">
                        <img src="<?= htmlspecialchars($artImg) ?>" data-fallback-url="<?= htmlspecialchars($artFallback) ?>" alt="<?= htmlspecialchars($secondArt['title'] ?? 'Article') ?>" class="w-full h-full object-cover" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>
            </div>

            <?php $hotNewsArt = $restArticles[2] ?? null; if ($hotNewsArt): ?>
            <?php $hotCatSlug = $hotNewsArt['category']['slug'] ?? null; $hotArtId = $hotNewsArt['id'] ?? $hotNewsArt['slug'] ?? null; $hotFallback = vivat_category_fallback_image($hotCatSlug, 626, 240, $hotArtId, 'hot'); $hotNewsImg = !empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : $hotFallback; ?>
            <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="block rounded-[32px] overflow-hidden relative min-w-0 w-full h-60">
                <img src="<?= htmlspecialchars($hotNewsImg) ?>" data-fallback-url="<?= htmlspecialchars($hotFallback) ?>" alt="<?= htmlspecialchars($hotNewsArt['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-[18px] flex justify-end items-end">
                    <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-[264px] max-w-[60%] gap-1.5 min-w-[min(100%,240px)]">
                        <?php if (!empty($hotNewsArt['category'])): ?>
                        <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($hotNewsArt['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-white line-clamp-3 text-xl"><?= htmlspecialchars($truncateGlassTitle($hotNewsArt['title'] ?? '')) ?></h3>
                        <p class="text-white/80 text-xs"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php $artLeft = $restArticles[10] ?? null; if ($artLeft): ?>
                <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="relative flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full h-[419px] bg-[#FFEFD1] p-6 gap-[18px]">
                    <div class="flex flex-col min-h-0 gap-2">
                        <?php if (!empty($artLeft['category'])): ?>
                        <span class="<?= $tagClass ?>" style="background: <?= $tagStyles['jaune']['bg'] ?>; color: <?= $tagStyles['jaune']['color'] ?>;"><?= htmlspecialchars($artLeft['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3 text-xl"><?= htmlspecialchars($artLeft['title']) ?></h3>
                        <p class="text-[#004241] font-light text-xs"><?= htmlspecialchars($artLeft['published_at'] ?? '') ?> • <?= (int) ($artLeft['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php endif; ?>

                <?php $artLeft2 = $restArticles[6] ?? null; if ($artLeft2): ?>
                <?php $left2CatSlug = $artLeft2['category']['slug'] ?? null; $left2ArtId = $artLeft2['id'] ?? $artLeft2['slug'] ?? null; $left2Fallback = vivat_category_fallback_image($left2CatSlug, 302, 419, $left2ArtId, 'left2'); $artLeft2Img = !empty($artLeft2['cover_image_url']) ? $artLeft2['cover_image_url'] : $left2Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="block rounded-[30px] overflow-hidden relative min-w-0 w-full h-[419px]">
                    <img src="<?= htmlspecialchars($artLeft2Img) ?>" data-fallback-url="<?= htmlspecialchars($left2Fallback) ?>" alt="<?= htmlspecialchars($artLeft2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute inset-[18px] flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[60%] min-w-0 gap-1.5 min-w-[180px]">
                            <?php if (!empty($artLeft2['category'])): ?>
                            <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($artLeft2['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3 text-xl"><?= htmlspecialchars($truncateGlassTitle($artLeft2['title'] ?? '')) ?></h3>
                            <p class="text-white/80 text-xs"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Colonne droite | md 4 cols, lg 6 cols -->
        <div class="md:col-span-4 lg:col-span-6 flex flex-col min-w-0 w-full gap-6">
            <?php
            $stdColors = ['#004241', '#FFEFD1'];
            foreach (array_slice($restArticles, 3, 2) as $i => $art):
                $bg = $stdColors[$i % 2];
                $isDark = ($bg === '#004241');
            ?>
            <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="relative flex flex-col justify-end rounded-[30px] overflow-hidden min-w-0 w-full h-[198px] p-6 gap-2 border border-white/10 <?= $isDark ? 'bg-[#004241]' : 'bg-[#FFEFD1]' ?>">
                <?php if (!empty($art['category'])): ?>
                <?php $tagVariant = $isDark ? 'vert' : 'jaune'; ?>
                <span class="<?= $tagClass ?>" style="background: <?= $tagStyles[$tagVariant]['bg'] ?>; color: <?= $tagStyles[$tagVariant]['color'] ?>;"><?= htmlspecialchars($art['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-medium line-clamp-2 text-xl <?= $isDark ? 'text-white' : 'text-[#004241]' ?>"><?= htmlspecialchars($art['title']) ?></h3>
                <p class="text-xs <?= $isDark ? 'text-white/70' : 'text-[#004241]/70' ?>"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php endforeach; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 min-w-0 gap-6">
                <?php $artRight = $restArticles[11] ?? null; if ($artRight): ?>
                <?php $rightCatSlug = $artRight['category']['slug'] ?? null; $rightArtId = $artRight['id'] ?? $artRight['slug'] ?? null; $rightFallback = vivat_category_fallback_image($rightCatSlug, 254, 190, $rightArtId, 'right'); $artRightImg = !empty($artRight['cover_image_url']) ? $artRight['cover_image_url'] : $rightFallback; ?>
                <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="flex flex-col rounded-[30px] overflow-hidden min-w-0 w-full h-[419px] bg-[#EBF1EF] p-6 gap-[18px]">
                    <div class="flex flex-col flex-1 min-h-0 gap-2">
                        <?php if (!empty($artRight['category'])): ?>
                        <span class="<?= $tagClass ?>" style="background: <?= $tagStyles['gris']['bg'] ?>; color: <?= $tagStyles['gris']['color'] ?>;"><?= htmlspecialchars($artRight['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-[#004241] line-clamp-3 text-xl"><?= htmlspecialchars($artRight['title']) ?></h3>
                        <p class="text-[#004241] font-light text-xs"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                    </div>
                    <div class="rounded-[21px] overflow-hidden flex-shrink-0 w-full h-[190px]">
                        <img src="<?= htmlspecialchars($artRightImg) ?>" data-fallback-url="<?= htmlspecialchars($rightFallback) ?>" alt="<?= htmlspecialchars($artRight['title'] ?? 'Article') ?>" class="w-full h-full object-cover" loading="lazy">
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($artForFullPhoto1): ?>
                <?php $full1CatSlug = $artForFullPhoto1['category']['slug'] ?? null; $full1ArtId = $artForFullPhoto1['id'] ?? $artForFullPhoto1['slug'] ?? null; $full1Fallback = vivat_category_fallback_image($full1CatSlug, 302, 419, $full1ArtId, 'full1'); $fullPhoto1Img = !empty($artForFullPhoto1['cover_image_url']) ? $artForFullPhoto1['cover_image_url'] : $full1Fallback; ?>
                <a href="/articles/<?= htmlspecialchars($artForFullPhoto1['slug']) ?>" class="block rounded-[25px] overflow-hidden relative min-w-0 w-full h-[419px]">
                    <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" data-fallback-url="<?= htmlspecialchars($full1Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto1['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute inset-[18px] flex items-end z-10">
                        <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[60%] min-w-0 gap-1.5 min-w-[180px]">
                            <?php if (!empty($artForFullPhoto1['category'])): ?>
                            <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($artForFullPhoto1['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3 text-xl"><?= htmlspecialchars($truncateGlassTitle($artForFullPhoto1['title'] ?? '')) ?></h3>
                            <p class="text-white/80 text-xs"><?= htmlspecialchars($artForFullPhoto1['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto1['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php endif; ?>
            </div>

            <?php if ($artForFullPhoto2): ?>
            <?php $full2CatSlug = $artForFullPhoto2['category']['slug'] ?? null; $full2ArtId = $artForFullPhoto2['id'] ?? $artForFullPhoto2['slug'] ?? null; $full2Fallback = vivat_category_fallback_image($full2CatSlug, 629, 235, $full2ArtId, 'full2'); $fullPhoto2Img = !empty($artForFullPhoto2['cover_image_url']) ? $artForFullPhoto2['cover_image_url'] : $full2Fallback; ?>
            <a href="/articles/<?= htmlspecialchars($artForFullPhoto2['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full min-w-0 h-[235px]">
                <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" data-fallback-url="<?= htmlspecialchars($full2Fallback) ?>" alt="<?= htmlspecialchars($artForFullPhoto2['title'] ?? 'Article') ?>" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                <div class="absolute inset-[18px] flex items-end">
                    <div class="rounded-[21px] flex flex-col <?= $glassBox ?> w-fit max-w-[60%] gap-1.5 min-w-[min(100%,220px)]">
                        <?php if (!empty($artForFullPhoto2['category'])): ?>
                        <span class="<?= $tagGlass ?>" style="color: #fff;"><?= htmlspecialchars($artForFullPhoto2['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-white line-clamp-3 text-xl"><?= htmlspecialchars($truncateGlassTitle($artForFullPhoto2['title'] ?? '')) ?></h3>
                        <p class="text-white/80 text-xs"><?= htmlspecialchars($artForFullPhoto2['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto2['reading_time'] ?? 0) ?> min</p>
                    </div>
                </div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Bouton Autres actualités -->
        <div class="flex justify-center md:col-span-8 lg:col-span-12">
            <a href="/articles" class="inline-flex items-center justify-center rounded-full font-medium text-white gap-2.5 h-12 w-[226px] bg-[#004241] px-[18px]">
                Autres actualités
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($pagination && $pagination->lastPage() > 1): ?>
    <?php
    $paginationView = $pagination->withQueryString();
    $pageWindowStart = max(1, $paginationView->currentPage() - 2);
    $pageWindowEnd   = min($paginationView->lastPage(), $paginationView->currentPage() + 2);
    ?>
    <nav class="mt-10 flex flex-wrap items-center justify-center gap-3" aria-label="Pagination des actualités">
        <?php if ($paginationView->onFirstPage()): ?>
        <span class="inline-flex h-12 min-w-12 items-center justify-center rounded-full px-5 text-sm font-medium text-[#004241]/35 bg-[#EBF1EF]">Précédent</span>
        <?php else: ?>
        <a href="<?= htmlspecialchars($paginationView->previousPageUrl()) ?>" class="inline-flex h-12 min-w-12 items-center justify-center rounded-full px-5 text-sm font-medium text-white bg-[#004241]">Précédent</a>
        <?php endif; ?>

        <?php for ($page = $pageWindowStart; $page <= $pageWindowEnd; $page++): ?>
        <?php $isActivePage = $page === $paginationView->currentPage(); ?>
        <?php if ($isActivePage): ?>
        <span class="inline-flex h-12 w-12 items-center justify-center rounded-full text-sm font-semibold text-white bg-[#004241]"><?= $page ?></span>
        <?php else: ?>
        <a href="<?= htmlspecialchars($paginationView->url($page)) ?>" class="inline-flex h-12 w-12 items-center justify-center rounded-full text-sm font-semibold text-[#004241] bg-[#FFF0D4]"><?= $page ?></a>
        <?php endif; ?>
        <?php endfor; ?>

        <?php if ($paginationView->hasMorePages()): ?>
        <a href="<?= htmlspecialchars($paginationView->nextPageUrl()) ?>" class="inline-flex h-12 min-w-12 items-center justify-center rounded-full px-5 text-sm font-medium text-white bg-[#004241]">Suivant</a>
        <?php else: ?>
        <span class="inline-flex h-12 min-w-12 items-center justify-center rounded-full px-5 text-sm font-medium text-[#004241]/35 bg-[#EBF1EF]">Suivant</span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

</div>
