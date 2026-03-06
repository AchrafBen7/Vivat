# Guide de Tests Postman — Vivat Content Acquisition Engine

> **Base URL** : `http://localhost:8000/api`
> **Headers requis** : `Accept: application/json` + `Content-Type: application/json` (pour POST/PUT)

---

## Table des matières

1. [Pipeline complet (end-to-end)](#1-pipeline-complet-end-to-end)
2. [Sources CRUD](#2-sources-crud)
3. [Catégories](#3-catégories)
4. [Category Templates CRUD](#4-category-templates-crud)
5. [RSS Feeds CRUD](#5-rss-feeds-crud)
6. [Pipeline : Ingestion RSS](#6-pipeline--ingestion-rss)
7. [RSS Items](#7-rss-items)
8. [Pipeline : Enrichissement](#8-pipeline--enrichissement)
9. [Pipeline : Sélection intelligente](#9-pipeline--sélection-intelligente)
10. [Clusters CRUD](#10-clusters-crud)
11. [Articles : Génération IA](#11-articles--génération-ia)
12. [Articles CRUD](#12-articles-crud)
13. [Articles : Publication](#13-articles--publication)
14. [Stats Dashboard](#14-stats-dashboard)
15. [Pipeline Status](#15-pipeline-status)
16. [Scénarios d'erreur](#16-scénarios-derreur)
17. [Workflow complet : du RSS à l'article publié](#17-workflow-complet--du-rss-à-larticle-publié)
18. [Workflow étape par étape pour le mentor (endpoints uniquement)](#18-workflow-étape-par-étape-pour-le-mentor-endpoints-uniquement)

---

## 1. Pipeline complet (end-to-end)

Le pipeline fonctionne en 5 étapes séquentielles :

```
Sources → RSS Feeds → Fetch RSS → Enrich → Select → Generate → Publish
```

Pour tester le pipeline complet de zéro, suivre les étapes 2 à 13 dans l'ordre.

---

## 2. Sources CRUD

### 2.1 Lister toutes les sources

```
GET /api/sources
```

**Réponse attendue** : `200` — tableau de sources avec `id`, `name`, `base_url`, `language`, `is_active`.

### 2.2 Créer une source

```
POST /api/sources

{
  "name": "Le Monde Planète",
  "base_url": "https://www.lemonde.fr/planete",
  "language": "fr",
  "is_active": true
}
```

**Réponse attendue** : `201` — la source créée avec un UUID.

### 2.3 Voir une source

```
GET /api/sources/{source_id}
```

**Réponse attendue** : `200` — détails de la source.

### 2.4 Modifier une source

```
PUT /api/sources/{source_id}

{
  "name": "Le Monde - Planète",
  "is_active": false
}
```

**Réponse attendue** : `200` — source mise à jour.

### 2.5 Supprimer une source

```
DELETE /api/sources/{source_id}
```

**Réponse attendue** : `204` — pas de contenu.

---

## 3. Catégories

### 3.1 Lister toutes les catégories

```
GET /api/categories
```

**Réponse attendue** : `200` — 14 catégories (Environnement, Santé, Économie, Énergie, Alimentation, Technologie, Société, Transport, Habitat, Biodiversité, Politique, Sciences, Mode de vie, International).

### 3.2 Voir une catégorie

```
GET /api/categories/{category_id}
```

**Réponse attendue** : `200` — détail avec `name`, `slug`, `description`.

---

## 4. Category Templates CRUD

Les templates définissent le ton, la structure et les règles SEO par catégorie.

### 4.1 Lister les templates

```
GET /api/category-templates
```

**Réponse attendue** : `200` — 14 templates (un par catégorie), chacun avec `tone`, `structure`, `min_word_count`, `max_word_count`, `style_notes`, `seo_rules`.

### 4.2 Créer un template

```
POST /api/category-templates

{
  "category_id": "{category_uuid}",
  "tone": "engageant et pédagogique",
  "structure": "listicle",
  "min_word_count": 600,
  "max_word_count": 1200,
  "style_notes": "Format liste avec des titres numérotés. Ton accessible.",
  "seo_rules": "Inclure 'top' ou 'meilleurs' dans le titre. 3 H2 minimum."
}
```

**Réponse attendue** : `201`.

### 4.3 Modifier un template

```
PUT /api/category-templates/{template_id}

{
  "tone": "expert et scientifique",
  "max_word_count": 2500
}
```

**Réponse attendue** : `200`.

### 4.4 Supprimer un template

```
DELETE /api/category-templates/{template_id}
```

**Réponse attendue** : `204`.

---

## 5. RSS Feeds CRUD

### 5.1 Lister les flux RSS

```
GET /api/rss-feeds
```

**Réponse attendue** : `200` — flux avec `source`, `category`, `feed_url`, `is_active`, `last_fetched_at`.

### 5.2 Lister uniquement les flux actifs

```
GET /api/rss-feeds?active=1
```

### 5.3 Créer un flux RSS

```
POST /api/rss-feeds

{
  "source_id": "{source_uuid}",
  "category_id": "{category_uuid}",
  "feed_url": "https://www.lemonde.fr/planete/rss_full.xml",
  "is_active": true,
  "fetch_interval_minutes": 60
}
```

**Réponse attendue** : `201` — feed créé avec les relations `source` et `category`.

### 5.4 Modifier un flux

```
PUT /api/rss-feeds/{feed_id}

{
  "is_active": false,
  "fetch_interval_minutes": 120
}
```

### 5.5 Supprimer un flux

```
DELETE /api/rss-feeds/{feed_id}
```

**Réponse attendue** : `204`.

---

## 6. Pipeline : Ingestion RSS

### 6.1 Déclencher le fetch de TOUS les flux actifs

```
POST /api/pipeline/fetch-rss

{
  "all": true
}
```

**Réponse attendue** : `200`
```json
{
  "message": "5 job(s) FetchRssFeedJob dispatché(s).",
  "count": 5,
  "mode": "all_active"
}
```

**Important** : Les jobs sont traités en arrière-plan. Il faut que le queue worker tourne :
```bash
docker compose exec app php artisan queue:work redis --queue=rss,enrichment,generation,default
```

### 6.2 Déclencher le fetch d'un seul flux

```
POST /api/pipeline/fetch-rss

{
  "feed_id": "{feed_uuid}"
}
```

### 6.3 Déclencher uniquement les flux "dus" (intervalle dépassé)

```
POST /api/pipeline/fetch-rss
```

(sans body = mode `due_only` par défaut)

### 6.4 Vérification après fetch

```
GET /api/pipeline/status
```

**Vérifier** : `rss_items_by_status.new` > 0 (items récupérés).

---

## 7. RSS Items

### 7.1 Lister les items (paginé)

```
GET /api/rss-items?per_page=10
```

**Réponse attendue** : `200` — items avec `title`, `url`, `status`, `rss_feed`, `category`, `enriched_item`.

### 7.2 Filtrer par statut

```
GET /api/rss-items?status=new
GET /api/rss-items?status=enriched
GET /api/rss-items?status=used
GET /api/rss-items?status=failed
```

### 7.3 Filtrer par catégorie

```
GET /api/rss-items?category_id={category_uuid}
```

### 7.4 Filtrer par flux RSS

```
GET /api/rss-items?rss_feed_id={feed_uuid}
```

### 7.5 Combinaison de filtres

```
GET /api/rss-items?status=enriched&category_id={uuid}&per_page=5
```

### 7.6 Voir un item avec son enrichissement

```
GET /api/rss-items/{item_id}
```

**Réponse attendue** : `200` — inclut `enriched_item` avec `lead`, `headings`, `key_points`, `seo_keywords`, `quality_score`, `seo_score`.

---

## 8. Pipeline : Enrichissement

### 8.1 Déclencher l'enrichissement

```
POST /api/pipeline/enrich

{
  "limit": 10,
  "delay": 2
}
```

- `limit` : nombre max d'items à enrichir (défaut: 50, max: 200)
- `delay` : secondes entre chaque job (défaut: 3, pour éviter le rate-limit OpenAI)

**Réponse attendue** : `200`
```json
{
  "message": "10 job(s) EnrichContentJob dispatché(s).",
  "count": 10
}
```

**Prérequis** : `OPENAI_API_KEY` valide dans `.env` + queue worker actif.

### 8.2 Vérifier l'avancement

```
GET /api/pipeline/status
```

**Vérifier** : `rss_items_by_status.enriched` augmente au fur et à mesure.

### 8.3 Voir un item enrichi

```
GET /api/rss-items/{item_id}
```

**Vérifier** :
- `enriched_item.lead` contient un résumé
- `enriched_item.key_points` contient 3-7 points clés
- `enriched_item.seo_keywords` contient 5-10 mots-clés SEO
- `enriched_item.primary_topic` contient le sujet principal
- `enriched_item.quality_score` entre 0-100
- `enriched_item.seo_score` entre 0-100

---

## 9. Pipeline : Sélection intelligente

> **C'est ici qu'on répond à : "Pourquoi générer CET article et pas un autre ?"**

### 9.1 Demander les propositions d'articles

```
GET /api/pipeline/select-items?count=3
```

**Réponse attendue** : `200`
```json
{
  "message": "3 proposition(s) d'article classées par pertinence.",
  "strategy": {
    "scoring": "Fraîcheur (25%) + Qualité contenu (25%) + Potentiel SEO (30%) + Diversité sources (20%)",
    "grouping": "Items regroupés par similarité de mots-clés (Jaccard >= 20%)",
    "priority": "Multi-sources > mono-source. Mots-clés longue traîne > génériques."
  },
  "proposals": [
    {
      "topic": "Transition / Énergétique / Renouvelable",
      "score": 92,
      "reasoning": "Couverture multi-sources (3 sources différentes)...",
      "category": { "id": "...", "name": "Énergie", "slug": "energie" },
      "seo_keywords": [...],
      "items": [...],
      "source_count": 3,
      "avg_quality": 78.5
    }
  ]
}
```

**Ce que le scoring évalue** :

| Critère | Poids | Description |
|---|---|---|
| Fraîcheur | 25% | Article de < 48h = score max. Décroît sur 7 jours. |
| Qualité contenu | 25% | quality_score de l'enrichissement + bonus contenu long |
| Potentiel SEO | 30% | Mots-clés longue traîne, spécifiques, faible concurrence |
| Diversité sources | 20% | Multi-sources = synthèse à haute valeur. +10 par source. |

### 9.2 Filtrer par catégorie

```
GET /api/pipeline/select-items?count=2&category_id={category_uuid}
```

### 9.3 Utiliser la proposition pour générer

Prendre les `item_ids` de la meilleure proposition et les envoyer au generate :

```
POST /api/articles/generate

{
  "item_ids": ["uuid1", "uuid2", "uuid3"],
  "category_id": "{category_uuid_from_proposal}"
}
```

---

## 10. Clusters CRUD

Les clusters regroupent des items par thématique (manuellement ou via sélection).

### 10.1 Lister les clusters

```
GET /api/clusters
GET /api/clusters?status=pending
GET /api/clusters?category_id={uuid}
```

### 10.2 Créer un cluster

```
POST /api/clusters

{
  "label": "Transition énergétique en France",
  "keywords": ["énergie", "renouvelable", "transition", "solaire"],
  "category_id": "{category_uuid}",
  "item_ids": ["{item_uuid_1}", "{item_uuid_2}", "{item_uuid_3}"]
}
```

**Réponse attendue** : `201` — cluster avec ses `cluster_items`.

### 10.3 Voir un cluster avec ses items enrichis

```
GET /api/clusters/{cluster_id}
```

**Réponse attendue** : `200` — inclut `cluster_items` avec les `rss_item.enriched_item`.

### 10.4 Modifier un cluster

```
PUT /api/clusters/{cluster_id}

{
  "label": "Transition énergétique 2026",
  "item_ids": ["{new_item_1}", "{new_item_2}"]
}
```

### 10.5 Supprimer un cluster

```
DELETE /api/clusters/{cluster_id}
```

**Réponse attendue** : `204` — supprime le cluster ET ses cluster_items.

---

## 11. Articles : Génération IA

### 11.1 Génération synchrone

```
POST /api/articles/generate

{
  "item_ids": ["{enriched_item_uuid_1}", "{enriched_item_uuid_2}"],
  "category_id": "{category_uuid}",
  "custom_prompt": "Insister sur les solutions concrètes pour les particuliers."
}
```

- `item_ids` : **obligatoire**, 1-10 UUIDs d'items enrichis
- `category_id` : optionnel, sinon prend la catégorie du premier item
- `custom_prompt` : optionnel, instructions supplémentaires pour l'IA

**Réponse attendue** : `201`
```json
{
  "data": {
    "id": "...",
    "title": "...",
    "slug": "...",
    "excerpt": "...",
    "content": "<h2>...</h2><p>...</p>...",
    "meta_title": "...",
    "meta_description": "...",
    "keywords": ["mot-clé 1", "mot-clé 2"],
    "status": "draft",
    "quality_score": 75,
    "reading_time": 5,
    "category": {...},
    "article_sources": [...]
  }
}
```

**Prérequis** : `OPENAI_API_KEY` valide + items enrichis.

### 11.2 Génération asynchrone (queue)

```
POST /api/articles/generate-async

{
  "item_ids": ["{item_1}", "{item_2}", "{item_3}"],
  "category_id": "{category_uuid}"
}
```

**Réponse attendue** : `202`
```json
{
  "message": "Génération d'article en cours (queue generation).",
  "item_ids": [...]
}
```

L'article sera créé en arrière-plan. Vérifier avec `GET /api/articles`.

---

## 12. Articles CRUD

### 12.1 Lister les articles

```
GET /api/articles
GET /api/articles?status=draft
GET /api/articles?status=published
GET /api/articles?category_id={uuid}
GET /api/articles?per_page=5
```

**Note** : Le `content` HTML n'est PAS inclus dans le listing (optimisation). Utiliser show.

### 12.2 Voir un article complet

```
GET /api/articles/{article_id}
```

**Réponse attendue** : `200` — inclut `content` HTML complet + `category` + `article_sources`.

### 12.3 Créer un article manuellement

```
POST /api/articles

{
  "title": "Mon article manuel",
  "slug": "mon-article-manuel",
  "excerpt": "Résumé de l'article...",
  "content": "<h2>Introduction</h2><p>Contenu...</p>",
  "meta_title": "Mon article | Vivat",
  "meta_description": "Description pour Google...",
  "category_id": "{category_uuid}",
  "reading_time": 3,
  "status": "draft"
}
```

### 12.4 Modifier un article

```
PUT /api/articles/{article_id}

{
  "title": "Titre modifié",
  "status": "review",
  "meta_description": "Nouvelle description SEO"
}
```

### 12.5 Supprimer un article

```
DELETE /api/articles/{article_id}
```

**Réponse attendue** : `204`.

---

## 13. Articles : Publication

### 13.1 Publier un article

```
POST /api/articles/{article_id}/publish
```

**Conditions requises** :
- `quality_score >= 60`
- `status` = `draft` ou `review`

**Si OK** : `200` — article avec `status: "published"` et `published_at` défini.

**Si KO** : `422`
```json
{
  "message": "Article non publiable (quality_score >= 60 et status draft ou review)."
}
```

### 13.2 Workflow de publication

1. Générer un article → `status: draft`
2. Relire / modifier → `PUT status: review`
3. Publier → `POST publish`
4. Archiver → `PUT status: archived`

---

## 14. Stats Dashboard

```
GET /api/stats
```

**Réponse attendue** : `200`
```json
{
  "sources": 6,
  "rss_feeds_active": 5,
  "rss_items_by_status": {
    "new": 100,
    "enriched": 15,
    "used": 3,
    "failed": 2
  },
  "articles_by_status": {
    "draft": 2,
    "published": 1
  },
  "articles_published": 1
}
```

---

## 15. Pipeline Status

```
GET /api/pipeline/status
```

**Réponse attendue** : `200`
```json
{
  "rss_feeds": {
    "total": 5,
    "active": 5,
    "due_for_fetch": 2
  },
  "rss_items_by_status": {
    "new": 100,
    "enriched": 15
  },
  "total_rss_items": 120
}
```

---

## 16. Scénarios d'erreur

### 16.1 Validation : item_ids manquant

```
POST /api/articles/generate

{}
```

**Réponse** : `422`
```json
{
  "message": "The item ids field is required.",
  "errors": { "item_ids": ["The item ids field is required."] }
}
```

### 16.2 Validation : UUID inexistant

```
POST /api/articles/generate

{
  "item_ids": ["00000000-0000-0000-0000-000000000000"]
}
```

**Réponse** : `422` — `The selected item_ids.0 is invalid.`

### 16.3 Item non enrichi

```
POST /api/articles/generate

{
  "item_ids": ["{item_id_with_status_new}"]
}
```

**Réponse** : `422` — `L'item {id} n'est pas enrichi.`

### 16.4 Publication avec quality_score insuffisant

```
POST /api/articles/{id}/publish
```

(article avec quality_score < 60)

**Réponse** : `422` — `Article non publiable (quality_score >= 60 et status draft ou review).`

### 16.5 Clé OpenAI invalide ou quota dépassé

```
POST /api/articles/generate

{
  "item_ids": ["{enriched_item_id}"]
}
```

**Réponse** : `502`
```json
{
  "message": "Erreur lors de la génération de l'article.",
  "error": "OpenAI API error: You exceeded your current quota..."
}
```

### 16.6 Source non trouvée (404)

```
GET /api/sources/00000000-0000-0000-0000-000000000000
```

**Réponse** : `404` — `No query results for model [App\Models\Source]`.

### 16.7 Validation : URL invalide

```
POST /api/rss-feeds

{
  "feed_url": "not-a-url"
}
```

**Réponse** : `422` — `The feed url field must be a valid URL.`

### 16.8 Validation : slug unique

```
POST /api/articles

{
  "title": "Test",
  "slug": "slug-deja-existant",
  "content": "..."
}
```

**Réponse** : `422` — `The slug has already been taken.`

---

## 17. Workflow complet : du RSS à l'article publié

Voici le scénario complet étape par étape pour tester le pipeline de A à Z.

### Étape 1 : Vérifier les données initiales

```
GET /api/stats
GET /api/sources
GET /api/categories
GET /api/rss-feeds
```

### Étape 2 : Déclencher l'ingestion RSS

```
POST /api/pipeline/fetch-rss
{ "all": true }
```

Puis vérifier :
```
GET /api/pipeline/status
GET /api/rss-items?status=new&per_page=5
```

### Étape 3 : Enrichir les items (nécessite OPENAI_API_KEY)

```
POST /api/pipeline/enrich
{ "limit": 15, "delay": 2 }
```

Attendre les jobs, puis vérifier :
```
GET /api/rss-items?status=enriched&per_page=5
```

### Étape 4 : Sélection intelligente des meilleurs topics

```
GET /api/pipeline/select-items?count=3
```

**Lire le `reasoning`** de chaque proposition : il explique POURQUOI cet article a été sélectionné (multi-sources, fraîcheur, potentiel SEO...).

### Étape 5 : Générer l'article à partir de la meilleure proposition

Copier les `item_ids` et le `category.id` de la proposition #1 :

```
POST /api/articles/generate

{
  "item_ids": ["uuid1", "uuid2", "uuid3"],
  "category_id": "category_uuid"
}
```

### Étape 6 : Relire et publier

```
GET /api/articles/{article_id}
```

Si satisfait, passer en review puis publier :

```
PUT /api/articles/{article_id}
{ "status": "review" }

POST /api/articles/{article_id}/publish
```

### Étape 7 : Vérifier le dashboard final

```
GET /api/stats
```

---

## 18. Workflow étape par étape pour le mentor (endpoints uniquement)

Tout se fait via **Postman** (aucune commande terminal). Idéal pour montrer au mentor comment le pipeline fonctionne de bout en bout. **Auth** : tous les appels (sauf login) utilisent `Authorization: Bearer {token}` (rôle admin).

| Étape | Méthode | URL | Body / Params | Réponse attendue |
|-------|--------|-----|----------------|------------------|
| **1. Connexion** | POST | `http://localhost:8000/api/auth/login` | `{"email":"admin@vivat.be","password":"password"}` | 200, `token` dans la réponse. Copier le token pour les étapes suivantes. |
| **2. Fetch RSS** | POST | `http://localhost:8000/api/pipeline/fetch-rss` | `{"all": true}` | 200, `"count": 5` (ou N jobs dispatchés). |
| **3. Statut (attendre les items)** | GET | `http://localhost:8000/api/pipeline/status` | — | 200, `rss_items_by_status.new` > 0. Répéter jusqu’à avoir des items `new` (Horizon doit tourner). |
| **4. Enrichissement** | POST | `http://localhost:8000/api/pipeline/enrich` | `{"limit": 20}` | 200, `"count": 20`. |
| **5. Statut (attendre enrichis)** | GET | `http://localhost:8000/api/pipeline/status` | — | 200, `rss_items_by_status.enriched` > 0. Attendre 1–2 min que les jobs traitent. |
| **6. Télécharger le CSV** | GET | `http://localhost:8000/api/pipeline/export-trends-csv` | Query : `?limit=500` ou `?per_source=200&sources=3` | 200, **fichier CSV** en téléchargement (Save Response → Save to a file dans Postman). Pas de JSON : c’est un fichier. |
| **7. Analyser les tendances (IA)** | POST | `http://localhost:8000/api/pipeline/analyze-trends` | Option A : body vide `{}` → CSV généré depuis la BDD (limit 500). Option B : Body form-data, key `csv_file`, type File → ton fichier CSV. | 200, `"success": true`, `"analysis": "1) CONNEXIONS..."`. Si CSV trop gros : `truncated: true` + `truncated_at_chars` (l’IA n’a pas tout lu). |
| **8. Propositions d’articles** | GET | `http://localhost:8000/api/pipeline/select-items?count=3` | — | 200, `proposals[]` avec `topic`, `score`, `reasoning`, `items` (avec `id`). |
| **9. Générer un article** | POST | `http://localhost:8000/api/articles/generate` | `{"item_ids":["uuid1","uuid2"],"article_type":"long_form","suggested_min_words":1000,"suggested_max_words":1800,"context_priority":"Sur X articles analysés..."}` (ids depuis l’étape 8) | 201, article créé (`id`, `title`, `content`, `status: draft`). |
| **10. Publier** | POST | `http://localhost:8000/api/articles/{article_id}/publish` | — | 200, `status: published`. |

**Headers communs (étapes 2–10)** :  
`Accept: application/json`  
`Content-Type: application/json`  
`Authorization: Bearer {token}`  

Pour l’**étape 6** (export CSV) : dans Postman, cliquer sur "Send and Download" ou "Save Response" → "Save to a file" pour récupérer le fichier.  
Pour l’**étape 7** avec fichier : onglet Body → form-data → key `csv_file`, type File → choisir le CSV téléchargé à l’étape 6 (ou un autre).

**Note** : Si le CSV est très volumineux, l’analyse (étape 7) tronque automatiquement le contenu envoyé à OpenAI ; l’IA n’a pas besoin de tout lire pour identifier tendances et connexions.

---

## Commandes Artisan utiles

```bash
# Lancer le queue worker (nécessaire pour les jobs)
docker compose exec app php artisan queue:work redis --queue=rss,enrichment,generation,default

# Fetch RSS via CLI
docker compose exec app php artisan rss:fetch --all

# Enrichir via CLI
docker compose exec app php artisan content:enrich --limit=20

# Voir les items prêts à générer
docker compose exec app php artisan articles:generate

# Horizon (dashboard queues)
http://localhost:8000/horizon

# phpMyAdmin
http://localhost:8080
```

---

## Résumé des endpoints (39 routes)

| Méthode | Endpoint | Description |
|---|---|---|
| **Sources** | | |
| GET | `/api/sources` | Lister les sources |
| POST | `/api/sources` | Créer une source |
| GET | `/api/sources/{id}` | Voir une source |
| PUT | `/api/sources/{id}` | Modifier une source |
| DELETE | `/api/sources/{id}` | Supprimer une source |
| **Catégories** | | |
| GET | `/api/categories` | Lister les catégories |
| GET | `/api/categories/{id}` | Voir une catégorie |
| **Category Templates** | | |
| GET | `/api/category-templates` | Lister les templates |
| POST | `/api/category-templates` | Créer un template |
| GET | `/api/category-templates/{id}` | Voir un template |
| PUT | `/api/category-templates/{id}` | Modifier un template |
| DELETE | `/api/category-templates/{id}` | Supprimer un template |
| **RSS Feeds** | | |
| GET | `/api/rss-feeds` | Lister les flux |
| POST | `/api/rss-feeds` | Créer un flux |
| GET | `/api/rss-feeds/{id}` | Voir un flux |
| PUT | `/api/rss-feeds/{id}` | Modifier un flux |
| DELETE | `/api/rss-feeds/{id}` | Supprimer un flux |
| **RSS Items** | | |
| GET | `/api/rss-items` | Lister les items (filtres: status, category_id, rss_feed_id) |
| GET | `/api/rss-items/{id}` | Voir un item + enrichissement |
| **Pipeline** | | |
| POST | `/api/pipeline/fetch-rss` | Déclencher l'ingestion RSS |
| POST | `/api/pipeline/enrich` | Déclencher l'enrichissement IA |
| GET | `/api/pipeline/export-trends-csv` | Télécharger le CSV tendances (query: limit, per_source, sources, status) |
| GET | `/api/pipeline/select-items` | Sélection intelligente des topics |
| GET | `/api/pipeline/status` | Statut global du pipeline |
| POST | `/api/pipeline/analyze-trends` | Analyser le CSV avec l'IA (body: limit/per_source/sources/status ou fichier csv_file) |
| **Clusters** | | |
| GET | `/api/clusters` | Lister les clusters |
| POST | `/api/clusters` | Créer un cluster |
| GET | `/api/clusters/{id}` | Voir un cluster + items |
| PUT | `/api/clusters/{id}` | Modifier un cluster |
| DELETE | `/api/clusters/{id}` | Supprimer un cluster |
| **Articles** | | |
| GET | `/api/articles` | Lister les articles (filtres: status, category_id) |
| POST | `/api/articles` | Créer un article manuellement |
| POST | `/api/articles/generate` | Générer via IA (synchrone) |
| POST | `/api/articles/generate-async` | Générer via IA (queue) |
| GET | `/api/articles/{id}` | Voir un article complet |
| PUT | `/api/articles/{id}` | Modifier un article |
| DELETE | `/api/articles/{id}` | Supprimer un article |
| POST | `/api/articles/{id}/publish` | Publier un article |
| **Stats** | | |
| GET | `/api/stats` | Dashboard global |
