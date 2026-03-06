# Workflow mentor : Scraper 500–1000 articles par source × 3 sources → CSV → analyse IA

Ce document décrit le **workflow demandé par le mentor** : scraper un gros volume d’articles (500–1000 par source sur 3 sources différentes), tout mettre dans un CSV/Excel, puis faire analyser par ChatGPT (avec un **prompt fort**) pour trouver les **connexions entre articles**, les **tendances**, le **meilleur sujet** pour écrire un article, des **poids** différents et identifier **hot news** vs **articles de fond** (avec contrainte de longueur en mots). Tout doit être **très bien expliqué**.

---

## 1. Ordre des étapes (ce qui est fait ou pas)

| Étape | Description | Fait dans le projet |
|-------|-------------|---------------------|
| 1 | Scraper 500–1000 articles **par source** sur **3 sources** différentes | **Partiel** : le fetch RSS récupère tout ce que chaque flux renvoie (souvent 10–50 par fetch). Pour atteindre 500–1000 par source, il faut **accumuler** en lançant le fetch régulièrement (ex. 1×/jour pendant des semaines) ou avoir plusieurs flux RSS par source. |
| 2 | Mettre tout dans un **CSV ou Excel** | **Oui** : commande `pipeline:export-trends-csv` avec options `--per-source=1000` et `--sources=3`. |
| 3 | Enrichir les articles (avant ou après le CSV ?) | **Recommandé AVANT** le CSV : une fois enrichis, les articles ont `primary_topic`, `seo_keywords`, `quality_score`, `seo_score`, ce qui permet à ChatGPT de trouver des **connexions et corrélations** bien plus pertinentes. Ordre retenu : **Fetch RSS → Enrichir → Exporter CSV**. |
| 4 | Demander à **l’IA** (prompt fort) d’analyser le CSV | **Automatisé** : le prompt est prédéfini dans `config/trends_analysis.php`. La commande `pipeline:analyze-trends` et l’API `POST /api/pipeline/analyze-trends` envoient le CSV à l’API OpenAI et récupèrent l’analyse (connexions, tendances, meilleur sujet, poids, hot news vs article de fond, fiche rédactionnelle). Tu peux aussi faire l’analyse **à la main** dans ChatGPT avec le même prompt (section 3). |
| 5 | Utiliser le résultat de l’analyse pour **générer l’article** | **Oui** : une fois que ChatGPT a rendu son analyse (meilleur sujet, type d’article, nombre de mots), tu peux appeler l’API de génération avec `article_type`, `suggested_min_words`, `suggested_max_words`, `context_priority` (voir LOGIQUE_SELECTION_ET_PROMPTS.md). |

---

## 2. Détail de ce qui est fait en code

### 2.1 Scraping (ingestion RSS)

- **Fetch RSS** : `POST /api/pipeline/fetch-rss` avec `{"all": true}` ou `php artisan rss:fetch --all`.
- Chaque flux RSS est récupéré ; les nouveaux articles sont stockés dans `rss_items` (statut `new`).
- **Limite** : un flux RSS typique renvoie 10–50 articles par appel. Pour avoir 500–1000 articles **par source**, il faut soit :
  - lancer le fetch **régulièrement** (ex. 1×/jour) pendant plusieurs semaines pour accumuler les items ;
  - soit configurer **plusieurs flux RSS par source** (si les médias le proposent).

### 2.2 Enrichissement (avant le CSV, recommandé)

- **Enrichir** : `POST /api/pipeline/enrich` avec `{"limit": 50}` ou `php artisan content:enrich --limit=50`.
- Pour chaque item `new`, le système scrape la page, appelle OpenAI pour extraire : lead, key_points, primary_topic, seo_keywords, quality_score, seo_score.
- Les items passent en statut `enriched`. Le CSV exporté contient alors des colonnes utiles pour l’analyse IA : `primary_topic`, `seo_keywords`, `quality_score`, `seo_score`.

### 2.3 Export CSV (500–1000 par source × 3 sources)

Commande (à lancer **dans le conteneur Docker** si ton app tourne avec Docker, sinon MySQL ne sera pas joignable) :

```bash
docker compose exec app php artisan pipeline:export-trends-csv --per-source=1000 --sources=3 --status=enriched --output=trends_mentor.csv
```

Si tu lances Artisan directement sur ta machine (sans Docker), utilise `php artisan ...` mais assure-toi que MySQL est accessible (ex. `DB_HOST=127.0.0.1` dans `.env`).

Le fichier est créé dans `storage/app/trends_mentor.csv` (dans le conteneur). Pour le récupérer sur ton Mac (chemin absolu requis pour `docker compose cp`) :

```bash
docker compose cp app:/var/www/html/storage/app/trends_mentor.csv ./
```

