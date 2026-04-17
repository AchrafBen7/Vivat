<?php
$article = $article ?? [];
$t = fn (string $key, ?string $fallback = null) => __($key) !== $key ? __($key) : ($fallback ?? $key);
$title = $article['title'] ?? 'Article';
$slug = $article['slug'] ?? '';
$published_at = $article['published_at'] ?? null;
$published_at_display = $article['published_at_display'] ?? null;
$published_at_iso = $article['published_at_iso'] ?? null;
$reading_time = $article['reading_time'] ?? null;
$category = $article['category'] ?? null;
$cover_image_url = $article['cover_image_url'] ?? null;
$has_generated_cover = (bool) ($article['has_generated_cover'] ?? false);
$cover_status_label = $article['cover_status_label'] ?? null;
$content = $article['content'] ?? '';
$excerpt = $article['excerpt'] ?? '';
$relatedCategoryName = $category['name'] ?? $t('site.featured', 'À la une');
$relatedCategorySlug = $category['slug'] ?? null;
$relatedBaseId = $article['id'] ?? $slug ?? 'article';
$catSlug = ($category ?? [])['slug'] ?? null;
$coverFallback = vivat_category_fallback_image($catSlug, 1282, 444, $relatedBaseId, 'cover');
$coverSrc = ! empty($cover_image_url) ? $cover_image_url : $coverFallback;
$backHref = $relatedCategorySlug ? '/categories/'.htmlspecialchars($relatedCategorySlug) : '/';
$relatedItems = ! empty($related_articles) ? array_map(fn (array $a) => [
    'title'        => $a['title'],
    'slug'         => $a['slug'],
    'date'         => $a['published_at_display'] ?? '',
    'reading_time' => $a['reading_time'] ?? 4,
    'category'     => $a['category'] ?? $relatedCategoryName,
    'image'        => $a['image'] ?? vivat_category_fallback_image($relatedCategorySlug, 760, 520, (string) $relatedBaseId, 'also-1'),
    'fallback'     => $a['fallback'] ?? $a['image'] ?? vivat_category_fallback_image($relatedCategorySlug, 760, 520, (string) $relatedBaseId, 'also-1b'),
], $related_articles) : [];
$showRelatedSection = count($relatedItems) > 0;
$useRelatedCarousel = count($relatedItems) > 2;
$alsoCarouselItems = [];
foreach ($relatedItems as $index => $item) {
    $alsoCarouselItems[] = ['type' => 'article'] + $item;

    if (
        $useRelatedCarousel
        && ($index + 1) % 3 === 0
        && $index < count($relatedItems) - 1
    ) {
        $alsoCarouselItems[] = [
            'type' => 'ad',
            'label' => $t('site.advertising', 'Publicité'),
        ];
    }
}
$tagClass = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';
$glassTagTailwind = 'bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$tagGlassOnImage = $tagClass.' '.$glassTagTailwind.' text-white';
$tagCarousel = $tagClass.' bg-white/20 backdrop-blur-[12px] border border-white/25 text-white';
$metaLine = trim(implode(' • ', array_filter([
    $published_at_display,
    $reading_time ? (int) $reading_time.' min' : null,
])));
$shareUrl = url('/articles/'.$slug);
$shareTitle = $title;
$isPreview = (bool) ($article['is_preview'] ?? false);
$previewContext = (string) ($article['preview_context'] ?? 'dashboard');
$previewBackHref = $article['preview_back_href'] ?? '/contributor/dashboard';
$previewBackLabel = $article['preview_back_label']
    ?? ($previewContext === 'admin' ? "Retour à l'administration" : 'Retour au tableau de bord');
$previewModeLabel = $previewContext === 'admin' ? 'Aperçu admin' : $t('site.preview_mode', 'Mode aperçu');
$previewHeading = $previewContext === 'admin'
    ? "Aperçu admin de l'article"
    : $t('site.article_preview_heading', 'Aperçu rédacteur de votre article');
