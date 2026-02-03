# Contexte complet du projet — Vivat

> **À lire systématiquement** : ce fichier est la source de vérité du projet.  
> Toute décision technique ou fonctionnelle doit s’aligner avec ce qui est décrit ici.

> **Projet Laravel créé.** Pour toute nouvelle fonctionnalité ou modification, s'aligner sur les **5 fichiers de référence** (voir règle Cursor) et utiliser **Laravel Boost**. Si un élément manque, le signaler avant d'inventer.

---

## 1. Vision & objectifs

- **Produit** : **Vivat** — un site avec des articles. Le projet est coupé en deux parties :
  1. **Génération automatisée des articles** (scraping, IA, enrichissement).
  2. **Le site lui-même** qui affichera ces articles.
- **Rôle** : tu es le **développeur backend**. Le frontend sera fait par quelqu’un d’autre.
- **Backend** : exposition d’**endpoints API** utilisés par le frontend.
- **Utilisateurs cibles** : *(à compléter)*
- **Problème résolu** : *(à compléter)*
- **Objectifs mesurables** : *(à compléter)*

---

## 2. Fonctionnalités du site (périmètre complet)

Le site Vivat couvre les 12 blocs ci‑dessous. Le **12** (création automatisée de contenu) est le pipeline déjà détaillé en section 9 ; les autres seront implémentés côté backend (API + logique) pour le front.

---

### 1) Gestion des accès et des rôles (Authentification et permissions)

**Rôles utilisateurs (4 niveaux) :**
- **Visiteur** : utilisateur non connecté.
- **Contributeur** : peut soumettre des articles.
- **Admin / Modérateur** : gestion et modération.

**Accès public (libre) :**
- Navigation sans compte.
- Lecture illimitée de tous les articles.

**Gestion des cookies (utilisateurs non connectés) :**
- Soumis au consentement.
- **Personnalisation** : suivi des centres d’intérêt.
- **Confort de lecture** : reprise à la dernière position consultée.

---

### 2) Personnalisation et centres d’intérêt

- **Collecte des préférences** : centres d’intérêt à la première interaction ou via cookies (visites suivantes).
- **Priorisation du contenu** : contenu ajusté/priorisé selon les centres d’intérêt. → vue générale par défaut si pas de cookies.
- **Accessibilité** : l’utilisateur peut consulter toutes les catégories, même non choisies.
- **Publicité ciblée (optionnel)** : si techniquement réalisable, publicités alignées aux centres d’intérêt.

---

### 3) Navigation et structure éditoriale

- **Organisation** : catégories et sous‑catégories.
- **Pages Hub (essentiel)** : chaque catégorie principale a une Page Hub avec :
  - brève présentation de la catégorie ;
  - sélection d’articles « À la une » ;
  - liste des articles récents.

---

### 4) UX de consultation d’articles

- Accès à tous les articles publiés.
- **Infos affichées** : temps de lecture estimé (ex. « 5 min »), date de publication, date de dernière mise à jour (« Mis à jour le… »).
- **Reprendre la lecture** : pour non connectés, progression sauvegardée via cookie ou localStorage.

---

### 5) *(réservé / numérotation)*

---

### 6) Suggestions de contenu personnalisées

- **Algorithme de recommandation** basé sur : centres d’intérêt, historique de lecture, popularité des articles.
- **Section dédiée** : « Recommandé pour vous ».

---

### 7) Recherche et filtrage

- **Recherche** : par mot‑clé sur tout le site.
- **Filtres** : sous‑catégorie, date ; (optionnel) durée de lecture.

---

### 8) Partage et engagement

- **Partage** : intégration partage réseaux sociaux (Facebook, Twitter, LinkedIn, etc.).

---

### 9) Gestion de la newsletter

- Abonnement / désabonnement simple.
- **Centres d’intérêt** : l’utilisateur choisit au minimum 3 thèmes.
- **Réception personnalisée** : newsletter adaptée aux thèmes choisis.

---

### 10) Contribution d’articles par les utilisateurs

