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
?>
<!-- Grille articles - Design System Figma -->
<div class="flex flex-col">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5" style="column-gap: 21px;">
        <!-- Colonne gauche: Hot news + Standard 2 -->
        <div class="lg:col-span-5 flex flex-col gap-5" style="gap: 31px;">
            <?php if ($top_news): ?>
            <!-- Hot news: 519x438, radius 30, overlay 20%, inner card glass -->
            <a href="/articles/<?= htmlspecialchars($top_news['slug']) ?>" class="block rounded-[30px] overflow-hidden relative" style="width: 100%; max-width: 519px; height: 438px;">
                <?php if (!empty($top_news['cover_image_url'])): ?>
                <img src="<?= htmlspecialchars($top_news['cover_image_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                <?php endif; ?>
                <div class="absolute inset-0" style="background: rgba(0,0,0,0.2);"></div>
                <div class="absolute bottom-6 left-6 rounded-[21px] flex flex-col p-6 gap-2 border" style="width: 299px; background: rgba(255,255,255,0.11); border: 1px solid rgba(255,255,255,0.15);">
                    <span class="text-xs font-medium uppercase tracking-wide text-white/90">Top news</span>
                    <h2 class="font-semibold text-white line-clamp-2" style="font-size: 32px; font-family: Figtree, sans-serif;"><?= htmlspecialchars($top_news['title']) ?></h2>
                    <?php if (!empty($top_news['excerpt'])): ?>
                    <p class="text-white/90 line-clamp-2" style="font-size: 16px;"><?= htmlspecialchars($top_news['excerpt']) ?></p>
                    <?php endif; ?>
                    <p class="text-white/80 text-sm mt-auto"><?= htmlspecialchars($top_news['published_at'] ?? '') ?> • <?= (int) ($top_news['reading_time'] ?? 0) ?> min</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($standard2): ?>
            <!-- Standard 2: 519x280, #FFF0D4, radius 30, pas de photo -->
            <a href="/articles/<?= htmlspecialchars($standard2['slug']) ?>" class="block rounded-[30px] overflow-hidden border border-gray-200/50 p-6 flex flex-col justify-end" style="width: 100%; max-width: 519px; height: 280px; background: #FFF0D4;">
                <?php if (!empty($standard2['category'])): ?>
                <span class="text-xs font-medium text-[#004241]/80"><?= htmlspecialchars($standard2['category']['name']) ?></span>
                <?php endif; ?>
                <h3 class="font-semibold text-[#004241] line-clamp-2 mt-1" style="font-size: 18px;"><?= htmlspecialchars($standard2['title']) ?></h3>
                <p class="text-sm text-[#004241]/70 mt-2"><?= htmlspecialchars($standard2['published_at'] ?? '') ?> • <?= (int) ($standard2['reading_time'] ?? 0) ?> min</p>
            </a>
            <?php endif; ?>
        </div>

        <!-- Colonne milieu: Feature + Standard 1 (21px marge avec hot news) -->
        <div class="lg:col-span-4 flex flex-col gap-5" style="gap: 21px;">
            <?php if ($feature1): ?>
            <!-- Feature: 411x237, image + titre, pas de description -->
            <a href="/articles/<?= htmlspecialchars($feature1['slug']) ?>" class="block rounded-[30px] overflow-hidden relative" style="width: 100%; max-width: 411px; height: 237px;">
                <?php if (!empty($feature1['cover_image_url'])): ?>
                <img src="<?= htmlspecialchars($feature1['cover_image_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 flex flex-col gap-2">
                    <?php if (!empty($feature1['category'])): ?>
                    <span class="text-xs font-medium text-white/90"><?= htmlspecialchars($feature1['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-semibold text-white line-clamp-2" style="font-size: 18px;"><?= htmlspecialchars($feature1['title']) ?></h3>
                    <p class="text-white/80 text-sm"><?= htmlspecialchars($feature1['published_at'] ?? '') ?> • <?= (int) ($feature1['reading_time'] ?? 0) ?> min</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($standard1): ?>
            <!-- Standard 1: 413x221, #004241, pas de photo -->
            <a href="/articles/<?= htmlspecialchars($standard1['slug']) ?>" class="block rounded-[30px] overflow-hidden border border-[#004241]/20 p-4 flex flex-col justify-end" style="width: 100%; max-width: 413px; height: 221px; background: #004241;">
                <div class="flex flex-col gap-2">
                    <?php if (!empty($standard1['category'])): ?>
                    <span class="text-xs font-medium text-white/80"><?= htmlspecialchars($standard1['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-semibold text-white line-clamp-2" style="font-size: 16px;"><?= htmlspecialchars($standard1['title']) ?></h3>
                    <p class="text-white/70 text-sm"><?= htmlspecialchars($standard1['published_at'] ?? '') ?> • <?= (int) ($standard1['reading_time'] ?? 0) ?> min</p>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($feature2): ?>
            <!-- Feature 2: 411x237, image + titre, pas de description (en dessous du standard vert) -->
            <a href="/articles/<?= htmlspecialchars($feature2['slug']) ?>" class="block rounded-[30px] overflow-hidden relative" style="width: 100%; max-width: 411px; height: 237px;">
                <?php if (!empty($feature2['cover_image_url'])): ?>
                <img src="<?= htmlspecialchars($feature2['cover_image_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 flex flex-col gap-2">
                    <?php if (!empty($feature2['category'])): ?>
                    <span class="text-xs font-medium text-white/90"><?= htmlspecialchars($feature2['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-semibold text-white line-clamp-2" style="font-size: 18px;"><?= htmlspecialchars($feature2['title']) ?></h3>
                    <p class="text-white/80 text-sm"><?= htmlspecialchars($feature2['published_at'] ?? '') ?> • <?= (int) ($feature2['reading_time'] ?? 0) ?> min</p>
                </div>
            </a>
            <?php endif; ?>
        </div>

        <!-- Colonne droite: Espace pub + CTA aligné avec featured -->
        <div class="lg:col-span-3 flex flex-col gap-5" style="gap: 21px;">
            <div class="flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm" style="width: 300px; height: 600px; padding-right: 48px; padding-bottom: 48px; gap: 8px;">
                <div class="flex-1 flex items-center justify-center">Espace publicitaire</div>
            </div>
            <!-- CTA: 301x114, flow vertical, aligné avec featured à gauche -->
            <a href="<?= htmlspecialchars($writer_signup_url) ?>" class="flex flex-col rounded-[30px] overflow-hidden flex-shrink-0" style="width: 301px; height: 114px; background: #FFF0D4; padding: 18px 8px 8px 18px; gap: 18px;">
                <p class="text-[#004241] font-medium text-sm leading-snug flex-1">Vivat est aussi écrit par ses lecteurs. Partagez votre point de vue.</p>
                <div class="flex justify-end">
                    <span class="flex items-center justify-center w-10 h-10 rounded-full bg-[#004241] text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>
            </a>
        </div>
    </div>

    <!-- 2ème pub : en dessous des articles, 65px de marge -->
    <div class="flex flex-col rounded-[30px] bg-gray-100 border-2 border-dashed border-gray-300 text-gray-400 text-sm overflow-hidden" style="width: 100%; max-width: 970px; height: 250px; padding: 48px; gap: 8px; margin-top: 65px;">
        <div class="flex-1 flex items-center justify-center">Espace publicitaire (bannière)</div>
    </div>

    <?php if (count($categories) > 0): ?>
    <!-- Découvrez vos rubriques préférées - 65px en dessous de la pub -->
    <section id="categories-section" style="margin-top: 65px;">
        <div class="flex flex-col lg:flex-row gap-6" style="gap: 24px;">
            <!-- Grande carte gauche: 732x523, titre 48px Figtree 600, description 24px 400 -->
            <a href="/categories" class="flex-shrink-0 rounded-[30px] overflow-hidden relative block" style="width: 100%; max-width: 732px; height: 523px;">
                <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800" alt="" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8">
                    <h2 class="font-semibold text-white" style="font-family: Figtree, sans-serif; font-size: 48px; font-weight: 600;">Découvrez vos rubriques préférées</h2>
                    <p class="text-white/95 mt-2" style="font-size: 24px; font-weight: 400;">Explorez dès maintenant les contenus qui vous correspondent.</p>
                </div>
            </a>

            <!-- Droite: 2 petites + 1 grande + flèche, 24px marge à gauche -->
            <div class="flex flex-shrink-0 items-center" style="gap: 24px;">
                <div class="categories-carousel flex items-start gap-3">
                    <?php foreach ($catChunks as $chunkIdx => $chunk):
                        $cat1 = $chunk[0] ?? null;
                        $cat2 = $chunk[1] ?? null;
                        $cat3 = $chunk[2] ?? null;
                    ?>
                    <div class="categories-group flex items-stretch gap-3 <?= $chunkIdx > 0 ? 'hidden' : '' ?>" data-group="<?= $chunkIdx ?>">
                        <!-- 2 petites cartes (193x250) à gauche -->
                        <div class="flex flex-col gap-3">
                            <?php if ($cat1): ?>
                            <a href="/categories/<?= htmlspecialchars($cat1['slug']) ?>" class="block rounded-[30px] overflow-hidden relative flex-shrink-0" style="width: 193px; height: 250px;">
                                <?php if (!empty($cat1['image_url'])): ?>
                                <img src="<?= htmlspecialchars($cat1['image_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                                <?php endif; ?>
                                <div class="absolute inset-0" style="background: #00000040;"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-4">
                                    <span class="text-white font-semibold" style="font-size: 20px;"><?= htmlspecialchars($cat1['name']) ?></span>
                                </div>
                            </a>
                            <?php endif; ?>
                            <?php if ($cat2): ?>
                            <a href="/categories/<?= htmlspecialchars($cat2['slug']) ?>" class="block rounded-[30px] overflow-hidden relative flex-shrink-0" style="width: 193px; height: 250px;">
                                <?php if (!empty($cat2['image_url'])): ?>
                                <img src="<?= htmlspecialchars($cat2['image_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                                <?php endif; ?>
                                <div class="absolute inset-0" style="background: #00000040;"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-4">
                                    <span class="text-white font-semibold" style="font-size: 20px;"><?= htmlspecialchars($cat2['name']) ?></span>
                                </div>
                            </a>
                            <?php endif; ?>
                        </div>
                        <!-- Grande carte (298x523) à droite -->
                        <?php if ($cat3): ?>
                        <a href="/categories/<?= htmlspecialchars($cat3['slug']) ?>" class="block rounded-[30px] overflow-hidden relative flex-shrink-0 self-stretch" style="width: 298px; height: 523px;">
                            <?php if (!empty($cat3['image_url'])): ?>
                            <img src="<?= htmlspecialchars($cat3['image_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                            <?php endif; ?>
                            <div class="absolute inset-0" style="background: #00000033;"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-6">
                                <span class="text-white font-semibold" style="font-size: 20px;"><?= htmlspecialchars($cat3['name']) ?></span>
                            </div>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($catChunks) > 1): ?>
                <!-- Flèche: 42x42, radius 29px, #004241 - pointant à droite, moitié collée à la grande carte -->
                <button type="button" id="categories-next" class="flex-shrink-0 flex items-center justify-center rounded-full bg-[#004241] text-white hover:bg-[#003535] transition -ml-[45px] relative z-10" style="width: 42px; height: 42px; border-radius: 29px;" aria-label="Rubriques suivantes">
                    <svg class="w-4 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
                <?php endif; ?>
            </div>
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
    <section class="mt-12">
        <!-- Titre: Figtree 32px Medium -->
        <h2 class="font-medium text-[#004241] mb-6" style="font-size: 32px;">Dernières actualités</h2>

        <div class="flex flex-col lg:flex-row gap-6" style="gap: 22px;">
            <!-- Colonne gauche: 2 featured + hot news -->
            <div class="flex flex-col" style="gap: 25px;">
                <!-- 2 featured cards (302x419), 22px entre elles -->
                <div class="flex gap-5" style="gap: 22px;">
                    <?php foreach (array_slice($restArticles, 0, 2) as $art): ?>
                    <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="flex flex-col rounded-[30px] overflow-hidden flex-shrink-0 p-6" style="width: 302px; height: 419px; background: #EBF1EF; gap: 18px;">
                        <div class="flex flex-col justify-end flex-1 min-h-0" style="gap: 18px;">
                            <?php if (!empty($art['category'])): ?>
                            <span class="text-xs font-medium text-[#004241]/80"><?= htmlspecialchars($art['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($art['title']) ?></h3>
                            <p class="text-[#004241] text-sm font-light"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
                        </div>
                        <?php if (!empty($art['cover_image_url'])): ?>
                        <div class="rounded-[21px] overflow-hidden flex-shrink-0" style="width: 254px; height: 190px;">
                            <img src="<?= htmlspecialchars($art['cover_image_url']) ?>" alt="" class="w-full h-full object-cover">
                        </div>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Hot news (626x240), photo prend toute la card -->
                <?php $hotNewsArt = $restArticles[2] ?? null; if ($hotNewsArt): ?>
                <a href="/articles/<?= htmlspecialchars($hotNewsArt['slug']) ?>" class="block rounded-[32px] overflow-hidden relative" style="width: 100%; max-width: 626px; height: 240px;">
                    <?php
                    $hotNewsImg = !empty($hotNewsArt['cover_image_url']) ? $hotNewsArt['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($hotNewsArt['slug'] ?? '') . '/626/240';
                    ?>
                    <img src="<?= htmlspecialchars($hotNewsImg) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 flex items-center justify-end p-6">
                        <div class="rounded-[21px] flex flex-col p-6 gap-2 border" style="width: 264px; background: rgba(255,255,255,0.11); border: 1px solid rgba(255,255,255,0.15);">
                            <?php if (!empty($hotNewsArt['category'])): ?>
                            <span class="inline-flex items-center rounded-full text-xs font-medium text-white/90 self-start px-3 py-2" style="border: 2px solid rgba(255,255,255,0.03); background: rgba(255,255,255,0.32);"><?= htmlspecialchars($hotNewsArt['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-2" style="font-size: 20px;"><?= htmlspecialchars($hotNewsArt['title']) ?></h3>
                            <p class="text-white/80 text-sm"><?= htmlspecialchars($hotNewsArt['published_at'] ?? '') ?> • <?= (int) ($hotNewsArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php endif; ?>

                <!-- 3e ligne gauche: #FFEFD1 + #EBF1EF côte à côte (left 82px et 405px) -->
                <div class="flex gap-5" style="gap: 22px; margin-top: 25px;">
                    <?php $artLeft = $restArticles[10] ?? null; if ($artLeft): ?>
                    <a href="/articles/<?= htmlspecialchars($artLeft['slug']) ?>" class="flex flex-col rounded-[30px] overflow-hidden flex-shrink-0 p-6" style="width: 302px; height: 419px; background: #FFEFD1; gap: 18px;">
                        <div class="flex flex-col justify-end flex-1 min-h-0" style="gap: 18px;">
                            <?php if (!empty($artLeft['category'])): ?>
                            <span class="text-xs font-medium text-[#004241]/80"><?= htmlspecialchars($artLeft['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artLeft['title']) ?></h3>
                            <p class="text-[#004241] text-sm font-light"><?= htmlspecialchars($artLeft['published_at'] ?? '') ?> • <?= (int) ($artLeft['reading_time'] ?? 0) ?> min</p>
                        </div>
                        <?php if (!empty($artLeft['cover_image_url'])): ?>
                        <div class="rounded-[21px] overflow-hidden flex-shrink-0" style="width: 254px; height: 190px;">
                            <img src="<?= htmlspecialchars($artLeft['cover_image_url']) ?>" alt="" class="w-full h-full object-cover">
                        </div>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                    <?php $artLeft2 = $restArticles[6] ?? null; if ($artLeft2): ?>
                    <a href="/articles/<?= htmlspecialchars($artLeft2['slug']) ?>" class="flex flex-col rounded-[30px] overflow-hidden flex-shrink-0 p-6" style="width: 302px; height: 419px; background: #EBF1EF; gap: 18px;">
                        <div class="flex flex-col justify-end flex-1 min-h-0" style="gap: 18px;">
                            <?php if (!empty($artLeft2['category'])): ?>
                            <span class="text-xs font-medium text-[#004241]/80"><?= htmlspecialchars($artLeft2['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artLeft2['title']) ?></h3>
                            <p class="text-[#004241] text-sm font-light"><?= htmlspecialchars($artLeft2['published_at'] ?? '') ?> • <?= (int) ($artLeft2['reading_time'] ?? 0) ?> min</p>
                        </div>
                        <?php if (!empty($artLeft2['cover_image_url'])): ?>
                        <div class="rounded-[21px] overflow-hidden flex-shrink-0" style="width: 254px; height: 190px;">
                            <img src="<?= htmlspecialchars($artLeft2['cover_image_url']) ?>" alt="" class="w-full h-full object-cover">
                        </div>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Colonne droite: 2 standards + double (#EBF1EF + image) + wide card -->
            <div class="flex flex-col flex-1" style="gap: 24px; max-width: 629px;">
                <?php
                $stdColors = ['#004241', '#FFEFD1'];
                foreach (array_slice($restArticles, 3, 2) as $i => $art):
                    $bg = $stdColors[$i % 2];
                    $isDark = ($bg === '#004241');
                ?>
                <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="flex flex-col rounded-[30px] overflow-hidden border p-6 relative" style="width: 100%; height: 198px; background: <?= $bg ?>; border: 1px solid rgba(255,255,255,0.1); gap: 8px;">
                    <?php if (!$isDark && !empty($art['cover_image_url'])): ?>
                    <img src="<?= htmlspecialchars($art['cover_image_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-30" style="filter: blur(60px);">
                    <?php endif; ?>
                    <?php if (!empty($art['category'])): ?>
                    <span class="inline-flex items-center rounded-full text-xs font-medium self-start px-3 py-2 <?= $isDark ? 'text-white/90' : 'text-[#004241]/90' ?>" style="border: 2px solid rgba(255,255,255,0.03); background: rgba(255,255,255,0.32);"><?= htmlspecialchars($art['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-medium line-clamp-2 flex-1 <?= $isDark ? 'text-white' : 'text-[#004241]' ?>" style="font-size: 20px;"><?= htmlspecialchars($art['title']) ?></h3>
                    <p class="text-sm <?= $isDark ? 'text-white/70' : 'text-[#004241]/70' ?>"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
                </a>
                <?php endforeach; ?>

                <!-- 3e ligne droite: #EBF1EF + card image complète côte à côte -->
                <div class="flex gap-5" style="gap: 22px;">
                    <?php $artRight = $restArticles[11] ?? null; if ($artRight): ?>
                    <a href="/articles/<?= htmlspecialchars($artRight['slug']) ?>" class="flex flex-col rounded-[30px] overflow-hidden flex-shrink-0" style="width: 302px; height: 419px; padding: 24px; gap: 18px; background: #EBF1EF;">
                        <div class="flex flex-col justify-end flex-1 min-h-0" style="gap: 18px;">
                            <?php if (!empty($artRight['category'])): ?>
                            <span class="text-xs font-medium text-[#004241]/80"><?= htmlspecialchars($artRight['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-[#004241] line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artRight['title']) ?></h3>
                            <p class="text-[#004241] text-sm font-light"><?= htmlspecialchars($artRight['published_at'] ?? '') ?> • <?= (int) ($artRight['reading_time'] ?? 0) ?> min</p>
                        </div>
                        <?php if (!empty($artRight['cover_image_url'])): ?>
                        <div class="rounded-[21px] overflow-hidden flex-shrink-0" style="width: 254px; height: 190px;">
                            <img src="<?= htmlspecialchars($artRight['cover_image_url']) ?>" alt="" class="w-full h-full object-cover">
                        </div>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($artForFullPhoto1): ?>
                    <?php $fullPhoto1Img = !empty($artForFullPhoto1['cover_image_url']) ? $artForFullPhoto1['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($artForFullPhoto1['slug'] ?? '') . '/302/419'; ?>
                    <a href="/articles/<?= htmlspecialchars($artForFullPhoto1['slug']) ?>" class="flex flex-col rounded-[25px] overflow-hidden flex-shrink-0 relative" style="width: 302px; height: 419px; padding: 18px; gap: 24px;">
                        <img src="<?= htmlspecialchars($fullPhoto1Img) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <div class="relative flex flex-col justify-end flex-1 min-h-0 z-10" style="gap: 24px;">
                            <?php if (!empty($artForFullPhoto1['category'])): ?>
                            <span class="text-xs font-medium text-white/90"><?= htmlspecialchars($artForFullPhoto1['category']['name']) ?></span>
                            <?php endif; ?>
                            <h3 class="font-medium text-white line-clamp-3" style="font-size: 20px;"><?= htmlspecialchars($artForFullPhoto1['title']) ?></h3>
                            <p class="text-white/80 text-sm"><?= htmlspecialchars($artForFullPhoto1['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto1['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Wide card photo complète (629x235) - juste en dessous du double -->
                <?php if ($artForFullPhoto2): ?>
                <?php $fullPhoto2Img = !empty($artForFullPhoto2['cover_image_url']) ? $artForFullPhoto2['cover_image_url'] : 'https://picsum.photos/seed/' . rawurlencode($artForFullPhoto2['slug'] ?? '') . '/629/235'; ?>
                <a href="/articles/<?= htmlspecialchars($artForFullPhoto2['slug']) ?>" class="block rounded-[30px] overflow-hidden relative w-full flex-shrink-0" style="height: 235px;">
                    <img src="<?= htmlspecialchars($fullPhoto2Img) ?>" alt="" class="absolute inset-0 w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6 flex flex-col gap-2">
                        <?php if (!empty($artForFullPhoto2['category'])): ?>
                        <span class="text-xs font-medium text-white/90"><?= htmlspecialchars($artForFullPhoto2['category']['name']) ?></span>
                        <?php endif; ?>
                        <h3 class="font-medium text-white line-clamp-2" style="font-size: 20px;"><?= htmlspecialchars($artForFullPhoto2['title']) ?></h3>
                        <p class="text-white/80 text-sm"><?= htmlspecialchars($artForFullPhoto2['published_at'] ?? '') ?> • <?= (int) ($artForFullPhoto2['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bouton Autres actualités - 23px en dessous -->
        <div class="flex justify-center mt-6" style="margin-top: 23px;">
            <a href="/articles" class="inline-flex items-center justify-center rounded-full font-medium text-white gap-2.5 transition" style="width: 226px; height: 48px; background: #004241; padding: 12px 18px;">
                Autres actualités
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>
    <?php endif; ?>
</div>
