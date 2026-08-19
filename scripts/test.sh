#!/bin/bash

echo "🧪 Rodando testes com SQLite..."

# Garantir que o banco de testes existe
touch database/database.sqlite
chmod 777 database/database.sqlite

# Rodar migrações no ambiente de teste
php artisan migrate:fresh --env=testing --force

# Rodar os testes
./vendor/bin/phpunit "$@"

echo "✅ Testes concluídos!"
