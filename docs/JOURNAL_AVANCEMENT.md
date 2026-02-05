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
- [x] Form Requests dédiés (Store/Update Source, RssFeed, Article ; GenerateArticle), Policies (Source, RssFeed, Article).
- [x] Commandes Artisan (rss:fetch, content:enrich, cleanup:old, articles:generate).
- [ ] (Optionnel) Auth Sanctum, Filament admin, Events/Listeners, tests.

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

*Dernière mise à jour : étape 11 (commandes Artisan pipeline) ajoutée.*
