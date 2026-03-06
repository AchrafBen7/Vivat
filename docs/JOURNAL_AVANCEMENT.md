# Journal d'avancement — Vivat

Ce fichier sert de **résumé étape par étape** de ce qui a été fait sur le projet. À mettre à jour à chaque avancée importante pour avoir une vue d’ensemble plus tard.

---

## Comment l’utiliser

- **Date** : indiquer la date (ou la session) de travail.
- **Résumé** : en 2–5 lignes, ce qui a été fait.
- **Fichiers / commandes** : liste courte des fichiers créés/modifiés ou commandes exécutées.

---

## Étape 1 — Contexte et documentation (avant Laravel)

- **Résumé** : Mise en place du contexte projet et des références pour que l’IA et l’équipe s’alignent sur la vision, l’architecture et les exemples de code.
- **Fichiers créés** :
  - `CONTEXTE_PROJET.md` — Vision, 12 fonctionnalités du site, stack (Laravel 12, MySQL, Redis, Horizon, OpenAI, Stripe, FR/NL), Laravel Boost, liste des fichiers à créer (pipeline).
  - `docs/EXEMPLES_CODE_REFERENCE.md` — Exemples : FetchRssFeedJob, RssParserService, EnrichContentJob, ContentExtractorService, ArticleGeneratorService, Horizon, Controller/Request/Policy, rate limiting.
  - `docs/MIGRATIONS_REFERENCE.md` — Les 12 migrations du pipeline (sources → categories → rss_feeds → rss_items → enriched_items → clusters → cluster_items → articles → article_sources → category_templates → pipeline_jobs → triggers), MySQL 8, UUID.
  - `docs/MODELS_REFERENCE.md` — Les 11 models Eloquent du pipeline (HasUuids, relations, scopes, helpers), avec `scopeDueForFetch` en MySQL.
  - `docs/SCHEMA_AUTRES_FONCTIONNALITES.md` — Schéma (peut encore changer) : users, categories, sub_categories, articles site, contributed_articles, payments, user_categories, refunds.
- **Règle Cursor** : `.cursor/rules/contexte-projet.mdc` — Toujours lire `CONTEXTE_PROJET.md` (puis étendu aux 5 fichiers + Laravel Boost).

---

## Étape 2 — Création du projet Laravel et Laravel Boost

- **Résumé** : Projet Laravel 12 créé à la racine du repo ; Laravel Boost installé (guidelines, skills, MCP) pour Cursor ; règles Cursor mises à jour pour imposer les 5 fichiers de référence et l’usage de Boost.
- **Actions** :
  - `composer create-project laravel/laravel vivat-temp` puis déplacement du contenu dans `Vivat/`.
  - `composer require laravel/boost --dev` puis `php artisan boost:install --guidelines --skills --mcp --no-interaction`.
- **Fichiers ajoutés / modifiés** :
  - Structure Laravel : `app/`, `config/`, `database/`, `routes/`, `public/`, etc.
  - Boost : `.cursor/rules/laravel-boost.mdc`, `.cursor/mcp.json`, `AGENTS.md`, `boost.json`.
  - Règle : `.cursor/rules/contexte-projet.mdc` — 5 fichiers obligatoires + utiliser Laravel Boost.
  - `CONTEXTE_PROJET.md` — Phrase « Ne pas commencer… » remplacée par « Projet Laravel créé… 5 fichiers de référence + Laravel Boost ».

---

## Étape 3 — Base de données locale MySQL

- **Résumé** : Configuration de la base de données pour le développement local avec MySQL (au lieu de SQLite).
- **Fichiers modifiés** :
  - `.env` et `.env.example` : `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=vivat`, `DB_USERNAME=root`, `DB_PASSWORD=`.
- **À faire côté dev** : Créer la base `vivat` (ex. `mysql -u root -e "CREATE DATABASE IF NOT EXISTS vivat ..."`) puis `php artisan migrate:status` pour vérifier.

---

## Étape 4 — Docker (environnement local)

