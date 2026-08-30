# ============================================================

# ESTÁGIO 1: Build dos assets com Vite

# ============================================================

FROM node:22-alpine AS vite-builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci --no-audit --no-fund

COPY . .

RUN npm run build

# ============================================================

# ESTÁGIO 2: Aplicação Laravel

# ============================================================

FROM php:8.4-fpm-alpine AS app

# Dependências do sistema

RUN apk add --no-cache 
git 
unzip 
libzip-dev 
libpng-dev 
libjpeg-turbo-dev 
freetype-dev 
oniguruma-dev 
postgresql-dev

# Configurar GD

RUN docker-php-ext-configure gd 
--with-freetype 
--with-jpeg

# Instalar extensões PHP

RUN docker-php-ext-install -j$(nproc) 
bcmath 
exif 
gd 
mbstring 
pdo_pgsql 
zip 
opcache

# Composer

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# ============================================================

# Instalar dependências PHP primeiro para aproveitar cache

# ============================================================

COPY composer.json composer.lock ./

RUN composer install 
--no-dev 
--prefer-dist 
--optimize-autoloader 
--no-interaction 
--no-scripts

# ============================================================

# Copiar código da aplicação

# ============================================================

COPY . .

# ============================================================

# Copiar assets compilados pelo Vite

# ============================================================

COPY --from=vite-builder 
/app/public/build 
/var/www/html/public/build

# ============================================================

# Criar estrutura obrigatória do Laravel

# ============================================================

RUN mkdir -p 
storage/framework/sessions 
storage/framework/views 
storage/framework/cache/data 
storage/logs 
bootstrap/cache 
&& touch storage/logs/laravel.log 
&& chown -R www-data:www-data storage bootstrap/cache 
&& chmod -R ug+rwx storage bootstrap/cache

# ============================================================

# Entrypoint

# ============================================================

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Render define PORT automaticamente

EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
