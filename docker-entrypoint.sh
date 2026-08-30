#!/bin/sh
set -e

# 1. Gera .env se não existir
if [ ! -f .env ]; then
    echo "Arquivo .env não encontrado. Criando a partir do .env.example..."
    cp .env.example .env
    php artisan key:generate
fi

# 2. (Opcional) Roda migrações se a variável FORCE_MIGRATION estiver definida
if [ "$FORCE_MIGRATION" = "true" ]; then
    echo "Executando migrações..."
    php artisan migrate --force
fi

# 3. (Opcional) Seeders, se necessário
if [ "$FORCE_SEED" = "true" ]; then
    echo "Executando seeders..."
    php artisan db:seed --force
fi

# 4. Otimiza o cache em produção (não fazer em desenvolvimento)
if [ "$APP_ENV" = "production" ]; then
    echo "Otimizando cache..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# 5. (Opcional) Inicia o supervisor para filas ou cron, se configurado
# Exemplo: se você tiver um arquivo supervisord.conf
# if [ "$QUEUE_WORKER" = "true" ]; then
#     supervisord -n -c /etc/supervisord.conf &
# fi

# 6. Executa o comando principal (CMD)
exec "$@"