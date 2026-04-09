# Werkplan 5 dagen implementatie (Nederlands)

Dit document legt uit wat er over 5 dagen is gebouwd voor het Vivat-site. Het is geschreven voor **iedereen** of je nu technisch bent of niet.

---

## Begrippen die je nodig hebt

| Begrip | Vereenvoudigde uitleg |
|--------|------------------------|
| **API** | Een "venster" waarmee apps of andere systemen data kunnen opvragen (bv. mobiele app, partners). |
| **Site public** | De website die bezoekers zien de 5 pagina's die we hebben gebouwd. |
| **Cache** | Een **tijdelijke kopie** van data. In plaats van elke keer opnieuw te zoeken in de database, gebruiken we deze kopie. Snel en minder belasting. |
| **TTL** | *Time To Live* hoe lang die kopie geldig blijft (bij ons: 30 min). |
| **Database** | De opslag waar alle artikelen, categorieën, etc. staan. |

---

## Dag 1: Hoe haalt de website haar data op?

### In het kort (voor iedereen)

De website die bezoekers zien bestaat uit **5 pagina's**. Die halen hun inhoud **niet** op via de API, maar rechtstreeks uit dezelfde bron waar de API ook uit put. Ze delen dus dezelfde "kennis" (data en logica), maar op een andere manier.

**Vergelijking** : De API is als een ober die bestellingen doorgeeft aan de keuken. De website gaat zelf naar de keuken geen ober nodig. Beide krijgen hetzelfde gerecht (de data).

### De 5 pagina's

| Pagina | Wat bezoekers zien | URL |
|--------|--------------------|-----|
| **Home** | Startpagina met laatste artikelen | `/` |
| **Categorieën** | Overzicht van alle rubrieken | `/categories` |
| **Hub categorie** | Artikelen van één rubriek | `/categories/economie` (voorbeeld) |
| **Lijst artikelen** | Alle artikelen met paginering | `/articles` |
| **Artikel** | Eén artikel lezen | `/articles/mon-article` |

### Voor developers

De site public roept geen HTTP-endpoints aan. Data komt uit:

- `PublicPageDataService::getHomeData()` home
- `PublicPageDataService::getCategoryHubData()` hub
- `PublicPageDataService::getArticlesIndexData()` lijst artikelen
- Controllers voor categorieën en individuele artikelen

---

## Dag 2: Cache waarom de home snel laadt

### In het kort (voor iedereen)

De homepagina toont veel data (artikelen, categorieën, enz.). Telkens alles opnieuw uit de database halen zou traag zijn. Daarom maken we een **tijdelijke kopie** : de **cache**.

- **Eerste bezoek** : We halen alles op en slaan het 30 minuten op.
- **Volgende bezoeken (binnen 30 min)** : We tonen de kopie. Snel, geen extra belasting.
- **Na 30 min** : De kopie is "verlopen". Het volgende bezoek haalt alles opnieuw op en maakt een nieuwe kopie.

**Vergelijking** : Net als een foto van een bord dat 30 min geldig is. Binnen die 30 min toon je de foto. Daarna maak je een nieuwe foto (of je wacht tot iemand het bord wijzigt zie Dag 3).

### Waarom 30 minuten?

- Er verschijnen meestal weinig nieuwe artikelen per dag.
- Artikelen worden zelden aangepast.
- Minder belasting op de database.
- Als je **wel** publiceert, wordt de cache direct leeggemaakt (Dag 3) dan zie je het nieuwe artikel meteen.

### Voor developers

- Cache-key: `vivat.home`
- TTL: 1800 seconden (30 min) in `config/vivat.php`
- Overschrijfbaar via `VIVAT_HOME_CACHE_TTL` in `.env`
- Mechanisme: `Cache::remember('vivat.home', 1800, closure)`

---

## Dag 3: Direct zichtbaar wat gebeurt er bij publiceren?

### In het kort (voor iedereen)

Als je een artikel publiceert, bewerkt of verwijdert, wil je dat bezoekers dat **meteen** zien. Daarom legen we de cache zodra er iets verandert. De "oude foto" wordt weggegooid.

**Wat triggert het legen van de cache?**

| Actie | Effect voor bezoekers |
|-------|------------------------|
| **Artikel publiceren** | Home, categorieën en hub tonen het nieuwe artikel bij het volgende bezoek. |
| **Artikel bewerken** | Zelfde wijzigingen zijn direct zichtbaar. |
| **Artikel verwijderen** | Het artikel verdwijnt direct van de site. |
| **Categorie aanmaken / bewerken / verwijderen** | Overzichten worden direct bijgewerkt. |

**Belangrijk** : Er wordt niets automatisch opnieuw geladen. Pas wanneer iemand de pagina opent *na* de wijziging, halen we de nieuwe data op en maken we een nieuwe cache.

### Voor developers

- `Cache::forget('vivat.home')` (en andere keys) bij: publish, update, destroy, store (als published), categorie-wijzigingen.
- Eerste request na invalidatie: `Cache::remember` vindt niets → closure → verse data → nieuwe cache.

---

## Dag 4: De pagina « Toutes les actualités »

### In het kort (voor iedereen)

We hebben een aparte pagina gebouwd waar **alle artikelen** staan. Die is bereikbaar via de knop "Autres actualités" op de homepagina.

**Wat zie je?**

- Titel: "Toutes les actualités"
- Een grid met kaarten: categorie, titel, datum, leestijd, afbeelding
- Paginering: 12 artikelen per pagina, met knoppen "Précédent" en "Suivant"

**Waarom geen cache?**

Deze pagina verandert vaker (nieuwe artikelen, paginering). Elke keer verse data ophalen is hier eenvoudiger en blijft voldoende snel (12 artikelen per pagina).

### Voor developers

- Route: `GET /articles` → `ArticleController@index`
- Service: `PublicPageDataService::getArticlesIndexData()` geen cache, 12 per pagina
- View: `resources/views/site/articles_index.php`

---

## Dag 5: Afronding en overzicht

### In het kort (voor iedereen)

Alles is geconfigureerd en gedocumenteerd. Het gedrag is nu duidelijk:

| Situatie | Wat gebeurt er? |
|----------|-----------------|
| Je publiceert een nieuw artikel | Bij de volgende bezoeker: het artikel staat op de home. |
| Je bewerkt of verwijdert een artikel | Bij de volgende bezoeker: de wijziging is zichtbaar. |
| Geen wijzigingen, iemand bezoekt binnen 30 min | De "snelle kopie" wordt getoond geen nieuwe zoekactie. |
| Geen wijzigingen, iemand bezoekt na 30 min | De kopie is verlopen we halen alles opnieuw op. |
| Je wijzigt een categorie | Relevante pagina's tonen de wijziging direct. |

### Voor developers

- TTL configureerbaar via `VIVAT_HOME_CACHE_TTL`
- Documentatie: `API_ENDPOINTS_ET_CACHE.md`, dit werkplan
- Geen background jobs voor refresh alles on-demand

---

## Overzicht per dag

| Dag | Onderwerp | In één zin |
|-----|-----------|------------|
| **1** | Architectuur | De website haalt data rechtstreeks op, niet via de API. |
| **2** | Cache | De home slaat een kopie op voor 30 min om snel te laden. |
| **3** | Invalidatie | Bij publicatie/wijziging wordt de kopie geleegd wijzigingen zijn direct zichtbaar. |
| **4** | Articles index | Nieuwe pagina met alle artikelen en paginering. |
| **5** | Afronding | Configuratie en documentatie afgerond. |
