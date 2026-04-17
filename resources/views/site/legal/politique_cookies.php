<?php $locale = $locale ?? 'fr'; ?>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section class="rounded-[32px] bg-[#EBF1EF] px-6 py-7 md:px-8 md:py-8">
        <div class="flex flex-col" style="gap: 14px;">
            <span class="inline-flex w-fit items-center justify-center rounded-full bg-white px-[16px] py-[8px] text-sm font-medium text-[#004241]"><?= $locale === 'nl' ? 'Juridisch' : 'Légal' ?></span>
            <h1 class="font-semibold text-[#004241]" style="font-family: Manrope, sans-serif; font-size: clamp(30px, 5vw, 52px); line-height: 1.05;"><?= $locale === 'nl' ? 'Cookiebeleid' : 'Politique de cookies' ?></h1>
        </div>
    </section>

    <section class="rounded-[30px] bg-white px-6 py-7 md:px-10 md:py-10" style="box-shadow: 0 18px 48px rgba(0,66,65,0.07);">
        <div class="prose max-w-none text-[#004241]/88" style="font-size: 16px; line-height: 1.7;">

        <?php if ($locale === 'nl') { ?>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241; margin-top: 0;">1. Algemeen</h2>
            <p>Vivat gebruikt "cookies" onder meer om het gebruik van haar website te verbeteren en om bezoekersstatistieken van de website op te stellen.</p>
            <p>De site die u bezoekt is eigendom van: Media*A SPRL, met maatschappelijke zetel te Kroonlaan 242, 1050 Elsene, België, ingeschreven bij de Kruispuntbank van Ondernemingen onder nr. 0479 701 523.</p>
            <p>Een "cookie" is een klein informatiebestand dat automatisch wordt opgeslagen op de computer of mobiel apparaat van een gebruiker wanneer hij een site bezoekt. Deze "cookies" kunnen de gebruiker niet identificeren, maar worden gebruikt om informatie over zijn surfgedrag op de site te registreren. Deze "cookies" maken het bijvoorbeeld mogelijk dat de site u herkent bij latere bezoeken, bepaalde voorkeuren onthoudt, zoals uw taalvoorkeur. Ze vergemakkelijken ook de interactie tussen de site en de gebruiker. Ten slotte maken ze het mogelijk om de inhoud en de advertenties op een site relevanter te maken voor de gebruiker.</p>
            <p>Vivat hecht er belang aan te voldoen aan de vereisten van de wet van 13 juni 2005 betreffende de elektronische communicatie wat betreft "cookies".</p>
            <p>Dit cookiebeleid, dat het privacybeleid aanvult, is bedoeld om u te informeren over de soorten cookies die op onze site worden gebruikt, over hun doel en hun bewaartermijn.</p>
            <p>U heeft de mogelijkheid om u te verzetten tegen de plaatsing van "cookies" wanneer u om aanvaarding wordt gevraagd. U moet echter weten dat deze weigering kan leiden tot het verlies van bepaalde functionaliteiten van onze site.</p>
            <p>Voor alle vragen over ons cookiebeleid kunt u ons altijd contacteren op het volgende adres: <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a>.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">2. Beheer van cookies</h2>
            <p>U kunt de instellingen van uw webbrowser gebruiken om cookies te weigeren, uit te schakelen of reeds geïnstalleerde cookies te verwijderen.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">3. Cookies gebruikt op Vivat</h2>
            <p>De volgende cookies kunnen op de site worden gebruikt:</p>

            <h3 style="font-size: 18px; font-weight: 600; color: #004241;">3.1 Functionele cookies</h3>
            <p>Deze cookies houden rechtstreeks verband met het gebruik van de site en haar functionaliteiten. Ze zijn bedoeld om de navigatie te vergemakkelijken.</p>
            <p>Deze cookies hebben een geldigheidsduur van één jaar.</p>

            <h3 style="font-size: 18px; font-weight: 600; color: #004241;">3.2 Cookies voor publieksmeting</h3>
            <p>Vivat gebruikt Google Analytics om publieksmetingen uit te voeren.</p>
            <p>Als u deze cookies wilt uitschakelen, klik dan op <a href="https://tools.google.com/dlpage/gaoptout?hl=nl" target="_blank" rel="noopener noreferrer" style="color:#004241;">tools.google.com/dlpage/gaoptout?hl=nl</a>.</p>

            <h3 style="font-size: 18px; font-weight: 600; color: #004241;">3.3 Reclame-cookies</h3>
            <p>Wanneer u onze website bezoekt, worden sociaal-demografische gegevens en profielgegevens verzameld om anoniem in cookies te worden opgeslagen.</p>
            <p>Deze worden geïnstalleerd door onze partners bij het verspreiden van hun advertenties.</p>
            <p>Ze voorkomen dat u herhaaldelijk met dezelfde advertenties wordt geconfronteerd, meten de doeltreffendheid van de reclame en personaliseren de reclame op de site.</p>
            <p>Hieronder vindt u de lijst van onze partners en de links naar hun cookiebeleid.</p>
            <ul>
                <li>DFP, Google: <a href="https://support.google.com/dfp_premium/answer/2839090?hl=nl" target="_blank" rel="noopener noreferrer" style="color:#004241;">support.google.com/dfp_premium/answer/2839090?hl=nl</a></li>
                <li>Adux: <a href="http://www.adux.com/groupe/" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.adux.com/groupe/</a></li>
                <li>Quantum: <a href="http://quantum-advertising.com/" target="_blank" rel="noopener noreferrer" style="color:#004241;">quantum-advertising.com</a></li>
                <li>Ligatus: <a href="https://www.ligatus.com/fr/privacy-policy" target="_blank" rel="noopener noreferrer" style="color:#004241;">ligatus.com/fr/privacy-policy</a></li>
                <li>Smilewanted: <a href="https://www.smilewanted.com/legal.php" target="_blank" rel="noopener noreferrer" style="color:#004241;">smilewanted.com/legal.php</a></li>
                <li>Appnexus: <a href="https://www.appnexus.com/en/company/platform-privacy-policy-fr" target="_blank" rel="noopener noreferrer" style="color:#004241;">appnexus.com/en/company/platform-privacy-policy-fr</a></li>
                <li>Dailymotion: <a href="https://www.dailymotion.com/legal/cookiemanagement" target="_blank" rel="noopener noreferrer" style="color:#004241;">dailymotion.com/legal/cookiemanagement</a></li>
                <li>Andréa Media: <a href="http://www.andreamedia.com/" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.andreamedia.com</a></li>
            </ul>
            <p>Als u reclame-cookies wilt weigeren, kunt u dat doen via de volgende website: <a href="http://www.youronlinechoices.com" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.youronlinechoices.com</a>.</p>

            <h3 style="font-size: 18px; font-weight: 600; color: #004241;">3.4 Cookies van sociale netwerken – Facebook</h3>
            <p>Deze cookie laat u toe de sociale module te gebruiken, bijvoorbeeld Vind-ik-leuk, Delen, enz. Facebook gebruikt onder meer cookies om het gebruik van zijn diensten te vergemakkelijken, informatie over u te registreren en reclame te tonen.</p>
            <p>Voor meer informatie raadpleeg het privacybeleid: <a href="https://www.facebook.com/policies/cookies/" target="_blank" rel="noopener noreferrer" style="color:#004241;">facebook.com/policies/cookies</a>.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">4. Aanvaarding van cookies</h2>
            <p>Door het vakje aan te vinken dat wordt weergegeven wanneer u de site voor het eerst bezoekt of door de site te blijven gebruiken, aanvaardt u dat cookies op uw computer of mobiel apparaat worden geïnstalleerd en dat Vivat vervolgens toegang heeft tot de zo opgeslagen gegevens.</p>
            <p>U kunt uw toestemming te allen tijde intrekken door een e-mail te sturen naar het adres: <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a>.</p>
            <p>Deze procedure is gratis.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">5. Wijziging van het cookiebeleid</h2>
            <p>Dit cookiebeleid kan te allen tijde door Vivat worden gewijzigd, met name wegens wijzigingen aan de site en de daarin opgenomen diensten, evolutie van de toepasselijke wetgeving, maar ook om andere legitieme redenen. Wijzigingen aan de huidige bepalingen van het cookiebeleid treden automatisch in werking na 15 dagen zichtbaar te zijn geweest op de site. Desgevallend wordt u per e-mail op de hoogte gebracht van komende wijzigingen. Uw gebruik van de site of de daarmee verbonden diensten na deze periode van 15 dagen geldt als uw aanvaarding zonder voorbehoud van de nieuwe bepalingen.</p>
            <p>Deze versie van het cookiebeleid werd online geplaatst op 11 juni 2018.</p>

        <?php } else { ?>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241; margin-top: 0;">1. Généralités</h2>
            <p>Vivat utilise des « cookies » afin notamment d'améliorer l'utilisation de son site web et afin d'établir des statistiques de visites de son site internet.</p>
            <p>Le site que vous visitez est la propriété de : Media*A SPRL, dont le siège social est établi 242 Av. de la Couronne à 1050 Ixelles, Belgique, enregistrée à la Banque-carrefour des entreprises sous le n° 0479 701 523.</p>
            <p>Un « cookie » est un petit fichier d'information enregistré automatiquement sur l'ordinateur ou l'appareil mobile d'un utilisateur lorsqu'il visite un site. Ces « cookies » ne permettent pas d'identifier l'utilisateur mais servent à enregistrer des informations relatives à sa navigation sur le site. Ces « cookies » permettent par exemple au site de vous reconnaître lors de vos visites ultérieures, de retenir certaines de vos préférences, comme votre préférence linguistique. Ils facilitent également l'interaction entre le site et l'utilisateur. Ils permettent enfin de rendre le contenu et la publicité présente sur un site plus pertinents pour l'utilisateur.</p>
            <p>Vivat met un point d'honneur à satisfaire aux exigences prévues par la loi du 13 juin 2005 relative aux communications électroniques s'agissant des « cookies ».</p>
            <p>La présente politique des cookies, qui complète la politique de la vie privée, vise dès lors à vous informer sur les types de cookies utilisés sur notre site, sur leur finalité, leur durée de conservation.</p>
            <p>Vous avez la possibilité de vous opposer au placement de « cookies » lorsque la demande d'acceptation vous est soumise. Toutefois, vous devez savoir que ce refus peut avoir pour conséquence de vous priver de certaines fonctionnalités de notre site.</p>
            <p>Pour toute question relative à notre politique d'utilisation de cookies, vous pouvez toujours nous contacter à l'adresse suivante : <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a>.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">2. Gestion des cookies</h2>
            <p>Vous pouvez utiliser les paramètres de votre navigateur web pour refuser, désactiver des cookies ou supprimer des cookies déjà installés.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">3. Cookies utilisés sur Vivat</h2>
            <p>Les cookies suivants peuvent être utilisés sur le site :</p>

            <h3 style="font-size: 18px; font-weight: 600; color: #004241;">3.1 Cookies fonctionnels</h3>
            <p>Ces cookies sont directement liés à l'utilisation du site et de ses fonctionnalités. Ils ont pour objectif de faciliter la navigation.</p>
            <p>Ces cookies ont une durée de validité d'un an.</p>

            <h3 style="font-size: 18px; font-weight: 600; color: #004241;">3.2 Cookies liés à la mesure d'audience</h3>
            <p>Vivat utilise Google Analytics pour effectuer des mesures d'audience.</p>
            <p>Si vous souhaitez désactiver ces cookies, cliquez sur <a href="https://tools.google.com/dlpage/gaoptout?hl=fr" target="_blank" rel="noopener noreferrer" style="color:#004241;">tools.google.com/dlpage/gaoptout?hl=fr</a>.</p>

            <h3 style="font-size: 18px; font-weight: 600; color: #004241;">3.3 Cookies publicitaires</h3>
            <p>Lorsque vous visitez notre site web, des données socio-démographiques et des données de profil sont collectées pour être stockées de façon anonyme dans des cookies.</p>
            <p>Ceux-ci sont installés par nos partenaires lors de la diffusion de leurs annonces.</p>
            <p>Ils permettent d'éviter que vous soyez confronté, de manière répétitive, aux mêmes annonces publicitaires, de mesurer l'efficacité de la publicité et de personnaliser la publicité sur le site.</p>
            <p>Vous trouverez ci-dessous la liste de nos partenaires ainsi que les liens vers leurs politiques des cookies.</p>
            <ul>
                <li>DFP, Google : <a href="https://support.google.com/dfp_premium/answer/2839090?hl=fr" target="_blank" rel="noopener noreferrer" style="color:#004241;">support.google.com/dfp_premium/answer/2839090?hl=fr</a></li>
                <li>Adux : <a href="http://www.adux.com/groupe/" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.adux.com/groupe/</a></li>
                <li>Quantum : <a href="http://quantum-advertising.com/" target="_blank" rel="noopener noreferrer" style="color:#004241;">quantum-advertising.com</a></li>
                <li>Ligatus : <a href="https://www.ligatus.com/fr/privacy-policy" target="_blank" rel="noopener noreferrer" style="color:#004241;">ligatus.com/fr/privacy-policy</a></li>
                <li>Smilewanted : <a href="https://www.smilewanted.com/legal.php" target="_blank" rel="noopener noreferrer" style="color:#004241;">smilewanted.com/legal.php</a></li>
                <li>Appnexus : <a href="https://www.appnexus.com/en/company/platform-privacy-policy-fr" target="_blank" rel="noopener noreferrer" style="color:#004241;">appnexus.com/en/company/platform-privacy-policy-fr</a></li>
                <li>Dailymotion : <a href="https://www.dailymotion.com/legal/cookiemanagement" target="_blank" rel="noopener noreferrer" style="color:#004241;">dailymotion.com/legal/cookiemanagement</a></li>
                <li>Andréa Media : <a href="http://www.andreamedia.com/" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.andreamedia.com</a></li>
            </ul>
            <p>Si vous souhaitez refuser les cookies publicitaires, vous pouvez le faire à l'aide du site web suivant : <a href="http://www.youronlinechoices.com" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.youronlinechoices.com</a>.</p>

            <h3 style="font-size: 18px; font-weight: 600; color: #004241;">3.4 Cookies des réseaux sociaux – Facebook</h3>
            <p>Ce cookie vous permet d'utiliser le module social, par exemple J'aime, Partage, etc. Facebook utilise notamment des cookies pour simplifier l'utilisation de ses services, enregistrer des informations vous concernant et diffuser de la publicité.</p>
            <p>Pour en savoir plus, veuillez consulter la politique de confidentialité : <a href="https://www.facebook.com/policies/cookies/" target="_blank" rel="noopener noreferrer" style="color:#004241;">facebook.com/policies/cookies</a>.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">4. Acceptation des cookies</h2>
            <p>Par le fait de cocher la case affichée lorsque vous accédez pour la première fois au site ou en continuant à utiliser le site, vous acceptez que des cookies soient installés sur votre ordinateur ou votre appareil mobile et que Vivat accède par la suite aux données ainsi stockées.</p>
            <p>Vous pouvez retirer votre consentement à tout moment en envoyant un courriel à l'adresse : <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a>.</p>
            <p>Cette démarche est gratuite.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">5. Modification de la politique des cookies</h2>
            <p>La présente politique des cookies est susceptible d'être modifiée à tout moment par Vivat, notamment en raison de la modification du site et des services qu'il comporte, de l'évolution de la législation applicable, mais également pour d'autres motifs légitimes. Les modifications qui seraient apportées aux dispositions actuelles de la politique des cookies entrent automatiquement en vigueur après avoir été affichées durant 15 jours sur le site. Le cas échéant, vous serez informé par courriel des modifications à venir. Votre utilisation du site et ou des services y associés au-delà de cette période de 15 jours témoigne de votre acceptation sans réserve des nouvelles dispositions adoptées.</p>
            <p>La présente version de la politique des cookies a été mise en ligne le 11 juin 2018.</p>

        <?php } ?>

        </div>
    </section>
</div>
