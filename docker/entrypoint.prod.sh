#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views public/uploads
chown -R www-data:www-data storage bootstrap/cache public/uploads 2>/dev/null || true
chmod -R 775 storage bootstrap/cache public/uploads 2>/dev/null || true

if [ -z "${APP_KEY:-}" ]; then
    echo "APP_KEY manquante. Configure .env.production avant de lancer la stack." >&2
    exit 1
fi

if [ "${DB_CONNECTION:-mysql}" = "mysql" ] && [ -n "${DB_HOST:-}" ]; then
    echo "Attente de la base de donnees ${DB_HOST}:${DB_PORT:-3306}..."
    i=0
    until php -r '
        $host = getenv("DB_HOST") ?: "mysql";
        $port = getenv("DB_PORT") ?: "3306";
        $db = getenv("DB_DATABASE") ?: "vivat";
        $user = getenv("DB_USERNAME") ?: "vivat";
        $pass = getenv("DB_PASSWORD") ?: "";
        try {
            new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, [PDO::ATTR_TIMEOUT => 3]);
            exit(0);
        } catch (Throwable $e) {
            exit(1);
        }
    '; do
        i=$((i+1))
        if [ "$i" -ge 30 ]; then
            echo "La base n'est pas joignable apres 60 secondes." >&2
            exit 1
        fi
        sleep 2
    done
fi

php artisan config:clear >/dev/null 2>&1 || true
php artisan cache:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan storage:link >/dev/null 2>&1 || true

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"
