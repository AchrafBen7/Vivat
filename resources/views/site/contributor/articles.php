<?php
$submissions = $submissions ?? [];

$statusStyles = [
    'draft' => [
        'label' => 'Brouillon',
        'pill' => 'background: #F3E8CC; color: #7A5A14; border: 1px solid rgba(122,90,20,0.18);',
        'dot' => '#C69214',
    ],
    'pending' => [
        'label' => 'En attente',
        'pill' => 'background: rgba(0,66,65,0.10); color: #004241; border: 1px solid rgba(0,66,65,0.16);',
        'dot' => '#004241',
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
<h1 class="font-medium text-[#004241] text-2xl mb-2">Mes articles</h1>
<p class="text-[#004241]/80 mb-8">Vos soumissions et brouillons</p>
<div class="mb-6 flex justify-end">
    <a href="<?= url('/contributor/new') ?>" class="inline-flex items-center justify-center rounded-full bg-[#004241] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#003535]">
        Créer un nouvel article
    </a>
</div>

<div>
    <?php if (empty($submissions)): ?>
    <p class="text-[#004241]/70">Vous n'avez pas encore soumis d'article.</p>
    <a href="<?= url('/contributor/new') ?>" class="mt-6 inline-flex h-12 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
        Rédiger votre premier article
    </a>
    <?php else: ?>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <?php foreach ($submissions as $sub): ?>
        <?php
        $status = $sub['status'] ?? 'draft';
        $statusStyle = $statusStyles[$status] ?? $statusStyles['draft'];
        $cover = $sub['cover_image_path'] ?? null;
        $fallbackImage = $status === 'pending'
            ? '/quotidien.jpg'
            : ($status === 'approved' ? '/chezsoi.jpg' : ($status === 'rejected' ? '/sante.jpg' : '/finance.jpg'));
        $coverSrc = $cover ?: $fallbackImage;
        $deleteUrl = $sub['delete_url'] ?? '#';
        ?>
        <a href="<?= htmlspecialchars($sub['preview_url'] ?? '#') ?>" class="group relative block h-[380px] overflow-hidden rounded-[30px] border border-[#004241]/10 bg-[#1E2D25] shadow-[0_18px_40px_rgba(0,66,65,0.08)] transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1">
            <img src="<?= htmlspecialchars($coverSrc) ?>" alt="<?= htmlspecialchars($sub['title']) ?>" class="absolute inset-0 h-full w-full object-cover transition-transform duration-[450ms] ease-in-out group-hover:scale-[1.03]">
            <div class="absolute inset-0 bg-gradient-to-t from-[#001F1F]/74 via-[#002F2F]/18 to-transparent"></div>

            <button
                type="button"
                class="js-delete-submission absolute left-5 top-5 z-10 inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/15 bg-[rgba(32,42,38,0.78)] text-white transition hover:bg-[rgba(152,38,38,0.88)]"
                data-delete-url="<?= htmlspecialchars($deleteUrl) ?>"
                data-article-title="<?= htmlspecialchars($sub['title']) ?>"
                aria-label="Supprimer cet article"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7h12M9 7V5.8c0-.66.54-1.2 1.2-1.2h3.6c.66 0 1.2.54 1.2 1.2V7m-7 0l.55 10.08A2 2 0 0010.55 19h2.9a2 2 0 001.99-1.92L16 7M10 11.25v4.5m4-4.5v4.5"/>
                </svg>
            </button>

            <div class="absolute right-5 top-5">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-[7px] text-xs font-medium" style="<?= htmlspecialchars($statusStyle['pill']) ?>">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: <?= htmlspecialchars($statusStyle['dot']) ?>"></span>
                    <?= htmlspecialchars($sub['status_label'] ?? $statusStyle['label']) ?>
                </span>
            </div>

            <div class="absolute inset-x-0 bottom-0 p-5">
                <div class="overflow-hidden rounded-[21px] border border-[rgba(230,230,230,0.20)] bg-[rgba(52,62,58,0.72)] p-[20px]">
                    <?php if (!empty($sub['category']['name'])): ?>
                    <span class="inline-flex items-center justify-center rounded-full border border-[rgba(230,230,230,0.25)] bg-[rgba(190,190,190,0.10)] px-4 py-[9px] text-sm font-medium text-white">
                        <?= htmlspecialchars($sub['category']['name']) ?>
                    </span>
                    <?php endif; ?>
                    <h2 class="mt-4 text-[22px] font-semibold leading-[1.35] text-white line-clamp-3"><?= htmlspecialchars($sub['title']) ?></h2>
                    <div class="mt-3 flex items-center gap-3 text-sm text-white/80">
                        <span><?= htmlspecialchars($sub['created_at'] ?? '') ?></span>
                        <?php if (!empty($sub['reading_time'])): ?>
                        <span>•</span>
                        <span><?= (int) $sub['reading_time'] ?> min</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($sub['excerpt'])): ?>
                    <p class="mt-3 line-clamp-2 text-sm leading-6 text-white/78">
                        <?= htmlspecialchars($sub['excerpt']) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<div id="delete-submission-modal" class="fixed inset-0 z-[140] hidden items-center justify-center bg-[#004241]/35 px-4">
    <div class="w-full max-w-md rounded-[28px] border border-[#DED8CE] bg-[#F8F6F2] p-6 shadow-[0_24px_60px_rgba(0,66,65,0.18)]">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-[22px] font-semibold leading-7 text-[#1B4B3B]">Supprimer l'article ?</h2>
                <p class="mt-3 text-sm leading-6 text-[#004241]/80">
                    Cette action supprimera définitivement <span id="delete-submission-title" class="font-semibold text-[#004241]"></span>.
                </p>
            </div>
            <button type="button" id="delete-submission-cancel-top" class="flex h-9 w-9 items-center justify-center rounded-full text-[#004241]/70 transition hover:bg-[#EBF1EF] hover:text-[#004241]" aria-label="Fermer la fenêtre">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="delete-submission-form" method="post" class="mt-6 flex justify-end gap-3">
            <?= csrf_field() ?>
            <?= method_field('DELETE') ?>
            <button type="button" id="delete-submission-cancel" class="inline-flex h-11 items-center justify-center rounded-full border border-[#004241]/12 bg-white px-5 text-sm font-semibold text-[#004241] transition hover:bg-[#EBF1EF]">
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
