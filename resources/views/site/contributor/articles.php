<?php
$submissions = $submissions ?? [];
$cardOverlay = 'absolute inset-0 box-border p-[18px] min-h-0 min-w-0';
$glassBox = 'rounded-[21px] flex w-full min-w-0 max-w-full shrink-0 flex-col gap-1.5 box-border p-[18px] bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)]';
$tagClass = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';
$tagGlassOnImage = $tagClass . ' bg-[rgba(190,190,190,0.1)] backdrop-blur-[15px] border border-[rgba(230,230,230,0.2)] text-white';
$articleMetaOnImage = 'text-white/80 text-xs';

$statusStyles = [
    'draft' => [
        'label' => 'Brouillon',
        'pill' => 'background: #F3E8CC; color: #7A5A14; border: 1px solid rgba(122,90,20,0.18);',
        'dot' => '#C69214',
    ],
    'pending' => [
        'label' => 'En attente',
        'pill' => 'background: rgba(0,66,65,0.10); color: #006664; border: 1px solid rgba(0,66,65,0.16);',
        'dot' => '#006664',
    ],
    'approved' => [
        'label' => 'Approuvé',
        'pill' => 'background: rgba(82,126,126,0.14); color: #2D5C5C; border: 1px solid rgba(82,126,126,0.18);',
        'dot' => '#527E7E',
    ],
    'rejected' => [
        'label' => 'Rejeté',
        'pill' => 'background: rgba(174,66,46,0.10); color: #AE422E; border: 1px solid rgba(174,66,46,0.16);',
        'dot' => '#AE422E',
    ],
];
?>
<h1 class="font-medium text-[#006664] text-2xl mb-2">Mes articles</h1>
<p class="text-[#006664]/80 mb-8">Vos soumissions et brouillons</p>
<?php if (! empty($submissions)) { ?>
<div class="mb-6 flex justify-end">
    <a href="<?= url('/contributor/new') ?>" class="inline-flex items-center justify-center rounded-full bg-[#006664] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#003535]">
        Créer un nouvel article
    </a>
</div>
<?php } ?>

