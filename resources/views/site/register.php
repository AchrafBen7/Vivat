<?php
$errors = $errors ?? [];
$old = $old ?? [];
$last_name = $old['last_name'] ?? '';
$first_name = $old['first_name'] ?? '';
$email = $old['email'] ?? '';
$termsAccepted = ! empty($old['terms_accepted']);
$hero_img = 'https://images.pexels.com/photos/3761509/pexels-photo-3761509.jpeg?auto=compress&cs=tinysrgb&w=900&h=1100&fit=crop';
$inputClass = 'h-11 w-full rounded-2xl border border-[#D6E1DD] bg-white px-4 text-sm text-[#004241] placeholder:text-[#004241]/42 outline-none transition focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/15';
?>

<div class="mx-auto grid w-full max-w-[1280px] gap-6 pb-6 lg:h-[calc(100svh-112px)] lg:max-h-[calc(100svh-112px)] lg:grid-cols-[1.02fr_0.98fr]">
    <section class="relative min-h-[320px] overflow-hidden rounded-[32px] lg:h-full lg:min-h-0">
        <img src="<?= htmlspecialchars($hero_img) ?>" alt="" class="absolute inset-0 h-full w-full object-cover" loading="eager">
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(0,0,0,0.12)_0%,rgba(0,66,65,0.22)_36%,rgba(0,32,31,0.82)_100%)]"></div>

        <a
            href="/"
            class="absolute left-6 top-6 z-10 inline-flex items-center justify-center gap-2 rounded-full bg-white/95 px-4 py-2.5 text-sm font-medium text-[#004241] transition hover:bg-white"
            aria-label="Retour"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour
        </a>

        <div class="absolute inset-x-0 bottom-0 z-10 p-6 md:p-8">
            <div class="max-w-[32rem] rounded-[28px] border border-white/10 bg-white/10 p-5 backdrop-blur-[16px]">
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-[#004241]">
                    Devenir rédacteur
                </span>
                <h1 class="mt-4 text-[2.15rem] font-semibold leading-[1.02] text-white md:text-[2.8rem]">
                    Écrire sur Vivat, simplement.
                </h1>
                <p class="mt-3 max-w-[28rem] text-[15px] leading-6 text-white/88 md:text-base">
                    Créez votre compte, préparez votre article et rejoignez un espace pensé pour publier dans l’univers éditorial Vivat.
                </p>

                <div class="mt-5 flex flex-wrap gap-2.5">
                    <span class="inline-flex items-center rounded-full bg-white/14 px-3.5 py-1.5 text-sm font-medium text-white ring-1 ring-white/18">
                        Brouillons enregistrables
                    </span>
                    <span class="inline-flex items-center rounded-full bg-white/14 px-3.5 py-1.5 text-sm font-medium text-white ring-1 ring-white/18">
                        Relecture éditoriale
                    </span>
                    <span class="inline-flex items-center rounded-full bg-white/14 px-3.5 py-1.5 text-sm font-medium text-white ring-1 ring-white/18">
                        Publication mise en avant
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[32px] border border-[#DCE5E1] bg-[linear-gradient(180deg,#F7FBFA_0%,#EEF4F1_100%)] p-6 shadow-[0_18px_48px_rgba(0,66,65,0.07)] md:p-8 lg:h-full lg:min-h-0 lg:overflow-hidden lg:p-8">
        <div class="max-w-[34rem]">
            <h2 class="text-[1.85rem] font-semibold leading-[1.02] text-[#004241] md:text-[2.15rem]">
                Créer votre compte rédacteur
            </h2>
            <p class="mt-2.5 max-w-[32rem] text-sm leading-6 text-[#004241]/72 md:text-[15px]">
                Entrez vos informations pour accéder à l’espace contributeur, rédiger vos contenus et suivre vos soumissions.
            </p>

            <form action="/register" method="post" class="mt-6 flex flex-col gap-3.5">
                <?= csrf_field() ?>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="last_name" class="mb-1.5 block text-sm font-medium text-[#004241]">Nom</label>
                        <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($last_name) ?>" placeholder="Votre nom" required class="<?= $inputClass ?>">
                        <?php if (! empty($errors['last_name'])) { ?>
                        <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['last_name']) ? $errors['last_name'][0] : $errors['last_name']) ?></p>
                        <?php } ?>
                    </div>

                    <div>
                        <label for="first_name" class="mb-1.5 block text-sm font-medium text-[#004241]">Prénom</label>
                        <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($first_name) ?>" placeholder="Votre prénom" required class="<?= $inputClass ?>">
                        <?php if (! empty($errors['first_name'])) { ?>
                        <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['first_name']) ? $errors['first_name'][0] : $errors['first_name']) ?></p>
                        <?php } ?>
                    </div>
                </div>

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-[#004241]">Email</label>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="vous@exemple.be" required class="<?= $inputClass ?>">
                    <?php if (! empty($errors['email'])) { ?>
                    <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['email']) ? $errors['email'][0] : $errors['email']) ?></p>
                    <?php } ?>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-[#004241]">Mot de passe</label>
                        <input type="password" name="password" id="password" placeholder="Choisissez un mot de passe" required class="<?= $inputClass ?>">
                        <?php if (! empty($errors['password'])) { ?>
                        <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['password']) ? $errors['password'][0] : $errors['password']) ?></p>
                        <?php } ?>
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-[#004241]">Confirmation</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirmez le mot de passe" required class="<?= $inputClass ?>">
                    </div>
                </div>

                <div class="rounded-[24px] bg-white/78 p-3.5">
                    <label for="terms_accepted" class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            name="terms_accepted"
                            id="terms_accepted"
                            value="1"
                            <?= $termsAccepted ? 'checked' : '' ?>
                            required
                            class="mt-1 h-4 w-4 rounded border-[#C7D7D2] text-[#004241] focus:ring-[#004241]/20"
                        >
                        <span class="text-sm leading-5 text-[#004241]/78">
                            J’accepte les <a href="/conditions" class="font-medium text-[#004241] underline hover:no-underline">Conditions d’utilisation</a> et la <a href="/confidentialite" class="font-medium text-[#004241] underline hover:no-underline">Politique de confidentialité</a>.
                        </span>
                    </label>
                    <?php if (! empty($errors['terms_accepted'])) { ?>
                    <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['terms_accepted']) ? $errors['terms_accepted'][0] : $errors['terms_accepted']) ?></p>
                    <?php } ?>
                </div>

                <button type="submit" class="mt-1 inline-flex h-12 w-full items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003130]">
                    Créer mon compte
                </button>
            </form>

            <div class="mt-6">
                <div class="flex items-center gap-3 text-[#004241]/45">
                    <span class="h-px flex-1 bg-[#004241]/10"></span>
                    <span class="text-xs font-medium uppercase tracking-[0.18em]">ou continuer avec</span>
                    <span class="h-px flex-1 bg-[#004241]/10"></span>
                </div>

                <a
                    href="#"
                    class="mt-5 inline-flex h-12 w-full items-center justify-center gap-3 rounded-2xl border border-[#D6E1DD] bg-white text-sm font-medium text-[#004241] transition hover:bg-[#F7FAF9]"
                    aria-label="Continuer avec Google"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continuer avec Google
                </a>

                <p class="mt-4 pb-2 text-sm text-[#004241]/78">
                    Déjà inscrit ? <a href="/login" class="font-medium text-[#004241] underline hover:no-underline">Se connecter</a>
                </p>
            </div>
        </div>
    </section>
</div>
