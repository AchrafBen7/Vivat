# Site public implémentation et optimisations

Ce document décrit tout ce qui a été mis en place pour le **site public Vivat** (HTML rendu côté serveur, sans Blade) et comment les performances ont été optimisées.

---

## 1. Choix d’architecture

### Objectif
- **SEO** : contenu en HTML complet dès la première réponse (pas de SPA qui charge du JSON puis rend en JS).
- **Pas de Blade** : templates en PHP pur (HTML + `<?php ?>`), comme demandé.
- **Tailwind** : styles via CDN dans les pages HTML.

### Résultat
- Une requête HTTP → le serveur génère la page complète → le navigateur affiche immédiatement.
- Aucun chargement de framework JS, aucun appel API depuis le client pour afficher la page.
- Les moteurs de recherche reçoivent directement le HTML (titres, meta, contenu).

---

## 2. Ce qui a été fait (liste complète)

### 2.1 Rendu des vues en PHP (sans Blade)

| Élément | Rôle |
|--------|------|
| **`app/Support/helpers.php`** | Fonction globale `render_php_view($template, $data)` : inclut un fichier `.php` avec `extract($data)` et retourne le HTML généré. Aucun moteur Blade. |
| **`composer.json`** | Ajout de `app/Support/helpers.php` dans `autoload.files` pour charger le helper à chaque requête. |

Les templates sont des fichiers **`.php`** dans `resources/views/site/` : HTML + éventuellement `<?php ?>` et `<?= ?>`. Pas de syntaxe Blade (`@if`, `{{ }}`, etc.).

---

### 2.2 Service de données partagé

| Fichier | Rôle |
|---------|------|
| **`app/Services/PublicPageDataService.php`** | Centralise la logique des données pour les pages publiques. Réutilise **les mêmes clés de cache** que l’API (`vivat.home`, `vivat.hub.{slug}`) pour éviter la duplication et garder une seule source de vérité. Convertit les modèles Eloquent en tableaux pour les vues PHP. |

**Méthodes :**
- `getHomeData()` : top news, à la une, dernières actus, catégories (pour l’accueil).
- `getCategoryHubData($categorySlug, $subCategorySlug)` : rubrique, sous-rubriques, articles à la une et derniers (pour la page hub).

La logique métier (requêtes, tris, limites) est identique à celle de l’API ; seuls le format de sortie (tableaux) et l’usage (vues HTML) changent.

---

### 2.3 Contrôleurs Web

| Contrôleur | Méthode | Rôle |
|------------|---------|------|
| **`App\Http\Controllers\Web\HomeController`** | `__invoke()` | Récupère les données via `PublicPageDataService::getHomeData()`, rend `site.home` puis `site.layout`, retourne une réponse HTML. |
| **`App\Http\Controllers\Web\ArticleController`** | `show($slug)` | Charge l’article publié par slug, rend `site.article` puis `site.layout`, retourne HTML. |
| **`App\Http\Controllers\Web\CategoryController`** | `index()` | Liste toutes les catégories (avec compteur d’articles), rend `site.categories` + layout. |
| **`App\Http\Controllers\Web\CategoryController`** | `hub($slug)` | Données hub via `getCategoryHubData($slug, ?sub_category)`, rend `site.category_hub` + layout. |

Chaque action :
1. Récupère les données (services ou modèles).
2. Rend d’abord la vue de contenu (`site.xxx`) en chaîne.
3. Rend le layout (`site.layout`) en lui passant cette chaîne dans `$content` + `$title` et `$meta_description`.
4. Retourne une `Response` avec le HTML et le header `Content-Type: text/html; charset=UTF-8`.

---

### 2.4 Vues PHP (structure et contenu)

Toutes les vues sont dans **`resources/views/site/`** :

| Fichier | Rôle |
|---------|------|
| **`layout.php`** | Structure commune : `<!DOCTYPE html>`, `<head>` (meta, titre, Tailwind CDN, police), `<header>` (logo Vivat, liens Accueil / Rubriques), `<?= $content ?>`, `<footer>`. |
| **`home.php`** | Bloc top news (grande carte avec image), grille « À la une », liste « Dernières actualités », grille « Rubriques », lien « Rédiger un article ». |
| **`article.php`** | Titre, catégorie, date, temps de lecture, image de couverture, extrait, contenu (HTML affiché tel quel). |
| **`categories.php`** | Liste des rubriques en cartes (nom, description, nombre d’articles, lien vers hub). |
| **`category_hub.php`** | Nom et description de la rubrique, filtres par sous-catégorie, « À la une » (3 articles), « Dernières actualités » (liste). |

Les vues reçoivent des **tableaux** (pas d’objets Eloquent) pour éviter la sérialisation et les accès N+1. L’échappement est fait avec `htmlspecialchars()` sur les textes utilisateur ; le contenu article (`content`) est affiché en HTML brut car généré côté backend.

---

### 2.5 Routes Web

Dans **`routes/web.php`** :

