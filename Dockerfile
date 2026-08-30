FROM node:22-alpine AS vite-builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

FROM php:8.4-fpm-alpine AS app

RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        ctype \
        exif \
        gd \
        mbstring \
        pdo \
        pdo_pgsql \
        zip \
        opcache \
    && docker-php-ext-enable pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

COPY . .
COPY --from=vite-builder /app/public/build /var/www/html/public/build

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache \
    && touch storage/logs/laravel.log \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]

