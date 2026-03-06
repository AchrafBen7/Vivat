# Logique de sélection, règles prédéfinies et prompts

Ce document décrit comment l’IA et le système font leurs choix (sélection d’articles, type d’article, longueur), les **règles prédéfinies** configurables, et comment **tester les prompts** et utiliser l’**export tendances** (data science / ChatGPT).

---

## 1. Règles prédéfinies (config)

Fichier : **`config/selection.php`**

### Profil de pondération

Variable d’environnement : `SELECTION_WEIGHT_PROFILE` (défaut : `default`).

| Profil | Objectif |
|--------|----------|
| **default** | Équilibre fraîcheur / qualité / SEO / diversité / fréquence sujet |
| **actu_focus** | Plus connecté à l’actu : plus de poids fraîcheur + fréquence sujet |
| **seo_focus** | Priorité au potentiel SEO |
| **long_form_focus** | Favorise les sujets multi-sources pour articles de fond |

### Poids des critères (total = 100)

- **freshness** : article récent = plus pertinent (actu).
- **quality** : qualité du contenu extrait (score enrichissement).
- **seo** : potentiel SEO (mots-clés, concurrence).
- **diversity** : multi-sources = plus de valeur.
- **topic_frequency** : si beaucoup d’articles sur le même sujet (ex. 10/50), ce sujet est plus important → bonus (corrélation, tendance).

Les poids exacts par profil sont dans `config/selection.php` → `weights`.

### Fréquence du sujet (corrélation)

- **topic_frequency.enabled** : activer/désactiver le bonus.
- **ratio_threshold** : seuil (ex. 0,10 = 10 % du pool) à partir duquel le sujet est considéré comme prioritaire.
- **max_bonus** : plafond du bonus en points.

Exemple : 50 articles au total, 10 sur le même thème → ratio 20 % → bonus appliqué, le sujet “ressort” et est prioritaire.

### Type d’article et longueur

- **hot_news** : brève / actualité chaude (< 48 h) → 400–650 mots, ton percutant.
- **long_form** : article de fond (plusieurs sources) → 1000–1800 mots, analytique.
- **standard** : entre les deux → 800–1200 mots.

Seuils (hot_news_hours, etc.) dans `config/selection.php` → `freshness` et `article_types`.

---

## 2. Comment l’IA fait un choix

1. **Sélection (select-items)**  
   Le service calcule pour chaque item un score (fraîcheur, qualité, SEO) avec les **poids du profil** en cours.  
   Il regroupe les items par similarité de sujet (mots-clés, Jaccard).  
   Pour chaque groupe, il ajoute le **bonus fréquence du sujet** (si le sujet représente une part significative du pool).  
   Il trie les groupes par score et retourne les N meilleures propositions avec :
   - **reasoning** : pourquoi cet article (sources, qualité, SEO, fraîcheur, priorité sujet).
   - **suggested_article_type** : hot_news | long_form | standard.
   - **suggested_min_words** / **suggested_max_words** : fourchette de mots cible.
   - **context_priority** : phrase réutilisable dans le prompt de génération (ex. “Sur 50 articles analysés, 10 portent sur ce sujet (tendance). Ce sujet est prioritaire.”).

2. **Génération (generate)**  
   L’IA reçoit :
   - **Contexte priorité** : `context_priority` (injecté dans le user prompt).
   - **Type d’article** : hot_news / long_form / standard (ton + longueur dans le system prompt).
   - **Longueur cible** : `suggested_min_words` / `suggested_max_words` (ou template catégorie) dans le system prompt.

Donc le choix est explicite : règles en config, score avec bonus fréquence, type et longueur dérivés automatiquement (ou overridés par l’API).

---

## 3. Variantes de poids

Pour “mettre du poids” dans le contenu et éviter “sortir tout et n’importe quoi” :

- Utiliser **actu_focus** si tu veux être très connecté à l’actu.
- Augmenter **topic_frequency** (poids + max_bonus) pour que les sujets qui reviennent souvent (corrélation) soient nettement favorisés.
- Utiliser **long_form_focus** pour privilégier les synthèses multi-sources (articles de fond).

Tu peux aussi créer un nouveau profil dans `config/selection.php` (ex. `trends_focus`) et le définir via `SELECTION_WEIGHT_PROFILE`.

---

## 4. Tester les prompts

Pour être sûr que l’IA ressort ce que tu veux :

1. **Sélection**  
   - Appeler `GET /api/pipeline/select-items?count=3`.  
   - Vérifier que les propositions ont un `reasoning` clair, un `suggested_article_type` cohérent (ex. item < 48 h → hot_news) et des `suggested_min_words` / `suggested_max_words` corrects.

2. **Génération**  
   - Choisir une proposition et noter `item_ids`, `suggested_article_type`, `suggested_min_words`, `suggested_max_words`, `context_priority`.  
   - Appeler `POST /api/articles/generate` avec :
     - `item_ids`
     - `article_type` = `suggested_article_type`
     - `suggested_min_words` / `suggested_max_words`
     - `context_priority` (copier depuis la proposition).  
   - Vérifier en sortie : longueur (nombre de mots), ton (brève vs analytique), présence des mots-clés et du sujet prioritaire.

3. **Tests manuels ciblés**  
   - **Hot news** : prendre des items très récents (< 48 h), générer avec `article_type=hot_news` et 400–650 mots ; vérifier que le texte est court et percutant.  
   - **Article de fond** : prendre 3+ sources sur le même sujet, générer avec `article_type=long_form` et 1000–1800 mots ; vérifier structure (h2/h3) et profondeur.

