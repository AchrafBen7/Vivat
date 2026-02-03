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

## Prochaines étapes (à remplir au fur et à mesure)

- [ ] Ajouter les 12 migrations du pipeline (depuis `docs/MIGRATIONS_REFERENCE.md`) et lancer `php artisan migrate`.
- [ ] Créer les 11 models du pipeline (depuis `docs/MODELS_REFERENCE.md`).
- [ ] (Optionnel) Services, Jobs, Horizon, etc. selon `docs/EXEMPLES_CODE_REFERENCE.md`.

---

*Dernière mise à jour : journal créé — étapes 1 à 3 remplies.*
