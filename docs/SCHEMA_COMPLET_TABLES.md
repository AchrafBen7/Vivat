# Vivat Schema complet de la base de donnees

> **36 tables** | **17 models Eloquent** | **UUID partout** | MySQL 8.0
> Derniere mise a jour : 9 fevrier 2026

---

## Table des matieres

1. [Logique d'affichage des articles](#logique-daffichage-des-articles)
2. [Tables du pipeline (11 tables)](#tables-du-pipeline)
3. [Tables utilisateurs et auth (7 tables)](#tables-utilisateurs-et-auth)
4. [Tables du site public (5 tables)](#tables-du-site-public)
5. [Tables legacy importees (5 tables)](#tables-legacy)
6. [Tables framework Laravel (6 tables)](#tables-framework)
7. [Diagramme des relations](#diagramme-des-relations)

---

## Logique d'affichage des articles

### Comment un article apparait-il sur le site ?

Chaque article passe par un **cycle de vie** avant d'etre visible :

```
[Pipeline IA] → draft → review → published ← seul statut visible sur le site
                                     |
                          condition : quality_score >= 60
```

**Un article est "publie"** quand :
- `status = 'published'`
- `published_at IS NOT NULL`
- `quality_score >= 60` (condition verifiee AVANT la publication)

Tous les endpoints publics utilisent le scope `Article::published()` qui filtre automatiquement sur ces criteres.

---

### Homepage Liste des articles

**Endpoint** : `GET /api/public/articles`
**Logique** : Tous les articles publies, tries par date de publication (les plus recents d'abord).

| Critere | Valeur |
|---|---|
| Filtre obligatoire | `status = published` ET `published_at IS NOT NULL` |
| Tri par defaut | `published_at DESC` (les plus recents) |
| Tris possibles | `published_at`, `reading_time`, `quality_score`, `title` |
| Filtres optionnels | Par categorie (slug), par duree de lecture max |
| Pagination | 12 articles/page (max 50) |

**En resume** : l'article le plus recent apparait en premier. Le frontend peut trier autrement (par qualite, par duree de lecture, etc.).

---

### A la une Articles mis en avant

**Endpoint** : `GET /api/public/categories/{slug}/hub`
**Logique** : Pour chaque categorie, les 3 articles avec le **meilleur `quality_score`** sont "a la une".

| Element | Logique |
|---|---|
| **Featured (a la une)** | `status = published` + `category_id = X` + **tri par `quality_score DESC`** + **limit 3** |
| **Recents** | `status = published` + `category_id = X` + tri par `published_at DESC` + limit 10 |
| **Total** | Comptage de tous les articles publies de la categorie |

**Ce qui determine qu'un article est "a la une"** :
- Il est **publie** (status = published)
- Il appartient a la **categorie de la page hub**
- Il a le **quality_score le plus eleve** parmi les articles de cette categorie
- Le `quality_score` (0-100) est calcule par l'IA lors de la generation : il mesure la qualite redactionnelle, la coherence, la richesse du contenu et la conformite SEO

```
Exemple :
  Article A : quality_score = 92 → A la une (1er)
  Article B : quality_score = 87 → A la une (2eme)
  Article C : quality_score = 85 → A la une (3eme)
  Article D : quality_score = 78 → Recents (pas a la une)
```

---

### Recommandations personnalisees

**Endpoint** : `GET /api/public/recommendations?interests=environnement,sante&session_id=xxx`
**Logique** : Algorithme de scoring multi-criteres dans `RecommendationService`.

| Critere | Poids | Explication |
|---|---|---|
| **Centres d'interet** | 40% | L'article correspond aux categories choisies par le visiteur ? +40 points |
| **Qualite de l'article** | 25% | `quality_score` normalise de 0 a 25 (un article a 80/100 → 20 points) |
| **Fraicheur** | 20% | Article publie il y a 0 jours → 20 pts, 15 jours → 10 pts, 30+ jours → 0 pts |
| **Popularite** | 15% | Approx. par `reading_time` comme indicateur d'engagement (max 15 pts) |
| **Penalite** | Exclusion | Articles deja lus a plus de 50% sont **exclus** (via `reading_histories`) |

**Fonctionnement sans compte** : Le visiteur envoie un `session_id` (cookie) et ses `interests` (categorie slugs). Le systeme ne requiert pas d'inscription.

**Fonctionnement avec compte** : Le `user_id` du token Sanctum est utilise. Les interets viennent du profil utilisateur.

```
Exemple de scoring :
  Article "Transition energetique" dans categorie "Energie"
    Visiteur interesse par "energie" → +40 (interet match)
    quality_score = 88              → +22 (qualite)
    Publie il y a 2 jours           → +18.7 (frais)
    reading_time = 7 min            → +10.5 (popularite)
    TOTAL = 91.2 points → Tres bien classe

  Article "Recette zero dechet" dans categorie "Mode de vie"
    Visiteur PAS interesse par "mode-de-vie" → +0
    quality_score = 72               → +18
    Publie il y a 20 jours           → +6.7
    reading_time = 4 min             → +6
    TOTAL = 30.7 points → Classe plus bas
```

---

### Recherche

**Endpoint** : `GET /api/public/search?q=climat&category=environnement&date_from=2026-01-01`

| Filtre | Champ(s) cherche(s) |
|---|---|
| `q` (mot-cle) | `title LIKE %q%` OU `excerpt LIKE %q%` OU `meta_description LIKE %q%` |
| `category` | Via slug de la categorie |
| `date_from` / `date_to` | Sur `published_at` |
| `reading_time_max` | Sur `reading_time` |

Toujours limite aux articles **publies** uniquement.

---

## Tables du pipeline

### 1. sources Medias configures

> Model : `Source` | 6 enregistrements

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `name` | string | Non | Nom du media (ex: "Reporterre", "Futura Sciences") |
| `base_url` | string | Non | URL du site (ex: "https://reporterre.net") |
| `language` | string(10) | Non | Langue (defaut: "fr") |
| `is_active` | boolean | Non | Source active ? (defaut: true) |
| `created_at` | timestamp | Oui | Date de creation |
| `updated_at` | timestamp | Oui | Derniere modification (trigger MySQL) |

**Index** : `is_active`, `language`
**Relations** : → rss_feeds (hasMany), → article_sources (hasMany)

---

### 2. categories Categories thematiques

> Model : `Category` | 14 enregistrements

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `name` | string | Non | Nom (ex: "Environnement", "Sante") |
| `slug` | string (unique) | Non | Slug SEO (ex: "environnement") |
| `description` | text | Oui | Description de la categorie |
| `color` | string(7) | Non | Couleur hex (defaut: "#3B82F6") |
| `created_at` | timestamp | Oui | Date de creation |

**Relations** : → rss_feeds, rss_items, clusters, articles (hasMany), → template (hasOne)

---

### 3. rss_feeds Flux RSS configures

> Model : `RssFeed` | 5 enregistrements

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `source_id` | UUID (FK → sources) | Oui | Media source |
| `category_id` | UUID (FK → categories) | Oui | Categorie du flux |
| `feed_url` | text | Non | URL du flux RSS/Atom |
| `is_active` | boolean | Non | Flux actif ? (defaut: true) |
| `last_fetched_at` | timestamp | Oui | Derniere recuperation |
| `fetch_interval_minutes` | smallint | Non | Intervalle de fetch (defaut: 30 min) |
| `created_at` | timestamp | Oui | Date de creation |

**Index** : `is_active`, `last_fetched_at`, composite `(is_active, last_fetched_at)`
**Scope** : `dueForFetch()` flux actifs dont le dernier fetch depasse l'intervalle

---

### 4. rss_items Articles bruts decouverts

> Model : `RssItem` | ~120 enregistrements

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `rss_feed_id` | UUID (FK → rss_feeds) | Oui | Flux d'origine |
| `category_id` | UUID (FK → categories) | Oui | Categorie |
| `guid` | string | Oui | GUID du flux RSS |
| `title` | string | Non | Titre de l'article source |
| `description` | text | Oui | Description/resume du flux |
| `url` | text | Non | URL de l'article original |
| `published_at` | timestamp | Oui | Date de publication dans le flux |
| `fetched_at` | timestamp | Oui | Date de recuperation |
| `status` | enum | Non | `new` → `enriching` → `enriched` / `failed` / `ignored` / `used` |
| `dedup_hash` | string(64) unique | Oui | Hash SHA-256 (url + title) pour deduplication |
| `created_at` | timestamp | Oui | Date de creation |

**Index** : `dedup_hash` (unique), `status`, `published_at`, `fetched_at`, fulltext `(title, description)`

---

### 5. enriched_items Contenu structure par l'IA

> Model : `EnrichedItem` | Relation 1:1 avec rss_items

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `rss_item_id` | UUID (FK → rss_items, unique) | Oui | Item source (1:1) |
| `lead` | text | Oui | Resume 1-2 phrases genere par l'IA |
| `headings` | JSON | Oui | Titres H2/H3 extraits |
| `key_points` | JSON | Oui | 3-7 points cles |
| `seo_keywords` | JSON | Oui | 5-10 mots-cles SEO longue traine |
| `primary_topic` | string(255) | Oui | Sujet principal en 2-4 mots |
| `extracted_text` | longText | Oui | Texte brut extrait de la page |
| `extraction_method` | string(50) | Non | Methode (defaut: "readability") |
| `quality_score` | tinyint (0-100) | Non | Score qualite contenu (defaut: 0) |
| `seo_score` | tinyint (0-100) | Non | Score potentiel SEO (defaut: 0) |
| `enriched_at` | timestamp | Oui | Date d'enrichissement |

---

### 6. clusters Groupes thematiques

> Model : `Cluster`

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `category_id` | UUID (FK → categories) | Oui | Categorie du cluster |
| `label` | string | Non | Nom du groupe thematique |
| `keywords` | JSON | Oui | Mots-cles du cluster |
| `status` | enum | Non | `pending` → `processing` → `generated` / `failed` |
| `created_at` | timestamp | Oui | Date de creation |

---

### 7. cluster_items Pivot cluster ↔ rss_item

> Model : `ClusterItem`

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `cluster_id` | UUID (FK → clusters) | Oui | Cluster |
| `rss_item_id` | UUID (FK → rss_items) | Oui | Item RSS |

**Contrainte** : unique `(cluster_id, rss_item_id)`

---

### 8. articles Articles generes par l'IA

> Model : `Article` | **Table centrale du site**

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `title` | string | Non | Titre de l'article |
| `slug` | string (unique) | Non | Slug SEO (URL) |
| `excerpt` | text | Oui | Chapeau / resume court |
| `content` | longText | Non | Contenu HTML complet |
| `meta_title` | string | Oui | Titre SEO (50-60 chars) |
| `meta_description` | string | Oui | Description SEO (150-160 chars) |
| `keywords` | JSON | Oui | Mots-cles SEO |
| `category_id` | UUID (FK → categories) | Oui | Categorie |
| `cluster_id` | UUID (FK → clusters) | Oui | Cluster source |
| `reading_time` | smallint | Non | Temps de lecture en minutes (defaut: 5) |
| `status` | enum | Non | `draft` → `review` → `published` / `archived` / `rejected` |
| `quality_score` | tinyint (0-100) | Non | **Score qualite** determine le classement "a la une" |
| `published_at` | timestamp | Oui | Date de publication |
| `created_at` | timestamp | Oui | Date de creation |
| `updated_at` | timestamp | Oui | Derniere modification (trigger) |

**Index** : `status`, `published_at`, `quality_score`, `(status, published_at)`, `(category_id, status)`, fulltext `(title, excerpt)`

**Champs cles pour l'affichage** :
- `status = 'published'` + `published_at` → article visible sur le site
- `quality_score` → determine le classement "a la une" dans les pages hub
- `published_at` → determine l'ordre "recents" sur la homepage
- `category_id` → determine dans quelle page hub l'article apparait
- `reading_time` → affiche "5 min de lecture" sur chaque article
- `keywords` → utilise pour la recherche et le SEO

---

### 9. article_sources Tracabilite article → sources

> Model : `ArticleSource`

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `article_id` | UUID (FK → articles) | Oui | Article genere |
| `rss_item_id` | UUID (FK → rss_items) | Oui | Item RSS source |
| `source_id` | UUID (FK → sources) | Oui | Media source |
| `url` | text | Non | URL de la source originale |
| `used_at` | timestamp | Oui | Date d'utilisation |

**Contrainte** : unique `(article_id, rss_item_id)` un item ne peut etre utilise qu'une fois par article.

---

### 10. category_templates Config de generation par categorie

> Model : `CategoryTemplate` | 14 enregistrements (1 par categorie)

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `category_id` | UUID (FK → categories, unique) | Oui | Categorie (1:1) |
| `tone` | string(50) | Non | Ton de l'article (defaut: "professional") |
| `structure` | string(50) | Non | Structure (defaut: "standard") |
| `min_word_count` | smallint | Non | Minimum de mots (defaut: 900) |
| `max_word_count` | smallint | Non | Maximum de mots (defaut: 2000) |
| `style_notes` | text | Oui | Notes de style pour le prompt IA |
| `seo_rules` | text | Oui | Regles SEO pour le prompt IA |
| `created_at` | timestamp | Oui | Date de creation |

---

### 11. pipeline_jobs Monitoring des jobs

> Model : `PipelineJob`

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `job_type` | enum | Non | `fetch_rss`, `enrich`, `cluster`, `generate`, `publish`, `cleanup` |
| `status` | enum | Non | `pending` → `running` → `completed` / `failed` |
| `started_at` | timestamp | Oui | Debut d'execution |
| `completed_at` | timestamp | Oui | Fin d'execution |
| `error_message` | text | Oui | Message d'erreur si echec |
| `metadata` | JSON | Oui | Donnees contextuelles |
| `retry_count` | tinyint | Non | Nombre de tentatives (defaut: 0) |
| `created_at` | timestamp | Oui | Date de creation |

---

## Tables utilisateurs et auth

### 12. users Utilisateurs

> Model : `User` | Traits : HasUuids, HasApiTokens (Sanctum), HasRoles (spatie)

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `name` | string | Non | Nom complet |
| `email` | string (unique) | Non | Email |
| `email_verified_at` | timestamp | Oui | Date de verification email |
| `password` | string | Non | Mot de passe (hash bcrypt) |
| `language` | enum (fr, nl) | Non | Langue preferee (defaut: fr) |
| `interests` | JSON | Oui | Categories preferees (slugs) |
| `avatar` | string | Oui | URL de l'avatar |
| `bio` | text | Oui | Biographie (pour contributeurs) |
| `remember_token` | string | Oui | Token "se souvenir de moi" |
| `created_at` | timestamp | Oui | Date de creation |
| `updated_at` | timestamp | Oui | Derniere modification |

**Roles** : `admin` (21 permissions), `contributor` (3 permissions)

---

### 13. personal_access_tokens Tokens API Sanctum

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint (PK) | ID auto-increment |
| `tokenable_type` | string | Type du model (App\Models\User) |
| `tokenable_id` | UUID | ID de l'utilisateur |
| `name` | text | Nom du token (ex: "api") |
| `token` | string(64) unique | Hash du token |
| `abilities` | text | Permissions du token |
| `last_used_at` | timestamp | Derniere utilisation |
| `expires_at` | timestamp | Date d'expiration |

---

### 14-18. Tables spatie/permission (5 tables)

| Table | Role |
|---|---|
| `roles` | Roles : admin, contributor |
| `permissions` | 21 permissions (articles.view, pipeline.fetch-rss, etc.) |
| `model_has_roles` | Pivot user ↔ role |
| `model_has_permissions` | Pivot user ↔ permission directe |
| `role_has_permissions` | Pivot role ↔ permissions |

---

## Tables du site public

### 19. submissions Articles soumis par les contributeurs

> Model : `Submission` | Traits : HasUuids, HasSlug (spatie)

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `user_id` | UUID (FK → users) | Non | Auteur |
| `title` | string | Non | Titre de l'article |
| `slug` | string (unique) | Non | Slug auto-genere depuis le titre |
| `excerpt` | text | Oui | Resume |
| `content` | longText | Non | Contenu complet |
| `category_id` | UUID (FK → categories) | Oui | Categorie |
| `status` | enum | Non | `draft` → `pending` → `approved` / `rejected` |
| `reviewer_notes` | text | Oui | Notes du moderateur |
| `reviewed_by` | UUID (FK → users) | Oui | Moderateur qui a valide/rejete |
| `reviewed_at` | timestamp | Oui | Date de moderation |
| `payment_id` | string | Oui | Reference au paiement |
| `created_at` | timestamp | Oui | Date de creation |
| `updated_at` | timestamp | Oui | Derniere modification |

---

### 20. payments Paiements Stripe

> Model : `Payment`

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `user_id` | UUID (FK → users) | Non | Payeur |
| `submission_id` | UUID (FK → submissions) | Oui | Article paye |
| `stripe_payment_intent_id` | string (unique) | Non | ID Stripe |
| `amount` | unsigned int | Non | Montant en centimes (ex: 1500 = 15.00 EUR) |
| `currency` | string(3) | Non | Devise (defaut: "eur") |
| `status` | enum | Non | `pending` → `paid` → `refunded` / `failed` |
| `refund_reason` | string | Oui | Raison du remboursement |
| `stripe_refund_id` | string | Oui | ID du remboursement Stripe |
| `created_at` | timestamp | Oui | Date de creation |
| `updated_at` | timestamp | Oui | Derniere modification |

---

### 21. newsletter_subscribers Abonnes newsletter

> Model : `NewsletterSubscriber`

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `email` | string (unique) | Non | Email |
| `name` | string | Oui | Nom |
| `interests` | JSON | Non | Min. 3 categories choisies |
| `confirmed` | boolean | Non | Email confirme ? (defaut: false) |
| `unsubscribe_token` | string(64) unique | Non | Token de desabonnement |
| `confirm_token` | string(64) unique | Oui | Token de confirmation (null apres confirmation) |
| `confirmed_at` | timestamp | Oui | Date de confirmation |
| `unsubscribed_at` | timestamp | Oui | Date de desabonnement |
| `created_at` | timestamp | Oui | Date de creation |
| `updated_at` | timestamp | Oui | Derniere modification |

---

### 22. reading_histories Progression de lecture

> Model : `ReadingHistory`

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `user_id` | UUID (FK → users) | Oui | Utilisateur connecte (null si cookie) |
| `session_id` | string(100) | Oui | ID de session cookie (si pas connecte) |
| `article_id` | UUID (FK → articles) | Non | Article lu |
| `progress` | tinyint (0-100) | Non | Pourcentage lu (defaut: 0) |
| `read_at` | timestamp | Non | Date de lecture |
| `created_at` | timestamp | Oui | Date de creation |
| `updated_at` | timestamp | Oui | Derniere modification |

**Utilise par** : `RecommendationService` (exclut les articles lus a >50%), endpoint "reprendre la lecture".

---

### 23. user_preferences Preferences visiteurs non connectes

> Model : `UserPreference`

| Colonne | Type | Nullable | Description |
|---|---|---|---|
| `id` | UUID (PK) | Non | Identifiant unique |
| `session_id` | string(100) unique | Non | ID de session cookie |
| `interests` | JSON | Oui | Categories preferees (slugs) |
| `language` | enum (fr, nl) | Non | Langue (defaut: fr) |
| `created_at` | timestamp | Oui | Date de creation |
| `updated_at` | timestamp | Oui | Derniere modification |

---

## Tables legacy

### 24. tbl_cont_pg 3 756 articles importes de l'ancien site

| Colonne | Type | Description |
|---|---|---|
| `contID` | int (PK, auto) | ID article |
| `contTitle` | varchar(250) | Titre |
| `contDesc` | text | Description |
| `contContent` | longText | Contenu HTML |
| `contKeywords` | text | Mots-cles |
| `contImgs` | varchar(255) | Chemin images |
| `contImgsAlt` | text | Alt text images |
| `contLang` | char(10) | Langue |
| `contRef1/2/3` | varchar(200) | References (categories) |
| `online` | int | En ligne (0/1) |
| `contDate` | date | Date article |
| `contPgs` | int | Type de page |
| `meta_title` | varchar(255) | SEO titre |
| `meta_desc` | varchar(255) | SEO description |
| `creation` | datetime | Date creation |
| `modification` | datetime | Derniere modification |
| `contPublishDate` | int | Timestamp publication |

### 25. tbl_ref 71 categories/references legacy

| Colonne | Type | Description |
|---|---|---|
| `id` | int (PK, auto) | ID |
| `refID` | int | Parent (0 = racine) |
| `refTitle` | varchar(255) | Libelle |
| `refLang` | varchar(2) | Langue |
| `refUrl` | varchar(255) | URL / slug |
| `meta_title/desc/kw` | varchar(255) | SEO |

### 26. tbl_usr 3 utilisateurs legacy

| Colonne | Type | Description |
|---|---|---|
| `usrID` | int (PK, auto) | ID |
| `usrNickName` | varchar(255) | Login |
| `usrPw` | varchar(255) | Mot de passe (hash) |
| `usrRealLastName/FirstName` | varchar(255) | Nom/prenom |
| `usrEmail` | varchar(255) | Email |
| `usrType` | tinyint | Role (1=admin, 2=editeur) |

### 27-28. logs + cloaked_ip Tables techniques legacy (vides)

Conservees pour compatibilite avec le dump. Non essentielles.

---

## Tables framework

| Table | Role |
|---|---|
| `cache` | Cache Laravel (cle/valeur) |
| `cache_locks` | Verrous de cache |
| `jobs` | Queue Redis (jobs en attente) |
| `job_batches` | Batches de jobs |
| `failed_jobs` | Jobs echoues (pour debug) |
| `migrations` | Suivi des migrations executees |
| `password_reset_tokens` | Tokens de reset mot de passe |
| `sessions` | Sessions utilisateur |

---

## Diagramme des relations

```
sources ─────────┐
                  ├──→ rss_feeds ──→ rss_items ──→ enriched_items
categories ──────┤                        |
                  |                        ├──→ cluster_items ──→ clusters
                  |                        |
                  |                        └──→ article_sources ──→ articles
                  |                                                    |
                  └──→ category_templates                              |
                                                                       |
users ──→ submissions ──→ payments                                     |
  |                                                                    |
  └──→ reading_histories ←────────────────────────────────────────────┘
  |
  └──→ user_preferences
  |
  └──→ personal_access_tokens
  |
  └──→ model_has_roles ──→ roles ──→ role_has_permissions ──→ permissions

newsletter_subscribers (independant)
```

---

*Document complet 36 tables, 17 models, logique d'affichage detaillee.*
