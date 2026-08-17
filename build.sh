#!/bin/bash

echo "🚀 Iniciando build..."

# Instalar dependências
echo "📦 Instalando dependências..."
composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Gerar APP_KEY
echo "🔑 Gerando APP_KEY..."
php artisan key:generate --force

# Criar link para storage
echo "🔗 Criando link para storage..."
php artisan storage:link || true

# Configurar permissões
echo "🔧 Configurando permissões..."
chmod -R 777 storage bootstrap/cache

echo "✅ Build concluído!"
