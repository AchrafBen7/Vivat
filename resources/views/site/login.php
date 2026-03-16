<?php
$errors = $errors ?? [];
$old = $old ?? [];
$email = $old['email'] ?? '';
$unsplash_img = 'https://images.unsplash.com/photo-1505142468610-359e7d316be0?w=800&h=400&fit=crop&q=80';
?>
<div class="pt-6 pb-12 px-4">
    <div class="max-w-[756px] mx-auto flex flex-col gap-[29px]">
        <!-- Carré image en haut -->
        <div class="relative w-full rounded-[30px] overflow-hidden" style="height: 190px;">
            <img src="<?= htmlspecialchars($unsplash_img) ?>" alt="" class="absolute inset-0 w-full h-full object-cover" loading="eager">
            <a href="/" class="absolute flex items-center justify-center gap-2.5 rounded-full text-[#004241] font-normal text-base leading-none hover:opacity-90 transition" style="top: 24px; left: 24px; padding: 12px 18px; background: #F3E8CC;">
                <svg class="w-5 h-5 flex-shrink-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                Retour
            </a>
        </div>

        <!-- Carré formulaire (72px haut, 140px gauche et droite pour centrer) -->
        <div class="w-full rounded-[30px] border border-[#EBF1EF] bg-[#EBF1EF] flex flex-col items-center" style="min-height: 631px; padding: 72px 140px 40px 140px;">
            <div class="w-full flex flex-col items-center" style="max-width: 474px;">
            <h1 class="font-semibold text-[#1B4B3B] mb-1 w-full text-left" style="font-size: 24px; line-height: 36px;">Se connecter</h1>
            <p class="text-[#004241]/80 text-sm mb-7 w-full text-left" style="margin-bottom: 28px;">Connectez-vous à votre compte contributeur</p>

            <form action="/login" method="post" class="flex flex-col w-full" style="gap: 16px;">
                <?= csrf_field() ?>

                <div>
                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required
                        class="w-full h-10 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241] text-sm">
                    <?php if (!empty($errors['email'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['email']) ? $errors['email'][0] : $errors['email']) ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <input type="password" name="password" id="password" placeholder="Mot de passe" required
                        class="w-full h-10 pl-4 pr-4 rounded-xl border border-gray-300 bg-white text-[#004241] placeholder:text-gray-400 outline-none focus:ring-2 focus:ring-[#004241]/25 focus:border-[#004241] text-sm">
                    <?php if (!empty($errors['password'])): ?>
                    <p class="mt-1 text-sm text-red-600"><?= htmlspecialchars(is_array($errors['password']) ? $errors['password'][0] : $errors['password']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex items-start gap-3">
                    <input type="checkbox" name="remember" id="remember" value="1"
                        class="mt-1 w-4 h-4 rounded-full border-gray-300 text-[#004241] focus:ring-[#004241]/25">
                    <label for="remember" class="text-sm text-[#004241] leading-snug">
                        Se souvenir de moi
                    </label>
                </div>

                <button type="submit" class="w-full h-10 rounded-full bg-[#004241] text-white font-semibold text-sm leading-5 hover:bg-[#003535] transition">
                    Se connecter
                </button>
            </form>

            <div class="flex flex-col items-center w-full mt-6">
                <p class="text-[#004241]/70 text-xs mb-4">ou continuer avec</p>

                <a href="#" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white text-[#004241] text-sm font-medium hover:bg-gray-50 transition mb-4" style="width: 280px; height: 40px; gap: 8px; padding: 12px 48.75px 12px 48.73px; border-width: 1px;" aria-label="Continuer avec Google">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Google
                </a>

                <p class="text-sm text-[#004241]">
                    Pas encore de compte ? <a href="/register" class="font-medium underline hover:no-underline text-[#004241]">Crée un compte</a>
                </p>
            </div>
            </div>
        </div>
    </div>
</div>
