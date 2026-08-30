FROM node:22-alpine AS vite-builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

FROM php:8.4-fpm-alpine AS app

# Instala dependências do sistema
RUN apk add --no-cache \
    git unzip libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg

# Instala extensões (pdo e pdo_pgsql em etapas separadas)
RUN docker-php-ext-install -j$(nproc) bcmath ctype exif gd mbstring zip opcache
RUN docker-php-ext-install -j$(nproc) pdo
RUN docker-php-ext-install -j$(nproc) pdo_pgsql

# Habilita as extensões
RUN docker-php-ext-enable pdo pdo_pgsql

# Cria php.ini com as extensões carregadas (redundante, mas garante)
RUN echo "extension=pdo.so" > /usr/local/etc/php/conf.d/docker-php-ext-pdo.ini
RUN echo "extension=pdo_pgsql.so" >> /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# Dependências
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# Código
COPY . .
COPY --from=vite-builder /app/public/build /var/www/html/public/build

# Pastas de cache
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]