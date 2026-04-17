# Vivat


## Stack

### Serveur

- PHP `8.2+`  
  Référence actuelle de déploiement : `PHP 8.4`
- MySQL `8`
- Redis `7.0+`
- Composer `2`
- Node.js `22`

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

## Installation locale

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

- fetch RSS
- enrichissement IA
- génération d'article
- `horizon:snapshot` : toutes les 10 minutes
- expiration des quotes
- newsletter
- health-check pipeline

## Paiements Stripe

Webhook à configurer :

```text
/api/stripe/webhook
```

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
