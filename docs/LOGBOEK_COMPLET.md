# Logboek Vivat Backend — Vanaf het begin van het project

> **Project** : Vivat — Geautomatiseerd mediaplatform  
> **Ontwikkelaar** : Achraf Ben Ali  
> **Periode** : januari – februari 2026 (data bij benadering)  
> **Status** : Backend operationeel, klaar voor frontend-integratie

---

## Fase 1 — Voorbereiding en architectuur

---

### Dag 1 — Contexte en technische documentatie (vóór Laravel)

**Wat gedaan** : Geen code geschreven; eerst alle referentiedocumenten opgesteld zodat het team en de IA dezelfde visie en architectuur hebben.

- **CONTEXTE_PROJET.md** : Visie, 12 geplande functionaliteiten, doelstack (Laravel 12, MySQL, Redis, Horizon, OpenAI, Stripe, FR/NL), Laravel Boost, lijst van te bouwen bestanden voor het pipeline.
- **docs/EXEMPLES_CODE_REFERENCE.md** : Codevoorbeelden voor FetchRssFeedJob, RssParserService, EnrichContentJob, ContentExtractorService, ArticleGeneratorService, Horizon, Controller/Request/Policy, rate limiting.
- **docs/MIGRATIONS_REFERENCE.md** : De 12 pipelinemigraties (sources → categories → rss_feeds → rss_items → enriched_items → clusters → cluster_items → articles → article_sources → category_templates → pipeline_jobs → triggers), MySQL 8, UUID.
- **docs/MODELS_REFERENCE.md** : De 11 Eloquent-models van het pipeline (HasUuids, relaties, scopes, helpers), o.a. scopeDueForFetch.
- **docs/SCHEMA_AUTRES_FONCTIONNALITES.md** : Schema voor de overige functionaliteiten (users, categories, contributed_articles, payments, enz.).
- **.cursor/rules/contexte-projet.mdc** : Regel om altijd CONTEXTE_PROJET.md en de 5 referentiebestanden te gebruiken.

---

### Dag 2 — Aanmaak project Laravel 12 en Laravel Boost

**Wat gedaan** : Laravel 12-project aangemaakt; Laravel Boost geïnstalleerd voor Cursor; regels en conventies vastgelegd.

- `composer create-project laravel/laravel vivat-temp`, inhoud verplaatst naar `Vivat/`.
- `composer require laravel/boost --dev` en `php artisan boost:install --guidelines --skills --mcp --no-interaction`.
- Laravel-structuur: `app/`, `config/`, `database/`, `routes/`, `public/`, enz.
- Boost: `.cursor/rules/laravel-boost.mdc`, `.cursor/mcp.json`, `AGENTS.md`, `boost.json`.
- CONTEXTE_PROJET.md bijgewerkt: "Projet Laravel créé… 5 fichiers de référence + Laravel Boost".

---

### Dag 3 — Configuratie MySQL

**Wat gedaan** : Database omgezet van SQLite (Laravel-default) naar MySQL 8.

- `.env` en `.env.example` : `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE=vivat`, `DB_USERNAME`, `DB_PASSWORD`.
- UTF-8 (utf8mb4) voor alle teksten.
- Ontwikkelaar moet lokaal de database `vivat` aanmaken en daarna `php artisan migrate:status` uitvoeren.

---

### Dag 4 — Docker (lokale omgeving)

**Wat gedaan** : Volledige Docker-omgeving voor lokaal ontwikkelen: PHP 8.3, MySQL 8, Redis, phpMyAdmin.

- **Dockerfile** : PHP 8.3 Alpine, extensies (pdo_mysql, redis, gd, zip, bcmath, intl, enz.), Composer, entrypoint.
- **docker-compose.yml** : Services `app` (poort 8000), `mysql` (8.0, 3306), `redis` (7), healthcheck MySQL, volumes voor data en code.
- **docker/entrypoint.sh** : `composer install` indien nodig, rechten op storage/bootstrap/cache.
- **.dockerignore** : Beperken van build-context.
- **docs/DOCKER.md** : Instructies voor start, eerste run (key:generate, migrate), nuttige commando’s.
- Gebruik: `docker compose up -d --build` en `docker compose exec app php artisan migrate`.

