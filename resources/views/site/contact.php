<?php
$locale = $locale ?? 'fr';
$editorialEmail = 'contact@vivat.be';
$partnershipEmail = 'partenariats@vivat.be';
?>
<style>
    .contact-card-grid .contact-card {
        transition: transform 280ms ease, background-color 220ms ease, border-color 220ms ease, box-shadow 280ms ease;
    }

    .contact-card-grid .contact-card:hover {
        transform: scale(1.015);
    }
</style>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section class="rounded-[32px] px-6 py-7 md:px-8 md:py-8" style="background: linear-gradient(135deg, #edf4f1 0%, #ebf1ef 52%, #f8fbfa 100%);" data-aos="fade-up" data-aos-delay="40">
        <div class="flex flex-col" style="gap: 14px;">
            <span class="inline-flex w-fit items-center justify-center rounded-full bg-white px-[16px] py-[8px] text-sm font-medium text-[#004241]">Contact</span>
            <h1 class="max-w-[10ch] font-semibold text-[#004241]" style="font-family: Figtree, sans-serif; font-size: clamp(34px, 5vw, 60px); line-height: 0.96;">Les bons interlocuteurs, sans détour.</h1>
            <p class="max-w-[52ch] text-[#004241]/78" style="font-size: 18px; line-height: 1.45;">Une question, un retour sur un article ou une demande de collaboration ? Voici simplement à qui écrire.</p>
        </div>
    </section>

    <section class="contact-card-grid grid grid-cols-1 gap-6 md:grid-cols-2">
        <a href="mailto:<?= htmlspecialchars($editorialEmail) ?>" class="contact-card group flex min-h-[260px] flex-col justify-between rounded-[30px] bg-[#004241] p-6 text-white no-underline" style="gap: 18px; box-shadow: inset 0 0 0 1px rgba(255,255,255,0.06);" data-aos="fade-up" data-aos-delay="100">
            <div class="flex flex-col" style="gap: 12px;">
                <span class="inline-flex w-fit items-center justify-center rounded-full bg-white/12 px-[14px] py-[7px] text-sm font-medium text-white">Rédaction</span>
                <h2 class="text-[32px] font-medium leading-[1.02]">Question éditoriale</h2>
                <p class="max-w-[30ch] text-white/78" style="font-size: 17px; line-height: 1.45;">Pour une remarque sur un contenu, une suggestion de sujet, une correction ou une question générale liée au site.</p>
            </div>
            <span class="inline-flex items-center gap-2 text-base font-medium text-white"><?= htmlspecialchars($editorialEmail) ?><span aria-hidden="true">→</span></span>
        </a>

        <a href="mailto:<?= htmlspecialchars($partnershipEmail) ?>" class="contact-card group flex min-h-[260px] flex-col justify-between rounded-[30px] bg-white p-6 text-[#004241] no-underline" style="gap: 18px; background-image: linear-gradient(135deg, #ffffff 0%, #fbfdfc 100%); box-shadow: 0 10px 28px rgba(0, 66, 65, 0.06); border: 1px solid rgba(0, 66, 65, 0.05);" data-aos="fade-up" data-aos-delay="180">
            <div class="flex flex-col" style="gap: 12px;">
                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#FFF0D4] px-[14px] py-[7px] text-sm font-medium text-[#004241]">Partenariats</span>
                <h2 class="text-[32px] font-medium leading-[1.02]">Marques et collaborations</h2>
                <p class="max-w-[30ch] text-[#004241]/75" style="font-size: 17px; line-height: 1.45;">Pour une campagne, un sponsoring, une collaboration éditoriale ou une demande liée à une marque.</p>
            </div>
            <span class="inline-flex items-center gap-2 text-base font-medium text-[#004241]"><?= htmlspecialchars($partnershipEmail) ?><span aria-hidden="true">→</span></span>
        </a>
    </section>

    <section class="rounded-[30px] bg-white px-6 py-5" style="background-image: linear-gradient(135deg, #ffffff 0%, #f8fbfa 100%); box-shadow: 0 10px 28px rgba(0, 66, 65, 0.06); border: 1px solid rgba(0, 66, 65, 0.05);" data-aos="fade-up" data-aos-delay="240">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <p class="m-0 max-w-[50ch] text-[#004241]/75" style="font-size: 17px; line-height: 1.45;">En général, l’équipe répond sous 48h ouvrées. Si vous hésitez, commencez par l’adresse de rédaction.</p>
            <a href="/faq" class="soft-button-hover soft-button-hover-light inline-flex items-center justify-center rounded-full bg-[#EBF1EF] px-5 py-3 text-sm font-medium text-[#004241] no-underline">Voir la FAQ</a>
        </div>
    </section>
</div>