$previewNotice = $previewContext === 'admin'
    ? "Cet aperçu interne reprend le rendu article avec un encadrement de prévisualisation avant publication ou vérification."
    : $t('site.article_preview_notice', "Ceci est un aperçu de votre article. Il n'est pas encore affiché publiquement comme version finale.");
$previewStatusLabel = $previewContext === 'admin' ? 'Prévisualisation interne' : $t('site.preview_not_public', 'Non publié');
$previewReaderLabel = $previewContext === 'admin' ? 'Aperçu admin avant publication' : $t('site.preview_reader_simulation', 'Simulation de lecture');
$previewReaderPill = $previewContext === 'admin' ? 'Vue interne rédaction' : $t('site.preview_reader_only', 'Aperçu interne');
$articleCanvasClass = $isPreview
    ? 'rounded-[32px] border border-dashed border-[#004241]/18 bg-[#F7FAF9] px-5 py-6 shadow-[0_18px_40px_rgba(0,66,65,0.05)] sm:px-7 sm:py-7'
    : '';

if (! function_exists('vivat_normalize_article_html')) {
    /**
     * Répare un HTML d'article potentiellement mal fermé pour éviter qu'il casse le layout suivant.
     */
    function vivat_normalize_article_html(string $html): string
    {
        $trimmed = trim($html);

        if ($trimmed === '' || ! preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $trimmed) || ! class_exists(\DOMDocument::class)) {
            return $html;
        }

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $wrapped = '<!DOCTYPE html><html><body>'.$trimmed.'</body></html>';
        $flags = \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD | \LIBXML_NOERROR | \LIBXML_NOWARNING;

        if (! $dom->loadHTML('<?xml encoding="utf-8" ?>'.$wrapped, $flags)) {
            libxml_clear_errors();

            return $html;
        }

        foreach (['script', 'style', 'iframe'] as $tag) {
            while (($nodes = $dom->getElementsByTagName($tag))->length > 0) {
                $nodes->item(0)?->parentNode?->removeChild($nodes->item(0));
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            libxml_clear_errors();

            return $html;
        }

        $normalized = '';
        foreach ($body->childNodes as $child) {
            $normalized .= $dom->saveHTML($child);
        }

        libxml_clear_errors();

        return $normalized !== '' ? $normalized : $html;
    }
}

