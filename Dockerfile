# Vivat - Laravel 12 / PHP 8.4 (aligné sur composer.lock / Symfony 8)
# Contexte : CONTEXTE_PROJET.md (MySQL 8, Redis)

FROM php:8.4-cli-alpine

# Dépendances système
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    linux-headers \
    $PHPIZE_DEPS

# Extensions PHP (Laravel + MySQL + Redis)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        zip \
        exif \
        pcntl \
        bcmath \
        intl \
        opcache

# Redis (pecl)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

# Entrypoint (composer install si volume monté sans vendor)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copie du projet (vendor exclu par .dockerignore)
COPY . .

# Dépendances (sans dev en prod ; avec dev en local selon build-arg)
ARG INSTALL_DEV_DEPS=true
RUN if [ "$INSTALL_DEV_DEPS" = "true" ]; then \
    composer install --no-interaction --prefer-dist --optimize-autoloader; \
    else \
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader; \
    fi

# Permissions storage / bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
# En dev : serve Laravel ; en prod on peut override avec php-fpm ou horizon
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