---

## Fase 2 — Content Acquisition Engine (kernfunctionaliteit)

---

### Dag 5 — Migraties van het pipeline (12 tabellen)

**Wat gedaan** : Alle 12 migraties voor het pipeline geschreven en uitgevoerd; MySQL geconfigureerd voor triggers.

- Migraties: `2024_01_01_000001_create_sources_table.php` t/m `2024_01_01_000012_create_updated_at_triggers.php`.
- Tabellen: sources, categories, rss_feeds, rss_items, enriched_items, clusters, cluster_items, articles, article_sources, category_templates, pipeline_jobs + triggers voor `updated_at` op sources en articles.
- docker-compose.yml: `command: ["--log_bin_trust_function_creators=1"]` voor MySQL.
- Commando: `docker compose run --rm app php artisan migrate --force`.

---

### Dag 6 — phpMyAdmin

**Wat gedaan** : phpMyAdmin toegevoegd aan Docker voor visuele inspectie van de database.

- docker-compose.yml: service `phpmyadmin` (poort 8080, PMA_HOST=mysql).
- docs/DOCKER.md: URL http://localhost:8080, inloggegevens.

---

### Dag 7 — Eloquent-models van het pipeline (11 models)

**Wat gedaan** : De 11 models aangemaakt die bij de pipelinetabellen horen.

- Bestanden: Source.php, Category.php, RssFeed.php, RssItem.php, EnrichedItem.php, Cluster.php, ClusterItem.php, CategoryTemplate.php, Article.php, ArticleSource.php, PipelineJob.php.
- HasUuids, fillable, casts, relaties (hasMany, belongsTo, belongsToMany, hasOne), scopes (active, dueForFetch, new, enriched, published, enz.), methodes (isEnriched, isPublishable, publish, start/complete/fail voor PipelineJob).

---

### Dag 8 — Services, Jobs, Horizon en Scheduler

**Wat gedaan** : De volledige pipelinelogica geïmplementeerd: 3 services, 3 jobs, Horizon, rate limiter OpenAI, geplande taken.

- **Services** : RssParserService (RSS/Atom parsen, generateDedupHash), ContentExtractorService (HTML extraheren via DOMDocument), ArticleGeneratorService (artikel genereren via OpenAI, Article + ArticleSource, sanitize, reading_time, quality_score).
- **Jobs** : FetchRssFeedJob (queue `rss`), EnrichContentJob (queue `enrichment`, RateLimited `openai`), GenerateArticleJob (queue `generation`).
- **config/horizon.php** : Supervisors voor rss, enrichment, generation, default; waits; omgevingen local/production.
- AppServiceProvider: RateLimiter voor `openai` (50/min per item).
- config/services.php: openai (api_key, model, max_tokens).
- bootstrap/app.php: withSchedule() — fetch RSS elke 30 min, verrijking elk uur (50 items, delay 3s), horizon:snapshot elke 5 min, queue:prune-failed dagelijks.
- .env.example: OPENAI_API_KEY, OPENAI_MODEL, OPENAI_MAX_TOKENS.
- `composer require laravel/horizon` en `php artisan horizon:install`.

---

### Dag 9 — REST-API (Postman)

**Wat gedaan** : REST-API voor het pipeline opgezet: routes onder /api, controllers, JSON-resources; alles testbaar in Postman.

- routes/api.php: sources (apiResource), categories, rss-feeds, rss-items, articles (incl. generate, generate-async, publish), stats.
- Resources: SourceResource, CategoryResource, RssFeedResource, RssItemResource, EnrichedItemResource, ArticleResource, ArticleSourceResource.
- Controllers: SourceController, CategoryController, RssFeedController, RssItemController, ArticleController, StatsController (CRUD + generate, publish).
- Validatie in controllers; paginatie op rss-items en articles (per_page, max 50).
- docs/API_POSTMAN.md: overzicht endpoints voor Postman.

---

### Dag 10 — Form Requests en Policies

**Wat gedaan** : Validatie naar Form Requests verplaatst; autorisatie via Policies (voorbereid voor latere auth).

