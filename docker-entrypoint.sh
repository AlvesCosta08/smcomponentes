#!/bin/sh
set -e

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

# ============================================================
# 1. Pastas de cache com permissões totais
# ============================================================
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
chmod -R 777 storage bootstrap/cache

# ============================================================
# 2. Gerar .env diretamente das variáveis (USANDO APENAS DATABASE_URL)
# ============================================================
echo "[ENTRYPOINT] Gerando .env..."

# Extrai host da DATABASE_URL para usar no teste
if [ -n "$DATABASE_URL" ]; then
    # Extrai host da string (tudo entre @ e : ou /)
    DB_HOST_EXTRACTED=$(echo "$DATABASE_URL" | sed -n 's/.*@\([^:]*\):.*/\1/p')
    if [ -z "$DB_HOST_EXTRACTED" ]; then
        DB_HOST_EXTRACTED=$(echo "$DATABASE_URL" | sed -n 's/.*@\([^\/]*\)\/.*/\1/p')
    fi
    echo "[ENTRYPOINT] Host extraído da DATABASE_URL: $DB_HOST_EXTRACTED"
else
    echo "[ENTRYPOINT] ERRO: DATABASE_URL não definida!"
    exit 1
fi

# Cria .env
cat > .env << EOF
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY=${APP_KEY}
APP_URL=${APP_URL:-https://smcomponentes.onrender.com}
ASSET_URL=${ASSET_URL:-$APP_URL}
VITE_APP_URL=${VITE_APP_URL:-$APP_URL}

APP_STORAGE=/var/www/html/storage
VIEW_COMPILED_PATH=/var/www/html/storage/framework/views

# Conexão com o banco via DATABASE_URL
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
# 3. TESTE DE CONEXÃO (usando DATABASE_URL)
# ============================================================
echo "[ENTRYPOINT] Testando conexão com o banco via DATABASE_URL..."

MAX_RETRIES=20
COUNT=0
CONNECTED=0

while [ $COUNT -lt $MAX_RETRIES ]; do
    ERROR_MSG=$(php -r "
        try {
            \$pdo = new PDO('$DATABASE_URL');
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
    fi
    COUNT=$((COUNT + 1))
    sleep 2
done

if [ $CONNECTED -eq 0 ]; then
    echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao banco."
    echo "[ENTRYPOINT] DATABASE_URL: $DATABASE_URL"
    echo "[ENTRYPOINT] Verifique a string de conexão e a conectividade."
    exit 1
fi

# ============================================================
# 4. Limpeza de caches
# ============================================================
echo "[ENTRYPOINT] Limpando caches..."
php artisan config:clear --no-interaction || true
php artisan cache:clear --no-interaction || true
php artisan view:clear --no-interaction || true
php artisan route:clear --no-interaction || true

# ============================================================
# 5. Gerar APP_KEY se não existir
# ============================================================
if grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate --no-interaction
fi

# ============================================================
# 6. Recriar autoload otimizado
# ============================================================
echo "[ENTRYPOINT] Recriando autoload otimizado..."
composer dump-autoload --optimize --no-interaction

# ============================================================
# 7. Executar package:discover
# ============================================================
echo "[ENTRYPOINT] Executando package:discover..."
php artisan package:discover --no-ansi --no-interaction || true

# ============================================================
# 8. Migrações e Seeders
# ============================================================
if [ "$FORCE_MIGRATION" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrations..."
    php artisan migrate --force --no-interaction || {
        echo "[ENTRYPOINT] ❌ ERRO: Falha nas migrations."
        exit 1
    }
fi

if [ "$FORCE_SEED" = "true" ]; then
    echo "[ENTRYPOINT] Executando seeders..."
    php artisan db:seed --force --no-interaction || {
        echo "[ENTRYPOINT] ❌ ERRO: Falha nos seeders."
        exit 1
    }
fi

# ============================================================
# 9. Otimizações para produção
# ============================================================
if [ "$APP_ENV" = "production" ]; then
    echo "[ENTRYPOINT] Otimizando cache para produção..."
    php artisan config:cache --no-interaction || true
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction || true
fi

echo "[ENTRYPOINT] ✅ Inicialização concluída. Iniciando servidor..."
exec "$@"