- **Résumé** : Mise en place de l’environnement Docker pour le dev local : PHP 8.3, MySQL 8, Redis. L’app Laravel tourne dans un container avec le code monté ; MySQL et Redis ont des volumes persistants.
- **Fichiers créés** :
  - `Dockerfile` — Image PHP 8.3 Alpine, extensions (pdo_mysql, redis, gd, zip, bcmath, intl, etc.), Composer, entrypoint pour `composer install` si besoin.
  - `docker-compose.yml` — Services `app` (port 8000), `mysql` (8.0, port 3306, user/password vivat), `redis` (7-alpine, port 6379) ; healthcheck MySQL ; volumes pour données et code.
  - `docker/entrypoint.sh` — Script d’entrée : `composer install` si `vendor` absent, permissions storage/bootstrap/cache.
  - `.dockerignore` — Réduction du contexte de build (git, vendor, .env, logs, etc.).
  - `docs/DOCKER.md` — Instructions : démarrage, premier lancement (key:generate, migrate), commandes utiles, variables d’environnement.
- **Utilisation** : `docker compose up -d --build` puis `docker compose exec app php artisan migrate`.

---

## Étape 5 — Migrations du pipeline (12 tables)

- **Résumé** : Création et exécution des 12 migrations du pipeline (sources → categories → rss_feeds → rss_items → enriched_items → clusters → cluster_items → articles → article_sources → category_templates → pipeline_jobs → triggers). MySQL configuré pour autoriser les triggers (`log_bin_trust_function_creators=1`).
- **Fichiers créés** :
  - `database/migrations/2024_01_01_000001_create_sources_table.php` à `2024_01_01_000012_create_updated_at_triggers.php` (12 fichiers).
- **Fichiers modifiés** :
  - `docker-compose.yml` — Ajout de `command: ["--log_bin_trust_function_creators=1"]` sur le service MySQL pour que la migration des triggers passe.
- **Commande** : `docker compose run --rm app php artisan migrate --force` (migrations exécutées dans le container).

---

## Étape 6 — phpMyAdmin

- **Résumé** : Ajout du service phpMyAdmin dans Docker pour visualiser les tables MySQL dans le navigateur.
- **Fichiers modifiés** :
  - `docker-compose.yml` — Service `phpmyadmin` (image phpmyadmin:latest, port 8080, PMA_HOST=mysql).
  - `docs/DOCKER.md` — URL http://localhost:8080, ports 8080, identifiants (vivat / vivat_secret ou root / root_secret).

---

## Étape 7 — Models Eloquent du pipeline (11 models)

- **Résumé** : Création des 11 models correspondant aux tables du pipeline : HasUuids, fillable, casts, relations, scopes et helpers selon `docs/MODELS_REFERENCE.md`.
- **Fichiers créés** :
  - `app/Models/Source.php`, `Category.php`, `RssFeed.php`, `RssItem.php`, `EnrichedItem.php`, `Cluster.php`, `ClusterItem.php`, `CategoryTemplate.php`, `Article.php`, `ArticleSource.php`, `PipelineJob.php`.
- **Détail** : Relations (hasMany, belongsTo, belongsToMany, hasOne), scopes (active, dueForFetch, new, enriched, published, etc.), méthodes (isEnriched, publish, start/complete/fail sur PipelineJob, etc.).

---

## Étape 8 — Services, Jobs, Horizon et Scheduler

- **Résumé** : Implémentation du pipeline côté code : 3 services (RSS, extraction, génération), 3 jobs (fetch RSS, enrichissement, génération), installation et configuration de Horizon, rate limiter OpenAI, planification des tâches (fetch 30 min, enrich 1h, snapshot, prune-failed).
- **Fichiers créés** :
  - **Services** : `app/Services/RssParserService.php` (parse RSS/Atom, generateDedupHash), `app/Services/ContentExtractorService.php` (extraction HTML via DOMDocument), `app/Services/ArticleGeneratorService.php` (génération article via OpenAI, Article + ArticleSource, sanitize, reading_time, quality_score).
  - **Jobs** : `app/Jobs/FetchRssFeedJob.php` (queue `rss`), `app/Jobs/EnrichContentJob.php` (queue `enrichment`, RateLimited `openai`), `app/Jobs/GenerateArticleJob.php` (queue `generation`).
  - **Config** : `config/horizon.php` (publiée puis adaptée : supervisors rss, enrichment, generation, default ; waits ; environnements local/production).