- Form Requests: StoreSourceRequest, UpdateSourceRequest, StoreRssFeedRequest, UpdateRssFeedRequest, StoreArticleRequest, UpdateArticleRequest, GenerateArticleRequest (item_ids 1–10, custom_prompt).
- Policies: SourcePolicy, RssFeedPolicy, ArticlePolicy (viewAny, view, create, update, delete, publish met isPublishable()).
- Controllers gebruiken de Form Requests en `$this->authorize()` op store, update, destroy, publish.

---

### Dag 11 — Artisan-commando’s (pipeline)

**Wat gedaan** : Vier CLI-commando’s om het pipeline handmatig te triggeren zonder op de scheduler te wachten.

- FetchRssFeedsCommand: `rss:fetch` (--due, --all, --limit).
- EnrichPendingItemsCommand: `content:enrich` (--limit=50, --delay=3).
- CleanupOldDataCommand: `cleanup:old` (--days=90, --prune-failed=168, --dry-run).
- GenerateDailyArticlesCommand: `articles:generate` (verwijst naar API voor echte generatie).
- docs/DOCKER.md: sectie over pipeline-commando’s.

---

### Dag 12 — Tests en SQLite-compatibiliteit

**Wat gedaan** : Unit- en featuretests toegevoegd; migraties compatibel gemaakt met SQLite voor tests in memory.

- Unit: RssParserServiceTest (parse leeg, RSS 2.0, generateDedupHash), ArticleTest (isPublishable).
- Feature: SourceApiTest (index, store, show), StatsApiTest (GET /api/stats).
- Controller: AuthorizesRequests-trait voor authorize() in API.
- Migraties: triggers alleen bij MySQL; fullText-indexen alleen bij MySQL (niet SQLite).
- 12 tests, 39 assertions; `php artisan test`.

---

## Fase 3 — Analyse en integratie oude database

---

### Dag 13 — Analyse bestaande database (ID93677_vivat)

**Wat gedaan** : Dump van de bestaande database geanalyseerd; legacy-tabellen en mapping gedocumenteerd.

- database/migrations/2024_01_01_000000_create_legacy_site_tables.php: tabellen cloaked_ip, logs, tbl_ref, tbl_usr, tbl_cont_pg (zelfde structuur als dump).
- docs/SCHEMA_BASE_EXISTANTE.md: beschrijving schema, kolommen, mapping pipeline ↔ site (articles → tbl_cont_pg, categories → tbl_ref, users → tbl_usr).
- Pipeline blijft ongewijzigd; legacy-tabellen naast het pipeline voor import bestaande content.

---

### Dag 14 — Import legacy-data en volledige audit pipeline

**Wat gedaan** : Data geïmporteerd van vivat_old naar vivat; bugs uit end-to-end tests opgelost; nieuwe endpoints en seeder toegevoegd.

- Import: 3756 artikelen (tbl_cont_pg), 71 refs (tbl_ref), 3 users (tbl_usr).
- Migratie legacy: index TBL_CONT_PG_IDX_PG_CONTENT met prefixen vanwege utf8mb4-limiet.
- Bugfixes: fetched_at en enriched_at in jobs; ArticleResource content op juiste routes; sanitizeContent guillemets; loadHtml encoding; $queue → onQueue() in jobs; created_at cast in models zonder timestamps; ArticlePolicy publish + controller isPublishable; ArticleController generate try/catch 502.
- Nieuwe endpoints: POST /api/pipeline/fetch-rss, POST /api/pipeline/enrich, GET /api/pipeline/status, CRUD clusters, CRUD category-templates.
- PipelineSeeder: 14 categorieën, 6 bronnen, 5 RSS-feeds, 14 category templates.
- Postman-tests: alle genoemde endpoints getest (OK); OpenAI-quota was overschreden (429/502).
- Audit van alle tabellen in vivat gedocumenteerd (rol, aantal rijen, nut).

---

### Dag 15 — Intelligente selectie artikelen + SEO + testhandleiding

**Wat gedaan** : Beslismodel “welk artikel genereren?” geïmplementeerd; SEO in enrichissement en generatie versterkt; Postman-documentatie afgerond.