- **`--per-source=1000`** : au maximum 1000 articles par source (objectif 500–1000 par source).
- **`--sources=3`** : on prend les **3 sources** qui ont le plus d’items (les plus “remplies”).
- **`--status=enriched`** : uniquement les articles déjà enrichis (recommandé pour avoir sujets et mots-clés dans le CSV).
- **`--output=...`** : fichier de sortie (par défaut dans `storage/app/trends_export_YYYY-MM-DD.csv`).

Colonnes du CSV (séparateur `;`, UTF-8 avec BOM pour Excel) :

- **date** — date de publication ou de fetch  
- **title** — titre de l’article  
- **category** — catégorie (ex. Environnement, Santé)  
- **source** — nom de la source (ex. Reporterre, Futura Sciences)  
- **primary_topic** — sujet principal (rempli après enrichissement)  
- **seo_keywords** — mots-clés SEO (séparés par ` | `)  
- **quality_score** — score qualité 0–100  
- **seo_score** — score SEO 0–100  
- **url** — lien vers l’article original  
- **status** — new / enriched / used  

Tu peux ouvrir ce fichier dans **Excel** ou LibreOffice.

### 2.4 Analyse des tendances par l’IA (automatisée)

Le **même prompt** que ci‑dessous (section 3) est utilisé automatiquement par l’application via l’API OpenAI :

- **Commande Artisan** (CSV depuis la BDD ou depuis un fichier) :
  ```bash
  docker compose exec app php artisan pipeline:analyze-trends
  ```
  Options : `--csv=storage/app/trends_mentor.csv` pour analyser un fichier ; sinon le CSV est généré depuis la BDD avec `--limit=500`, `--per-source=`, `--sources=3`, `--status=`. L’analyse est affichée et sauvegardée dans `storage/app/trends_analysis_YYYY-MM-DD.txt`.

- **API** (admin, authentification requise) :
  ```http
  POST /api/pipeline/analyze-trends
  Authorization: Bearer {token}
  Content-Type: application/json

  {}
  ```
  Le corps peut être vide : le CSV est alors généré depuis la BDD (500 lignes par défaut). Ou envoie `limit`, `per_source`, `sources`, `status` pour paramétrer.  
  **Alternative** : envoie un fichier CSV en `multipart/form-data` avec la clé `csv_file` ; l’IA analysera ce fichier.

Réponse : `{ "success": true, "analysis": "..." }` (texte complet de l’analyse : connexions, tendances, meilleur sujet, poids, hot news vs article de fond, fiche rédactionnelle).

Le prompt utilisé est défini dans **`config/trends_analysis.php`** (`system_prompt` et `user_prompt_template`). Tu peux le modifier pour adapter le comportement de l’IA.

---

## 3. Prompt fort (analyse du CSV)

Ce prompt est **prédéfini** dans `config/trends_analysis.php` et utilisé automatiquement par la commande `pipeline:analyze-trends` et l’API `POST /api/pipeline/analyze-trends`. Tu peux aussi le copier-coller dans ChatGPT et joindre le CSV pour une analyse manuelle.

---

### Prompt à utiliser avec ChatGPT