| Méthode + URL | Nom de route | Contrôleur / action |
|---------------|--------------|----------------------|
| `GET /` | `home` | `WebHomeController` |
| `GET /categories` | `categories.index` | `WebCategoryController@index` |
| `GET /categories/{slug}` | `categories.hub` | `WebCategoryController@hub` |
| `GET /articles/{slug}` | `articles.show` | `WebArticleController@show` |

Aucun préfixe : ce sont les URLs du site public. L’API reste sous `/api/...`.

---

## 3. Optimisations mises en place

### 3.1 Cache des données (principal levier de performance)

- **Home** : les données (top news, à la une, dernières actus, catégories) sont mises en cache sous la clé **`vivat.home`** avec un TTL configurable (défaut 300 s dans `config('vivat.home_cache_ttl', 300)`).
- **Hub catégorie** : clé **`vivat.hub.{slug}`** (et `vivat.hub.{slug}.{sub_category}` si filtre), TTL 900 s (15 min).

Effet : après la première requête (ou un hit API), les requêtes suivantes **ne refont pas** les grosses requêtes SQL ; elles lisent le cache (Redis ou fichier selon `config/cache.php`). C’est la raison principale pour laquelle les pages semblent « instantanées ».

Le **même cache** est utilisé par l’API et par le site HTML (via `PublicPageDataService`), donc pas de double logique ni de double cache.

---

### 3.2 Une requête = une réponse HTML complète

- Pas de SPA : pas de bundle JS à télécharger pour « dessiner » la page.
- Pas de second round-trip (fetch JSON puis rendu côté client).
- Le navigateur reçoit directement le document HTML final → affichage immédiat, sans état de chargement intermédiaire.

---

### 3.3 Données prêtes pour les vues (tableaux, pas Eloquent)

- `PublicPageDataService` transforme les modèles en **tableaux** avant de les passer aux vues.
- Les vues n’accèdent pas aux relations Eloquent (pas de lazy loading, pas de N+1).
- Moins de travail pour le moteur PHP (pas de sérialisation d’objets complexes dans la vue).

---

### 3.4 Layout unique et contenu injecté

- Un seul fichier **`layout.php`** pour tout le site (header, footer, Tailwind).
- Chaque page ne rend que son **contenu** dans une vue dédiée ; ce contenu est injecté dans le layout via `$content`.
- Pas de duplication de structure HTML, maintenance simple.

---

### 3.5 Assets légers

- **Tailwind** chargé via CDN (`cdn.tailwindcss.com`) : pas de build CSS côté projet pour le site public.
- Une seule police (Inter) via Google Fonts.
- Aucun framework JS pour le rendu des pages → temps de chargement et d’exécution minimaux.

---

### 3.6 Réutilisation de la logique métier

- Les mêmes règles métier que l’API (articles publiés, ordre, limites, hub avec sous-catégories) sont utilisées dans **`PublicPageDataService`**.
- Pas de duplication de requêtes ni de règles : une seule source pour « ce qu’on affiche sur la home » et « ce qu’on affiche sur le hub ».

---

## 4. Résumé schématique

```
Requête GET /
    → HomeController
    → PublicPageDataService::getHomeData()
        → Cache::remember('vivat.home', 300, ...)  ← lecture cache (ou 1 seule fois BDD)
    → render_php_view('site.home', $data)         ← HTML du contenu
    → render_php_view('site.layout', ['content' => $content, ...])  ← HTML final
    → Response 200 avec body HTML
```

Pour **GET /categories/economie** (hub) : même principe avec la clé `vivat.hub.economie` et la vue `site.category_hub`.

---

## 5. Fichiers créés ou modifiés (référence)

| Fichier | Action |
|---------|--------|
| `app/Support/helpers.php` | Créé |
| `composer.json` (autoload.files) | Modifié |
| `app/Services/PublicPageDataService.php` | Créé |
| `app/Http/Controllers/Web/HomeController.php` | Créé |
| `app/Http/Controllers/Web/ArticleController.php` | Créé |
| `app/Http/Controllers/Web/CategoryController.php` | Créé |
| `resources/views/site/layout.php` | Créé |
| `resources/views/site/home.php` | Créé |
| `resources/views/site/article.php` | Créé |
| `resources/views/site/categories.php` | Créé |
| `resources/views/site/category_hub.php` | Créé |
| `routes/web.php` | Modifié (routes site public) |

---

## 6. Pour aller plus loin (optionnel)

- **Cache HTTP** : headers `Cache-Control` / `ETag` sur les réponses HTML pour que le navigateur ou un reverse proxy mettent en cache la page.
- **Tailwind en build** : remplacer le CDN par un build (npm + Tailwind) pour réduire la taille CSS et personnaliser le thème.
- **Invalider le cache** : quand un article est publié ou une catégorie modifiée, appeler `Cache::forget('vivat.home')` (et les clés hub concernées) pour que la prochaine requête régénère les données.

Ces points ne sont pas en place par défaut mais peuvent être ajoutés si besoin.
