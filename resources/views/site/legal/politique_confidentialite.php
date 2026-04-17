<?php $locale = $locale ?? 'fr'; ?>
<div class="mx-auto flex w-full max-w-[1280px] flex-col" style="gap: 24px;">
    <section class="rounded-[32px] bg-[#EBF1EF] px-6 py-7 md:px-8 md:py-8">
        <div class="flex flex-col" style="gap: 14px;">
            <span class="inline-flex w-fit items-center justify-center rounded-full bg-white px-[16px] py-[8px] text-sm font-medium text-[#004241]"><?= $locale === 'nl' ? 'Juridisch' : 'Légal' ?></span>
            <h1 class="font-semibold text-[#004241]" style="font-family: Manrope, sans-serif; font-size: clamp(30px, 5vw, 52px); line-height: 1.05;"><?= $locale === 'nl' ? 'Privacybeleid' : 'Politique de confidentialité' ?></h1>
        </div>
    </section>

    <section class="rounded-[30px] bg-white px-6 py-7 md:px-10 md:py-10" style="box-shadow: 0 18px 48px rgba(0,66,65,0.07);">
        <div class="prose max-w-none text-[#004241]/88" style="font-size: 16px; line-height: 1.7;">

        <?php if ($locale === 'nl') { ?>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241; margin-top: 0;">Algemene informatie</h2>
            <p>Dit privacybeleid, hierna "Beleid", is van toepassing op de website <strong>Vivat</strong>, hierna "de Site", en op alle diensten die via deze site worden aangeboden.</p>
            <p>De site die u bezoekt is eigendom van: Media*A SPRL, met maatschappelijke zetel te Kroonlaan 242, 1050 Brussel, België, ingeschreven in de Kruispuntbank van Ondernemingen onder nummer 0479.701.523.</p>
            <p>Alle vragen over dit Beleid kunnen worden gericht aan Media*A SPRL via het volgende e-mailadres: <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a>.</p>
            <p>Media*A SPRL is zich bewust van uw legitieme zorg om uw privacy te laten respecteren bij het gebruik van de Site en de daarin opgenomen diensten. Daarom stelt zij alles in het werk om uw gegevens te verwerken conform de toepasselijke wetgeving en om uw gegevens de vertrouwelijkheid en veiligheid te garanderen die zij vereisen.</p>
            <p>De verwerking van uw gegevens in het kader van het gebruik van de Site en de daarin opgenomen diensten is onderworpen aan het Belgische recht, namelijk de wet van 8 december 1992. Vanaf 25 mei 2018 is de Algemene Verordening Gegevensbescherming (AVG) van toepassing.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Toestemming</h2>
            <p>Elke gegevensverwerking in het kader van het gebruik van de Site en de daarmee verbonden diensten gebeurt op vrijwillige basis, overeenkomstig de voorwaarden van dit Beleid.</p>
            <p>Door de Site te bezoeken erkent u kennis te hebben genomen van en zonder voorbehoud in te stemmen met de bepalingen van dit Beleid. Bij vragen kunt u ons contacteren via de hierboven vermelde contactgegevens.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">De verantwoordelijke voor de verwerking en de bescherming van gegevens</h2>
            <p>Voor de toepassing van de regels inzake privacybescherming is de verantwoordelijke voor de verwerking Media*A SPRL, waarvan de contactgegevens hierboven zijn vermeld.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Verzamelde gegevens, doeleinden, vertrouwelijkheid en veiligheid van de verwerking</h2>
            <p>Uit zorg om de impact van de gegevensverwerking op uw privacy te beperken, beperkt Media*A SPRL zich tot het verzamelen van gegevens die strikt noodzakelijk zijn voor het gebruik van de Site en de daarmee verbonden diensten.</p>
            <p>De volgende gegevens kunnen worden verzameld bij uw gebruik van de Site en de daarmee verbonden diensten:</p>
            <ul>
                <li>Uw naam, voornaam en geboortedatum ter identificatie;</li>
                <li>Uw e-mailadres, postadres en andere relevante informatie om met u te communiceren en op uw vragen te antwoorden;</li>
                <li>Uw IP-adres, automatisch verzameld;</li>
                <li>Informatie over uw gebruik van de Site, inclusief de pagina's die u raadpleegt;</li>
                <li>Alle andere informatie die vrijwillig wordt verstrekt tijdens uw gebruik van de Site en de daarmee verbonden diensten.</li>
            </ul>
            <p>Deze gegevens worden verzameld om de volgende doeleinden te realiseren:</p>
            <ul>
                <li>Toegang verlenen tot de diensten die Media*A SPRL op en via de Site aanbiedt;</li>
                <li>U per e-mail informatie toesturen, waaronder reclame, waarvan wij denken dat deze u kan interesseren;</li>
                <li>Statistieken of marktonderzoeken uitvoeren;</li>
                <li>De Site en de aangeboden diensten verbeteren.</li>
            </ul>
            <p>Media*A SPRL besteedt veel aandacht aan de veiligheid en vertrouwelijkheid van uw gegevens.</p>
            <p>Media*A SPRL neemt alle passende maatregelen om een veilige verwerking van uw gegevens te garanderen. Media*A SPRL verzekert bovendien dat uw gegevens nooit zonder uw toestemming aan derden worden doorgegeven. Media*A SPRL kan uitzonderlijk van deze regel afwijken om te voldoen aan een wettelijke verplichting of aan een geldende regelgeving, of om redenen van veiligheid van de site, van haar gebruikers of van het algemeen publiek.</p>
            <p>Uw gegevens worden niet langer bewaard dan nodig is voor de realisatie van bovenstaande doeleinden.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Uw rechten</h2>
            <p>U kunt zich richten tot de verantwoordelijke voor de verwerking, wiens contactgegevens hierboven zijn vermeld, om bepaalde rechten uit te oefenen die u door de geldende wetgeving worden toegekend.</p>
            <p>U heeft het recht op toegang tot uw gegevens en om onjuiste gegevens te laten wijzigen of onvolledige gegevens aan te vullen.</p>
            <p>In bepaalde door de wetgeving voorziene gevallen kunt u ook de verwijdering van uw gegevens, de beperking van de verwerking of het bezwaar tegen de verwerking verkrijgen. In deze drie laatste gevallen is het echter mogelijk dat u niet langer kunt genieten van alle of een deel van de diensten die op de Site beschikbaar zijn.</p>
            <p>Wanneer u de verwijdering van uw Vivat-account vraagt, kunnen uw persoonlijke identificatie- en contactgegevens onomkeerbaar worden verwijderd of geanonimiseerd. Bepaalde informatie die strikt noodzakelijk is voor het behoud van de redactionele geschiedenis, de boekhoudkundige verantwoording, de preventie van fraude of het naleven van wettelijke verplichtingen kan echter worden bewaard gedurende de door de toepasselijke regelgeving opgelegde termijn.</p>
            <p>U heeft het recht om zich te allen tijde te verzetten tegen de verwerking voor direct-marketingdoeleinden.</p>
            <p>Vanaf 25 mei 2018 heeft u ook het recht op de overdraagbaarheid van uw gegevens, dat u toelaat uw persoonsgegevens die u aan Media*A SPRL heeft verstrekt, in een gestructureerd, gangbaar en machineleesbaar formaat te ontvangen.</p>
            <p>Indien u, ondanks de inspanningen van Media*A SPRL om uw persoonsgegevens te verwerken conform dit Beleid en de toepasselijke wetgeving, een klacht wilt indienen over de verwerking van uw gegevens, verzoeken wij u dit te doen via het volgende e-mailadres: <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a>. U kunt ook een klacht indienen bij de toezichthoudende autoriteit, namelijk in België de Commissie voor de bescherming van de persoonlijke levenssfeer.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Wijzigingen aan het privacybeleid</h2>
            <p>Dit Beleid kan te allen tijde door Media*A SPRL worden gewijzigd, met name wegens wijzigingen aan de Site en de aangeboden diensten, evolutie van de toepasselijke wetgeving of andere legitieme redenen. Wijzigingen aan de huidige bepalingen van het Beleid treden automatisch in werking na 15 dagen zichtbaar te zijn geweest op de Site. Desgevallend wordt u per e-mail op de hoogte gebracht van komende wijzigingen. Uw gebruik van de Site of de daarmee verbonden diensten na deze periode van 15 dagen geldt als uw aanvaarding zonder voorbehoud van de nieuwe bepalingen.</p>
            <p>Deze versie van het privacybeleid werd online geplaatst op 18 juni 2018.</p>

        <?php } else { ?>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241; margin-top: 0;">Informations générales</h2>
            <p>La présente politique de protection de la vie privée, ci-après « Politique », s'applique au site internet <strong>Vivat</strong>, ci-après « le Site », et à l'ensemble des services fournis par son intermédiaire.</p>
            <p>Le site que vous visitez est la propriété de : Media*A SPRL, dont le siège social est établi 242 Av. de la Couronne à 1050 Bruxelles, Belgique, enregistrée à la Banque-carrefour des entreprises sous le n° 0479.701.523.</p>
            <p>Toute question relative à la présente Politique peut être adressée à Media*A SPRL au moyen de l'adresse de courrier électronique : <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a>.</p>
            <p>Media*A SPRL est conscient de votre souci légitime de voir votre vie privée respectée lors de l'utilisation du Site et des services qu'il comprend. C'est pourquoi elle met tout en œuvre pour traiter vos données conformément à la législation applicable et pour assurer à vos données la confidentialité et la sécurité qu'elles requièrent.</p>
            <p>Le traitement de vos données dans le cadre de l'utilisation du Site et des services qu'il comprend est soumis au droit belge, à savoir la loi du 8 décembre 1992. À partir du 25 mai 2018, le règlement général sur la protection des données, RGPD, est d'application. Ces textes peuvent être consultés sur le site de la Commission belge de la protection de la vie privée : <a href="https://www.privacycommission.be/fr/legislation-et-normes" target="_blank" rel="noopener noreferrer" style="color:#004241;">privacycommission.be/fr/legislation-et-normes</a>.</p>
            <p>Pour tout renseignement relatif à la législation applicable à la protection des données, vous pouvez consulter le site de la Commission de la protection de la vie privée : <a href="https://www.privacycommission.be" target="_blank" rel="noopener noreferrer" style="color:#004241;">www.privacycommission.be</a>.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Consentement</h2>
            <p>Tout traitement de données opéré dans le cadre de l'utilisation du Site et des services y associés s'effectue sur une base volontaire, conformément aux conditions arrêtées dans la présente Politique.</p>
            <p>En visitant le Site, vous reconnaissez avoir pris connaissance et accepter sans réserve les dispositions de la présente Politique. En cas de question, vous pouvez nous contacter en utilisant les données de contact reprises ci-dessus.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Le responsable de traitements et de la protection des données</h2>
            <p>Aux fins de l'application des règles qui régissent la protection de la vie privée, le responsable de traitement est Media*A SPRL, dont les coordonnées et les données de contact figurent ci-dessus.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Les données collectées, les finalités, la confidentialité et la sécurité du traitement</h2>
            <p>Soucieux de limiter l'impact du traitement de données sur votre vie privée, Media*A SPRL se limite à collecter les données qui sont strictement nécessaires à l'utilisation du Site et des services y associés.</p>
            <p>Les données suivantes sont ainsi susceptibles d'être collectées lors de votre utilisation du Site et des services y associés :</p>
            <ul>
                <li>Vos nom, prénom et date de naissance à des fins d'identification ;</li>
                <li>Votre adresse de courriel, votre adresse postale, et autres informations pertinentes permettant de communiquer avec vous et de répondre à vos demandes ;</li>
                <li>Votre adresse IP, collectée automatiquement ;</li>
                <li>Des informations concernant votre utilisation du Site, y compris les pages que vous consultez ;</li>
                <li>Toute autre information donnée volontairement lors de votre utilisation du Site et des services y associés.</li>
            </ul>
            <p>Ces données sont collectées en vue de réaliser les finalités suivantes :</p>
            <ul>
                <li>Permettre votre accès aux services proposés par Media*A SPRL sur le Site et au moyen du Site ;</li>
                <li>Vous transmettre, par courriels, des informations notamment publicitaires, dont nous pensons qu'elles peuvent vous intéresser ;</li>
                <li>Réaliser des statistiques ou des études de marché ;</li>
                <li>Améliorer le Site et les services qui y sont fournis.</li>
            </ul>
            <p>Media*A SPRL est très attentive à la sécurité et la confidentialité de vos données.</p>
            <p>Media*A SPRL prend toutes les mesures adéquates pour garantir un traitement sécurisé de vos données. Media*A SPRL assure par ailleurs que vos données ne seront jamais transmises à des tiers sans votre consentement. Media*A SPRL ne pourrait exceptionnellement déroger à cette règle que pour se conformer à une obligation légale ou à une réglementation en vigueur ou pour des motifs tenant à la sécurité du site, de ses utilisateurs ou du public en général.</p>
            <p>Vos données ne seront pas conservées au-delà de la durée nécessaire pour réaliser les finalités qui précèdent.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Vos droits</h2>
            <p>Vous pouvez vous adresser au responsable de traitement, dont les coordonnées figurent ci-dessus, en vue d'exercer certains droits qui vous sont reconnus par le droit en vigueur.</p>
            <p>Vous disposez du droit d'accéder à vos données et d'obtenir la modification de données inexactes ou la possibilité de compléter des données incomplètes.</p>
            <p>Dans certains cas prévus par la législation, vous pouvez également obtenir l'effacement de vos données, la limitation du traitement ou vous opposer au traitement. Dans ces trois dernières hypothèses, vous pourriez cependant ne plus avoir la possibilité de bénéficier de tout ou partie des services mis à votre disposition sur le Site.</p>
            <p>Lorsque vous demandez la suppression de votre compte sur Vivat, vos données personnelles d'identification et de contact peuvent être supprimées ou anonymisées de manière irréversible. Certaines informations strictement nécessaires à la conservation de l'historique éditorial, à la justification comptable, à la prévention des fraudes ou au respect d'obligations légales peuvent toutefois être conservées pendant la durée imposée par la réglementation applicable.</p>
            <p>Vous disposez du droit de vous opposer à tout moment au traitement à des fins de prospection.</p>
            <p>À partir du 25 mai 2018, vous disposez également du droit à la portabilité de vos données, qui vous permet de recevoir les données à caractère personnel qui vous concernent et que vous avez fournies à Media*A SPRL, et ce, dans un format structuré, couramment utilisé et lisible par machine.</p>
            <p>Si, en dépit des efforts mis par Media*A SPRL à traiter vos données à caractère personnel en conformité avec les principes de la présente Politique et avec les règles découlant de la législation applicable, vous souhaitez formuler une réclamation à propos du traitement de vos données dans le cadre de l'utilisation du Site ou d'un service y associé, nous vous prions de nous en faire part au moyen de l'adresse de courriel suivante : <a href="mailto:info@mediaa.be" style="color:#004241;">info@mediaa.be</a>. Vous pouvez également introduire une réclamation auprès de l'autorité de contrôle, à savoir en Belgique la Commission de la protection de la vie privée, dont vous pouvez retrouver les données de contact en consultant le lien suivant : <a href="https://www.privacycommission.be/fr/contact" target="_blank" rel="noopener noreferrer" style="color:#004241;">privacycommission.be/fr/contact</a>.</p>

            <h2 style="font-size: 20px; font-weight: 600; color: #004241;">Modifications de la Politique de protection de la vie privée</h2>
            <p>La présente Politique est susceptible d'être modifiée à tout moment par Media*A SPRL, notamment en raison de la modification du Site et des services qu'il comporte, de l'évolution de la législation applicable, mais également pour d'autres motifs légitimes. Les modifications qui seraient apportées aux dispositions actuelles de la Politique entrent automatiquement en vigueur après avoir été affichées durant 15 jours sur le Site. Le cas échéant, vous serez informé par courriel des modifications à venir. Votre utilisation du Site et ou des services y associés au-delà de cette période de 15 jours témoigne de votre acceptation sans réserve des nouvelles dispositions adoptées.</p>
            <p>La présente version de la Politique de protection de la vie privée a été mise en ligne le 18 juin 2018.</p>

        <?php } ?>

        </div>
    </section>
</div>
