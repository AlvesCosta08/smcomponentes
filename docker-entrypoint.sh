#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

if [ "$FORCE_MIGRATION" = "true" ]; then
    php artisan migrate --force
fi

if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec "$@"