4. **Régression**  
   Si tu modifies les prompts dans `ArticleGeneratorService` (ou les libellés dans `config/selection.php`), refaire au moins un test de chaque type ci-dessus.

---

## 5. Trends : gros volumes et workflow mentor (CSV + ChatGPT)

Objectif : scraper **500–1000 articles par source** sur **3 sources** différentes, tout mettre dans un **CSV/Excel**, puis faire analyser par **ChatGPT** (avec un **prompt fort**) pour trouver : connexions entre articles, corrélations, tendances, meilleur sujet pour écrire un article, poids à attribuer, et identifier **hot news** vs **articles de fond** (avec contrainte de mots). Tout est détaillé dans **`docs/WORKFLOW_MENTOR_CSV_ET_IA.md`**.

### Export CSV (par source × N sources)

Commande pour le workflow mentor (500–1000 par source × 3 sources) :

```bash
php artisan pipeline:export-trends-csv --per-source=1000 --sources=3 --status=enriched
```

Options :

- **`--per-source=1000`** : max d’items par source (objectif 500–1000).
- **`--sources=3`** : nombre de sources à inclure (les 3 avec le plus d’items).
- **`--limit=1000`** : nombre max total d’items (utilisé si `--per-source` n’est pas fourni).
- **`--status=enriched`** : filtrer par statut (recommandé : enrichir avant d’exporter).
- **`--output=mon_export.csv`** : fichier de sortie (défaut : `storage/app/trends_export_YYYY-MM-DD.csv`).

Colonnes du CSV : **date**, **title**, **category**, **source**, **primary_topic**, **seo_keywords**, **quality_score**, **seo_score**, **url**, **status**.

### Ordre recommandé : Fetch RSS → Enrichir → Exporter CSV → Analyser avec ChatGPT

1. Accumuler les articles (fetch RSS régulier, puis enrichissement).
2. Exporter avec `--per-source=1000 --sources=3 --status=enriched`.
3. Ouvrir le CSV dans Excel (ou envoyer un extrait à ChatGPT).
4. Utiliser le **prompt fort** décrit dans `docs/WORKFLOW_MENTOR_CSV_ET_IA.md` pour que ChatGPT retourne : connexions, corrélations, tendances, meilleur sujet, poids, hot news vs article de fond (avec longueur en mots).
5. Utiliser la “fiche rédactionnelle” de ChatGPT pour appeler `POST /api/articles/generate` avec `article_type`, `suggested_min_words`, `suggested_max_words`, `context_priority`.

### Nettoyer les données

- Ouvrir le CSV (Excel, LibreOffice, ou script).
- Vérifier encodage (UTF-8 avec BOM déjà écrit par la commande).
- Supprimer ou corriger les lignes incohérentes (titres vides, doublons évidents, etc.) selon ton besoin.

### Ancienne section (analyse libre avec ChatGPT)

Tu peux aussi envoyer (ou coller un extrait) du CSV et demander par exemple :

- “Quels titres / sujets ressortent le plus dans ce jeu de données ?”
- “En fonction de la date, de la source, de la catégorie, qu’est-ce qui ressort ?”
- “Donne-moi les thèmes min, top, et des références (exemples de titres).”
- “Résume les tendances pour que je puisse améliorer les consignes de rédaction (prompts) pour une IA.”

Les réponses peuvent servir à :
- Ajuster les mots-clés à haute valeur dans `ArticleSelectionService` (ou en config).
- Enrichir le **context_priority** ou les instructions dans le prompt de génération.
- Définir des profils ou règles supplémentaires dans `config/selection.php`.

### Intégration dans le prompt

Une fois que tu as des tendances (phrases, thèmes prioritaires, contraintes), tu peux :

- Soit les mettre en **instructions supplémentaires** (`custom_prompt`) lors de l’appel à `POST /api/articles/generate`.
- Soit les intégrer dans le **system prompt** ou le **user prompt** dans `ArticleGeneratorService` (ex. section “Contexte tendances” basée sur une config ou un cache).

---

## 6. Résumé des fichiers modifiés / ajoutés

| Fichier | Rôle |
|---------|------|
| `config/selection.php` | Règles prédéfinies, poids par profil, fréquence sujet, types d’article (hot_news, long_form, standard). |
| `app/Services/ArticleSelectionService.php` | Poids depuis la config, bonus “fréquence du sujet”, sortie suggested_article_type, suggested_min/max_words, context_priority. |
| `app/Services/ArticleGeneratorService.php` | Paramètres articleType, minWords, maxWords, contextPriority ; injection dans system et user prompts. |
| `app/Http/Requests/GenerateArticleRequest.php` | Champs optionnels : article_type, suggested_min_words, suggested_max_words, context_priority. |
| `app/Http/Controllers/Api/ArticleController.php` | Passe les nouveaux champs à `generate()` et à `GenerateArticleJob`. |
| `app/Jobs/GenerateArticleJob.php` | Nouveaux paramètres pour la génération asynchrone. |
| `app/Console/Commands/ExportTrendsCsvCommand.php` | Commande `pipeline:export-trends-csv` (options --per-source, --sources pour workflow mentor). Voir docs/WORKFLOW_MENTOR_CSV_ET_IA.md. |
| `docs/WORKFLOW_MENTOR_CSV_ET_IA.md` | Workflow mentor : 500-1000 par source x 3, CSV, prompt fort ChatGPT. |
| `docs/LOGIQUE_SELECTION_ET_PROMPTS.md` | Ce document. |

---

*Dernière mise à jour : février 2026*
