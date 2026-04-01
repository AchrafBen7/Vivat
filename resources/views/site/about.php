<?php
$locale = $locale ?? 'fr';

// Hero : lecture & cadre de vie (Pexels, ambiance magazine / bien-être)
$aboutHeroImageUrl = 'https://images.pexels.com/photos/4050320/pexels-photo-4050320.jpeg?auto=compress&cs=tinysrgb&w=1400&h=1750&fit=crop';

// Tokens alignés sur `home.php`
$cardGreenSurface = 'bg-[#004241] transition-colors duration-200 hover:bg-[#003130]';
$cardYellowSurface = 'bg-[#FFF0B6] transition-colors duration-200 hover:bg-[#FBE9A3]';
$cardSoftSurface = 'bg-[#EBF1EF] transition-colors duration-200 hover:bg-[#DEE7E4]';
$cardWhiteSurface = 'bg-white shadow-[0_18px_48px_rgba(0,66,65,0.08)] transition-colors duration-200 hover:bg-[#F7FAF9]';

$tagClass = 'inline-flex items-center justify-center w-fit max-w-full min-h-[30px] px-3 rounded-full text-[12px] leading-none font-medium tracking-[0.02em] whitespace-nowrap flex-shrink-0';
$tagTopNews = 'bg-[#FFF1B9] text-[#004241]';
$tagOnYellowCard = 'bg-[#004241] text-white';
$tagOnGreenCard = 'bg-[#527E7E] text-white';
$tagOnSoftCard = 'bg-white text-[#004241]';
$tagOnWhiteCard = 'bg-[#EBF1EF] text-[#004241]';

// Cartes droite hero : même équilibre que la colonne articles (home)
$heroColorCardTitleCompact = 'font-semibold leading-tight text-xl';
$heroColorCardExcerptOnLight = 'text-sm leading-relaxed text-[#004241]/88 md:text-[15px]';
$heroColorCardExcerptOnDark = 'text-sm leading-relaxed text-white/90 md:text-[15px]';

// Blocs larges bas de page (équivalent cartes h4 / pleine largeur home)
$sectionWideTitle = 'font-semibold leading-tight text-2xl text-[#004241] md:text-3xl';
$sectionWideLead = 'mt-3 max-w-prose text-base leading-relaxed text-[#004241]/88 md:text-[17px]';

