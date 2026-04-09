<?php
$locale = $locale ?? 'fr';
$faqGroups = $locale === 'nl'
    ? [
        [
            'title' => 'Lezen op Vivat',
            'items' => [
                ['question' => 'Welk type content vind je op Vivat?', 'answer' => 'Vivat biedt artikels over het dagelijkse leven, gezondheid, technologie, financiën, reizen, wonen en andere praktische of inspirerende onderwerpen die vlot leesbaar zijn.'],
                ['question' => 'Moet ik een account aanmaken om artikels te lezen?', 'answer' => 'Nee. Publieke artikels zijn vrij toegankelijk. Je hebt geen lezersaccount nodig om door de rubrieken te bladeren, de zoekfunctie te gebruiken of content te lezen.'],
                ['question' => 'Hoe vind ik een specifiek onderwerp terug?', 'answer' => 'Je kunt via de rubrieken van de site gaan of de zoekfunctie gebruiken om sneller een trefwoord, thema of artikel terug te vinden.'],
                ['question' => 'Zijn artikels geordend per rubriek?', 'answer' => 'Ja. De inhoud is georganiseerd per categorie zodat de navigatie duidelijker is en je de onderwerpen die je interesseren sneller terugvindt.'],
            ],
        ],
        [
            'title' => 'Redactie en bijdragen',
            'items' => [
                ['question' => 'Kan ik een artikel voorstellen aan Vivat?', 'answer' => 'Ja. Als je wilt bijdragen, kun je via de redacteursruimte op de site het inschrijvings- of indieningsproces volgen.'],
                ['question' => 'Wordt alle voorgestelde content automatisch gepubliceerd?', 'answer' => 'Nee. Inhoud kan eerst gecontroleerd of redactioneel verwerkt worden om de samenhang en kwaliteit van de site te bewaren.'],
                ['question' => 'Hoe krijg ik toegang tot mijn redacteursruimte?', 'answer' => 'Als je een auteurs- of bijdragersaccount hebt, kun je inloggen en daarna naar je eigen ruimte gaan om je inhoud en gegevens te beheren.'],
                ['question' => 'Kan ik een artikel wijzigen nadat ik het heb ingediend?', 'answer' => 'Afhankelijk van je status en de toestand van de content kunnen sommige wijzigingen vanuit de bijdragersruimte gebeuren. Indien nodig kun je ook contact opnemen met het team.'],
            ],
        ],
        [
            'title' => 'Newsletter en contact',
            'items' => [
                ['question' => 'Wat bevat de Vivat-newsletter?', 'answer' => 'De newsletter bundelt een selectie recente artikels, uitgelichte content en leestips die rechtstreeks per e-mail worden verstuurd.'],
                ['question' => 'Kan ik me makkelijk uitschrijven voor de newsletter?', 'answer' => 'Ja. Elke verzending moet het mogelijk maken om je abonnement te beheren of je eenvoudig uit te schrijven.'],
                ['question' => 'Hoe neem ik contact op met het Vivat-team?', 'answer' => 'Je kunt de contactpagina gebruiken om een algemene vraag te stellen, feedback op een artikel te geven, informatie op te vragen of contact op te nemen met het team.'],
                ['question' => 'Hoe meld ik problematische content?', 'answer' => 'Als content fout, gevoelig of ongepast lijkt, kun je dit via de contactpagina aan het team melden zodat het snel onderzocht kan worden.'],
                ['question' => 'Wat doet Vivat met mijn gegevens?', 'answer' => 'Gegevens die eventueel via de site of haar diensten worden verzameld, worden verwerkt in het kader van het gebruik van de site, communicatie en verbetering van de diensten, in overeenstemming met het privacybeleid.'],
            ],
        ],
    ]
    : [
        [
            'title' => 'Lire sur Vivat',
            'items' => [
                ['question' => 'Quel type de contenus trouve-t-on sur Vivat ?', 'answer' => "Vivat propose des articles autour du quotidien, de la santé, de la technologie, de la finance, du voyage, de la maison et d'autres sujets pratiques ou inspirants à lire simplement."],
                ['question' => 'Dois-je créer un compte pour consulter les articles ?', 'answer' => "Non. Les articles publics sont consultables librement. Aucun compte lecteur n'est nécessaire pour parcourir les rubriques, utiliser la recherche ou lire les contenus."],
                ['question' => 'Comment retrouver un sujet précis ?', 'answer' => 'Vous pouvez passer par les rubriques du site ou utiliser la recherche pour retrouver un mot-clé, une thématique ou un article plus rapidement.'],
                ['question' => 'Les articles sont-ils classés par rubrique ?', 'answer' => 'Oui. Les contenus sont organisés par catégories pour rendre la navigation plus claire et vous permettre de retrouver plus facilement les sujets qui vous intéressent.'],
            ],
        ],
        [
            'title' => 'Rédaction et contribution',
            'items' => [
                ['question' => 'Puis-je proposer un article à Vivat ?', 'answer' => "Oui. Si vous souhaitez contribuer, vous pouvez passer par l'espace rédacteur prévu sur le site et suivre le parcours d'inscription ou de soumission disponible."],
                ['question' => 'Tous les contenus proposés sont-ils publiés automatiquement ?', 'answer' => "Non. Les contenus peuvent faire l'objet d'une vérification ou d'un traitement éditorial avant publication afin de préserver la cohérence et la qualité du site."],
                ['question' => 'Comment accéder à mon espace rédacteur ?', 'answer' => "Si vous disposez d'un compte auteur ou contributeur, vous pouvez vous connecter puis accéder à votre espace dédié pour gérer vos contenus et vos informations."],
                ['question' => "Puis-je modifier un article après l'avoir soumis ?", 'answer' => "Selon votre statut et l'état du contenu, certaines modifications peuvent être faites depuis l'espace contributeur. Si besoin, vous pouvez aussi contacter l'équipe."],
            ],
        ],
        [
            'title' => 'Newsletter et contact',
            'items' => [
                ['question' => 'Que contient la newsletter Vivat ?', 'answer' => "La newsletter rassemble une sélection d'articles récents, des contenus mis en avant et des idées de lecture envoyées directement par e-mail."],
                ['question' => 'Puis-je me désabonner facilement de la newsletter ?', 'answer' => 'Oui. Chaque envoi doit permettre de gérer votre abonnement ou de vous désinscrire simplement.'],
                ['question' => "Comment contacter l'équipe Vivat ?", 'answer' => "Vous pouvez utiliser la page contact pour poser une question générale, faire un retour sur un article, demander un renseignement ou prendre contact avec l'équipe."],
                ['question' => 'Comment signaler un contenu problématique ?', 'answer' => "Si un contenu vous semble erroné, sensible ou inapproprié, vous pouvez le signaler à l'équipe via la page contact afin qu'il soit examiné rapidement."],
                ['question' => 'Que fait Vivat avec mes données ?', 'answer' => "Les données éventuellement collectées via le site ou ses services sont traitées dans le cadre de l'utilisation du site, de la communication et de l'amélioration des services, conformément à la politique de vie privée."],
            ],
        ],
    ];
