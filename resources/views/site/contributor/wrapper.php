<?php
$activeTab = $activeTab ?? 'articles';
$contributorContent = $contributorContent ?? '';
$submissionNotice = session('submission_notice');
?>
<div class="flex gap-8 pt-6 pb-12">
    <?= render_php_view('site.contributor.sidebar', ['activeTab' => $activeTab]) ?>
    <main class="flex-1 min-w-0">
        <?= $contributorContent ?>
    </main>
</div>

<?php if (is_array($submissionNotice) && ! empty($submissionNotice['message'])) { ?>
<div id="submission-notice-overlay" class="fixed inset-0 z-[120] flex items-center justify-center bg-[#006664]/25 px-4">
    <div class="w-full max-w-md rounded-[28px] border border-[#DED8CE] bg-[#F8F6F2] p-6 shadow-[0_24px_60px_rgba(0,66,65,0.18)]">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <div class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#006664] text-[#F8F6F2]">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-[20px] font-semibold leading-7 text-[#1B4B3B]"><?= htmlspecialchars($submissionNotice['title'] ?? 'Soumission envoyée') ?></h2>
                    <p class="mt-2 text-sm leading-6 text-[#006664]/80"><?= htmlspecialchars($submissionNotice['message']) ?></p>
                </div>
            </div>
            <button type="button" id="submission-notice-close" class="flex h-9 w-9 items-center justify-center rounded-full text-[#006664]/70 transition hover:bg-[#EBF1EF] hover:text-[#006664]" aria-label="Fermer la confirmation">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="button" id="submission-notice-confirm" class="inline-flex h-11 items-center justify-center rounded-full bg-[#006664] px-6 text-sm font-semibold text-[#F8F6F2] transition hover:bg-[#003535]">
                Compris
            </button>
        </div>
    </div>
</div>

<script>
(() => {
    const overlay = document.getElementById('submission-notice-overlay');
    const closeButton = document.getElementById('submission-notice-close');
    const confirmButton = document.getElementById('submission-notice-confirm');

    if (!overlay) {
        return;
    }

    function closeNotice() {
        overlay.remove();
    }

    closeButton?.addEventListener('click', closeNotice);
    confirmButton?.addEventListener('click', closeNotice);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            closeNotice();
        }
    });
})();
</script>
<?php } ?>
