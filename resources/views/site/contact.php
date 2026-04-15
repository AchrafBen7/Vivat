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
        'hero_faq' => 'Veelgestelde vragen en snelle wegwijzers',
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
        'hero_faq' => 'Questions fréquentes et repères rapides',
        'see_faq' => 'Voir la FAQ',
    ];

$contactHeroImageUrl = 'https://images.pexels.com/photos/8867244/pexels-photo-8867244.jpeg?auto=compress&cs=tinysrgb&w=1400&h=1750&fit=crop';

$tagClass = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';
$tagTopNews = 'bg-[#FFF1B9] text-[#004241]';
?>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section data-contact-hero class="relative overflow-hidden rounded-[30px] bg-neutral-900 px-5 py-6 text-white md:px-6 md:py-8 lg:px-8 lg:py-10">
        <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-[30px]" aria-hidden="true">
            <img
                src="<?= htmlspecialchars($contactHeroImageUrl) ?>"
                alt=""
                width="1400"
                height="1750"
                class="absolute left-1/2 top-1/2 h-[103%] w-[103%] max-w-none -translate-x-1/2 -translate-y-1/2 object-cover blur-sm sm:blur-[6px]"
                loading="eager"
                decoding="async"
            >
        </div>
        <div class="pointer-events-none absolute inset-0 rounded-[30px] bg-gradient-to-b from-black/45 via-black/35 to-black/60" aria-hidden="true"></div>
        <div class="relative z-[1] flex flex-col gap-5">
            <div class="flex flex-col gap-5">
                <span class="<?= $tagClass ?> <?= $tagTopNews ?>"><?= htmlspecialchars($t['badge']) ?></span>
                <div class="flex max-w-[58ch] flex-col gap-4">
                    <h1 class="font-semibold leading-[1.06] text-white text-[28px] md:text-[34px] lg:text-[38px]"><?= htmlspecialchars($t['title']) ?></h1>
                    <p class="m-0 max-w-[56ch] text-[15px] leading-relaxed text-white/92 md:text-base"><?= htmlspecialchars($t['lead']) ?></p>
                </div>
            </div>
        </div>
    </section>

    <section data-contact-cards class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <a data-contact-card href="mailto:<?= htmlspecialchars($editorialEmail) ?>" class="group flex min-h-[260px] flex-col justify-between rounded-[30px] bg-[#004241] p-6 text-white no-underline transition hover:opacity-95" style="gap: 18px;">
            <div class="flex flex-col" style="gap: 12px;">
                <span class="inline-flex w-fit items-center justify-center rounded-full bg-white/12 px-[14px] py-[7px] text-sm font-medium text-white"><?= htmlspecialchars($t['editorial_badge']) ?></span>
                <h2 class="text-[32px] font-medium leading-[1.02]"><?= htmlspecialchars($t['editorial_title']) ?></h2>
                <p class="max-w-[30ch] text-white/78" style="font-size: 17px; line-height: 1.45;"><?= htmlspecialchars($t['editorial_text']) ?></p>
            </div>
            <span class="inline-flex items-center gap-2 text-base font-medium text-white"><?= htmlspecialchars($editorialEmail) ?><svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
        </a>

        <a data-contact-card href="mailto:<?= htmlspecialchars($partnershipEmail) ?>" class="group flex min-h-[260px] flex-col justify-between rounded-[30px] bg-white p-6 text-[#004241] no-underline transition hover:bg-[#f8fbfa]" style="gap: 18px; box-shadow: 0 18px 48px rgba(0, 66, 65, 0.08);">
            <div class="flex flex-col" style="gap: 12px;">
                <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#FFF0D4] px-[14px] py-[7px] text-sm font-medium text-[#004241]"><?= htmlspecialchars($t['partnership_badge']) ?></span>
                <h2 class="text-[32px] font-medium leading-[1.02]"><?= htmlspecialchars($t['partnership_title']) ?></h2>
                <p class="max-w-[30ch] text-[#004241]/75" style="font-size: 17px; line-height: 1.45;"><?= htmlspecialchars($t['partnership_text']) ?></p>
            </div>
            <span class="inline-flex items-center gap-2 text-base font-medium text-[#004241]"><?= htmlspecialchars($partnershipEmail) ?><svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg></span>
        </a>
    </section>

    <section data-contact-info class="rounded-[30px] bg-white px-6 py-5" style="box-shadow: 0 18px 48px rgba(0, 66, 65, 0.08);">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <p class="m-0 max-w-[50ch] text-[#004241]/75" style="font-size: 17px; line-height: 1.45;"><?= htmlspecialchars($t['response_time']) ?></p>
            <a href="/faq" class="inline-flex items-center justify-center rounded-full bg-[#EBF1EF] px-5 py-3 text-sm font-medium text-[#004241] no-underline transition hover:bg-[#e2ece8]"><?= htmlspecialchars($t['see_faq']) ?></a>
        </div>
    </section>
</div>

<script>
(function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    function initContactMotion() {
        if (!window.gsap || !window.ScrollTrigger) return;

        var hero = document.querySelector('[data-contact-hero]');
        if (hero) {
            window.gsap.from(hero, {
                opacity: 0,
                x: -28,
                duration: 0.85,
                ease: 'power2.out',
                clearProps: 'opacity,transform',
                scrollTrigger: {
                    trigger: hero,
                    start: 'top 90%',
                },
            });
        }

        var cardsSection = document.querySelector('[data-contact-cards]');
        var cards = document.querySelectorAll('[data-contact-card]');
        if (cardsSection && cards.length) {
            var stCards = {
                trigger: cardsSection,
                start: 'top 84%',
            };
            // Pas de stagger : les deux cartes arrivent ensemble (évite l’effet partenariat « qui suit » la rédac).
            // ≥ md : entrée symétrique (gauche / droite) ; mobile en colonne : même montée pour les deux.
            if (cards.length >= 2 && window.matchMedia('(min-width: 768px)').matches) {
                window.gsap
                    .timeline({
                        scrollTrigger: stCards,
                    })
                    .from(
                        cards[0],
                        { opacity: 0, x: -16, y: 8, duration: 0.52, ease: 'power2.out', clearProps: 'opacity,transform' },
                        0
                    )
                    .from(
                        cards[1],
                        { opacity: 0, x: 16, y: 8, duration: 0.52, ease: 'power2.out', clearProps: 'opacity,transform' },
                        0
                    );
            } else {
                window.gsap.from(cards, {
                    opacity: 0,
                    y: 14,
                    duration: 0.52,
                    ease: 'power2.out',
                    stagger: 0,
                    clearProps: 'opacity,transform',
                    scrollTrigger: stCards,
                });
            }
        }

        var info = document.querySelector('[data-contact-info]');
        if (info) {
            window.gsap.from(info, {
                opacity: 0,
                y: 18,
                duration: 0.48,
                ease: 'power2.out',
                clearProps: 'opacity,transform',
                scrollTrigger: {
                    trigger: info,
                    // Plus tôt que "top 88%" : dès que le bloc entre dans la zone basse (ligne du bas de l’écran)
                    start: 'top bottom',
                },
            });
        }
    }

    if (document.readyState === 'complete') {
        initContactMotion();
    } else {
        window.addEventListener('load', initContactMotion);
    }
})();
</script>
