# Postman — Nouveaux endpoints et route de test complète

## ⚠️ Important : pour éviter du HTML ou des 500

- **URL de base** : utilise **exactement** `http://localhost:8000/api` (port **8000**, pas 5173 ni 3000). Si tu as un frontend Vite sur 5173, les requêtes **API** doivent aller vers **8000**.
- **Headers sur toutes les requêtes API** (sauf Login si tu veux) :  
  **`Accept`** : `application/json`  
  Sans ce header, Laravel peut renvoyer une page HTML en cas d’erreur.
- **Pour les POST avec body JSON** :  
  **`Content-Type`** : `application/json`
- **Étape 2 (Fetch RSS)** : envoie bien **`{"all": true}`** dans le body. Sans `all: true`, l’API ne prend que les flux « dus » (`due_for_fetch`). Si aucun n’est dû, tu obtiens « Aucun flux à traiter ». Avec `all: true`, tous les flux actifs sont traités.

---

## Nouveaux endpoints à ajouter dans Postman

Ces **2 endpoints** ont été ajoutés pour le workflow CSV + analyse IA. Ils sont **protégés** (admin) : utilise un token obtenu via `POST /api/auth/login`.

| Méthode | URL | Description |
|--------|-----|-------------|
| **GET** | `http://localhost:8000/api/pipeline/export-trends-csv` | Télécharge le CSV des articles (tendances). Query params optionnels : `limit`, `per_source`, `sources`, `status`. |
| **POST** | `http://localhost:8000/api/pipeline/analyze-trends` | Envoie le CSV à l’IA (ou génère le CSV depuis la BDD) et retourne l’analyse (connexions, tendances, fiche rédactionnelle). |

---

## Test des endpoints Home (9 catégories + 10 articles)

Pour tester **GET /api/public/home** et les 4 types d'affichage (top_news, featured, standard, secondary) :

1. **Créer les 9 catégories** : voir `docs/POSTMAN_9_CATEGORIES_BODIES.md` (POST /api/categories pour chaque body).
2. **Créer les 10 articles** :
   - **Option A (Postman)** : **POST** `http://localhost:8000/api/seed-home-articles` avec **Headers** : `Accept: application/json`, `Authorization: Bearer {{token}}` (token admin). Body : aucun. Réponse : 200, message de confirmation.
   - **Option B (CLI)** : `php artisan db:seed --class=HomeArticlesSeeder` (voir `docs/POSTMAN_10_ARTICLES_HOME_BODIES.md`).
3. **Tester la home** : **GET** `http://localhost:8000/api/public/home` avec **Accept: application/json** (pas de token). Vérifier `top_news`, `featured`, `latest`, `categories`, `writer_cta`.

**Checklist complète des tests Home** : voir **`docs/POSTMAN_HOME_TESTS.md`** (tous les endpoints, pagination, hub, writer_cta, cache).

---

## Route de test complète (à créer dans Postman)

Crée une **Collection** "Vivat – Test complet" et ajoute les requêtes dans l’ordre ci‑dessous. Base URL : `http://localhost:8000/api`.

---

### 1. Login (obtenir le token)

- **Method** : `POST`
- **URL** : `http://localhost:8000/api/auth/login`
- **Headers** :  
  `Accept` : `application/json`  
  `Content-Type` : `application/json`
- **Body** (raw JSON) :
```json
{
  "email": "admin@vivat.be",
  "password": "password"
}
```
- **Réponse** : 200, copie la valeur de `token` (ex. `1|abc123...`) pour l’étape 2.

---

### 2. Fetch RSS

- **Method** : `POST`
- **URL** : `http://localhost:8000/api/pipeline/fetch-rss`
- **Headers** :  
  `Accept` : `application/json`  
  `Content-Type` : `application/json`  
  `Authorization` : `Bearer {{token}}`  
  (Remplace `{{token}}` par le token de l’étape 1, ou crée une variable Postman `token`.)
- **Body** (raw JSON) — **obligatoire** : sans `"all": true`, tu peux avoir « Aucun flux à traiter » si aucun flux n’est « dû ».
```json
{
  "all": true
}
```
- **Réponse** : 200, `"count": 5` (ou un autre nombre).

---

### 3. Statut du pipeline (à répéter jusqu’à avoir des items `new`)

- **Method** : `GET`
- **URL** : `http://localhost:8000/api/pipeline/status`
- **Headers** :  
  `Accept` : `application/json`  
  `Authorization` : `Bearer {{token}}`
- **Body** : aucun.
- **Réponse** : 200. Vérifier `rss_items_by_status.new` > 0. Si 0, relancer après quelques secondes (Horizon doit tourner).

---

### 4. Enrichissement

- **Method** : `POST`
- **URL** : `http://localhost:8000/api/pipeline/enrich`
- **Headers** :  
  `Accept` : `application/json`  
  `Content-Type` : `application/json`  
  `Authorization` : `Bearer {{token}}`
- **Body** (raw JSON) :
```json
{
  "limit": 20
}
```
- **Réponse** : 200, `"count": 20`.

---

### 5. Statut (attendre des items `enriched`)

- **Method** : `GET`
- **URL** : `http://localhost:8000/api/pipeline/status`
- **Headers** :  
  `Accept` : `application/json`  
  `Authorization` : `Bearer {{token}}`
- **Réponse** : 200. Vérifier `rss_items_by_status.enriched` > 0. Attendre 1–2 min si besoin.

---

### 6. Export CSV (nouveau) — télécharger le fichier

