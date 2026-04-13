# Vivat

Vivat est une plateforme éditoriale Laravel avec :

- site public orienté contenu
- espace admin Filament
- espace rédacteur / soumissions
- workflow de review, proposition de prix, paiement Stripe, publication
- pipeline de génération et d'enrichissement IA

## Repo

- HTTPS : `https://github.com/AchrafBen7/Vivat.git`
- SSH : `git@github.com:AchrafBen7/Vivat.git`

## Stack

### Serveur

- PHP `8.2+`  
  Référence actuelle de déploiement : `PHP 8.4`
- MySQL `8`
- Redis `7.0+`
- Composer `2`
- Node.js `22`
- Nginx via Ploi

### Backend

- Laravel `12`
- Filament `5`
- Horizon `5.43+`
- Sanctum `4.3+`
- Socialite `5.26+`
- Spatie Permission `6.24+`
- Stripe PHP SDK `19.3+`

### Frontend

- Vite `7.0.7+`
- Tailwind CSS `4.0+`
- axios `1.11+`
- GSAP `3.14.2+`
- Lenis `1.3.21+`

## Services externes

- Stripe
- OpenAI
- Resend via SMTP
- Google OAuth
- Cloudinary
- Pexels

## Variables d'environnement

Exemples disponibles :

- [`.env.example`](./.env.example)

Points importants en production :

- `APP_ENV=production`
- `APP_DEBUG=false`
- `QUEUE_CONNECTION=redis`
- `SESSION_DRIVER=redis`
- `CACHE_STORE=redis`
- `GOOGLE_REDIRECT_URI` doit pointer vers le domaine prod
- `STRIPE_WEBHOOK_SECRET` doit correspondre au webhook prod
- le mail actuel est configuré en **Resend via SMTP**

## Installation locale

### Option 1 : Laravel + services installés en local

```bash
composer install
cp .env.example .env
php artisan key:generate
npm ci
php artisan migrate
npm run build
php artisan serve
```

Il faut aussi que MySQL et Redis tournent.

### Option 2 : Docker

Le projet contient aussi un environnement Docker local :

```bash
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
npm ci
npm run build
```

Commandes utiles avec Docker :

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan migrate
```

## Commandes utiles

### Développement

```bash
composer dev
```

Cette commande lance :

- le serveur Laravel
- l'écoute des queues
- les logs Laravel Pail
- Vite

### Build frontend

```bash
npm ci
npm run build
```

### Tests

```bash
composer test
```

## Queue, scheduler et Horizon

Le projet dépend fortement de Redis et Horizon.

### Scheduler Laravel

Le scheduler global doit être exécuté **toutes les minutes** :

```bash
* * * * * php /chemin/projet/artisan schedule:run >> /dev/null 2>&1
```

Cette ligne se configure dans le cron du serveur ou dans Ploi.  
Elle ne lance pas toutes les tâches à chaque minute : elle vérifie simplement ce qui est dû.

### Horizon

Horizon doit tourner en **processus permanent** :

```bash
php artisan horizon
```

Après un déploiement :

```bash
php artisan horizon:terminate || true
```

### Tâches planifiées actuellement

- fetch RSS : toutes les 6 heures
- enrichissement IA : quotidien
- génération quotidienne d'article : quotidien
- `horizon:snapshot` : toutes les 10 minutes
- expiration des quotes : toutes les heures
- digest newsletter : hebdomadaire
- health-check pipeline : toutes les 2 heures

## Paiements Stripe

Le workflow paiement repose sur :

- Checkout Stripe
- webhook Stripe
- remboursement admin
- dépublication si remboursement d'un article publié

Webhook à configurer :

```text
/api/stripe/webhook
```

En production, le webhook est obligatoire pour fiabiliser la réconciliation des paiements.

## Déploiement Ploi

### Pré-requis

- Nginx via Ploi
- PHP `8.4`
- MySQL `8`
- Redis `7.0+`
- Composer `2`
- Node.js `22`
- fichier `.env` de production

### Commandes de déploiement

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan horizon:terminate || true
```

### À configurer dans Ploi

- domaine et SSL
- variables d'environnement de production
- cron Laravel
- processus Horizon
- webhook Stripe

## Données et base locale

Après un `git pull`, pense à lancer :

```bash
php artisan migrate
```

Si la base locale ne contient pas assez de données, le rendu peut être différent d'une autre machine.

Pour reconstruire un jeu de données local :

```bash
php artisan db:seed
```

Pour cloner exactement une base d'un collègue, il faut partager un dump SQL.

## Santé applicative

Route health Laravel :

```text
/up
```

## Fichiers importants

- [`composer.json`](./composer.json)
- [`package.json`](./package.json)
- [`bootstrap/app.php`](./bootstrap/app.php)
- [`config/horizon.php`](./config/horizon.php)
- [`config/pipeline_schedule.php`](./config/pipeline_schedule.php)
- [`config/services.php`](./config/services.php)
- [`routes/web.php`](./routes/web.php)
- [`routes/api.php`](./routes/api.php)

## Notes

- `QUEUE_CONNECTION=redis` est requis pour Horizon.
- Le projet utilise actuellement **Resend via SMTP**, pas uniquement un mailer SMTP générique.
- Le provider d'image actuellement choisi est `pexels`.
- Les migrations sont nécessaires au déploiement : ne pas supprimer `database/migrations`.
