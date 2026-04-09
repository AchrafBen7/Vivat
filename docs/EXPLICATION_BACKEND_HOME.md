## Contexte général

Ce document résume, de façon **chronologique** et **motivée**, ce qui a été mis en place sur l’API Vivat pour :
- la **gestion des catégories et des articles** côté admin,
- la **différence de rôle** entre un admin et un utilisateur public,
- les **endpoints publics** qui alimentent la home et les pages hub (rubriques).

L’objectif est que le PM (développeur) comprenne :
- **ce que j’ai fait** (endpoints, modèles, schéma),
- **dans quel ordre**,
- et **pourquoi** ces choix ont été faits du point de vue produit / front.

### Vue d’ensemble des étapes

1. **Étape 1 Structurer les rubriques**  
   J’ai d’abord stabilisé le modèle `Category` et les endpoints `/api/categories` pour que l’admin puisse définir les 9 rubriques principales (nom, slug, description, ordre d’affichage sur la home, image/vidéo de carte).

2. **Étape 2 Structurer les articles**  
   Ensuite j’ai posé le modèle `Article` et `POST /api/articles` pour créer des articles complets (texte, SEO, type éditorial, visuel, rattachement à une catégorie/sous-catégorie).

3. **Étape 3 Séparer les rôles**  
   Puis j’ai bien séparé les routes **admin** (`/api/...`) des routes **publiques** (`/api/public/...`) pour que le front ne voie que ce qui est publiable et stable.

4. **Étape 4 Construire la home**  
   À partir de là, j’ai conçu `GET /api/public/home` pour renvoyer, en un seul payload, tout ce dont le front a besoin pour la home : top_news, featured, latest, rubriques et CTA « Rédiger ».

5. **Étape 5 Ajouter la liste complète et les hubs**  
   Enfin, j’ai ajouté :
   `GET /api/public/articles` pour la pagination / « Show all »,
   `GET /api/public/articles/{slug}` pour le détail,
   `GET /api/public/categories/{slug}/hub` pour les pages hub par rubrique, avec filtre optionnel par sous-catégorie.

---

## 1. Gestion des catégories (admin) `/api/categories`

### 1.1. Modèle et schéma

Table `categories` :
- `id` (UUID, PK)
- `name`, `slug` (unique), `description`
- `home_order` : entier optionnel pour l’ordre d’affichage des **rubriques** sur la home (1–9).  
  > **Important** : `home_order` ne fige **pas** les articles affichés, uniquement l’ordre des cartes rubriques (Au quotidien, Énergie, Finance…). Les articles restent sélectionnés dynamiquement (voir section home).
- `image_url` : visuel de la carte rubrique
- `video_url` : URL vidéo optionnelle pour la carte
- `created_at`

Modèle `Category` :
- `fillable` : `name`, `slug`, `description`, `home_order`, `image_url`, `video_url`
- relations :
  `articles()` → articles de la rubrique
  `subCategories()` → sous-catégories (max 5 par catégorie), ordonnées par `order`

### 1.2. Endpoints admin

Les routes admin sont protégées par `auth:sanctum` + `role:admin`.  
Pour les catégories :
- **GET** `/api/categories`  
  Retourne la liste complète des catégories avec `published_articles_count` (nombre d’articles publiés dans la catégorie).

- **POST** `/api/categories`  
  Crée une nouvelle catégorie avec validation :
  `name` requis,
  `slug` requis + unique,
  `home_order`, `image_url`, `video_url` optionnels.
  Après création on fait un `refresh()` pour récupérer `created_at` et on invalide les caches home / catégories.

- **GET** `/api/categories/{id}`  
  Détail d’une catégorie pour l’admin (avec `published_articles_count`).

- **PUT** `/api/categories/{id}`  
  Mise à jour partielle (`sometimes`) avec validation, puis invalidation des caches :
  `vivat.categories.index`
  `vivat.hub.{slug}`
  `vivat.home`

- **DELETE** `/api/categories/{id}`  
  Supprime la catégorie et invalide les mêmes caches.  
  Les articles liés basculent sur `category_id = null` grâce à `nullOnDelete`.

---

## 2. Gestion des articles (admin) `POST /api/articles`

### 2.1. Modèle et schéma

Table `articles` :
- Contenu : `title`, `slug` (unique), `excerpt`, `content`
- SEO : `meta_title`, `meta_description`, `keywords` (JSON)
- Classification :
  `category_id` → catégorie principale
  `sub_category_id` → sous-catégorie facultative (pour le hub)
  `cluster_id`
- Affichage home / hub :
  `article_type` (`hot_news`, `long_form`, `standard`)
  `cover_image_url`
  `cover_video_url`
- Métadonnées :
  `reading_time`
  `status` (`draft`, `review`, `published`, `archived`, `rejected`)
  `quality_score`
  `published_at`
  `created_at` / `updated_at`

