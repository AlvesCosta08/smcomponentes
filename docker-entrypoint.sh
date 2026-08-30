#!/bin/sh
set -e

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

# ============================================================
# 1. Criar pastas de cache
# ============================================================
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
chmod -R 777 storage bootstrap/cache

# ============================================================
# 2. Definir DATABASE_URL (se não existir)
# ============================================================
if [ -z "$DATABASE_URL" ]; then
    DATABASE_URL="postgresql://loja_virtual_eilu_user:FeNuDK8XRL0XoI7WwqCgvCOJzT6d0Kof@dpg-daa41s9f2nfc7395g1dg-a.oregon-postgres.render.com/loja_virtual_eilu?sslmode=require"
    echo "[ENTRYPOINT] Usando DATABASE_URL embutida."
else
    echo "[ENTRYPOINT] Usando DATABASE_URL do ambiente."
fi

# ============================================================
# 3. Extrair parâmetros da URL (para DSN manual)
# ============================================================
# Remove protocolo
URL_NO_PROTO=$(echo "$DATABASE_URL" | sed -E 's/^postgres(ql)?:\/\///')

# Extrai usuário, senha, host, porta, database
DB_USER=$(echo "$URL_NO_PROTO" | cut -d':' -f1)
DB_PASS=$(echo "$URL_NO_PROTO" | cut -d':' -f2 | cut -d'@' -f1)
HOST_PORT=$(echo "$URL_NO_PROTO" | cut -d'@' -f2 | cut -d'/' -f1)
DB_NAME=$(echo "$URL_NO_PROTO" | cut -d'/' -f2 | cut -d'?' -f1)
DB_HOST=$(echo "$HOST_PORT" | cut -d':' -f1)
DB_PORT=$(echo "$HOST_PORT" | cut -d':' -f2)

[ -z "$DB_PORT" ] && DB_PORT=5432

echo "[ENTRYPOINT] DB_HOST=$DB_HOST, DB_PORT=$DB_PORT, DB_NAME=$DB_NAME, DB_USER=$DB_USER"

# ============================================================
# 4. Montar DSN manual para teste
# ============================================================
DSN="pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;sslmode=require"

# ============================================================
# 5. TESTE DE CONEXÃO COM DSN MANUAL
# ============================================================
echo "[ENTRYPOINT] Testando conexão com banco usando DSN manual..."

MAX_RETRIES=10
COUNT=0
CONNECTED=0

while [ $COUNT -lt $MAX_RETRIES ]; do
    ERROR_MSG=$(php -r "
        try {
            new PDO('$DSN', '$DB_USER', '$DB_PASS');
            echo 'ok';
        } catch (PDOException \$e) {
            echo 'erro: ' . \$e->getMessage();
        }
    " 2>&1)

    if echo "$ERROR_MSG" | grep -q '^ok$'; then
        echo "[ENTRYPOINT] ✅ Conexão com o banco bem-sucedida!"
        CONNECTED=1
        break
    else
        echo "[ENTRYPOINT] Tentativa $((COUNT+1))/$MAX_RETRIES falhou: $ERROR_MSG"
        sleep 2
    fi
    COUNT=$((COUNT + 1))
done

if [ $CONNECTED -eq 0 ]; then
    echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao banco."
    echo "[ENTRYPOINT] DSN usado: $DSN"
    echo "[ENTRYPOINT] Último erro: $ERROR_MSG"
    exit 1
fi

# ============================================================
# 6. GERAR .env
# ============================================================
echo "[ENTRYPOINT] Gerando .env..."

cat > .env << EOF
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY=${APP_KEY}
APP_URL=${APP_URL:-https://smcomponentes.onrender.com}
ASSET_URL=${ASSET_URL:-$APP_URL}
VITE_APP_URL=${VITE_APP_URL:-$APP_URL}

APP_STORAGE=/var/www/html/storage
VIEW_COMPILED_PATH=/var/www/html/storage/framework/views

DATABASE_URL=$DATABASE_URL
DB_CONNECTION=pgsql

CACHE_DRIVER=${CACHE_DRIVER:-file}
SESSION_DRIVER=${SESSION_DRIVER:-file}
SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}
FORCE_HTTPS=${FORCE_HTTPS:-true}
BROADCAST_DRIVER=${BROADCAST_DRIVER:-log}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-error}
EOF

echo "[ENTRYPOINT] .env gerado com sucesso."

# ============================================================
# 7. Limpar caches
# ============================================================
php artisan config:clear --no-interaction || true
php artisan cache:clear --no-interaction || true
php artisan view:clear --no-interaction || true
php artisan route:clear --no-interaction || true

# ============================================================
# 8. Gerar APP_KEY se necessário
# ============================================================
if grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --no-interaction
fi

# ============================================================
# 9. Recriar autoload
# ============================================================
composer dump-autoload --optimize --no-interaction

# ============================================================
# 10. Package discover
# ============================================================
php artisan package:discover --no-ansi --no-interaction || true

# ============================================================
# 11. Migrações
# ============================================================
if [ "$FORCE_MIGRATION" = "true" ]; then
    php artisan migrate --force --no-interaction || exit 1
fi

# ============================================================
# 12. Seeders
# ============================================================
if [ "$FORCE_SEED" = "true" ]; then
    php artisan db:seed --force --no-interaction || exit 1
fi

# ============================================================
# 13. Otimizações
# ============================================================
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache --no-interaction || true
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction || true
fi

# ============================================================
# 14. Iniciar servidor
# ============================================================
echo "[ENTRYPOINT] ✅ Inicialização concluída. Iniciando servidor..."
exec "$@"