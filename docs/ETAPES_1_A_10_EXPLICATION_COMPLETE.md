# Pipeline complet : étapes 1 à 10 — Explication et code

Document de référence pour comprendre **ce qui se passe** à chaque étape du workflow Postman (connexion → fetch RSS → enrichissement → export CSV → analyse tendances → sélection → génération → publication) et **comment c’est fait dans le code**. Destiné aux développeurs et au chef de projet.

**Dernière mise à jour** : février 2026.

---

## Vue d’ensemble du flux

```
1. Login (token) → 2. Fetch RSS (jobs) → 3. Status (vérif) → 4. Enrich (jobs) → 5. Status (vérif)
→ 6. Export CSV (téléchargement) → 7. Analyse tendances (OpenAI) → 8. Select items (propositions)
→ 9. Generate article (OpenAI) → 10. Publish
```

- **Étapes 2 et 4** : on envoie des **jobs** dans des files (queues). Un process (Horizon ou `queue:work`) les exécute en arrière-plan.
- **Étapes 7 et 9** : appels directs à l’**API OpenAI** (prompts en config ou construits dans le code).

---

## Étape 1 — Connexion (obtenir le token)

### Ce que tu fais

- **POST** `http://localhost:8000/api/auth/login`
- **Body** : `{"email": "admin@vivat.be", "password": "password"}`
- **Réponse** : un **token** (ex. `2|039RE3N3cMVR2dhyu...`) à mettre dans le header `Authorization: Bearer {token}` pour toutes les requêtes suivantes.

### Ce qui se passe dans le code

- **Route** : `POST /api/auth/login` → `AuthController::login`.
- **Validation** : email et mot de passe requis.
- **Authentification** : `Auth::attempt($credentials)` vérifie l’utilisateur en base (`users`). Si OK, un **token Sanctum** est créé et renvoyé. Ce token identifie l’utilisateur et son rôle (admin) pour les endpoints protégés.

**Fichiers** : `routes/api.php` (prefix `auth`), `app/Http/Controllers/Api/AuthController.php`, Laravel Sanctum.

---

## Étape 2 — Fetch RSS (déclencher la récupération des articles)

### Ce que tu fais

- **POST** `http://localhost:8000/api/pipeline/fetch-rss`
- **Headers** : `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Body** : `{"all": true}` — **obligatoire** pour traiter tous les flux actifs (sinon seuls les flux « dus » sont pris, et tu peux avoir « Aucun flux à traiter »).
- **Réponse** : `{"message": "5 job(s) FetchRssFeedJob dispatché(s).", "count": 5}` (exemple).

### Ce qui se passe dans le code

- **Route** : `POST /api/pipeline/fetch-rss` (middleware admin) → `PipelineController::fetchRss`.
- **Logique** :
  - Si `feed_id` est fourni : un seul `RssFeed` est récupéré et un job est dispatché pour ce flux.
  - Sinon : si `all` est vrai → `RssFeed::active()->get()`, sinon → `RssFeed::dueForFetch()->get()`.
  - Pour chaque flux, **FetchRssFeedJob** est dispatché sur la queue **`rss`**.

**Job FetchRssFeedJob** (`app/Jobs/FetchRssFeedJob.php`) :

1. Requête HTTP GET sur l’URL du flux RSS (header `User-Agent` + `Accept` XML).
2. Le **RssParserService** parse le XML (RSS 2.0 ou Atom) et retourne une liste d’items (titre, lien, date, description, guid).
3. Pour chaque item, un **hash de déduplication** est calculé (guid/link/title). Si un `RssItem` avec ce hash existe déjà, on ignore.
4. Sinon, création d’un **RssItem** avec `status = 'new'`, `rss_feed_id`, `category_id`, `title`, `url`, `published_at`, etc.
5. Mise à jour de `last_fetched_at` sur le flux.

**Aucun prompt OpenAI** ici : uniquement HTTP + parsing XML.

**Fichiers** : `PipelineController::fetchRss`, `FetchRssFeedJob`, `RssParserService`.

---

## Étape 3 — Statut du pipeline (vérifier que des items « new » existent)

### Ce que tu fais

- **GET** `http://localhost:8000/api/pipeline/status`
- **Headers** : `Authorization: Bearer {token}`
- **Réponse** : JSON avec `rss_feeds` (total, active, due_for_fetch) et **`rss_items_by_status`** (new, enriched, failed, etc.). Tu répètes jusqu’à voir `rss_items_by_status.new > 0` (les jobs de l’étape 2 ont été traités par Horizon).

