# Tests Home Checklist Postman

Toutes les fonctionnalités de la page d’accueil à tester. **Prérequis** : 9 catégories créées + 10 articles (voir `POSTMAN_9_CATEGORIES_BODIES.md` et `POSTMAN_10_ARTICLES_HOME_BODIES.md`, ou `php artisan db:seed --class=HomeArticlesSeeder`).

**Headers communs** (endpoints publics) : `Accept: application/json`. Pas de token pour les requêtes ci‑dessous sauf mention.

---

## 1. GET /api/public/home (données complètes home)

| Méthode | URL | Description |
|--------|-----|-------------|
| **GET** | `http://localhost:8000/api/public/home` | Toutes les données pour la page d’accueil |

### À vérifier dans la réponse

| Champ | Attendu |
|-------|---------|
| **top_news** | 1 article ou `null`. Si présent : `display_type: "top_news"`, `article_type: "hot_news"`, `cover_image_url` renseigné, `category` chargé. |
| **featured** | Tableau (max 4 par défaut). Chaque élément a `display_type: "featured"`, soit `article_type: "hot_news"` soit `cover_image_url` non vide. |
| **latest** | `{ "label": "Dernières actualités", "articles": [ ... ] }`. Chaque article a `display_type: "standard"` (avec image) ou `"secondary"` (sans image). |
| **categories** | 9 catégories avec `id`, `name`, `slug`, `description`, `home_order` (1–9), `image_url`, `published_articles_count`. Tri par `home_order`. |
| **writer_cta** | `signup_url`, `dashboard_url`, `is_authenticated_as_contributor` (sans token = `false`). |

### display_type (résumé)

- **top_news** : 1 article mis en avant (carré grand, image en fond).
- **featured** : articles importants (carrés moyens, image en fond).
- **standard** : article avec petite image.
- **secondary** : article sans image.

---

## 2. GET /api/public/articles (Show all / pagination)

| Méthode | URL | Description |
|--------|-----|-------------|
| **GET** | `http://localhost:8000/api/public/articles` | Liste des articles publiés (page 1, 12 par page par défaut). |
| **GET** | `http://localhost:8000/api/public/articles?page=2&per_page=12` | Page 2, 12 par page. |
| **GET** | `http://localhost:8000/api/public/articles?category=sante` | Filtre par slug de catégorie. |
| **GET** | `http://localhost:8000/api/public/articles?sort=reading_time&dir=asc` | Tri (sort : `published_at`, `reading_time`, `quality_score`, `title` ; dir : `asc` ou `desc`). |

### À vérifier

- Réponse : `data` (tableau d’articles), `meta` (`current_page`, `last_page`, `per_page`, `total`), `links` (`prev`, `next`).
- Chaque article : `id`, `title`, `slug`, `excerpt`, `category`, `reading_time`, `published_at`, `article_type`, `cover_image_url` (pas de `display_type` ici).

---

## 3. GET /api/public/articles/{slug} (détail d’un article)

| Méthode | URL | Description |
|--------|-----|-------------|
| **GET** | `http://localhost:8000/api/public/articles/ia-revolution-medicale-sans-garde-fou` | Détail par slug (ex. article 1 du seeder). |

### À vérifier

- Réponse 200 : article avec `content` inclus, `category` chargée.
- Réponse 404 si slug inconnu.

---

## 4. GET /api/public/categories (liste des rubriques)

| Méthode | URL | Description |
|--------|-----|-------------|
| **GET** | `http://localhost:8000/api/public/categories` | Toutes les catégories (avec `published_articles_count`). |

### À vérifier

- 9 catégories avec `home_order` 1–9, `image_url`, etc. Utile pour la section « Découvrez vos rubriques préférées » ou pour les filtres.

---

## 5. GET /api/public/categories/{slug}/hub (page hub d’une rubrique)

| Méthode | URL | Description |
|--------|-----|-------------|
| **GET** | `http://localhost:8000/api/public/categories/finance/hub` | Page hub « Finance » : catégorie, description, sous-catégories (max 5), à la une, dernières actualités avec display_type. |
| **GET** | `http://localhost:8000/api/public/categories/finance/hub?sub_category=epargne` | Même hub filtré par sous-catégorie (slug). |

### À vérifier

- Réponse : `category`, `description`, `total_published`, **`sub_categories`** (tableau, max 5), **`featured`** (articles avec `display_type: "featured"`), **`latest`** : `{ "label": "Dernières actualités", "articles": [ ... ] }` avec **`display_type`** par article : `standard` ou `secondary`. Même logique que la home (image/vidéo = standard, sinon secondary).
- Les articles peuvent avoir **`cover_image_url`** et **`cover_video_url`** ; les catégories/sous-catégories **`image_url`** et **`video_url`**.

---

## 6. writer_cta (bouton « Rédiger un article »)

- **Sans token** : `GET /api/public/home` → `writer_cta.is_authenticated_as_contributor` = `false` → le front affiche un lien vers `signup_url` (ex. `/register`).
- **Avec token contributor ou admin** : envoyer `Authorization: Bearer {{token}}` sur **GET /api/public/home** (si l’API utilise le token pour writer_cta). Vérifier que `is_authenticated_as_contributor` = `true` et utiliser `dashboard_url` (ex. `/contributor/submissions`).

*Note : aujourd’hui le home est en cache et ne reçoit pas toujours le user ; si writer_cta est toujours `false` même avec un token, le front peut appeler **GET /api/auth/me** pour savoir si l’utilisateur est connecté et a le rôle contributor/admin.*

---

## 7. Cache home

- Après **GET /api/public/home**, modifier ou publier un article (admin) puis rappeler **GET /api/public/home** : les données doivent être à jour (cache invalidé à la publication / modification d’article publié et à la modification des catégories).
- TTL du cache : 5 min par défaut (`config/vivat.php` → `home_cache_ttl`).

---

## Récap des endpoints Home à tester

| # | Méthode | Endpoint | Rôle |
|---|---------|----------|------|
| 1 | GET | `/api/public/home` | Données complètes home (top_news, featured, latest, categories, writer_cta) |
| 2 | GET | `/api/public/articles?page=1&per_page=12` | Pagination « Show all » |
| 3 | GET | `/api/public/articles?category=sante` | Filtre par catégorie |
| 4 | GET | `/api/public/articles/{slug}` | Détail d’un article |
| 5 | GET | `/api/public/categories` | Liste des 9 rubriques |
| 6 | GET | `/api/public/categories/{slug}/hub` | Page hub d’une rubrique |

---

## Option : recréer les 10 articles (admin)

- **POST** `http://localhost:8000/api/seed-home-articles`  
- **Headers** : `Accept: application/json`, `Authorization: Bearer {{token}}` (token admin).  
- **Body** : aucun.  
- Remplace les 10 articles de test (mêmes slugs) et republie. Alternative en CLI : `php artisan db:seed --class=HomeArticlesSeeder` (voir `POSTMAN_10_ARTICLES_HOME_BODIES.md`).