**Contributeur :**
- Espace de rédaction (interface dédiée).
- Soumission pour validation.
- Historique de ses articles (soumis, publiés, refusés) sur son profil.
- Suivi du statut : en attente / publié / refusé.

**Modérateur / Admin :**
- Validation : accepter ou refuser l’article soumis (éventuellement via IA).

---

### 11) Publication ponctuelle et paiement / remboursement

- **Publication one‑time** : paiement pour publier un seul article ; l’article passe par un processus de validation.
- **En cas de refus** : choix entre remboursement automatique ou crédit.
- **Tableau de bord** : statuts clairs — Payé, En validation, Publié, Refusé, Remboursé.

---

### 12) Système automatisé de création de contenu

→ Correspond au **pipeline** déjà décrit en **section 9** (RSS, enrichissement, clustering, génération IA, jobs, Horizon, etc.). C’est la partie sur laquelle on travaille en priorité avec le backend.

---

## 3. Architecture & stack technique (Backend)

### Stack principale

| Élément | Choix |
|--------|--------|
| **Framework** | Laravel 12 |
| **PHP** | 8.3+ |
| **Architecture** | MVC enrichie (Controllers + Services + Jobs) |

### Principes d’architecture

- **Controllers fins** — pas de logique métier dans les controllers.
- **Logique métier** dans des **Services**.
- **Tâches lourdes** via **Jobs / Queues**.
- **Validation** via **Form Requests**.
- **Sécurité** via **Policies** & **Middleware**.
- **Langues** : **FR & NL** — système de localisation Laravel, support multi-langues (FR / NL), traductions côté backend prêtes pour le frontend.

### Base de données

- **MySQL 8**
- Schéma orienté **contenu média**.
- Indexation sur : **articles**, **catégories**, **dates**, **statuts**.

### Scraping & IA

- **Scraping** : Laravel Jobs/Queues + scheduled tasks.
- **Laravel Horizon** : gestion des jobs (fetch RSS, enrichment, génération).
- **Laravel Scheduler** : tâches automatiques (cron).
- **OpenAI** : intégration IA.
- **Intelligence Artificielle** — OpenAI **GPT-4.5** pour :
  - synthèse multi-sources ;
  - génération d’articles originaux ;
  - enrichissement SEO.
- Appels IA effectués via **Jobs asynchrones**.

### Paiement

- **Stripe**
- **Payment Intents**
- Gestion : paiement ponctuel, validation de publication, remboursements automatiques.
- Historique des transactions conservé en base.

### Cache & performance

- **Redis** pour :
  - cache articles ;
  - cache homepage & hub pages ;
  - files d’attente (scraping / IA).
- Compatible avec Laravel Queues & Horizon.

### Packages Laravel utilisés

| Package | Usage |
|---------|--------|
| **spatie/laravel-sluggable** | Slugs SEO propres |
| **spatie/laravel-sitemap** | Sitemap dynamique pour SEO |
| **Laravel Sanctum** | API auth |
| **Laravel Telescope** | Debug en dev |
| **Sentry / Bugsnag** | Monitoring erreurs en prod |

### Laravel Boost (développement assisté par IA)

**Laravel Boost** fournit des guidelines et agent skills pour que les agents IA (Cursor, Claude Code, etc.) écrivent du code Laravel de qualité et conforme aux bonnes pratiques. Il expose aussi une **API de documentation** de l’écosystème Laravel (MCP + base de connaissances, recherche sémantique) pour des résultats précis et à jour.

- **Installation** (à faire après création du projet Laravel) :
  ```bash
  composer require laravel/boost --dev
  php artisan boost:install
  ```
- `boost:install` génère les fichiers de guidelines et skills pour les agents sélectionnés lors de l’installation.
- À installer en **début de projet** pour que l’IA s’appuie sur les bonnes pratiques Laravel pendant tout le développement.

### Infrastructure & environnement

- **Conteneurisation** : **Docker** (PHP, MySQL, Redis).
- Environnement identique en local / staging / prod.
- **Serveur cible** : VPS (DigitalOcean / Hetzner), **Nginx** (reverse proxy + SSL).