- **Fichiers modifiés** :
  - `app/Providers/AppServiceProvider.php` — RateLimiter::for('openai', 50/min par item).
  - `config/services.php` — Clé `openai` (api_key, model, max_tokens).
  - `bootstrap/app.php` — `withSchedule()` : fetch RSS toutes les 30 min, enrichissement toutes les heures (50 items, délai 3s), horizon:snapshot everyFiveMinutes, queue:prune-failed daily.
  - `.env.example` — OPENAI_API_KEY, OPENAI_MODEL, OPENAI_MAX_TOKENS ; commentaire QUEUE_CONNECTION=redis pour Horizon.
- **Commande** : `composer require laravel/horizon` puis `php artisan horizon:install`.

---

## Étape 9 — API REST (Postman)

- **Résumé** : Mise en place de l’API REST pour le pipeline : routes sous `/api`, contrôleurs, resources JSON. Permet de tester tous les endpoints dans Postman (sans auth pour l’instant).
- **Fichiers créés** :
  - `routes/api.php` — Routes : sources (apiResource), categories (index, show), rss-feeds (apiResource), rss-items (index, show), articles (apiResource + generate, generate-async, publish), stats.
  - `bootstrap/app.php` — Enregistrement de `api: __DIR__.'/../routes/api.php'` dans `withRouting`.
  - **Resources** : `app/Http/Resources/SourceResource.php`, `CategoryResource.php`, `RssFeedResource.php`, `RssItemResource.php`, `EnrichedItemResource.php`, `ArticleResource.php`, `ArticleSourceResource.php`.
  - **Contrôleurs API** : `app/Http/Controllers/Api/SourceController.php`, `CategoryController.php`, `RssFeedController.php`, `RssItemController.php`, `ArticleController.php`, `StatsController.php` (index, store, show, update, destroy selon ressource ; Article : generate, generateAsync, publish).
  - `docs/API_POSTMAN.md` — Liste des endpoints pour Postman (base URL, méthodes, paramètres).
- **Détail** : Validation dans les contrôleurs (request()->validate). Pagination sur rss-items et articles (per_page, max 50). ArticleController::generate vérifie que les items sont enrichis puis appelle ArticleGeneratorService. Stats : sources, rss_feeds_active, rss_items_by_status, articles_by_status, articles_published.

---

## Prochaines étapes (à remplir au fur et à mesure)

- [x] Ajouter les 12 migrations du pipeline et lancer `php artisan migrate`.
- [x] Créer les 11 models du pipeline.
- [x] Services, Jobs, Horizon, rate limiter, Scheduler.
- [x] Contrôleurs API, Resources, routes API (testables dans Postman).
- [x] Form Requests dédiés, Policies (Source, RssFeed, Article).
- [x] Commandes Artisan (rss:fetch, content:enrich, cleanup:old, articles:generate).
- [x] Tests Unit/Feature, compatibilité SQLite.
- [ ] **Pour que tout marche** : Seeders (données de démarrage), vérif .env + Horizon + scheduler (voir ci‑dessous).
- [ ] (Optionnel) Auth Sanctum, Filament admin, Events/Listeners.

---

## Prochaines étapes pour que tout fonctionne

Ce qui reste à faire pour que le **pipeline et l’API** tournent de bout en bout et que tout ce qui est prévu soit en place.

### 1. Données de démarrage (obligatoire pour tester le pipeline)

Sans au moins **1 catégorie, 1 source et 1 flux RSS**, `rss:fetch` n’a rien à traiter.

- [ ] **Seeders** : créer `CategorySeeder`, `SourceSeeder`, `RssFeedSeeder` (ex. 1 catégorie « Actualités », 1 source, 1 flux RSS réel), les appeler depuis `DatabaseSeeder`, puis `php artisan db:seed`.
- **Alternative** : créer les enregistrements à la main via l’API (POST /api/sources, POST /api/categories si endpoint, POST /api/rss-feeds) ou phpMyAdmin.

