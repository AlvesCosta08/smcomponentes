#!/bin/sh
set -e

# Se não existir .env, cria a partir do exemplo (mas já foi criado no build)
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Executa migrações se solicitado
if [ "$FORCE_MIGRATION" = "true" ]; then
    php artisan migrate --force
fi

# Otimiza cache em produção
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"