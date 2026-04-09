<?php
$payments = $payments ?? [];
$pendingQuotesCount = $pending_quotes_count ?? 0;
$pagination = $pagination ?? null;
$paginationView = $pagination ? $pagination->withQueryString() : null;
$statusStyles = [
    'emerald' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'sky' => 'bg-sky-50 text-sky-700 border border-sky-200',
    'rose' => 'bg-rose-50 text-rose-700 border border-rose-200',
    'amber' => 'bg-amber-50 text-amber-700 border border-amber-200',
    'slate' => 'bg-slate-100 text-slate-700 border border-slate-200',
];
?>
<div class="space-y-6">
    <div>
        <h1 class="font-semibold text-[#004241] text-2xl">Mes paiements</h1>
        <p class="text-[#004241]/70 text-sm mt-0.5">Retrouvez ici vos paiements en attente et vos paiements effectués.</p>
    </div>

    <?php if ($pendingQuotesCount > 0): ?>
    <div class="rounded-[20px] border border-amber-200 bg-amber-50 px-5 py-4">
        <div class="flex items-center gap-3">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500 text-sm font-bold text-white"><?= $pendingQuotesCount ?></span>
            <p class="text-sm font-semibold text-amber-900">
                <?= $pendingQuotesCount === 1 ? 'Un prix a été proposé pour votre article.' : "{$pendingQuotesCount} prix ont été proposés pour vos articles." ?>
                Finalisez le paiement pour déclencher la publication.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($payments)) { ?>
    <div class="rounded-[24px] border border-[#004241]/12 bg-[#EBF1EF]/50 p-8 text-center">
        <p class="text-[#004241]/75">Aucun paiement n’a encore été enregistré sur votre compte.</p>
    </div>
    <?php } else { ?>
    <div class="space-y-4">
        <?php foreach ($payments as $payment) { ?>
        <?php $style = $statusStyles[$payment['status_color'] ?? 'slate'] ?? $statusStyles['slate']; ?>
        <div class="rounded-[24px] border border-[#004241]/10 bg-white p-5 shadow-[0_4px_20px_rgba(0,66,65,0.06)]">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <?php if (! empty($payment['category_name'])) { ?>
                        <span class="rounded-full bg-[#EBF1EF] px-3 py-1 text-xs font-medium text-[#004241]">
                            <?= htmlspecialchars($payment['category_name']) ?>
                        </span>
                        <?php } ?>
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold <?= $style ?>">
                            <?= htmlspecialchars($payment['status_label']) ?>
                        </span>
                    </div>

                    <h2 class="mt-3 text-lg font-semibold text-[#004241] leading-snug">
                        <?= htmlspecialchars($payment['title']) ?>
                    </h2>

                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-[#004241]/65">
                        <span><?= htmlspecialchars($payment['amount_label']) ?></span>
                        <span>•</span>
                        <span><?= htmlspecialchars($payment['created_at'] ?? '') ?></span>
                        <span>•</span>
                        <span>Soumission : <?= htmlspecialchars($payment['submission_status_label']) ?></span>
                    </div>

                    <p class="mt-3 text-sm leading-6 text-[#004241]/80">
                        <?= htmlspecialchars($payment['status_description']) ?>
                    </p>

                    <?php if (! empty($payment['note_to_author'])) { ?>
                    <div class="mt-3 rounded-[18px] bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-900">
                        <span class="font-semibold">Note de la rédaction :</span>
                        <?= htmlspecialchars($payment['note_to_author']) ?>
                    </div>
                    <?php } ?>
                    <?php if (! empty($payment['expires_at'])) { ?>
                    <p class="mt-2 text-xs text-[#004241]/50">Offre valable jusqu'au <?= htmlspecialchars($payment['expires_at']) ?></p>
                    <?php } ?>
                    <?php if (! empty($payment['refund_reason'])) { ?>
                    <div class="mt-3 rounded-[18px] bg-[#F4F8F7] px-4 py-3 text-sm text-[#004241]/80">
                        <span class="font-semibold text-[#004241]">Motif du remboursement :</span>
                        <?= htmlspecialchars($payment['refund_reason']) ?>
                    </div>
                    <?php } ?>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2">
                    <?php if (! empty($payment['checkout_url'])): ?>
                    <form method="POST" action="<?= htmlspecialchars($payment['checkout_url']) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="inline-flex h-10 items-center justify-center rounded-full bg-amber-500 px-5 text-sm font-bold text-white transition hover:bg-amber-600">
                            Payer maintenant
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if (! empty($payment['refund_receipt_url'])) { ?>
                    <a href="<?= htmlspecialchars($payment['refund_receipt_url']) ?>" class="inline-flex h-10 items-center justify-center rounded-full bg-[#004241] px-4 text-sm font-semibold text-white transition hover:bg-[#003130]">
                        Voir le reçu
                    </a>
                    <?php } ?>
                    <?php if (! empty($payment['submission_preview_url'])) { ?>
                    <a href="<?= htmlspecialchars($payment['submission_preview_url']) ?>" class="inline-flex h-10 items-center justify-center rounded-full border border-[#004241]/15 px-4 text-sm font-semibold text-[#004241] transition hover:bg-[#EBF1EF]">
                        Voir la soumission
                    </a>
                    <?php } ?>
                    <?php if (! empty($payment['published_article_url'])) { ?>
                    <a href="<?= htmlspecialchars($payment['published_article_url']) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 items-center justify-center rounded-full border border-[#004241]/15 px-4 text-sm font-semibold text-[#004241] transition hover:bg-[#EBF1EF]">
                        Article publié
                    </a>
                    <?php } elseif (($payment['status'] ?? null) !== 'paid' && ! empty($payment['submission_edit_url'])) { ?>
                    <a href="<?= htmlspecialchars($payment['submission_edit_url']) ?>" class="inline-flex h-10 items-center justify-center rounded-full border border-[#7A5A14]/20 px-4 text-sm font-semibold text-[#7A5A14] transition hover:bg-[#F3E8CC]">
                        Reprendre la soumission
                    </a>
                    <?php } ?>
                </div>
            </div>
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
