<?php
$errors = $errors ?? [];
$old = $old ?? [];
$email = $old['email'] ?? '';
$hero_img = 'https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&w=900&h=1100&fit=crop';
$inputClass = 'h-11 w-full rounded-full border border-transparent bg-white px-5 text-sm text-[#004241] placeholder:text-gray-400 outline-none transition focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/25';
$locale = content_locale();
$t = $locale === 'nl'
    ? [
        'back' => 'Terug',
        'hero_title' => 'Terug naar je Vivat-ruimte.',
        'hero_text' => 'Log in om je concepten terug te vinden, je content te beheren en je inzendingen op te volgen.',
        'hero_meta' => 'Bewaarbare concepten · Redactionele opvolging · Uitgelichte publicatie',
        'title' => 'Inloggen',
        'subtitle' => 'Log in op je bijdragersaccount',
        'email' => 'E-mail',
        'password' => 'Wachtwoord',
        'forgot' => 'Wachtwoord vergeten?',
        'remember' => 'Onthoud mij',
        'submit' => 'Inloggen',
        'or' => 'of ga verder met',
        'google' => 'Verder met Google',
        'no_account' => 'Nog geen account?',
        'create' => 'Maak een account aan',
    ]
    : [
        'back' => 'Retour',
        'hero_title' => 'Reprendre votre espace Vivat.',
        'hero_text' => 'Connectez-vous pour retrouver vos brouillons, gérer vos contenus et suivre vos soumissions.',
        'hero_meta' => 'Brouillons enregistrables · Suivi éditorial · Publication mise en avant',
        'title' => 'Se connecter',
        'subtitle' => 'Connectez-vous à votre compte contributeur',
        'email' => 'Email',
        'password' => 'Mot de passe',
        'forgot' => 'Mot de passe oublié ?',
        'remember' => 'Se souvenir de moi',
        'submit' => 'Se connecter',
        'or' => 'ou continuer avec',
        'google' => 'Continuer avec Google',
        'no_account' => 'Pas encore de compte ?',
        'create' => 'Crée un compte',
    ];
?>