### Ce qui se passe dans le code

- **Route** : `GET /api/pipeline/status` → `PipelineController::status`.
- Requêtes en base : `RssFeed::count()`, `RssFeed::active()->count()`, `RssFeed::dueForFetch()->count()`, puis `RssItem::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')`. Pas d’appel externe, pas de prompt.

**Fichier** : `PipelineController::status`.

---

## Étape 4 — Enrichissement (scraping + analyse IA)

### Ce que tu fais

- **POST** `http://localhost:8000/api/pipeline/enrich`
- **Headers** : `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Body** : `{"limit": 20}` (nombre max d’items « new » à envoyer en queue).
- **Réponse** : `{"message": "20 job(s) EnrichContentJob dispatché(s).", "count": 20}`.

### Ce qui se passe dans le code

- **Route** : `POST /api/pipeline/enrich` → `PipelineController::enrich`.
- **Logique** : récupération des `RssItem` avec `status = 'new'`, limit au `limit` demandé (max 200). Pour chaque item, **EnrichContentJob** est dispatché sur la queue **`enrichment`**, avec un délai progressif entre chaque dispatch pour limiter la charge.

**Job EnrichContentJob** (`app/Jobs/EnrichContentJob.php`) :

1. Mise à jour de l’item : `status = 'enriching'`.
2. **ContentExtractorService::extract($url)** : requête HTTP sur l’URL de l’article, extraction du contenu principal (balises article/main, nettoyage scripts/nav/footer). Retourne titre, headings, texte. Si échec ou texte trop court (< 200 caractères) → `status = 'failed'` et fin.
3. **Appel OpenAI** avec le texte extrait (tronqué à 6000 caractères) pour obtenir une analyse structurée en JSON.

**Prompt utilisé (enrichissement)** — dans le job, en dur :

- **System** : *« Tu es un analyste de contenu SEO expert. Tu analyses des articles et produis une analyse structurée avec des mots-clés SEO ciblés (longue traîne, spécifiques, faible concurrence). Privilégie les termes recherchés par les utilisateurs mais peu concurrentiels. Réponds uniquement en JSON. »*
- **User** : titre + titres de sections + contenu, puis consignes pour générer un JSON avec :
  - `lead` (résumé 1–2 phrases)
  - `headings` (tableau des H2/H3)
  - `key_points` (3–7 points clés)
  - `seo_keywords` (5–10 mots-clés SEO)
  - `primary_topic` (sujet principal en 2–4 mots)
  - `quality_score` (0–100)
  - `seo_score` (0–100)

4. Création ou mise à jour d’un **EnrichedItem** (lié au `RssItem`) avec ces champs + `extracted_text`, puis `status = 'enriched'` sur le `RssItem`. En cas d’erreur OpenAI (ex. 429) : `status = 'new'` pour réessayer plus tard.

**Fichiers** : `PipelineController::enrich`, `EnrichContentJob`, `ContentExtractorService`, modèle `EnrichedItem`.

---

## Étape 5 — Statut (vérifier que des items sont « enriched »)

### Ce que tu fais

- **GET** `http://localhost:8000/api/pipeline/status` (même qu’étape 3).
- Tu répètes jusqu’à avoir **`rss_items_by_status.enriched` > 0** (attendre 1–2 minutes que Horizon traite les jobs d’enrichissement).

### Ce qui se passe dans le code

Identique à l’étape 3 : `PipelineController::status`, lecture des comptes par statut en base.

---

