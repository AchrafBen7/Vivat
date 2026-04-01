<?php
$errors = $errors ?? [];
$old = $old ?? [];
$token = $token ?? '';
$email = $email ?? ($old['email'] ?? '');
$inputClass = 'h-11 w-full rounded-full border border-gray-300 bg-white px-5 text-sm text-[#004241] placeholder:text-gray-400 outline-none transition focus:border-[#004241] focus:ring-2 focus:ring-[#004241]/25';
?>

<div class="mx-auto w-full max-w-[720px] pb-8">
    <section class="rounded-[40px] border border-[#EBF1EF] bg-[#EBF1EF] p-6 shadow-[0_18px_48px_rgba(0,66,65,0.07)] md:p-8">
        <div>
            <h1 class="text-[2rem] font-medium leading-[1.1] text-[#004241]">Réinitialiser le mot de passe</h1>
            <p class="mt-2 text-sm leading-6 text-[#004241]/80">
                Choisissez un nouveau mot de passe sécurisé pour votre compte Vivat.
            </p>
        </div>

        <form action="<?= route('password.update') ?>" method="post" class="mt-6 flex flex-col gap-4">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required class="<?= $inputClass ?>">
                <?php if (! empty($errors['email'])) { ?>
                <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['email']) ? $errors['email'][0] : $errors['email']) ?></p>
                <?php } ?>
            </div>

            <div>
                <input type="password" name="password" id="password" placeholder="Nouveau mot de passe" required class="<?= $inputClass ?>">
                <p class="mt-2 text-xs leading-5 text-[#004241]/62">Minimum 12 caractères avec une majuscule, une minuscule, un chiffre et un symbole.</p>
                <?php if (! empty($errors['password'])) { ?>
                <p class="mt-2 text-sm text-[#AE422E]"><?= htmlspecialchars(is_array($errors['password']) ? $errors['password'][0] : $errors['password']) ?></p>
                <?php } ?>
            </div>

            <div>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirmer le mot de passe" required class="<?= $inputClass ?>">
            </div>

            <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-full bg-[#004241] px-6 text-sm font-semibold text-white transition hover:bg-[#003535]">
                Réinitialiser le mot de passe
            </button>
        </form>
    </section>
</div>
