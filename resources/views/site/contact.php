<?php
$locale = $locale ?? 'fr';
$editorialEmail = 'contact@vivat.be';
$partnershipEmail = 'partenariats@vivat.be';
$t = $locale === 'nl'
    ? [
        'badge' => 'Contact',
        'title' => 'De juiste contactpersonen, zonder omweg.',
        'lead' => 'Een vraag, feedback over een artikel of een samenwerkingsaanvraag? Hier zie je meteen naar wie je moet schrijven.',
        'editorial_badge' => 'Redactie',
        'editorial_title' => 'Redactionele vraag',
        'editorial_text' => 'Voor een opmerking over een artikel, een onderwerpvoorstel, een correctie of een algemene vraag over de site.',
        'partnership_badge' => 'Partnerschappen',
        'partnership_title' => 'Merken en samenwerkingen',
        'partnership_text' => 'Voor een campagne, sponsoring, redactionele samenwerking of een vraag rond een merk.',
        'response_time' => 'In het algemeen antwoordt het team binnen 48 werkuren. Twijfel je, begin dan met het redactieadres.',
        'see_faq' => 'Bekijk de FAQ',
    ]
    : [
        'badge' => 'Contact',
        'title' => 'Les bons interlocuteurs, sans détour.',
        'lead' => 'Une question, un retour sur un article ou une demande de collaboration ? Voici simplement à qui écrire.',
        'editorial_badge' => 'Rédaction',
        'editorial_title' => 'Question éditoriale',
        'editorial_text' => 'Pour une remarque sur un contenu, une suggestion de sujet, une correction ou une question générale liée au site.',
        'partnership_badge' => 'Partenariats',
        'partnership_title' => 'Marques et collaborations',
        'partnership_text' => 'Pour une campagne, un sponsoring, une collaboration éditoriale ou une demande liée à une marque.',
        'response_time' => 'En général, l’équipe répond sous 48h ouvrées. Si vous hésitez, commencez par l’adresse de rédaction.',
        'see_faq' => 'Voir la FAQ',
    ];
?>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section class="rounded-[32px] bg-[#EBF1EF] px-6 py-7 md:px-8 md:py-8">
        <div class="flex flex-col" style="gap: 14px;">
            <span class="inline-flex w-fit items-center justify-center rounded-full bg-white px-[16px] py-[8px] text-sm font-medium text-[#004241]"><?= htmlspecialchars($t['badge']) ?></span>
            <h1 class="max-w-[10ch] font-semibold text-[#004241]" style="font-family: Figtree, sans-serif; font-size: clamp(34px, 5vw, 60px); line-height: 0.96;"><?= htmlspecialchars($t['title']) ?></h1>
            <p class="max-w-[52ch] text-[#004241]/78" style="font-size: 18px; line-height: 1.45;"><?= htmlspecialchars($t['lead']) ?></p>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <a href="mailto:<?= htmlspecialchars($editorialEmail) ?>" class="group flex min-h-[260px] flex-col justify-between rounded-[30px] bg-[#004241] p-6 text-white no-underline transition hover:opacity-95" style="gap: 18px;">
            <div class="flex flex-col" style="gap: 12px;">
                <span class="inline-flex w-fit items-center justify-center rounded-full bg-white/12 px-[14px] py-[7px] text-sm font-medium text-white"><?= htmlspecialchars($t['editorial_badge']) ?></span>
                <h2 class="text-[32px] font-medium leading-[1.02]"><?= htmlspecialchars($t['editorial_title']) ?></h2>
                <p class="max-w-[30ch] text-white/78" style="font-size: 17px; line-height: 1.45;"><?= htmlspecialchars($t['editorial_text']) ?></p>
            </div>
            <span class="inline-flex items-center gap-2 text-base font-medium text-white"><?= htmlspecialchars($editorialEmail) ?><svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
        </a>

        <a href="mailto:<?= htmlspecialchars($partnershipEmail) ?>" class="group flex min-h-[260px] flex-col justify-between rounded-[30px] bg-white p-6 text-[#004241] no-underline transition hover:bg-[#f8fbfa]" style="gap: 18px; box-shadow: 0 18px 48px rgba(0, 66, 65, 0.08);">
            <div class="flex flex-col" style="gap: 12px;">
                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#FFF0D4] px-[14px] py-[7px] text-sm font-medium text-[#004241]"><?= htmlspecialchars($t['partnership_badge']) ?></span>
                <h2 class="text-[32px] font-medium leading-[1.02]"><?= htmlspecialchars($t['partnership_title']) ?></h2>
                <p class="max-w-[30ch] text-[#004241]/75" style="font-size: 17px; line-height: 1.45;"><?= htmlspecialchars($t['partnership_text']) ?></p>
            </div>
            <span class="inline-flex items-center gap-2 text-base font-medium text-[#004241]"><?= htmlspecialchars($partnershipEmail) ?><svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
        </a>
    </section>

    <section class="rounded-[30px] bg-white px-6 py-5" style="box-shadow: 0 18px 48px rgba(0, 66, 65, 0.08);">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <p class="m-0 max-w-[50ch] text-[#004241]/75" style="font-size: 17px; line-height: 1.45;"><?= htmlspecialchars($t['response_time']) ?></p>
            <a href="/faq" class="inline-flex items-center justify-center rounded-full bg-[#EBF1EF] px-5 py-3 text-sm font-medium text-[#004241] no-underline transition hover:bg-[#e2ece8]"><?= htmlspecialchars($t['see_faq']) ?></a>
        </div>
    </section>
</div>
