#!/bin/bash

# Instalar dependências do Composer
composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Criar .env se não existir
if [ ! -f .env ]; then
    cp .env.example .env 2>/dev/null || echo "APP_KEY=" > .env
fi

# Gerar APP_KEY
php artisan key:generate --force

# Configurar permissões
chmod -R 777 storage bootstrap/cache

# Executar migrações (opcional - pode ser feito no deploy)
# php artisan migrate --force