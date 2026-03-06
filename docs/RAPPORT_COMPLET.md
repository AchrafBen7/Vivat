# Vivat — Rapport complet du travail realise

> **Projet** : Vivat — Plateforme media automatisee
> **Developpeur Backend** : Achraf Ben Ali
> **Periode** : Janvier — Fevrier 2026
> **Technologie** : Laravel 12, PHP 8.3, MySQL 8, Redis, Docker, OpenAI GPT-4o, Stripe

---

## Introduction

Ce document presente de maniere detaillee tout le travail effectue sur le backend du projet Vivat, de la premiere ligne de documentation jusqu'a l'API complete avec 94 endpoints. Il est destine au chef de projet pour avoir une vue claire de l'avancement, des choix techniques et de ce qui reste a faire.

Le projet Vivat est une plateforme media qui repose sur un moteur d'acquisition de contenu automatise : le systeme recupere automatiquement des articles depuis des flux RSS de medias francophones, les analyse par intelligence artificielle, puis genere des articles originaux optimises pour le referencement (SEO).

---

## PHASE 1 — Fondations du projet

### Etape 1 — Documentation technique prealable

**Objectif** : Ne pas coder a l'aveugle. Poser les bases avant de commencer.

Avant d'ecrire la moindre ligne de code, j'ai redige 5 documents de reference :

| Document | Contenu |
|---|---|
| `CONTEXTE_PROJET.md` | La vision du produit, les 12 fonctionnalites prevues, la stack technique choisie (Laravel 12, MySQL, Redis, OpenAI, Stripe), et la liste de tous les fichiers a creer |
| `EXEMPLES_CODE_REFERENCE.md` | Des exemples concrets de code pour chaque composant : comment fetcher un flux RSS, comment extraire du contenu HTML, comment appeler OpenAI, comment gerer les queues |
| `MIGRATIONS_REFERENCE.md` | Le schema de toutes les tables a creer pour le pipeline, avec les types de colonnes, les relations et les index |
| `MODELS_REFERENCE.md` | La structure de chaque model (objet PHP qui represente une table), avec les relations entre eux |
| `SCHEMA_AUTRES_FONCTIONNALITES.md` | Le schema prevu pour les fonctionnalites cote site (utilisateurs, paiements, contributions, etc.) |

**Pourquoi c'est important** : Ces documents servent de feuille de route. Chaque decision est documentee avant l'implementation. Un nouveau developpeur peut lire ces fichiers et comprendre le projet en 30 minutes.

---

### Etape 2 — Creation du projet Laravel 12

**Objectif** : Initialiser le projet avec le framework.

