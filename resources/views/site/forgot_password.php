<?php
$errors = $errors ?? [];
$old = $old ?? [];
$email = $old['email'] ?? '';
$status = $status ?? null;
$inputClass = 'h-11 w-full rounded-full border border-gray-300 bg-white px-5 text-sm text-[#004241] placeholder:text-gray-400 outline-none transition focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/25';
?>

<div class="mx-auto w-full max-w-[720px] pb-8">
    <section class="rounded-[40px] border border-[#EBF1EF] bg-[#EBF1EF] p-6 shadow-[0_18px_48px_rgba(0,66,65,0.07)] md:p-8">
        <a href="/login" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-medium text-[#004241] shadow-sm transition hover:bg-[#F8F6F2]">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19l-7-7 7-7"/></svg>
            Retour à la connexion
        </a>

        <div class="mt-6">
            <h1 class="text-[2rem] font-medium leading-[1.1] text-[#004241]">Mot de passe oublié</h1>
            <p class="mt-2 text-sm leading-6 text-[#004241]/80">
                Entrez votre adresse email. Si un compte existe, vous recevrez un lien pour choisir un nouveau mot de passe.
            </p>
        </div>

        <?php if ($status) { ?>
        <div class="mt-6 rounded-[20px] border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
            <?= htmlspecialchars($status) ?>
        </div>
        <?php } ?>

        <form action="<?= route('password.email') ?>" method="post" class="mt-6 flex flex-col gap-4">
            <?= csrf_field() ?>

            <div>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required class="<?= $inputClass ?>">
                <?php if (! empty($errors['email'])) { ?>
                <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['email']) ? $errors['email'][0] : $errors['email']) ?></p>
                <?php } ?>
            </div>

            <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
                Envoyer le lien de réinitialisation
            </button>
        </form>
    </section>
</div>
