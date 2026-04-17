<?php
$payment = $payment ?? null;
$submission = $submission ?? null;
$t = fn (string $key, ?string $fallback = null) => __($key) !== $key ? __($key) : ($fallback ?? $key);
?>

<div class="space-y-6">
    <div class="flex flex-col gap-1">
        <h1 class="font-semibold text-[#004241] text-2xl"><?= htmlspecialchars($t('site.refund_receipt_title', 'Reçu de remboursement')) ?></h1>
        <p class="text-[#004241]/70 text-sm mt-0.5"><?= htmlspecialchars($t('site.refund_receipt_intro', 'Preuve du remboursement lié à votre article.')) ?></p>
    </div>

    <div class="rounded-[28px] border border-[#004241]/10 bg-white p-6 shadow-[0_8px_32px_rgba(0,66,65,0.08)]">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-[#004241]/10 pb-5">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#004241]/45">Vivat</p>
                <h2 class="mt-2 text-xl font-semibold text-[#004241]"><?= htmlspecialchars($t('site.refund_confirmation', 'Confirmation de remboursement')) ?></h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#004241]/75">
                    <?= htmlspecialchars($t('site.refund_confirmation_text', 'Ce document confirme que le paiement lié à votre soumission a bien été remboursé.')) ?>
                </p>
            </div>
            <span class="inline-flex rounded-full bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                <?= htmlspecialchars($t('site.payment_refunded', 'Paiement remboursé')) ?>
            </span>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div class="rounded-[20px] bg-[#F4F8F7] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#004241]/45"><?= htmlspecialchars($t('site.article', 'Article')) ?></p>
                <p class="mt-2 text-sm font-semibold text-[#004241]"><?= htmlspecialchars($submission?->title ?? $t('site.unknown_submission', 'Soumission inconnue')) ?></p>
            </div>
            <div class="rounded-[20px] bg-[#F4F8F7] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#004241]/45"><?= htmlspecialchars($t('site.refunded_amount', 'Montant remboursé')) ?></p>
                <p class="mt-2 text-sm font-semibold text-[#004241]">
                    <?php if ($payment) { ?>
                        <?= htmlspecialchars(number_format(($payment->amount ?? 0) / 100, 2, ',', ' ')) ?> <?= htmlspecialchars(strtoupper($payment->currency ?: 'EUR')) ?>
                    <?php } else { ?>
                        <?= htmlspecialchars($t('site.unknown', 'Inconnu')) ?>
                    <?php } ?>
                </p>
            </div>
            <div class="rounded-[20px] bg-[#F4F8F7] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#004241]/45"><?= htmlspecialchars($t('site.refund_date', 'Date de remboursement')) ?></p>
                <p class="mt-2 text-sm font-semibold text-[#004241]"><?= htmlspecialchars($payment?->updated_at?->format('d/m/Y H:i') ?? $t('site.unknown_feminine', 'Inconnue')) ?></p>
            </div>
            <div class="rounded-[20px] bg-[#F4F8F7] p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#004241]/45"><?= htmlspecialchars($t('site.reference', 'Référence')) ?></p>
                <p class="mt-2 break-all text-sm font-semibold text-[#004241]"><?= htmlspecialchars($payment?->stripe_refund_id ?? $t('site.not_available', 'Non disponible')) ?></p>
            </div>
        </div>

        <?php if (! empty($payment?->refund_reason)) { ?>
        <div class="mt-6 rounded-[20px] border border-[#004241]/10 bg-white p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#004241]/45"><?= htmlspecialchars($t('site.refund_reason_shared', 'Motif communiqué')) ?></p>
            <p class="mt-2 text-sm leading-6 text-[#004241]/80"><?= nl2br(htmlspecialchars($payment->refund_reason)) ?></p>
        </div>
        <?php } ?>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="<?= url('/contributor/dashboard') ?>" class="inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003130]">
                <?= htmlspecialchars($t('site.back_to_my_articles', 'Retour à mes articles')) ?>
            </a>
            <?php if (! empty($submission?->slug)) { ?>
            <a href="<?= route('contributor.articles.show', ['submission' => $submission->slug]) ?>" class="inline-flex h-11 items-center justify-center rounded-full border border-[#004241]/15 px-6 text-sm font-semibold text-[#004241] transition hover:bg-[#EBF1EF]">
                <?= htmlspecialchars($t('site.view_preview', "Voir l'aperçu")) ?>
            </a>
            <?php } ?>
        </div>
    </div>
</div>
