<?php
$submissions = $submissions ?? [];
?>
<h1 class="font-medium text-[#004241] text-2xl mb-2">Mes articles</h1>
<p class="text-[#004241]/80 mb-8">Vos soumissions et brouillons</p>

<div class="rounded-[30px] bg-[#EBF1EF] border border-[#EBF1EF] p-8">
    <?php if (empty($submissions)): ?>
    <p class="text-[#004241]/70">Vous n'avez pas encore soumis d'article.</p>
    <a href="<?= url('/contributor/new') ?>" class="inline-flex items-center justify-center rounded-full bg-[#004241] text-white font-semibold text-sm h-12 px-6 hover:bg-[#003535] transition mt-6">
        Rédiger votre premier article
    </a>
    <?php else: ?>
    <ul class="space-y-4">
        <?php foreach ($submissions as $sub): ?>
        <li class="flex items-center justify-between py-3 border-b border-[#004241]/10 last:border-0">
            <span class="font-medium text-[#004241]"><?= htmlspecialchars($sub['title']) ?></span>
            <span class="text-sm text-[#004241]/70"><?= htmlspecialchars($sub['status']) ?> • <?= htmlspecialchars($sub['created_at']) ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>