<div class="mx-auto grid w-full max-w-[1280px] items-stretch gap-6 pb-6 lg:min-h-[calc(100svh-136px)] lg:grid-cols-[1.02fr_0.98fr]">
    <section class="relative flex min-h-[560px] flex-col overflow-hidden rounded-[40px] border border-[#EBF1EF] shadow-[0_18px_48px_rgba(0,66,65,0.07)] lg:h-full lg:min-h-0">
        <img src="<?= htmlspecialchars($hero_img) ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-center" loading="eager">
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(255,255,255,0.06)_0%,rgba(255,255,255,0)_36%,rgba(255,255,255,0.08)_100%)]" aria-hidden="true"></div>
        <div
            class="pointer-events-none absolute inset-x-0 bottom-0 z-[1] h-[48%] min-h-[16rem] bg-[linear-gradient(to_top,rgba(235,241,239,0.95)_0%,rgba(235,241,239,0.84)_24%,rgba(235,241,239,0.54)_52%,rgba(235,241,239,0.16)_78%,transparent_100%)]"
            aria-hidden="true"
        ></div>
        <div
            class="pointer-events-none absolute inset-x-0 bottom-0 z-[1] h-[42%] min-h-[14rem] bg-white/10 backdrop-blur-[18px] [mask-image:linear-gradient(to_top,black_0%,rgba(0,0,0,0.95)_42%,rgba(0,0,0,0.58)_76%,transparent_100%)] [-webkit-mask-image:linear-gradient(to_top,black_0%,rgba(0,0,0,0.95)_42%,rgba(0,0,0,0.58)_76%,transparent_100%)]"
            aria-hidden="true"
        ></div>

        <a
            href="/"
            class="absolute left-5 top-5 z-10 inline-flex items-center justify-center gap-2 rounded-full bg-white/95 px-4 py-2.5 text-sm font-medium text-[#004241] shadow-md transition hover:bg-white md:left-6 md:top-6"
            aria-label="<?= htmlspecialchars($t['back']) ?>"
        >
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" transform="matrix(-1 0 0 1 24 0)" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            <?= htmlspecialchars($t['back']) ?>
        </a>

        <div class="relative z-[2] mt-auto px-5 pb-6 pt-16 md:px-7 md:pb-7 md:pt-20">
            <h1 class="font-sans text-[1.75rem] font-semibold leading-[1.08] text-[#004241] sm:text-[2rem] md:text-[2.25rem]">
                <?= htmlspecialchars($t['hero_title']) ?>
            </h1>
            <p class="mt-3 max-w-[27rem] text-[15px] leading-relaxed text-[#004241]/82">
                <?= htmlspecialchars($t['hero_text']) ?>
            </p>

            <p class="mt-4 max-w-[27rem] text-[13px] leading-relaxed text-[#004241]/52">
                <?= htmlspecialchars($t['hero_meta']) ?>
            </p>
        </div>
    </section>

    <section class="rounded-[40px] border border-[#EBF1EF] bg-[#EBF1EF] p-5 shadow-[0_18px_48px_rgba(0,66,65,0.07)] md:p-6 lg:flex lg:h-full lg:flex-col lg:px-8 lg:py-7">
        <div class="mx-auto flex w-full max-w-[34rem] flex-1 flex-col lg:max-w-[35rem] lg:justify-center">
            <div>
                <h2 class="text-[2rem] font-medium leading-[1.1] text-[#004241]">
                    <?= htmlspecialchars($t['title']) ?>
                </h2>
                <p class="mt-2 text-sm text-[#004241]/80">
                    <?= htmlspecialchars($t['subtitle']) ?>
                </p>
            </div>

            <form action="/login" method="post" class="mt-7 flex flex-col gap-4">
                <?= csrf_field() ?>

                <div class="grid gap-4">
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="<?= htmlspecialchars($t['email']) ?>" required class="<?= $inputClass ?>">
                    <?php if (! empty($errors['email'])) { ?>
                    <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['email']) ? $errors['email'][0] : $errors['email']) ?></p>
                    <?php } ?>
                </div>

                <div class="grid gap-4">
                    <input type="password" name="password" id="password" placeholder="<?= htmlspecialchars($t['password']) ?>" required class="<?= $inputClass ?>">
                    <?php if (! empty($errors['password'])) { ?>
                    <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['password']) ? $errors['password'][0] : $errors['password']) ?></p>
                    <?php } ?>
                </div>

                <div class="-mt-1 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <label for="remember" class="inline-flex items-center gap-3 text-sm leading-none text-[#004241]">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            value="1"
                            class="h-4 w-4 rounded border-[#004241]/20 text-[#004241] focus:ring-[#004241]/25"
                        >
                        <span><?= htmlspecialchars($t['remember']) ?></span>
                    </label>

                    <a href="<?= route('password.request') ?>" class="text-sm font-medium text-[#004241] underline underline-offset-4 hover:no-underline">
                        <?= htmlspecialchars($t['forgot']) ?>
                    </a>
                </div>

                <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
                    <?= htmlspecialchars($t['submit']) ?>
                </button>
            </form>

            <div class="mt-7 flex flex-col items-center">
                <div class="flex w-full items-center gap-3 text-[#004241]/45">
                    <span class="h-px flex-1 bg-[#D9E5E1]"></span>
                    <span class="text-xs font-medium"><?= htmlspecialchars($t['or']) ?></span>
                    <span class="h-px flex-1 bg-[#D9E5E1]"></span>
                </div>

                <a
                    href="<?= htmlspecialchars(route('auth.google.redirect')) ?>"
                    class="mt-5 inline-flex h-11 w-full items-center justify-center gap-3 rounded-full border border-gray-300 bg-white text-sm font-medium text-[#004241] transition hover:bg-gray-50"
                    aria-label="<?= htmlspecialchars($t['google']) ?>"
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
                    <?= htmlspecialchars($t['no_account']) ?> <a href="/register" class="font-medium text-[#004241] underline hover:no-underline"><?= htmlspecialchars($t['create']) ?></a>
                </p>
            </div>
        </div>
    </section>
</div>
