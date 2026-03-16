<?php
$activeTab = $activeTab ?? 'articles';
$contributorContent = $contributorContent ?? '';
?>
<div class="flex gap-8 pt-6 pb-12">
    <?= render_php_view('site.contributor.sidebar', ['activeTab' => $activeTab]) ?>
    <main class="flex-1 min-w-0">
        <?= $contributorContent ?>
    </main>
</div>
