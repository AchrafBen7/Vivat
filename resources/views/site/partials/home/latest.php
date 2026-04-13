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
$articleType = static function (array $article): string {
    $type = (string) ($article['article_type'] ?? 'standard');

    return $type !== '' ? $type : 'standard';
};
$allLatestPool = array_values($restArticles);
$isImageArticle = static fn (array $article): bool => in_array($articleType($article), ['hot_news', 'long_form'], true)
    || ! empty($article['cover_image_url']);
$isEditorialArticle = static fn (array $article): bool => $articleType($article) === 'standard';
$isHotNewsArticle = static fn (array $article): bool => $articleType($article) === 'hot_news';
$isLongFormArticle = static fn (array $article): bool => $articleType($article) === 'long_form';
$latestColorCardContent = 'flex flex-col justify-end gap-3';
$latestCardContentMotion = 'flex flex-col gap-3 min-h-0 overflow-hidden transition-transform duration-[900ms] ease-[cubic-bezier(0.16,1,0.3,1)] md:group-hover:-translate-y-1';
$latestColorCardTitle = 'line-clamp-3 text-xl font-semibold leading-tight';
$latestColorCardTitleLarge = 'line-clamp-3 text-2xl font-semibold leading-tight max-sm:text-xl';
$latestColorCardExcerpt = 'line-clamp-2 text-sm';
$latestCardExcerptRevealOnLight = 'line-clamp-2 text-sm max-md:max-h-[3rem] max-md:opacity-100 md:max-h-0 md:overflow-hidden md:translate-y-2 md:opacity-0 md:transition-[max-height,opacity,transform] md:duration-[900ms] md:ease-[cubic-bezier(0.16,1,0.3,1)] md:group-hover:max-h-[3rem] md:group-hover:translate-y-0 md:group-hover:opacity-100';
$latestCardExcerptRevealOnDark = 'line-clamp-2 text-sm max-md:max-h-[3rem] max-md:opacity-100 md:max-h-0 md:overflow-hidden md:translate-y-2 md:opacity-0 md:transition-[max-height,opacity,transform] md:duration-[900ms] md:ease-[cubic-bezier(0.16,1,0.3,1)] md:group-hover:max-h-[3rem] md:group-hover:translate-y-0 md:group-hover:opacity-100';
$latestHeroTitleMedium = 'line-clamp-3 text-xl font-semibold leading-tight';
$latestHeroTitleSmall = 'line-clamp-3 text-lg font-semibold leading-tight';
$latestColorCardMeta = 'text-xs';
$pickLatestArticle = static function (array &$pool, callable ...$predicates): ?array {
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
$latestImageData = static function (array $article, int $width, int $height, string $slot): array {
    $categorySlug = $article['category']['slug'] ?? null;
    $articleId = $article['id'] ?? $article['slug'] ?? null;
    $fallback = vivat_category_fallback_image($categorySlug, $width, $height, $articleId, $slot);
    $image = ! empty($article['cover_image_url']) ? $article['cover_image_url'] : $fallback;

    return [$image, $fallback];
};

$topFeatureEditorialArt = $pickLatestArticle($allLatestPool, $isEditorialArticle, $isLongFormArticle, $isHotNewsArticle);
$topVisualArt = $pickLatestArticle($allLatestPool, $isImageArticle, $isHotNewsArticle);
$leftEditorialArt = $pickLatestArticle($allLatestPool, $isEditorialArticle, $isLongFormArticle);
$centerVisualArt = $pickLatestArticle($allLatestPool, $isLongFormArticle, $isImageArticle);
$rightEditorialArt = $pickLatestArticle($allLatestPool, $isEditorialArticle, $isHotNewsArticle, $isLongFormArticle);
$bottomLeftVisualArt = $pickLatestArticle($allLatestPool, $isImageArticle, $isLongFormArticle);
$bottomCenterEditorialArt = $pickLatestArticle($allLatestPool, $isEditorialArticle, $isLongFormArticle);
$bottomRightVisualArt = $pickLatestArticle($allLatestPool, $isImageArticle, $isHotNewsArticle);
$latestTabletCards = array_values(array_filter([
    ['kind' => 'image', 'article' => $topVisualArt, 'slot' => 'latest-tablet-top-visual', 'width' => 520, 'height' => 320],
    ['kind' => 'soft', 'article' => $leftEditorialArt],
    ['kind' => 'image', 'article' => $centerVisualArt, 'slot' => 'latest-tablet-center-visual', 'width' => 620, 'height' => 320],
    ['kind' => 'green', 'article' => $rightEditorialArt],
    ['kind' => 'image', 'article' => $bottomLeftVisualArt, 'slot' => 'latest-tablet-bottom-left-visual', 'width' => 420, 'height' => 320],
    ['kind' => 'yellow', 'article' => $bottomCenterEditorialArt],
    ['kind' => 'image', 'article' => $bottomRightVisualArt, 'slot' => 'latest-tablet-bottom-right-visual', 'width' => 420, 'height' => 320],
], static fn (array $card): bool => ! empty($card['article'])));
$latestTabletCards = array_slice($latestTabletCards, 0, 4);
?>

    <?php if (count($restArticles) > 0) { ?>
    <section class="mt-16 hidden w-full min-w-0 grid-cols-8 gap-6 md:grid lg:hidden">
        <h2 class="mb-0 col-span-8 text-[32px] font-medium text-[#004241]">Dernières actualités</h2>
        <?php if ($topFeatureEditorialArt) { ?>
        <a href="/articles/<?= htmlspecialchars($topFeatureEditorialArt['slug']) ?>" class="group relative col-span-8 flex h-[300px] w-full flex-col justify-end overflow-hidden rounded-[32px] px-9 py-8 <?= $cardYellowSurface ?>">
            <span class="<?= $cardArrowOnYellow ?>">
                <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </span>
            <div class="<?= $latestCardContentMotion ?> max-w-[760px]">
                <?php if (! empty($topFeatureEditorialArt['category'])) { ?>
                <span class="<?= $tagClass ?> <?= $tagOnYellowCard ?>"><?= htmlspecialchars($topFeatureEditorialArt['category']['name']) ?></span>
                <?php } ?>
                <h3 class="<?= $latestColorCardTitleLarge ?> text-[#004241]"><?= htmlspecialchars($topFeatureEditorialArt['title'] ?? '') ?></h3>
                <?php if (! empty($topFeatureEditorialArt['excerpt'])) { ?>
                <p class="<?= $latestCardExcerptRevealOnLight ?> text-[#004241]/75"><?= htmlspecialchars($topFeatureEditorialArt['excerpt']) ?></p>
                <?php } ?>
                <p class="<?= $latestColorCardMeta ?> text-[#004241]/70"><?= htmlspecialchars($topFeatureEditorialArt['published_at'] ?? '') ?> • <?= (int) ($topFeatureEditorialArt['reading_time'] ?? 0) ?> min</p>
                        </div>
        </a>
        <?php } ?>
        <?php foreach ($latestTabletCards as $tabletCard) { ?>
        <?php
        $tabletArticle = $tabletCard['article'];
            $tabletCardKind = $tabletCard['kind'];
            $isImageTabletCard = $tabletCardKind === 'image';
            $tabletCardSurface = match ($tabletCardKind) {
                'soft' => $cardSoftSurface,
                'green' => $cardGreenSurface,
                default => $cardYellowSurface,
            };
            $tabletCardArrow = $tabletCardKind === 'green' ? $cardArrowOnGreen : $cardArrowOnYellow;
            $tabletCardTag = match ($tabletCardKind) {
                'soft' => $tagOnSoftCard,
                'green' => $tagOnGreenCard,
                default => $tagOnYellowCard,
            };
            $tabletCardTitleText = $tabletCardKind === 'green' ? 'text-white' : 'text-[#004241]';
            $tabletCardExcerptText = $tabletCardKind === 'green' ? 'text-white/75' : 'text-[#004241]/72';
            $tabletCardMetaText = $tabletCardKind === 'green' ? 'text-white/70' : 'text-[#004241]/70';
            ?>
        <?php if ($isImageTabletCard) { ?>
        <?php [$tabletCardImage, $tabletCardFallback] = $latestImageData($tabletArticle, $tabletCard['width'], $tabletCard['height'], $tabletCard['slot']); ?>
        <a href="/articles/<?= htmlspecialchars($tabletArticle['slug']) ?>" class="<?= $articleImageZoom ?> relative col-span-4 block h-[320px] w-full overflow-hidden rounded-[32px]">
            <img src="<?= htmlspecialchars($tabletCardImage) ?>" data-fallback-url="<?= htmlspecialchars($tabletCardFallback) ?>" alt="<?= htmlspecialchars($tabletArticle['title'] ?? 'Article') ?>" class="absolute inset-0 h-full w-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
            <div class="<?= $overlayImagePhoto ?>"></div>
            <div class="absolute inset-x-0 bottom-0 z-10 p-6">
                <div class="<?= $glassBox ?> min-h-[146px] justify-end">
                    <?php if (! empty($tabletArticle['category'])) { ?>
                    <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($tabletArticle['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="<?= $latestHeroTitleMedium ?> text-white"><?= htmlspecialchars($tabletArticle['title'] ?? '') ?></h3>
                    <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($tabletArticle['published_at'] ?? '') ?> • <?= (int) ($tabletArticle['reading_time'] ?? 0) ?> min</p>
                        </div>
            </div>
        </a>
        <?php } else { ?>
        <a href="/articles/<?= htmlspecialchars($tabletArticle['slug']) ?>" class="group relative col-span-4 flex h-[320px] w-full flex-col justify-end overflow-hidden rounded-[32px] px-8 py-8 <?= $tabletCardSurface ?>">
            <span class="<?= $tabletCardArrow ?>">
                <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </span>
            <div class="<?= $latestCardContentMotion ?>">
                <?php if (! empty($tabletArticle['category'])) { ?>
                <span class="<?= $tagClass ?> <?= $tabletCardTag ?>"><?= htmlspecialchars($tabletArticle['category']['name']) ?></span>
                <?php } ?>
                <h3 class="<?= $latestColorCardTitleLarge ?> line-clamp-3 <?= $tabletCardTitleText ?>"><?= htmlspecialchars($tabletArticle['title'] ?? '') ?></h3>
                <?php if (! empty($tabletArticle['excerpt'])) { ?>
                <p class="<?= $tabletCardKind === 'green' ? $latestCardExcerptRevealOnDark : $latestCardExcerptRevealOnLight ?> <?= $tabletCardExcerptText ?>"><?= htmlspecialchars($tabletArticle['excerpt']) ?></p>
                <?php } ?>
                <p class="<?= $latestColorCardMeta ?> <?= $tabletCardMetaText ?>"><?= htmlspecialchars($tabletArticle['published_at'] ?? '') ?> • <?= (int) ($tabletArticle['reading_time'] ?? 0) ?> min</p>
                </div>
        </a>
        <?php } ?>
        <?php } ?>
        <div class="col-span-8 flex justify-center pt-2">
            <a href="/articles" class="inline-flex h-12 items-center justify-center rounded-full bg-[#004241] px-8 text-base font-semibold text-white no-underline transition-colors duration-200 hover:bg-[#003130]">
                Plus d'articles
            </a>
        </div>
    </section>

    <section class="mt-12 grid w-full min-w-0 grid-cols-1 gap-[18px] md:hidden lg:mt-16 lg:grid lg:grid-cols-12 lg:gap-6">
        <h2 class="mb-0 text-[32px] font-medium text-[#004241] md:col-span-8 lg:col-span-12">Dernières actualités</h2>
        <div class="grid min-w-0 w-full grid-cols-1 gap-[18px] lg:col-span-12 lg:grid-cols-12 lg:gap-6">
            <?php if ($topFeatureEditorialArt) { ?>
            <a href="/articles/<?= htmlspecialchars($topFeatureEditorialArt['slug']) ?>" class="group relative flex h-[300px] w-full flex-col justify-end overflow-hidden rounded-[32px] px-9 py-8 md:col-span-8 lg:col-span-7 lg:h-[340px] <?= $cardYellowSurface ?>">
                <span class="<?= $cardArrowOnYellow ?>">
                    <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
                <div class="<?= $latestCardContentMotion ?> max-w-[760px]">
                    <?php if (! empty($topFeatureEditorialArt['category'])) { ?>
                    <span class="<?= $tagClass ?> <?= $tagOnYellowCard ?>"><?= htmlspecialchars($topFeatureEditorialArt['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="<?= $latestColorCardTitleLarge ?> text-[#004241]"><?= htmlspecialchars($topFeatureEditorialArt['title'] ?? '') ?></h3>
                    <?php if (! empty($topFeatureEditorialArt['excerpt'])) { ?>
                    <p class="<?= $latestCardExcerptRevealOnLight ?> text-[#004241]/75"><?= htmlspecialchars($topFeatureEditorialArt['excerpt']) ?></p>
                    <?php } ?>
                    <p class="<?= $latestColorCardMeta ?> text-[#004241]/70"><?= htmlspecialchars($topFeatureEditorialArt['published_at'] ?? '') ?> • <?= (int) ($topFeatureEditorialArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                </a>
            <?php } ?>

            <?php if ($topVisualArt) { ?>
            <?php [$topVisualImg, $topVisualFallback] = $latestImageData($topVisualArt, 520, 340, 'latest-top-visual'); ?>
            <a href="/articles/<?= htmlspecialchars($topVisualArt['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[280px] w-full overflow-hidden rounded-[32px] md:col-span-4 lg:col-span-5 lg:h-[340px]">
                <img src="<?= htmlspecialchars($topVisualImg) ?>" data-fallback-url="<?= htmlspecialchars($topVisualFallback) ?>" alt="<?= htmlspecialchars($topVisualArt['title'] ?? 'Article') ?>" class="absolute inset-0 h-full w-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                <div class="<?= $overlayImagePhoto ?>"></div>
                <div class="absolute inset-x-0 bottom-0 z-10 p-5 md:p-6">
                    <div class="<?= $glassBox ?> min-h-[146px] justify-end">
                        <?php if (! empty($topVisualArt['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($topVisualArt['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="<?= $latestHeroTitleMedium ?> text-white"><?= htmlspecialchars($topVisualArt['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($topVisualArt['published_at'] ?? '') ?> • <?= (int) ($topVisualArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                        </div>
            </a>
            <?php } ?>

            <?php if ($leftEditorialArt) { ?>
            <a href="/articles/<?= htmlspecialchars($leftEditorialArt['slug']) ?>" class="group relative flex h-[280px] w-full flex-col justify-end overflow-hidden rounded-[32px] px-8 py-8 md:col-span-4 lg:col-span-3 lg:h-[360px] <?= $cardSoftSurface ?>">
                <span class="<?= $cardArrowOnYellow ?>">
                    <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
                <div class="<?= $latestCardContentMotion ?>">
                    <?php if (! empty($leftEditorialArt['category'])) { ?>
                    <span class="<?= $tagClass ?> <?= $tagOnSoftCard ?>"><?= htmlspecialchars($leftEditorialArt['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="<?= $latestColorCardTitleLarge ?> line-clamp-3 text-[#004241]"><?= htmlspecialchars($leftEditorialArt['title'] ?? '') ?></h3>
                    <?php if (! empty($leftEditorialArt['excerpt'])) { ?>
                    <p class="<?= $latestCardExcerptRevealOnLight ?> text-[#004241]/72"><?= htmlspecialchars($leftEditorialArt['excerpt']) ?></p>
                    <?php } ?>
                    <p class="<?= $latestColorCardMeta ?> text-[#004241]/70"><?= htmlspecialchars($leftEditorialArt['published_at'] ?? '') ?> • <?= (int) ($leftEditorialArt['reading_time'] ?? 0) ?> min</p>
                        </div>
            </a>
            <?php } ?>

            <?php if ($centerVisualArt) { ?>
            <?php [$centerVisualImg, $centerVisualFallback] = $latestImageData($centerVisualArt, 620, 360, 'latest-center-visual'); ?>
            <a href="/articles/<?= htmlspecialchars($centerVisualArt['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[280px] w-full overflow-hidden rounded-[32px] md:col-span-4 lg:col-span-5 lg:h-[360px]">
                <img src="<?= htmlspecialchars($centerVisualImg) ?>" data-fallback-url="<?= htmlspecialchars($centerVisualFallback) ?>" alt="<?= htmlspecialchars($centerVisualArt['title'] ?? 'Article') ?>" class="absolute inset-0 h-full w-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/15 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 z-10 p-5 md:p-6">
                    <div class="<?= $glassBox ?> min-h-[146px] justify-end">
                        <?php if (! empty($centerVisualArt['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($centerVisualArt['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="<?= $latestHeroTitleMedium ?> text-white"><?= htmlspecialchars($centerVisualArt['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($centerVisualArt['published_at'] ?? '') ?> • <?= (int) ($centerVisualArt['reading_time'] ?? 0) ?> min</p>
                </div>
            </div>
            </a>
            <?php } ?>

            <?php if ($rightEditorialArt) { ?>
            <a href="/articles/<?= htmlspecialchars($rightEditorialArt['slug']) ?>" class="group relative flex h-[280px] w-full flex-col justify-end overflow-hidden rounded-[32px] px-8 py-8 md:col-span-8 lg:col-span-4 lg:h-[360px] <?= $cardGreenSurface ?>">
                <span class="<?= $cardArrowOnGreen ?>">
                    <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
                <div class="<?= $latestCardContentMotion ?>">
                    <?php if (! empty($rightEditorialArt['category'])) { ?>
                    <span class="<?= $tagClass ?> <?= $tagOnGreenCard ?>"><?= htmlspecialchars($rightEditorialArt['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="<?= $latestColorCardTitleLarge ?> line-clamp-3 text-white"><?= htmlspecialchars($rightEditorialArt['title'] ?? '') ?></h3>
                    <?php if (! empty($rightEditorialArt['excerpt'])) { ?>
                    <p class="<?= $latestCardExcerptRevealOnDark ?> text-white/75"><?= htmlspecialchars($rightEditorialArt['excerpt']) ?></p>
                    <?php } ?>
                    <p class="<?= $latestColorCardMeta ?> text-white/70"><?= htmlspecialchars($rightEditorialArt['published_at'] ?? '') ?> • <?= (int) ($rightEditorialArt['reading_time'] ?? 0) ?> min</p>
                </div>
            </a>
            <?php } ?>

            <?php if ($bottomLeftVisualArt) { ?>
            <?php [$bottomLeftVisualImg, $bottomLeftVisualFallback] = $latestImageData($bottomLeftVisualArt, 420, 280, 'latest-bottom-left-visual'); ?>
            <a href="/articles/<?= htmlspecialchars($bottomLeftVisualArt['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[280px] w-full overflow-hidden rounded-[32px] md:col-span-4 lg:col-span-4 lg:h-[290px]">
                <img src="<?= htmlspecialchars($bottomLeftVisualImg) ?>" data-fallback-url="<?= htmlspecialchars($bottomLeftVisualFallback) ?>" alt="<?= htmlspecialchars($bottomLeftVisualArt['title'] ?? 'Article') ?>" class="absolute inset-0 h-full w-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 z-10 p-5 md:p-6">
                    <div class="<?= $glassBox ?> min-h-[132px] justify-end">
                        <?php if (! empty($bottomLeftVisualArt['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($bottomLeftVisualArt['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="<?= $latestHeroTitleSmall ?> text-white"><?= htmlspecialchars($bottomLeftVisualArt['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($bottomLeftVisualArt['published_at'] ?? '') ?> • <?= (int) ($bottomLeftVisualArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                        </div>
            </a>
            <?php } ?>

            <?php if ($bottomCenterEditorialArt) { ?>
            <a href="/articles/<?= htmlspecialchars($bottomCenterEditorialArt['slug']) ?>" class="group relative flex h-[280px] w-full flex-col justify-end overflow-hidden rounded-[32px] px-8 py-7 md:col-span-4 lg:col-span-4 lg:h-[290px] <?= $cardYellowSurface ?>">
                <span class="<?= $cardArrowOnYellow ?>">
                    <svg class="h-[26px] w-[26px] flex-shrink-0 -rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </span>
                <div class="<?= $latestCardContentMotion ?>">
                    <?php if (! empty($bottomCenterEditorialArt['category'])) { ?>
                    <span class="<?= $tagClass ?> <?= $tagOnYellowCard ?>"><?= htmlspecialchars($bottomCenterEditorialArt['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="<?= $latestColorCardTitle ?> text-[#004241]"><?= htmlspecialchars($bottomCenterEditorialArt['title'] ?? '') ?></h3>
                    <?php if (! empty($bottomCenterEditorialArt['excerpt'])) { ?>
                    <p class="<?= $latestCardExcerptRevealOnLight ?> text-[#004241]/72"><?= htmlspecialchars($bottomCenterEditorialArt['excerpt']) ?></p>
                    <?php } ?>
                    <p class="<?= $latestColorCardMeta ?> text-[#004241]/70"><?= htmlspecialchars($bottomCenterEditorialArt['published_at'] ?? '') ?> • <?= (int) ($bottomCenterEditorialArt['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </a>
            <?php } ?>

            <?php if ($bottomRightVisualArt) { ?>
            <?php [$bottomRightVisualImg, $bottomRightVisualFallback] = $latestImageData($bottomRightVisualArt, 420, 280, 'latest-bottom-right-visual'); ?>
            <a href="/articles/<?= htmlspecialchars($bottomRightVisualArt['slug']) ?>" class="<?= $articleImageZoom ?> relative block h-[280px] w-full overflow-hidden rounded-[32px] md:col-span-8 lg:col-span-4 lg:h-[290px]">
                <img src="<?= htmlspecialchars($bottomRightVisualImg) ?>" data-fallback-url="<?= htmlspecialchars($bottomRightVisualFallback) ?>" alt="<?= htmlspecialchars($bottomRightVisualArt['title'] ?? 'Article') ?>" class="absolute inset-0 h-full w-full object-cover <?= $articleImageZoomImg ?>" loading="lazy">
                <div class="<?= $overlayImagePhoto ?>"></div>
                <div class="absolute inset-x-0 bottom-0 z-10 p-5 md:p-6">
                    <div class="<?= $glassBox ?> min-h-[132px] justify-end">
                        <?php if (! empty($bottomRightVisualArt['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($bottomRightVisualArt['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="<?= $latestHeroTitleSmall ?> text-white"><?= htmlspecialchars($bottomRightVisualArt['title'] ?? '') ?></h3>
                        <p class="<?= $articleMetaOnImage ?>"><?= htmlspecialchars($bottomRightVisualArt['published_at'] ?? '') ?> • <?= (int) ($bottomRightVisualArt['reading_time'] ?? 0) ?> min</p>
                    </div>
                    </div>
                </a>
            <?php } ?>
        </div>

        <!-- Bouton Autres actualités -->
        <div class="flex justify-center md:col-span-8 lg:col-span-12">
            <a href="/articles" class="inline-flex items-center justify-center rounded-full font-medium text-white gap-2.5 h-12 w-[226px] bg-[#004241] px-[18px] transition-colors duration-200 hover:bg-[#003130]">
                Autres actualités
                <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </section>
    <?php } ?>

</div>

<script>
(function () {
    function init() {
    if (typeof gsap === 'undefined') return;

    // — Hero : reveal vertical haut → bas, sans voile blanc
    // autoAlpha utilise visibility:hidden (pas opacity:0) = pas de blending blanc
    var heroGrids = document.querySelectorAll('[data-home-hero]');
    heroGrids.forEach(function (grid) {
        if (grid.offsetParent === null) return;
        var cards = Array.from(grid.querySelectorAll(':scope > a, :scope > div'));
        if (cards.length === 0) return;
        cards.sort(function (a, b) {
            return a.getBoundingClientRect().top - b.getBoundingClientRect().top;
        });
        gsap.set(cards, { autoAlpha: 0, y: 20 });
        gsap.to(cards, {
            autoAlpha: 1,
            y: 0,
            duration: 1.8,
            ease: 'power3.out',
            stagger: 0.2,
            delay: 0.15,
        });
    });

    // — Sections en dessous : uniquement "Dernières actualités"
    // Les sections rubriques (carousel + vidéo) sont exclues car autoAlpha cause un accroche
    if (typeof ScrollTrigger === 'undefined') return;

    var sections = document.querySelectorAll('section:not(#categories-section):not(#categories-section-tablet)');
    sections.forEach(function (el) {
        gsap.set(el, { autoAlpha: 0, y: 28 });
        gsap.to(el, {
            autoAlpha: 1,
            y: 0,
            duration: 2.2,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: el,
                start: 'top 88%',
                toggleActions: 'play none none none',
            },
        });
    });
    }
    // Démarre immédiatement si page déjà chargée (AJAX swap), sinon attend window.load
    if (document.readyState === 'complete') {
        init();
    } else {
        window.addEventListener('load', init);
    }
})();
</script>
