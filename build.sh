#!/bin/bash

echo "🚀 Iniciando build..."

# Instalar dependências
composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Verificar se o vendor foi instalado
if [ -d "vendor" ]; then
    echo "✅ Vendor instalado com sucesso!"
else
    echo "❌ Vendor não encontrado!"
    exit 1
fi

# Criar .env se não existir
if [ ! -f .env ]; then
    echo "APP_ENV=production" > .env
    echo "APP_DEBUG=false" >> .env
    php artisan key:generate --force
fi

# Configurar permissões
chmod -R 777 storage bootstrap/cache

echo "✅ Build concluído!"
