<?php
$user = $user ?? null;
$errors = $errors ?? [];
$old = $old ?? [];
$t = fn (string $key, ?string $fallback = null) => __($key) !== $key ? __($key) : ($fallback ?? $key);

$name = $old['name'] ?? ($user->name ?? '');
$email = $user->email ?? '';
$bio = $old['bio'] ?? ($user->bio ?? '');
$instagramUrl = $old['instagram_url'] ?? ($user->instagram_url ?? '');
$twitterUrl = $old['twitter_url'] ?? ($user->twitter_url ?? '');
$websiteUrl = $old['website_url'] ?? ($user->website_url ?? '');
$roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames() : collect();
$primaryRole = $roles instanceof \Illuminate\Support\Collection ? $roles->first() : null;
$roleLabel = match ($primaryRole) {
    'admin' => $t('site.administrator', 'Administrateur'),
    'contributor' => $t('site.editor', 'Rédacteur'),
    default => $t('site.member', 'Membre'),
};
$avatarUrl = $user->avatar ?? null;
$requiresPasswordForDeletion = empty($user->google_id);
$initials = collect(preg_split('/\s+/', trim((string) $name)) ?: [])
    ->filter()
    ->take(2)
    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
    ->implode('');
$initials = $initials !== '' ? $initials : 'V';
$hasBio = trim((string) $bio) !== '';
$hasTwitter = trim((string) $twitterUrl) !== '';
$hasInstagram = trim((string) $instagramUrl) !== '';
$hasWebsite = trim((string) $websiteUrl) !== '';
$hasAvatar = ! empty($avatarUrl);
$hasVerifiedEmail = method_exists($user, 'hasVerifiedEmail') ? (bool) $user->hasVerifiedEmail() : true;
$profileCriteria = [
    trim((string) $name) !== '',
    $hasBio,
    $hasTwitter,
    $hasInstagram,
    $hasWebsite,
    $hasAvatar,
];
$profileCompletedCount = count(array_filter($profileCriteria));
$profileCompletionPercent = (int) round(($profileCompletedCount / max(count($profileCriteria), 1)) * 100);
$publicLinksCount = count(array_filter([$hasTwitter, $hasInstagram, $hasWebsite]));
$accountAccessLabel = empty($user->google_id) ? $t('site.password_account', 'Connexion par mot de passe') : $t('site.google_account', 'Connexion Google');
$memberSinceLabel = '';
if (! empty($user?->created_at) && $user->created_at instanceof \Carbon\CarbonInterface) {
    $memberSinceLabel = $user->created_at->format('d/m/Y');
}
$cardClass = 'rounded-[28px] border border-[#004241]/10 bg-white p-6 shadow-[0_8px_28px_rgba(0,66,65,0.06)]';
$softCardClass = 'rounded-[28px] border border-[#004241]/8 bg-[#F8FBFA] p-5';
$dangerCardClass = 'rounded-[28px] border border-[#D65B57]/18 bg-[#FFF8F7] p-6 shadow-[0_8px_28px_rgba(142,46,42,0.05)]';
$labelClass = 'text-[11px] font-semibold uppercase tracking-[0.16em] text-[#004241]/62';
$inputClass = 'w-full h-11 rounded-[16px] border border-[#D6E3E1] bg-[#F7FAF9] px-4 text-sm text-[#004241] outline-none transition focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/10';
$readonlyInputClass = 'w-full h-11 rounded-[16px] border border-[#D6E3E1] bg-[#EEF4F2] px-4 text-sm text-[#004241]/78 outline-none';
$textareaClass = 'min-h-[140px] w-full rounded-[20px] border border-[#D6E3E1] bg-[#F7FAF9] px-4 py-3 text-sm text-[#004241] outline-none transition focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/10';
$helpClass = 'mt-2 text-xs leading-5 text-[#004241]/58';
$errorClass = 'mt-2 text-sm text-red-600';
?>
<div class="flex w-full max-w-[920px] flex-col gap-6">
    <section class="overflow-hidden rounded-[32px] bg-[#EBF1EF] p-6 shadow-[0_14px_40px_rgba(0,66,65,0.08)] md:p-8">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_280px] xl:items-start">
            <div class="flex flex-col gap-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full border-[4px] border-white/85 bg-[#DCE8E4] text-[28px] font-semibold text-[#004241] shadow-[0_8px_24px_rgba(0,66,65,0.12)]">
                        <?php if ($avatarUrl): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Photo de profil" class="h-full w-full object-cover">
                        <?php else: ?>
                        <span><?= htmlspecialchars($initials) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-[#004241] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-white">
                                <?= htmlspecialchars($roleLabel) ?>
                            </span>
                            <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-medium text-[#004241]/78">
                                <?= htmlspecialchars($t('site.my_profile', 'Mon profil')) ?>
                            </span>
                        </div>
                        <h1 class="mt-4 text-[30px] font-semibold leading-[1.02] text-[#004241] sm:text-[36px]">
                            <?= htmlspecialchars($name) ?>
                        </h1>
                        <p class="mt-2 text-base text-[#004241]/72">
                            <?= htmlspecialchars($email) ?>
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1.5 text-xs font-medium text-[#004241]">
                                <?= htmlspecialchars($accountAccessLabel) ?>
                            </span>
                            <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium <?= $hasVerifiedEmail ? 'bg-[#DCEFE8] text-[#1F6A51]' : 'bg-[#F3E8CC] text-[#7A5A14]' ?>">
                                <?= htmlspecialchars($hasVerifiedEmail ? $t('site.email_verified', 'Email vérifié') : $t('site.email_not_verified', 'Email non vérifié')) ?>
                            </span>
                            <?php if ($memberSinceLabel !== ''): ?>
                            <span class="inline-flex items-center rounded-full bg-white/80 px-3 py-1.5 text-xs font-medium text-[#004241]/78">
                                <?= htmlspecialchars($t('site.member_since', 'Membre depuis')) ?> <?= htmlspecialchars($memberSinceLabel) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="<?= $softCardClass ?>">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#004241]/56"><?= htmlspecialchars($t('site.profile_completion', 'Complétion')) ?></p>
                        <p class="mt-2 text-[28px] font-semibold leading-none text-[#004241]"><?= $profileCompletionPercent ?>%</p>
                        <p class="mt-2 text-sm leading-6 text-[#004241]/66"><?= $profileCompletedCount ?>/<?= count($profileCriteria) ?> <?= htmlspecialchars($t('site.profile_points_completed', 'éléments remplis')) ?></p>
                    </div>
                    <div class="<?= $softCardClass ?>">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#004241]/56"><?= htmlspecialchars($t('site.public_links', 'Liens publics')) ?></p>
                        <p class="mt-2 text-[28px] font-semibold leading-none text-[#004241]"><?= $publicLinksCount ?></p>
                        <p class="mt-2 text-sm leading-6 text-[#004241]/66"><?= htmlspecialchars($t('site.shared_profiles_count', 'profils ou sites partagés')) ?></p>
                    </div>
                    <div class="<?= $softCardClass ?>">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#004241]/56"><?= htmlspecialchars($t('site.author_space', 'Espace auteur')) ?></p>
                        <p class="mt-2 text-sm font-medium leading-6 text-[#004241]"><?= htmlspecialchars($hasBio ? $t('site.author_profile_ready', 'Présentation prête pour votre espace auteur') : $t('site.author_profile_missing', 'Ajoutez une bio pour mieux présenter votre profil')) ?></p>
                    </div>
                </div>
            </div>

            <div class="rounded-[28px] border border-white/60 bg-white/72 p-5 backdrop-blur-sm">
                <h2 class="text-lg font-semibold text-[#004241]"><?= htmlspecialchars($t('site.quick_overview', 'Repères rapides')) ?></h2>
                <p class="mt-2 text-sm leading-6 text-[#004241]/68">
                    <?= htmlspecialchars($t('site.profile_overview_help', 'Mettez à jour vos informations publiques, sécurisez votre accès et gardez un profil propre avant vos prochaines soumissions.')) ?>
                </p>

                <div class="mt-5 h-2 overflow-hidden rounded-full bg-[#D7E5E1]">
                    <div class="h-full rounded-full bg-[#004241] transition-all duration-500" style="width: <?= max(0, min(100, $profileCompletionPercent)) ?>%"></div>
                </div>

                <div class="mt-5 space-y-3 text-sm text-[#004241]/74">
                    <div class="flex items-start justify-between gap-3 rounded-[18px] bg-[#F5FAF8] px-4 py-3">
                        <span><?= htmlspecialchars($t('site.public_name', 'Nom public')) ?></span>
                        <span class="font-medium text-[#004241]"><?= htmlspecialchars(trim((string) $name) !== '' ? $t('site.done', 'OK') : $t('site.to_complete', 'À compléter')) ?></span>
                    </div>
                    <div class="flex items-start justify-between gap-3 rounded-[18px] bg-[#F5FAF8] px-4 py-3">
                        <span><?= htmlspecialchars($t('site.biography', 'Biographie')) ?></span>
                        <span class="font-medium text-[#004241]"><?= htmlspecialchars($hasBio ? $t('site.done', 'OK') : $t('site.to_complete', 'À compléter')) ?></span>
                    </div>
                    <div class="flex items-start justify-between gap-3 rounded-[18px] bg-[#F5FAF8] px-4 py-3">
                        <span><?= htmlspecialchars($t('site.public_links', 'Liens publics')) ?></span>
                        <span class="font-medium text-[#004241]"><?= $publicLinksCount ?>/3</span>
                    </div>
                </div>

                <p class="mt-5 text-xs leading-5 text-[#004241]/56">
                    <?= htmlspecialchars($t('site.avatar_upload_coming_soon', 'La gestion avancée de la photo de profil arrivera bientôt.')) ?>
                </p>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.18fr)_minmax(320px,0.82fr)]">
        <form action="<?= url('/contributor/profile') ?>" method="post" class="<?= $cardClass ?> flex flex-col gap-6">
        <?= csrf_field() ?>

            <div class="flex flex-col gap-1">
                <h2 class="text-[22px] font-semibold leading-7 text-[#004241]"><?= htmlspecialchars($t('site.edit_profile', 'Modifier mon profil')) ?></h2>
                <p class="text-sm leading-6 text-[#004241]/68"><?= htmlspecialchars($t('site.profile_intro_help', 'Mettez à jour les informations visibles dans votre espace auteur et autour de vos articles.')) ?></p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="name" class="<?= $labelClass ?>"><?= htmlspecialchars($t('site.full_name', 'Nom complet')) ?></label>
                    <div>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?= htmlspecialchars($name) ?>"
                            class="<?= $inputClass ?>"
                        >
                        <p class="<?= $helpClass ?>"><?= htmlspecialchars($t('site.public_name_help', 'C’est le nom affiché publiquement avec vos articles.')) ?></p>
                        <?php if (!empty($errors['name'])): ?>
                        <p class="<?= $errorClass ?>"><?= htmlspecialchars(is_array($errors['name']) ? $errors['name'][0] : $errors['name']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="email" class="<?= $labelClass ?>">Email</label>
                    <div>
                        <input
                            type="email"
                            id="email"
                            value="<?= htmlspecialchars($email) ?>"
                            readonly
                            class="<?= $readonlyInputClass ?>"
                        >
                        <p class="<?= $helpClass ?>"><?= htmlspecialchars($t('site.current_login_email', 'Adresse actuellement utilisée pour vous connecter.')) ?></p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label for="bio" class="<?= $labelClass ?>"><?= htmlspecialchars($t('site.biography', 'Biographie')) ?></label>
                <div>
                    <textarea
                        id="bio"
                        name="bio"
                        rows="5"
                        class="<?= $textareaClass ?>"
                    ><?= htmlspecialchars($bio) ?></textarea>
                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs leading-5 text-[#004241]/58"><?= htmlspecialchars($t('site.bio_help', 'Présentez-vous en quelques lignes. Cette bio peut apparaître dans votre espace auteur.')) ?></p>
                        <span class="text-xs font-medium text-[#004241]/46"><?= mb_strlen(trim((string) $bio)) ?>/2000</span>
                    </div>
                    <?php if (!empty($errors['bio'])): ?>
                    <p class="<?= $errorClass ?>"><?= htmlspecialchars(is_array($errors['bio']) ? $errors['bio'][0] : $errors['bio']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="space-y-4 rounded-[24px] border border-[#004241]/8 bg-[#F8FBFA] p-5">
                <div>
                    <h3 class="text-base font-semibold text-[#004241]"><?= htmlspecialchars($t('site.public_links', 'Liens publics')) ?></h3>
                    <p class="mt-1 text-sm leading-6 text-[#004241]/64"><?= htmlspecialchars($t('site.links_help', 'Partagez vos profils ou votre portfolio si vous souhaitez les rendre visibles dans votre environnement auteur.')) ?></p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="flex flex-col gap-2">
                        <label for="twitter" class="<?= $labelClass ?>">Twitter</label>
                        <div>
                            <input
                                type="url"
                                id="twitter"
                                name="twitter_url"
                                value="<?= htmlspecialchars($twitterUrl) ?>"
                                placeholder="https://twitter.com/votre-profil"
                                class="<?= $inputClass ?>"
                            >
                            <p class="<?= $helpClass ?>"><?= htmlspecialchars($t('site.add_full_link_help', 'Ajoutez un lien complet si vous souhaitez le partager.')) ?></p>
                            <?php if (!empty($errors['twitter_url'])): ?>
                            <p class="<?= $errorClass ?>"><?= htmlspecialchars(is_array($errors['twitter_url']) ? $errors['twitter_url'][0] : $errors['twitter_url']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="instagram" class="<?= $labelClass ?>">Instagram</label>
                        <div>
                            <input
                                type="url"
                                id="instagram"
                                name="instagram_url"
                                value="<?= htmlspecialchars($instagramUrl) ?>"
                                placeholder="https://instagram.com/votre-profil"
                                class="<?= $inputClass ?>"
                            >
                            <p class="<?= $helpClass ?>"><?= htmlspecialchars($t('site.add_full_link_help', 'Ajoutez un lien complet si vous souhaitez le partager.')) ?></p>
                            <?php if (!empty($errors['instagram_url'])): ?>
                            <p class="<?= $errorClass ?>"><?= htmlspecialchars(is_array($errors['instagram_url']) ? $errors['instagram_url'][0] : $errors['instagram_url']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="website" class="<?= $labelClass ?>">Site web</label>
                        <div>
                            <input
                                type="url"
                                id="website"
                                name="website_url"
                                value="<?= htmlspecialchars($websiteUrl) ?>"
                                placeholder="https://votresite.be"
                                class="<?= $inputClass ?>"
                            >
                            <p class="<?= $helpClass ?>"><?= htmlspecialchars($t('site.website_help', 'Utilisez l’adresse complète de votre site ou portfolio.')) ?></p>
                            <?php if (!empty($errors['website_url'])): ?>
                            <p class="<?= $errorClass ?>"><?= htmlspecialchars(is_array($errors['website_url']) ? $errors['website_url'][0] : $errors['website_url']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end pt-1">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-7 text-sm font-semibold text-[#F3EFE7] transition hover:bg-[#003535]">
                    <?= htmlspecialchars($t('site.save', 'Sauvegarder')) ?>
                </button>
            </div>
        </form>

        <div class="flex flex-col gap-6">
            <form action="<?= url('/contributor/profile') ?>" method="post" class="<?= $cardClass ?> flex flex-col gap-5">
                <?= csrf_field() ?>
                <input type="hidden" name="form_type" value="password">

                <div class="flex flex-col gap-1">
                    <h2 class="text-[22px] font-semibold leading-7 text-[#004241]"><?= htmlspecialchars($t('site.change_password', 'Changer mon mot de passe')) ?></h2>
                    <p class="text-sm leading-6 text-[#004241]/68"><?= htmlspecialchars($t('site.password_panel_help', 'Mettez à jour vos identifiants pour garder votre espace rédacteur sécurisé.')) ?></p>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="current_password" class="<?= $labelClass ?>"><?= htmlspecialchars($t('site.current_password', 'Mot de passe actuel')) ?></label>
                        <div>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="<?= $inputClass ?>"
                            >
                            <?php if (!empty($errors['current_password'])): ?>
                            <p class="<?= $errorClass ?>"><?= htmlspecialchars(is_array($errors['current_password']) ? $errors['current_password'][0] : $errors['current_password']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label for="new_password" class="<?= $labelClass ?>"><?= htmlspecialchars($t('site.new_password', 'Nouveau mot de passe')) ?></label>
                            <div>
                                <input
                                    type="password"
                                    id="new_password"
                                    name="password"
                                    class="<?= $inputClass ?>"
                                >
                                <p class="<?= $helpClass ?>"><?= htmlspecialchars($t('site.password_rules_help', 'Minimum 12 caractères avec majuscule, minuscule, chiffre et symbole.')) ?></p>
                                <?php if (!empty($errors['password'])): ?>
                                <p class="<?= $errorClass ?>"><?= htmlspecialchars(is_array($errors['password']) ? $errors['password'][0] : $errors['password']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2">
                            <label for="password_confirmation" class="<?= $labelClass ?>"><?= htmlspecialchars($t('site.confirmation', 'Confirmation')) ?></label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="<?= $inputClass ?>"
                            >
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
                        <?= htmlspecialchars($t('site.update_password', 'Mettre à jour le mot de passe')) ?>
                    </button>
                </div>
            </form>

            <form action="<?= url('/contributor/profile') ?>" method="post" class="<?= $dangerCardClass ?> flex flex-col gap-5">
        <?= csrf_field() ?>
        <input type="hidden" name="form_type" value="delete_account">

                <div class="flex flex-col gap-2">
                    <h2 class="text-[22px] font-semibold leading-7 text-[#8E2E2A]"><?= htmlspecialchars($t('site.delete_my_account', 'Supprimer mon compte')) ?></h2>
                    <p class="text-sm leading-6 text-[#8E2E2A]/80">
                        <?= htmlspecialchars($t('site.delete_account_warning', 'Cette action est irréversible. Vos données personnelles seront anonymisées, votre accès sera révoqué, mais les articles, paiements et décisions éditoriales pourront être conservés si une conservation légale ou comptable s’impose.')) ?>
                    </p>
                    <?php if (!empty($errors['delete_account'])): ?>
                    <p class="text-sm text-red-600"><?= htmlspecialchars(is_array($errors['delete_account']) ? $errors['delete_account'][0] : $errors['delete_account']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="delete_email" class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#8E2E2A]/70"><?= htmlspecialchars($t('site.confirm_your_email', 'Confirmez votre email')) ?></label>
                        <div>
                            <input
                                type="email"
                                id="delete_email"
                                name="delete_email"
                                value="<?= htmlspecialchars($old['delete_email'] ?? '') ?>"
                                placeholder="<?= htmlspecialchars($email) ?>"
                                class="w-full h-11 rounded-[16px] border border-[#E8C8C6] bg-white px-4 text-sm text-[#6A2420] outline-none transition focus:border-[#8E2E2A] focus:ring-2 focus:ring-[#8E2E2A]/10"
                            >
                            <p class="mt-2 text-xs leading-5 text-[#8E2E2A]/60"><?= htmlspecialchars($t('site.delete_email_help', 'Saisissez exactement l’adresse liée à ce compte pour confirmer l’opération.')) ?></p>
                            <?php if (!empty($errors['delete_email'])): ?>
                            <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['delete_email']) ? $errors['delete_email'][0] : $errors['delete_email']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($requiresPasswordForDeletion): ?>
                    <div class="flex flex-col gap-2">
                        <label for="current_password_delete" class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#8E2E2A]/70"><?= htmlspecialchars($t('site.current_password', 'Mot de passe actuel')) ?></label>
                        <div>
                            <input
                                type="password"
                                id="current_password_delete"
                                name="current_password_delete"
                                class="w-full h-11 rounded-[16px] border border-[#E8C8C6] bg-white px-4 text-sm text-[#6A2420] outline-none transition focus:border-[#8E2E2A] focus:ring-2 focus:ring-[#8E2E2A]/10"
                            >
                            <p class="mt-2 text-xs leading-5 text-[#8E2E2A]/60"><?= htmlspecialchars($t('site.delete_password_help', 'Cette vérification protège votre compte contre une suppression lancée depuis une session ouverte.')) ?></p>
                            <?php if (!empty($errors['current_password_delete'])): ?>
                            <p class="mt-2 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['current_password_delete']) ? $errors['current_password_delete'][0] : $errors['current_password_delete']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <label class="inline-flex items-start gap-3 rounded-[18px] border border-[#E8C8C6] bg-white px-4 py-3 text-sm leading-6 text-[#6A2420]">
                    <input type="checkbox" name="delete_confirmation" value="1" class="mt-1 h-4 w-4 rounded border-[#D65B57] text-[#8E2E2A] focus:ring-[#8E2E2A]/20">
                    <span><?= htmlspecialchars($t('site.delete_account_checkbox', 'Je confirme vouloir supprimer définitivement mon compte et perdre l’accès à l’espace rédacteur.')) ?></span>
                </label>
                <?php if (!empty($errors['delete_confirmation'])): ?>
                <p class="text-sm text-red-600"><?= htmlspecialchars(is_array($errors['delete_confirmation']) ? $errors['delete_confirmation'][0] : $errors['delete_confirmation']) ?></p>
                <?php endif; ?>

                <div class="flex justify-end pt-1">
                    <button
                        type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-full bg-[#8E2E2A] px-6 text-sm font-semibold text-white transition hover:bg-[#73231F]"
                        onclick="return window.confirm('<?= htmlspecialchars($t('site.delete_account_confirm', 'Confirmez-vous la suppression définitive de votre compte ?')) ?>');"
                    >
                        <?= htmlspecialchars($t('site.delete_my_account', 'Supprimer mon compte')) ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
