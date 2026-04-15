<?php
$activeTab = $activeTab ?? 'articles';
$pendingQuotesCount = $pendingQuotesCount ?? 0;
$sidebarUser = $contributorUser ?? null;
$t = fn (string $key, ?string $fallback = null) => __($key) !== $key ? __($key) : ($fallback ?? $key);

$errorBag = session('errors');
$hasDeleteErrors = $sidebarUser && $errorBag instanceof \Illuminate\Support\ViewErrorBag
    && (
        $errorBag->has('delete_email')
        || $errorBag->has('delete_account')
        || $errorBag->has('current_password_delete')
        || $errorBag->has('delete_confirmation')
    );
$openDeleteAccountModal = $hasDeleteErrors && ($activeTab ?? '') !== 'profile';
$requiresPasswordForDeletion = $sidebarUser && empty($sidebarUser->google_id);
$sidebarAccountEmail = $sidebarUser ? (string) $sidebarUser->email : '';
$oldDeleteEmail = old('delete_email', '');

$deleteFieldError = static function (string $key) use ($errorBag): string {
    if (! $errorBag instanceof \Illuminate\Support\ViewErrorBag || ! $errorBag->has($key)) {
        return '';
    }

    $first = $errorBag->first($key);

    return is_string($first) ? $first : '';
};