- Installation de Laravel 12, la derniere version du framework PHP le plus utilise au monde
- Installation de Laravel Boost (outils de developpement pour l'IDE Cursor)
- Mise en place des conventions de code et des regles de developpement

**Resultat** : Un projet Laravel vierge mais structure, pret a recevoir du code.

---

### Etape 3 — Configuration de la base de donnees

**Objectif** : Utiliser MySQL au lieu de SQLite (la base par defaut de Laravel).

- Configuration de MySQL 8 comme base de donnees principale
- Encodage `utf8mb4` pour supporter les caracteres speciaux (accents, emojis)
- Parametrage des variables d'environnement (identifiants, ports)

**Pourquoi MySQL et pas SQLite** : MySQL est une base de donnees production-ready, plus performante, avec support du full-text search (recherche par mots-cles dans les articles) et des triggers (mises a jour automatiques).

---

### Etape 4 — Dockerisation complete de l'environnement

**Objectif** : Que n'importe quel developpeur puisse lancer le projet en une seule commande.

J'ai cree un environnement Docker complet avec 4 services :

| Service | Technologie | Port | Role |
|---|---|---|---|
| **app** | PHP 8.3 (Alpine Linux) | 8000 | L'application Laravel elle-meme |
| **mysql** | MySQL 8.0 | 3306 | La base de donnees |
| **redis** | Redis 7 | 6379 | Cache, files d'attente, sessions |
| **phpmyadmin** | phpMyAdmin | 8080 | Interface web pour visualiser la base |

**Fichiers crees** :
- `Dockerfile` : l'image PHP avec toutes les extensions necessaires (pdo_mysql, redis, gd pour les images, zip, bcmath, intl pour les langues)
- `docker-compose.yml` : l'orchestration des 4 services avec healthcheck sur MySQL et volumes persistants (les donnees survivent au redemarrage)
- `docker/entrypoint.sh` : script qui installe automatiquement les dependances au premier lancement
- `.dockerignore` : liste des fichiers a ne pas inclure dans l'image Docker

**Utilisation** :
```
docker compose up -d          ← lance tout
docker compose exec app bash  ← entre dans le container
```

**Pourquoi Docker** : L'environnement est identique en local, en staging et en production. Plus de "ca marche chez moi mais pas chez toi". Un seul `docker compose up -d` et tout est pret.

---

## PHASE 2 — Le moteur d'acquisition de contenu (fonctionnalite principale)

C'est le coeur du projet. Un pipeline automatise en 5 etapes qui transforme des flux RSS en articles originaux.

### Comment ca marche (vue simplifiee)

```
Medias (Reporterre, Futura Sciences, Novethic...)
        |
        v
  [1] RECUPERATION des flux RSS (automatique, 1 fois par jour)
        |
        v
  [2] ANALYSE par IA : l'article est scrape, structure, note
        |
        v
  [3] SELECTION : l'algorithme choisit les meilleurs sujets
        |
        v
  [4] GENERATION : l'IA ecrit un article original a partir de plusieurs sources
        |
        v
  [5] PUBLICATION : l'article passe en review puis est publie
```

---

### Etape 5 — Schema de base de donnees (12 migrations, 11 tables)

**Objectif** : Creer toutes les tables necessaires au fonctionnement du pipeline.

J'ai ecrit **12 fichiers de migration** (des scripts PHP qui creent les tables automatiquement). Chaque migration est numerotee pour s'executer dans le bon ordre, car certaines tables dependent d'autres (un flux RSS est lie a une source, donc la table `sources` doit exister avant `rss_feeds`).

**Choix technique important** : Toutes les tables utilisent des **UUID** comme identifiant (ex: `019c413d-f29d-71b9-b289-0ff1bd1ac7ad`) au lieu de nombres (1, 2, 3...). Avantages : impossible de deviner un ID dans l'URL, pas de collision si on fusionne des bases, compatible avec une architecture distribuee.

Voici le detail de chaque table creee, avec ses colonnes et son role :

---

#### Table 1 — `sources` (les medias surveilles)

Chaque source represente un media qu'on surveille (Reporterre, Futura Sciences, Novethic...).

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `name` | string | Nom du media ("Reporterre") |
| `base_url` | string | URL du site ("https://reporterre.net") |
| `language` | string(10), defaut: 'fr' | Langue du media |
| `is_active` | boolean, defaut: true | Est-ce qu'on surveille ce media ? |
| `created_at` | timestamp | Date de creation |
| `updated_at` | timestamp | Date de derniere modification (mise a jour automatique via un trigger MySQL) |

**Index** : sur `is_active` et `language` pour filtrer rapidement les sources actives.

---

#### Table 2 — `categories` (les 14 categories thematiques)

Les categories editoriales du site (Environnement, Sante, Energie, Alimentation...).

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `name` | string | Nom de la categorie ("Environnement") |
| `slug` | string, unique | Version URL-friendly ("environnement") — utilisee dans les URLs |
| `description` | text, optionnel | Description de la categorie |
| `color` | string(7), defaut: '#3B82F6' | Couleur d'affichage (code hexadecimal) |
| `created_at` | timestamp | Date de creation |

**Index unique** sur `slug` : deux categories ne peuvent pas avoir le meme slug.

---

#### Table 3 — `rss_feeds` (les flux RSS a recuperer)

Chaque flux RSS est lie a une source et a une categorie. Le systeme sait quand il doit les recuperer.

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `source_id` | UUID (cle etrangere → `sources`) | A quel media appartient ce flux |
| `category_id` | UUID (cle etrangere → `categories`) | Dans quelle categorie classer les items |
| `feed_url` | text | L'URL du flux RSS a recuperer |
| `is_active` | boolean, defaut: true | Est-ce qu'on recupere ce flux ? |
| `last_fetched_at` | timestamp, optionnel | Derniere fois qu'on l'a recupere |
| `fetch_interval_minutes` | entier, defaut: 30 | Toutes les combien de minutes on recupere |
| `created_at` | timestamp | Date de creation |

**Index composite** `idx_feeds_due_fetch` sur (`is_active`, `last_fetched_at`) : permet de trouver instantanement les flux qui doivent etre recuperes.

**Cles etrangeres** : si on supprime une source, le `source_id` passe a NULL (l'article n'est pas supprime).

---

#### Table 4 — `rss_items` (les articles bruts decouverts)

Chaque item est un article decouvert dans un flux RSS. C'est la matiere premiere du pipeline.

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `rss_feed_id` | UUID (cle etrangere → `rss_feeds`) | De quel flux vient cet item |
| `category_id` | UUID (cle etrangere → `categories`) | Categorie assignee |
| `guid` | string, optionnel | Identifiant unique donne par le flux RSS |
| `title` | string | Titre de l'article |
| `description` | text, optionnel | Resume fourni par le flux RSS |
| `url` | text | URL de l'article original |
| `published_at` | timestamp, optionnel | Date de publication originale |
| `fetched_at` | timestamp | Quand on l'a recupere |
| `status` | enum | `new` → `enriching` → `enriched` → `used` (ou `failed`/`ignored`) |
| `dedup_hash` | string(64), unique | Hash SHA-256 de l'URL+titre — empeche les doublons |
| `created_at` | timestamp | Date de creation |

**Statuts possibles** et leur signification :
- `new` : vient d'etre decouvert, pas encore analyse
- `enriching` : en cours d'analyse par l'IA
- `enriched` : analyse terminee, pret pour la selection
- `used` : a ete utilise pour generer un article
- `failed` : l'enrichissement a echoue
- `ignored` : ignore (contenu non pertinent)

**Index full-text** sur (`title`, `description`) : permet la recherche par mots-cles dans les items.

**Deduplication** : le hash `dedup_hash` empeche d'inserer deux fois le meme article, meme s'il apparait dans plusieurs flux RSS.

---

#### Table 5 — `enriched_items` (l'analyse IA de chaque article)

Chaque item enrichi est le resultat de l'analyse IA d'un article brut. Relation 1:1 avec `rss_items`.

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `rss_item_id` | UUID (cle etrangere → `rss_items`), unique | L'item source |
| `lead` | text, optionnel | Le chapeau / introduction de l'article |
| `headings` | JSON, optionnel | Les titres de sections extraits (H2, H3...) |
| `key_points` | JSON, optionnel | Les points cles identifies par l'IA |
| `seo_keywords` | JSON, optionnel | Mots-cles SEO longue traine extraits par l'IA |
| `primary_topic` | string, optionnel | Le sujet principal identifie |
| `extracted_text` | longText, optionnel | Le texte complet scrape et nettoye |
| `extraction_method` | string(50), defaut: 'readability' | Methode utilisee pour le scraping |
| `quality_score` | entier (0-255), defaut: 0 | Score de qualite donne par l'IA (0-100) |
| `seo_score` | entier (0-255), defaut: 0 | Score de potentiel SEO (0-100) |
| `enriched_at` | timestamp | Quand l'enrichissement a ete fait |

**Contrainte unique** sur `rss_item_id` : un item RSS n'a qu'un seul enrichissement.

**Les champs JSON** (`headings`, `key_points`, `seo_keywords`) sont stockes au format JSON natif MySQL — ca permet de stocker des listes sans creer de tables supplementaires.

---

#### Table 6 — `clusters` (groupes d'articles par sujet)

Les clusters regroupent des items RSS qui parlent du meme sujet (ex: 3 articles de 3 medias sur "la transition energetique" forment 1 cluster).

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `category_id` | UUID (cle etrangere → `categories`) | Categorie du cluster |
| `label` | string | Nom du sujet ("Transition energetique en Europe") |
| `keywords` | JSON, optionnel | Mots-cles partages par les items du cluster |
| `status` | enum | `pending` → `processing` → `generated` (ou `failed`) |
| `created_at` | timestamp | Date de creation |

---

#### Table 7 — `cluster_items` (table pivot cluster ↔ item RSS)

Table de liaison qui dit "cet item RSS fait partie de ce cluster". Relation N:N.

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `cluster_id` | UUID (cle etrangere → `clusters`) | Le cluster |
| `rss_item_id` | UUID (cle etrangere → `rss_items`) | L'item RSS |

**Contrainte unique** sur (`cluster_id`, `rss_item_id`) : un item ne peut etre qu'une fois dans un cluster.

**Suppression en cascade** : si on supprime un cluster, toutes ses liaisons sont supprimees automatiquement.

---

#### Table 8 — `articles` (les articles generes par l'IA)

C'est la table la plus importante : les articles finaux publies sur le site.

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `title` | string | Titre de l'article |
| `slug` | string, unique | URL-friendly du titre ("transition-energetique-europe-2026") |
| `excerpt` | text, optionnel | Resume court (affiche dans les listes) |
| `content` | longText | Le contenu HTML complet de l'article |
| `meta_title` | string, optionnel | Titre pour le SEO (balise `<title>`) |
| `meta_description` | string, optionnel | Description pour le SEO (balise `<meta>`) |
| `keywords` | JSON, optionnel | Mots-cles de l'article |
| `category_id` | UUID (cle etrangere → `categories`) | Categorie de l'article |
| `cluster_id` | UUID (cle etrangere → `clusters`) | Cluster source (optionnel) |
| `reading_time` | entier, defaut: 5 | Temps de lecture estime (en minutes) |
| `status` | enum | `draft` → `review` → `published` (ou `archived`/`rejected`) |
| `quality_score` | entier (0-255), defaut: 0 | Score de qualite IA (doit etre >= 60 pour etre publiable) |
| `published_at` | timestamp, optionnel | Date de publication (rempli quand status = published) |
| `created_at` | timestamp | Date de creation |
| `updated_at` | timestamp | Derniere modification (trigger MySQL automatique) |

**Statuts possibles** :
- `draft` : brouillon, vient d'etre genere par l'IA
- `review` : en cours de relecture par l'equipe editoriale
- `published` : publie et visible sur le site
- `archived` : retire du site mais conserve en base
- `rejected` : refuse (qualite insuffisante)

**Index full-text** sur (`title`, `excerpt`) : permet la recherche par mots-cles sur le site.

**Index composite** `idx_articles_published` sur (`status`, `published_at`) : optimise la requete "afficher les articles publies tries par date", la plus frequente sur le site.

---

#### Table 9 — `article_sources` (tracabilite : d'ou vient chaque article)

Permet de savoir exactement quels articles RSS ont ete utilises pour generer un article. C'est l'audit trail.

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `article_id` | UUID (cle etrangere → `articles`) | L'article genere |
| `rss_item_id` | UUID (cle etrangere → `rss_items`) | L'item RSS source |
| `source_id` | UUID (cle etrangere → `sources`) | Le media d'origine |
| `url` | text | L'URL de l'article original |
| `used_at` | timestamp | Quand cette source a ete utilisee |

**Utilite** : un article genere peut dire "Base sur 3 sources : Reporterre (url1), Futura Sciences (url2), Novethic (url3)". C'est essentiel pour la transparence et le respect des sources.

---

#### Table 10 — `category_templates` (regles de generation par categorie)

Chaque categorie a un template qui dicte a l'IA comment ecrire. Le ton, la longueur et les regles SEO changent selon la categorie.

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `category_id` | UUID (cle etrangere → `categories`), unique | La categorie concernee |
| `tone` | string(50), defaut: 'professional' | Le ton de l'article (professionnel, pedagogique, engage...) |
| `structure` | string(50), defaut: 'standard' | Structure de l'article (standard, analyse, liste, interview...) |
| `min_word_count` | entier, defaut: 900 | Nombre minimum de mots |
| `max_word_count` | entier, defaut: 2000 | Nombre maximum de mots |
| `style_notes` | text, optionnel | Notes additionnelles sur le style |
| `seo_rules` | text, optionnel | Regles SEO specifiques a la categorie |

**Exemple concret** : pour la categorie "Environnement", le template dit : ton = pedagogique, 1000-1800 mots, "Utiliser des donnees chiffrees et des sources scientifiques, inclure des pistes d'action concretes".

---

#### Table 11 — `pipeline_jobs` (suivi de chaque tache)

Enregistre chaque execution du pipeline pour le monitoring et le debug.

| Colonne | Type | Description |
|---|---|---|
| `id` | UUID (cle primaire) | Identifiant unique |
| `job_type` | enum | `fetch_rss`, `enrich`, `cluster`, `generate`, `publish`, `cleanup` |
| `status` | enum | `pending` → `running` → `completed` (ou `failed`) |
| `started_at` | timestamp, optionnel | Debut de l'execution |
| `completed_at` | timestamp, optionnel | Fin de l'execution |
| `error_message` | text, optionnel | Message d'erreur (si echec) |
| `metadata` | JSON, optionnel | Donnees supplementaires (nombre d'items traites, duree...) |
| `retry_count` | entier, defaut: 0 | Nombre de tentatives en cas d'echec |
| `created_at` | timestamp | Date de creation |

**Index composite** `idx_jobs_pending` sur (`status`, `created_at`) : permet de retrouver instantanement les jobs en attente.

---

#### Migration 12 — Triggers de mise a jour automatique

**Contexte** : En Laravel, quand un model a `$timestamps = true` (valeur par defaut), le framework met a jour automatiquement les colonnes `created_at` et `updated_at` a chaque sauvegarde. Pour les tables `sources` et `articles`, on utilise bien les timestamps, mais pour eviter tout oubli (par exemple si on modifie une ligne en SQL brut ou via un autre outil), j'ai ajoute une **securite au niveau de la base de donnees** : un **trigger MySQL**.

**Qu'est-ce qu'un trigger ?**  
Un trigger est une regle stockee dans MySQL qui s'execute automatiquement avant ou apres une action (INSERT, UPDATE, DELETE). Ici, on utilise un trigger **BEFORE UPDATE** : juste avant que MySQL n'ecrive la ligne modifiee, il execute notre regle et met a jour la colonne `updated_at`.

**Comment c'est fait en code** :  
Fichier : `database/migrations/2024_01_01_000012_create_updated_at_triggers.php`.

1. **Verification du moteur** : le code verifie qu'on est bien sur MySQL (`DB::getDriverName() === 'mysql'`). Si on utilise SQLite (par exemple pour les tests), les triggers ne sont pas crees, car la syntaxe des triggers est differente selon les moteurs.

2. **Creation des triggers** : on execute du SQL "brut" via `DB::unprepared()` car Laravel ne fournit pas de methode dediee pour les triggers. Pour chaque table (`sources` et `articles`), on cree un trigger avec le meme principe :

   ```sql
   CREATE TRIGGER sources_updated_at_trigger
   BEFORE UPDATE ON sources
   FOR EACH ROW
   SET NEW.updated_at = NOW()
   ```

   - **BEFORE UPDATE** : le trigger s'execute avant que la ligne soit ecrite.
   - **ON sources** (puis **ON articles**) : uniquement quand on fait un `UPDATE` sur cette table.
   - **FOR EACH ROW** : pour chaque ligne modifiee par la requete.
   - **SET NEW.updated_at = NOW()** : on force la valeur qui sera ecrite : la date/heure courante. `NEW` designe la nouvelle version de la ligne en cours d'update.

3. **Rollback (methode `down()`)** : si on annule la migration, on supprime les triggers avec `DROP TRIGGER IF EXISTS ...` pour ne rien laisser en base.

**En resume** : a chaque fois qu'une ligne de `sources` ou d'`articles` est modifiee (que ce soit depuis Laravel, phpMyAdmin ou une requete SQL directe), MySQL met automatiquement `updated_at` a la date du jour. C'est fait une seule fois, dans une migration, et c'est la base de donnees qui garantit la coherence.

---

#### Resume des tables du pipeline

```
sources ──→ rss_feeds ──→ rss_items ──→ enriched_items
   |             |             |                |
   |             |             ↓                |
   |             |        cluster_items ←── clusters
   |             |             |
   |             ↓             ↓
   └──→ article_sources ←── articles ←── category_templates
                                |
                          pipeline_jobs (monitoring)
```

**En chiffres** : 12 fichiers de migration, 11 tables, 4 cles etrangeres avec cascade, 2 index full-text (recherche), 8 index composites (performance), 2 triggers (automatisation), et chaque colonne est typee avec precision.

---

### Etape 6 — phpMyAdmin

**Objectif** : Avoir une interface graphique pour visualiser la base de donnees.

J'ai ajoute phpMyAdmin dans le `docker-compose.yml` :
- Accessible sur `http://localhost:8080`
- Connexion automatique au serveur MySQL
- Permet de voir les tables, les donnees, executer des requetes SQL, exporter des dumps

C'est un outil de developpement — il ne sera pas present en production. Il est utile pour :
- Verifier que les migrations ont bien cree les tables
- Inspecter les donnees inserees par le pipeline
- Debugger les problemes de donnees
- Montrer la structure de la base au chef de projet

---

### Etape 7 — Models Eloquent (11 models)

**Objectif** : Creer la couche PHP qui represente chaque table et definit les regles metier.

En Laravel, chaque table a un **Model** : un fichier PHP qui definit comment lire, ecrire, relier et manipuler les donnees de cette table. C'est le coeur de l'application.

J'ai cree **11 models** dans `/app/Models/`. Voici ce que contient chaque model :

---

#### Model `Source` (fichier `app/Models/Source.php`)

Represente un media surveille.

**Ce qu'on peut faire avec** :
- `Source::active()->get()` → recuperer toutes les sources actives
- `$source->rssFeeds` → acceder a tous les flux RSS de cette source
- `$source->articleSources` → voir dans quels articles cette source a ete citee

| Element | Detail |
|---|---|
| Relations | `rssFeeds()` : a plusieurs flux RSS / `articleSources()` : citee dans plusieurs articles |
| Scope | `active()` : filtre les sources ou `is_active = true` |

---

#### Model `Category` (fichier `app/Models/Category.php`)

Represente une categorie thematique.

**Ce qu'on peut faire avec** :
- `$category->articles` → tous les articles de cette categorie
- `$category->template` → les regles de generation pour cette categorie
- `$category->rssFeeds` → les flux RSS associes a cette categorie

| Element | Detail |
|---|---|
| Relations | `rssFeeds()`, `rssItems()`, `clusters()`, `articles()` : a plusieurs de chaque / `template()` : a un seul template de generation |

---

#### Model `RssFeed` (fichier `app/Models/RssFeed.php`)

Represente un flux RSS a surveiller.

**Ce qu'on peut faire avec** :
- `RssFeed::active()->get()` → les flux actifs
- `RssFeed::dueForFetch()->get()` → les flux qu'il faut recuperer maintenant (la derniere recuperation + l'intervalle est depasse)
- `$feed->source` → le media auquel appartient ce flux
- `$feed->items` → tous les items decouverts dans ce flux

| Element | Detail |
|---|---|
| Relations | `source()` : appartient a une source / `category()` : appartient a une categorie / `items()` : a plusieurs items RSS |
| Scopes | `active()` : flux actifs / `dueForFetch()` : flux qui doivent etre recuperes (calcul automatique basé sur `last_fetched_at` et `fetch_interval_minutes`) |

Le scope `dueForFetch()` est important : c'est lui qui determine automatiquement quels flux doivent etre recuperes en comparant `last_fetched_at + fetch_interval_minutes` avec l'heure actuelle.

---

#### Model `RssItem` (fichier `app/Models/RssItem.php`)

Represente un article brut decouvert dans un flux RSS.

**Ce qu'on peut faire avec** :
- `RssItem::new()->get()` → les items pas encore analyses
- `RssItem::enriched()->get()` → les items analyses et prets pour la selection
- `$item->enrichedItem` → l'analyse IA de cet item
- `$item->clusters` → les clusters dont fait partie cet item
- `$item->isEnriched()` → verifie que l'item a bien ete enrichi

| Element | Detail |
|---|---|
| Relations | `rssFeed()` : vient de quel flux / `category()` : categorie / `enrichedItem()` : son analyse IA (1:1) / `clusters()` : ses groupes thematiques (N:N via `cluster_items`) / `articleSources()` : dans quels articles finaux il a ete utilise |
| Scopes | `status($status)` : filtre par statut / `new()` : statut "new" / `enriched()` : statut "enriched" |
| Methode metier | `isEnriched()` : retourne `true` si le statut est "enriched" ET que l'enrichissement existe en base |

---

#### Model `EnrichedItem` (fichier `app/Models/EnrichedItem.php`)

Represente l'analyse IA d'un article. Lie 1:1 a un `RssItem`.

**Ce qu'on peut faire avec** :
- `$enriched->rssItem` → l'item RSS original
- `$enriched->getWordCount()` → compter les mots du texte extrait
- `$enriched->isHighQuality()` → savoir si le score qualite est >= 70

| Element | Detail |
|---|---|
| Relations | `rssItem()` : l'item RSS source |
| Methodes metier | `getWordCount()` : retourne le nombre de mots du `extracted_text` / `isHighQuality()` : retourne `true` si `quality_score >= 70` |

Les champs JSON (`headings`, `key_points`, `seo_keywords`) sont automatiquement convertis en tableaux PHP grace au cast `array`. On peut ecrire `$enriched->seo_keywords` et recevoir directement `['recyclage', 'textile', 'economie circulaire']`.

---

#### Model `Cluster` (fichier `app/Models/Cluster.php`)

Represente un groupe d'articles sur le meme sujet.

| Element | Detail |
|---|---|
| Relations | `category()` : categorie / `rssItems()` : les items du cluster (N:N via `cluster_items`) / `article()` : l'article genere a partir de ce cluster (1:1) |
| Scopes | `pending()` : clusters en attente / `generated()` : clusters deja utilises pour generer un article |

---

#### Model `ClusterItem` (fichier `app/Models/ClusterItem.php`)

Table pivot entre `Cluster` et `RssItem`. Pas de logique metier, juste les liaisons.

---

#### Model `Article` (fichier `app/Models/Article.php`)

**Le model le plus important** — represente un article genere par l'IA et publie sur le site.

**Ce qu'on peut faire avec** :
- `Article::published()->get()` → tous les articles publies
- `Article::draft()->get()` → les brouillons
- `$article->isPublishable()` → est-ce que cet article peut etre publie ?
- `$article->publish()` → publier l'article (change le statut et rempli `published_at`)
- `$article->category` → sa categorie
- `$article->sources` → les medias originaux qui ont servi a le generer
- `$article->articleSources` → le detail de chaque source avec l'URL

| Element | Detail |
|---|---|
| Relations | `category()` : appartient a une categorie / `cluster()` : genere a partir d'un cluster / `articleSources()` : liens vers les items RSS sources / `sources()` : les medias sources (N:N via `article_sources`, avec les champs pivot `rss_item_id`, `url`, `used_at`) |
| Scopes | `status($status)` : filtre par statut / `published()` : articles publies avec `published_at` non null / `draft()` : brouillons |
| Methode `isPublishable()` | Retourne `true` si **quality_score >= 60** ET statut est `draft` ou `review`. Un article avec un score de 45 ne peut PAS etre publie — c'est une regle metier qui garantit la qualite minimale. |
| Methode `publish()` | Change le statut en `published`, rempli `published_at` avec la date actuelle, et sauvegarde. Refuse de publier si `isPublishable()` est false. |

**Pourquoi le seuil de 60 ?** C'est un compromis : assez haut pour filtrer les articles de mauvaise qualite, assez bas pour ne pas bloquer des articles corrects. Ce seuil est modifiable.

---

#### Model `ArticleSource` (fichier `app/Models/ArticleSource.php`)

La tracabilite : pour chaque article genere, on sait exactement quelles sources ont ete utilisees.

| Element | Detail |
|---|---|
| Relations | `article()` : l'article genere / `rssItem()` : l'item RSS source / `source()` : le media d'origine |

---

#### Model `CategoryTemplate` (fichier `app/Models/CategoryTemplate.php`)

Les regles de generation pour chaque categorie.

| Element | Detail |
|---|---|
| Relations | `category()` : la categorie concernee |

Quand l'IA genere un article pour la categorie "Environnement", elle regarde le template : ton pedagogique, 1000-1800 mots, regles SEO specifiques. Pour la categorie "Technologie", le ton sera plus analytique et la structure plus technique.

---

#### Model `PipelineJob` (fichier `app/Models/PipelineJob.php`)

Le monitoring de chaque tache du pipeline.

| Element | Detail |
|---|---|
| Scopes | `running()` : jobs en cours / `failed()` : jobs echoues / `ofType($type)` : jobs d'un type precis (ex: `fetch_rss`) |
| Methode `start()` | Passe le statut a `running` et enregistre l'heure de debut |
| Methode `complete()` | Passe le statut a `completed` et enregistre l'heure de fin |
| Methode `fail($message)` | Passe le statut a `failed`, enregistre le message d'erreur, et incremente le compteur de tentatives |

**Exemple d'utilisation dans le code** :
```php
$job = PipelineJob::create(['job_type' => 'fetch_rss']);
$job->start();                    // statut → "running"
// ... execution du fetch ...
$job->complete();                 // statut → "completed"
// OU en cas d'erreur :
$job->fail("Timeout apres 30s"); // statut → "failed", retry_count +1
```

---

#### Resume des models : la chaine de relations

```
Source (1) ──→ (N) RssFeed (1) ──→ (N) RssItem (1) ──→ (1) EnrichedItem
                                          |
                                          ↕ (N:N)
                                       Cluster (1) ──→ (1) Article
                                                              |
                                                         ArticleSource
                                                         (tracabilite)
```

**En chiffres** : 11 models, 23 relations definies, 8 scopes reutilisables, 7 methodes metier, tous avec UUIDs, et chaque champ JSON est automatiquement converti en tableau PHP.

---

### Etape 8 — Services, Jobs, Horizon et Scheduler

**Objectif** : Implementer toute la logique du pipeline.

C'est l'etape la plus complexe. J'ai separe le code en 3 couches :

**Les Services** (la logique metier pure) :

| Service | Ce qu'il fait |
|---|---|
| `RssParserService` | Prend un fichier XML (RSS ou Atom) et en extrait les articles avec titre, URL, date. Genere un hash unique pour eviter les doublons. |
| `ContentExtractorService` | Va sur le site original, scrape la page HTML, extrait uniquement le contenu de l'article (retire les menus, pubs, footers). Si le contenu est trop court, explore les liens internes. |
| `ArticleGeneratorService` | Construit un prompt pour OpenAI a partir de plusieurs sources enrichies, appelle l'API, parse la reponse JSON, cree l'article en base avec le score qualite et les metadonnees SEO. |

**Les Jobs** (taches asynchrones — ne bloquent pas le serveur) :

| Job | Queue | Ce qu'il fait |
|---|---|---|
| `FetchRssFeedJob` | `rss` | Recupere un flux RSS et cree les items en base |
| `EnrichContentJob` | `enrichment` | Scrape une page + appelle OpenAI pour l'analyse |
| `GenerateArticleJob` | `generation` | Genere un article complet via OpenAI |

**Laravel Horizon** : un dashboard temps reel (accessible sur `http://localhost:8000/horizon`) qui montre l'etat de toutes les queues, les jobs en cours, les echecs, les temps de traitement.

**Le Scheduler** (taches automatiques) :

| Tache | Frequence |
|---|---|
| Recuperer les flux RSS | 1 fois par jour |
| Enrichir les nouveaux items par IA | Toutes les heures (50 items max) |
| Snapshot des stats Horizon | Toutes les 5 minutes |
| Nettoyage des jobs echoues | Tous les jours |

**Tout est automatise** : une fois le serveur lance, le pipeline tourne tout seul sans intervention humaine.

---

### Etape 9 — API REST complete

**Objectif** : Permettre a n'importe quel client (frontend, mobile, Postman) de communiquer avec le backend.

J'ai cree une API REST avec :
- **6 controllers** : un par ressource (Sources, Categories, RSS Feeds, RSS Items, Articles, Stats)
- **7 resources JSON** : chaque reponse est formatee proprement (pas de donnees sensibles exposees)
- **Pagination** : les listes sont paginees (12 articles/page par defaut, max 50)
- **Filtres** : par statut, par categorie, par date, par duree de lecture

Tous les endpoints sont documentees et testables dans Postman.

---

### Etape 10 — Validation et autorisations

**Objectif** : S'assurer que les donnees envoyees sont valides et que les actions sont autorisees.

- **7 Form Requests** : chaque requete est validee avant d'atteindre le code metier (ex: on verifie que l'URL d'un flux RSS est bien une URL valide)
- **3 Policies** : des regles d'autorisation par ressource (qui peut creer une source ? Qui peut publier un article ?)

---

### Etape 11 — Commandes Artisan (CLI)

**Objectif** : Pouvoir declencher le pipeline manuellement en ligne de commande.

4 commandes creees :
- `php artisan rss:fetch --all` → recupere tous les flux RSS maintenant
- `php artisan content:enrich --limit=50` → enrichit les 50 prochains items
- `php artisan cleanup:old --days=90` → supprime les vieilles donnees
- `php artisan articles:generate` → lance la generation d'articles

Utile pour le developpement et les tests sans attendre le scheduler.

---

### Etape 12 — Tests automatises

**Objectif** : S'assurer que le code fonctionne correctement.

- **12 tests** avec **39 assertions** (PHPUnit)
- Tests unitaires : le parsing RSS fonctionne, la logique de publication est correcte
- Tests d'integration : les endpoints API retournent les bonnes reponses
- Compatibilite SQLite pour que les tests tournent en memoire (plus rapide)

---

## PHASE 3 — Analyse et integration de l'ancienne base de donnees

### Etape 13 — Analyse de la base existante

**Objectif** : Comprendre la base de donnees du site existant et decider quoi reutiliser.

Le chef de projet m'a fourni un dump SQL de l'ancienne base (`ID93677_vivat.sql`). J'ai analyse chaque table en detail :

| Table ancienne | Contenu | Decision |
|---|---|---|
| `tbl_cont_pg` | **3 756 articles** existants (titre, contenu HTML, meta SEO, date, langue) | **A conserver** — c'est le contenu du site actuel |
| `tbl_ref` | **71 categories/references** hierarchiques (categories parentes et enfants) | **A conserver** — structure editoriale du site |
| `tbl_usr` | **3 utilisateurs** (admin, editeurs) | **A conserver** — utilisateurs existants |
| `logs` | Logs d'activite | Conserve pour compatibilite (vide) |
| `cloaked_ip` | IPs de bots | Conserve pour compatibilite (vide) |

J'ai redige un document complet (`SCHEMA_BASE_EXISTANTE.md`) qui explique :
- Chaque colonne de chaque table
- Le mapping entre l'ancien et le nouveau schema
- Comment les anciennes categories correspondent aux nouvelles

---

### Etape 14 — Import des donnees et audit complet du pipeline

**Objectif** : Copier les donnees utiles et verifier que tout fonctionne de bout en bout.

**Import des donnees** :
- Chargement du dump dans une base temporaire `vivat_old` dans Docker
- Copie selective : 3 756 articles, 71 references, 3 utilisateurs vers la base `vivat`
- Correction des incompatibilites (index trop longs pour utf8mb4, dates au format `0000-00-00`, doublons)

**Audit et correction de bugs** :
En testant le pipeline complet du debut a la fin, j'ai decouvert et corrige 9 bugs :

| Bug | Correction |
|---|---|
| Timestamps manquants sur les items RSS et enrichis | Ajout de `fetched_at` et `enriched_at` |
| Conflit entre les Jobs et le trait Laravel pour les queues | Remplacement de la propriete par `$this->onQueue()` |
| Encodage HTML incorrect lors du scraping | Ajout d'un pragma XML pour l'encodage UTF-8 |
| Erreur de regex sur les guillemets typographiques francais | Correction des patterns Unicode |
| Erreur 403 lors de la publication (au lieu d'un message clair) | Refonte de la Policy pour deleguer au controller |
| Erreur 500 si OpenAI echoue (stack trace brut) | Try/catch avec message d'erreur 502 descriptif |
| Dates mal castees dans les Resources JSON | Ajout du cast `datetime` sur les models |
| Contenu manquant dans la reponse de generation | Ajout de la route dans la condition de l'ArticleResource |
| Index MySQL trop long pour utf8mb4 | Utilisation de prefixes dans l'index |

**Test complet Postman** : tous les endpoints testes et fonctionnels.

**Nouveaux endpoints ajoutes** :
- `POST /api/pipeline/fetch-rss` — declencher le fetch RSS
- `POST /api/pipeline/enrich` — declencher l'enrichissement
- `GET /api/pipeline/select-items` — voir les propositions d'articles
- `GET /api/pipeline/status` — etat global du pipeline
- CRUD complet pour les `clusters` et `category-templates`

**Donnees de depart (seeder)** : j'ai cree un seeder automatique qui insere 14 categories, 6 sources medias, 5 flux RSS et 14 templates de generation.

---

### Etape 15 — Selection intelligente des articles et strategie SEO

**Objectif** : Repondre a la question "Pourquoi generer CET article et pas un autre ?"

Quand le pipeline recupere 120 articles depuis 15 sources, il faut choisir lesquels meritent d'etre generes. J'ai cree un algorithme de selection intelligent :

**Le scoring** (chaque article recoit un score sur 100) :

| Critere | Poids | Logique |
|---|---|---|
| **Potentiel SEO** | 30% | Les mots-cles longue traine (specifiques) valent plus que les mots generiques. "Recyclage textile France 2026" > "ecologie" |
| **Fraicheur** | 25% | Un article de moins de 48h a le score max. Apres 7 jours → score nul |
| **Qualite du contenu** | 25% | Le score donne par l'IA lors de l'enrichissement + bonus si le contenu est long et riche |
| **Diversite des sources** | 20% | Un sujet couvert par 3 medias differents vaut plus qu'un sujet d'une seule source (synthese multi-sources = plus de valeur) |

**Le regroupement par sujet** : les articles qui partagent plus de 20% de mots-cles sont regroupes. On genere UN article de synthese a partir de 3-4 sources plutot que de copier un seul article.

**L'explication** : chaque proposition d'article inclut un texte "reasoning" qui dit : "Cet article est propose parce que 3 sources couvrent le sujet, le potentiel SEO est eleve (score 78), et le sujet est frais (publie il y a 6h)."

**Amelioration SEO** : j'ai enrichi le prompt d'OpenAI pour qu'il extraie des mots-cles longue traine, un sujet principal, et un score SEO (0-100). Ces donnees sont stockees dans `enriched_items` et reutilisees lors de la generation.

**Documentation** : j'ai ecrit un guide complet de tests Postman (`TESTING_POSTMAN.md`) avec 37 endpoints documentes et 17 scenarios de test.

---

## PHASE 4 — Fonctionnalites du site (11 features)

### Etape 16 — Audit et correction de la stack technique

**Objectif** : Verifier que tout ce qu'on annonce dans la stack est bien installe et configure.

J'ai compare la stack annoncee avec ce qui existait reellement dans le code :

| Annonce | Realite avant correction | Action |
|---|---|---|
| Laravel Sanctum pour l'auth | Pas installe | Installe et configure |
| spatie/laravel-permission pour les roles | Pas installe | Installe et configure |
| spatie/laravel-sluggable pour les slugs SEO | Pas installe | Installe et configure |
| Stripe pour le paiement | Pas installe | Installe et configure |
| GPT-4.5 dans la doc | N'existe pas (c'est GPT-4o) | Corrige dans la doc |
| APP_NAME=Laravel | Mauvais nom | Corrige → APP_NAME=Vivat |
| APP_LOCALE=en | Mauvaise langue | Corrige → APP_LOCALE=fr |
| Table users avec ID numerique | Incoherent (tout le reste est UUID) | Migre vers UUID |

**Resultat** : la stack annoncee correspond maintenant a 100% au code reel.

---

### Etape 17 — Authentification, roles, API publique et personnalisation

**Objectif** : Implementer les fonctionnalites visibles cote site.

**Authentification (Sanctum)** :

| Endpoint | Ce qu'il fait |
|---|---|
| `POST /api/auth/register` | Creer un compte (attribue automatiquement le role "contributor") |
| `POST /api/auth/login` | Se connecter (retourne un token Bearer a envoyer dans chaque requete) |
| `POST /api/auth/logout` | Se deconnecter (revoque le token) |
| `GET /api/auth/me` | Voir son profil + ses roles |
| `PUT /api/auth/profile` | Modifier son nom, langue, centres d'interet, bio |

**Roles et permissions** :

| Role | Ce qu'il peut faire |
|---|---|
| **Admin** | Tout : pipeline, generation, moderation, stats, newsletter, paiements (21 permissions) |
| **Contributeur** | Voir les articles, soumettre des articles, voir ses soumissions (3 permissions) |
| **Visiteur** (non connecte) | Lire les articles, chercher, voir les categories, s'abonner a la newsletter |

**Protection des routes** : chaque route est protegee par le bon niveau d'acces. Un contributeur ne peut pas declencher le pipeline. Un visiteur ne peut pas moderer les soumissions.

**API publique** (accessible sans compte) :

| Endpoint | Ce qu'il fait |
|---|---|
| `GET /api/public/articles` | Liste des articles publies (pagination, tri, filtres) |
| `GET /api/public/articles/{slug}` | Un article par son slug (URL SEO) |
| `GET /api/public/categories` | Liste des categories avec le nombre d'articles |
| `GET /api/public/categories/{slug}/hub` | Page hub d'une categorie (a la une + recents) |
| `GET /api/public/search?q=climat` | Recherche par mot-cle, filtres categorie/date/duree |
| `GET /api/public/recommendations` | Articles recommandes selon les interets du visiteur |
| `POST /api/public/preferences` | Sauvegarder ses centres d'interet (cookie, sans compte) |
| `GET /api/public/preferences` | Recuperer ses preferences |
| `POST /api/public/reading-progress` | Sauvegarder sa progression de lecture |
| `GET /api/public/reading-progress` | Reprendre la ou on s'etait arrete |

**Logique d'affichage des articles** :

| Ou ? | Critere | Comment ca marche |
|---|---|---|
| **Homepage** | Les plus recents | Tries par `published_at` (date de publication) descendant |
| **A la une** (page hub) | Les meilleurs | Les 3 articles avec le **quality_score** le plus eleve dans la categorie |
| **Recommandations** | Personnalise | Algorithme : interets (40%) + qualite (25%) + fraicheur (20%) + popularite (15%), en excluant les articles deja lus |
| **Recherche** | Par mots-cles | Recherche dans le titre, le resume et la description SEO |

**Systeme de recommandation** : fonctionne meme sans compte. Le visiteur envoie un identifiant de session (cookie) et ses centres d'interet. Le systeme calcule un score pour chaque article et retourne les plus pertinents.

**Progression de lecture** : quand un visiteur lit un article, le frontend peut envoyer le pourcentage lu (ex: 65%). La prochaine fois, il peut reprendre la ou il s'etait arrete. Fonctionne avec un cookie (sans compte) ou avec un compte utilisateur.

---

### Etape 18 — Espace contributeur, newsletter et paiement Stripe

**Espace contributeur** :

| Endpoint | Ce qu'il fait |
|---|---|
| `POST /api/contributor/submissions` | Soumettre un article (brouillon ou directement en validation) |
| `GET /api/contributor/submissions` | Voir l'historique de ses soumissions |
| `PUT /api/contributor/submissions/{id}` | Modifier un brouillon ou un article rejete |
| `DELETE /api/contributor/submissions/{id}` | Supprimer un brouillon |

Le contributeur peut voir le statut de chaque article : `brouillon` → `en attente` → `approuve` / `rejete`.

**Moderation (admin)** :

| Endpoint | Ce qu'il fait |
|---|---|
| `GET /api/submissions` | Liste des soumissions a moderer (filtre par statut) |
| `GET /api/submissions/{id}` | Detail d'une soumission |
| `POST /api/submissions/{id}/approve` | Approuver (avec notes optionnelles) |
| `POST /api/submissions/{id}/reject` | Rejeter (notes obligatoires pour expliquer pourquoi) |

**Newsletter** :

| Endpoint | Ce qu'il fait |
|---|---|
| `POST /api/newsletter/subscribe` | S'abonner (email + minimum 3 centres d'interet) |
| `GET /api/newsletter/confirm?token=xxx` | Confirmer son abonnement par token |
| `POST /api/newsletter/unsubscribe` | Se desabonner par token |
| `GET /api/newsletter/subscribers` | (Admin) Liste des abonnes |