Modèle `Article` :
- `fillable` inclut : `category_id`, `sub_category_id`, `article_type`, `cover_image_url`, `cover_video_url`, etc.
- relations :
  `category()`
  `subCategory()`
  `cluster()`
  `articleSources()`
- méthodes :
  `scopePublished()` pour filtrer les articles publiés,
  `isPublishable()` + `publish()` pour contrôler la mise en ligne (ex. `quality_score >= 60`).

### 2.2. Création et mise à jour

`StoreArticleRequest` (POST `/api/articles`) :
- Valide :
  `title`, `slug`, `content` (obligatoires),
  `category_id`, `sub_category_id`, `article_type`, `cover_image_url`, `cover_video_url`, etc.
- Fixe des valeurs par défaut via `prepareForValidation()` :
  `status = draft` si absent,
  `reading_time = 5` si absent.

`UpdateArticleRequest` (PUT `/api/articles/{id}`) :
- Gère les mises à jour partielles (`sometimes`) :
  contenu, SEO, classification (`category_id`, `sub_category_id`),
  affichage (`cover_image_url`, `cover_video_url`, `article_type`),
  `status`, `published_at`.

`ArticleController@store` :
- Vérifie les autorisations (`Policy` sur `Article`).
- Crée l’article avec les données validées et un `quality_score` par défaut si non fourni.

---

## 3. Rôles : admin vs utilisateur public

La différence de rôle est nette :

- **Admin** :
  Accès aux routes protégées `auth:sanctum` + `role:admin` :
    CRUD catégories `/api/categories`
    CRUD articles `/api/articles`
    Gestion des sous-catégories `/api/categories/{category}/sub-categories`, `/api/sub-categories/{id}`
    Pipeline, stats, etc.
  Peut structurer l’arborescence éditoriale (rubriques, sous-rubriques) et publier / dépublier des contenus.

- **Utilisateur public** (non authentifié) :
  Accès **lecture seule** aux routes `prefix('public')` :
    `/api/public/home` (home complète),
    `/api/public/articles` (liste paginée d’articles publiés),
    `/api/public/articles/{slug}` (détail d’un article),
    `/api/public/categories` (liste des rubriques),
    `/api/public/categories/{slug}/hub` (page hub d’une rubrique),
    `/api/public/search`, `/api/public/recommendations`, etc.
  Ne peut ni créer ni modifier les ressources.

Cette séparation garantit :
- que la **structure éditoriale** reste pilotée par l’admin,
- que le **lecteur public** ne voit que les contenus publiés via les endpoints `public.*`.

---

## 4. Fonctionnalités Home `/api/public/home`

L’endpoint `GET /api/public/home` fournit en un seul appel toutes les données nécessaires à la page d’accueil.

### 4.1. top_news

- Sélectionne l’article le plus récent avec `article_type = hot_news` et `status = published`.
- Charge la catégorie associée.
- Retourne un objet article enrichi avec :
  `display_type = "top_news"`,
  toutes les métadonnées utiles (titre, excerpt, catégorie, temps de lecture, cover_image_url / cover_video_url, dates…).

### 4.2. featured

- À partir de la liste des articles publiés (hors `top_news`), on sélectionne les **articles importants** :
  soit `article_type = hot_news`,
  soit présence d’un média (`cover_image_url` ou `cover_video_url` non null).
- Limite configurable via `config('vivat.home_featured_count')` (par défaut 4).
- Chaque article est renvoyé avec `display_type = "featured"` pour que le front choisisse le gabarit visuel adapté (cartes mises en avant).

### 4.3. latest (Dernières actualités)

- On repart de la liste des articles publiés **en excluant** tous ceux déjà utilisés dans `top_news` et `featured`.
- On prend les plus récents (`published_at` desc), quantité configurable (`home_latest_count`).
- Chaque article reçoit un `display_type` calculé :
  `standard` si un média est présent (`cover_image_url` ou `cover_video_url`),
  `secondary` sinon (article sans visuel).
- Le bloc est structuré comme :
  ```json
  {
    \"label\": \"Dernières actualités\",
    \"articles\": [ ... ]
  }
  ```

### 4.4. categories (Rubriques)

- L’API retourne au maximum `home_categories_count` catégories (par défaut 9) :
  si des `home_order` existent → tri par `home_order`,
  sinon tri alphabétique sur `name`.
- Chaque catégorie inclut :
  `id`, `name`, `slug`, `description`, `color`,
  `home_order`,
  `image_url`, `video_url`,
  `published_articles_count` (nombre d’articles publiés dans la rubrique).

### 4.5. writer_cta (bouton « Rédiger un article »)

- Objet `writer_cta` dans la réponse :
  `signup_url` : URL pour s’inscrire comme rédacteur (ex. `/register`),
  `dashboard_url` : URL du dashboard rédacteur (ex. `/contributor/submissions`),
  `is_authenticated_as_contributor` : booléen qui dit si l’utilisateur courant a un rôle `contributor` ou `admin`.

