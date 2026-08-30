# ====================
# ESTÁGIO 1: Build do Vite (assets)
# ====================
FROM node:22-alpine AS vite-builder

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build

# ====================
# ESTÁGIO 2: PHP + Laravel
# ====================
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
    postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        ctype \
        exif \
        gd \
        mbstring \
        pdo_mysql \
        pdo_pgsql \
        zip \
        opcache

# Instala o Composer (global)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 1. Copia apenas os arquivos de dependência do PHP
COPY composer.json composer.lock ./

# 2. Instala as dependências (sem executar scripts)
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# 3. Copia o restante do código-fonte (agora o helpers.php estará disponível)
COPY . .

# 4. Copia os assets compilados (do estágio Node)
COPY --from=vite-builder /app/public/build /var/www/html/public/build

# 5. Executa os scripts de pós-autoload (agora com todos os arquivos presentes)
RUN composer run-script post-autoload-dump

# 6. Ajusta permissões
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 7. Script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]