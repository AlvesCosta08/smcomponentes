#!/bin/bash
set -e

# Permissões
chown -R laravel:laravel /var/www/html/storage
chown -R laravel:laravel /var/www/html/bootstrap/cache

# Limpa cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Gera chave se não existir
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Rodar migrations
php artisan migrate --force

# Link storage
php artisan storage:link

# Otimiza
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"