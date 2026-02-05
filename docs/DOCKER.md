# Docker — Vivat (local)

Environnement local avec **PHP 8.3**, **MySQL 8**, **Redis**, aligné sur le contexte projet.

## Prérequis

- Docker et Docker Compose installés
- Ports libres : **8000** (app), **8080** (phpMyAdmin), **3306** (MySQL), **6379** (Redis)

## Démarrer l’environnement

```bash
# Construction et démarrage
docker compose up -d --build

# Vérifier que les services tournent
docker compose ps
```

- **App Laravel** : http://localhost:8000  
- **phpMyAdmin** : http://localhost:8080 (voir les tables MySQL dans le navigateur)  
- **MySQL** : `localhost:3306` (user `vivat` / password `vivat_secret`, root `root_secret`)  
- **Redis** : `localhost:6379`

## Premier lancement

1. **Créer la clé d’application** (si besoin) :
   ```bash
   docker compose exec app php artisan key:generate
   ```

2. **Lancer les migrations** :
   ```bash
   docker compose exec app php artisan migrate
   ```

3. **Optionnel** : générer un lien symbolique pour le storage :
   ```bash
   docker compose exec app php artisan storage:link
   ```

## Commandes utiles

| Action | Commande |
|--------|----------|
| Logs de l’app | `docker compose logs -f app` |
| Shell dans le container | `docker compose exec app sh` |
| Artisan | `docker compose exec app php artisan <cmd>` |
| Composer | `docker compose exec app composer <cmd>` |
| Arrêter | `docker compose down` |
| Arrêter + supprimer volumes | `docker compose down -v` |

## Commandes pipeline (Artisan)

| Commande | Description |
|----------|-------------|
| `php artisan rss:fetch` | Dispatch les jobs de fetch RSS (flux dus ; `--all` pour tous, `--limit=N`) |
| `php artisan content:enrich` | Dispatch l’enrichissement des items "new" (`--limit=50`, `--delay=3`) |
| `php artisan articles:generate` | Affiche les items enrichis prêts pour génération (utiliser l’API pour générer) |
| `php artisan cleanup:old` | Prune les jobs échoués + optionnellement supprime vieux rss_items (`--days=90`, `--dry-run`) |

Avec Docker : `docker compose exec app php artisan rss:fetch`, etc. Horizon doit tourner pour traiter les jobs (`php artisan horizon`).

## Variables d’environnement (Docker)

Le `docker-compose.yml` définit pour le service **app** :

- `DB_HOST=mysql`, `DB_DATABASE=vivat`, `DB_USERNAME=vivat`, `DB_PASSWORD=vivat_secret`
- `REDIS_HOST=redis`
- `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `QUEUE_CONNECTION=redis`

Ton fichier **`.env`** local est monté dans le container ; les variables ci‑dessus sont prioritaires quand tu lances avec `docker compose up`. Pour utiliser uniquement le `.env` (sans override), tu peux commenter la section `environment` du service `app` et mettre dans `.env` :

- `DB_HOST=mysql`, `DB_DATABASE=vivat`, `DB_USERNAME=vivat`, `DB_PASSWORD=vivat_secret`
- `REDIS_HOST=redis`

## Fichiers

- **`Dockerfile`** : image PHP 8.3 (Alpine), extensions Laravel + MySQL + Redis, Composer, entrypoint.
- **`docker-compose.yml`** : services `app`, `mysql`, `redis`, `phpmyadmin` ; volumes pour données MySQL/Redis et code (montage du projet).
- **`docker/entrypoint.sh`** : exécute `composer install` si `vendor` est absent (volume monté).
- **`.dockerignore`** : réduit le contexte de build (exclut `.git`, `vendor`, `.env`, etc.).

## Horizon / Scheduler (plus tard)

Quand Horizon et le scheduler seront en place, on pourra ajouter les services `horizon` et `scheduler` dans le même `docker-compose.yml` (voir `docs/EXEMPLES_CODE_REFERENCE.md`).
