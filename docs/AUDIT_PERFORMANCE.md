# Audit performance — Vivat Backend

> **Date** : Février 2026  
> **Périmètre** : API Laravel (requêtes BDD, cache, recherche, endpoints publics)

---

## Note globale : **7/10**

L’application est déjà bien structurée (eager loading, pagination, index BDD, Redis en prod). Les principaux gains possibles concernent le **cache des réponses** et l’usage de l’**index full-text** pour la recherche. Après les correctifs proposés, l’objectif est d’atteindre **8,5/10**.

---

## Ce qui est déjà bien fait

| Point | Détail |
|-------|--------|
| **Eager loading** | Les contrôleurs utilisent systématiquement `->with('category')` (ou équivalent) sur les listes d’articles, clusters, RSS items, etc. Pas de N+1 sur les relations chargées. |
| **Pagination** | Toutes les listes sont paginées (`paginate(15)` ou `paginate(12)`), avec plafond à 50. Pas de `->get()` sur des tables volumineuses. |
| **Index BDD** | Index composites utiles : `idx_articles_published`, `idx_feeds_due_fetch`, `idx_jobs_pending`, etc. Index full-text sur `articles(title, excerpt)` et `rss_items(title, description)`. |
| **Resources** | `ArticleResource` utilise `whenLoaded('category')` et `whenLoaded('articleSources')` : pas de requête en plus si la relation n’est pas chargée. |
| **Cache driver** | En production (`.env`) le cache est sur **Redis** (`CACHE_STORE=redis`), adapté à la performance. |
| **Jobs asynchrones** | Pipeline (fetch RSS, enrichissement, génération) déporté dans des jobs et files Horizon : pas de blocage des requêtes HTTP. |
| **Rate limiting** | Limitation sur l’API (60 req/min par IP/user) et sur les appels OpenAI (50/min) pour éviter les abus. |

---

## Problèmes identifiés et correctifs

### 1. Recherche : `LIKE '%...%'` au lieu de l’index full-text

**Problème** : Dans `ArticleController::search()`, la recherche utilise `where('title', 'LIKE', "%{$q}%")` (et idem pour `excerpt`, `meta_description`). Sur MySQL, un `LIKE` avec un `%` au début **ne peut pas utiliser l’index** → scan complet de la table.

**Correctif** : Utiliser `whereFullText(['title', 'excerpt'], $q)` lorsque le driver est MySQL. L’index `ft_articles_search` sera utilisé. Conserver un fallback `LIKE` pour `meta_description` (non présent dans l’index) et pour SQLite (tests).

**Impact** : Recherche beaucoup plus rapide dès que le nombre d’articles augmente (plusieurs milliers).

---

### 2. Page Hub : pas d’eager load de `category` sur les articles

**Problème** : Dans `CategoryController::hub()`, les requêtes `$featured` et `$recent` ne chargent pas la relation `category`. Si le front affiche le nom de la catégorie pour chaque article, `ArticleResource` ne fait pas de N+1 car `whenLoaded('category')` n’ajoute rien si non chargé — mais la réponse JSON n’inclut pas l’objet `category`. Pour être cohérent et éviter tout risque de requête supplémentaire côté front, mieux vaut charger `category` une fois.

**Correctif** : Ajouter `->with('category')` sur les deux requêtes (featured et recent).

**Impact** : Réponse hub plus riche (catégorie incluse par article) et garantie de ne jamais déclencher de N+1 si on modifie le front plus tard.

---

### 3. Aucun cache sur les endpoints publics les plus sollicités

**Problème** : Aucun usage de `Cache::` ou `cache()` dans l’app. Les endpoints publics (liste d’articles, liste de catégories, page hub) refont les mêmes requêtes à chaque appel.

**Correctif** :
- **Liste des catégories** (`GET /api/public/categories`) : cache 1 h (les catégories changent rarement).
- **Page hub** (`GET /api/public/categories/{slug}/hub`) : cache 15 min par slug (contenu éditorial qui peut changer après publication d’un nouvel article).

**Impact** : Réduction forte de la charge BDD et du temps de réponse sur les pages les plus vues. Invalidation simple (TTL) sans toucher au code de publication pour l’instant.

**Invalidation** : Le cache des catégories (`vivat.categories.index`) est vidé à la création, mise à jour ou suppression d’une catégorie (admin). Le cache d’un hub (`vivat.hub.{slug}`) est vidé à la mise à jour/suppression de la catégorie et **à chaque publication d’un article** de cette catégorie, afin que la page hub affiche tout de suite le nouvel article.

**Non mis en cache (pour l’instant)** : liste des articles publiés (`/api/public/articles`) et recherche (hors TTL catégories/hub), car dépendent fortement des filtres et de la pagination ; on peut ajouter plus tard un cache court (2–5 min) avec clé incluant les paramètres.

---

### 4. Recommandations : 100 articles en mémoire

**Constat** : `RecommendationService::recommend()` fait `->limit(100)->get()` puis trie en PHP. Pour un petit volume d’articles c’est acceptable ; au-delà de quelques milliers, on pourrait limiter à 50 ou déplacer une partie du scoring en SQL.

**Action** : Aucun correctif immédiat. À reconsidérer si le nombre d’articles publiés dépasse ~5 000.

---

## Synthèse des modifications appliquées

1. **ArticleController::search()** : utilisation de `whereFullText` sur MySQL pour `title` et `excerpt`, avec fallback `LIKE` pour SQLite et pour `meta_description`. Échappement des caractères spéciaux dans les `LIKE` (`addcslashes`).
2. **CategoryController::hub()** : ajout de `->with('category')` sur les requêtes featured et recent ; mise en cache de la réponse (modèles puis passage aux Resources à la sortie) avec TTL 15 min.
3. **CategoryController::index()** : mise en cache de la liste des catégories (TTL 1 h).
4. **Invalidation** : `Cache::forget('vivat.categories.index')` et `Cache::forget('vivat.hub.{slug}')` dans les actions admin (création/mise à jour/suppression de catégorie) et à la publication d’un article.

**Fichiers modifiés** : `app/Http/Controllers/Api/ArticleController.php`, `app/Http/Controllers/Api/CategoryController.php`.

---

## Recommandations futures (hors correctifs immédiats)

| Priorité | Action |
|----------|--------|
| Moyenne | Mettre en cache la première page de `GET /api/public/articles` (sans filtre) avec TTL 2–5 min, et invalider au publish d’un article. |
| Basse | Si la recherche devient lente malgré full-text : envisager un moteur dédié (Scout + Meilisearch ou Elasticsearch). |
| Basse | Activer le **query log** ou Laravel Telescope en staging pour détecter d’éventuels N+1 sur de nouveaux endpoints. |

---

*Rapport généré après analyse du code (controllers, models, migrations, config cache).*
