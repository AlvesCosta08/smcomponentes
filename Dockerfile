# ====================
# ESTÁGIO 1: Build do Vite (assets)
# ====================
FROM node:22-alpine AS vite-builder

WORKDIR /app

# Copia apenas os arquivos de dependência para melhor cache
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

# Copia o restante do código e compila
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

# 1. Copia apenas os arquivos de dependência do PHP (melhor cache)
COPY composer.json composer.lock ./

# 2. Instala as dependências em modo produção (sem scripts)
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# 3. Copia o restante do código-fonte
COPY . .

# 4. Copia os assets compilados (do estágio Node)
COPY --from=vite-builder /app/public/build /var/www/html/public/build

# 5. Cria pastas de cache e ajusta permissões (durante o build)
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# 6. Copia o script de entrada e dá permissão de execução
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expõe a porta 8000 (para o servidor embutido)
EXPOSE 8000

# Define o entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Comando padrão: inicia o servidor embutido do PHP (usa a porta definida pelo Render)
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]