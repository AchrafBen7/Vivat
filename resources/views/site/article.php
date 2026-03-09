<?php
$article = $article ?? [];
$title = $article['title'] ?? 'Article';
$slug = $article['slug'] ?? '';
$published_at = $article['published_at'] ?? null;
$published_at_iso = $article['published_at_iso'] ?? null;
$reading_time = $article['reading_time'] ?? null;
$category = $article['category'] ?? null;
$cover_image_url = $article['cover_image_url'] ?? null;
$content = $article['content'] ?? '';
$excerpt = $article['excerpt'] ?? '';
?>
<article class="max-w-3xl mx-auto">
    <header class="mb-8">
        <?php if ($category): ?>
        <a href="/categories/<?= htmlspecialchars($category['slug']) ?>" class="text-sm font-medium text-amber-600 hover:text-amber-700"><?= htmlspecialchars($category['name']) ?></a>
        <?php endif; ?>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2"><?= htmlspecialchars($title) ?></h1>
        <div class="flex gap-4 mt-4 text-sm text-gray-500">
            <?php if ($published_at): ?>
            <time datetime="<?= htmlspecialchars($published_at_iso ?? $published_at) ?>"><?= htmlspecialchars($published_at) ?></time>
            <?php endif; ?>
            <?php if ($reading_time): ?>
            <span><?= (int) $reading_time ?> min de lecture</span>
            <?php endif; ?>
        </div>
    </header>
    <?php if ($cover_image_url): ?>
    <figure class="rounded-xl overflow-hidden mb-8">
        <img src="<?= htmlspecialchars($cover_image_url) ?>" alt="<?= htmlspecialchars($title) ?>" class="w-full aspect-video object-cover" loading="eager">
    </figure>
    <?php endif; ?>
    <?php if ($excerpt): ?>
    <p class="text-xl text-gray-600 mb-8"><?= nl2br(htmlspecialchars($excerpt)) ?></p>
    <?php endif; ?>
    <div class="prose prose-lg prose-gray max-w-none">
        <?= $content ?>
    </div>
</article>
