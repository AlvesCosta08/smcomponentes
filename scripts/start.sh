#!/bin/bash

echo "🚀 Iniciando ambiente de desenvolvimento..."

# Verificar se o Docker está rodando
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker não está rodando. Inicie o Docker primeiro."
    exit 1
fi

# Subir o container MySQL
echo "🐳 Subindo container MySQL..."
docker-compose up -d

# Aguardar o MySQL ficar pronto
echo "⏳ Aguardando MySQL ficar pronto..."
sleep 10

# Gerar APP_KEY se não existir
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    echo "🔑 Gerando APP_KEY..."
    php artisan key:generate
fi

# Rodar migrações
echo "📦 Rodando migrações..."
php artisan migrate --force

echo "✅ Ambiente pronto!"
echo "🌐 Acesse: http://localhost:8080"