## Étape 6 — Export CSV (télécharger le fichier tendances)

### Ce que tu fais

- **GET** `http://localhost:8000/api/pipeline/export-trends-csv?limit=500`
- **Headers** : `Authorization: Bearer {token}`
- **Réponse** : **fichier CSV** (téléchargement). Dans Postman : « Send and Download » ou « Save Response » pour enregistrer le fichier. Colonnes : date, title, category, source, primary_topic, seo_keywords, quality_score, seo_score, url, status.

### Ce qui se passe dans le code

- **Route** : `GET /api/pipeline/export-trends-csv` → `PipelineController::exportTrendsCsv`.
- **Query params** : `limit` (défaut 1000), `per_source`, `sources` (défaut 3), `status` (optionnel). Même logique que la commande `pipeline:export-trends-csv` : soit N sources × M items par source (`per_source` + `sources`), soit un `limit` global.
- **Méthode privée** `buildCsvForTrends` : requête sur `RssItem` avec relations `enrichedItem`, `rssFeed.source`, `category`, tri par date, puis construction d’une chaîne CSV (séparateur `;`, une ligne par item avec les colonnes ci-dessus).
- **Réponse** : `response()->streamDownload(...)` avec BOM UTF-8 + contenu CSV, headers `Content-Type: text/csv; charset=UTF-8` et `Content-Disposition: attachment`. Pas d’OpenAI, pas de prompt.

**En résumé** : ce CSV contient les **articles enrichis** (avec primary_topic, seo_keywords, scores). C’est ce fichier (ou le même contenu généré depuis la BDD) qui sera ensuite **lu par le prompt OpenAI** à l’étape 7 (`POST /api/pipeline/analyze-trends`) pour l’analyse des tendances, connexions et choix du meilleur sujet.

**Fichiers** : `PipelineController::exportTrendsCsv`, `buildCsvForTrends`.

---

## Étape 7 — Analyser les tendances (IA lit le CSV)

### Ce que tu fais

- **POST** `http://localhost:8000/api/pipeline/analyze-trends`
- **Headers** : `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Body (option A)** : `{"limit": 500}` — le CSV est généré depuis la BDD (même logique qu’étape 6) puis envoyé à l’IA.
- **Body (option B)** : en **form-data**, clé `csv_file` (type File) = le fichier téléchargé à l’étape 6.
- **Réponse** : `{"success": true, "analysis": "1) CONNEXIONS ENTRE ARTICLES\n\n..."}`. Si le CSV a été tronqué : `truncated: true`, `truncated_at_chars: 45000`.

### Ce qui se passe dans le code

- **Route** : `POST /api/pipeline/analyze-trends` → `PipelineController::analyzeTrends`.
- **Contenu CSV** : soit lecture du fichier uploadé (`csv_file`), soit appel à `buildCsvForTrends` avec les paramètres du body (limit, per_source, sources, status).
- **TrendsAnalysisService::analyze($csvContent)** :
  1. Troncation du CSV à **max_csv_chars** (config `trends_analysis.max_csv_chars`, défaut 45 000) pour tenir dans le contexte OpenAI. L’IA n’a pas besoin de tout le fichier pour identifier tendances et connexions.
  2. Chargement du **prompt** depuis **`config/trends_analysis.php`** :
     - **system_prompt** : définit le rôle (expert analyse éditoriale, tendances, média environnement/santé/énergie/société) et le format de réponse (français, structuré).
     - **user_prompt_template** : contient le placeholder `{csv_content}`. Le contenu tronqué du CSV y est injecté, puis les consignes demandent :
       1. Connexions entre articles (groupes par sujet, sources, corrélations)
       2. Corrélations et tendances (dates, primary_topic, seo_keywords ; chaud vs de fond)
       3. Meilleur sujet pour écrire un article (justification, titre, phrase de contexte priorité)
       4. Poids à attribuer (fraîcheur, qualité, SEO, diversité, fréquence sujet)
       5. Hot news vs article de fond (fourchettes de mots, ton)
       + une « fiche rédactionnelle » finale (sujet, type, min–max mots, ton, contexte priorité, mots-clés SEO).
  3. Appel **OpenAI** (chat completions) avec ce system + user. En cas de **429**, un **retry** après 60 secondes est fait une fois.
  4. Retour de l’analyse en texte ; si troncation, ajout de `truncated` et `truncated_at_chars` dans la réponse JSON.

**Fichiers** : `PipelineController::analyzeTrends`, `TrendsAnalysisService`, `config/trends_analysis.php` (prompts et `max_csv_chars`).

---

## Étape 8 — Sélection intelligente (propositions d’articles)

### Ce que tu fais

- **GET** `http://localhost:8000/api/pipeline/select-items?count=3`
- **Headers** : `Authorization: Bearer {token}`
- **Réponse** : `proposals[]` — pour chaque proposition : `topic`, `score`, `reasoning`, `items` (avec `id`, `title`, `url`, `source`, `quality_score`), `suggested_article_type`, `suggested_min_words`, `suggested_max_words`, `context_priority`. Tu notes les **item_ids** pour l’étape 9.

