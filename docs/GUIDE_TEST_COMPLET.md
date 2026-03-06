# Guide de test complet — Du début à la fin jusqu'à obtenir un article

Ce guide te permet de tester le pipeline complet : fetch RSS → enrichissement → sélection → génération → article publié.

---

## Prérequis

### 1. Vérifier que Docker tourne

```bash
docker compose ps
```

Tu dois voir : `app`, `mysql`, `redis`, `phpmyadmin` (tous "Up").

Si pas lancé :
```bash
docker compose up -d
```

---

### 2. Vérifier que la base de données est migrée et seedée

```bash
docker compose exec app php artisan migrate:status
```

Si des migrations sont en attente :
```bash
docker compose exec app php artisan migrate
```

Puis seed les données de base (catégories, sources, flux RSS) :
```bash
docker compose exec app php artisan db:seed
```

Cela crée :
- 14 catégories
- 6 sources (Reporterre, Futura Sciences, Novethic, etc.)
- 5 flux RSS
- 14 category templates
- 1 admin (`admin@vivat.be` / `password`)
- 1 contributeur (`contributeur@vivat.be` / `password`)

---

### 3. Vérifier OpenAI API Key

```bash
docker compose exec app php artisan tinker
```

Puis dans tinker :
```php
config('services.openai.api_key')
```

Si `null` ou vide, ajoute dans `.env` :
```
OPENAI_API_KEY=sk-...
```

Puis redémarre :
```bash
docker compose restart app
```

⚠️ **Important** : Sans clé OpenAI valide avec crédits, l'enrichissement et la génération échoueront (erreur 429 ou 402).

---

### 4. Lancer Horizon (pour les jobs asynchrones)

```bash
docker compose exec app php artisan horizon
```

Laisse-le tourner dans un terminal séparé (ou en arrière-plan). Horizon traite les jobs des queues `rss`, `enrichment`, `generation`.

---

## Étape 1 : Vérifier les données de base

### 1.1 Vérifier les catégories

```bash
curl http://localhost:8000/api/categories
```

Tu dois voir au moins quelques catégories (ex: "Environnement", "Santé", "Énergie").

### 1.2 Vérifier les sources et flux RSS

```bash
curl http://localhost:8000/api/sources
curl http://localhost:8000/api/rss-feeds
```

Tu dois voir des sources (ex: "Reporterre", "Futura Sciences") et des flux RSS avec leurs URLs.

---

## Étape 2 : Fetch RSS (récupérer les articles depuis les flux)

### Option A : Via l'API (recommandé)

```bash
curl -X POST http://localhost:8000/api/pipeline/fetch-rss \
  -H "Content-Type: application/json" \
  -d '{"all": true}'
```

**Réponse attendue** : `200` avec `"message": "X job(s) FetchRssFeedJob dispatché(s)."`

### Option B : Via commande Artisan

```bash
docker compose exec app php artisan rss:fetch --all
```

Ou seulement les flux dus :
```bash
docker compose exec app php artisan rss:fetch --due
```

**Ce qui se passe** :
- Les jobs `FetchRssFeedJob` sont dispatchés dans la queue `rss`
- Horizon les traite automatiquement (si Horizon tourne)
- Les nouveaux articles sont créés dans `rss_items` avec statut `new`

### 1.3 Vérifier que les items RSS ont été créés

**Attendre 10-30 secondes** (le temps que Horizon traite les jobs), puis :

```bash
curl http://localhost:8000/api/rss-items?status=new | jq '.data | length'
```

Ou via phpMyAdmin : http://localhost:8080 → table `rss_items` → filtrer `status = 'new'`.

