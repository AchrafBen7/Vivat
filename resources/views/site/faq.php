<?php
$locale = $locale ?? 'fr';
$faqGroups = [
    [
        'title' => 'Le site Vivat',
        'items' => [
            [
                'question' => 'Quel type de contenus trouve-t-on sur Vivat ?',
                'answer' => 'Vivat propose des articles de magazine autour du quotidien, de la santé, de la technologie, de la finance, du voyage, de la maison et d’autres thématiques utiles au quotidien.',
            ],
            [
                'question' => 'Dois-je créer un compte pour consulter les articles ?',
                'answer' => 'Non. Les contenus éditoriaux publics sont consultables librement. La navigation sur le site ne nécessite pas de créer un compte lecteur.',
            ],
            [
                'question' => 'Comment retrouver une rubrique ou un sujet précis ?',
                'answer' => 'Vous pouvez passer par la navigation principale, les pages de rubriques, ou utiliser la recherche pour retrouver un thème, un mot-clé ou un article plus rapidement.',
            ],
        ],
    ],
    [
        'title' => 'Newsletter',
        'items' => [
            [
                'question' => 'Que contient la newsletter Vivat ?',
                'answer' => 'La newsletter rassemble une sélection d’articles récents, des contenus mis en avant par la rédaction et des idées de lecture à retrouver directement par e-mail.',
            ],
            [
                'question' => 'À quelle fréquence est-elle envoyée ?',
                'answer' => 'Le rythme peut évoluer selon l’actualité éditoriale, mais l’objectif reste de proposer une sélection utile sans multiplier inutilement les envois.',
            ],
            [
                'question' => 'Puis-je me désabonner facilement ?',
                'answer' => 'Oui. Chaque envoi de newsletter doit permettre de gérer votre abonnement ou de vous désinscrire simplement.',
            ],
        ],
    ],
    [
        'title' => 'Vie privée et contact',
        'items' => [
            [
                'question' => 'Que fait Vivat avec mes données ?',
                'answer' => 'Les données éventuellement collectées via le site ou ses services sont traitées dans le cadre de l’utilisation du site, de la communication et de l’amélioration des services, conformément à la politique de vie privée.',
            ],
            [
                'question' => 'Comment contacter l’équipe Vivat ?',
                'answer' => 'Vous pouvez passer par la page contact pour joindre l’équipe, poser une question générale, faire un retour sur un contenu ou demander un renseignement complémentaire.',
            ],
            [
                'question' => 'Comment signaler un contenu problématique ou illicite ?',
                'answer' => 'Un signalement peut être adressé à l’équipe éditoriale ou au contact mentionné dans les informations légales, afin qu’un examen soit effectué dans les meilleurs délais.',
            ],
        ],
    ],
];
?>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="flex flex-col rounded-[30px] bg-white p-6 lg:col-span-4" style="gap: 16px; box-shadow: 0 18px 48px rgba(0, 66, 65, 0.08);">
            <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[16px] py-[8px] text-sm font-medium text-[#004241]">Repères</span>
            <h2 class="font-medium text-[#004241]" style="font-size: 32px; line-height: 1.05;">Besoin d’aller plus vite ?</h2>
            <p class="text-[#004241]/75" style="font-size: 17px; line-height: 1.45;">Cette page reprend les réponses les plus courantes dans l’esprit du site Vivat. Si l’information recherchée n’y figure pas, la page contact reste le canal le plus direct.</p>
            <div class="grid grid-cols-1 gap-3">
                <a href="/contact" class="inline-flex items-center justify-between rounded-[24px] bg-[#F6F8F7] px-5 py-4 text-[#004241] no-underline transition hover:bg-[#edf3f0]">
                    <span class="font-medium">Accéder au contact</span>
                    <span aria-hidden="true">→</span>
                </a>
                <a href="/search" class="inline-flex items-center justify-between rounded-[24px] bg-[#F6F8F7] px-5 py-4 text-[#004241] no-underline transition hover:bg-[#edf3f0]">
                    <span class="font-medium">Chercher un sujet</span>
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>

        <div class="flex flex-col lg:col-span-8" style="gap: 24px;">
            <?php foreach ($faqGroups as $group): ?>
            <section class="rounded-[30px] bg-[#EEF4F1] p-6 md:p-7" style="gap: 18px;">
                <div class="mb-5 flex flex-col" style="gap: 10px;">
                    <span class="inline-flex w-fit items-center justify-center rounded-full bg-white px-[16px] py-[8px] text-sm font-medium text-[#004241]"><?= htmlspecialchars($group['title']) ?></span>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($group['items'] as $item): ?>
                    <details class="group rounded-[24px] bg-white px-5 py-4 text-[#004241]" style="box-shadow: 0 10px 28px rgba(0, 66, 65, 0.06);">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium" style="font-size: 20px;">
                            <span><?= htmlspecialchars($item['question']) ?></span>
                            <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#EBF1EF] text-[#004241] transition group-open:rotate-45">+</span>
                        </summary>
                        <p class="pt-4 text-[#004241]/78" style="font-size: 17px; line-height: 1.55;"><?= htmlspecialchars($item['answer']) ?></p>
                    </details>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endforeach; ?>
        </div>
    </section>
</div>