### Ce qui se passe dans le code

- **Route** : `GET /api/pipeline/select-items` → `PipelineController::selectItems` → **ArticleSelectionService::selectBestTopics**.
- **ArticleSelectionService** (`app/Services/ArticleSelectionService.php`) :
  1. Récupération des **RssItem** avec `status = 'enriched'` et relation `enrichedItem`, optionnel filtre par `category_id`.
  2. **Score par item** (0–100) à partir de **config/selection.php** :
     - **weights** (profil `default` ou `actu_focus`, etc.) : freshness, quality, seo, diversity, topic_frequency.
     - Fraîcheur : décroissance sur 7 jours (`decay_days`), bonus si < 48 h (hot_news_hours).
     - Qualité : `enrichedItem->quality_score`, bonus si mot count ≥ 1000.
     - SEO : mots-clés extraits (titre, lead, key_points, headings), estimation du poids SEO (longueur, termes thématiques).
  3. **Clustering par sujet** : extraction de mots-clés (stop words FR retirés), similarité **Jaccard** entre ensembles de mots-clés. Les items dont similarité ≥ `similarity_threshold` (défaut 20 %) sont regroupés. Limite par groupe : `max_items_per_topic` (défaut 5).
  4. **Score par groupe (topic)** : moyenne des scores des items + bonus diversité (plusieurs sources) + bonus « fréquence du sujet » (config `topic_frequency` : si beaucoup d’articles sur le même thème dans le pool, bonus jusqu’à `max_bonus`).
  5. **Type d’article suggéré** : si le plus récent item du groupe a < 48 h → `hot_news`, sinon si au moins 3 items → `long_form`, sinon `standard`. Les fourchettes de mots (`suggested_min_words`, `suggested_max_words`) viennent de **config/selection.php** → `article_types` (hot_news : 400–650, long_form : 1000–1800, standard : 800–1200).
  6. Tri par score décroissant, prise des N meilleures propositions, construction du `reasoning` et de `context_priority` (phrase du type « Sur X articles analysés, Y portent sur ce sujet (tendance). Ce sujet est prioritaire. »).

**Aucun appel OpenAI** dans cette étape : tout est calculé en PHP à partir de la config et des données enrichies.

**Fichiers** : `PipelineController::selectItems`, `ArticleSelectionService`, `config/selection.php` (weights, freshness, article_types, topic_frequency, clustering).

---

## Étape 9 — Générer un article (synthèse IA)

### Ce que tu fais

