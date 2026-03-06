<?php
$categories = $categories ?? [];
?>
<div>
    <h1 class="text-3xl font-bold text-gray-900 mb-2">Rubriques</h1>
    <p class="text-gray-600 mb-8">Découvrez les rubriques Vivat.</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($categories as $cat): ?>
        <a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="block rounded-xl border border-gray-200 bg-white p-6 hover:border-amber-500 hover:shadow-lg transition">
            <?php if (!empty($cat['image_url'])): ?>
            <img src="<?= htmlspecialchars($cat['image_url']) ?>" alt="" class="w-16 h-16 rounded-lg object-cover mb-4">
            <?php endif; ?>
            <h2 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($cat['name']) ?></h2>
            <?php if (!empty($cat['description'])): ?>
            <p class="text-gray-600 mt-2 line-clamp-2"><?= htmlspecialchars($cat['description']) ?></p>
            <?php endif; ?>
            <?php if (isset($cat['published_articles_count'])): ?>
            <p class="text-sm text-gray-500 mt-3"><?= (int) $cat['published_articles_count'] ?> articles</p>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php if (count($categories) === 0): ?>
    <p class="text-gray-500">Aucune rubrique pour le moment.</p>
    <?php endif; ?>
</div>
