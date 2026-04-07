<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Seeder;

class FakeDutchArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get()->keyBy('slug');

        if ($categories->isEmpty()) {
            $this->command?->warn('Aucune catégorie trouvée. Lancez d’abord le seeding des catégories.');

            return;
        }

        $articles = $this->articles();
        $created = 0;
        $updated = 0;

        foreach ($articles as $index => $data) {
            $category = $categories->get($data['category_slug']) ?? $categories->first();
            $coverImageUrl = 'https://picsum.photos/seed/'.$data['slug'].'/1200/800';

            $article = Article::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'meta_title' => $data['title'],
                    'meta_description' => $data['excerpt'],
                    'keywords' => $data['keywords'],
                    'category_id' => $category?->id,
                    'language' => 'nl',
                    'reading_time' => $data['reading_time'],
                    'status' => 'published',
                    'article_type' => $data['article_type'],
                    'cover_image_url' => $coverImageUrl,
                    'quality_score' => 100,
                    'published_at' => now()->subDays(20 - $index)->setTime(rand(7, 18), rand(0, 59)),
                ]
            );

            if ($article->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->command?->info("20 fake NL-artikelen verwerkt. Nieuw: {$created}, bijgewerkt: {$updated}.");
    }

    private function articles(): array
    {
        return [
            [
                'slug' => 'waarom-steeds-meer-jonge-gezinnen-kiezen-voor-een-weekend-dichtbij-huis',
                'category_slug' => 'famille',
                'title' => 'Waarom steeds meer jonge gezinnen kiezen voor een weekend dicht bij huis',
                'excerpt' => 'Korte uitstappen dichtbij blijken goedkoper, eenvoudiger te plannen en vaak verrassend ontspannend voor jonge gezinnen.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['gezin', 'weekend', 'België', 'vrije tijd'],
                'content' => '<p>Voor veel jonge gezinnen is een verre reis niet langer de enige manier om even te ontsnappen aan de dagelijkse routine. Een weekend dichtbij huis biedt vaak voldoende afwisseling zonder ingewikkelde planning.</p><p>Lokale uitstappen zijn meestal beter betaalbaar, vragen minder organisatie en laten gezinnen toe om spontaner te vertrekken. Bovendien ontdekken veel ouders opnieuw hoe rijk het aanbod in eigen streek eigenlijk is.</p><p>Volgens verschillende toeristische spelers stijgt de vraag naar korte gezinsvriendelijke verblijven al meerdere seizoenen op rij. Vooral natuur, kindvriendelijke activiteiten en flexibiliteit spelen daarin een grote rol.</p>',
            ],
            [
                'slug' => 'de-comeback-van-thuis-koken-bij-stedelijke-twintigers',
                'category_slug' => 'au-quotidien',
                'title' => 'De comeback van thuis koken bij stedelijke twintigers',
                'excerpt' => 'Steeds meer jongvolwassenen koken opnieuw thuis om geld te besparen en bewuster met voeding om te gaan.',
                'reading_time' => 5,
                'article_type' => 'hot_news',
                'keywords' => ['koken', 'jongeren', 'budget', 'eten'],
                'content' => '<p>Na jaren van snelle maaltijden en delivery-apps herontdekken veel twintigers het plezier van zelf koken. Dat heeft niet alleen met smaak te maken, maar ook met budget en gezondheid.</p><p>Wie thuis kookt, controleert beter wat er op het bord ligt en houdt de kosten makkelijker onder controle. Ook sociale media spelen mee: eenvoudige recepten maken koken toegankelijker dan ooit.</p><p>Voor velen is koken intussen meer dan een verplichting. Het is ook een rustmoment geworden na een drukke dag.</p>',
            ],
            [
                'slug' => 'waarom-goed-onderhouden-wagens-sneller-verkopen-op-de-tweedehandsmarkt',
                'category_slug' => 'finance',
                'title' => 'Waarom goed onderhouden wagens sneller verkopen op de tweedehandsmarkt',
                'excerpt' => 'Een verzorgde wagen straalt vertrouwen uit en helpt verkopers hun vraagprijs beter te verdedigen.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['auto', 'tweedehands', 'verkoop', 'waarde'],
                'content' => '<p>Op de tweedehandsmarkt telt de eerste indruk enorm. Een nette carrosserie, een proper interieur en duidelijke foto’s zorgen ervoor dat een wagen sneller de aandacht trekt.</p><p>Potentiële kopers linken een verzorgde presentatie vaak automatisch aan goed onderhoud. Daardoor verloopt de onderhandeling meestal vlotter en kan de verkoper zijn prijs beter verantwoorden.</p><p>Detailing en voorbereiding worden daardoor steeds belangrijker in het verkoopproces van gebruikte wagens.</p>',
            ],
            [
                'slug' => 'slimmer-omgaan-met-energieverbruik-begint-bij-kleine-gewoontes',
                'category_slug' => 'energie',
                'title' => 'Slimmer omgaan met energieverbruik begint bij kleine gewoontes',
                'excerpt' => 'Kleine dagelijkse aanpassingen maken een merkbaar verschil op de energiefactuur van gezinnen.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['energie', 'verbruik', 'factuur', 'woning'],
                'content' => '<p>Wie zijn energieverbruik wil verlagen, denkt vaak meteen aan grote investeringen. Toch begint een besparing meestal met eenvoudige gewoontes die weinig moeite vragen.</p><p>Elektrische toestellen volledig uitschakelen, korter ventileren en bewuster verwarmen zijn voorbeelden van ingrepen die snel effect hebben. Voor gezinnen levert dat op jaarbasis vaak een zichtbare besparing op.</p><p>Experts benadrukken dat de combinatie van kleine gewoontes en gerichte renovatie het meeste oplevert.</p>',
            ],
            [
                'slug' => 'de-populariteit-van-flexibele-thuiswerkplekken-blijft-stijgen',
                'category_slug' => 'chez-soi',
                'title' => 'De populariteit van flexibele thuiswerkplekken blijft stijgen',
                'excerpt' => 'Mensen richten hun woning steeds vaker zo in dat werken, leven en ontspannen beter samengaan.',
                'reading_time' => 5,
                'article_type' => 'long_form',
                'keywords' => ['thuiswerk', 'interieur', 'woning', 'werkplek'],
                'content' => '<p>Nu hybride werken voor veel werknemers de norm is geworden, verandert ook de manier waarop mensen naar hun woning kijken. Een vaste bureauhoek is niet langer een luxe, maar een praktische noodzaak.</p><p>Veel bewoners zoeken naar flexibele oplossingen: een compacte werktafel, slimme opbergruimte of meubels die meerdere functies combineren. Zo blijft de leefruimte bruikbaar zonder dat werk alles overneemt.</p><p>Interieurprofessionals merken dat comfort, licht en rust de belangrijkste criteria zijn voor een goede thuiswerkplek.</p>',
            ],
            [
                'slug' => 'waarom-basisstukken-opnieuw-centraal-staan-in-de-mode',
                'category_slug' => 'mode',
                'title' => 'Waarom basisstukken opnieuw centraal staan in de mode',
                'excerpt' => 'Tijdloze kleding en sobere combinaties winnen terrein op de snel veranderende modemarkt.',
                'reading_time' => 3,
                'article_type' => 'standard',
                'keywords' => ['mode', 'minimalisme', 'kleding', 'stijl'],
                'content' => '<p>Consumenten kiezen steeds vaker voor eenvoud in hun garderobe. In plaats van elk seizoen nieuwe trends te volgen, groeit de voorkeur voor stukken die langer meegaan.</p><p>Dat heeft te maken met budget, duurzaamheid en gebruiksgemak. Een beperkte maar veelzijdige garderobe maakt combineren eenvoudiger en zorgt voor minder impulsaankopen.</p><p>Modeketens spelen daarop in met collecties die minder vluchtig en meer praktisch worden voorgesteld.</p>',
            ],
            [
                'slug' => 'korte-pauzes-overdag-helpen-mentale-vermoeidheid-te-verminderen',
                'category_slug' => 'sante',
                'title' => 'Korte pauzes overdag helpen mentale vermoeidheid te verminderen',
                'excerpt' => 'Regelmatige onderbrekingen tijdens het werk verbeteren concentratie en verlagen stressniveaus.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['gezondheid', 'stress', 'werk', 'pauze'],
                'content' => '<p>Wie lange tijd zonder onderbreking doorwerkt, merkt vaak dat concentratie en energie snel afnemen. Korte pauzes kunnen dat effect beperken.</p><p>Onderzoekers wijzen erop dat zelfs enkele minuten weg van een scherm al voldoende kunnen zijn om de aandacht te herstellen. Een korte wandeling of een rustige ademhalingsoefening helpt vaak meer dan gedacht.</p><p>Werkgevers besteden daarom steeds meer aandacht aan realistische werkritmes en mentale gezondheid.</p>',
            ],
            [
                'slug' => 'waarom-belgen-opnieuw-kiezen-voor-korte-stedentrips',
                'category_slug' => 'voyage',
                'title' => 'Waarom Belgen opnieuw kiezen voor korte stedentrips',
                'excerpt' => 'Korte citytrips combineren flexibiliteit, cultuur en een beheersbaar budget voor veel reizigers.',
                'reading_time' => 4,
                'article_type' => 'hot_news',
                'keywords' => ['reizen', 'citytrip', 'België', 'weekend'],
                'content' => '<p>Na een periode waarin grote reizen minder vanzelfsprekend waren, kiezen veel Belgen opnieuw voor korte stedentrips. Die zijn eenvoudiger te plannen en vragen minder voorbereiding.</p><p>Een citytrip van twee of drie dagen biedt voldoende afwisseling zonder zware logistiek. Reizigers combineren cultuur, gastronomie en ontspanning binnen een beperkt budget.</p><p>Toeristische spelers zien vooral een sterke vraag naar bestemmingen die snel bereikbaar zijn per trein of auto.</p>',
            ],
            [
                'slug' => 'hoe-digitale-gewoontes-het-dagelijkse-leven-van-gezinnen-veranderen',
                'category_slug' => 'technologie',
                'title' => 'Hoe digitale gewoontes het dagelijkse leven van gezinnen veranderen',
                'excerpt' => 'Van boodschappenlijstjes tot schermtijd: technologie beïnvloedt steeds meer gezinsroutines.',
                'reading_time' => 5,
                'article_type' => 'long_form',
                'keywords' => ['technologie', 'gezin', 'apps', 'digitale gewoontes'],
                'content' => '<p>Digitale hulpmiddelen zijn diep doorgedrongen in het dagelijkse leven van gezinnen. Agenda’s, schoolcommunicatie, betalingen en zelfs maaltijdplanning verlopen steeds vaker via apps.</p><p>Dat biedt comfort, maar roept ook vragen op over balans en schermtijd. Ouders zoeken daarom naar manieren om technologie nuttig te houden zonder dat ze het hele gezinsritme gaat bepalen.</p><p>Volgens experts ligt de uitdaging niet in minder technologie, maar in bewuster gebruik ervan.</p>',
            ],
            [
                'slug' => 'waarom-kleine-balkons-steeds-creatiever-worden-ingedeeld',
                'category_slug' => 'chez-soi',
                'title' => 'Waarom kleine balkons steeds creatiever worden ingedeeld',
                'excerpt' => 'Ook beperkte buitenruimtes worden vandaag slim ingericht als volwaardige rustplek in huis.',
                'reading_time' => 3,
                'article_type' => 'standard',
                'keywords' => ['balkon', 'interieur', 'buitenruimte', 'wonen'],
                'content' => '<p>In steden wordt elke vierkante meter belangrijker. Dat zie je ook aan de manier waarop bewoners hun balkon gebruiken.</p><p>Kleine buitenruimtes worden ingericht met opklapmeubels, plantenbakken en compacte verlichting. Zo ontstaat een plek die zowel functioneel als aangenaam is.</p><p>Voor veel bewoners is zo’n balkon intussen een verlengstuk van de leefruimte geworden.</p>',
            ],
            [
                'slug' => 'de-nieuwe-aandacht-voor-financiele-rust-bij-jonge-werknemers',
                'category_slug' => 'finance',
                'title' => 'De nieuwe aandacht voor financiële rust bij jonge werknemers',
                'excerpt' => 'Steeds meer jonge werknemers zoeken niet alleen een hoger loon, maar vooral voorspelbare financiële stabiliteit.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['geld', 'werk', 'jongeren', 'stabiliteit'],
                'content' => '<p>Voor jonge werknemers is financiële rust een belangrijker criterium geworden bij de keuze van een job. Niet alleen het loon telt, maar ook voorspelbaarheid en zekerheid.</p><p>Extra voordelen, duidelijke kosten en de mogelijkheid om te plannen op langere termijn spelen een grote rol. In een context van stijgende levensduurte wordt stabiliteit opnieuw een prioriteit.</p><p>Werkgevers die daarin helder communiceren, blijken aantrekkelijker voor jonge profielen.</p>',
            ],
            [
                'slug' => 'waarom-lichte-maaltijden-op-werkdagen-aan-populariteit-winnen',
                'category_slug' => 'au-quotidien',
                'title' => 'Waarom lichte maaltijden op werkdagen aan populariteit winnen',
                'excerpt' => 'Meer mensen kiezen op drukke werkdagen bewust voor eenvoudigere en lichtere maaltijden.',
                'reading_time' => 3,
                'article_type' => 'standard',
                'keywords' => ['voeding', 'werkdag', 'maaltijd', 'routine'],
                'content' => '<p>Tijdens drukke werkdagen kiezen veel mensen voor eenvoudige maaltijden die snel klaar zijn en toch voedzaam blijven. Dat patroon is de afgelopen jaren duidelijk sterker geworden.</p><p>Lichte gerechten geven minder een zwaar gevoel tijdens de dag en passen beter in een ritme met werk, verplaatsingen en beperkte tijd.</p><p>Maaltijdplanning en voorbereiding op voorhand helpen die gewoonte vol te houden.</p>',
            ],
            [
                'slug' => 'meer-bewoners-investeren-in-eenvoudige-isolatie-oplossingen',
                'category_slug' => 'energie',
                'title' => 'Meer bewoners investeren in eenvoudige isolatie-oplossingen',
                'excerpt' => 'Niet elke energiebesparing vraagt zware renovatie: kleine ingrepen worden steeds populairder.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['isolatie', 'energie', 'woning', 'besparing'],
                'content' => '<p>De stijgende energieprijzen hebben veel bewoners aangezet om sneller kleine verbeteringen aan hun woning door te voeren. Tochtstrips, dakisolatie en aangepaste gordijnen zijn populaire voorbeelden.</p><p>Die ingrepen vragen minder budget dan grote renovaties, maar kunnen op korte termijn al een verschil maken. Daardoor zijn ze voor veel huishoudens een haalbare eerste stap.</p><p>Adviseurs benadrukken wel dat een goede diagnose belangrijk blijft om de juiste keuzes te maken.</p>',
            ],
            [
                'slug' => 'de-invloed-van-sociale-media-op-moderne-opvoedingskeuzes',
                'category_slug' => 'famille',
                'title' => 'De invloed van sociale media op moderne opvoedingskeuzes',
                'excerpt' => 'Ouders halen inspiratie online, maar botsen tegelijk op druk, vergelijkingen en tegenstrijdige adviezen.',
                'reading_time' => 5,
                'article_type' => 'long_form',
                'keywords' => ['ouders', 'sociale media', 'opvoeding', 'gezin'],
                'content' => '<p>Sociale media zijn voor veel ouders een snelle bron van ideeën geworden. Van lunchboxen tot slaapschema’s: advies is overal beschikbaar en lijkt altijd binnen handbereik.</p><p>Tegelijk zorgt die overvloed aan informatie voor twijfel en vergelijking. Ouders krijgen het gevoel dat ze voortdurend keuzes moeten verantwoorden of optimaliseren.</p><p>Pedagogen pleiten daarom voor meer vertrouwen in eigen context en minder druk om perfecte routines te volgen.</p>',
            ],
            [
                'slug' => 'waarom-eenvoudige-accessoires-een-outfit-sterker-maken',
                'category_slug' => 'mode',
                'title' => 'Waarom eenvoudige accessoires een outfit sterker maken',
                'excerpt' => 'Een sobere look krijgt vaak meer karakter door één goed gekozen accessoire dan door meerdere trends tegelijk.',
                'reading_time' => 3,
                'article_type' => 'standard',
                'keywords' => ['accessoires', 'mode', 'stijl', 'outfit'],
                'content' => '<p>In hedendaagse mode draait het minder om overdaad en meer om balans. Een eenvoudige outfit kan veel sterker ogen door één doordacht detail.</p><p>Een sjaal, tas of opvallend paar schoenen geeft karakter zonder de hele look te verzwaren. Daardoor kiezen veel consumenten opnieuw voor minder, maar gerichtere aankopen.</p><p>Die evolutie sluit aan bij een bredere voorkeur voor duurzame en tijdloze stijlkeuzes.</p>',
            ],
            [
                'slug' => 'slaapritme-blijft-een-onderschatte-factor-voor-dagelijkse-energie',
                'category_slug' => 'sante',
                'title' => 'Slaapritme blijft een onderschatte factor voor dagelijkse energie',
                'excerpt' => 'Niet alleen de duur van de slaap telt, ook regelmaat blijkt cruciaal voor hoe mensen zich overdag voelen.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['slaap', 'energie', 'gezondheid', 'routine'],
                'content' => '<p>Veel mensen focussen op het aantal uren slaap, maar vergeten dat regelmaat minstens even belangrijk is. Een vast ritme helpt het lichaam om beter te herstellen.</p><p>Onregelmatige slaapuren verstoren concentratie, humeur en energieniveau. Vooral op lange termijn kan dat zwaar doorwegen op het dagelijkse functioneren.</p><p>Gezondheidsexperts raden daarom aan om ook in drukke periodes een zo stabiel mogelijk slaapritme te bewaren.</p>',
            ],
            [
                'slug' => 'digitale-planning-maakt-korte-reizen-minder-stressvol',
                'category_slug' => 'voyage',
                'title' => 'Digitale planning maakt korte reizen minder stressvol',
                'excerpt' => 'Reizigers combineren apps en slimme tools om hun korte trips vlotter en flexibeler te organiseren.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['reizen', 'apps', 'planning', 'weekendtrip'],
                'content' => '<p>Van tickets en routeplanning tot restauranttips: digitale tools helpen reizigers om hun korte trips sneller te organiseren. Zeker bij spontane uitstappen is dat een groot voordeel.</p><p>Door alles op één plek te bewaren, verliezen mensen minder tijd aan praktische details. Zo blijft er meer ruimte over voor de ervaring zelf.</p><p>De populariteit van korte trips sluit daardoor ook aan bij een bredere digitalisering van vrijetijdsplanning.</p>',
            ],
            [
                'slug' => 'waarom-meer-gezinnen-kiezen-voor-een-gedeelde-digitale-agenda',
                'category_slug' => 'technologie',
                'title' => 'Waarom meer gezinnen kiezen voor een gedeelde digitale agenda',
                'excerpt' => 'Een gezamenlijke agenda helpt gezinnen om school, werk en vrije tijd beter op elkaar af te stemmen.',
                'reading_time' => 3,
                'article_type' => 'standard',
                'keywords' => ['agenda', 'gezin', 'technologie', 'organisatie'],
                'content' => '<p>Gezinnen met drukke agenda’s zoeken steeds vaker naar eenvoudige digitale oplossingen om afspraken overzichtelijk te houden. Een gedeelde agenda is daarbij een populaire keuze.</p><p>Ze maakt het makkelijker om schoolmomenten, werkroosters en vrije tijd op elkaar af te stemmen. Daardoor neemt het risico op misverstanden of dubbele planning af.</p><p>De tool lijkt eenvoudig, maar heeft in de praktijk vaak een groot effect op de dagelijkse rust binnen het gezin.</p>',
            ],
            [
                'slug' => 'meer-mensen-vervangen-grote-renovaties-door-gerichte-woonupdates',
                'category_slug' => 'chez-soi',
                'title' => 'Meer mensen vervangen grote renovaties door gerichte woonupdates',
                'excerpt' => 'Kleine, doordachte aanpassingen winnen terrein tegenover grote en dure renovatieprojecten.',
                'reading_time' => 4,
                'article_type' => 'standard',
                'keywords' => ['renovatie', 'woning', 'interieur', 'budget'],
                'content' => '<p>In plaats van hun woning volledig te verbouwen, kiezen veel bewoners voor kleinere ingrepen met direct zichtbaar effect. Dat maakt renovatie beter beheersbaar in tijd en budget.</p><p>Nieuwe verlichting, aangepaste verfkleuren of betere opbergruimte veranderen vaak al veel aan het wooncomfort. Daardoor wordt het idee van wonen stap voor stap aantrekkelijker.</p><p>Die aanpak past bij een bredere trend van bewuster investeren in comfort zonder alles in één keer om te gooien.</p>',
            ],
            [
                'slug' => 'jonge-professionals-zoeken-meer-balans-dan-status-in-hun-uitgaven',
                'category_slug' => 'finance',
                'title' => 'Jonge professionals zoeken meer balans dan status in hun uitgaven',
                'excerpt' => 'Voor veel jonge professionals draait consumeren minder om uitstraling en meer om grip op het dagelijks leven.',
                'reading_time' => 5,
                'article_type' => 'long_form',
                'keywords' => ['consumptie', 'jongeren', 'budget', 'levensstijl'],
                'content' => '<p>Uitgavenpatronen van jonge professionals veranderen zichtbaar. Waar status vroeger een belangrijk motief was, zoeken velen vandaag vooral balans en voorspelbaarheid.</p><p>Dat zie je in keuzes rond wonen, mobiliteit en vrije tijd. Minder impulsaankopen en meer aandacht voor gebruikswaarde worden steeds normaler.</p><p>Volgens analisten past die houding in een generatie die sneller nadenkt over lange termijn en financiële ademruimte.</p>',
            ],
        ];
    }
}
