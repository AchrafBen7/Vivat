<?php
$category = $category ?? [];
$description = $description ?? '';
$total_published = $total_published ?? 0;
$sub_categories = $sub_categories ?? [];
$featured = $featured ?? [];
$latest = $latest ?? [];
$category_name = $category['name'] ?? 'Rubrique';
$category_slug = $category['slug'] ?? '';
?>
<div>
    <header class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($category_name) ?></h1>
        <?php if ($description): ?>
        <p class="text-gray-600 mt-2 max-w-2xl"><?= htmlspecialchars($description) ?></p>
        <?php endif; ?>
        <p class="text-sm text-gray-500 mt-2"><?= (int) $total_published ?> article(s)</p>
    </header>

    <?php if (count($sub_categories) > 0): ?>
    <nav class="flex flex-wrap gap-2 mb-8">
        <a href="/categories/<?= htmlspecialchars($category_slug) ?>" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-medium">Tous</a>
        <?php foreach ($sub_categories as $sub): ?>
        <a href="/categories/<?= htmlspecialchars($category_slug) ?>?sub_category=<?= htmlspecialchars($sub['slug']) ?>" class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 text-sm font-medium hover:border-amber-500 hover:text-amber-600"><?= htmlspecialchars($sub['name']) ?></a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <?php if (count($featured) > 0): ?>
    <section class="mb-10">
        <h2 class="text-xl font-bold text-gray-900 mb-4">À la une</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($featured as $article): ?>
            <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="block rounded-lg overflow-hidden bg-white border border-gray-200 hover:shadow-md transition">
                <?php if (!empty($article['cover_image_url'])): ?>
                <div class="aspect-video bg-gray-100">
                    <img src="<?= htmlspecialchars($article['cover_image_url']) ?>" alt="" class="w-full h-full object-cover">
                </div>
                <?php endif; ?>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 line-clamp-2 hover:text-amber-600"><?= htmlspecialchars($article['title']) ?></h3>
                    <?php if (!empty($article['published_at'])): ?>
                    <p class="text-sm text-gray-500 mt-2"><?= htmlspecialchars($article['published_at']) ?></p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (count($latest) > 0): ?>
    <section>
        <h2 class="text-xl font-bold text-gray-900 mb-4">Dernières actualités</h2>
        <ul class="space-y-4">
            <?php foreach ($latest as $article): ?>
            <li>
                <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="flex gap-4 py-3 border-b border-gray-100 hover:bg-gray-50 rounded-lg px-2 -mx-2 transition">
                    <?php if (!empty($article['cover_image_url'])): ?>
                    <div class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden bg-gray-100">
                        <img src="<?= htmlspecialchars($article['cover_image_url']) ?>" alt="" class="w-full h-full object-cover">
                    </div>
                    <?php endif; ?>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-medium text-gray-900 line-clamp-2 hover:text-amber-600"><?= htmlspecialchars($article['title']) ?></h3>
                        <?php if (!empty($article['published_at'])): ?>
                        <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($article['published_at']) ?></p>
                        <?php endif; ?>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>

    <?php if (count($featured) === 0 && count($latest) === 0): ?>
    <p class="text-gray-500">Aucun article dans cette rubrique pour le moment.</p>
    <?php endif; ?>
</div>
