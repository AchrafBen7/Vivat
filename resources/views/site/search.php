<?php
$articles = array_values($articles ?? []);
$pagination = $pagination ?? null;
$search_term = $search_term ?? '';
$matched_category = $matched_category ?? null;
$paginationView = $pagination ? $pagination->withQueryString() : null;
$totalArticles = $pagination ? (int) $pagination->total() : count($articles);
$currentPage = $pagination ? (int) $pagination->currentPage() : 1;
$lastPage = $pagination ? (int) $pagination->lastPage() : 1;
$gridArticles = $articles;
$locale = content_locale();

$tagBase = 'inline-flex w-fit items-center justify-center rounded-full px-3 py-1.5 text-[12px] font-medium tracking-[0.02em]';
$tagClass = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';
$glassTagTailwind = 'bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$cardOverlay = 'absolute inset-0 box-border p-[18px] min-h-0 min-w-0';
$glassBox = 'rounded-[21px] flex w-full min-w-0 max-w-full shrink-0 flex-col gap-1.5 box-border p-[18px] bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$articleImageZoom = 'group min-h-[112px] min-w-[128px] overflow-hidden';
$articleImageZoomImg = 'transition-transform duration-[650ms] ease-[cubic-bezier(0.22,1,0.36,1)] will-change-transform group-hover:scale-[1.045]';
$overlayImageSoft = 'absolute inset-0 bg-gradient-to-t from-black/30 to-transparent';
$tagGlassOnImage = $tagClass.' '.$glassTagTailwind.' text-white';
$articleMetaOnImage = 'text-white/80 text-xs';
$tagDark = $tagBase.' bg-[#004241] text-white';

$resolveImage = static function (array $article, int $width, int $height, string $slot): array {
    $categorySlug = $article['category']['slug'] ?? null;
    $articleId = $article['id'] ?? $article['slug'] ?? null;
    $fallback = vivat_category_fallback_image($categorySlug, $width, $height, $articleId, $slot);
    $coverUrl = $article['cover_image_url'] ?? null;
    $src = ! empty($coverUrl) ? $coverUrl : $fallback;

    return [$src, $fallback];
};

$t = $locale === 'nl'
    ? ['title' => 'Artikels zoeken', 'desc' => 'Typ een trefwoord of rubriek om Vivat-content te doorzoeken in dezelfde lay-out als de actualiteitspagina.', 'badge' => 'Zoeken', 'results_in' => 'Resultaten in ', 'results_for' => 'Resultaten voor', 'desc_cat' => 'Hier zie je de artikels die bij deze categorie horen, in hetzelfde redactionele ritme als de actualiteitspagina.', 'desc_search' => 'Hier zie je de artikels die overeenkomen met je zoekopdracht, met dezelfde visuele hiërarchie als de actualiteitspagina.', 'result' => 'resultaat', 'results' => 'resultaten', 'page' => 'Pagina', 'of' => 'van', 'keyword' => 'Zoekwoord', 'empty_badge' => 'Geen resultaat', 'empty_title' => 'Geen artikel gevonden', 'empty_text' => 'Geen enkel artikel komt momenteel overeen met deze zoekopdracht. Probeer een ander trefwoord of een andere rubriek.', 'start_badge' => 'Start een zoekopdracht', 'start_title' => 'Geef een trefwoord in', 'start_text' => 'Voer een onderwerp, categorie of enkele woorden in om de resultaten in deze lay-out te tonen.', 'pagination' => 'Paginering van zoekresultaten', 'previous' => 'Vorige', 'next' => 'Volgende']
    : ['title' => 'Rechercher des articles', 'desc' => 'Saisissez un mot-clé ou une rubrique pour parcourir les contenus Vivat dans la même mise en page que la page actualités.', 'badge' => 'Recherche', 'results_in' => 'Résultats dans ', 'results_for' => 'Résultats pour', 'desc_cat' => 'Voici les articles correspondant à cette catégorie, présentés dans le même rythme éditorial que la page actualités.', 'desc_search' => 'Voici les articles correspondant à votre recherche, avec la même hiérarchie visuelle que la page actualités.', 'result' => 'résultat', 'results' => 'résultats', 'page' => 'Page', 'of' => 'sur', 'keyword' => 'Mot-clé', 'empty_badge' => 'Aucun résultat', 'empty_title' => 'Aucun article trouvé', 'empty_text' => 'Aucun article ne correspond à cette recherche pour le moment. Essayez un autre mot-clé ou une autre rubrique.', 'start_badge' => 'Lancez une recherche', 'start_title' => 'Saisissez un mot-clé', 'start_text' => 'Entrez un sujet, une catégorie ou quelques mots pour afficher les résultats dans ce même layout.', 'pagination' => 'Pagination des résultats de recherche', 'previous' => 'Précédent', 'next' => 'Suivant'];

