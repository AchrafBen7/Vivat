<?php
$user = $user ?? null;
?>
<h1 class="font-medium text-[#004241] text-2xl mb-2">Mon profil</h1>
<p class="text-[#004241]/80 mb-8">Gérez vos informations personnelles</p>

<div class="rounded-[30px] bg-[#EBF1EF] border border-[#EBF1EF] p-8 max-w-2xl">
    <dl class="space-y-4">
        <div>
            <dt class="text-sm text-[#004241]/70">Nom</dt>
            <dd class="font-medium text-[#004241]"><?= htmlspecialchars($user->name ?? '') ?></dd>
        </div>
        <div>
            <dt class="text-sm text-[#004241]/70">Email</dt>
            <dd class="font-medium text-[#004241]"><?= htmlspecialchars($user->email ?? '') ?></dd>
        </div>
    </dl>
</div>
