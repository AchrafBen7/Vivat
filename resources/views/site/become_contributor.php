<?php
$publicationPriceEur = $publication_price_eur ?? 15;
$hero_img = 'https://images.pexels.com/photos/3761509/pexels-photo-3761509.jpeg?auto=compress&cs=tinysrgb&w=800&h=600&fit=crop';
?>
<div class="pt-6 pb-12 px-4">
    <div class="max-w-[756px] mx-auto flex flex-col gap-6">
        <div class="relative w-full rounded-[30px] overflow-hidden" style="height: 220px;">
            <img src="<?= htmlspecialchars($hero_img) ?>" alt="" class="absolute inset-0 w-full h-full object-cover" loading="eager">
            <a href="/" class="absolute flex items-center justify-center gap-2.5 rounded-full text-[#004241] font-normal text-base leading-none bg-white hover:opacity-90 transition" style="top: 24px; left: 24px; padding: 12px 18px;">
                <svg class="w-5 h-5 flex-shrink-0 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                Retour
            </a>
        </div>

        <div class="w-full rounded-[30px] border border-[#004241]/10 bg-white p-8 md:p-10 shadow-[0_4px_20px_rgba(0,66,65,0.06)]">
            <h1 class="font-semibold text-[#004241] text-2xl mb-2">Devenir rédacteur sur Vivat</h1>
            <p class="text-[#004241]/80 text-base leading-relaxed mb-6">
                Vous souhaitez partager une idée, un point de vue ou une histoire ? Vivat vous ouvre ses colonnes. Avant de créer votre compte, voici ce qu’il faut savoir.
            </p>

            <div class="rounded-[20px] bg-[#EBF1EF] p-5 mb-6">
                <h2 class="font-semibold text-[#004241] text-lg mb-2">Participation à la publication</h2>
                <p class="text-[#004241]/85 text-sm leading-relaxed mb-3">
                    Pour envoyer un article à notre équipe éditoriale, une participation de <strong><?= (int) $publicationPriceEur ?>€</strong> par soumission est demandée. Elle couvre la relecture, la mise en forme et le traitement de votre demande.
                </p>
                <p class="text-[#004241]/70 text-sm leading-relaxed">
                    Si votre article n’est pas retenu après relecture, un remboursement pourra être effectué selon nos conditions.
                </p>
            </div>

            <h2 class="font-semibold text-[#004241] text-lg mb-3">Pourquoi rédiger un article sur Vivat ?</h2>
            <ul class="space-y-3 mb-8 text-[#004241]/85 text-sm leading-relaxed">
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#004241]/10 text-[#004241] text-xs font-semibold">1</span>
                    <span><strong>Visibilité</strong> — Vos idées peuvent toucher des milliers de lecteurs.</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#004241]/10 text-[#004241] text-xs font-semibold">2</span>
                    <span><strong>Relecture éditoriale</strong> — Notre équipe relit et accompagne votre texte.</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#004241]/10 text-[#004241] text-xs font-semibold">3</span>
                    <span><strong>Publication professionnelle</strong> — Vos articles sont mis en valeur sur le site.</span>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#004241]/10 text-[#004241] text-xs font-semibold">4</span>
                    <span><strong>Simplicité</strong> — Rédigez, enregistrez en brouillon, puis soumettez quand vous êtes prêt.</span>
                </li>
            </ul>

            <div class="flex flex-col sm:flex-row gap-3">
                <a href="<?= url('/register') ?>" class="inline-flex h-12 items-center justify-center rounded-full bg-[#004241] px-8 text-base font-semibold text-white transition-colors hover:bg-[#003130]">
                    Créer mon compte
                </a>
                <a href="<?= url('/login') ?>" class="inline-flex h-12 items-center justify-center rounded-full border border-[#004241]/25 px-8 text-base font-medium text-[#004241] transition-colors hover:bg-[#EBF1EF]">
                    J'ai déjà un compte
                </a>
            </div>

            <p class="mt-6 text-[#004241]/60 text-xs leading-relaxed">
                En créant un compte, vous acceptez nos <a href="/mentions-legales" class="text-[#004241] font-medium underline hover:no-underline">Mentions légales</a> et notre <a href="/politique-confidentialite" class="text-[#004241] font-medium underline hover:no-underline">Politique de confidentialité</a>.
            </p>
        </div>
    </div>
</div>
