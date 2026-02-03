#!/bin/sh
set -e
cd /var/www/html

# .env Docker : partir de .env ou .env.example, appliquer les valeurs pour le container
if [ ! -f .env ]; then
    [ -f .env.example ] && cp .env.example .env
fi
# Forcer les variables Docker (PHP pour éviter les soucis sed Alpine / volume)
php -r "
\$f = '.env';
\$c = file_get_contents(\$f);
\$c = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=mysql', \$c);
\$c = preg_replace('/^REDIS_HOST=.*/m', 'REDIS_HOST=redis', \$c);
\$c = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=vivat', \$c);
\$c = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=vivat', \$c);
\$c = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=vivat_secret', \$c);
\$c = preg_replace('/^SESSION_DRIVER=.*/m', 'SESSION_DRIVER=redis', \$c);
\$c = preg_replace('/^CACHE_STORE=.*/m', 'CACHE_STORE=redis', \$c);
\$c = preg_replace('/^QUEUE_CONNECTION=.*/m', 'QUEUE_CONNECTION=redis', \$c);
file_put_contents(\$f, \$c);
"
# Clé d'application (obligatoire) : générer si absente
if ! grep -q 'APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --no-interaction || true
fi
# Supprimer le cache de config (sinon Laravel garde 127.0.0.1 du cache de l'hôte)
php artisan config:clear 2>/dev/null || true

# Installer les deps si vendor absent (volume monté sans vendor)
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

# Permissions
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

exec "$@"