// Si le contenu est du texte brut saisi par un rédacteur, convertir les lignes vides en paragraphes.
// On conserve le HTML existant pour ne pas casser les anciens articles déjà formatés.
if (is_string($content) && trim($content) !== '' && ! preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $content)) {
    $paragraphs = preg_split("/(?:\r\n|\r|\n)\s*(?:\r\n|\r|\n)+/", trim($content)) ?: [];
    $content = implode('', array_map(static function (string $paragraph): string {
        $paragraph = trim($paragraph);

        if ($paragraph === '') {
            return '';
        }

        return '<p>'.nl2br(htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8')).'</p>';
    }, $paragraphs));
}

if (is_string($content) && trim($content) !== '') {
    $content = vivat_normalize_article_html($content);
    $content = preg_replace('/<div class="article-sources">.*?<\/div>\s*$/is', '', $content) ?? $content;
}

// Insérer la pub au milieu du contenu (après le paragraphe du milieu)
$adMidContent = '<div class="my-6 flex items-center justify-center"><div class="flex h-[250px] w-full max-w-[970px] items-center justify-center overflow-hidden">' . render_php_view('site.partials.adsense_slot', ['slotKey' => 'article_mid_banner_970x250']) . '</div></div>';
if (! $isPreview) {
    $paraCount = preg_match_all('/<\/p>\s*/i', $content);
    $insertAfterPara = $paraCount >= 2 ? (int) floor($paraCount / 2) : 1;
    $count = 0;
    $content = preg_replace_callback('/(<\/p>\s*)/i', function ($m) use ($adMidContent, $insertAfterPara, &$count) {
        $count++;

        return $count === $insertAfterPara ? $m[1].$adMidContent : $m[1];
    }, $content);
}
?>
<div class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
    <?php if ($isPreview) { ?>
    <div class="mb-8 rounded-[30px] border border-[#D6E3E1] bg-white p-5 sm:p-6">
        <div class="flex flex-col gap-4">
            <div class="flex min-w-0 items-start gap-4">
                <span class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-[#004241] text-lg font-semibold text-white">i</span>
                <div class="min-w-0">
                    <p class="text-[12px] font-semibold uppercase tracking-[0.14em] text-[#004241]/55"><?= htmlspecialchars($previewModeLabel) ?></p>
                    <h2 class="mt-1 text-[26px] font-semibold leading-[1.05] text-[#004241]"><?= htmlspecialchars($previewHeading) ?></h2>
                    <p class="mt-2 max-w-[60rem] text-[15px] leading-6 text-[#004241]/72"><?= htmlspecialchars($previewNotice) ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <?php if (! $isPreview) { ?>
    <!-- Bannière pub 728×90 -->
    <div class="mb-6 flex items-center justify-center">
        <div class="flex h-[90px] w-full max-w-[728px] items-center justify-center overflow-hidden">
            <?= render_php_view('site.partials.adsense_slot', ['slotKey' => 'article_top_banner_728x90']) ?>
        </div>
    </div>
    <?php } ?>

    <!-- Grand carré hero : photo + overlay + bouton retour, titre, date -->
    <div class="relative w-full mx-auto rounded-[30px] overflow-hidden mb-[54px] max-w-[1282px] h-[444px] min-h-[280px] bg-black/30">
        <img src="<?= htmlspecialchars($coverSrc) ?>" data-fallback-url="<?= htmlspecialchars($coverFallback) ?>" alt="<?= htmlspecialchars($title) ?>" class="absolute inset-0 w-full h-full object-cover" loading="eager" onerror="this.onerror=null;this.src=this.dataset.fallbackUrl||'';">
        <div class="absolute inset-0 bg-black/30" aria-hidden="true"></div>
        <div class="absolute inset-0 flex flex-col p-8 top-0 left-0">
            <?php if ($isPreview) { ?>
            <div class="mb-[85px] flex flex-wrap items-center gap-3">
                <a href="<?= htmlspecialchars($previewBackHref) ?>" class="inline-flex items-center justify-center gap-2 self-start rounded-full bg-white/95 px-4 py-2.5 text-sm font-medium text-[#004241] shadow-md transition hover:bg-white">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" transform="matrix(-1 0 0 1 24 0)" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <?= htmlspecialchars($previewBackLabel) ?>
                </a>
                <?php if ($cover_status_label) { ?>
                <span class="inline-flex items-center gap-2 rounded-full border border-white/18 bg-[rgba(255,255,255,0.14)] px-4 py-2 text-sm font-medium text-white backdrop-blur-[14px]">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full <?= $has_generated_cover ? 'bg-[#0F766E] text-white' : 'bg-[#B91C1C] text-white' ?>">
                        <?= $has_generated_cover ? '✓' : '!' ?>
                    </span>
                    <span><?= htmlspecialchars($cover_status_label) ?></span>
                </span>
                <?php } ?>
            </div>
            <?php } else { ?>
            <a href="<?= htmlspecialchars($backHref) ?>"
               onclick="if(history.length>1){event.preventDefault();history.back();}"
               class="mb-[85px] inline-flex items-center justify-center gap-2 self-start rounded-full bg-white/95 px-4 py-2.5 text-sm font-medium text-[#004241] shadow-md transition hover:bg-white" aria-label="<?= htmlspecialchars($t('site.back', 'Retour')) ?>">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" transform="matrix(-1 0 0 1 24 0)" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                <?= htmlspecialchars($t('site.back', 'Retour')) ?>
            </a>
            <?php } ?>
            <h1 class="mb-[9px] max-w-[947px] font-sans text-[2.35rem] font-semibold leading-[0.96] text-white sm:text-5xl"><?= htmlspecialchars($title) ?></h1>
            <?php if ($metaLine) { ?>
            <p class="text-white font-light leading-none text-xl opacity-95 font-sans">
                <time datetime="<?= htmlspecialchars($published_at_iso ?? '') ?>"><?= htmlspecialchars($metaLine) ?></time>
            </p>
            <?php } ?>
        </div>
    </div>
</div>

<?php
$shareLinks = [
    ['Facebook', 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($shareUrl), 'M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z'],
    ['X', 'https://twitter.com/intent/tweet?url='.rawurlencode($shareUrl).'&text='.rawurlencode($shareTitle), 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
    ['LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($shareUrl), 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'],
    ['WhatsApp', 'https://wa.me/?text='.rawurlencode($shareTitle.' '.$shareUrl), 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z'],
];
?>
<style>
.article-body p { margin-bottom: 1.25rem; line-height: 1.75; }
.article-body p:last-child { margin-bottom: 0; }
.article-body h2 { font-size: 1.5rem; font-weight: 600; color: #004241; margin-top: 2rem; margin-bottom: 0.75rem; line-height: 1.3; }
.article-body h3 { font-size: 1.25rem; font-weight: 600; color: #004241; margin-top: 1.75rem; margin-bottom: 0.5rem; }
.article-body a { color: #004241; text-decoration: underline; text-underline-offset: 3px; }
.article-body a:hover { opacity: 0.8; }
.article-body ul, .article-body ol { margin: 1rem 0; padding-left: 1.5rem; }
.article-body li { margin-bottom: 0.5rem; line-height: 1.65; }
.article-body .article-sources { margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid rgba(0, 66, 65, 0.1); }
.article-body .article-sources h3 { margin: 0 0 0.75rem; font-size: 0.95rem; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(0, 66, 65, 0.5); }
.article-body .article-sources ul { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 0.45rem; }
.article-body .article-sources li { margin: 0; font-size: 0.88rem; line-height: 1.45; display: flex; flex-wrap: wrap; gap: 0.45rem; align-items: baseline; }
.article-body .article-sources li a { text-decoration: none; color: #004241; max-width: min(100%, 540px); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; }
.article-body .article-sources li span { font-size: 0.78rem; color: rgba(0, 66, 65, 0.45); white-space: nowrap; }
</style>
<div class="w-full max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col items-center">
        <article class="w-full max-w-[680px]">
            <?php if ($isPreview) { ?>
            <div class="mb-6 rounded-[22px] border border-[#D6E3E1] bg-white px-5 py-4 text-sm text-[#004241]/72 shadow-[0_12px_30px_rgba(0,66,65,0.04)]">
                <span class="font-medium text-[#004241]"><?= htmlspecialchars($previewReaderLabel) ?></span>
            </div>
            <?php } ?>
            <div class="<?= $articleCanvasClass ?>">
            <?php if ($excerpt) { ?>
            <p class="text-lg text-[#004241]/80 leading-relaxed mb-10 font-sans" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($excerpt)) ?></p>
            <?php } ?>
            <div class="article-body text-[#004241] font-sans text-[18px]" style="line-height: 1.75;">
        <?= $content ?>
            </div>
            </div>

            <?php if (! $isPreview) { ?>
            <aside class="flex flex-col items-center mt-14 pt-10 border-t border-[#004241]/10" aria-label="<?= htmlspecialchars($t('site.share_article', "Partager l'article")) ?>">
                <span class="text-sm font-medium text-[#004241]/60 uppercase tracking-wider mb-5"><?= htmlspecialchars($t('site.share', 'Partager')) ?></span>
                <div class="flex items-center justify-center gap-5">
                    <?php foreach ($shareLinks as $share) { ?>
                    <a href="<?= htmlspecialchars($share[1]) ?>" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-11 h-11 rounded-full bg-[#EBF1EF] text-[#004241] hover:bg-[#004241] hover:text-white transition-colors duration-200" aria-label="<?= htmlspecialchars($t('site.share_on', 'Partager sur')) ?> <?= htmlspecialchars($share[0]) ?>">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?= htmlspecialchars($share[2]) ?>"/></svg>
                    </a>
                    <?php } ?>
                </div>
            </aside>
            <?php } ?>
        </article>
    </div>
</div>

<?php if ($showRelatedSection && ! $isPreview) { ?>
<section class="mx-auto mb-0 mt-16 max-w-[1500px]" aria-label="<?= htmlspecialchars($t('site.read_also', 'À lire aussi')) ?>">
    <!-- Titre + boutons avec padding normal -->
    <div class="mb-6 flex items-center justify-between">
        <h2 class="font-sans text-3xl font-medium text-[#004241]"><?= htmlspecialchars($t('site.read_also', 'À lire aussi')) ?></h2>
        <?php if ($useRelatedCarousel) { ?>
        <div class="flex items-center gap-2">
            <button type="button" id="also-prev"
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-[#EBF1EF] text-[#004241] transition-all duration-200 hover:bg-[#D8E8E3] disabled:opacity-30 disabled:cursor-not-allowed"
                    aria-label="<?= htmlspecialchars($t('site.previous_article', 'Précédent')) ?>">
                <svg class="h-5 w-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
            <button type="button" id="also-next"
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-[#EBF1EF] text-[#004241] transition-all duration-200 hover:bg-[#D8E8E3] disabled:opacity-30 disabled:cursor-not-allowed"
                    aria-label="<?= htmlspecialchars($t('site.next_article', 'Suivant')) ?>">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
        <?php } ?>
    </div>

    <!-- Carousel pleine largeur (pas de padding latéral) -->
    <div>
        <div id="also-frame" class="overflow-hidden">
            <div id="also-rail" class="<?= $useRelatedCarousel ? 'flex gap-4 transition-transform duration-[900ms] ease-out will-change-transform' : 'grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3' ?>">
                <?php foreach ($alsoCarouselItems as $index => $item) { ?>
                <?php if (($item['type'] ?? 'article') === 'ad') { ?>
                <aside <?= $useRelatedCarousel ? 'data-also-item' : '' ?>
                       class="<?= $useRelatedCarousel ? 'also-card flex-shrink-0' : 'aspect-square w-full' ?> flex items-center justify-center overflow-hidden rounded-[28px]">
                    <?= render_php_view('site.partials.adsense_slot', ['slotKey' => 'article_related_square_336x280']) ?>
                </aside>
                <?php } else { ?>
                <?php $catData = $item['category'] ?? null; $itemCategory = is_array($catData) ? ($catData['name'] ?? $relatedCategoryName) : ($catData ?? $relatedCategoryName); ?>
                <?php $isPriorityAlsoCard = $index < 3; ?>
                <a href="<?= !empty($item['slug']) ? '/articles/'.htmlspecialchars($item['slug']) : '#' ?>"
                   <?= $useRelatedCarousel ? 'data-also-item' : '' ?>
                   class="group relative <?= $useRelatedCarousel ? 'also-card flex-shrink-0' : 'aspect-square w-full' ?> block overflow-hidden rounded-[28px]">
                    <img src="<?= htmlspecialchars($item['image']) ?>"
                         data-fallback-url="<?= htmlspecialchars($item['fallback'] ?? $item['image']) ?>"
                         alt="<?= htmlspecialchars($item['title']) ?>"
                         class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-[1.05]"
                         loading="<?= $isPriorityAlsoCard ? 'eager' : 'lazy' ?>"
                         <?= $isPriorityAlsoCard ? 'fetchpriority="high" decoding="sync"' : 'decoding="async"' ?>
                         onerror="this.onerror=null;this.src=this.dataset.fallbackUrl||'';">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-[18px]">
                        <div class="rounded-[21px] flex w-full min-w-0 flex-col gap-1.5 p-[18px] bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]">
                            <span class="<?= $tagCarousel ?>"><?= htmlspecialchars($itemCategory) ?></span>
                            <h3 class="line-clamp-2 text-base font-medium leading-snug text-white"><?= htmlspecialchars($item['title']) ?></h3>
                            <p class="text-xs text-white/70"><?= htmlspecialchars($item['date']) ?> • <?= (int)($item['reading_time'] ?? 0) ?> min</p>
                        </div>
                    </div>
                </a>
                <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php if ($useRelatedCarousel) { ?>
    <script>
    (function() {
        var prevBtn = document.getElementById('also-prev');
        var nextBtn = document.getElementById('also-next');
        var frame   = document.getElementById('also-frame');
        var rail    = document.getElementById('also-rail');
        if (!prevBtn || !nextBtn || !frame || !rail) return;

        var GAP = 16;
        var isAnimating = false;
        var cloneCount = 0;
        var origItems = Array.from(rail.querySelectorAll('[data-also-item]'));
        var realCount = origItems.length;
        if (realCount < 2) return;
        cloneCount = Math.min(3, realCount);

        for (var i = 0; i < cloneCount; i++) {
            var headClone = origItems[realCount - cloneCount + i].cloneNode(true);
            headClone.setAttribute('aria-hidden', 'true');
            rail.insertBefore(headClone, rail.firstChild);
        }

        for (var j = 0; j < cloneCount; j++) {
            var tailClone = origItems[j].cloneNode(true);
            tailClone.setAttribute('aria-hidden', 'true');
            rail.appendChild(tailClone);
        }

        var idx = cloneCount;

        function items() {
            return Array.from(rail.querySelectorAll('[data-also-item]'));
        }

        function cols() {
            if (window.matchMedia('(min-width: 1024px)').matches) return 3;
            if (window.matchMedia('(min-width: 640px)').matches) return 2;
            return 1;
        }

        function cardSize() {
            var n = cols();
            var viewportWidth = frame.getBoundingClientRect().width;
            return Math.floor((viewportWidth - GAP * (n - 1)) / n);
        }

        function syncSizes() {
            var size = cardSize();
            items().forEach(function(el) {
                el.style.width = size + 'px';
                el.style.height = size + 'px';
                el.style.flexBasis = size + 'px';
            });
        }

        function goTo(i, instant) {
            idx = i;
            var size = cardSize();
            var offset = idx * (size + GAP);
            if (instant) rail.style.transition = 'none';
            rail.style.transform = 'translate3d(-' + Math.round(offset) + 'px, 0, 0)';
            if (instant) {
                rail.offsetHeight;
                rail.style.transition = '';
            }
        }

        rail.addEventListener('transitionend', function(e) {
            if (e.target !== rail || e.propertyName !== 'transform') return;
            if (idx < cloneCount) {
                idx = realCount + idx;
                goTo(idx, true);
            } else if (idx >= realCount + cloneCount) {
                idx = idx - realCount;
                goTo(idx, true);
            }
            isAnimating = false;
        });

        syncSizes();
        goTo(idx, true);

        prevBtn.addEventListener('click', function() {
            if (isAnimating) return;
            isAnimating = true;
            goTo(idx - 1, false);
        });
        nextBtn.addEventListener('click', function() {
            if (isAnimating) return;
            isAnimating = true;
            goTo(idx + 1, false);
        });
        window.addEventListener('resize', function() {
            syncSizes();
            goTo(idx, true);
        });
    })();
    </script>
    <?php } ?>
</section>
<?php } ?>
