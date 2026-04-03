<?php
$publicationPriceEur = $publication_price_eur ?? 15;
$hero_img = 'https://images.pexels.com/photos/3761509/pexels-photo-3761509.jpeg?auto=compress&cs=tinysrgb&w=1280&h=520&fit=crop';
$t = fn (string $key, ?string $fallback = null, array $replace = []) => __($key, $replace) !== $key ? __($key, $replace) : ($fallback ?? $key);
$advantages = [
    ['title' => $t('site.visibility', 'Visibilité'), 'desc' => $t('site.visibility_text', 'Vos idées peuvent toucher des milliers de lecteurs.')],
    ['title' => $t('site.editorial_review', 'Relecture éditoriale'), 'desc' => $t('site.editorial_review_text', 'Notre équipe relit et accompagne votre texte.')],
    ['title' => $t('site.professional_publication', 'Publication professionnelle'), 'desc' => $t('site.professional_publication_text', 'Vos articles sont mis en valeur sur le site.')],
    ['title' => $t('site.simplicity', 'Simplicité'), 'desc' => $t('site.simplicity_text', 'Rédigez, enregistrez en brouillon, puis soumettez quand vous êtes prêt.')],
];
?>
<div class="w-full max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 pb-8 lg:pb-12">
    <!-- Hero + CTA principal -->
    <div class="relative w-full rounded-[30px] overflow-hidden mb-8" style="height: 320px; min-height: 280px;">
        <img src="<?= htmlspecialchars($hero_img) ?>" alt="" class="absolute inset-0 w-full h-full object-cover" loading="eager">
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/25 to-transparent"></div>
        <a href="/" class="absolute flex items-center justify-center gap-2 rounded-full text-[#004241] font-medium text-sm bg-white/95 hover:bg-white transition top-6 left-6 px-4 py-2.5 z-10" aria-label="<?= htmlspecialchars($t('site.back', 'Retour')) ?>">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" transform="matrix(-1 0 0 1 24 0)" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            <?= htmlspecialchars($t('site.back', 'Retour')) ?>
        </a>
        <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8 flex flex-col md:flex-row md:items-end md:justify-between md:gap-8">
            <div>
                <h1 class="font-semibold text-white text-3xl md:text-4xl lg:text-5xl leading-tight font-sans"><?= htmlspecialchars($t('site.become_contributor_title', 'Votre voix compte.')) ?></h1>
                <p class="text-white/95 text-base md:text-lg mt-2 max-w-xl">
                    <?= htmlspecialchars($t('site.become_contributor_text', 'Partagez une idée, un point de vue ou une histoire. Rejoignez nos rédacteurs.')) ?>
                </p>
            </div>
            <a href="<?= url('/register') ?>" class="mt-6 md:mt-0 flex-shrink-0 inline-flex items-center justify-center gap-2 rounded-full bg-[#FFF0B6] text-[#004241] font-semibold text-base px-8 py-4 hover:bg-[#FFE999] transition-colors shadow-lg">
                <?= htmlspecialchars($t('site.write_first_article', 'Écrire mon premier article')) ?>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>

    <!-- Bento : avantages en priorité -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <?php foreach ($advantages as $i => $adv) { ?>
        <div class="rounded-[30px] border border-[#004241]/10 bg-white p-5 md:p-6 flex flex-col gap-3 shadow-[0_4px_20px_rgba(0,66,65,0.04)] hover:shadow-[0_8px_30px_rgba(0,66,65,0.08)] transition-shadow">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#EBF1EF] text-[#004241] text-sm font-semibold"><?= $i + 1 ?></span>
            <h3 class="font-semibold text-[#004241] text-lg"><?= htmlspecialchars($adv['title']) ?></h3>
            <p class="text-[#004241]/75 text-sm leading-relaxed"><?= htmlspecialchars($adv['desc']) ?></p>
        </div>
        <?php } ?>
    </div>

    <!-- Participation : transparente, pas intrusive -->
    <div class="mt-6 lg:mt-8 rounded-[30px] bg-[#EBF1EF] p-6 md:p-8 flex flex-col sm:flex-row sm:items-center gap-6">
        <div class="flex-1">
            <h3 class="font-semibold text-[#004241] text-lg mb-2"><?= htmlspecialchars($t('site.full_support', 'Une participation pour un accompagnement complet')) ?></h3>
            <p class="text-[#004241]/80 text-sm leading-relaxed">
                <?= htmlspecialchars($t('site.full_support_text', ':amount par soumission couvre la relecture éditoriale, la mise en forme et le traitement de votre demande. Remboursement possible si l’article n’est pas retenu.', [':amount' => (int) $publicationPriceEur . '€'])) ?>
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 flex-shrink-0">
            <a href="<?= url('/register') ?>" class="inline-flex h-12 items-center justify-center rounded-full bg-[#004241] px-6 text-base font-semibold text-white transition hover:bg-[#003130]">
                <?= htmlspecialchars($t('site.create_my_account', 'Créer mon compte')) ?>
            </a>
            <a href="<?= url('/login') ?>" class="inline-flex h-12 items-center justify-center rounded-full px-6 text-base font-medium text-[#004241] transition hover:bg-white/80">
                <?= htmlspecialchars($t('site.already_have_account', "J'ai déjà un compte")) ?>
            </a>
        </div>
    </div>

    <!-- Footer legal -->
    <p class="mt-8 text-center text-[#004241]/55 text-xs leading-relaxed">
        <?= str_replace(
            [':legal', ':privacy'],
            [
                '<a href="/mentions-legales" class="text-[#004241] font-medium underline hover:no-underline">' . htmlspecialchars($t('site.legal_notice', 'Mentions légales')) . '</a>',
                '<a href="/politique-confidentialite" class="text-[#004241] font-medium underline hover:no-underline">' . htmlspecialchars($t('site.privacy', 'Politique de confidentialité')) . '</a>',
            ],
            htmlspecialchars($t('site.legal_acceptance', 'En créant un compte, vous acceptez nos :legal et notre :privacy.'))
        ) ?>
    </p>
</div>