- **POST** `http://localhost:8000/api/articles/generate`
- **Headers** : `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Body** : `{"item_ids": ["uuid1", "uuid2", "uuid3"], "article_type": "long_form", "suggested_min_words": 1000, "suggested_max_words": 1800, "context_priority": "Sur 50 articles analysés, 12 portent sur ce sujet (tendance). Ce sujet est prioritaire."}` (exemple). Les `item_ids` viennent des propositions de l’étape 8.
- **Réponse** : 201, article créé (`id`, `title`, `excerpt`, `content` HTML, `meta_title`, `meta_description`, `keywords`, `reading_time`, `quality_score`, `status: draft`).

### Ce qui se passe dans le code

- **Route** : `POST /api/articles/generate` (admin) → `ArticleController::generate` → validation **GenerateArticleRequest** (item_ids requis, article_type, suggested_min_words, suggested_max_words, context_priority optionnels) → **ArticleGeneratorService::generate**.
- **ArticleGeneratorService** :
  1. Chargement des **RssItem** par `item_ids`, avec `enrichedItem`, `rssFeed.source`, `category`. Vérification que tous ont un `enrichedItem`.
  2. **CategoryTemplate** optionnel (si `category_id` fourni) pour ton, structure, min/max mots, règles SEO.
  3. **buildSystemPrompt** : utilise **config/selection.php** → `article_types` pour le type (hot_news / long_form / standard) → ton, fourchette de mots (sinon template catégorie). Instructions : ton, longueur obligatoire, HTML h2/h3, titre avec mot-clé, premier paragraphe, meta_title/meta_description/keywords, réponse en JSON. Si `articleType === 'hot_news'` ou `'long_form'`, une ligne d’instruction spécifique est ajoutée (brève vs article de fond).
  4. **buildUserPrompt** : pour chaque item, bloc « Source : titre (nom source) », URL, sujet principal, lead, points clés, mots-clés SEO, extrait du texte. Si **context_priority** est fourni, un bloc « Contexte de priorité » est ajouté. Liste des mots-clés SEO à intégrer. Optionnel : `custom_prompt`.
  5. **Appel OpenAI** (chat completions, `response_format: json_object`) avec ce system + user. Réponse attendue : `title`, `excerpt`, `content`, `meta_title`, `meta_description`, `keywords`.
  6. Nettoyage du contenu, calcul du **reading_time** (mots / 200), **quality_score** (longueur titre, nombre de mots, présence de h2/h3, nombre de keywords).
  7. Création de l’**Article** (status `draft`) et des **ArticleSource** (lien article ↔ rss_item, source, url). Mise à jour des `RssItem` en `status = 'used'`.

**Prompts** : construits dynamiquement dans `ArticleGeneratorService::buildSystemPrompt` et `buildUserPrompt` à partir de `config/selection.php` (article_types) et des CategoryTemplates. Pas de fichier de prompt séparé pour la génération ; tout est dans le code et la config.

**Fichiers** : `ArticleController::generate`, `GenerateArticleRequest`, `ArticleGeneratorService`, `config/selection.php`, modèles `Article`, `ArticleSource`.

---

## Étape 10 — Publier l’article

### Ce que tu fais

- **POST** `http://localhost:8000/api/articles/{article_id}/publish`
- **Headers** : `Authorization: Bearer {token}`
- **Réponse** : 200, article avec `status: published` et `published_at` renseigné.

### Ce qui se passe dans le code

- **Route** : `POST /api/articles/{article}/publish` → `ArticleController::publish`.
- **Autorisation** : policy `publish` sur l’article (admin).
- **Vérification** : `$article->isPublishable()` — le modèle **Article** exige `quality_score >= 60` et status `draft` ou `review`. Sinon réponse JSON d’erreur.
- Mise à jour : `status = 'published'`, `published_at = now()`.
- **Invalidation du cache** : `Cache::forget('vivat.hub.' . $article->category->slug)` et `Cache::forget('vivat.categories.index')` pour que les pages hub et la liste des catégories reflètent le nouvel article publié.

**Fichiers** : `ArticleController::publish`, `Article` (méthode `isPublishable`), policies.

---

## Récapitulatif des prompts et configs