### Sécurité

- HTTPS.
- Validation serveur.
- Protection API.
- **Rate limiting**.

---

## 4. Design & UX

*(Côté backend : pas de maquettes directes ; les endpoints sont conçus pour être consommés par le front. À compléter si contraintes d’API ou de formats.)*

- 
- 

---

## 5. Règles métier & contraintes

- 
- 

---

## 6. Glossaire & termes clés

| Terme | Définition |
|-------|------------|
| *(à compléter)* | |

---

## 7. Exemples de code et implémentations de référence

Les **exemples de code** à suivre pour le pipeline (fetch RSS, enrichissement, génération, Horizon, Controller, Request, Policy) sont décrits dans :

**`docs/EXEMPLES_CODE_REFERENCE.md`**

Ce fichier contient les références pour :

- **FetchRssFeedJob** + **RssParserService** : récupération et parse RSS/Atom, déduplication par `dedup_hash`, queue `rss`.
- **EnrichContentJob** + **ContentExtractorService** : extraction contenu web (sans Firecrawl), appel OpenAI pour lead/headings/key_points/quality_score, rate limiter `openai` (50/min), queue `enrichment`, dispatch avec délai.
- **ArticleGeneratorService** : génération d’article depuis plusieurs sources, template catégorie, prompts, sanitize, reading_time, quality_score ; utilisé par **GenerateArticleJob** (queue `generation`).
- **Horizon** : config complète (supervisors rss / enrichment / generation / default), waits, environnements local/production, middleware `AuthorizeHorizon`, Scheduler (fetch 30 min, enrich 1h, snapshot, prune-failed), Supervisor et Docker.
- **Génération côté web** : **GenerateArticleRequest**, **ArticleGenerationController** (index, generate synchrone, generateAsync, preview, edit, update, publish), **ArticlePolicy**, routes `web.php` sous `auth`.

**Note** : Le Job d’enrichissement utilise **ContentExtractorService** pour l’extraction HTML ; la liste en section 9 mentionne **ContentEnrichmentService** — à aligner (soit un seul service extraction+IA, soit ContentEnrichmentService qui utilise ContentExtractorService).

---

## 8. Notes importantes

- 
- 

---

## 9. Concrétisation de l’architecture — Pipeline (scraping & génération)

> **Important** : cette architecture couvre la **génération automatisée des articles** (RSS → enrichissement → clustering → génération IA). Ce n’est **pas encore** l’ensemble des fonctionnalités du site (paiement, utilisateurs, etc. viendront en plus).

### Liste complète des fichiers à créer

#### Migrations (12 fichiers)

Détail complet (code MySQL 8, UUID, index, fullText) → **`docs/MIGRATIONS_REFERENCE.md`**.

`database/migrations/`
- `2024_01_01_000001_create_sources_table.php`
- `2024_01_01_000002_create_categories_table.php`
- `2024_01_01_000003_create_rss_feeds_table.php`
- `2024_01_01_000004_create_rss_items_table.php`
- `2024_01_01_000005_create_enriched_items_table.php`
- `2024_01_01_000006_create_clusters_table.php`
- `2024_01_01_000007_create_cluster_items_table.php`
- `2024_01_01_000008_create_articles_table.php`
- `2024_01_01_000009_create_article_sources_table.php`
- `2024_01_01_000010_create_category_templates_table.php`
- `2024_01_01_000011_create_pipeline_jobs_table.php`
- `2024_01_01_000012_create_updated_at_triggers.php`

| # | Table | Rôle |
|---|--------|------|
| 1 | sources | Base des médias |
| 2 | categories | Catégorisation |
| 3 | rss_feeds | Flux RSS |
| 4 | rss_items | Items collectés |
| 5 | enriched_items | Données enrichies |
| 6 | clusters | Groupements thématiques |
| 7 | cluster_items | Pivot cluster ↔ items |
| 8 | articles | Articles générés |
| 9 | article_sources | Traçabilité sources |
| 10 | category_templates | Config génération / catégorie |
| 11 | pipeline_jobs | Suivi des jobs |
| 12 | triggers | Auto-update timestamps (sources, articles) |

