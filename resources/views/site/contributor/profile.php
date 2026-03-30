<?php
$user = $user ?? null;
$errors = $errors ?? [];
$old = $old ?? [];

$name = $old['name'] ?? ($user->name ?? '');
$email = $user->email ?? '';
$bio = $old['bio'] ?? ($user->bio ?? '');
$instagramUrl = $old['instagram_url'] ?? ($user->instagram_url ?? '');
$twitterUrl = $old['twitter_url'] ?? ($user->twitter_url ?? '');
$websiteUrl = $old['website_url'] ?? ($user->website_url ?? '');
$roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames() : collect();
$primaryRole = $roles instanceof \Illuminate\Support\Collection ? $roles->first() : null;
$roleLabel = match ($primaryRole) {
    'admin' => 'Administrateur',
    'contributor' => 'Redacteur',
    default => 'Membre',
};
$avatarUrl = $user->avatar ?? null;
$initials = collect(preg_split('/\s+/', trim((string) $name)) ?: [])
    ->filter()
    ->take(2)
    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
    ->implode('');
$initials = $initials !== '' ? $initials : 'V';
?>
<div class="w-full max-w-[856px] flex flex-col gap-8">
    <section class="flex items-center gap-5 min-h-20">
        <div class="relative shrink-0">
            <div class="w-20 h-20 rounded-full border-[3px] border-[#DED8CE] overflow-hidden bg-[#F3EFE7] flex items-center justify-center text-[#004241] text-xl font-semibold">
                <?php if ($avatarUrl): ?>
                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Photo de profil" class="w-full h-full object-cover">
                <?php else: ?>
                <span><?= htmlspecialchars($initials) ?></span>
                <?php endif; ?>
            </div>
            <button type="button" class="absolute right-[2px] bottom-[2px] w-4 h-4 rounded-full bg-[#1B4B3B] text-[#F3EFE7] flex items-center justify-center shadow-sm" aria-label="Changer la photo de profil">
                <svg class="w-[10px] h-[10px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h4l2-2h6l2 2h4v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm9 9a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>
            </button>
        </div>

        <div class="min-w-0 flex flex-col gap-3">
            <span class="inline-flex h-[21px] w-fit min-w-[116px] items-center justify-center rounded-full border border-[#1B4B3B4D] px-3 py-[2px] text-xs font-medium uppercase tracking-[0.06em] text-[#1B4B3B]">
                <?= htmlspecialchars($roleLabel) ?>
            </span>
            <h1 class="text-[#1B4B3B] text-[24px] leading-[30px] font-semibold"><?= htmlspecialchars($name) ?></h1>
        </div>
    </section>

    <div class="w-full h-7">
        <h2 class="text-[18px] leading-7 font-medium text-[#1B4B3B]">Modifier mon profil</h2>
    </div>

    <form action="<?= url('/contributor/profile') ?>" method="post" class="mt-[-16px] rounded-2xl border border-[#DED8CE66] bg-[#F8F6F2] p-5 flex flex-col gap-5">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="pt-[5px] flex flex-col gap-[9px]">
                <label for="name" class="text-xs font-medium uppercase tracking-[0.06em] text-[#004241]">Nom complet</label>
                <div>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?= htmlspecialchars($name) ?>"
                        class="w-full h-10 rounded-xl border border-[#DED8CE99] bg-[#F3EFE7] px-3 text-sm text-[#004241] outline-none focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/10"
                    >
                    <p class="mt-2 text-xs text-[#004241]/60">C’est le nom affiché publiquement avec vos articles.</p>
                    <?php if (!empty($errors['name'])): ?>
                    <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['name']) ? $errors['name'][0] : $errors['name']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pt-[5px] flex flex-col gap-[9px]">
                <label for="email" class="text-xs font-medium uppercase tracking-[0.06em] text-[#004241]">Email</label>
                <input
                    type="email"
                    id="email"
                    value="<?= htmlspecialchars($email) ?>"
                    readonly
                    class="w-full h-10 rounded-xl border border-[#DED8CE99] bg-[#F3EFE7] px-3 text-sm text-[#004241]/80 outline-none"
                >
            </div>
        </div>

        <div class="pt-[5px] flex flex-col gap-[9px]">
            <label for="bio" class="text-xs font-medium uppercase tracking-[0.06em] text-[#004241]">Biographie</label>
            <div>
                <textarea
                    id="bio"
                    name="bio"
                    rows="3"
                    class="min-h-20 w-full rounded-xl border border-[#DED8CE99] bg-[#F3EFE7] px-3 pt-2 pb-[30px] text-sm text-[#004241] outline-none focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/10"
                ><?= htmlspecialchars($bio) ?></textarea>
                <p class="mt-2 text-xs text-[#004241]/60">Présentez-vous en quelques lignes. Cette bio peut apparaître dans votre espace auteur.</p>
                <?php if (!empty($errors['bio'])): ?>
                <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['bio']) ? $errors['bio'][0] : $errors['bio']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="pt-[5px] flex flex-col gap-[9px]">
                <label for="twitter" class="text-xs font-medium uppercase tracking-[0.06em] text-[#004241]">Twitter</label>
                <div>
                    <input
                    type="url"
                    id="twitter"
                    name="twitter_url"
                    value="<?= htmlspecialchars($twitterUrl) ?>"
                    placeholder="https://twitter.com/votre-profil"
                    class="w-full h-10 rounded-xl border border-[#DED8CE99] bg-[#F3EFE7] px-3 text-sm text-[#004241] outline-none focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/10"
                    >
                    <p class="mt-2 text-xs text-[#004241]/60">Ajoutez un lien complet si vous souhaitez le partager.</p>
                    <?php if (!empty($errors['twitter_url'])): ?>
                    <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['twitter_url']) ? $errors['twitter_url'][0] : $errors['twitter_url']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pt-[5px] flex flex-col gap-[9px]">
                <label for="instagram" class="text-xs font-medium uppercase tracking-[0.06em] text-[#004241]">Instagram</label>
                <div>
                    <input
                    type="url"
                    id="instagram"
                    name="instagram_url"
                    value="<?= htmlspecialchars($instagramUrl) ?>"
                    placeholder="https://instagram.com/votre-profil"
                    class="w-full h-10 rounded-xl border border-[#DED8CE99] bg-[#F3EFE7] px-3 text-sm text-[#004241] outline-none focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/10"
                    >
                    <p class="mt-2 text-xs text-[#004241]/60">Ajoutez un lien complet si vous souhaitez le partager.</p>
                    <?php if (!empty($errors['instagram_url'])): ?>
                    <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['instagram_url']) ? $errors['instagram_url'][0] : $errors['instagram_url']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="pt-[5px] flex flex-col gap-[9px]">
                <label for="website" class="text-xs font-medium uppercase tracking-[0.06em] text-[#004241]">Site web</label>
                <div>
                    <input
                    type="url"
                    id="website"
                    name="website_url"
                    value="<?= htmlspecialchars($websiteUrl) ?>"
                    placeholder="https://votresite.be"
                    class="w-full h-10 rounded-xl border border-[#DED8CE99] bg-[#F3EFE7] px-3 text-sm text-[#004241] outline-none focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/10"
                    >
                    <p class="mt-2 text-xs text-[#004241]/60">Utilisez l’adresse complète de votre site ou portfolio.</p>
                    <?php if (!empty($errors['website_url'])): ?>
                    <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['website_url']) ? $errors['website_url'][0] : $errors['website_url']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-1">
            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-full bg-[#004241] px-7 text-sm font-medium leading-5 text-[#F3EFE7] hover:bg-[#003535] transition">
                Sauvegarder
            </button>
        </div>
    </form>
</div>