| Étape | Où c’est défini | Rôle |
|-------|------------------|------|
| **4 – Enrichissement** | `EnrichContentJob::callOpenAI` (en dur) | System : analyste SEO, réponse JSON. User : titre + contenu + consignes (lead, headings, key_points, seo_keywords, primary_topic, quality_score, seo_score). |
| **7 – Analyse tendances** | `config/trends_analysis.php` | system_prompt + user_prompt_template avec `{csv_content}`. Demande connexions, tendances, meilleur sujet, poids, hot news vs de fond, fiche rédactionnelle. |
| **8 – Sélection** | `config/selection.php` | Poids (freshness, quality, seo, diversity, topic_frequency), seuils fraîcheur, article_types (hot_news, long_form, standard) avec min/max mots et ton, topic_frequency bonus, clustering (similarity_threshold). Pas d’appel OpenAI. |
| **9 – Génération article** | `ArticleGeneratorService` + `config/selection.php` | System : rédacteur SEO, ton et longueur depuis article_types (ou template catégorie), type hot_news/long_form si fourni. User : sources (titre, URL, lead, key_points, mots-clés, extrait) + contexte priorité + liste mots-clés à intégrer. |

---

## Fichiers principaux par étape

| Étape | Contrôleur / Route | Service / Job | Config / Modèles |
|-------|--------------------|----------------|-------------------|
| 1 | AuthController, routes api.php | Sanctum | User |
| 2 | PipelineController::fetchRss | FetchRssFeedJob, RssParserService | RssFeed, RssItem |
| 3 | PipelineController::status | — | RssFeed, RssItem |
| 4 | PipelineController::enrich | EnrichContentJob, ContentExtractorService | EnrichedItem, RssItem |
| 5 | PipelineController::status | — | — |
| 6 | PipelineController::exportTrendsCsv | buildCsvForTrends | — |
| 7 | PipelineController::analyzeTrends | TrendsAnalysisService | config/trends_analysis.php |
| 8 | PipelineController::selectItems | ArticleSelectionService | config/selection.php |
| 9 | ArticleController::generate | ArticleGeneratorService | config/selection.php, CategoryTemplate, Article, ArticleSource |
| 10 | ArticleController::publish | — | Article, cache |

---

## Génération du PDF

À partir du fichier Markdown `docs/ETAPES_1_A_10_EXPLICATION_COMPLETE.md` :

```bash
node docs/generate-pdf.cjs ETAPES_1_A_10_EXPLICATION_COMPLETE
```

Cela crée `docs/ETAPES_1_A_10_EXPLICATION_COMPLETE.html`. Pour obtenir le PDF : ouvrir ce HTML dans Chrome ou Safari → Cmd+P (Ctrl+P) → « Enregistrer en PDF » → format A4, marges par défaut.

---

## Annexe — Les deux prompts OpenAI en texte complet

Les deux prompts ci-dessous sont ceux envoyés à l’API OpenAI lors des étapes 4 (enrichissement) et 7 (analyse des tendances). Ils figurent ici en intégralité pour référence dans le PDF.

### 1) Prompt d’enrichissement (étape 4)

**Défini dans** : `app/Jobs/EnrichContentJob.php` (méthode `callOpenAI`).

**Message system :**

Tu es un analyste de contenu SEO expert. Tu analyses des articles et produis une analyse structurée avec des mots-clés SEO ciblés (longue traîne, spécifiques, faible concurrence). Privilégie les termes recherchés par les utilisateurs mais peu concurrentiels. Réponds uniquement en JSON.

**Message user :** *(les parties entre crochets sont remplies dynamiquement à partir de l’article scrapé)*