### 2. Environnement opérationnel

- [ ] **.env** (ou variables Docker) : `QUEUE_CONNECTION=redis`, `REDIS_HOST=redis` (en Docker), `OPENAI_API_KEY` renseignée pour l’enrichissement et la génération d’articles.
- [ ] **Horizon** : lancer `php artisan horizon` (ou service Docker dédié) pour que les jobs (rss, enrichment, generation) soient exécutés.
- [ ] **Scheduler** : en local `php artisan schedule:work`, ou en prod une entrée cron `* * * * * php artisan schedule:run` pour le fetch toutes les 30 min et l’enrichissement toutes les heures.

### 3. Vérification de bout en bout

- [ ] Lancer `php artisan rss:fetch --all` → des `rss_items` en statut `new` apparaissent (vérifier en BDD ou GET /api/rss-items?status=new).
- [ ] Lancer `php artisan content:enrich` (avec Horizon qui tourne) → des items passent en `enriched` (et des `enriched_items` sont créés).
- [ ] Appeler POST /api/articles/generate avec des `item_ids` enrichis → un article est créé (ou POST /api/articles/generate-async puis vérifier en BDD / Horizon).

### 4. Compléments utiles (prévus dans le contexte projet)

| Élément | Statut | Priorité |
|--------|--------|----------|
| **Seeders** (catégories, sources, flux) | À faire | Haute (pour démo / tests) |
| **Filament** (admin : sources, flux, articles, jobs) | Non fait | Moyenne |
| **Auth Sanctum** (protéger l’API / admin) | Non fait | Selon besoin |
| **Clustering** (ClusteringService, ClusterItemsJob) | Non fait | Optionnel (génération possible sans) |
| **PipelineController** (état des jobs pipeline) | Non fait | Optionnel (Stats suffit pour l’instant) |
| **RssItemPolicy / PipelineJobPolicy** | Non fait | Optionnel |
| **Factories** (pour tests / seed) | Non fait | Utile avec seeders |

### 5. Ordre recommandé

1. **Seeders** (catégories + sources + 1–2 flux RSS) → `db:seed`.
2. Vérifier **.env** (redis, OpenAI), lancer **Horizon** et **schedule:work** (ou cron).
3. Tester la chaîne : **rss:fetch** → **content:enrich** (attendre les jobs) → **POST /api/articles/generate**.
4. Ensuite, selon besoin : **Filament** (admin), **Sanctum** (auth), puis reste (clustering, policies, etc.).

---

## Étape 10 — Form Requests et Policies

- **Résumé** : Validation déléguée aux Form Requests ; autorisation via Policies (pour l’instant tout autorisé, prêt pour une future auth).
- **Form Requests créés** : `StoreSourceRequest`, `UpdateSourceRequest`, `StoreRssFeedRequest`, `UpdateRssFeedRequest`, `StoreArticleRequest`, `UpdateArticleRequest`, `GenerateArticleRequest` (item_ids 1–10, custom_prompt strip_tags dans prepareForValidation).
- **Policies créées** : `SourcePolicy`, `RssFeedPolicy`, `ArticlePolicy` (viewAny, view, create, update, delete ; ArticlePolicy + publish qui s’appuie sur isPublishable()).
- **Contrôleurs** : SourceController, RssFeedController, ArticleController utilisent les Form Requests et appellent `$this->authorize()` sur store, update, destroy, publish.

---

## Étape 11 — Commandes Artisan (pipeline)

- **Résumé** : Commandes pour lancer manuellement le fetch RSS, l’enrichissement et le nettoyage (utile en dev / tests sans attendre le scheduler).
- **Fichiers créés** :
  - `app/Console/Commands/FetchRssFeedsCommand.php` — `rss:fetch` (--due par défaut, --all, --limit) ; dispatch FetchRssFeedJob pour chaque flux.
  - `app/Console/Commands/EnrichPendingItemsCommand.php` — `content:enrich` (--limit=50, --delay=3) ; dispatch EnrichContentJob pour les items "new".
  - `app/Console/Commands/CleanupOldDataCommand.php` — `cleanup:old` (--days=90, --prune-failed=168, --dry-run) ; queue:prune-failed + suppression optionnelle des vieux rss_items (used/failed/ignored).
  - `app/Console/Commands/GenerateDailyArticlesCommand.php` — `articles:generate` ; affiche la liste des items enrichis et rappelle d’utiliser l’API pour générer.
