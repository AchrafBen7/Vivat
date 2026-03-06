# Logboek Vivat Backend — 11 t/m 17 februari 2026

> **Project** : Vivat — Geautomatiseerd mediaplatform  
> **Ontwikkelaar** : Achraf Ben Ali  
> **Periode** : 11–17 februari 2026

---

## Maandag 11 februari 2026

**Documentatie voor de projectleider**

- Uitbreiding van het volledige rapport (`RAPPORT_COMPLET.md`) met gedetailleerde uitleg van de database: alle 11 pipelinetabellen kolom per kolom (types, indexen, foreign keys, rollen). Dit maakt het mogelijk om aan de projectleider precies uit te leggen wat er in de code is gebouwd.
- Uitgebreide uitleg toegevoegd over **Migration 12 — Triggers** : wat een MySQL-trigger is, waarom die wordt gebruikt (`updated_at` op `sources` en `articles`), het exacte bestand (`2024_01_01_000012_create_updated_at_triggers.php`), de SQL (`BEFORE UPDATE ... FOR EACH ROW SET NEW.updated_at = NOW()`), en de `down()`-methode voor rollback.
- Documentatie bijgewerkt: frequentie van de RSS-fetch aangepast in de rapporten van "elke 30 min" naar **"1 keer per dag"** conform de afspraken.
- HTML-versie van het rapport opnieuw gegenereerd voor export naar PDF.

**Bestanden** : `docs/RAPPORT_COMPLET.md`, `docs/RAPPORT_COMPLET.html`

---

## Dinsdag 12 februari 2026

**Performance-audit en optimalisaties**

- Uitgevoerd: **audit van de prestaties** van de API (eager loading, cache, zoekopdrachten, indexen). Conclusie: basis was al goed; verbeterpunten geïdentificeerd voor cache en full-text zoeken.
- Nieuw document: `docs/AUDIT_PERFORMANCE.md` met een **score van 7/10** en een concreet actieplan (doel 8,5/10).
- **Optimalisaties doorgevoerd** :
  - **Zoeken** : in `ArticleController::search()` gebruik van MySQL full-text index (`whereFullText`) op `title` en `excerpt` in plaats van `LIKE '%...%'` (geen indexgebruik). Fallback voor SQLite (tests) en voor `meta_description` behouden.
  - **Hub-pagina** : in `CategoryController::hub()` eager load `->with('category')` toegevoegd voor featured en recente artikelen; **cache van 15 minuten** per categorie-slug; invalidatie bij publicatie van een artikel of wijziging van de categorie.
  - **Lijst categorieën** : `GET /api/public/categories` gecached (TTL 1 uur) met invalidatie bij aanmaken/aanpassen/verwijderen van een categorie.
- In `ArticleController::publish()` invalidatie van de hub- en categoriecache toegevoegd wanneer een artikel wordt gepubliceerd.

**Bestanden** : `docs/AUDIT_PERFORMANCE.md`, `app/Http/Controllers/Api/ArticleController.php`, `app/Http/Controllers/Api/CategoryController.php`

---

## Woensdag 13 februari 2026

**Documentatie en verduidelijkingen**

- Rapport opnieuw gegenereerd (HTML) na de wijzigingen in de rapporttekst en de performance-aanpassingen.
- Statusoverzicht opgesteld: **"Waar staan we?"** — samenvatting van de 18 stappen, de 4 fases, de huidige cijfers (94 routes, 36 tabellen, 17 models, enz.) en de volgende prioriteiten (OpenAI-credits, staging-deploy, frontend-integratie).
- Bevestiging van de **volgorde van ontwikkeling** : eerst de pipeline (Services, Jobs, Horizon, Scheduler), daarna de REST-API; dit staat zo in het journal en het rapport.

**Bestanden** : `docs/RAPPORT_COMPLET.html` (regeneratie)

---

## Donderdag 14 februari 2026

**Uitleg ContentExtractorService**

- Gedetailleerde uitleg opgesteld van de **ContentExtractorService** voor de projectleider: doel (URL → schone content voor de IA), constanten (MIN_PARAGRAPHS, MIN_WORDS, USER_AGENT, REMOVE_TAGS), en stap-voor-stap door de methode `extract()` (HTTP, DOM laden, tags verwijderen, titel/headings/main content, deep scrape indien nodig, interne links). Elke private methode (`loadHtml`, `parseBaseUrl`, `removeTags`, `extractTitle`, `extractHeadings`, `extractMainContent`, `extractLargestTextBlock`, `needsDeepScrape`, `htmlToText`, `extractInternalLinks`) is kort toegelicht.