- ArticleSelectionService: scoring (versheid 25%, kwaliteit 25%, SEO 30%, bronnendiversiteit 20%), clustering op trefwoorden (Jaccard ≥ 20%), reasoning per voorstel.
- Migratie 2024_01_01_000013: seo_keywords (JSON), seo_score, primary_topic op enriched_items; EnrichedItem-model bijgewerkt.
- OpenAI-enrichment: prompt vraagt seo_keywords, primary_topic, seo_score.
- ArticleGeneratorService: SEO-keywords en -regels in prompts; meta_title/meta_description lengtes.
- Endpoint: GET /api/pipeline/select-items?count=3&category_id=.
- docs/TESTING_POSTMAN.md: 37 endpoints, 17 testscenario’s, volledige workflow, foutscenario’s, Artisan-commando’s.

---

## Fase 4 — Sitefunctionaliteiten (11 features)

---

### Dag 16 — Stack-audit en ontbrekende packages

**Wat gedaan** : Gecontroleerd of de aangekondigde stack overeenkomt met de code; ontbrekende packages geïnstalleerd en configuratie gecorrigeerd.

- Geïnstalleerd: laravel/sanctum, spatie/laravel-permission, spatie/laravel-sluggable, stripe/stripe-php.
- Configuratie: APP_NAME=Vivat, APP_LOCALE=fr, APP_FAKER_LOCALE=fr_FR.
- Users-tabel gemigreerd naar UUID (consistentie met rest van schema).
- config/sanctum.php, config/permission.php, config/services.php (stripe) gepubliceerd/aangepast.
- Migraties voor Sanctum en Permission aangepast (uuid voor tokenable en model_id).

---

### Dag 17 — Authentificatie, rollen en openbare API

**Wat gedaan** : Sanctum-auth, twee rollen met 21 permissies, vier toegangsniveaus, openbare endpoints voor artikelen, zoeken, hubs, aanbevelingen, voorkeuren en leesvoortgang.

- Auth: register, login, logout, me, updateProfile (Bearer token).
- Rollen: admin (21 permissies), contributor (3 permissies); spatie/laravel-permission.
- Routes: public / auth / contributor / admin.
- Openbare API: artikelen gepubliceerd, zoeken, filters (categorie, datum, leestijd), hub per categorie (aanbevolen + recent), aanbevelingen; voorkeuren (session_id); leesvoortgang (cookie of account).
- ReadingHistory, UserPreference; RecommendationService (gewichten: interesses, kwaliteit, versheid, populariteit).

---

### Dag 18 — Bijdragers, nieuwsbrief en betaling

**Wat gedaan** : Bijdragersportaal (indienen artikelen), moderatie door admin, nieuwsbrief (inschrijving, bevestiging, uitschrijving), Stripe voor eenmalige publicatie en terugbetaling bij weigering.

- Submissions: contributor kan indienen (concept → ingediend), historiek, wijziging vóór validatie; admin: lijst, goedkeuren/afwijzen met notities.
- Newsletter: subscribe (min. 3 interesses), confirm via token, unsubscribe; NewsletterSubscriber-model.
- Stripe: PaymentIntent voor eenmalige publicatie; bevestiging; terugbetaling bij afwijzing; Payment-model; PaymentController, refund-endpoint.

---

## Transversale documentatie en optimalisaties

---

### Dag 19 — Stacktechniek en rapporten voor projectleider

**Wat gedaan** : Volledige stack gedocumenteerd; rapport voor projectleider uitgebreid met gedetailleerde databasebeschrijving en uitleg triggers.

- docs/STACK_TECHNIQUE.md: architectuur, pipeline in 5 stappen, 36 tabellen, veiligheid, auth, rollen, OpenAI, cache, Redis, Docker, code-inventaris.
- docs/RAPPORT_COMPLET.md: uitgebreid met alle 11 pipelinetabellen kolom per kolom (types, indexen, foreign keys); Migration 12 — Triggers in detail (wat is een trigger, bestand, SQL, down()); rapport gegenereerd als HTML voor PDF-export.
- docs/SCHEMA_COMPLET_TABLES.md: alle modellen/tabellen in detail; logica voor weergave op homepage, “à la une” en aanbevelingen.
- docs/RESUME_AVANCEMENT.md: compacte samenvatting voor projectleider; Docker, analyse oude DB, stack.
- Frequentie RSS in documentatie aangepast naar “1 keer per dag”.

---

