#!/bin/bash

# Aguarda o banco de dados ficar disponível (se estiver usando PostgreSQL)
# while ! nc -z $DB_HOST $DB_PORT; do sleep 1; done

# Roda as migrations
php artisan migrate --force

# Limpa e otimiza o cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Inicia o Apache
apache2-foreground