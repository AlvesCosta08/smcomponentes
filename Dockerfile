FROM php:8.4-cli

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar apenas os arquivos essenciais primeiro
COPY composer.json composer.lock ./

# Criar um artisan mínimo para evitar erros
RUN echo '#!/usr/bin/env php\n<?php\necho "artisan placeholder\\n";' > artisan && chmod +x artisan

# Instalar dependências
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Copiar o resto dos arquivos
COPY . .

# Criar diretórios e definir permissões
RUN mkdir -p storage/framework/cache \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p bootstrap/cache \
    && chmod -R 777 storage \
    && chmod -R 777 bootstrap/cache

# Criar link para storage
RUN php artisan storage:link || true

# Definir permissões
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
