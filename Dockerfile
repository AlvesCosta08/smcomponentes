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

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia apenas os arquivos de dependência do PHP (melhor cache)
COPY composer.json composer.lock ./

# Instala as dependências em modo produção
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Copia o restante do código
COPY . .

# Copia os assets compilados (do estágio Node)
COPY --from=vite-builder /app/public/build /var/www/html/public/build

# Ajusta permissões para pastas que o Laravel precisa escrever
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copia o script de entrada e dá permissão de execução
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expõe a porta 8000 (para o servidor embutido, ou 9000 se usar PHP-FPM)
EXPOSE 8000

# Define o entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Comando padrão: inicia o servidor embutido (pode ser substituído por PHP-FPM + Nginx)
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]