$modalBodyText = __('site.delete_account_modal_body', ['email' => $sidebarAccountEmail]);
?>
<aside class="flex w-[260px] shrink-0 flex-col self-start sticky top-6 rounded-[24px] border border-[#004241]/10 bg-white shadow-[0_4px_20px_rgba(0,66,65,0.06)]">
    <nav class="flex flex-col p-3 gap-1">
        <a href="<?= url('/contributor/dashboard') ?>" class="flex items-center gap-3 rounded-[16px] h-12 px-4 transition-colors <?= $activeTab === 'articles' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-[#EBF1EF]' ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="font-medium text-[15px]"><?= htmlspecialchars($t('site.my_articles', 'Mes articles')) ?></span>
        </a>
        <a href="<?= url('/contributor/new') ?>" class="flex items-center gap-3 rounded-[16px] h-12 px-4 transition-colors <?= $activeTab === 'new' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-[#EBF1EF]' ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
            <span class="font-medium text-[15px]"><?= htmlspecialchars($t('site.new_article', 'Nouvel article')) ?></span>
        </a>
        <a href="<?= url('/contributor/payments') ?>" class="flex items-center gap-3 rounded-[16px] h-12 px-4 transition-colors <?= $activeTab === 'payments' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-[#EBF1EF]' ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M3.75 6h16.5A1.5 1.5 0 0121.75 7.5v9A1.5 1.5 0 0120.25 18H3.75a1.5 1.5 0 01-1.5-1.5v-9A1.5 1.5 0 013.75 6zm12 7.5h2.25"/></svg>
            <span class="font-medium text-[15px]"><?= htmlspecialchars($t('site.payments', 'Paiements')) ?></span>
            <?php if ($pendingQuotesCount > 0): ?>
            <span class="ml-auto flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-500 text-[11px] font-bold text-white <?= $activeTab === 'payments' ? 'bg-white text-[#004241]' : '' ?>"><?= $pendingQuotesCount ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= url('/contributor/profile') ?>" class="flex items-center gap-3 rounded-[16px] h-12 px-4 transition-colors <?= $activeTab === 'profile' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-[#EBF1EF]' ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            <span class="font-medium text-[15px]"><?= htmlspecialchars($t('site.my_profile', 'Mon profil')) ?></span>
        </a>
    </nav>

    <div class="mt-auto border-t border-[#004241]/8 p-3 flex flex-col gap-1">
        <a href="<?= url('/contributor/profile') ?>#current_password" class="flex items-center gap-3 rounded-[12px] h-10 px-4 text-[14px] font-medium text-[#004241]/75 hover:bg-[#EBF1EF] hover:text-[#004241] transition-colors">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            <?= htmlspecialchars($t('site.password', 'Mot de passe')) ?>
        </a>
        <form action="<?= url('/logout') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="flex w-full items-center gap-3 rounded-[12px] h-10 px-4 text-left text-[14px] font-medium text-[#004241]/75 hover:bg-[#EBF1EF] hover:text-[#004241] transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9h5.25M12 15.75v-7.5M8.25 9H3.375M3.375 9c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75M9 11.25l1.5-1.5 1.5 1.5"/></svg>
                <?= htmlspecialchars($t('site.logout', 'Déconnexion')) ?>
            </button>
        </form>
        <?php if ($sidebarUser) { ?>
        <button
            type="button"
            id="delete-account-open"
            class="flex w-full items-center gap-3 rounded-[12px] h-10 px-4 text-left text-[14px] font-medium text-[#AE422E]/90 hover:bg-[#AE422E]/5 hover:text-[#AE422E] transition-colors"
            aria-haspopup="dialog"
            aria-controls="delete-account-modal"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            <?= htmlspecialchars($t('site.delete_account', 'Supprimer le compte')) ?>
        </button>
        <?php } ?>
    </div>
</aside>

<?php if ($sidebarUser) { ?>
<div
    id="delete-account-modal"
    class="<?= $openDeleteAccountModal ? 'flex' : 'hidden' ?> fixed inset-0 z-[130] items-center justify-center bg-black/50 p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="delete-account-modal-title"
    aria-hidden="<?= $openDeleteAccountModal ? 'false' : 'true' ?>"
>
    <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-[24px] border border-[#E8C8C6] bg-white p-6 shadow-[0_24px_60px_rgba(0,66,65,0.18)]">
        <h2 id="delete-account-modal-title" class="text-[20px] font-semibold leading-7 text-[#8E2E2A]"><?= htmlspecialchars($t('site.delete_account_modal_title', 'Supprimer ce compte ?')) ?></h2>
        <p class="mt-3 text-sm leading-relaxed text-[#6A2420]/90"><?= htmlspecialchars($modalBodyText) ?></p>
        <p class="mt-2 text-sm leading-relaxed text-[#004241]/70"><?= htmlspecialchars($t('site.delete_account_warning', 'Cette action est irréversible. Vos données personnelles seront anonymisées, votre accès sera révoqué, mais les articles, paiements et décisions éditoriales pourront être conservés si une conservation légale ou comptable s’impose.')) ?></p>

        <form action="<?= url('/contributor/profile') ?>" method="post" class="mt-6 flex flex-col gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="form_type" value="delete_account">

            <?php if ($delAccErr = $deleteFieldError('delete_account')) { ?>
            <p class="text-sm text-red-600"><?= htmlspecialchars($delAccErr) ?></p>
            <?php } ?>

            <div class="flex flex-col gap-2">
                <label for="sidebar_delete_email" class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#8E2E2A]/70"><?= htmlspecialchars($t('site.confirm_your_email', 'Confirmez votre email')) ?></label>
                <input
                    type="email"
                    id="sidebar_delete_email"
                    name="delete_email"
                    value="<?= htmlspecialchars($oldDeleteEmail) ?>"
                    placeholder="<?= htmlspecialchars($sidebarAccountEmail) ?>"
                    autocomplete="email"
                    required
                    class="w-full h-11 rounded-[14px] border border-[#E8C8C6] bg-white px-4 text-sm text-[#6A2420] outline-none transition focus:border-[#8E2E2A] focus:ring-2 focus:ring-[#8E2E2A]/10"
                >
                <p class="text-xs leading-5 text-[#8E2E2A]/60"><?= htmlspecialchars($t('site.delete_email_help', 'Saisissez exactement l’adresse liée à ce compte pour confirmer l’opération.')) ?></p>
                <?php if ($emErr = $deleteFieldError('delete_email')) { ?>
                <p class="text-sm text-red-600"><?= htmlspecialchars($emErr) ?></p>
                <?php } ?>
            </div>

            <?php if ($requiresPasswordForDeletion) { ?>
            <div class="flex flex-col gap-2">
                <label for="sidebar_current_password_delete" class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#8E2E2A]/70"><?= htmlspecialchars($t('site.current_password', 'Mot de passe actuel')) ?></label>
                <input
                    type="password"
                    id="sidebar_current_password_delete"
                    name="current_password_delete"
                    autocomplete="current-password"
                    required
                    class="w-full h-11 rounded-[14px] border border-[#E8C8C6] bg-white px-4 text-sm text-[#6A2420] outline-none transition focus:border-[#8E2E2A] focus:ring-2 focus:ring-[#8E2E2A]/10"
                >
                <p class="text-xs leading-5 text-[#8E2E2A]/60"><?= htmlspecialchars($t('site.delete_password_help', 'Cette vérification protège votre compte contre une suppression lancée depuis une session ouverte.')) ?></p>
                <?php if ($pwErr = $deleteFieldError('current_password_delete')) { ?>
                <p class="text-sm text-red-600"><?= htmlspecialchars($pwErr) ?></p>
                <?php } ?>
            </div>
            <?php } ?>

            <label class="inline-flex items-start gap-3 rounded-[14px] border border-[#E8C8C6] bg-[#FFFBFA] px-4 py-3 text-sm leading-6 text-[#6A2420]">
                <input type="checkbox" name="delete_confirmation" value="1" class="mt-1 h-4 w-4 rounded border-[#D65B57] text-[#8E2E2A] focus:ring-[#8E2E2A]/20" <?= old('delete_confirmation') ? 'checked' : '' ?> required>
                <span><?= htmlspecialchars($t('site.delete_account_checkbox', 'Je confirme vouloir supprimer définitivement mon compte et perdre l’accès à l’espace rédacteur.')) ?></span>
            </label>
            <?php if ($chkErr = $deleteFieldError('delete_confirmation')) { ?>
            <p class="text-sm text-red-600"><?= htmlspecialchars($chkErr) ?></p>
            <?php } ?>

            <div class="mt-2 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end sm:gap-3">
                <button type="button" id="delete-account-cancel" class="inline-flex h-11 items-center justify-center rounded-full border border-[#004241]/20 bg-white px-5 text-sm font-semibold text-[#004241] transition hover:bg-[#EBF1EF]">
                    <?= htmlspecialchars($t('site.delete_account_modal_cancel', 'Annuler')) ?>
                </button>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#8E2E2A] px-5 text-sm font-semibold text-white transition hover:bg-[#73231F]">
                    <?= htmlspecialchars($t('site.delete_my_account', 'Supprimer mon compte')) ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('delete-account-modal');
    const openBtn = document.getElementById('delete-account-open');
    const cancelBtn = document.getElementById('delete-account-cancel');
    if (!modal || !openBtn) {
        return;
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        const first = modal.querySelector('input:not([type="checkbox"])');
        if (first) {
            window.setTimeout(function () { first.focus(); }, 0);
        }
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        openBtn.focus();
    }

    openBtn.addEventListener('click', function () {
        openModal();
    });

    cancelBtn?.addEventListener('click', closeModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    <?php if ($openDeleteAccountModal) { ?>
    openModal();
    <?php } ?>
})();
</script>
<?php } ?>
