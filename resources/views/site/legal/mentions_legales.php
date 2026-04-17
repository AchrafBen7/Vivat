<?php $locale = $locale ?? 'fr'; ?>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section class="rounded-[32px] bg-[#EBF1EF] px-6 py-7 md:px-8 md:py-8">
        <div class="flex flex-col" style="gap: 14px;">
            <span class="inline-flex w-fit items-center justify-center rounded-full bg-white px-[16px] py-[8px] text-sm font-medium text-[#004241]"><?= $locale === 'nl' ? 'Juridisch' : 'Légal' ?></span>
            <h1 class="font-semibold text-[#004241]" style="font-family: Manrope, sans-serif; font-size: clamp(30px, 5vw, 52px); line-height: 1.05;"><?= $locale === 'nl' ? 'Wettelijke vermeldingen' : 'Mentions légales' ?></h1>
        </div>
    </section>

    <section class="rounded-[30px] bg-white px-6 py-7 md:px-10 md:py-10" style="box-shadow: 0 18px 48px rgba(0,66,65,0.07);">
        <div class="prose max-w-none text-[#004241]/88" style="font-size: 16px; line-height: 1.7;">

        <?php if ($locale === 'nl') { ?>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241; margin-top: 0;">Uitgever van de site</h2>
            <p>De site <strong>vivat.be</strong> wordt uitgegeven door:<br>
            MEDIAA SPRL<br>
            Kroonlaan 242 – 1050 Brussel<br>
            E-mail: <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a><br>
            Ondernemingsnummer: 0479.701.523<br>
            Hoofdredacteur: Mathieu Fontaine</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Hosting</h2>
            <p>De site <strong>vivat.be</strong> wordt gehost door:<br>
            Combell nv<br>
            Skaldenstraat 121<br>
            9042 Gent<br>
            België<br><br>
            Ondernemingsnummer: BE 0541.977.701<br>
            RPR Gent</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Intellectuele eigendom</h2>
            <p>De site <strong>vivat.be</strong>, zijn inhoud en diensten, software, tekeningen, modellen, databanken, merken en logo's zijn onderworpen aan het intellectuele eigendomsrecht. Deze verschillende elementen zijn eigendom van Media*A. Media*A, die de site <strong>vivat.be</strong> uitgeeft, verleent de gebruiker enkel een niet-exclusief en niet-overdraagbaar gebruiksrecht, beperkt tot niet-commercieel gebruik via browsen, deelname en keuze van inschrijving op de verschillende diensten. Media*A behoudt zich dan ook alle exploitatierechten voor wat betreft verspreiding, overdracht en elk ander recht op de elementen die haar site en diensten vormen.</p>
            <p>Alle inhoud van derden of commerciële inhoud die op de site wordt getoond, blijft eigendom van de respectieve eigenaars, inclusief foto's, en is beschermd door het intellectuele eigendomsrecht. Elke reproductie van inhoud van derden is verboden zonder voorafgaande toestemming van de auteur.</p>
            <p>De onderscheidende tekens en met name de gedeponeerde merken die op de site <strong>vivat</strong> worden genoemd, zijn voorwerp van exclusieve rechten en mogen bijgevolg niet worden gereproduceerd zonder de toestemming van hun houders.</p>
            <p>Wanneer een gebruiker kiest voor de participatieve diensten van de site, zoals beoordelingen, commentaren, fora en meer algemeen wanneer hij bijdraagt aan inhoud met het oog op publicatie op de site <strong>vivat</strong>, verbindt hij zich ertoe de toepasselijke regels inzake auteursrecht, bescherming van persoonsgegevens en privacy te respecteren.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Aansprakelijkheid</h2>
            <p>Media*A stelt alles in het werk om een kwalitatieve site en diensten aan te bieden. Desondanks kan Media*A niet aansprakelijk worden gesteld voor enige onbeschikbaarheid, storing, wijziging of fout die zich zou voordoen bij het gebruik van haar site of haar diensten, behoudens voor de gevallen voorzien in de specifieke voorwaarden van bepaalde diensten. Media*A kan de continuïteit, toegankelijkheid en absolute veiligheid van de dienst niet garanderen, gezien de risico's verbonden aan het internet.</p>
            <p>De gebruiker erkent uitdrukkelijk dat hij de site en diensten van <strong>vivat.be</strong> op eigen risico gebruikt.</p>
            <p>Media*A kan niet aansprakelijk worden gesteld voor enige schade, direct of indirect, van welke aard ook, die zich voordoet bij het gebruik van haar site of diensten, behoudens voor schade veroorzaakt door een tekortkoming in haar verplichtingen.</p>
            <p>Media*A kan niet aansprakelijk worden gesteld voor de relaties, contractueel of niet, tussen adverteerders of partners en gebruikers van haar sites en diensten, behoudens uitdrukkelijke contractuele bepaling.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Onrechtmatige inhoud melden</h2>
            <p>U kunt onrechtmatige inhoud melden door te schrijven naar: <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a></p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Verwijdering van account en persoonsgegevens</h2>
            <p>Gebruikers kunnen de verwijdering van hun account vragen overeenkomstig de toepasselijke regelgeving inzake gegevensbescherming. Wanneer dit verzoek ontvankelijk is, worden de direct identificerende persoonsgegevens verwijderd of geanonimiseerd, onder voorbehoud van de informatie die bewaard moet blijven om te voldoen aan wettelijke, boekhoudkundige, fiscale, veiligheids- of bewijsverplichtingen.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Webmastering en natuurlijke verwijzing</h2>
            <p>De site <strong>Vivat.be</strong>, zijn code en zijn databank worden ontwikkeld en onderhouden door het webbureau Media*A (<a href="https://www.mediaa.be" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.mediaa.be</a>) gevestigd in Genval, België. De natuurlijke verwijzing van de website in zoekmachines (SEO) wordt eveneens verzekerd door het e-marketingteam van het bureau.</p>

        <?php } else { ?>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241; margin-top: 0;">Éditeur du site</h2>
            <p>Le site <strong>vivat.be</strong> est édité par :<br>
            MEDIAA SPRL<br>
            Av. de la Couronne, 242 – 1050 Bruxelles<br>
            E-mail : <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a><br>
            Numéro d'entreprise : 0479.701.523<br>
            Rédacteur en chef : Mathieu Fontaine</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Hébergement</h2>
            <p>Le site <strong>vivat.be</strong> est hébergé par :<br>
            Combell nv<br>
            Skaldenstraat 121<br>
            9042 Gent<br>
            België<br><br>
            Ondernemingsnummer : BE 0541.977.701<br>
            RPR Gent</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Propriété intellectuelle</h2>
            <p>Le site <strong>vivat.be</strong>, ses contenus et services, ses logiciels, dessins, modèles, bases de données, marques et logos sont soumis au droit de la propriété intellectuelle. Ces différents éléments sont la propriété de la société Media*A. La société Media*A qui édite le site <strong>vivat.be</strong> ne confère à l'utilisateur qu'un droit non exclusif et incessible d'utilisation, entendu comme un usage non commercial caractérisé par la navigation, la participation et le choix de la souscription aux différents services. Elle se réserve par conséquent les droits d'exploitation de diffusion, cession, ainsi que tout autre droit sur les éléments qui constituent son site et ses services.</p>
            <p>Tous les contenus tiers ou commerciaux présentés sur le site sont la propriété de leurs propriétaires respectifs, en ce compris les photographies, et sont protégés par le droit de la propriété intellectuelle. Toute reproduction d'un contenu tiers est interdite sans l'accord préalable de son auteur.</p>
            <p>Les signes distinctifs et notamment les marques déposées cités sur le site <strong>vivat</strong> sont l'objet de droits exclusifs et ne peuvent, à ce titre, pas être reproduits sans l'accord de leurs titulaires.</p>
            <p>Lorsqu'il fait le choix de recourir aux services participatifs du site, notamment avis, commentaires, forums et plus généralement lorsqu'il participe à un contenu en vue de sa diffusion sur le site <strong>vivat</strong>, l'utilisateur accepte de respecter les règles applicables en matière de droit d'auteur, de protection des données personnelles et de la vie privée.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Responsabilité</h2>
            <p>La société Media*A met en place les moyens pour assurer un site et des services de qualité. Néanmoins, la société Media*A ne saurait être tenue pour responsable d'une quelconque indisponibilité, défaillance, modification ou erreur survenue lors de l'utilisation de son site ou de ses services, à l'exception de celles prévues dans les conditions spécifiques inhérentes à certains services. La société Media*A ne saurait garantir la continuité, l'accessibilité et la sécurité absolue du service, compte tenu des risques liés à Internet.</p>
            <p>L'utilisateur reconnaît expressément utiliser le site et les services <strong>vivat.be</strong> à ses seuls et entiers risques et périls.</p>
            <p>La société Media*A ne pourra être tenue pour responsable de tout dommage, direct ou indirect, de quelque nature qu'il soit, survenu lors de l'utilisation de son site ou de ses services, à l'exception de ceux qui auraient pour cause un manquement à ses obligations.</p>
            <p>La société Media*A ne saurait être tenue pour responsable des relations, contractuelles ou non, entre les annonceurs ou partenaires et les utilisateurs de ses sites et services, sauf stipulation contractuelle expresse.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Signaler un contenu illicite</h2>
            <p>Vous pouvez signaler un contenu illicite en écrivant à l'adresse : <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a></p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Suppression de compte et données personnelles</h2>
            <p>Les utilisateurs peuvent demander la suppression de leur compte conformément à la réglementation applicable en matière de protection des données. Lorsque cette demande est recevable, les données personnelles directement identifiantes sont supprimées ou anonymisées, sous réserve des informations qui doivent être conservées pour répondre à des obligations légales, comptables, fiscales, de sécurité ou de preuve.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Webmastering et référencement naturel</h2>
            <p>Le site <strong>Vivat.be</strong>, son code et sa base de donnée, est développé et maintenu par l'agence web Media*A (<a href="https://www.mediaa.be" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.mediaa.be</a>) située à Genval en Belgique. Le référencement naturel du site internet dans les moteurs de recherche (SEO) est également assuré par l'équipe e-marketing de l'agence.</p>

        <?php } ?>

        </div>
    </section>
</div>
