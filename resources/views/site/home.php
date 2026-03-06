<?php
$top_news = $top_news ?? null;
$featured = $featured ?? [];
$latest = $latest ?? [];
$categories = $categories ?? [];
$writer_signup_url = $writer_signup_url ?? '#';
$writer_dashboard_url = $writer_dashboard_url ?? '#';
?>
<div class="space-y-12">
    <?php if ($top_news): ?>
    <section>
        <a href="/articles/<?= htmlspecialchars($top_news['slug']) ?>" class="block rounded-xl overflow-hidden bg-gray-900 aspect-[2/1] relative">
            <?php if (!empty($top_news['cover_image_url'])): ?>
            <img src="<?= htmlspecialchars($top_news['cover_image_url']) ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-70">
            <?php endif; ?>
            <div class="absolute inset-0 flex flex-col justify-end p-8">
                <span class="text-sm font-medium text-amber-400 uppercase tracking-wide">Top news</span>
                <h1 class="text-3xl md:text-4xl font-bold text-white mt-1"><?= htmlspecialchars($top_news['title']) ?></h1>
                <?php if (!empty($top_news['excerpt'])): ?>
                <p class="text-gray-300 mt-2 max-w-2xl"><?= htmlspecialchars($top_news['excerpt']) ?></p>
                <?php endif; ?>
            </div>
        </a>
    </section>
    <?php endif; ?>

    <?php if (count($featured) > 0): ?>
    <section>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">À la une</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($featured as $article): ?>
            <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="group block rounded-lg overflow-hidden bg-white border border-gray-200 hover:border-gray-300 hover:shadow-md transition">
                <?php if (!empty($article['cover_image_url'])): ?>
                <div class="aspect-video bg-gray-100">
                    <img src="<?= htmlspecialchars($article['cover_image_url']) ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                </div>
                <?php endif; ?>
                <div class="p-4">
                    <?php if (!empty($article['category'])): ?>
                    <span class="text-xs font-medium text-gray-500"><?= htmlspecialchars($article['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-semibold text-gray-900 mt-1 line-clamp-2 group-hover:text-amber-600"><?= htmlspecialchars($article['title']) ?></h3>
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
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Dernières actualités</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($latest as $article): ?>
            <a href="/articles/<?= htmlspecialchars($article['slug']) ?>" class="group flex gap-4 rounded-lg p-3 hover:bg-white hover:shadow border border-transparent hover:border-gray-200 transition">
                <?php if (!empty($article['cover_image_url'])): ?>
                <div class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden bg-gray-100">
                    <img src="<?= htmlspecialchars($article['cover_image_url']) ?>" alt="" class="w-full h-full object-cover">
                </div>
                <?php endif; ?>
                <div class="min-w-0 flex-1">
                    <?php if (!empty($article['category'])): ?>
                    <span class="text-xs font-medium text-gray-500"><?= htmlspecialchars($article['category']['name']) ?></span>
                    <?php endif; ?>
                    <h3 class="font-semibold text-gray-900 mt-0.5 line-clamp-2 group-hover:text-amber-600"><?= htmlspecialchars($article['title']) ?></h3>
                    <?php if (!empty($article['reading_time'])): ?>
                    <p class="text-sm text-gray-500 mt-1"><?= (int) $article['reading_time'] ?> min</p>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (count($categories) > 0): ?>
    <section>
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Découvrez nos rubriques</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($categories as $cat): ?>
            <a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="block rounded-lg border border-gray-200 bg-white p-4 hover:border-amber-500 hover:shadow transition text-center">
                <?php if (!empty($cat['image_url'])): ?>
                <img src="<?= htmlspecialchars($cat['image_url']) ?>" alt="" class="w-12 h-12 mx-auto rounded-lg object-cover">
                <?php endif; ?>
                <span class="font-medium text-gray-900 mt-2 block"><?= htmlspecialchars($cat['name']) ?></span>
                <?php if (isset($cat['published_articles_count'])): ?>
                <span class="text-sm text-gray-500"><?= (int) $cat['published_articles_count'] ?> articles</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="pt-8 border-t border-gray-200">
        <p class="text-gray-600 text-center">
            <a href="<?= htmlspecialchars($writer_signup_url) ?>" class="font-medium text-amber-600 hover:text-amber-700">Rédiger un article</a>
        </p>
    </section>
</div>
