<?php
$locale = $locale ?? 'fr';
$faqGroups = [
    [
        'title' => 'Lire sur Vivat',
        'items' => [
            [
                'question' => 'Quel type de contenus trouve-t-on sur Vivat ?',
                'answer' => 'Vivat propose des articles autour du quotidien, de la santé, de la technologie, de la finance, du voyage, de la maison et d’autres sujets pratiques ou inspirants à lire simplement.',
            ],
            [
                'question' => 'Dois-je créer un compte pour consulter les articles ?',
                'answer' => 'Non. Les articles publics sont consultables librement. Aucun compte lecteur n’est nécessaire pour parcourir les rubriques, utiliser la recherche ou lire les contenus.',
            ],
            [
                'question' => 'Comment retrouver un sujet précis ?',
                'answer' => 'Vous pouvez passer par les rubriques du site ou utiliser la recherche pour retrouver un mot-clé, une thématique ou un article plus rapidement.',
            ],
            [
                'question' => 'Les articles sont-ils classés par rubrique ?',
                'answer' => 'Oui. Les contenus sont organisés par catégories pour rendre la navigation plus claire et vous permettre de retrouver plus facilement les sujets qui vous intéressent.',
            ],
        ],
    ],
    [
        'title' => 'Rédaction et contribution',
        'items' => [
            [
                'question' => 'Puis-je proposer un article à Vivat ?',
                'answer' => 'Oui. Si vous souhaitez contribuer, vous pouvez passer par l’espace rédacteur prévu sur le site et suivre le parcours d’inscription ou de soumission disponible.',
            ],
            [
                'question' => 'Tous les contenus proposés sont-ils publiés automatiquement ?',
                'answer' => 'Non. Les contenus peuvent faire l’objet d’une vérification ou d’un traitement éditorial avant publication afin de préserver la cohérence et la qualité du site.',
            ],
            [
                'question' => 'Comment accéder à mon espace rédacteur ?',
                'answer' => 'Si vous disposez d’un compte auteur ou contributeur, vous pouvez vous connecter puis accéder à votre espace dédié pour gérer vos contenus et vos informations.',
            ],
            [
                'question' => 'Puis-je modifier un article après l’avoir soumis ?',
                'answer' => 'Selon votre statut et l’état du contenu, certaines modifications peuvent être faites depuis l’espace contributeur. Si besoin, vous pouvez aussi contacter l’équipe.',
            ],
        ],
    ],
    [
        'title' => 'Newsletter et contact',
        'items' => [
            [
                'question' => 'Que contient la newsletter Vivat ?',
                'answer' => 'La newsletter rassemble une sélection d’articles récents, des contenus mis en avant et des idées de lecture envoyées directement par e-mail.',
            ],
            [
                'question' => 'Puis-je me désabonner facilement de la newsletter ?',
                'answer' => 'Oui. Chaque envoi doit permettre de gérer votre abonnement ou de vous désinscrire simplement.',
            ],
            [
                'question' => 'Comment contacter l’équipe Vivat ?',
                'answer' => 'Vous pouvez utiliser la page contact pour poser une question générale, faire un retour sur un article, demander un renseignement ou prendre contact avec l’équipe.',
            ],
            [
                'question' => 'Comment signaler un contenu problématique ?',
                'answer' => 'Si un contenu vous semble erroné, sensible ou inapproprié, vous pouvez le signaler à l’équipe via la page contact afin qu’il soit examiné rapidement.',
            ],
            [
                'question' => 'Que fait Vivat avec mes données ?',
                'answer' => 'Les données éventuellement collectées via le site ou ses services sont traitées dans le cadre de l’utilisation du site, de la communication et de l’amélioration des services, conformément à la politique de vie privée.',
            ],
        ],
    ],
];
?>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="flex flex-col rounded-[30px] bg-white p-6 lg:col-span-4" style="gap: 16px; box-shadow: 0 18px 48px rgba(0, 66, 65, 0.08);">
            <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[16px] py-[8px] text-sm font-medium text-[#004241]">Repères</span>
            <h2 class="font-medium text-[#004241]" style="font-size: 32px; line-height: 1.05;">Les réponses utiles, au même endroit.</h2>
            <p class="text-[#004241]/75" style="font-size: 17px; line-height: 1.45;">Cette FAQ reprend les questions les plus fréquentes sur la lecture des articles, la contribution au site, la newsletter et les moyens de contact.</p>
            <div class="grid grid-cols-1 gap-3">
                <a href="/contact" class="inline-flex items-center justify-between rounded-[24px] bg-[#F6F8F7] px-5 py-4 text-[#004241] no-underline transition hover:bg-[#edf3f0]">
                    <span class="font-medium">Contacter l’équipe</span>
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="/search" class="inline-flex items-center justify-between rounded-[24px] bg-[#F6F8F7] px-5 py-4 text-[#004241] no-underline transition hover:bg-[#edf3f0]">
                    <span class="font-medium">Rechercher un article</span>
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
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