- **Documentation** : `docs/DOCKER.md` — section « Commandes pipeline (Artisan) » ajoutée.

---

## Étape 12 — Tests et compatibilité SQLite

- **Résumé** : Ajout de tests Unit et Feature pour le pipeline et l’API ; migrations rendues compatibles SQLite (tests en mémoire) ; correction du Controller de base pour l’autorisation.
- **Tests créés** :
  - **Unit** : `tests/Unit/RssParserServiceTest.php` (parse RSS vide, RSS 2.0, generateDedupHash) ; `tests/Unit/ArticleTest.php` (isPublishable selon quality_score et status).
  - **Feature API** : `tests/Feature/Api/SourceApiTest.php` (index 200, store 201 + validation, show 200) ; `tests/Feature/Api/StatsApiTest.php` (GET /api/stats structure).
- **Correctifs** :
  - `app/Http/Controllers/Controller.php` — ajout du trait `AuthorizesRequests` pour que `$this->authorize()` fonctionne dans les contrôleurs API.
  - `database/migrations/2024_01_01_000012_create_updated_at_triggers.php` — exécution des triggers uniquement si `DB::getDriverName() === 'mysql'` (évite l’erreur en SQLite).
  - `database/migrations/2024_01_01_000004_create_rss_items_table.php` et `2024_01_01_000008_create_articles_table.php` — index fullText créés uniquement pour MySQL (SQLite non supporté par le schema builder).
- **Lancement** : `vendor/bin/phpunit` ou `php artisan test` (12 tests, 39 assertions).

---

## Étape 13 — Alignement avec la base existante (ID93677_vivat)

- **Résumé** : Alignement du schéma Laravel avec la base fournie par le chef de projet : création des tables legacy (site) pour pouvoir importer les articles et données existantes, sans toucher au pipeline (scraping + génération d’articles IA).
- **Fichiers créés** :
  - `database/migrations/2024_01_01_000000_create_legacy_site_tables.php` — Création des tables `cloaked_ip`, `logs`, `tbl_ref`, `tbl_usr`, `tbl_cont_pg` avec la même structure que le dump `ID93677_vivat.sql` (contID, contTitle, contDesc, contContent, contRef1/2/3, tbl_ref hiérarchique, tbl_usr, etc.).
  - `docs/SCHEMA_BASE_EXISTANTE.md` — Documentation du schéma de la base existante (colonnes, rôles) et du mapping pipeline vs site (articles → tbl_cont_pg, catégories → tbl_ref, users → tbl_usr).
- **Stratégie** :
  - **Pipeline inchangé** : sources, rss_feeds, rss_items, enriched_items, clusters, cluster_items, **articles** (UUID), article_sources, category_templates, pipeline_jobs, triggers.
  - **Site / legacy** : tables `tbl_cont_pg` (articles préexistants), `tbl_ref` (catégories/références), `tbl_usr` (utilisateurs), plus `logs` et `cloaked_ip` pour alignement complet avec le dump.
- **Import des données** : Après `php artisan migrate`, importer les INSERT du fichier SQL fourni vers ces tables (voir `docs/SCHEMA_BASE_EXISTANTE.md`).

---

## Étape 14 — Import données legacy + Audit complet backend pipeline

- **Import vivat_old → vivat** :
  - Base `vivat_old` créée dans Docker avec le dump `ID93677_vivat.sql`.
  - Données copiées : **3756** articles (`tbl_cont_pg`), **71** refs (`tbl_ref`), **3** users (`tbl_usr`).
  - Correction migration legacy : index `TBL_CONT_PG_IDX_PG_CONTENT` avec préfixes pour rester sous la limite MySQL 3072 bytes (utf8mb4).

