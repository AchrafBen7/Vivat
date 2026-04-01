<?php
$activeTab = $activeTab ?? 'articles';
?>
<aside class="flex w-[260px] shrink-0 flex-col rounded-[24px] border border-[#004241]/10 bg-white shadow-[0_4px_20px_rgba(0,66,65,0.06)]">
    <nav class="flex flex-col p-3 gap-1">
        <a href="<?= url('/contributor/dashboard') ?>" class="flex items-center gap-3 rounded-[16px] h-12 px-4 transition-colors <?= $activeTab === 'articles' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-[#EBF1EF]' ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="font-medium text-[15px]">Mes articles</span>
        </a>
        <a href="<?= url('/contributor/new') ?>" class="flex items-center gap-3 rounded-[16px] h-12 px-4 transition-colors <?= $activeTab === 'new' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-[#EBF1EF]' ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
            <span class="font-medium text-[15px]">Nouvel article</span>
        </a>
        <a href="<?= url('/contributor/payments') ?>" class="flex items-center gap-3 rounded-[16px] h-12 px-4 transition-colors <?= $activeTab === 'payments' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-[#EBF1EF]' ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M3.75 6h16.5A1.5 1.5 0 0121.75 7.5v9A1.5 1.5 0 0120.25 18H3.75a1.5 1.5 0 01-1.5-1.5v-9A1.5 1.5 0 013.75 6zm12 7.5h2.25"/></svg>
            <span class="font-medium text-[15px]">Paiements</span>
        </a>
        <a href="<?= url('/contributor/profile') ?>" class="flex items-center gap-3 rounded-[16px] h-12 px-4 transition-colors <?= $activeTab === 'profile' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-[#EBF1EF]' ?>">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
            <span class="font-medium text-[15px]">Mon profil</span>
        </a>
    </nav>

    <div class="mt-auto border-t border-[#004241]/8 p-3 flex flex-col gap-1">
        <a href="<?= url('/contributor/profile') ?>#current_password" class="flex items-center gap-3 rounded-[12px] h-10 px-4 text-[14px] font-medium text-[#004241]/75 hover:bg-[#EBF1EF] hover:text-[#004241] transition-colors">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
            Mot de passe
        </a>
        <form action="<?= url('/logout') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="flex w-full items-center gap-3 rounded-[12px] h-10 px-4 text-left text-[14px] font-medium text-[#004241]/75 hover:bg-[#EBF1EF] hover:text-[#004241] transition-colors">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9h5.25M12 15.75v-7.5M8.25 9H3.375M3.375 9c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75M9 11.25l1.5-1.5 1.5 1.5"/></svg>
                Déconnexion
            </button>
        </form>
        <a href="#" class="flex items-center gap-3 rounded-[12px] h-10 px-4 text-[14px] font-medium text-[#AE422E]/90 hover:bg-[#AE422E]/5 hover:text-[#AE422E] transition-colors" title="Bientôt disponible">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
            Supprimer le compte
        </a>
    </div>
</aside>