### Dag 20 — Performance-audit en optimalisaties

**Wat gedaan** : Prestatie-audit uitgevoerd; cache en full-text zoeken geïmplementeerd; invalidatie bij publicatie en wijzigingen.

- docs/AUDIT_PERFORMANCE.md: score 7/10, doel 8,5/10; wat al goed was (eager loading, paginatie, indexen, Redis); verbeterpunten.
- ArticleController::search(): whereFullText op MySQL voor title en excerpt; fallback LIKE voor meta_description en SQLite; addcslashes voor LIKE.
- CategoryController::index(): cache 1 uur voor lijst categorieën; Cache::forget bij store/update/destroy.
- CategoryController::hub(): eager load category; cache 15 min per slug; Cache::forget bij update/destroy categorie en bij publicatie artikel (ArticleController::publish).
- Inhoud cache: modellen (niet Resources) voor correcte serialisatie.

---

### Dag 21 — Uitleg services, jobs en scheduler

**Wat gedaan** : Gedetailleerde uitleg opgesteld voor overdracht en projectleider: ContentExtractorService, de drie services, de drie jobs, en de scheduler.

- ContentExtractorService: doel, constanten, extract()-stroom, elke private methode (loadHtml, parseBaseUrl, removeTags, extractTitle, extractHeadings, extractMainContent, extractLargestTextBlock, needsDeepScrape, htmlToText, extractInternalLinks).
- Drie services: RssParserService, ContentExtractorService, ArticleGeneratorService (in-/uitgang, gebruik in pipeline).
- Drie jobs: FetchRssFeedJob, EnrichContentJob, GenerateArticleJob (queue, retries, backoff, timeout, handle(), failed(), middleware waar van toepassing).
- Scheduler: vier taken (pipeline:fetch-rss elke 30 min, pipeline:enrich elk uur, horizon:snapshot elke 5 min, queue:prune-failed dagelijks); verduidelijking dat artikelgeneratie niet door de scheduler loopt.
- Volgorde ontwikkeling bevestigd: eerst pipeline (services, jobs, Horizon, scheduler), daarna REST-API.

---

### Dag 22 — Logboek en afronding

**Wat gedaan** : Volledig logboek vanaf het begin van het project opgesteld in het Nederlands (dit document); logboek 11–17 februari bijgewerkt/geïntegreerd.

- docs/LOGBOEK_11-17_FEB_2026.md: week 11–17 februari (documentatie, audit, optimalisaties, uitleg services/jobs/scheduler).
- docs/LOGBOEK_COMPLET.md: volledig logboek van dag 1 t/m 22 (data bij benadering).

---

## Overzicht per fase

| Fase | Inhoud |
|------|--------|
| **Fase 1** | Documentatie, Laravel 12, MySQL, Docker (4 dagen). |
| **Fase 2** | Migraties, phpMyAdmin, models, services/jobs/Horizon/scheduler, API, Form Requests, Policies, Artisan-commando’s, tests (8 dagen). |
| **Fase 3** | Analyse legacy-DB, import, bugfixes, selectie + SEO, TESTING_POSTMAN (3 dagen). |
| **Fase 4** | Stack-packages, Sanctum, rollen, openbare API, bijdragers, nieuwsbrief, Stripe (3 dagen). |
| **Doc + perf** | Stack-doc, rapporten, schema, audit, cache/full-text, uitleg services/jobs/scheduler, logboeken (4 dagen). |

---

## Cijfers (stand einde project)

- **Routes API** : 94  
- **Tabellen** : 36  
- **Models** : 17  
- **Controllers API** : 16  
- **Services** : 5  
- **Jobs** : 3  
- **Rollen** : 2 (admin, contributor)  
- **Permissies** : 21  
- **Geïmporteerde legacy-records** : 3830  
- **Documenten** : STACK_TECHNIQUE, TESTING_POSTMAN, SCHEMA_BASE_EXISTANTE, SCHEMA_COMPLET_TABLES, RAPPORT_COMPLET, RESUME_AVANCEMENT, AUDIT_PERFORMANCE, DOCKER, JOURNAL_AVANCEMENT, LOGBOEK_11-17_FEB_2026, LOGBOEK_COMPLET  

---

*Laatste wijziging : februari 2026*