- **Bugs corrigés** :
  - `FetchRssFeedJob` : ajout `fetched_at => now()` (manquait).
  - `EnrichContentJob` : ajout `enriched_at => now()` (manquait).
  - `ArticleResource` : `content` retourné sur `articles.show`, `generate`, `generate-async`, `publish` (pas seulement show).
  - `ArticleGeneratorService::sanitizeContent()` : correction ParseError sur les guillemets typographiques (regex Unicode).
  - `ContentExtractorService::loadHtml()` : correction `mb_convert_encoding()` deprecated → XML encoding pragma.
  - Tous les Jobs (`FetchRssFeedJob`, `EnrichContentJob`, `GenerateArticleJob`) : suppression de la propriété `$queue` (conflit avec trait `Queueable`), usage de `$this->onQueue()` dans le constructeur.
  - Models avec `$timestamps = false` (`Category`, `CategoryTemplate`, `Cluster`, `RssFeed`, `RssItem`, `PipelineJob`) : ajout cast `'created_at' => 'datetime'` pour éviter les erreurs `toIso8601String()` sur string.
  - `ArticlePolicy::publish()` : retourne toujours `true`, la vérification business `isPublishable()` est dans le controller (message 422 clair).
  - `ArticleController::generate()` : try/catch avec erreur 502 descriptive au lieu d'un stack trace brut.

- **Endpoints ajoutés** :
  - `POST /api/pipeline/fetch-rss` — Déclenche le fetch des flux RSS (tous actifs ou un seul).
  - `POST /api/pipeline/enrich` — Déclenche l'enrichissement des items "new".
  - `GET /api/pipeline/status` — Statut global du pipeline (feeds, items par statut).
  - `CRUD /api/clusters` — Gestion des clusters (create, read, update, delete avec items).
  - `CRUD /api/category-templates` — Gestion des templates de génération par catégorie.

- **Seeder** :
  - `database/seeders/PipelineSeeder.php` : 14 catégories, 6 sources (Reporterre, Futura Sciences, Novethic, Natura Sciences, etc.), 5 flux RSS, 14 category templates.

- **Tests Postman (tous OK)** :
  | Endpoint | Méthode | Résultat |
  |---|---|---|
  | `/api/sources` | GET/POST/PUT/DELETE | OK (CRUD complet) |
  | `/api/categories` | GET (index/show) | OK |
  | `/api/category-templates` | GET/POST/PUT/DELETE | OK |
  | `/api/rss-feeds` | GET/POST/PUT/DELETE | OK |
  | `/api/pipeline/fetch-rss` | POST | OK (120 items récupérés de 5 flux) |
  | `/api/rss-items` | GET (index/show, filtres status/category/feed) | OK |
  | `/api/pipeline/enrich` | POST | OK (extraction OK, OpenAI = quota dépassé) |
  | `/api/pipeline/status` | GET | OK |
  | `/api/clusters` | GET/POST/PUT/DELETE | OK |
  | `/api/articles` | GET/POST/PUT/DELETE | OK |
  | `/api/articles/generate` | POST | OK (validation + OpenAI call, erreur quota 502) |
  | `/api/articles/{id}/publish` | POST | OK (vérification quality_score >= 60) |
  | `/api/stats` | GET | OK |

- **Blocage actuel** : La clé OpenAI (`OPENAI_API_KEY`) a dépassé son quota → les appels enrichissement IA et génération d'articles retournent une erreur 429/502. **Il faut ajouter des crédits** sur https://platform.openai.com/settings/organization/billing.

