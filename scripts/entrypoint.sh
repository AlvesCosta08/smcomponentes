#!/bin/bash

# Aguardar o banco de dados
echo "Aguardando banco de dados..."
while ! nc -z db 3306; do
  sleep 1
done
echo "Banco de dados pronto!"

# Aguardar Redis
echo "Aguardando Redis..."
while ! nc -z redis 6379; do
  sleep 1
done
echo "Redis pronto!"

# Gerar APP_KEY se não existir
if [ ! -f .env ] || ! grep -q "^APP_KEY=" .env || [ -z "$(grep ^APP_KEY= .env | cut -d= -f2)" ]; then
    echo "Gerando APP_KEY..."
    php artisan key:generate --force
fi

# Executar migrações se configurado
if [ "$FORCE_MIGRATE" = "true" ]; then
    echo "Executando migrações..."
    php artisan migrate --force
fi

# Executar seeders se configurado
if [ "$RUN_SEED" = "true" ]; then
    echo "Executando seeders..."
    php artisan db:seed --force
fi

# Limpar cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Executar o comando principal
exec "$@"