<div>
    <?php if (empty($submissions)) { ?>
    <p class="text-[#006664]/70">Vous n'avez pas encore soumis d'article.</p>
    <a href="<?= url('/contributor/new') ?>" class="mt-6 inline-flex h-12 items-center justify-center rounded-full bg-[#006664] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
        Rédiger votre premier article
    </a>
    <?php } else { ?>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <?php foreach ($submissions as $sub) { ?>
        <?php
        $status = $sub['status'] ?? 'draft';
        $statusStyle = $statusStyles[$status] ?? $statusStyles['draft'];
        $cover = $sub['cover_image_url'] ?? null;
        $fallbackImage = $status === 'pending'
            ? '/quotidien.jpg'
            : ($status === 'approved' ? '/chezsoi.jpg' : ($status === 'rejected' ? '/sante.jpg' : '/finance.jpg'));
        $coverSrc = $cover ?: $fallbackImage;
        $deleteUrl = $sub['delete_url'] ?? '#';
        $editUrl = $sub['edit_url'] ?? '#';
        $hasReviewerNote = ! empty($sub['reviewer_notes']);
        ?>
        <div class="flex flex-col gap-3">
        <article
            class="group relative h-[380px] overflow-hidden rounded-[30px] border border-[#006664]/10 bg-[#1E2D25] shadow-[0_18px_40px_rgba(0,66,65,0.08)] transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1"
            onclick="window.location.href='<?= htmlspecialchars($sub['preview_url'] ?? '#', ENT_QUOTES) ?>'"
        >
            <img src="<?= htmlspecialchars($coverSrc) ?>" alt="<?= htmlspecialchars($sub['title']) ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.015]">
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>

            <div class="absolute left-5 top-5 z-10 flex items-center gap-3">
                <button
                    type="button"
                    class="js-delete-submission inline-flex items-center justify-center rounded-full bg-[rgba(174,66,46,0.92)] px-4 py-[7px] text-xs font-medium text-white shadow-[0_10px_24px_rgba(0,0,0,0.14)] transition hover:bg-[rgba(152,38,38,0.96)]"
                    data-delete-url="<?= htmlspecialchars($deleteUrl) ?>"
                    data-article-title="<?= htmlspecialchars($sub['title']) ?>"
                    aria-label="Supprimer cet article"
                >
                    Supprimer
                </button>

                <a href="<?= htmlspecialchars($editUrl) ?>" class="inline-flex items-center rounded-full px-3 py-[7px] text-xs font-medium text-white shadow-[0_8px_20px_rgba(0,0,0,0.16)] transition hover:opacity-90" style="<?= htmlspecialchars($statusStyle['pill']) ?> color: #FFFFFF;" onclick="event.stopPropagation();">
                    Modifier
                </a>
            </div>

            <div class="absolute right-5 top-5">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-[7px] text-xs font-medium" style="<?= htmlspecialchars($statusStyle['pill']) ?>">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: <?= htmlspecialchars($statusStyle['dot']) ?>"></span>
                    <?= htmlspecialchars($sub['status_label'] ?? $statusStyle['label']) ?>
                </span>
            </div>

            <div class="absolute left-5 top-[74px] z-10">
                <a href="<?= htmlspecialchars($editUrl) ?>" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-[rgba(32,42,38,0.78)] px-4 py-[10px] text-xs font-semibold text-white transition hover:bg-[rgba(255,255,255,0.18)]" onclick="event.stopPropagation();">
                    Modifier
                </a>
            </div>

            <div class="absolute inset-x-0 bottom-0 p-5">
                <div class="overflow-hidden rounded-[21px] border border-[rgba(230,230,230,0.20)] bg-[rgba(52,62,58,0.72)] p-[20px]">
                    <?php if (! empty($sub['category']['name'])) { ?>
                    <span class="inline-flex items-center justify-center rounded-full border border-[rgba(230,230,230,0.25)] bg-[rgba(190,190,190,0.10)] px-4 py-[9px] text-sm font-medium text-white">
                        <?= htmlspecialchars($sub['category']['name']) ?>
                    </span>
                    <?php } ?>
                    <h2 class="mt-4 text-[22px] font-semibold leading-[1.35] text-white line-clamp-3"><?= htmlspecialchars($sub['title']) ?></h2>
                    <div class="mt-3 flex items-center gap-3 text-sm !text-white/80">
                        <span class="!text-white/80"><?= htmlspecialchars($sub['created_at'] ?? '') ?></span>
                        <?php if (! empty($sub['reading_time'])) { ?>
                        <span class="!text-white/70">•</span>
                        <span class="!text-white/80"><?= (int) $sub['reading_time'] ?> min</span>
                        <?php } ?>
                    </div>
                    <?php if (! empty($sub['excerpt'])) { ?>
                    <p class="mt-3 line-clamp-2 text-sm leading-6" style="color: rgba(255, 255, 255, 0.78);">
                        <?= htmlspecialchars($sub['excerpt']) ?>
                    </p>
                    <?php } ?>
                </div>
            </div>
        </article>
        <?php if ($hasReviewerNote) { ?>
        <div class="rounded-[24px] border border-[#D6E3E1] bg-[#F4F8F7] px-5 py-4 text-[#006664] shadow-[0_10px_24px_rgba(0,66,65,0.05)]">
            <div class="flex items-center gap-2 text-sm font-semibold">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#006664] text-white">i</span>
                <span>Note de relecture</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-[#006664]/84"><?= nl2br(htmlspecialchars($sub['reviewer_notes'])) ?></p>
            <?php if (! empty($sub['reviewer_name']) || ! empty($sub['reviewed_at'])) { ?>
            <p class="mt-3 text-xs font-medium uppercase tracking-[0.14em] text-[#006664]/52">
                <?= htmlspecialchars(trim(($sub['reviewer_name'] ?? '').(! empty($sub['reviewed_at']) ? ' • '.$sub['reviewed_at'] : ''))) ?>
            </p>
            <?php } ?>
        </div>
        <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<div id="delete-submission-modal" class="fixed inset-0 z-[140] hidden items-center justify-center bg-[#006664]/35 px-4">
    <div class="w-full max-w-md rounded-[28px] border border-[#DED8CE] bg-[#F8F6F2] p-6 shadow-[0_24px_60px_rgba(0,66,65,0.18)]">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-[22px] font-semibold leading-7 text-[#1B4B3B]">Supprimer l'article ?</h2>
                <p class="mt-3 text-sm leading-6 text-[#006664]/80">
                    Cette action supprimera définitivement <span id="delete-submission-title" class="font-semibold text-[#006664]"></span>.
                </p>
            </div>
            <button type="button" id="delete-submission-cancel-top" class="flex h-9 w-9 items-center justify-center rounded-full text-[#006664]/70 transition hover:bg-[#EBF1EF] hover:text-[#006664]" aria-label="Fermer la fenêtre">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="delete-submission-form" method="post" class="mt-6 flex justify-end gap-3">
            <?= csrf_field() ?>
            <?= method_field('DELETE') ?>
            <button type="button" id="delete-submission-cancel" class="inline-flex h-11 items-center justify-center rounded-full border border-[#006664]/12 bg-white px-5 text-sm font-semibold text-[#006664] transition hover:bg-[#EBF1EF]">
                Annuler
            </button>
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#AE422E] px-5 text-sm font-semibold text-white transition hover:bg-[#963524]">
                Supprimer
            </button>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('delete-submission-modal');
    const form = document.getElementById('delete-submission-form');
    const title = document.getElementById('delete-submission-title');
    const cancelButton = document.getElementById('delete-submission-cancel');
    const cancelTopButton = document.getElementById('delete-submission-cancel-top');
    const triggers = document.querySelectorAll('.js-delete-submission');

    if (!modal || !form || !title || !triggers.length) {
        return;
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form.removeAttribute('action');
        title.textContent = '';
    }

    function openModal(deleteUrl, articleTitle) {
        form.setAttribute('action', deleteUrl);
        title.textContent = articleTitle ? '"' + articleTitle + '"' : 'cet article';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            openModal(trigger.dataset.deleteUrl || '', trigger.dataset.articleTitle || '');
        });
    });

    cancelButton?.addEventListener('click', closeModal);
    cancelTopButton?.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });
})();
</script>