```
Tu es un expert en analyse éditoriale et en tendances pour un site média (environnement, santé, énergie, société). Je te transmets un export CSV d’articles issus de plusieurs sources (flux RSS récupérés et enrichis par IA). Chaque ligne = un article avec : date, title, category, source, primary_topic, seo_keywords, quality_score, seo_score, url, status.

Ta mission est de faire une analyse complète et structurée pour décider QUEL article nous devrions écrire et COMMENT (type d’article, longueur, angle). Réponds en français, de façon très claire et exploitable.

1) CONNEXIONS ENTRE ARTICLES  
   - Identifie les groupes d’articles qui parlent du même sujet ou de sujets très proches (mots-clés communs, primary_topic, titres).  
   - Pour chaque groupe important : donne un label de sujet, le nombre d’articles, les sources concernées, et une courte explication du lien entre eux (corrélation, tendance, actualité commune).

2) CORRÉLATIONS ET TENDANCES  
   - En t’appuyant sur les dates, les primary_topic et les seo_keywords : quelles tendances vois-tu (sujets qui reviennent souvent, pics par période, par source ou par catégorie) ?  
   - Quels sujets sont “chauds” (très présents récemment) vs plus “de fond” (récurrents sur la période) ?

3) MEILLEUR SUJET POUR ÉCRIRE UN ARTICLE  
   - En te basant sur les connexions et tendances : quel est LE meilleur sujet pour écrire un article maintenant ?  
   - Justifie en 3–5 phrases (pertinence, multi-sources, potentiel SEO, actualité).  
   - Propose un titre d’article et une phrase de contexte priorité (ex. : “Sur X articles analysés, Y portent sur ce sujet (tendance). Ce sujet est prioritaire.”).

4) POIDS À ATTRIBUER (recommandations)  
   - Pour notre algorithme de sélection (critères : fraîcheur, qualité, SEO, diversité des sources, fréquence du sujet), quels poids recommandes-tu pour ce jeu de données (ex. : fraîcheur 30 %, qualité 25 %, SEO 25 %, diversité 10 %, fréquence sujet 10 %) ?  
   - Explique brièvement pourquoi (ex. : “Beaucoup d’actualité récente → augmenter fraîcheur.”).

5) HOT NEWS vs ARTICLE DE FOND  
   - Parmi les groupes/sujets identifiés :  
     - Lesquels qualifierais-tu de “hot news” (actualité chaude, brève) ? Pour chacun : indique la fourchette de mots recommandée (ex. : 400–650 mots) et le ton (percutant, factuel).  
     - Lesquels qualifierais-tu d’“article de fond” (analyse, synthèse multi-sources) ? Pour chacun : indique la fourchette de mots recommandée (ex. : 1000–1800 mots) et le ton (approfondi, analytique).  
   - Pour le meilleur sujet que tu as choisi en (3) : précise si c’est un article “hot news” ou “de fond” et donne la fourchette de mots exacte et le ton à utiliser (contexte pour l’IA : “Ton article doit faire entre X et Y mots, [ton].”).

Résume à la fin en une “fiche rédactionnelle” : sujet retenu, type (hot news / de fond), nombre de mots (min–max), ton, phrase de contexte priorité, et 3–5 mots-clés SEO à intégrer.
```

---

## 4. Utiliser le résultat de l’analyse pour générer l’article

Une fois que ChatGPT a rendu sa “fiche rédactionnelle” :

1. Dans l’appli, récupère les **item_ids** des articles sources correspondant au sujet retenu (via `GET /api/pipeline/select-items?count=5` ou en filtrant en base / phpMyAdmin).
2. Appelle la génération avec les paramètres issus de l’analyse :
   - **article_type** : `hot_news` ou `long_form` (ou `standard`)
   - **suggested_min_words** / **suggested_max_words** : la fourchette donnée par ChatGPT (ex. 1000–1800 pour un article de fond)
   - **context_priority** : la phrase de contexte priorité fournie par ChatGPT

Exemple d’appel API :

```http
POST /api/articles/generate
Content-Type: application/json

{
  "item_ids": ["uuid1", "uuid2", "uuid3"],
  "article_type": "long_form",
  "suggested_min_words": 1000,
  "suggested_max_words": 1800,
  "context_priority": "Sur 150 articles analysés, 12 portent sur la transition énergétique en Europe (tendance). Ce sujet est prioritaire."
}
```

L’IA de génération utilisera ce contexte pour adapter le ton et la longueur (voir LOGIQUE_SELECTION_ET_PROMPTS.md et ArticleGeneratorService).

---

## 5. Résumé : workflow complet (très bien expliqué)

1. **Scraper** : lancer régulièrement le fetch RSS (et l’enrichissement) pour accumuler assez d’articles (objectif 500–1000 par source sur 3 sources). En pratique, les flux RSS donnent 10–50 items par run ; il faut donc plusieurs runs (ex. quotidien) pour atteindre le volume.
2. **Enrichir** : avant d’exporter, enrichir les items (`new` → `enriched`) pour avoir primary_topic, seo_keywords, scores dans le CSV.
3. **Exporter en CSV** (optionnel si tu utilises l’analyse automatique) : `php artisan pipeline:export-trends-csv --per-source=1000 --sources=3 --status=enriched`. Ou passer directement à l’étape 4.
4. **Analyser avec l’IA** : **automatique** — `php artisan pipeline:analyze-trends` ou `POST /api/pipeline/analyze-trends` (avec ou sans fichier CSV). L’IA (OpenAI) lit le CSV et renvoie connexions, corrélations, tendances, meilleur sujet, poids, hot news vs article de fond avec longueur en mots et ton. **Manuel** : ouvrir le CSV dans ChatGPT et coller le prompt de la section 3.
5. **Générer l’article** : avec la fiche rédactionnelle (sujet, type, min/max mots, context_priority), appeler l’API de génération avec les bons `item_ids` et paramètres. L’article généré respecte le type (hot news vs de fond) et la contrainte de mots.

Tout est expliqué ici de façon détaillée pour que ton mentor (et toi) puissiez suivre le flux de bout en bout et comprendre où chaque chose est faite (code vs manuel) et dans quel ordre.

---

*Dernière mise à jour : février 2026*
