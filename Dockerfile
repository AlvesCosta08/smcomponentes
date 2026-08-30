# Estágio 1: Build do Vite (Node)
FROM node:22-alpine AS vite-builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# Estágio 2: PHP + Laravel (agora com PHP 8.4)
FROM php:8.4-fpm-alpine AS app

# Instala dependências do sistema e extensões PHP
RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        ctype \
        exif \
        gd \
        mbstring \
        pdo_mysql \
        zip \
        opcache

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia composer.json e .lock
COPY composer.json composer.lock ./

# Instala as dependências (agora compatíveis com PHP 8.4)
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Copia o restante do código
COPY . .

# Copia os assets compilados do Vite
COPY --from=vite-builder /app/public/build /var/www/html/public/build

# Permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Script de entrada (opcional)
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]