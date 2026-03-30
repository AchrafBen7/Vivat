<?php
$errors = $errors ?? [];
$old = $old ?? [];
$email = $old['email'] ?? '';
$hero_img = 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=900&h=1100&fit=crop';
$inputClass = 'h-10 w-full rounded-xl border border-gray-300 bg-white px-4 text-sm text-[#004241] placeholder:text-gray-400 outline-none transition focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/25';
?>

<div class="mx-auto grid w-full max-w-[1280px] items-stretch gap-6 pb-6 lg:min-h-[calc(100svh-136px)] lg:grid-cols-[1.02fr_0.98fr]">
    <section class="relative min-h-[320px] overflow-hidden rounded-[32px] lg:h-full lg:min-h-0">
        <img src="<?= htmlspecialchars($hero_img) ?>" alt="" class="absolute inset-0 h-full w-full object-cover" loading="eager">
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.06)_0%,rgba(0,0,0,0.14)_42%,rgba(0,0,0,0.52)_100%)]"></div>

        <a
            href="/"
            class="absolute left-5 top-5 z-10 inline-flex items-center justify-center gap-2 rounded-full bg-white/95 px-4 py-2.5 text-sm font-medium text-[#004241] transition hover:bg-white md:left-6 md:top-6"
            aria-label="Retour"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour
        </a>

        <div class="absolute inset-x-0 bottom-0 z-10 p-5 md:p-6">
            <div class="max-w-[31rem] rounded-[28px] border border-white/10 bg-white/10 p-5 backdrop-blur-[16px] md:p-6">
                <span class="inline-flex items-center rounded-full border border-[rgba(230,230,230,0.2)] bg-[rgba(190,190,190,0.1)] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-white backdrop-blur-[15px]">
                    Espace contributeur
                </span>
                <h1 class="mt-4 text-[2rem] font-semibold leading-[1.02] text-white md:text-[2.55rem]">
                    Reprendre votre espace Vivat.
                </h1>
                <p class="mt-3 max-w-[27rem] text-[15px] leading-6 text-white/88">
                    Connectez-vous pour retrouver vos brouillons, gérer vos contenus et suivre vos soumissions.
                </p>

                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full border border-[rgba(230,230,230,0.2)] bg-[rgba(190,190,190,0.1)] px-3.5 py-1.5 text-sm font-medium text-white backdrop-blur-[15px]">
                        Brouillons enregistrables
                    </span>
                    <span class="inline-flex items-center rounded-full border border-[rgba(230,230,230,0.2)] bg-[rgba(190,190,190,0.1)] px-3.5 py-1.5 text-sm font-medium text-white backdrop-blur-[15px]">
                        Suivi éditorial
                    </span>
                    <span class="inline-flex items-center rounded-full border border-[rgba(230,230,230,0.2)] bg-[rgba(190,190,190,0.1)] px-3.5 py-1.5 text-sm font-medium text-white backdrop-blur-[15px]">
                        Publication mise en avant
                    </span>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-[32px] border border-[#EBF1EF] bg-[#EBF1EF] p-5 shadow-[0_18px_48px_rgba(0,66,65,0.07)] md:p-6 lg:flex lg:h-full lg:flex-col lg:px-8 lg:py-7">
        <div class="mx-auto flex w-full max-w-[34rem] flex-1 flex-col lg:max-w-[35rem] lg:justify-center">
            <div>
                <h2 class="text-[2rem] font-medium leading-[1.1] text-[#004241]">
                    Se connecter
                </h2>
                <p class="mt-2 text-sm text-[#004241]/80">
                    Connectez-vous à votre compte contributeur
                </p>
            </div>

            <form action="/login" method="post" class="mt-7 flex flex-col gap-4">
                <?= csrf_field() ?>

                <div class="grid gap-4">
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required class="<?= $inputClass ?>">
                    <?php if (! empty($errors['email'])) { ?>
                    <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['email']) ? $errors['email'][0] : $errors['email']) ?></p>
                    <?php } ?>
                </div>

                <div class="grid gap-4">
                    <input type="password" name="password" id="password" placeholder="Mot de passe" required class="<?= $inputClass ?>">
                    <?php if (! empty($errors['password'])) { ?>
                    <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['password']) ? $errors['password'][0] : $errors['password']) ?></p>
                    <?php } ?>
                </div>

                <div class="flex items-start gap-3">
                    <label for="remember" class="flex items-start gap-3 text-sm leading-snug text-[#004241]">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            value="1"
                            class="mt-1 h-4 w-4 rounded-full border-gray-300 text-[#004241] focus:ring-[#004241]/25"
                        >
                        <span>Se souvenir de moi</span>
                    </label>
                </div>

                <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
                    Se connecter
                </button>
            </form>

            <div class="mt-7 flex flex-col items-center">
                <div class="flex w-full items-center gap-3 text-[#004241]/45">
                    <span class="h-px flex-1 bg-[#D9E5E1]"></span>
                    <span class="text-xs font-medium">ou continuer avec</span>
                    <span class="h-px flex-1 bg-[#D9E5E1]"></span>
                </div>

                <a
                    href="#"
                    class="mt-5 inline-flex h-10 w-full items-center justify-center gap-3 rounded-xl border border-gray-300 bg-white text-sm font-medium text-[#004241] transition hover:bg-gray-50"
                    aria-label="Continuer avec Google"
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Google
                </a>

                <p class="mt-6 text-sm text-[#004241]/78">
                    Pas encore de compte ? <a href="/register" class="font-medium text-[#004241] underline hover:no-underline">Crée un compte</a>
                </p>
            </div>
        </div>
    </section>
</div>
