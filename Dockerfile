# ---------------- Estágio 1: Build do Vite ----------------
FROM node:22-alpine AS vite-builder

WORKDIR /app

# Copia os arquivos de dependência do Node
COPY package.json package-lock.json ./

# Instala as dependências (incluindo Vite e plugins)
RUN npm ci --no-audit --no-fund

# Copia o restante do código (para que o Vite possa compilar)
COPY . .

# Compila os assets (CSS, JS, etc.) – gera a pasta public/build
RUN npm run build

# ---------------- Estágio 2: PHP + Laravel ----------------
FROM php:8.3-fpm-alpine AS app

# Instala extensões PHP necessárias para o Laravel
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

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia apenas os arquivos de dependência do PHP primeiro (melhor cache)
COPY composer.json composer.lock ./

# Instala as dependências PHP em modo produção
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Copia o restante do código (exceto node_modules, etc.)
COPY . .

# Copia os assets compilados do estágio Vite (pasta public/build)
COPY --from=vite-builder /app/public/build /var/www/html/public/build

# Ajusta permissões para pastas que o Laravel precisa escrever
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cria um script de entrada para gerar APP_KEY, se necessário
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expõe a porta (o Render definirá a variável PORT)
EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]