# Création d’articles via Postman — Body JSON

Tu crées les articles toi-même. Voici la structure et un exemple de body complet (titre, sous-titre, date, temps de lecture, texte).

---

## Endpoint

- **Méthode** : `POST`
- **URL** : `http://localhost:8000/api/articles`
- **Headers** : `Accept: application/json`, `Content-Type: application/json`, `Authorization: Bearer {{token}}` (token admin)

---

## Champs du body (résumé)

| Champ | Type | Obligatoire | Description |
|-------|------|-------------|-------------|
| `title` | string | oui | Titre de l’article |
| `slug` | string | oui | URL unique (ex. `ia-revolution-medicale-sans-garde-fou`) |
| `excerpt` | string | non | Sous-titre / chapô |
| `content` | string | oui | Texte de l’article (HTML autorisé) |
| `meta_title` | string | non | Titre SEO (max 70 car.) |
| `meta_description` | string | non | Description SEO (max 160 car.) |
| `category_id` | uuid | non | ID d’une catégorie (GET /api/categories pour les avoir) |
| `reading_time` | integer | non | Temps de lecture en minutes (défaut 5) |
| `status` | string | non | `draft`, `review`, `published`, … (défaut `draft`) |
| `article_type` | string | non | `hot_news`, `long_form`, `standard` |
| `cover_image_url` | string | non | URL de l’image de couverture |
| `quality_score` | integer | non | 0–100. Mettre **≥ 60** (ex. 70) si tu veux publier juste après avec l’étape 2. |

Pour pouvoir **publier** juste après (étape 2), envoie au minimum : `status: "review"` et `quality_score: 70` (ou plus).

---

## Exemple de body complet (ton exemple)

**Requête 1 — Créer l’article**

Corps (raw JSON) :

```json
{
  "title": "IA : la révolution médicale avance sans garde-fou",
  "slug": "ia-revolution-medicale-sans-garde-fou",
  "excerpt": "Dans un nouveau rapport, l'OMS appelle les États à renforcer d'urgence les règles juridiques et éthiques pour protéger patients et soignants.",
  "content": "<p>En quelques années, l'intelligence artificielle a profondément changé la pratique médicale. Les médecins l'utilisent pour poser des diagnostics, alléger les tâches administratives ou encore mieux communiquer avec les patients. Mais qui est responsable lorsqu'un système d'IA se trompe ou cause un préjudice ? Apprenant à partir de données, l'IA en reproduit les biais ou les lacunes : elle peut manquer un diagnostic, proposer un traitement inadéquat ou accentuer les inégalités de santé.</p>\n\n<p>Dans son nouveau rapport <em>Artificial Intelligence in Health : State of Readiness across the WHO European Region</em>, l'OMS appelle les États à adapter leurs cadres juridiques et éthiques pour mieux protéger patients et soignants. Moins d'un pays sur dix dispose aujourd'hui de normes de responsabilité encadrant l'usage de l'IA en santé. Plus largement, seuls 4 des 50 États interrogés ont mis en place une stratégie nationale pour accompagner son développement.</p>\n\n<blockquote><p>Nous sommes à la croisée des chemins, déclare le docteur Natasha Azzopardi-Muscat, directrice de la division Systèmes de santé à l'OMS/Europe. Soit l'IA sera utilisée pour améliorer la santé et le bien-être des personnes, alléger la charge de travail de nos travailleurs de la santé épuisés et faire baisser les coûts des soins de santé, soit elle pourrait nuire à la sécurité des patients, compromettre la protection de la vie privée et creuser les inégalités en matière de soins.</p></blockquote>\n\n<h2>Des usages déjà bien réels…</h2>\n\n<ul>\n<li>32 pays (64 %) utilisent des outils d'IA pour assister le diagnostic, notamment en imagerie médicale.</li>\n<li>La moitié des pays ont introduit des chatbots pour l'engagement et le suivi des patients.</li>\n<li>26 pays ont identifié des priorités nationales pour l'usage de l'IA dans leurs systèmes de santé.</li>\n</ul>\n\n<h2>… mais sans filets juridiques ni stratégiques solides</h2>\n\n<ul>\n<li>Seuls 4 pays sur 50 disposent d'une stratégie nationale dédiée à l'IA en santé.</li>\n<li>Moins d'un pays sur 10 a mis en place des normes juridiques de responsabilité qui déterminent qui est tenu pour responsable lorsqu'un système d'IA se trompe ou cause un préjudice.</li>\n<li>86 % des pays considèrent l'incertitude juridique comme le principal frein à l'adoption de l'IA, et 78 % évoquent des contraintes financières importantes.</li>\n</ul>",
  "meta_title": "IA : la révolution médicale avance sans garde-fou",
  "meta_description": "Dans un nouveau rapport, l'OMS appelle les États à renforcer d'urgence les règles juridiques et éthiques pour protéger patients et soignants.",
  "reading_time": 2,
  "status": "review",
  "quality_score": 70,
  "article_type": "hot_news",
  "cover_image_url": "https://picsum.photos/1200/800?random=med"
}
```

Remplace `category_id` si tu veux lier à une catégorie : récupère les IDs avec **GET** `http://localhost:8000/api/categories` (route admin) ou **GET** `http://localhost:8000/api/public/categories`, puis ajoute par exemple `"category_id": "uuid-de-la-categorie"`.

**Réponse** : 201 avec l’article créé (dont `id`). Note l’`id` pour l’étape 2.

---

## Requête 2 — Publier l’article

- **Méthode** : `POST`
- **URL** : `http://localhost:8000/api/articles/{id}/publish`  
  (remplacer `{id}` par l’`id` reçu à l’étape 1)
- **Headers** : `Accept: application/json`, `Authorization: Bearer {{token}}`
- **Body** : aucun (ou `{}`)

**Réponse** : 200 avec l’article en `status: "published"` et `published_at` renseigné (date du jour).

---

## (Optionnel) Changer la date de publication

Si tu veux une date affichée du type « Publié le 29 décembre 2025 » au lieu de la date du jour :

- **Méthode** : `PUT`
- **URL** : `http://localhost:8000/api/articles/{id}`
- **Headers** : `Accept: application/json`, `Content-Type: application/json`, `Authorization: Bearer {{token}}`
- **Body** (raw JSON) :
```json
{
  "published_at": "2025-12-29T12:00:00.000000Z"
}
```

Format : ISO 8601 (`YYYY-MM-DDTHH:mm:ss.ssssssZ`).

---

## Récap pour un article avec vrai texte

1. **POST** `/api/articles` avec le body ci-dessus (titre, slug, excerpt, content, reading_time, status, quality_score, etc.).
2. **POST** `/api/articles/{id}/publish` avec l’`id` de l’étape 1.
3. (Optionnel) **PUT** `/api/articles/{id}` avec `published_at` pour fixer la date d’affichage.

Ensuite **GET** `http://localhost:8000/api/public/home` pour voir l’article sur la home (si `article_type: "hot_news"` et qu’il est le plus récent, il peut apparaître en top_news).
