<?php
$user = $user ?? null;
$resent = $resent ?? false;
?>

<div class="mx-auto grid w-full max-w-[980px] gap-6 pb-6 lg:min-h-[calc(100svh-136px)] lg:grid-cols-[1.02fr_0.98fr]">
    <section class="relative min-h-[320px] overflow-hidden rounded-[32px] bg-[#0d4f4b] p-6 md:p-8">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(255,255,255,0.16),transparent_42%),linear-gradient(180deg,rgba(255,255,255,0.06),rgba(0,0,0,0.12))]"></div>
        <div class="relative z-10 flex h-full flex-col justify-between">
            <div>
                <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-[#004241]">
                    Vérification email
                </span>
                <h1 class="mt-5 max-w-[18rem] text-[2.1rem] font-semibold leading-[1.02] text-white md:text-[2.7rem]">
                    Activez votre espace rédacteur.
                </h1>
                <p class="mt-4 max-w-[28rem] text-[15px] leading-7 text-white/86">
                    Nous avons envoyé un lien de confirmation à votre adresse email. Cette étape protège votre compte et sécurise vos futures soumissions.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-white/14 px-3.5 py-1.5 text-sm font-medium text-white ring-1 ring-white/18">
                    Accès sécurisé
                </span>
                <span class="inline-flex items-center rounded-full bg-white/14 px-3.5 py-1.5 text-sm font-medium text-white ring-1 ring-white/18">
                    Validation de votre identité
                </span>
                <span class="inline-flex items-center rounded-full bg-white/14 px-3.5 py-1.5 text-sm font-medium text-white ring-1 ring-white/18">
                    Publication encadrée
                </span>
            </div>
        </div>
    </section>

    <section class="rounded-[32px] border border-[#EBF1EF] bg-[#EBF1EF] p-6 shadow-[0_18px_48px_rgba(0,66,65,0.07)] md:p-8">
        <div class="mx-auto flex h-full max-w-[34rem] flex-col justify-center">
            <h2 class="text-[2rem] font-medium leading-[1.1] text-[#004241]">
                Vérifiez votre email
            </h2>
            <p class="mt-3 text-sm leading-6 text-[#004241]/78">
                <?php if ($user?->email): ?>
                    Un email a été envoyé à <strong><?= htmlspecialchars($user->email) ?></strong>.
                <?php else: ?>
                    Un email de vérification vient d'être envoyé.
                <?php endif; ?>
                Cliquez sur le lien reçu pour accéder pleinement à votre tableau de bord contributeur.
            </p>

            <?php if ($resent): ?>
                <div class="mt-5 rounded-2xl border border-[#CFE3DB] bg-white px-4 py-3 text-sm text-[#004241]">
                    Un nouveau lien de vérification vient d'être envoyé.
                </div>
            <?php endif; ?>

            <form action="<?= route('verification.send') ?>" method="post" class="mt-7">
                <?= csrf_field() ?>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
                    Renvoyer le lien
                </button>
            </form>

            <p class="mt-5 text-sm leading-6 text-[#004241]/72">
                Vous pourrez écrire, sauvegarder et gérer vos contenus, mais certaines actions resteront bloquées tant que votre adresse n'est pas validée.
            </p>

            <form action="<?= route('logout') ?>" method="post" class="mt-5">
                <?= csrf_field() ?>
                <button type="submit" class="inline-flex items-center justify-center rounded-full border border-[#C8D9D4] bg-white px-5 py-2.5 text-sm font-medium text-[#004241] transition hover:bg-[#F5FAF8]">
                    Se déconnecter
                </button>
            </form>
        </div>
    </section>
</div>