// Section prose pleine largeur
$aboutProse = 'text-[17px] leading-[1.65] text-[#004241]/90 md:text-[18px]';
?>
<div class="flex w-full flex-col">
    <div class="mb-6 flex w-full flex-col items-center justify-center" role="region" aria-label="Publicité">
        <div class="flex h-[50px] w-[320px] max-w-full shrink-0 items-center justify-center rounded-[30px] border-2 border-dashed border-gray-300 bg-gray-100 text-sm text-gray-400 md:hidden">
            Publicité 320×50
        </div>
        <div class="hidden h-[90px] w-full max-w-[728px] items-center justify-center rounded-[30px] border-2 border-dashed border-gray-300 bg-gray-100 text-sm text-gray-400 md:flex">
            Publicité 728×90
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid lg:grid-cols-12 lg:gap-6 lg:items-stretch">
        <!-- Colonne gauche : flou très léger sur l’image + voile sombre -->
        <section class="relative flex min-h-[420px] flex-col justify-start gap-4 overflow-hidden rounded-[30px] bg-neutral-900 p-8 text-white md:p-10 lg:col-span-5 lg:min-h-[524px]">
            <div class="pointer-events-none absolute inset-0 overflow-hidden rounded-[30px]" aria-hidden="true">
                <img
                    src="<?= htmlspecialchars($aboutHeroImageUrl) ?>"
                    alt=""
                    width="1400"
                    height="1750"
                    class="absolute left-1/2 top-1/2 h-[103%] w-[103%] max-w-none -translate-x-1/2 -translate-y-1/2 object-cover blur-sm sm:blur-[6px]"
                    loading="eager"
                    decoding="async"
                >
            </div>
            <div class="pointer-events-none absolute inset-0 rounded-[30px] bg-gradient-to-b from-black/45 via-black/30 to-black/55" aria-hidden="true"></div>
            <div class="relative z-[1] flex max-w-[58ch] flex-col gap-5">
                <span class="<?= $tagClass ?> <?= $tagTopNews ?>">À propos</span>
                <h1 class="font-semibold leading-[1.08] text-[32px] text-white max-sm:text-2xl md:text-[36px] lg:text-[40px]">Vivat, un magazine tourné vers le mieux vivre.</h1>
                <p class="m-0 text-base leading-relaxed text-white md:text-[17px]">
                    <strong class="font-semibold text-white">Vivat</strong> est un magazine en ligne centré sur le quotidien, le développement durable et les sujets qui touchent à la qualité de vie.
                </p>
                <p class="m-0 text-base leading-relaxed text-white/95 md:text-[17px]">
                    Le site propose une information pratique et accessible sur la maison, la santé, la mobilité, les finances ou la technologie, en <strong class="font-semibold text-white">français</strong> comme en <strong class="font-semibold text-white">néerlandais</strong>.
                </p>
                <p class="m-0 text-base leading-relaxed text-white/95 md:text-[17px]">
                    L’idée est d’aider à mieux comprendre son époque, découvrir de nouvelles pistes et lire des contenus utiles sans passer par un discours compliqué.
                </p>
            </div>
        </section>

        <!-- Colonne droite : 3 bandes égales comme sur la home (grid-rows-3 + h-full) -->
        <div class="mt-6 flex min-h-0 flex-col gap-6 lg:col-span-7 lg:mt-0 lg:grid lg:min-h-[524px] lg:grid-rows-3 lg:gap-6 lg:self-stretch">
            <div class="relative flex min-h-[220px] w-full flex-col justify-end gap-3 overflow-hidden rounded-[30px] p-8 lg:h-full lg:min-h-0 <?= $cardGreenSurface ?>">
                <span class="<?= $tagClass ?> <?= $tagOnGreenCard ?>">Mission</span>
                <h2 class="<?= $heroColorCardTitleCompact ?> text-white">Une information concrète</h2>
                <p class="<?= $heroColorCardExcerptOnDark ?>">Des contenus utiles, lisibles et pensés pour rester proches de la vie réelle.</p>
            </div>

            <div class="relative flex min-h-[220px] w-full flex-col justify-end gap-3 overflow-hidden rounded-[30px] p-8 lg:h-full lg:min-h-0 <?= $cardYellowSurface ?>">
                <span class="<?= $tagClass ?> <?= $tagOnYellowCard ?>">Langues</span>
                <h2 class="<?= $heroColorCardTitleCompact ?> text-[#004241]">Deux langues, une même approche</h2>
                <p class="<?= $heroColorCardExcerptOnLight ?>">Le même ton et la même ligne éditoriale dans les deux langues.</p>
            </div>

            <div class="relative flex min-h-[220px] w-full flex-col justify-end gap-3 overflow-hidden rounded-[30px] p-8 lg:h-full lg:min-h-0 <?= $cardWhiteSurface ?>">
                <span class="<?= $tagClass ?> <?= $tagOnWhiteCard ?>">Sur le site</span>
                <h2 class="<?= $heroColorCardTitleCompact ?> text-[#004241]">Une lecture simple</h2>
                <p class="<?= $heroColorCardExcerptOnLight ?>">Une navigation simple pour trouver vite un sujet et poursuivre la lecture.</p>
            </div>
        </div>

        <!-- Bandeau pleine largeur : respiration type bandeau CTA home -->
        <section class="flex flex-col gap-8 overflow-hidden rounded-[30px] bg-white p-8 shadow-[0_18px_48px_rgba(0,66,65,0.08)] md:flex-row md:items-center md:justify-between md:gap-12 md:p-10 lg:col-span-12">
            <div class="min-w-0 flex-1">
                <h2 class="m-0 font-semibold leading-tight text-[#004241] text-2xl md:text-3xl">Continuer la découverte</h2>
                <p class="mt-3 max-w-[48ch] text-base leading-relaxed text-[#004241]/85 md:text-[17px]">Parcourez les derniers articles ou contactez-nous si vous avez une question ou une remarque.</p>
            </div>
            <div class="flex w-full min-w-0 flex-col gap-4 sm:flex-row sm:flex-wrap sm:justify-end md:max-w-[560px]">
                <a href="/articles" class="group inline-flex flex-1 items-center justify-center gap-2 rounded-[24px] bg-[#004241] px-8 py-5 text-center text-base font-semibold text-white no-underline transition-colors duration-200 hover:bg-[#003130] sm:min-w-[200px]">
                    Voir les articles
                    <svg class="block h-6 w-6 flex-shrink-0 translate-y-0 transition-transform duration-300 ease-out will-change-transform group-hover:translate-x-[14px] motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="/contact" class="group inline-flex flex-1 items-center justify-center gap-2 rounded-[24px] bg-[#EBF1EF] px-8 py-5 text-center text-base font-semibold text-[#004241] no-underline transition-colors duration-200 hover:bg-[#DEE7E4] sm:min-w-[200px]">
                    Contact
                    <svg class="block h-6 w-6 flex-shrink-0 translate-y-0 transition-transform duration-300 ease-out will-change-transform group-hover:translate-x-[14px] motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </section>

        <!-- Bannière type home (970×250) -->
        <div class="mt-2 flex w-full justify-center lg:col-span-12" role="region" aria-label="Publicité">
            <div class="flex h-[250px] w-full max-w-[970px] items-center justify-center rounded-[30px] border-2 border-dashed border-gray-300 bg-gray-100 text-sm text-gray-400">
                Publicité 970×250
            </div>
        </div>
    </div>
</div>
