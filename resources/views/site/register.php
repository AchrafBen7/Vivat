<?php
$errors = $errors ?? [];
$old = $old ?? [];
$last_name = $old['last_name'] ?? '';
$first_name = $old['first_name'] ?? '';
$email = $old['email'] ?? '';
// Image Unsplash paysage côtier (variante aléatoire via seed)
$unsplash_img = 'https://images.unsplash.com/photo-1505142468610-359e7d316be0?w=800&h=1000&fit=crop&q=80';
?>
<div class="pt-6 pb-12">
    <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-stretch justify-center max-w-[1320px] mx-auto">
        <!-- Colonne gauche : image Unsplash + bouton Retour -->
        <div class="relative w-full lg:w-[628px] flex-shrink-0 rounded-[30px] overflow-hidden" style="height: 850px; min-height: 400px;">
            <img src="<?= htmlspecialchars($unsplash_img) ?>" alt="" class="absolute inset-0 w-full h-full object-cover" loading="eager">
            <a href="/" class="absolute flex items-center justify-center gap-2.5 rounded-full text-[#004241] font-normal text-base leading-none hover:opacity-90 transition" style="top: 24px; left: 24px; width: 115px; height: 43px; padding: 12px 18px; background: #F3E8CC;">
                <svg class="w-5 h-5 flex-shrink-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                Retour
            </a>
        </div>

        <!-- Colonne droite : formulaire (182px marge entre le haut du carré et le titre) -->
        <div class="w-full lg:w-[628px] flex-shrink-0 rounded-[30px] border border-[#EBF1EF] bg-[#EBF1EF] flex flex-col" style="padding: 182px 77px 40px 77px;">
            <h1 class="font-medium text-[#004241] text-2xl mb-1" style="font-size: 28px; line-height: 36px;">Créer votre compte</h1>
            <p class="text-[#004241]/80 text-sm mb-8" style="font-size: 14px; line-height: 20px;">Entrez vos informations pour vous inscrire</p>

            <form action="/register" method="post" class="flex flex-col gap-4">
                <?= csrf_field() ?>

                <!-- Nom + Prénom -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($last_name) ?>" placeholder="Nom" required
                            class="w-full h-10 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241] text-sm">
                        <?php if (!empty($errors['last_name'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['last_name']) ? $errors['last_name'][0] : $errors['last_name']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($first_name) ?>" placeholder="Prénom" required
                            class="w-full h-10 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241] text-sm">
                        <?php if (!empty($errors['first_name'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['first_name']) ? $errors['first_name'][0] : $errors['first_name']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required
                        class="w-full h-10 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241] text-sm">
                    <?php if (!empty($errors['email'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['email']) ? $errors['email'][0] : $errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <!-- Mot de passe -->
                <div>
                    <input type="password" name="password" id="password" placeholder="Mot de passe" required
                        class="w-full h-10 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241] text-sm">
                    <?php if (!empty($errors['password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['password']) ? $errors['password'][0] : $errors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirmer le mot de passe" required
                        class="w-full h-10 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241] text-sm">
                </div>

                <!-- CGU -->
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="terms_accepted" id="terms_accepted" value="1" required
                        class="mt-1 w-4 h-4 rounded-full border-gray-300 text-[#004241] focus:ring-[#004241]/25">
                    <label for="terms_accepted" class="text-sm text-[#004241] leading-snug">
                        J'accepte les <a href="/conditions" class="text-[#004241] font-medium underline hover:no-underline">Conditions d'utilisation</a> et la <a href="/confidentialite" class="text-[#004241] font-medium underline hover:no-underline">Politique de confidentialité</a>
                    </label>
                </div>
                <?php if (!empty($errors['terms_accepted'])): ?>
                <p class="text-sm text-red-600"><?= htmlspecialchars(is_array($errors['terms_accepted']) ? $errors['terms_accepted'][0] : $errors['terms_accepted']) ?></p>
                <?php endif; ?>

                <!-- Bouton S'inscrire -->
                <button type="submit" class="w-full h-10 rounded-full bg-[#004241] text-white font-semibold text-sm leading-5 hover:bg-[#003535] transition">
                    S'inscrire
                </button>
            </form>

            <div class="flex flex-col items-center w-full mt-6">
                <p class="text-[#004241]/70 text-xs mb-4">ou continuer avec</p>

                <a href="#" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white text-[#004241] text-sm font-medium hover:bg-gray-50 transition mb-4" style="width: 280px; height: 40px; gap: 8px; padding: 12px 48.75px 12px 48.73px; border-width: 1px;" aria-label="Continuer avec Google">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Google
                </a>

                <p class="text-sm text-[#004241]">
                    Déjà un compte ? <a href="/login" class="font-medium underline hover:no-underline text-[#004241]">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</div>