$faqIntro = $locale === 'nl'
    ? ['badge' => 'Wegwijzers', 'title' => 'Nuttige antwoorden, op één plek.', 'text' => 'Deze FAQ bundelt de meest gestelde vragen over het lezen van artikels, bijdragen aan de site, de newsletter en de contactmogelijkheden.', 'contact' => 'Contacteer het team', 'search' => 'Zoek een artikel']
    : ['badge' => 'Repères', 'title' => 'Les réponses utiles, au même endroit.', 'text' => 'Cette FAQ reprend les questions les plus fréquentes sur la lecture des articles, la contribution au site, la newsletter et les moyens de contact.', 'contact' => "Contacter l'équipe", 'search' => 'Rechercher un article'];
?>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <div class="flex flex-col rounded-[30px] bg-white p-6 lg:col-span-4" style="gap: 16px; box-shadow: 0 18px 48px rgba(0, 66, 65, 0.08);">
            <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[16px] py-[8px] text-sm font-medium text-[#004241]"><?= htmlspecialchars($faqIntro['badge']) ?></span>
            <h2 class="font-medium text-[#004241]" style="font-size: 32px; line-height: 1.05;"><?= htmlspecialchars($faqIntro['title']) ?></h2>
            <p class="text-[#004241]/75" style="font-size: 17px; line-height: 1.45;"><?= htmlspecialchars($faqIntro['text']) ?></p>
            <div class="grid grid-cols-1 gap-3">
                <a href="/contact" class="group inline-flex items-center justify-between rounded-[24px] bg-[#FFF0B6] px-5 py-4 text-[#004241] no-underline transition hover:bg-[#FBE9A3]">
                    <span class="font-medium"><?= htmlspecialchars($faqIntro['contact']) ?></span>
                    <svg class="mr-2 block h-6 w-6 flex-shrink-0 translate-y-0 transition-transform duration-300 ease-out will-change-transform group-hover:translate-x-[14px] motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="/search" class="group inline-flex items-center justify-between rounded-[24px] bg-[#FFF0B6] px-5 py-4 text-[#004241] no-underline transition hover:bg-[#FBE9A3]">
                    <span class="font-medium"><?= htmlspecialchars($faqIntro['search']) ?></span>
                    <svg class="mr-2 block h-6 w-6 flex-shrink-0 translate-y-0 transition-transform duration-300 ease-out will-change-transform group-hover:translate-x-[14px] motion-reduce:transition-none motion-reduce:group-hover:translate-x-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>

        <div class="flex flex-col lg:col-span-8" style="gap: 24px;">
            <?php foreach ($faqGroups as $group) { ?>
            <section class="rounded-[30px] bg-[#EEF4F1] p-6 md:p-7" style="gap: 18px;">
                <div class="mb-5 flex flex-col" style="gap: 10px;">
                    <span class="inline-flex w-fit items-center justify-center rounded-full bg-white px-[16px] py-[8px] text-sm font-medium text-[#004241]"><?= htmlspecialchars($group['title']) ?></span>
                </div>
                <div class="grid grid-cols-1 gap-4">
                    <?php foreach ($group['items'] as $item) { ?>
                    <div class="rounded-[24px] bg-white px-5 py-4 text-[#004241]" style="box-shadow: 0 10px 28px rgba(0, 66, 65, 0.06);" data-faq-item>
                        <button type="button" class="flex w-full cursor-pointer items-center justify-between gap-4 text-left text-[18px] font-medium md:text-[20px]" aria-expanded="false" data-faq-trigger>
                            <span><?= htmlspecialchars($item['question']) ?></span>
                            <span class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#EBF1EF] text-[#004241] transition duration-300 ease-out data-[state=open]:rotate-45" data-faq-icon data-state="closed">+</span>
                        </button>
                        <div class="h-0 overflow-hidden opacity-0 transition-[height,opacity] duration-300 ease-out" data-faq-panel>
                            <div data-faq-inner>
                                <p class="pt-4 text-[#004241]/78" style="font-size: 17px; line-height: 1.55;"><?= htmlspecialchars($item['answer']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </section>
            <?php } ?>
        </div>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const faqItems = document.querySelectorAll('[data-faq-item]');

        const openPanel = function (item) {
            const trigger = item.querySelector('[data-faq-trigger]');
            const panel = item.querySelector('[data-faq-panel]');
            const icon = item.querySelector('[data-faq-icon]');

            if (!trigger || !panel || !icon) {
                return;
            }

            panel.style.height = '0px';
            panel.classList.remove('opacity-0');
            panel.classList.add('opacity-100');

            requestAnimationFrame(function () {
                panel.style.height = panel.scrollHeight + 'px';
            });

            trigger.setAttribute('aria-expanded', 'true');
            icon.dataset.state = 'open';
        };

        const closePanel = function (item) {
            const trigger = item.querySelector('[data-faq-trigger]');
            const panel = item.querySelector('[data-faq-panel]');
            const icon = item.querySelector('[data-faq-icon]');

            if (!trigger || !panel || !icon) {
                return;
            }

            panel.style.height = panel.scrollHeight + 'px';

            requestAnimationFrame(function () {
                panel.style.height = '0px';
                panel.classList.remove('opacity-100');
                panel.classList.add('opacity-0');
            });

            trigger.setAttribute('aria-expanded', 'false');
            icon.dataset.state = 'closed';
        };

        faqItems.forEach(function (item) {
            const trigger = item.querySelector('[data-faq-trigger]');
            const panel = item.querySelector('[data-faq-panel]');

            if (!trigger || !panel) {
                return;
            }

            trigger.addEventListener('click', function () {
                const isOpen = trigger.getAttribute('aria-expanded') === 'true';

                if (isOpen) {
                    closePanel(item);

                    return;
                }

                openPanel(item);
            });

            panel.addEventListener('transitionend', function (event) {
                if (event.propertyName !== 'height') {
                    return;
                }

                if (trigger.getAttribute('aria-expanded') === 'true') {
                    panel.style.height = 'auto';
                }
            });
        });
    });
</script>