- **Method** : `GET`
- **URL** : `http://localhost:8000/api/pipeline/export-trends-csv?limit=500`
  - Variante avec 3 sources :  
    `http://localhost:8000/api/pipeline/export-trends-csv?per_source=200&sources=3`
- **Headers** :  
  `Accept` : `application/json` (ou `*/*`)  
  `Authorization` : `Bearer {{token}}`
- **Body** : aucun.
- **Dans Postman** : cliquer sur **Send and Download** (ou **Save Response** → **Save to a file**) et enregistrer le fichier (ex. `trends_export.csv`).
- **Réponse** : 200, corps = fichier CSV (pas du JSON).

---

### 7. Analyser les tendances (nouveau) — Option A : CSV depuis la BDD

- **Method** : `POST`
- **URL** : `http://localhost:8000/api/pipeline/analyze-trends`
- **Headers** :  
  `Accept` : `application/json`  
  `Content-Type` : `application/json`  
  `Authorization` : `Bearer {{token}}`
- **Body** (raw JSON) :
```json
{
  "limit": 500
}
```
- **Réponse** : 200 avec `"success": true`, `"analysis": "1) CONNEXIONS ENTRE ARTICLES..."`. Si le CSV a été tronqué : `"truncated": true`, `"truncated_at_chars": 45000`.

---

### 7 bis. Analyser les tendances — Option B : envoyer un fichier CSV

- **Method** : `POST`
- **URL** : `http://localhost:8000/api/pipeline/analyze-trends`
- **Headers** :  
  `Accept` : `application/json`  
  `Authorization` : `Bearer {{token}}`  
  (Ne pas mettre `Content-Type` : Postman le met automatiquement en multipart.)
- **Body** : onglet **form-data**  
  - Key : `csv_file` | Type : **File** | Value : sélectionner le fichier téléchargé à l’étape 6 (ou un autre CSV).
- **Réponse** : 200, `"success": true`, `"analysis": "..."`.

---

### 8. Propositions d’articles (select-items)

- **Method** : `GET`
- **URL** : `http://localhost:8000/api/pipeline/select-items?count=3`
- **Headers** :  
  `Accept` : `application/json`  
  `Authorization` : `Bearer {{token}}`
- **Réponse** : 200, `proposals[]` avec pour chaque proposition : `topic`, `score`, `reasoning`, `items` (chaque item a un `id`). Noter 2 ou 3 `id` pour l’étape 9.

---

### 9. Générer un article

- **Method** : `POST`
- **URL** : `http://localhost:8000/api/articles/generate`
- **Headers** :  
  `Accept` : `application/json`  
  `Content-Type` : `application/json`  
  `Authorization` : `Bearer {{token}}`
- **Body** (raw JSON) — **remplacer les UUID par ceux de l’étape 8** :
```json
{
  "item_ids": ["UUID_1", "UUID_2", "UUID_3"],
  "article_type": "long_form",
  "suggested_min_words": 1000,
  "suggested_max_words": 1800,
  "context_priority": "Sur les articles analysés, ce sujet est prioritaire (tendance)."
}
```
- **Réponse** : 201, article créé avec `id`, `title`, `content`, `status: "draft"`. Noter l’`id` pour l’étape 10.

---

### 10. Publier l’article

- **Method** : `POST`
- **URL** : `http://localhost:8000/api/articles/{article_id}/publish`  
  Remplacer `{article_id}` par l’`id` de l’étape 9.
- **Headers** :  
  `Accept` : `application/json`  
  `Authorization` : `Bearer {{token}}`
- **Body** : aucun (ou `{}`).
- **Réponse** : 200, `"status": "published"`.

---

## Résumé : nouveaux endpoints seulement

À ajouter dans ta collection Postman si tu avais déjà les anciens :

1. **GET** `http://localhost:8000/api/pipeline/export-trends-csv`  
   - Params : `limit` (optionnel), `per_source` (optionnel), `sources` (optionnel), `status` (optionnel).  
   - Envoi : **Send and Download** pour sauvegarder le CSV.

2. **POST** `http://localhost:8000/api/pipeline/analyze-trends`  
   - Soit body JSON : `{"limit": 500}` (ou `per_source`, `sources`, `status`).  
   - Soit body **form-data** : clé `csv_file`, type **File**, valeur = ton fichier CSV.

Toutes les requêtes (sauf Login) utilisent :  
`Authorization: Bearer {{token}}`  
avec `{{token}}` = valeur reçue dans la réponse de `POST /api/auth/login`.

---

## Dépannage : HTML ou 500 Internal Server Error

| Problème | Cause probable | Solution |
|----------|----------------|----------|
| Réponse = page HTML (Vivat, Tailwind) au lieu de JSON | Requête vers le mauvais serveur (ex. frontend 5173) ou header manquant | URL doit être **http://localhost:8000/api/...** (port 8000). Ajouter **Accept: application/json** sur la requête. |
| « Aucun flux à traiter » à l’étape 2 | Body absent ou sans `"all": true` | Body (raw JSON) : `{"all": true}`. |
| 500 Internal Server Error | Erreur côté API (PHP, BDD, etc.) | Vérifier **storage/logs/laravel.log** dans le projet. Vérifier que le serveur API tourne (Docker ou `php artisan serve` sur le port 8000). |
| 401 Unauthenticated | Token absent, expiré ou mal collé | Refaire l’étape 1 (Login), copier tout le `token` (ex. `2|039RE3N3c...`), et le mettre dans **Authorization: Bearer** sans espace avant/après. |

**Vérification rapide** : dans Postman, ouvre l’onglet **Headers** et assure-toi que **Accept** = `application/json` pour les étapes 2 à 10. Pour les POST avec body, **Content-Type** = `application/json`.