**Doel** : de projectleider kan de scrapinglogica en de keuzes (article/main vs. body, fallback op grootste tekstblok) begrijpen zonder in de code te duiken.

---

## Vrijdag 15 februari 2026

**Uitleg van de 3 services en de 3 jobs**

- **Drie services** gedocumenteerd:
  - **RssParserService** : parseert RSS 2.0 en Atom XML, levert genormaliseerde items (titel, link, description, pubDate, guid), genereert dedup-hash.
  - **ContentExtractorService** : (zie 14 feb).
  - **ArticleGeneratorService** : laadt verrijkte items, bouwt system- en user-prompt met CategoryTemplate en SEO-keywords, roept OpenAI aan, maakt Article (draft) en ArticleSource aan, zet items op status "used".
- **Drie jobs** gedocumenteerd:
  - **FetchRssFeedJob** (queue `rss`) : HTTP GET van de feed-URL, parse met RssParserService, dedup op hash, aanmaak RssItem (status `new`), update `last_fetched_at`.
  - **EnrichContentJob** (queue `enrichment`) : item op "enriching", ContentExtractorService, controle op minimale tekstlengte, aanroep OpenAI voor lead/headings/key_points/seo_keywords/primary_topic/scores, EnrichedItem aanmaken/bijwerken, item op "enriched" of "failed"; rate limit en retry/backoff.
  - **GenerateArticleJob** (queue `generation`) : delegeert aan ArticleGeneratorService met itemIds, categoryId en customPrompt; geen eigen scheduler, wordt handmatig of via API getriggerd.

**Doel** : één overzicht van wat elk onderdeel van de pipeline doet en hoe ze samenwerken.

---

## Zaterdag 16 februari 2026

**Scheduler en volgorde van het systeem**

- **Scheduler** gedocumenteerd (definitie in `bootstrap/app.php`):
  - **pipeline:fetch-rss** : elke 30 minuten; haalt `RssFeed::dueForFetch()` op en dispatched voor elk flux een `FetchRssFeedJob` (queue `rss`).
  - **pipeline:enrich** : elk uur; haalt tot 50 `RssItem::new()` op en dispatched voor elk een `EnrichContentJob` met delay (3 s tussen elk) op queue `enrichment`.
  - **horizon:snapshot** : elke 5 minuten voor Horizon-metrics.
  - **queue:prune-failed** : dagelijks; verwijdert failed jobs ouder dan 168 uur (7 dagen).
- Verduidelijking: de **generatie van artikelen** wordt niet door de scheduler gedaan; die wordt via de API of een Artisan-commando getriggerd.
- Bevestiging van de **bouwvolgorde in de code** : eerst pipeline (migrations, models, services, jobs, Horizon, scheduler), daarna REST-API (controllers, resources, routes) — zoals vastgelegd in het journal (stap 8 vóór stap 9).

**Bestanden** : geen code wijzigingen; documentatie/verduidelijking.

---

## Zondag 17 februari 2026

**Logboek en afronding**

- Opstellen van dit **logboek (11–17 februari)** in het Nederlands voor archivering en rapportage aan de projectleider.
- Geen nieuwe functionele wijzigingen; focus op documentatie en uitleg van de bestaande code (services, jobs, scheduler, triggers, performance-aanpassingen).

**Bestanden** : `docs/LOGBOEK_11-17_FEB_2026.md`

---

## Samenvatting van de week

| Onderwerp | Actie |
|-----------|--------|
| **Rapport projectleider** | Uitgebreid met gedetailleerde tabellen en uitleg triggers; frequentie RSS aangepast naar 1×/dag in de doc. |
| **Performance** | Audit (7/10), cache (categorieën + hub), full-text zoeken, invalidatie bij publicatie/wijzigingen. |
| **Documentatie** | ContentExtractorService, 3 services, 3 jobs, scheduler en bouwvolgorde gedocumenteerd voor overdracht. |
| **Logboek** | Logboek 11–17 februari in het Nederlands opgesteld. |

---

*Laatste wijziging : 17 februari 2026*