En pratique :
- visiteur non connecté → le front affiche un CTA vers `signup_url`,
- contributeur/admin connecté → le front affiche un CTA vers `dashboard_url`.

L’ensemble de la réponse `/api/public/home` est mis en cache (`vivat.home`) avec un TTL configurable (5 minutes par défaut). Le cache est invalidé dès qu’un article publié ou une catégorie change.

---

## 5. Liste paginée d’articles `/api/public/articles`

L’endpoint `GET /api/public/articles` sert de base à :
- la fonctionnalité **« Show all »** depuis la home,
- une éventuelle page « Tous les articles ».

### 5.1. Paramètres

- `page` : numéro de page (1, 2, 3…)
- `per_page` : nombre d’articles par page (défaut 12, max 50)
- `category` : slug de catégorie (`?category=finance`)
- `reading_time_max` : filtre par temps de lecture maximum
- `sort` : champ de tri (`published_at`, `reading_time`, `quality_score`, `title`)
- `dir` : sens (`asc` ou `desc`)

### 5.2. Réponse

- `data` : tableau d’articles (sans `content`, optimisé pour la liste)
- `meta` : `current_page`, `last_page`, `per_page`, `total`, etc.
- `links` : `first`, `last`, `prev`, `next`

Chaque article inclut les champs nécessaires pour être affiché dans une liste (titre, slug, excerpt, catégorie, temps de lecture, média, dates…).

---

## 6. Détail d’un article `/api/public/articles/{slug}`

- Route : `GET /api/public/articles/{article:slug}`.
- `ArticleController@showBySlug` :
  utilise le binding Laravel sur le slug,
  charge les relations nécessaires (`category`, `articleSources`, `subCategory`).
- Dans `ArticleResource`, la route de détail (nommée `public.articles.show`) active le retour du :
  `content` complet (HTML),
  `article_sources`,
  relations de contexte (catégorie, sous-catégorie).

Le front peut ainsi construire la page article à partir de l’URL de type `/articles/ia-revolution-medicale-sans-garde-fou` (slug friendly et SEO-friendly).

---

## 7. Page Hub de rubrique `/api/public/categories/{slug}/hub`

La page hub reprend la logique de la home mais filtrée par **rubrique**, et éventuellement par **sous-catégorie**.

### 7.1. Sans filtre (par catégorie)

`GET /api/public/categories/finance/hub` renvoie :
- `category` : `CategoryResource` avec `image_url`, `video_url`, `sub_categories` (max 5).
- `description` : description éditoriale de la rubrique.
- `total_published` : nombre d’articles publiés dans la rubrique (tous confondus).
- `sub_categories` : liste des sous-catégories (nom, slug, ordre, images/vidéos) pour alimenter un filtre UI.
- `featured` :
  articles publiés de la catégorie avec au moins un média (`cover_image_url` ou `cover_video_url`),
  triés par `quality_score`,
  limités à 3,
  chaque article enrichi avec `display_type = "featured"`.
- `latest` :
  les autres articles (hors `featured`),
  triés par `published_at` desc, limite 12,
  `display_type = "standard"` si média, `secondary` sinon.

Le tout est mis en cache sous des clés de type `vivat.hub.{slug}`.

### 7.2. Avec filtre par sous-catégorie

- Query param : `?sub_category={slug}`.
- Ex. : `GET /api/public/categories/finance/hub?sub_category=epargne`.
- Le contrôleur :
  résout d’abord la `SubCategory` correspondant au slug dans cette catégorie,
  si trouvée, filtre les articles sur `sub_category_id`,
  recalcule `total_published`, `featured` et `latest` sur ce sous-ensemble.
- Le cache est segmenté par sous-catégorie : `vivat.hub.finance.epargne`, etc.

Visuellement, ça permet :
- d’avoir une **page hub riche** par rubrique (même pattern que la home),
- d’ajouter un **filtre par sous-catégorie** qui recompose les blocs featured/standard/secondary sans changer de page.

---

## Conclusion

En résumé :
- Tu as mis en place un **back-office propre** (catégories, sous-catégories, articles) avec une séparation claire des rôles :  
  **admin** pour la gestion éditoriale, **public** pour la lecture via `/api/public/...`.
- Tu as conçu des endpoints **pensés pour le front** :
  `/api/public/home` pour toute la home (top_news, featured, latest, catégories, CTA),
  `/api/public/articles` pour la pagination / « Show all »,
  `/api/public/articles/{slug}` pour le détail d’article SEO-friendly,
  `/api/public/categories/{slug}/hub` pour les hubs de rubrique, avec filtre par sous-catégorie et la même logique de `display_type` que sur la home.

Ce socle rend la page d’accueil et les hubs **faciles à implémenter côté front** (iOS, web ou autre), tout en restant **extensible** (vidéos, nouvelles sections, rôles supplémentaires).