L'abonne recoit un email de confirmation avec un token unique. Il peut se desabonner a tout moment avec un autre token unique.

**Paiement Stripe** (publication ponctuelle d'article) :

Le flux complet :
1. Le contributeur soumet un article et veut le publier (payant)
2. Il appelle `POST /api/contributor/payments/create-intent` → le backend cree un PaymentIntent Stripe (15 EUR) et retourne un `client_secret`
3. Le frontend utilise Stripe.js pour afficher le formulaire de paiement et confirmer
4. Le contributeur appelle `POST /api/contributor/payments/confirm` → le backend verifie aupres de Stripe que le paiement a reussi, marque le paiement comme "paye", et soumet automatiquement l'article pour validation
5. L'admin approuve ou rejette l'article
6. Si rejete : l'admin peut rembourser via `POST /api/payments/{id}/refund` → le backend cree un remboursement Stripe

Le contributeur peut suivre ses paiements via `GET /api/contributor/payments` avec les statuts : `en attente` → `paye` → `rembourse` (si rejete).

---

### Etape 19 — Amelioration de la logique de selection (feedback mentor)

**Objectif** : Repondre au feedback du mentor pour ameliorer la logique de selection et rendre les choix de l'IA plus explicites et bases sur des regles predefinies.

**Problematiques identifiees** :
- Pas assez connecte a l'actu (actualite chaude)
- Manque d'elements de correlation (si 10 articles sur 50 traitent du meme sujet, ce sujet est plus important)
- Comment l'IA fait un choix ? (manque de transparence)
- Pas de distinction entre hot news (breve) et articles de fond (analyse longue)
- Besoin de tester les prompts pour etre sur que l'IA ressort ce qu'on veut
- Pour les tendances : besoin d'exporter en CSV (500-1000 articles) pour analyse data science / ChatGPT

**Solutions implementees** :

**1. Regles predefinies configurables** (`config/selection.php`) :

- **4 profils de ponderation** : `default`, `actu_focus` (plus connecte a l'actu), `seo_focus`, `long_form_focus` (articles de fond)
- **Poids configurables** : fraicheur, qualite, SEO, diversite, **topic_frequency** (nouveau critere)
- **Bonus "frequence du sujet"** : si sur N articles du pool, M traitent du meme sujet (ex: 10/50), ce sujet recoit un bonus de score → priorite tendance, correlation
- **Types d'article** : **hot_news** (400-650 mots, percutant, < 48h), **long_form** (1000-1800 mots, analytique, multi-sources), **standard** (800-1200 mots)

**2. Selection intelligente amelioree** (`ArticleSelectionService`) :

- Utilise les poids depuis la config (plus de constantes hardcodees)
- Calcule le bonus "frequence du sujet" : ratio = nombre d'items du groupe / taille totale du pool. Si ratio >= seuil (ex: 10%), bonus ajoute au score
- Retourne pour chaque proposition :
  - **reasoning** : explication detaillee (sources, qualite, SEO, fraicheur, **priorite sujet si applicable**)
  - **suggested_article_type** : hot_news | long_form | standard (determine automatiquement selon fraicheur et nombre de sources)
  - **suggested_min_words** / **suggested_max_words** : longueur cible pour l'IA
  - **context_priority** : phrase reutilisable dans le prompt (ex: "Sur 50 articles analyses, 10 portent sur ce sujet (tendance). Ce sujet est prioritaire.")

**3. Generation avec contexte** (`ArticleGeneratorService`) :

- Accepte maintenant : `articleType`, `minWords`, `maxWords`, `contextPriority`
- **System prompt adapte** : si `articleType = hot_news` → "Brève / actualité chaude. Style percutant, direct, factuel. 400-650 mots." Si `long_form` → "Article de fond. Approfondi, analytique. 1000-1800 mots."
- **User prompt enrichi** : injection de `contextPriority` pour que l'IA sache que ce sujet est prioritaire (ex: "Sur 50 articles, 10 sur ce sujet → priorite tendance")

**4. Export CSV pour analyse tendances** :

- **Commande** : `php artisan pipeline:export-trends-csv --limit=1000`
- Export CSV avec colonnes : date, title, category, source, primary_topic, seo_keywords, quality_score, seo_score, url, status
- **Usage** : nettoyer les donnees, puis utiliser avec ChatGPT / SVC / data science pour identifier ce qui ressort (titres, sujets, min/top, references) et ameliorer les prompts

**5. Documentation** :

- **`docs/LOGIQUE_SELECTION_ET_PROMPTS.md`** : regles predefinies, comment l'IA fait un choix, variantes de poids, tests de prompts, guide trends (export CSV + utilisation avec ChatGPT)

**Fichiers crees/modifies** :
- `config/selection.php` (nouveau) : profils, poids, topic_frequency, article_types
- `app/Services/ArticleSelectionService.php` : poids configurables, bonus frequence sujet, suggested_article_type, context_priority
- `app/Services/ArticleGeneratorService.php` : parametres articleType/minWords/maxWords/contextPriority, prompts adaptes
- `app/Http/Requests/GenerateArticleRequest.php` : nouveaux champs optionnels
- `app/Console/Commands/ExportTrendsCsvCommand.php` (nouveau) : export CSV pour trends
- `.env.example` : `SELECTION_WEIGHT_PROFILE=default`

**Resultat** : La logique de selection est maintenant basee sur des **regles predefinies** configurables, **connectee a l'actu** (fraicheur + frequence sujet), avec **correlation** (bonus si beaucoup d'articles sur le meme sujet), **transparente** (reasoning + context_priority), et **adaptative** (hot_news vs article de fond selon le contexte). L'export CSV permet d'analyser de gros volumes (500-1000+ articles) avec ChatGPT / data science pour affiner encore les prompts.

---

## Documentation produite

### Stack technique documentee

En plus du code, j'ai produit un document de stack technique complet (`docs/STACK_TECHNIQUE.md`) qui couvre :
- L'architecture logicielle (pourquoi Controllers fins + Services + Jobs)
- Le detail du pipeline en 5 etapes avec les fichiers concernes
- Le schema complet des 36 tables
- L'authentification, les roles, la securite
- L'integration IA (OpenAI GPT-4o) avec les prompts
- Le cache et la performance (Redis)
- L'infrastructure Docker
- L'inventaire complet du code

Ce document est concu pour etre presente au chef de projet ou a un nouveau developpeur.

### Schema de base de donnees

Un document detaille (`docs/SCHEMA_COMPLET_TABLES.md`) qui decrit chaque table colonne par colonne, avec les types, les relations, les index, et surtout **la logique d'affichage des articles** (homepage, a la une, recommandations, recherche).

---

## Chiffres cles du projet

| Element | Nombre |
|---|---|
| **Routes API** | 94 |
| **Tables en base de donnees** | 36 |
| **Models Eloquent** | 17 |
| **Controllers API** | 16 |
| **Services metier** | 5 |
| **Jobs asynchrones** | 3 |
| **Migrations de base de donnees** | 24 |
| **Roles** | 2 (admin, contributor) |
| **Permissions** | 21 |
| **Donnees legacy importees** | 3 830 enregistrements |
| **Tests automatises** | 12 tests, 39 assertions |
| **Documents de reference** | 10 |
| **Commandes Artisan** | 5 (rss:fetch, content:enrich, cleanup:old, articles:generate, pipeline:export-trends-csv) |

---

## Livrables

| Document | Description |
|---|---|
| `docs/STACK_TECHNIQUE.md` | Stack technique complete |
| `docs/SCHEMA_COMPLET_TABLES.md` | Schema detaille des 36 tables + logique d'affichage |
| `docs/TESTING_POSTMAN.md` | Guide de test Postman (94 endpoints) |
| `docs/SCHEMA_BASE_EXISTANTE.md` | Analyse de l'ancienne base de donnees |
| `docs/JOURNAL_AVANCEMENT.md` | Journal detaille de chaque etape |
| `docs/DOCKER.md` | Guide d'installation Docker |
| `docs/RESUME_AVANCEMENT.md` | Resume compact pour le chef de projet |
| `docs/RAPPORT_COMPLET.md` | Ce document |
| `docs/LOGIQUE_SELECTION_ET_PROMPTS.md` | Regles predefinies, logique de selection, tests prompts, guide trends |
| `docs/AUDIT_PERFORMANCE.md` | Audit performance (score 7/10 → 8,5/10) avec optimisations |

---

## Ce qui reste a faire

| Priorite | Tache | Effort estime |
|---|---|---|
| **Haute** | Crediter la cle OpenAI (l'enrichissement IA et la generation sont bloques par le quota) | Configuration |
| **Haute** | Deploiement sur un VPS (DigitalOcean/Hetzner) avec Nginx + SSL | 1-2 jours |
| **Moyenne** | Integration frontend (le frontend consomme les endpoints `/api/public/*`) | Selon le frontend |
| **Moyenne** | Configuration d'un vrai service email (Resend/Mailgun) pour la newsletter | 0.5 jour |
| **Moyenne** | Installer Sentry pour le monitoring d'erreurs en production | 0.5 jour |
| **Basse** | Sitemap XML dynamique (`spatie/laravel-sitemap`) | 0.5 jour |
| **Basse** | Support multi-langues FR/NL (fichiers de traduction Laravel) | 1 jour |
| **Basse** | Laravel Telescope pour le debug en developpement | 0.5 jour |

---

*Rapport complet — Achraf Ben Ali — 17 fevrier 2026*