#### Models (11 fichiers)

Détail complet (HasUuids, fillable, casts, relations, scopes, helpers) → **`docs/MODELS_REFERENCE.md`**.

`app/Models/`
- `Source.php`
- `Category.php`
- `RssFeed.php`
- `RssItem.php`
- `EnrichedItem.php`
- `Cluster.php`
- `ClusterItem.php`
- `CategoryTemplate.php`
- `Article.php`
- `ArticleSource.php`
- `PipelineJob.php`

#### Factories (8 fichiers)
`database/factories/`
- `SourceFactory.php`, `CategoryFactory.php`, `RssFeedFactory.php`, `RssItemFactory.php`
- `EnrichedItemFactory.php`, `ClusterFactory.php`, `ArticleFactory.php`, `PipelineJobFactory.php`

#### Seeders (10 fichiers)
`database/seeders/`
- `DatabaseSeeder.php`
- `SourceSeeder.php`, `CategorySeeder.php`, `RssFeedSeeder.php`, `RssItemSeeder.php`
- `EnrichedItemSeeder.php`, `ClusterSeeder.php`, `CategoryTemplateSeeder.php`, `ArticleSeeder.php`
- `ProductionDataSeeder.php` *(import depuis Supabase)*

#### Services (6 fichiers)
`app/Services/`
- `RssParserService.php` — parse les flux RSS
- `ContentExtractorService.php` — extraction contenu web depuis URL (utilisé par EnrichContentJob ; réf. `docs/EXEMPLES_CODE_REFERENCE.md`)
- `ArticleGeneratorService.php` — génération via IA
- `ClusteringService.php` — regroupement thématique
- `QualityScoreService.php` — calcul des scores
- `DeduplicationService.php` — détection doublons

#### Jobs (8 fichiers)
`app/Jobs/`
- `FetchRssFeedJob.php` — récupère un flux RSS
- `FetchAllRssFeedsJob.php` — orchestre tous les flux
- `EnrichContentJob.php` — enrichit un item
- `BatchEnrichContentJob.php` — enrichissement par lot
- `GenerateArticleJob.php` — génère un article
- `DailyArticleGeneratorJob.php` — orchestrateur quotidien
- `ClusterItemsJob.php` — crée les clusters
- `CleanupOldItemsJob.php` — nettoyage périodique

#### Controllers API (7 fichiers)
`app/Http/Controllers/Api/`
- `SourceController.php`, `CategoryController.php`, `RssFeedController.php`, `RssItemController.php`
- `ArticleController.php`, `PipelineController.php`, `StatsController.php`

#### API Resources (8 fichiers)
`app/Http/Resources/`
- `SourceResource.php`, `CategoryResource.php`, `RssFeedResource.php`, `RssItemResource.php`
- `EnrichedItemResource.php`, `ArticleResource.php`, `ArticleCollection.php`, `PipelineStatsResource.php`

#### Form Requests (6 fichiers)
`app/Http/Requests/`
- `StoreSourceRequest.php`, `UpdateSourceRequest.php`, `StoreRssFeedRequest.php`
- `StoreArticleRequest.php`, `UpdateArticleRequest.php`, `GenerateArticleRequest.php`

#### Policies (5 fichiers)
`app/Policies/`
- `SourcePolicy.php`, `RssFeedPolicy.php`, `RssItemPolicy.php`, `ArticlePolicy.php`, `PipelineJobPolicy.php`

#### Filament (admin) — 12 fichiers
`app/Filament/Resources/` (8) : `SourceResource.php`, `CategoryResource.php`, `RssFeedResource.php`, `RssItemResource.php`, `EnrichedItemResource.php`, `ArticleResource.php`, `ClusterResource.php`, `PipelineJobResource.php`  
`app/Filament/Widgets/` (4) : `PipelineStatsWidget.php`, `ArticleStatusChart.php`, `RecentArticlesWidget.php`, `FeedHealthWidget.php`

