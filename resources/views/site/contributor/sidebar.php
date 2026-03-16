<?php
$activeTab = $activeTab ?? 'articles';
?>
<aside class="flex-shrink-0 w-[249px] rounded-[30px] border border-gray-200 bg-white flex flex-col" style="min-height: 834px; padding: 54px 0 16px 0;">
    <nav class="flex flex-col px-4" style="gap: 18px;">
        <a href="<?= url('/contributor/dashboard') ?>" class="flex items-center gap-2 py-2 px-3 rounded-xl h-12 transition <?= $activeTab === 'articles' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-gray-100' ?>" style="padding: 8px 8px 8px 18px;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span class="font-medium text-base">Mes articles</span>
        </a>
        <a href="<?= url('/contributor/new') ?>" class="flex items-center gap-2 py-2 px-3 rounded-xl h-12 transition <?= $activeTab === 'new' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-gray-100' ?>" style="padding: 8px 8px 8px 18px;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            <span class="font-medium text-base">Nouvel article</span>
        </a>
        <a href="<?= url('/contributor/profile') ?>" class="flex items-center gap-2 py-2 px-3 rounded-xl h-12 transition <?= $activeTab === 'profile' ? 'bg-[#004241] text-white' : 'text-[#004241] hover:bg-gray-100' ?>" style="padding: 8px 8px 8px 18px;">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="font-medium text-base">Mon profil</span>
        </a>
    </nav>

    <div class="mt-auto px-4" style="gap: 8px; padding-bottom: 16px;">
        <a href="#" class="flex items-center gap-2 w-full rounded-xl text-[#004241] hover:bg-gray-100 transition text-sm" style="height: 32px; padding: 8px 18px;" title="Bientôt disponible">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Modifier mot de passe
        </a>
        <form action="<?= url('/logout') ?>" method="post" class="block">
            <?= csrf_field() ?>
            <button type="submit" class="flex items-center gap-2 w-full rounded-xl text-[#004241] hover:bg-gray-100 transition text-sm text-left" style="height: 32px; padding: 8px 18px;">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Déconnexion
            </button>
        </form>
        <a href="#" class="flex items-center gap-2 w-full rounded-xl text-red-600 hover:bg-red-50 transition text-sm mt-1" style="height: 32px; padding: 8px 18px;" title="Bientôt disponible">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Supprimer le compte
        </a>
    </div>
</aside>
