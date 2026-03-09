<?php
$articles = $articles ?? [];
$pagination = $pagination ?? null;
?>
<div class="max-w-6xl mx-auto px-4 py-12">
    <h1 class="font-medium text-[#004241] mb-10" style="font-size: 32px; font-family: Figtree, sans-serif;">Toutes les actualités</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($articles as $art): ?>
        <a href="/articles/<?= htmlspecialchars($art['slug']) ?>" class="block rounded-[30px] overflow-hidden border border-gray-200/40 p-6 flex flex-col bg-[#EBF1EF] hover:bg-[#E5ECEA] transition" style="min-height: 300px;">
            <?php if (!empty($art['category'])): ?>
            <span class="text-xs font-medium text-[#004241]/80"><?= htmlspecialchars($art['category']['name']) ?></span>
            <?php endif; ?>
            <h2 class="font-medium text-[#004241] line-clamp-3 mt-2 flex-1" style="font-size: 18px;"><?= htmlspecialchars($art['title']) ?></h2>
            <p class="text-[#004241] text-sm font-light mt-2"><?= htmlspecialchars($art['published_at'] ?? '') ?> • <?= (int) ($art['reading_time'] ?? 0) ?> min</p>
            <?php if (!empty($art['cover_image_url'])): ?>
            <div class="rounded-[21px] overflow-hidden mt-4 flex-shrink-0">
                <img src="<?= htmlspecialchars($art['cover_image_url']) ?>" alt="<?= htmlspecialchars($art['title'] ?? 'Article') ?>" class="w-full h-40 object-cover" loading="lazy">
            </div>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($articles)): ?>
    <p class="text-gray-500 text-center py-12">Aucun article pour le moment.</p>
    <?php endif; ?>

    <?php if ($pagination && $pagination->hasPages()): ?>
    <nav class="flex justify-center gap-2 mt-10">
        <?php if ($pagination->onFirstPage()): ?>
        <span class="px-4 py-2 rounded-full bg-gray-100 text-gray-500">Précédent</span>
        <?php else: ?>
        <a href="<?= $pagination->previousPageUrl() ?>" class="px-4 py-2 rounded-full bg-[#004241] text-white hover:bg-[#003535] transition">Précédent</a>
        <?php endif; ?>
        <span class="px-4 py-2 text-[#004241]">Page <?= $pagination->currentPage() ?> sur <?= $pagination->lastPage() ?></span>
        <?php if ($pagination->hasMorePages()): ?>
        <a href="<?= $pagination->nextPageUrl() ?>" class="px-4 py-2 rounded-full bg-[#004241] text-white hover:bg-[#003535] transition">Suivant</a>
        <?php else: ?>
        <span class="px-4 py-2 rounded-full bg-gray-100 text-gray-500">Suivant</span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</div>