Titre: [titre de l'article scrapé]

Titres de sections: [liste des H2/H3 séparés par des virgules, max 10]

Contenu: [texte principal de l'article, tronqué à 6000 caractères]

Analyse ce contenu et génère un JSON avec :
- lead: résumé 1-2 phrases
- headings: tableau des titres H2/H3
- key_points: tableau de 3-7 points clés
- seo_keywords: tableau de 5-10 mots-clés SEO pertinents (termes spécifiques, pas génériques, longue traîne si possible)
- primary_topic: le sujet principal en 2-4 mots (ex: 'transition énergétique', 'biodiversité marine')
- quality_score: 0-100 (qualité rédactionnelle et informative)
- seo_score: 0-100 (potentiel SEO estimé : originalité du sujet, spécificité des mots-clés, intérêt de recherche)

---

### 2) Prompt d’analyse des tendances (étape 7)

**Défini dans** : `config/trends_analysis.php`.

**system_prompt :**

Tu es un expert en analyse éditoriale et en tendances pour un site média (environnement, santé, énergie, société). Tu reçois un export CSV d'articles issus de plusieurs sources (flux RSS récupérés et enrichis par IA). Chaque ligne = un article avec : date, title, category, source, primary_topic, seo_keywords, quality_score, seo_score, url, status. Tu réponds en français, de façon structurée et exploitable.

**user_prompt_template :** *(le placeholder `{csv_content}` est remplacé par le contenu CSV tronqué à max_csv_chars, défaut 45 000 caractères)*

Voici les données CSV des articles (séparateur ;). Le contenu tronqué du CSV est injecté à la place de `{csv_content}`.

Ta mission est de faire une analyse complète et structurée pour décider QUEL article nous devrions écrire et COMMENT (type d'article, longueur, angle). Réponds en français, de façon très claire et exploitable.

**1) CONNEXIONS ENTRE ARTICLES**
- Identifie les groupes d'articles qui parlent du même sujet ou de sujets très proches (mots-clés communs, primary_topic, titres).
- Pour chaque groupe important : donne un label de sujet, le nombre d'articles, les sources concernées, et une courte explication du lien entre eux (corrélation, tendance, actualité commune).

**2) CORRÉLATIONS ET TENDANCES**
- En t'appuyant sur les dates, les primary_topic et les seo_keywords : quelles tendances vois-tu (sujets qui reviennent souvent, pics par période, par source ou par catégorie) ?
- Quels sujets sont "chauds" (très présents récemment) vs plus "de fond" (récurrents sur la période) ?

**3) MEILLEUR SUJET POUR ÉCRIRE UN ARTICLE**
- En te basant sur les connexions et tendances : quel est LE meilleur sujet pour écrire un article maintenant ?
- Justifie en 3–5 phrases (pertinence, multi-sources, potentiel SEO, actualité).
- Propose un titre d'article et une phrase de contexte priorité (ex. : "Sur X articles analysés, Y portent sur ce sujet (tendance). Ce sujet est prioritaire.").

**4) POIDS À ATTRIBUER (recommandations)**
- Pour notre algorithme de sélection (critères : fraîcheur, qualité, SEO, diversité des sources, fréquence du sujet), quels poids recommandes-tu pour ce jeu de données (ex. : fraîcheur 30 %, qualité 25 %, SEO 25 %, diversité 10 %, fréquence sujet 10 %) ?
- Explique brièvement pourquoi (ex. : "Beaucoup d'actualité récente → augmenter fraîcheur.").

**5) HOT NEWS vs ARTICLE DE FOND**
- Parmi les groupes/sujets identifiés :
  - Lesquels qualifierais-tu de "hot news" (actualité chaude, brève) ? Pour chacun : indique la fourchette de mots recommandée (ex. : 400–650 mots) et le ton (percutant, factuel).
  - Lesquels qualifierais-tu d'article de fond (analyse, synthèse multi-sources) ? Pour chacun : indique la fourchette de mots recommandée (ex. : 1000–1800 mots) et le ton (approfondi, analytique).
- Pour le meilleur sujet que tu as choisi en (3) : précise si c'est un article "hot news" ou "de fond" et donne la fourchette de mots exacte et le ton à utiliser (contexte pour l'IA : "Ton article doit faire entre X et Y mots, [ton].").

Résume à la fin en une "fiche rédactionnelle" : sujet retenu, type (hot news / de fond), nombre de mots (min–max), ton, phrase de contexte priorité, et 3–5 mots-clés SEO à intégrer.

---

*Document rédigé pour le projet Vivat — Pipeline Content Acquisition.*
