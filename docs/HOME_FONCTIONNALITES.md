# Fonctionnalités Home — Spécification API (backend)

Ce document décrit les **fonctionnalités de la page d'accueil** et les **endpoints / données** fournis par le backend pour que le frontend puisse les implémenter sans coder le front.

---

## 1) Tous les articles (4 types visuels)

Le front reçoit les articles via **GET /api/public/home** (sections `top_news`, `featured`, `latest`) et peut charger plus via **GET /api/public/articles** (pagination = "Show all").

Chaque article exposé contient au minimum : `id`, `title`, `slug`, `excerpt`, `category`, `reading_time`, `published_at`, `article_type`, `cover_image_url`, et en contexte home un **`display_type`** qui indique comment l'afficher :

| display_type | Rôle visuel | Données à afficher |
|--------------|-------------|---------------------|
| **top_news** | 1.1 — Article le plus important, carré grand, image en fond | Image bg (`cover_image_url`), label "Top news", **titre**, **sous-titre** (`excerpt`), date, temps de lecture |
| **featured** | 1.2 — Article important mais pas hot news, carré plus petit | Image en bg, **titre** (pas de sous-titre), **catégorie**, date de publication, temps de lecture |
| **standard** | 1.3 — Article moyen / régulier | **Petite image** (pas en bg), titre, catégorie, date, temps de lecture |
| **secondary** | 1.4 — Sujet secondaire, moins important | **Pas d'image** ; titre, catégorie, date, temps de lecture |

- **article_type** (côté article) : `hot_news` | `long_form` | `standard` — renseigné à la génération ou en admin.
- **cover_image_url** et **cover_video_url** : URL de l’image ou de la vidéo de couverture ; si vides, le front peut afficher en `standard` sans média ou en `secondary`.

---

## 2) Les 9 catégories (rubriques)

Section « Découvrez vos rubriques préférées ».

- **Endpoint** : **GET /api/public/home** → champ **`categories`** (tableau de 9 catégories max).
- Ou **GET /api/public/categories** pour toutes les catégories (le front peut prendre les 9 premières ou filtrer par `home_order`).

Chaque catégorie contient : `id`, `name`, `slug`, `description`, **`home_order`** (1–9), **`image_url`** et **`video_url`** (média de la carte rubrique), `published_articles_count`, et optionnellement **`sub_categories`** (liste des sous-catégories, max 5 en hub).

L’ordre d’affichage : catégories avec `home_order` renseigné, tri par `home_order` ; sinon tri par `name`. Limite 9 (configurable via `config/vivat.php` → `home_categories_count`).

---

## 3) Section « Dernières actualités »

- **Endpoint** : **GET /api/public/home** → champ **`latest`**.
- Structure : `{ "label": "Dernières actualités", "articles": [ ... ] }`.
- Chaque article dans `latest.articles` a un **`display_type`** : `standard` (avec image) ou `secondary` (sans image).

---

## 4) Bouton « Show all »

Afficher encore plus d’articles (pagination).

- **Endpoint** : **GET /api/public/articles** avec **pagination**.
- Query params : `page`, `per_page` (défaut 12, max 50), optionnel `category` (slug), `sort`, `dir`.
- Réponse : `data` (liste d’articles), `meta` (current_page, last_page, per_page, total), `links` (prev, next).

Le front envoie par exemple `GET /api/public/articles?page=2&per_page=12` après un premier chargement via `/api/public/home`.

---

## 5) Bouton « Rédiger un article »

Comportement attendu :
- **Utilisateur non connecté** → rediriger vers la **création de compte** (inscription rédacteur).
- **Utilisateur connecté avec rôle rédacteur (contributor) ou admin** → rediriger vers le **profil rédacteur** (liste des soumissions, création d’article).

- **Endpoint** : **GET /api/public/home** → champ **`writer_cta`**.
- Contenu :
  - **`signup_url`** : URL d’inscription (ex. `/register`) — à utiliser si l’utilisateur n’est pas connecté.
  - **`dashboard_url`** : URL du tableau de bord rédacteur (ex. `/contributor/submissions`) — à utiliser si l’utilisateur est connecté et `is_authenticated_as_contributor` est vrai.
  - **`is_authenticated_as_contributor`** : `true` si l’utilisateur courant a le rôle contributor ou admin (le front peut alors afficher un lien vers `dashboard_url` au lieu de `signup_url`).

Les URLs sont configurables dans **`config/vivat.php`** (`writer_signup_url`, `writer_dashboard_url`) ou via variables d’environnement `VIVAT_WRITER_SIGNUP_URL`, `VIVAT_WRITER_DASHBOARD_URL`.

---

## Page Hub (rubrique)

- **Endpoint** : **GET /api/public/categories/{slug}/hub** (ex. `/api/public/categories/finance/hub`).
- **Query** : `?sub_category=slug` pour filtrer par sous-catégorie (ex. `?sub_category=epargne`).

Réponse : `category`, `description`, `total_published`, **`sub_categories`** (max 5 sous-catégories pour le filtre), **`featured`** (articles à la une avec image ou vidéo, `display_type: "featured"`), **`latest`** : `{ "label": "Dernières actualités", "articles": [ ... ] }` avec **`display_type`** par article : `standard` (avec image/vidéo) ou `secondary` (sans). Même logique d’affichage que la home (featured / standard / secondary).

Chaque sous-catégorie a : `id`, `name`, `slug`, `description`, `order`, `image_url`, `video_url`. Les articles peuvent avoir **`sub_category_id`** et **`sub_category`** (objet) pour le filtre par sous-catégorie.

---

## Récap des endpoints

| Besoin | Méthode | Endpoint | Remarque |
|--------|---------|----------|----------|
| Données complètes home | GET | `/api/public/home` | top_news, featured, latest, categories, writer_cta |
| Page Hub (rubrique) | GET | `/api/public/categories/{slug}/hub` | sub_categories, featured, latest avec display_type ; `?sub_category=slug` pour filtrer |
| Plus d’articles (Show all) | GET | `/api/public/articles?page=...&per_page=...` | Pagination |
| Liste des catégories | GET | `/api/public/categories` | Toutes les catégories |
| Détail d’un article | GET | `/api/public/articles/{slug}` | Pour les liens vers un article |

---

## Modèle Article (champs utiles pour le home / hub)

- **article_type** : `hot_news` | `long_form` | `standard` (nullable).
- **cover_image_url**, **cover_video_url** : URL de l’image ou vidéo de couverture (nullable).
- **sub_category_id** : optionnel, pour le filtre par sous-catégorie sur le hub.
- En réponse home/hub, chaque article est enrichi avec **display_type** : `top_news` | `featured` | `standard` | `secondary`.

## Modèle Category (champs utiles pour le home / hub)

- **home_order** : entier 1–9 pour l’ordre d’affichage sur la home (nullable).
- **image_url**, **video_url** : URL de l’image ou vidéo de la carte rubrique (nullable).
- **sub_categories** : relation (max 5 en hub) ; chaque sous-catégorie a `name`, `slug`, `order`, `image_url`, `video_url`.

---

## Cache et invalidation

- Réponse **GET /api/public/home** mise en cache (TTL configurable, défaut 5 min : `config/vivat.php` → `home_cache_ttl`).
- Le cache `vivat.home` est invalidé à la **publication** d’un article, à la **modification** d’un article publié, et à la **création / modification / suppression** d’une catégorie.

---

*Dernière mise à jour : février 2026*

**Tests Postman** : voir `docs/POSTMAN_HOME_TESTS.md` pour la checklist complète des endpoints home à tester.