$heroTitle = $t['title'];
$heroDescription = $t['desc'];
$heroBadge = $t['badge'];

if ($search_term !== '') {
    $heroTitle = $matched_category
        ? $t['results_in'].($matched_category['name'] ?? '')
        : $t['results_for'].' "'.$search_term.'"';
    $heroDescription = $matched_category
        ? $t['desc_cat']
        : $t['desc_search'];
}
?>

<div class="mx-auto flex w-full max-w-[1400px] flex-col px-[18px] pb-16 md:px-8 lg:px-10 xl:px-20">
    <section class="rounded-[36px] bg-[#EBF1EF] px-6 py-8 md:px-10 md:py-10">
        <div>
            <div class="max-w-4xl">
                <span class="inline-flex items-center rounded-full border border-[#004241]/10 bg-white/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-[#004241]/65">
                    <?= htmlspecialchars($heroBadge) ?>
                </span>
                <h1 class="mt-5 max-w-4xl text-[2.5rem] font-semibold leading-[1.02] text-[#004241] sm:text-[3.15rem]">
                    <?= htmlspecialchars($heroTitle) ?>
                </h1>
                <p class="mt-4 max-w-3xl text-base leading-7 text-[#004241]/76 md:text-lg">
                    <?= htmlspecialchars($heroDescription) ?>
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <?php if ($search_term !== '') { ?>
                    <span class="inline-flex items-center rounded-full bg-[#004241] px-4 py-2 text-sm font-medium text-white">
                        <?= $totalArticles ?> <?= htmlspecialchars($totalArticles > 1 ? $t['results'] : $t['result']) ?>
                    </span>
                    <?php } ?>
                    <span class="inline-flex items-center rounded-full bg-white/75 px-4 py-2 text-sm font-medium text-[#004241]">
                        <?= htmlspecialchars($t['page']) ?> <?= $currentPage ?> <?= htmlspecialchars($t['of']) ?> <?= $lastPage ?>
                    </span>
                    <?php if ($search_term !== '' && ! $matched_category) { ?>
                    <span class="inline-flex items-center rounded-full bg-white/75 px-4 py-2 text-sm font-medium text-[#004241]/80">
                        <?= htmlspecialchars($t['keyword']) ?>: <?= htmlspecialchars($search_term) ?>
                    </span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($search_term !== '' && $gridArticles !== []) { ?>
    <?php
    $count = count($gridArticles);
    $gridClass = $count === 1
        ? 'grid gap-6 grid-cols-1 max-w-[520px]'
        : ($count === 2 ? 'grid gap-6 grid-cols-1 md:grid-cols-2' : 'grid gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3');
    ?>
    <section class="mt-8">
        <div class="<?= $gridClass ?>">
            <?php foreach ($gridArticles as $index => $article) { ?>
            <?php
            [$imageSrc, $imageFallback] = $resolveImage($article, 640, 420, 'search-grid-'.$index);
            $variantPattern = [0, 1, 1, 0, 1, 0];
            $variant = $variantPattern[$index % count($variantPattern)];
            ?>

            <?php if ($variant === 0) { ?>
            <a
                href="/articles/<?= htmlspecialchars($article['slug']) ?>"
                class="<?= $articleImageZoom ?> relative min-h-[390px] overflow-hidden rounded-[30px] shadow-[0_18px_40px_rgba(0,66,65,0.08)]"
            >
                <img
                    src="<?= htmlspecialchars($imageSrc) ?>"
                    data-fallback-url="<?= htmlspecialchars($imageFallback) ?>"
                    alt="<?= htmlspecialchars($article['title'] ?? 'Article') ?>"
                    class="absolute inset-0 h-full w-full object-cover <?= $articleImageZoomImg ?>"
                    loading="lazy"
                >
                <div class="<?= $overlayImageSoft ?>"></div>
                <div class="<?= $cardOverlay ?> flex items-end">
                    <div class="<?= $glassBox ?> w-full">
                        <?php if (! empty($article['category'])) { ?>
                        <span class="<?= $tagGlassOnImage ?>"><?= htmlspecialchars($article['category']['name']) ?></span>
                        <?php } ?>
                        <h3 class="font-semibold text-white line-clamp-5 text-lg">
                            <?= htmlspecialchars($article['title'] ?? '') ?>
                        </h3>
                        <p class="<?= $articleMetaOnImage ?>">
                            <?= htmlspecialchars($article['published_at'] ?? '') ?> • <?= (int) ($article['reading_time'] ?? 0) ?> min
                        </p>
                    </div>
                </div>
            </a>
            <?php } else { ?>
            <a
                href="/articles/<?= htmlspecialchars($article['slug']) ?>"
                class="group flex min-h-[390px] flex-col overflow-hidden rounded-[30px] bg-white p-6 shadow-[0_18px_48px_rgba(0,66,65,0.08)] transition-transform duration-300 hover:-translate-y-1"
            >
                <div class="flex min-h-0 flex-1 flex-col">
                    <?php if (! empty($article['category'])) { ?>
                    <span class="<?= $tagDark ?>"><?= htmlspecialchars($article['category']['name']) ?></span>
                    <?php } ?>
                    <h3 class="mt-4 text-[1.5rem] font-semibold leading-[1.16] text-[#004241]">
                        <?= htmlspecialchars($article['title'] ?? '') ?>
                    </h3>
                    <?php if (! empty($article['excerpt'])) { ?>
                    <p class="mt-3 line-clamp-3 text-sm leading-6 text-[#004241]/72">
                        <?= htmlspecialchars($article['excerpt']) ?>
                    </p>
                    <?php } ?>
                    <p class="mt-5 text-sm text-[#004241]/68">
                        <?= htmlspecialchars($article['published_at'] ?? '') ?> • <?= (int) ($article['reading_time'] ?? 0) ?> min
                    </p>
                </div>

                <div class="relative mt-6 h-[190px] overflow-hidden rounded-[24px] bg-[#F6FAF8]">
                    <img
                        src="<?= htmlspecialchars($imageSrc) ?>"
                        data-fallback-url="<?= htmlspecialchars($imageFallback) ?>"
                        alt="<?= htmlspecialchars($article['title'] ?? 'Article') ?>"
                        class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]"
                        loading="lazy"
                    >
                </div>
            </a>
            <?php } ?>
            <?php } ?>
        </div>
    </section>
    <?php } elseif ($search_term !== '') { ?>
    <section class="mt-8 rounded-[34px] border border-[#D6E1DD] bg-[linear-gradient(135deg,#F8FBFA_0%,#EEF5F2_100%)] px-6 py-12 text-center md:px-10">
        <span class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-medium text-[#004241] shadow-sm">
            <?= htmlspecialchars($t['empty_badge']) ?>
        </span>
        <h2 class="mt-5 text-[2rem] font-semibold text-[#004241]"><?= htmlspecialchars($t['empty_title']) ?></h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-[#004241]/70 md:text-base">
            <?= htmlspecialchars($t['empty_text']) ?>
        </p>
    </section>
    <?php } else { ?>
    <section class="mt-8 rounded-[34px] border border-[#D6E1DD] bg-[linear-gradient(135deg,#F8FBFA_0%,#EEF5F2_100%)] px-6 py-12 text-center md:px-10">
        <span class="inline-flex items-center rounded-full bg-white px-4 py-2 text-sm font-medium text-[#004241] shadow-sm">
            <?= htmlspecialchars($t['start_badge']) ?>
        </span>
        <h2 class="mt-5 text-[2rem] font-semibold text-[#004241]"><?= htmlspecialchars($t['start_title']) ?></h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-7 text-[#004241]/70 md:text-base">
            <?= htmlspecialchars($t['start_text']) ?>
        </p>
    </section>
    <?php } ?>

    <?php if ($paginationView && $paginationView->hasPages()) { ?>
    <nav class="mt-10 flex flex-wrap items-center justify-center gap-3" aria-label="<?= htmlspecialchars($t['pagination']) ?>">
        <?php if ($paginationView->onFirstPage()) { ?>
        <span class="inline-flex h-11 items-center justify-center rounded-full bg-[#EBF1EF] px-5 text-sm font-medium text-[#004241]/40">
            <?= htmlspecialchars($t['previous']) ?>
        </span>
        <?php } else { ?>
        <a
            href="<?= htmlspecialchars($paginationView->previousPageUrl()) ?>"
            class="inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-5 text-sm font-medium text-white transition hover:opacity-90"
        >
            <?= htmlspecialchars($t['previous']) ?>
        </a>
        <?php } ?>

        <span class="text-sm font-medium text-[#004241]/80">
            <?= htmlspecialchars($t['page']) ?> <?= $paginationView->currentPage() ?> <?= htmlspecialchars($t['of']) ?> <?= $paginationView->lastPage() ?>
        </span>

        <?php if ($paginationView->hasMorePages()) { ?>
        <a
            href="<?= htmlspecialchars($paginationView->nextPageUrl()) ?>"
            class="inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-5 text-sm font-medium text-white transition hover:opacity-90"
        >
            <?= htmlspecialchars($t['next']) ?>
        </a>
        <?php } else { ?>
        <span class="inline-flex h-11 items-center justify-center rounded-full bg-[#EBF1EF] px-5 text-sm font-medium text-[#004241]/40">
            <?= htmlspecialchars($t['next']) ?>
        </span>
        <?php } ?>
    </nav>
    <?php } ?>
</div>
