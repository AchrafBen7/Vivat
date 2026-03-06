# Implémentation des rôles : Admin vs Contributor (user normal)

Ce document décrit **étape par étape** comment les 2 rôles sont mis en place dans Vivat.

---

## 1. Package et tables

- **Package** : `spatie/laravel-permission`
- **Tables** (migration `create_permission_tables`) :
  - `roles` : liste des rôles (ex. `admin`, `contributor`)
  - `permissions` : liste des permissions (ex. `articles.publish`, `categories.manage`)
  - `model_has_roles` : lien user ↔ rôle
  - `role_has_permissions` : lien rôle ↔ permissions

Tu as déjà la migration et le config dans `config/permission.php`.

---

## 2. Middleware enregistrés

Dans **`bootstrap/app.php`** les alias sont déclarés :

```php
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
]);
```

Ça permet d’utiliser `->middleware('role:admin')` ou `role:contributor|admin` dans les routes.

---

## 3. Création des rôles et permissions (seeder)

Fichier : **`database/seeders/RolesAndPermissionsSeeder.php`**

**Étapes dans le seeder :**

1. **Reset du cache** Spatie (pour ne pas garder d’anciennes permissions en cache).
2. **Création des permissions** : toutes les permissions métier (articles, pipeline, sources, categories, submissions, newsletter, payments, etc.) avec `guard_name = 'web'`.
3. **Rôle `admin`** : créé avec `guard_name = 'web'`, puis **toutes les permissions** lui sont assignées (`syncPermissions($permissions)`).
4. **Rôle `contributor`** : créé avec `guard_name = 'web'`, puis **seulement** :
   - `articles.view`
   - `submissions.create`
   - `submissions.view-own`

Donc : **admin = tout**, **contributor = limité (soumissions + voir articles)**.

---

## 4. Attribution du rôle à l’inscription

Dans **`app/Http/Controllers/Api/AuthController.php`**, méthode `register` :

```php
$user = User::create([...]);
$user->assignRole('contributor');  // ← chaque nouvel inscrit est "contributor"
```

Tout utilisateur qui s’inscrit via l’API reçoit **automatiquement** le rôle `contributor` (user “normal”). Personne n’est admin à l’inscription.

---

## 5. Utilisateur admin et contributor de test (seeder)

Dans **`database/seeders/DatabaseSeeder.php`** :

1. **`RolesAndPermissionsSeeder`** est appelé en premier (crée les rôles et permissions).
2. Ensuite :
   - Un user **admin** est créé ou récupéré : `admin@vivat.be` / `password` → `assignRole('admin')`.
   - Un user **contributor** est créé ou récupéré : `contributeur@vivat.be` / `password` → `assignRole('contributor')`.

Donc après `php artisan db:seed`, tu as 2 comptes de test : un admin, un contributor.

---

## 6. Modèle User

Dans **`app/Models/User.php`** :

- Le trait **`HasRoles`** (Spatie) est utilisé → `$user->hasRole('admin')`, `$user->assignRole('admin')`, etc.
- Méthodes pratiques :
  - `$user->isAdmin()` → `hasRole('admin')`
  - `$user->isContributor()` → `hasRole('contributor')`

Les rôles sont exposés dans la réponse auth via `$user->getRoleNames()` (dans `userPayload` du `AuthController`).

---

## 7. Routes protégées par rôle

Dans **`routes/api.php`** :

| Bloc | Middleware | Qui y a accès |
|------|------------|----------------|
| `Route::prefix('public')->...` | aucun | Tout le monde (liste catégories, articles, etc.) |
| `Route::prefix('contributor')->...` | `auth:sanctum`, `role:contributor\|admin` | Utilisateur connecté avec rôle **contributor** ou **admin** (soumissions, paiements contributeur) |
| `Route::middleware(['auth:sanctum', 'role:admin'])->...` | `auth:sanctum`, `role:admin` | **Uniquement admin** (sources, catégories CRUD, articles, pipeline, stats, modération, etc.) |

En résumé :

- **User non connecté** : uniquement les routes `public`.
- **User connecté contributor** : public + routes `contributor`.
- **User connecté admin** : public + contributor + toutes les routes admin.

---

## 8. Récap étape par étape (ce qui est déjà fait)

| Étape | Fait ? | Où |
|-------|--------|-----|
| 1. Installer Spatie Permission | Oui | `composer require spatie/laravel-permission` + config |
| 2. Migration des tables rôles/permissions | Oui | `database/migrations/..._create_permission_tables.php` |
| 3. Enregistrer les middlewares `role` / `permission` | Oui | `bootstrap/app.php` |
| 4. Créer les rôles et permissions | Oui | `RolesAndPermissionsSeeder` |
| 5. Attribuer le rôle à l’inscription | Oui | `AuthController::register` → `assignRole('contributor')` |
| 6. Créer un user admin (test) | Oui | `DatabaseSeeder` → `admin@vivat.be` + `assignRole('admin')` |
| 7. Protéger les routes par rôle | Oui | `routes/api.php` (prefix `contributor` et bloc `role:admin`) |
| 8. Exposer les rôles dans l’API (me, login, register) | Oui | `AuthController::userPayload` → `roles` |

---

## 9. Ce que tu dois faire de ton côté

1. **Lancer les migrations** (si pas déjà fait) :
   ```bash
   php artisan migrate
   ```

2. **Lancer le seeder** (crée les rôles, permissions, admin + contributor de test) :
   ```bash
   php artisan db:seed
   ```
   Ou seulement les rôles :
   ```bash
   php artisan db:seed --class=RolesAndPermissionsSeeder
   ```
   Puis si tu veux les 2 users de test :
   ```bash
   php artisan db:seed --class=DatabaseSeeder
   ```

3. **Tester en Postman** :
   - **Contributor** : `POST /api/auth/login` avec `contributeur@vivat.be` / `password` → utiliser le token sur les routes `/api/contributor/*`.
   - **Admin** : `POST /api/auth/login` avec `admin@vivat.be` / `password` → utiliser le token sur les routes admin (ex. `GET /api/categories`, `GET /api/articles`, etc.).

4. **Donner le rôle admin à un autre user** (par ex. ton compte) :
   ```bash
   php artisan tinker
   ```
   Puis :
   ```php
   $u = \App\Models\User::where('email', 'ton@email.com')->first();
   $u->assignRole('admin');
   ```

---

## 10. En résumé

- **Oui**, l’implémentation des 2 rôles (admin et contributor) est faite.
- **Comment** : Spatie Permission (rôles + permissions), seeders pour les créer et pour les 2 comptes de test, `assignRole('contributor')` à l’inscription, middleware `role:admin` et `role:contributor|admin` sur les routes, et `User::HasRoles` + `isAdmin()` / `isContributor()`.

Si tu veux, on peut détailler une partie précise (ex. ajouter une permission, ou une route réservée à un rôle).
