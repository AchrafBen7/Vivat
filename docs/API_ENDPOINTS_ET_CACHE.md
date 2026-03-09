# API endpoints, site public et cache — récapitulatif

Ce document regroupe les réponses aux questions fréquentes sur l’utilisation des endpoints API, le site public HTML, et le comportement du cache lors des modifications.

---

## 1. Les endpoints API sont-ils utilisés par le site public ?

### Réponse : **Non**

Le **site public HTML** (pages `/`, `/categories`, `/categories/{slug}`, `/articles`, `/articles/{slug}`) **n’appelle pas** les endpoints API. Il utilise la même logique backend (modèles, services, cache) directement en PHP, sans requête HTTP vers l’API.

| Endpoint | Utilisé par le site public ? | Utilisé par qui ? |
|----------|------------------------------|-------------------|
| `GET /api/public/home` | Non | App mobile, SPA, Postman, partenaires |
| `GET /api/public/categories` | Non | Idem |
| `GET /api/public/categories/{slug}` | Non | Idem |
| `GET /api/public/articles` | Non | Idem |
| `GET /api/public/articles/{slug}` | Non | Idem |

### Comment le site public récupère les données

- **Accueil** (`/`) : `PublicPageDataService::getHomeData()` → même logique et **même cache** (`vivat.home`) que l’API, mais en PHP direct.
- **Liste catégories** (`/categories`) : `Category::query()->...` dans le contrôleur Web.
- **Hub catégorie** (`/categories/{slug}`) : `PublicPageDataService::getCategoryHubData()` → même cache que l’API hub.
- **Liste des articles** (`/articles`) : `PublicPageDataService::getArticlesIndexData()` → articles paginés (12 par page), **sans cache** (données fraîches à chaque requête).
- **Article** (`/articles/{slug}`) : `Article::published()->where('slug', $slug)` dans le contrôleur Web.

**En résumé** : site public et API partagent la même logique et le même cache, mais le site public ne fait pas d’appels HTTP vers l’API.

### Note sur l’URL avec UUID

L’URL `http://localhost:8000/api/public/categories/019c8a1f-2ddb-7160-a2bb-03a7d6ec7c98` utilise un **UUID** (id). L’API catégories attend un **slug** dans l’URL (ex. `economie`, `politique`). Cette URL ne fonctionne que si une catégorie a exactement ce slug. En général, il faut utiliser le slug :  
`http://localhost:8000/api/public/categories/economie`

---

## 2. Les changements (articles, catégories) sont-ils reflétés directement ?

### Réponse : **Oui, dès la prochaine requête**

Avec l’invalidation du cache en place, les changements (nouveaux articles, publication, modification, suppression, catégories) sont pris en compte **dès la prochaine requête** sur le site public et l’API.

Sans invalidation, le cache (5 min pour la home, 15 min pour les hubs) aurait continué à servir l’ancienne version.

### Ce qui était déjà invalidé (avant les corrections)

| Action | Cache invalidé | Effet |
|--------|----------------|-------|
| **Publication** d’un article (`publish`) | `vivat.home`, `vivat.hub.{slug}`, `vivat.categories.index` | Home, hub catégorie et liste catégories à jour au prochain chargement. |
| **Création / modification / suppression** d’une catégorie | `vivat.categories.index`, `vivat.home`, `vivat.hub.{slug}` | Idem. |
| **Modification** d’une sous-catégorie | Hub concernés | Les hubs concernés se mettent à jour. |

### Ce qui manquait et a été ajouté

| Action | Avant | Après (modif dans `ArticleController`) |
|--------|-------|----------------------------------------|
| **Suppression** d’un article (`destroy`) | Aucun `Cache::forget` → ancienne home/hub jusqu’à expiration du cache | Invalidation de `vivat.home`, `vivat.categories.index` et `vivat.hub.{slug}` de la catégorie. La suppression est visible tout de suite. |
| **Modification** d’un article publié (`update`) | Seulement `vivat.home` | Invalidation aussi de `vivat.hub.{slug}` et `vivat.categories.index`. Home, hub et liste catégories se mettent à jour dès le prochain chargement. |

### Comportement actuel (résumé)

- **Ajout d’articles** : tant qu’ils ne sont pas publiés, ils n’apparaissent pas sur le site public. Dès qu’un article est **publié**, le cache est invalidé → changement visible au prochain chargement.
- **Modification** d’un article publié : cache invalidé (home + hub + index catégories) → changement visible au prochain chargement.
- **Suppression** d’un article : cache invalidé (home + hub de la catégorie + index catégories) → changement visible au prochain chargement.
- **Catégories / sous-catégories** : déjà géré (création, modification, suppression) → changement visible au prochain chargement.

---

## 3. Quand le chargement des nouvelles données se fait-il ?

### Réponse : **À la prochaine requête après l’invalidation**

Les nouvelles données sont chargées **à la demande**, au moment de la **première requête** qui a besoin de ce cache après une invalidation.

### Déroulement

1. **Tu modifies les données** (article publié/supprimé/modifié, catégorie modifiée, etc.).
2. Le contrôleur fait **`Cache::forget('vivat.home')`** (et les autres clés concernées) → le cache est vidé immédiatement pour ces clés.
3. **La requête suivante** qui demande la home (ou le hub, ou la liste des catégories) :
   - appelle `Cache::remember('vivat.home', 300, function () { ... })`,
   - ne trouve **rien** en cache (car on vient de le supprimer),
   - **exécute la closure** → requêtes en base → **charge les nouvelles données**,
   - les remet en cache pour 5 min (home) ou 15 min (hub),
   - renvoie cette réponse (HTML ou JSON).

Donc : le chargement des nouvelles données se fait **au moment de cette première requête après l’invalidation**, pas avant et pas en arrière-plan.

### En résumé

| Moment | Ce qui se passe |
|--------|-----------------|
| **À la modification** (ex. publication d’un article) | On invalide le cache (`Cache::forget`). Aucun rechargement de données ici. |
| **À la prochaine visite** (ex. quelqu’un ouvre `/` ou appelle l’API home) | `Cache::remember` ne trouve rien → il relance les requêtes → les nouvelles données sont chargées et servies. |

Il n’y a pas de rechargement automatique en tâche de fond : les données sont rafraîchies **à la demande**, au premier hit après l’invalidation.

---

## 4. Dernières modifications

### Page « Toutes les actualités » (`/articles`)

- **Route ajoutée** : `GET /articles` → liste paginée des articles (12 par page).
- **Service** : `PublicPageDataService::getArticlesIndexData()` — récupère les articles publiés, formatés via `articleToArray()`, sans cache.
- **Vue** : `resources/views/site/articles_index.php` — grille de cartes (catégorie, titre, date, temps de lecture, image) avec pagination.
- **Lien** : le bouton « Autres actualités » sur l’accueil (section Dernières actualités) pointe vers `/articles`.