- **Audit base de données vivat** :
  | Table | Rôle | Rows | Utile ? |
  |---|---|---|---|
  | `sources` | Médias configurés | 6 | Oui (pipeline) |
  | `categories` | 14 catégories thématiques | 14 | Oui (pipeline + site) |
  | `rss_feeds` | Flux RSS liés sources/catégories | 5 | Oui (pipeline) |
  | `rss_items` | Articles bruts découverts | 120 | Oui (pipeline) |
  | `enriched_items` | Contenu extrait et structuré IA | 3 | Oui (pipeline) |
  | `clusters` | Groupes thématiques d'items | 1 | Oui (pipeline) |
  | `cluster_items` | Pivot cluster ↔ rss_item | 2 | Oui (pipeline) |
  | `articles` | Articles générés | 1 | Oui (pipeline) |
  | `article_sources` | Traçabilité article → sources | 0 | Oui (pipeline) |
  | `category_templates` | Config génération par catégorie | 14 | Oui (pipeline) |
  | `pipeline_jobs` | Monitoring des jobs | 0 | Oui (monitoring) |
  | `tbl_cont_pg` | Articles legacy (site existant) | 3756 | Oui (données importées) |
  | `tbl_ref` | Catégories legacy | 71 | Oui (données importées) |
  | `tbl_usr` | Utilisateurs legacy | 3 | Oui (données importées) |
  | `users` | Users Laravel (auth) | 0 | Oui (auth future) |
  | `sessions` | Sessions Laravel | 0 | Oui (auth) |
  | `cache` / `cache_locks` | Cache Laravel | 0 | Oui (framework) |
  | `jobs` / `job_batches` / `failed_jobs` | Queue Laravel | 0 | Oui (queue) |
  | `migrations` | Migration tracking | 15 | Oui (framework) |
  | `password_reset_tokens` | Reset password | 0 | Oui (auth) |
  | `cloaked_ip` | IPs cloakées (legacy) | 0 | Non essentiel (legacy vide) |
  | `logs` | Logs legacy (MyISAM) | 0 | Non essentiel (legacy vide) |

  **Conclusion** : Toutes les tables servent. Les 2 tables legacy vides (`cloaked_ip`, `logs`) sont non essentielles mais ne gênent pas.

---

## Étape 15 — Sélection intelligente des articles + SEO + Guide de tests

- **Problématique** : Avec 120+ items RSS récupérés et ~15 infos par jour, comment décider QUEL article générer ? Pourquoi celui-ci et pas un autre ?

- **ArticleSelectionService** (`app/Services/ArticleSelectionService.php`) :
  - **Scoring multi-critères** (0-100) :
    - Fraîcheur (25%) : articles < 48h = score max, décroît sur 7 jours
    - Qualité contenu (25%) : quality_score de l'enrichissement + bonus contenu long
    - Potentiel SEO (30%) : mots-clés longue traîne, spécifiques, faible concurrence
    - Diversité sources (20%) : multi-sources = synthèse à haute valeur (+10/source)
  - **Regroupement par sujet** : similarité de Jaccard sur les mots-clés (>= 20% = même sujet)
  - **Sélection** : prend les N meilleurs groupes et explique POURQUOI
  - **Mots-clés SEO heuristiques** : termes de 6-12 chars, détection termes haute valeur (environnement), pénalisation termes génériques
  - **Reasoning** : chaque proposition a un texte explicatif complet (sources, qualité, SEO, fraîcheur)

- **Enrichissement SEO amélioré** :
  - Nouveau prompt OpenAI qui demande : `seo_keywords` (5-10 mots longue traîne), `primary_topic`, `seo_score` (0-100)
  - Migration `2024_01_01_000013_add_seo_keywords_to_enriched_items.php` : colonnes `seo_keywords` (JSON), `seo_score` (tinyint), `primary_topic` (varchar)
  - Model `EnrichedItem` mis à jour avec les nouveaux champs

- **Prompt de génération amélioré** :
  - Inclusion des mots-clés SEO consolidés dans le user prompt
  - Consignes SEO dans le system prompt (densité 1-2%, mot-clé dans titre + H1 + premier paragraphe)
  - meta_title 50-60 chars, meta_description 150-160 chars
  - Sources nommées dans le prompt pour meilleure synthèse

- **Nouvel endpoint** :
  - `GET /api/pipeline/select-items?count=3&category_id=` — Propositions d'articles classées par pertinence avec reasoning

- **Documentation complète** :
  - `docs/TESTING_POSTMAN.md` : 37 endpoints documentés, 17 scénarios de test, workflow complet du RSS à l'article publié, scénarios d'erreur, commandes Artisan

---

*Dernière mise à jour : étape 15 (sélection intelligente, SEO, guide de tests Postman complet).*
