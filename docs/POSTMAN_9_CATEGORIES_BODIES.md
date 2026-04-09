# Connexion phpMyAdmin + Bodies pour créer les 9 catégories

## Réinitialiser les catégories (tout supprimer)

Si tu as déjà des catégories et que tu veux repartir de zéro avec uniquement ces 9 catégories :

```bash
docker compose exec app php artisan vivat:reset-categories
```

Avec confirmation. Pour forcer sans demander : `--force`.

---

## phpMyAdmin Connexion

- **URL** : http://localhost:8080
- **Utilisateur** : `vivat`
- **Mot de passe** : `vivat_secret`

(Si ça échoue, essaie avec **utilisateur** : `root`, **mot de passe** : `root_secret`.)

Base à sélectionner après connexion : **`vivat`**.

---

## Créer les 9 catégories via Postman

Chaque catégorie a une **image de fond** (`image_url`) pour la carte rubrique, pas de couleur.

**Endpoint** : **POST** `http://localhost:8000/api/categories`  
**Headers** : `Accept: application/json`, `Content-Type: application/json`, `Authorization: Bearer {{token}}` (token admin obtenu via POST /api/auth/login).

Envoie **une requête par body** ci-dessous (chaque body = une catégorie). Récupère l’`id` dans la réponse 201 pour chaque catégorie, puis remplace les placeholders dans les bodies des 10 articles (voir POSTMAN_10_ARTICLES_HOME_BODIES.md).

---

### Catégorie 1 Au quotidien

```json
{
  "name": "Au quotidien",
  "slug": "au-quotidien",
  "description": "Rubrique catch-all du quotidien",
  "home_order": 1,
  "image_url": "https://picsum.photos/400/300?random=quotidien"
}
```

---

### Catégorie 2 Énergie

```json
{
  "name": "Énergie",
  "slug": "energie",
  "description": "Énergies renouvelables, transition énergétique",
  "home_order": 2,
  "image_url": "https://picsum.photos/400/300?random=energie"
}
```

---

### Catégorie 3 Finance

```json
{
  "name": "Finance",
  "slug": "finance",
  "description": "Argent, finance, investissement",
  "home_order": 3,
  "image_url": "https://picsum.photos/400/300?random=finance"
}
```

---

### Catégorie 4 Technologie

```json
{
  "name": "Technologie",
  "slug": "technologie",
  "description": "Innovation, tech, numérique",
  "home_order": 4,
  "image_url": "https://picsum.photos/400/300?random=techno"
}
```

---

### Catégorie 5 Chez soi

```json
{
  "name": "Chez soi",
  "slug": "chez-soi",
  "description": "Maison, déco, DIY, logement",
  "home_order": 5,
  "image_url": "https://picsum.photos/400/300?random=chezsoi"
}
```

---

### Catégorie 6 Mode

```json
{
  "name": "Mode",
  "slug": "mode",
  "description": "Mode, tendances, style",
  "home_order": 6,
  "image_url": "https://picsum.photos/400/300?random=mode"
}
```

---

### Catégorie 7 Santé

```json
{
  "name": "Santé",
  "slug": "sante",
  "description": "Santé publique, médecine, bien-être",
  "home_order": 7,
  "image_url": "https://picsum.photos/400/300?random=sante"
}
```

---

### Catégorie 8 Voyage

```json
{
  "name": "Voyage",
  "slug": "voyage",
  "description": "Voyage, découverte, culture",
  "home_order": 8,
  "image_url": "https://picsum.photos/400/300?random=voyage"
}
```

---

### Catégorie 9 Famille

```json
{
  "name": "Famille",
  "slug": "famille",
  "description": "Famille, relations, parentalité",
  "home_order": 9,
  "image_url": "https://picsum.photos/400/300?random=famille"
}
```

---

## Récap

| # | Nom          | Slug         | home_order | image_url (background carte) |
|---|--------------|--------------|------------|------------------------------|
| 1 | Au quotidien | au-quotidien | 1         | à remplacer par ta propre URL si besoin |
| 2 | Énergie      | energie      | 2         | idem |
| 3 | Finance      | finance      | 3         | idem |
| 4 | Technologie  | technologie  | 4         | idem |
| 5 | Chez soi     | chez-soi     | 5         | idem |
| 6 | Mode         | mode         | 6         | idem |
| 7 | Santé        | sante        | 7         | idem |
| 8 | Voyage       | voyage       | 8         | idem |
| 9 | Famille      | famille      | 9         | idem |

Les bodies ci-dessus utilisent des images placeholder (picsum.photos). Tu peux remplacer `image_url` par l’URL de ta propre image de fond pour chaque rubrique.

Après avoir créé les 9 catégories, fais **GET** `http://localhost:8000/api/public/categories` pour récupérer les `id` (UUID) et remplacer les placeholders dans les bodies des 10 articles.
