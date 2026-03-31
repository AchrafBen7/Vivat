<?php
$submissions = $submissions ?? [];
$pagination = $pagination ?? null;
$paginationView = $pagination ? $pagination->withQueryString() : null;
$statusStyles = [
    'draft' => [
        'label' => 'Brouillon',
        'bg' => 'bg-[#F3E8CC]',
        'text' => 'text-[#7A5A14]',
        'dot' => '#C69214',
    ],
    'pending' => [
        'label' => 'En attente',
        'bg' => 'bg-[#004241]/10',
        'text' => 'text-[#006664]',
        'dot' => '#006664',
    ],
    'approved' => [
        'label' => 'Approuvé',
        'bg' => 'bg-[#527E7E]/15',
        'text' => 'text-[#2D5C5C]',
        'dot' => '#527E7E',
    ],
    'rejected' => [
        'label' => 'Rejeté',
        'bg' => 'bg-[#AE422E]/10',
        'text' => 'text-[#AE422E]',
        'dot' => '#AE422E',
    ],
];
?>
<div class="space-y-6">
    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-semibold text-[#004241] text-2xl">Mes articles</h1>
            <p class="text-[#004241]/70 text-sm mt-0.5">Vos soumissions et brouillons</p>
        </div>
        <?php if (! empty($submissions)) { ?>
        <a href="<?= url('/contributor/new') ?>" class="inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition-colors hover:bg-[#003130] shrink-0">
            Créer un nouvel article
        </a>
        <?php } ?>
    </div>

    <?php if (empty($submissions)) { ?>
    <div class="rounded-[24px] border border-[#004241]/12 bg-[#EBF1EF]/50 p-8 text-center">
        <p class="text-[#004241]/75">Vous n'avez pas encore soumis d'article.</p>
        <a href="<?= url('/contributor/new') ?>" class="mt-4 inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition-colors hover:bg-[#003130]">
            Rédiger votre premier article
        </a>
    </div>
    <?php } else { ?>
    <div class="grid grid-cols-1 gap-6">
        <?php foreach ($submissions as $sub) { ?>
        <?php
        $status = $sub['status'] ?? 'draft';
        $statusStyle = $statusStyles[$status] ?? $statusStyles['draft'];
        $cover = $sub['cover_image_url'] ?? null;
        $fallbackImage = $status === 'pending'
            ? '/quotidien.jpg'
            : ($status === 'approved' ? '/chezsoi.jpg' : ($status === 'rejected' ? '/sante.jpg' : '/finance.jpg'));
        $coverSrc = $cover ?: $fallbackImage;
        $deleteUrl = $sub['delete_url'] ?? null;
        $canDelete = (bool) ($sub['can_delete'] ?? false);
        $editUrl = $sub['edit_url'] ?? null;
        $previewUrl = $sub['preview_url'] ?? '#';
        $hasReviewerNote = ! empty($sub['reviewer_notes']);
        $paymentStatus = $sub['payment_status'] ?? null;
        $isRefunded = $paymentStatus === 'refunded';
        $isRefundPending = $status === 'rejected' && $paymentStatus === 'paid';
        $publishedArticleUrl = $sub['published_article_url'] ?? null;
        $requestUnpublishUrl = $sub['request_unpublish_url'] ?? null;
        $hasUnpublishRequest = ! empty($sub['depublication_requested_at']);
        ?>
        <div class="group flex flex-col overflow-hidden rounded-[24px] border border-[#004241]/10 bg-white shadow-[0_4px_20px_rgba(0,66,65,0.06)] transition-shadow hover:shadow-[0_8px_32px_rgba(0,66,65,0.1)]">
            <div class="flex flex-col sm:flex-row min-h-0 gap-4 p-4">
                <!-- Image -->
                <a href="<?= htmlspecialchars($previewUrl) ?>" class="relative block aspect-[4/3] w-full sm:w-[200px] sm:min-w-[200px] sm:aspect-[4/3] shrink-0 overflow-hidden rounded-[16px] bg-[#1E2D25]">
                    <img src="<?= htmlspecialchars($coverSrc) ?>" alt="" class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                </a>

                <!-- Contenu -->
                <div class="flex flex-1 flex-col gap-3 min-w-0 py-1">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2 min-w-0">
                            <?php if (! empty($sub['category']['name'])) { ?>
                            <span class="rounded-full bg-[#EBF1EF] px-3 py-1 text-xs font-medium text-[#004241]">
                                <?= htmlspecialchars($sub['category']['name']) ?>
                            </span>
                            <?php } ?>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium <?= $statusStyle['bg'] ?> <?= $statusStyle['text'] ?>">
                                <span class="h-2 w-2 rounded-full shrink-0" style="background: <?= htmlspecialchars($statusStyle['dot']) ?>"></span>
                                <?= htmlspecialchars($sub['status_label'] ?? $statusStyle['label']) ?>
                            </span>
                        </div>
                    </div>

                    <a href="<?= htmlspecialchars($previewUrl) ?>" class="font-semibold text-[#004241] text-lg leading-snug line-clamp-2 hover:text-[#003130] transition-colors">
                        <?= htmlspecialchars($sub['title']) ?>
                    </a>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#004241]/60">
                        <span><?= htmlspecialchars($sub['created_at'] ?? '') ?></span>
                        <?php if (! empty($sub['reading_time'])) { ?>
                        <span>•</span>
                        <span><?= (int) $sub['reading_time'] ?> min</span>
                        <?php } ?>
                    </div>

                    <?php if (! empty($sub['excerpt'])) { ?>
                    <p class="text-sm text-[#004241]/70 line-clamp-2 leading-relaxed">
                        <?= htmlspecialchars($sub['excerpt']) ?>
                    </p>
                    <?php } ?>

                    <?php if ($isRefunded || $isRefundPending) { ?>
                    <div class="rounded-[18px] border px-4 py-3 <?= $isRefunded ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' ?>">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold <?= $isRefunded ? 'text-emerald-700' : 'text-amber-700' ?>">
                                    <?= $isRefunded ? 'Paiement remboursé' : 'Remboursement à traiter' ?>
                                </p>
                                <p class="mt-1 text-xs <?= $isRefunded ? 'text-emerald-700/80' : 'text-amber-700/80' ?>">
                                    <?php if ($isRefunded) { ?>
                                        <?= htmlspecialchars($sub['payment_amount_label'] ?? 'Montant inconnu') ?>
                                        <?php if (! empty($sub['refunded_at'])) { ?> · remboursé le <?= htmlspecialchars($sub['refunded_at']) ?><?php } ?>
                                    <?php } else { ?>
                                        L’article a été refusé, mais le remboursement n’est pas encore confirmé dans le système.
                                    <?php } ?>
                                </p>
                            </div>

                            <?php if ($isRefunded && ! empty($sub['refund_receipt_url'])) { ?>
                            <a href="<?= htmlspecialchars($sub['refund_receipt_url']) ?>" class="inline-flex h-9 items-center rounded-full bg-white px-4 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                                Voir le reçu
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if ($status === 'approved' && $hasUnpublishRequest) { ?>
                    <div class="rounded-[18px] border border-sky-200 bg-sky-50 px-4 py-3">
                        <p class="text-sm font-semibold text-sky-700">Dépublication demandée</p>
                        <p class="mt-1 text-xs text-sky-700/80">
                            Votre demande a été envoyée à la rédaction<?php if (! empty($sub['depublication_requested_at'])) { ?> le <?= htmlspecialchars($sub['depublication_requested_at']) ?><?php } ?>.
                        </p>
                    </div>
                    <?php } ?>

                    <!-- Actions -->
                    <div class="mt-auto pt-2 flex flex-wrap items-center gap-2">
                        <?php if (! empty($editUrl)) { ?>
                        <a href="<?= htmlspecialchars($editUrl) ?>" class="inline-flex h-9 items-center gap-2 rounded-full bg-[#004241] px-4 text-sm font-medium text-white transition-colors hover:bg-[#003130]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            <?= $status === 'rejected' ? 'Nouvelle tentative' : 'Modifier' ?>
                        </a>
                        <?php } ?>
                        <a href="<?= htmlspecialchars($previewUrl) ?>" class="inline-flex h-9 items-center gap-2 rounded-full border border-[#004241]/20 px-4 text-sm font-medium text-[#004241] transition-colors hover:bg-[#EBF1EF]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Voir
                        </a>
                        <?php if ($publishedArticleUrl) { ?>
                        <a href="<?= htmlspecialchars($publishedArticleUrl) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex h-9 items-center gap-2 rounded-full border border-[#004241]/20 px-4 text-sm font-medium text-[#004241] transition-colors hover:bg-[#EBF1EF]">
                            Article publié
                        </a>
                        <?php } ?>
                        <?php if ($requestUnpublishUrl && ! $hasUnpublishRequest) { ?>
                        <form method="post" action="<?= htmlspecialchars($requestUnpublishUrl) ?>" class="inline-flex">
                            <?= csrf_field() ?>
                            <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-full border border-[#7A5A14]/30 px-4 text-sm font-medium text-[#7A5A14] transition-colors hover:bg-[#F3E8CC]">
                                Demande de dépublication
                            </button>
                        </form>
                        <?php } ?>
                        <?php if ($canDelete && ! empty($deleteUrl)) { ?>
                        <button
                            type="button"
                            class="js-delete-submission inline-flex h-9 items-center gap-2 rounded-full border border-[#AE422E]/30 px-4 text-sm font-medium text-[#AE422E] transition-colors hover:bg-[#AE422E]/5"
                            data-delete-url="<?= htmlspecialchars($deleteUrl) ?>"
                            data-article-title="<?= htmlspecialchars($sub['title']) ?>"
                            aria-label="Supprimer cet article"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            Supprimer
                        </button>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php if ($hasReviewerNote) { ?>
            <div class="border-t border-[#004241]/8 bg-[#F4F8F7] px-5 py-4">
                <div class="flex items-center gap-2 text-sm font-semibold text-[#004241]">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#004241]/15 text-[#004241]">i</span>
                    Note de relecture
                </div>
                <p class="mt-2 text-sm leading-relaxed text-[#004241]/80"><?= nl2br(htmlspecialchars($sub['reviewer_notes'])) ?></p>
                <?php if (! empty($sub['reviewer_name']) || ! empty($sub['reviewed_at'])) { ?>
                <p class="mt-2 text-xs font-medium uppercase tracking-wider text-[#004241]/50">
                    <?= htmlspecialchars(trim(($sub['reviewer_name'] ?? '').(! empty($sub['reviewed_at']) ? ' • '.$sub['reviewed_at'] : ''))) ?>
                </p>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <?php if ($paginationView && $paginationView->hasPages()) { ?>
    <div class="flex items-center justify-between gap-4 pt-2">
        <?php if ($paginationView->onFirstPage()) { ?>
        <span class="inline-flex h-10 items-center justify-center rounded-full border border-[#004241]/12 px-4 text-sm font-medium text-[#004241]/35">
            Précédent
        </span>
        <?php } else { ?>
        <a href="<?= htmlspecialchars($paginationView->previousPageUrl()) ?>" class="inline-flex h-10 items-center justify-center rounded-full bg-[#004241] px-4 text-sm font-medium text-white transition hover:opacity-90">
            Précédent
        </a>
        <?php } ?>

        <span class="text-sm font-medium text-[#004241]/75">
            Page <?= $paginationView->currentPage() ?> sur <?= $paginationView->lastPage() ?>
        </span>

        <?php if ($paginationView->hasMorePages()) { ?>
        <a href="<?= htmlspecialchars($paginationView->nextPageUrl()) ?>" class="inline-flex h-10 items-center justify-center rounded-full bg-[#004241] px-4 text-sm font-medium text-white transition hover:opacity-90">
            Suivant
        </a>
        <?php } else { ?>
        <span class="inline-flex h-10 items-center justify-center rounded-full border border-[#004241]/12 px-4 text-sm font-medium text-[#004241]/35">
            Suivant
        </span>
        <?php } ?>
    </div>
    <?php } ?>
    <?php } ?>
</div>

<div id="delete-submission-modal" class="fixed inset-0 z-[140] hidden flex items-center justify-center bg-[#004241]/25 px-4">
    <div class="w-full max-w-md rounded-[24px] border border-[#DED8CE] bg-white p-6 shadow-[0_24px_60px_rgba(0,66,65,0.18)]">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold text-[#004241]">Supprimer l'article ?</h2>
                <p class="mt-2 text-sm leading-6 text-[#004241]/80">
                    Cette action supprimera définitivement <span id="delete-submission-title" class="font-semibold text-[#004241]"></span>.
                </p>
            </div>
            <button type="button" id="delete-submission-cancel-top" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-[#004241]/60 transition hover:bg-[#EBF1EF] hover:text-[#004241]" aria-label="Fermer">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="delete-submission-form" method="post" class="mt-6 flex justify-end gap-3">
            <?= csrf_field() ?>
            <?= method_field('DELETE') ?>
            <button type="button" id="delete-submission-cancel" class="inline-flex h-10 items-center justify-center rounded-full border border-[#004241]/15 bg-white px-4 text-sm font-semibold text-[#004241] transition hover:bg-[#EBF1EF]">
                Annuler
            </button>
            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-full bg-[#AE422E] px-4 text-sm font-semibold text-white transition hover:bg-[#963524]">
                Supprimer
            </button>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('delete-submission-modal');
    const form = document.getElementById('delete-submission-form');
    const titleEl = document.getElementById('delete-submission-title');
    const cancelBtn = document.getElementById('delete-submission-cancel');
    const cancelTopBtn = document.getElementById('delete-submission-cancel-top');
    const triggers = document.querySelectorAll('.js-delete-submission');

    if (!modal || !form || !titleEl || !triggers.length) return;

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form.removeAttribute('action');
        titleEl.textContent = '';
    }

    function openModal(deleteUrl, articleTitle) {
        form.setAttribute('action', deleteUrl);
        titleEl.textContent = articleTitle ? '"' + articleTitle + '"' : 'cet article';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    triggers.forEach((t) => {
        t.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            openModal(t.dataset.deleteUrl || '', t.dataset.articleTitle || '');
        });
    });
    cancelBtn?.addEventListener('click', closeModal);
    cancelTopBtn?.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
})();
</script>