#### Console Commands (5 fichiers)
`app/Console/Commands/`
- `FetchRssFeeds.php` — `php artisan rss:fetch`
- `EnrichPendingItems.php` — `php artisan content:enrich`
- `GenerateDailyArticles.php` — `php artisan articles:generate`
- `CleanupOldData.php` — `php artisan cleanup:old`
- `ImportSupabaseData.php` — `php artisan import:supabase`

#### Schedule
`app/Console/Kernel.php` — planification des tâches cron.

#### Events & Listeners (6 fichiers)
`app/Events/` : `RssItemFetched.php`, `ContentEnriched.php`, `ArticleGenerated.php`  
`app/Listeners/` : `LogRssItemFetched.php`, `TriggerEnrichmentOnFetch.php`, `NotifyArticleGenerated.php`

#### Tests (12+ fichiers)
`tests/Unit/Services/` : `RssParserServiceTest.php`, `ContentEnrichmentServiceTest.php`, `ArticleGeneratorServiceTest.php`  
`tests/Unit/Models/` : `RssItemTest.php`, `ArticleTest.php`  
`tests/Feature/Api/` : `SourceApiTest.php`, `RssFeedApiTest.php`, `ArticleApiTest.php`  
`tests/Feature/Jobs/` : `FetchRssFeedJobTest.php`, `GenerateArticleJobTest.php`

#### Config (3 fichiers)
`config/`
- `content-engine.php` — config du moteur
- `ai.php` — config API IA
- `horizon.php` — config queues

#### Routes
`routes/api.php` — routes API REST  
`routes/console.php` — routes Artisan

---

### Horizon — Supervisors

Dans `config/horizon.php` : queues dédiées **rss**, **enrichment**, **generation** (+ default/notifications). Détail complet (waits, environnements local/production, middleware, Scheduler, Docker) → **`docs/EXEMPLES_CODE_REFERENCE.md`** section 5.

```php
'supervisor-rss' => [
    'connection' => 'redis',
    'queue' => ['rss'],
    'balance' => 'auto',
    'maxProcesses' => 3,  // 5 en production
],
'supervisor-enrichment' => [
    'queue' => ['enrichment'],
    'maxProcesses' => 2,  // Rate limit OpenAI
],
'supervisor-generation' => [
    'queue' => ['generation'],
    'maxProcesses' => 1,  // Rate limit strict
],
```

---

### Résumé quantitatif (pipeline uniquement)

| Catégorie            | Nombre |
|----------------------|--------|
| Migrations           | 12     |
| Models               | 11     |
| Factories            | 8      |
| Seeders              | 10     |
| Services             | 6      |
| Jobs                 | 8      |
| Controllers API      | 7      |
| API Resources        | 8      |
| Form Requests        | 6      |
| Policies             | 5      |
| Filament Resources   | 8      |
| Filament Widgets     | 4      |
| Commands             | 5      |
| Events/Listeners     | 6      |
| Tests                | 12+    |
| Config               | 3      |
| **TOTAL**            | **~120 fichiers** |

---

## Ce qu’il me faut encore (avant de créer le projet)

- **Migrations** : documentées dans `docs/MIGRATIONS_REFERENCE.md` (12 migrations, MySQL 8, UUID).
- **Models** : documentés dans `docs/MODELS_REFERENCE.md` (11 models pipeline).
- **Schéma des autres fonctionnalités** (users, categories éditorial, sub_categories, articles site, contributed_articles, payments, user_categories, refunds) : **`docs/SCHEMA_AUTRES_FONCTIONNALITES.md`** — **peut encore changer** ; INT AUTO_INCREMENT pour l’instant ; à aligner plus tard avec le pipeline (une ou deux tables articles, INT vs UUID).
- **Filament** : confirmé comme outil d’admin backend (pas seulement API pour le front) ?
- Exemples d’endpoints ou de specs API pour le front.
- Tout autre détail ou exemple que tu juges important.

---

*Dernière mise à jour : à compléter*