Tu dois voir des `rss_items` avec :
- `title` (titre de l'article)
- `url` (lien vers l'article original)
- `status = 'new'`
- `published_at` (date de publication originale)

---

## Étape 3 : Enrichissement (scraping + analyse IA)

### 3.1 Lancer l'enrichissement

**Via l'API** :

```bash
curl -X POST http://localhost:8000/api/pipeline/enrich \
  -H "Content-Type: application/json" \
  -d '{"limit": 10}'
```

**Via commande Artisan** :

```bash
docker compose exec app php artisan content:enrich --limit=10
```

Par défaut, la commande traite 50 items avec un délai de 3 secondes entre chaque dispatch :
```bash
docker compose exec app php artisan content:enrich
```

**Ce qui se passe** :
- Les jobs `EnrichContentJob` sont dispatchés dans la queue `enrichment`
- Horizon les traite (avec rate limiting OpenAI : 50/min)
- Chaque job :
  1. Scrape l'URL de l'article (extraction HTML)
  2. Appelle OpenAI pour analyser le contenu (lead, key_points, seo_keywords, primary_topic, quality_score, seo_score)
  3. Crée un `EnrichedItem` lié au `RssItem`
  4. Met le `RssItem` en statut `enriched`

### 3.2 Vérifier les items enrichis

**Attendre 1-2 minutes** (le temps que Horizon traite les jobs et que OpenAI réponde), puis :

```bash
curl http://localhost:8000/api/rss-items?status=enriched | jq '.data | length'
```

Ou vérifier dans phpMyAdmin :
- Table `rss_items` : filtrer `status = 'enriched'`
- Table `enriched_items` : tu dois voir des enregistrements avec `lead`, `key_points`, `seo_keywords`, `quality_score`, `seo_score`, `primary_topic`

**Vérifier un item enrichi en détail** :

```bash
curl http://localhost:8000/api/rss-items/{un_id_enriched} | jq '.data.enriched_item'
```

Tu dois voir :
- `lead` : résumé 1-2 phrases
- `key_points` : tableau de points clés
- `seo_keywords` : tableau de mots-clés SEO
- `primary_topic` : sujet principal (ex: "transition énergétique")
- `quality_score` : 0-100
- `seo_score` : 0-100

---

## Étape 4 : Sélection intelligente (voir les propositions d'articles)

### 4.1 Voir les meilleures propositions

```bash
curl "http://localhost:8000/api/pipeline/select-items?count=3" | jq
```

**Réponse attendue** : `200` avec un tableau `proposals` contenant pour chaque proposition :
- `topic` : label du sujet (ex: "transition énergétique / Europe / renouvelables")
- `score` : score 0-100
- `reasoning` : explication détaillée (pourquoi cet article)
- `suggested_article_type` : `hot_news` | `long_form` | `standard`
- `suggested_min_words` / `suggested_max_words` : longueur cible
- `context_priority` : phrase de contexte (ex: "Sur 50 articles analysés, 10 portent sur ce sujet (tendance). Ce sujet est prioritaire.")
- `items` : tableau des articles sources (avec titre, URL, source, quality_score)
- `source_count` : nombre de sources différentes
- `avg_quality` : qualité moyenne

**Exemple de réponse** :
```json
{
  "message": "3 proposition(s) d'article classées par pertinence.",
  "proposals": [
    {
      "topic": "transition énergétique / Europe / renouvelables",
      "score": 87,
      "reasoning": "Couverture multi-sources (3 sources différentes) = synthèse à haute valeur ajoutée. Qualité moyenne élevée (78/100) : contenu bien structuré et informatif. Fort potentiel SEO (score moyen : 72/100) sur : transition énergétique, énergies renouvelables, Europe, décarbonation, politique climatique. Actualité récente (< 3 jours).",
      "suggested_article_type": "long_form",
      "suggested_min_words": 1000,
      "suggested_max_words": 1800,
      "context_priority": "Sur 50 articles analysés, 3 portent sur ce sujet (tendance, corrélation). Ce sujet est prioritaire.",
      "items": [
        {
          "id": "...",
          "title": "L'Europe accélère sa transition énergétique",
          "url": "https://reporterre.net/...",
          "source": "Reporterre",
          "quality_score": 80,
          "published_at": "2026-02-15T10:00:00Z"
        },
        ...
      ],
      "source_count": 3,
      "avg_quality": 78.5
    }
  ]
}
```

### 4.2 Choisir une proposition

Note les `item_ids` de la proposition que tu veux utiliser (ex: le premier avec score 87).

---

## Étape 5 : Génération d'article (IA)

### 5.1 Génération synchrone (attendre la réponse)

```bash
curl -X POST http://localhost:8000/api/articles/generate \
  -H "Content-Type: application/json" \
  -d '{
    "item_ids": ["id1", "id2", "id3"],
    "article_type": "long_form",
    "suggested_min_words": 1000,
    "suggested_max_words": 1800,
    "context_priority": "Sur 50 articles analysés, 3 portent sur ce sujet (tendance). Ce sujet est prioritaire."
  }' | jq
```

Remplace `["id1", "id2", "id3"]` par les vrais UUIDs de ta proposition.

**Réponse attendue** : `201` avec l'article généré :
- `title` : titre de l'article
- `slug` : URL-friendly
- `excerpt` : résumé
- `content` : contenu HTML complet
- `meta_title`, `meta_description`, `keywords` : SEO
- `reading_time` : temps de lecture estimé
- `quality_score` : score qualité (doit être >= 60 pour publier)
- `status` : `draft`

**Temps d'attente** : 30-90 secondes (appel OpenAI).

### 5.2 Génération asynchrone (retour immédiat, job en arrière-plan)

```bash
curl -X POST http://localhost:8000/api/articles/generate-async \
  -H "Content-Type: application/json" \
  -d '{
    "item_ids": ["id1", "id2", "id3"],
    "article_type": "long_form",
    "suggested_min_words": 1000,
    "suggested_max_words": 1800,
    "context_priority": "Sur 50 articles analysés, 3 portent sur ce sujet (tendance). Ce sujet est prioritaire."
  }'
```

**Réponse attendue** : `202` avec `"message": "Génération d'article en cours (queue generation)."`

Puis vérifier dans Horizon ou dans la base :
- Horizon : http://localhost:8000/horizon → queue `generation` → voir le job en cours/terminé
- Base : table `articles` → voir l'article créé avec `status = 'draft'`

---

## Étape 6 : Vérifier l'article généré

### 6.1 Lister les articles

```bash
curl http://localhost:8000/api/articles | jq '.data[0]'
```

### 6.2 Voir un article en détail

```bash
curl http://localhost:8000/api/articles/{article_id} | jq
```

Tu dois voir :
- `title` : titre généré par l'IA
- `content` : contenu HTML complet (h2, h3, paragraphes)
- `excerpt` : résumé
- `meta_title`, `meta_description`, `keywords` : SEO
- `reading_time` : temps de lecture (ex: 5 minutes)
- `quality_score` : score qualité (ex: 75)
- `status` : `draft`
- `article_sources` : tableau des sources originales (avec URLs)

### 6.3 Vérifier la traçabilité

Dans `article_sources`, tu dois voir les URLs des articles sources réels utilisés pour générer cet article.

---

## Étape 7 : Publier l'article

### 7.1 Vérifier que l'article est publiable

```bash
curl http://localhost:8000/api/articles/{article_id} | jq '.data.quality_score'
```

Si `quality_score >= 60`, l'article est publiable.

### 7.2 Publier

```bash
curl -X POST http://localhost:8000/api/articles/{article_id}/publish \
  -H "Authorization: Bearer {token_admin}" \
  -H "Content-Type: application/json"
```

⚠️ **Note** : La publication nécessite une authentification admin. Tu peux te connecter d'abord :

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@vivat.be",
    "password": "password"
  }' | jq '.token'
```

Puis utiliser le token dans le header `Authorization: Bearer {token}`.

**Réponse attendue** : `200` avec l'article publié :
- `status` : `published`
- `published_at` : date de publication

---

## Vérification finale

### Voir l'article publié (API publique)

```bash
curl http://localhost:8000/api/public/articles | jq '.data[0]'
```

Ou par slug :
```bash
curl http://localhost:8000/api/public/articles/{slug} | jq
```

---

## Checklist complète

- [ ] Docker lancé (`docker compose ps`)
- [ ] Migrations exécutées (`php artisan migrate`)
- [ ] Seeders exécutés (`php artisan db:seed`)
- [ ] OpenAI API Key configurée (`.env`)
- [ ] Horizon lancé (`php artisan horizon`)
- [ ] Fetch RSS lancé → items `new` créés
- [ ] Enrichissement lancé → items `enriched` avec `enriched_items`
- [ ] Sélection intelligente → propositions avec scores et reasoning
- [ ] Génération d'article → article `draft` créé
- [ ] Vérification article (titre, contenu, sources, quality_score)
- [ ] Publication → article `published` avec `published_at`

---

## Dépannage

### Problème : Pas d'items RSS créés

- Vérifier que les flux RSS sont actifs : `GET /api/rss-feeds`
- Vérifier Horizon : les jobs `FetchRssFeedJob` sont-ils traités ?
- Vérifier les logs : `docker compose logs app | grep FetchRssFeedJob`

### Problème : Enrichissement échoue (429 ou 402)

- OpenAI quota dépassé → ajouter des crédits sur https://platform.openai.com
- Vérifier la clé API : `config('services.openai.api_key')` dans tinker

### Problème : Génération échoue

- Vérifier que les items sont bien `enriched` (avec `enrichedItem` non null)
- Vérifier OpenAI quota
- Vérifier les logs Horizon : `docker compose logs app | grep GenerateArticleJob`

### Problème : Article non publiable (quality_score < 60)

- Normal : le système filtre les articles de mauvaise qualité
- Tu peux quand même voir l'article en `draft` via `GET /api/articles/{id}`
- Pour tester la publication, génère un autre article ou modifie manuellement `quality_score` en base (pour les tests uniquement)

---

*Guide de test complet — Février 2026*
