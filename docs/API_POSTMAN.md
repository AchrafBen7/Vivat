# API Vivat — Tests Postman

Base URL (local) : **http://localhost:8000/api**

Aucune authentification requise pour l’instant. En-tête : `Accept: application/json`.

---

## Sources

| Méthode | URL | Description |
|--------|-----|-------------|
| GET | `/api/sources` | Liste des sources |
| POST | `/api/sources` | Créer une source (body: name, base_url, language?, is_active?) |
| GET | `/api/sources/{id}` | Détail d’une source |
| PUT/PATCH | `/api/sources/{id}` | Modifier une source |
| DELETE | `/api/sources/{id}` | Supprimer une source |

---

## Catégories

| Méthode | URL | Description |
|--------|-----|-------------|
| GET | `/api/categories` | Liste des catégories |
| GET | `/api/categories/{id}` | Détail d’une catégorie |

---

## Flux RSS

| Méthode | URL | Description |
|--------|-----|-------------|
| GET | `/api/rss-feeds` | Liste des flux (?active=1 pour actifs uniquement) |
| POST | `/api/rss-feeds` | Créer un flux (body: feed_url, source_id?, category_id?, is_active?, fetch_interval_minutes?) |
| GET | `/api/rss-feeds/{id}` | Détail d’un flux |
| PUT/PATCH | `/api/rss-feeds/{id}` | Modifier un flux |
| DELETE | `/api/rss-feeds/{id}` | Supprimer un flux |

---

## Items RSS

| Méthode | URL | Description |
|--------|-----|-------------|
| GET | `/api/rss-items` | Liste paginée (?status=new, ?category_id=, ?rss_feed_id=, ?per_page=15) |
| GET | `/api/rss-items/{id}` | Détail d’un item (avec enriched_item si chargé) |

---

## Articles

| Méthode | URL | Description |
|--------|-----|-------------|
| GET | `/api/articles` | Liste paginée (?status=, ?category_id=, ?per_page=15) |
| POST | `/api/articles` | Créer un article à la main (title, slug, content, …) |
| GET | `/api/articles/{id}` | Détail (inclut content) |
| PUT/PATCH | `/api/articles/{id}` | Modifier un article |
| DELETE | `/api/articles/{id}` | Supprimer un article |
| POST | `/api/articles/generate` | **Génération synchrone** (body: item_ids[], category_id?, custom_prompt?) |
| POST | `/api/articles/generate-async` | **Génération asynchrone** (body: idem, retour 202) |
| POST | `/api/articles/{id}/publish` | Publier l’article (si quality_score >= 60) |

---

## Stats

| Méthode | URL | Description |
|--------|-----|-------------|
| GET | `/api/stats` | Statistiques (sources, feeds actifs, items par statut, articles par statut, articles publiés) |

---

*Référence : routes/api.php, app/Http/Controllers/Api/*.*
