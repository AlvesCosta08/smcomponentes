#!/bin/sh
set -e

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

# ============================================================
# 1. Pastas de cache
# ============================================================
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
chmod -R 777 storage bootstrap/cache

# ============================================================
# 2. Variáveis essenciais
# ============================================================
export APP_STORAGE=/var/www/html/storage
export VIEW_COMPILED_PATH=/var/www/html/storage/framework/views
export SESSION_DRIVER=${SESSION_DRIVER:-file}
export CACHE_DRIVER=${CACHE_DRIVER:-file}

# ============================================================
# 3. Configuração do banco (DATABASE_URL embutida)
# ============================================================
# Se a variável DATABASE_URL não estiver definida no ambiente, usa a embutida
if [ -z "$DATABASE_URL" ]; then
    export DATABASE_URL="postgresql://loja_virtual_eilu_user:FeNuDK8XRL0XoI7WwqCgvCOJzT6d0Kof@dpg-daa41s9f2nfc7395g1dg-a.oregon-postgres.render.com/loja_virtual_eilu?sslmode=require"
    echo "[ENTRYPOINT] Usando DATABASE_URL embutida."
else
    echo "[ENTRYPOINT] Usando DATABASE_URL do ambiente."
fi

# ============================================================
# 4. Verificar se o driver PDO_PGSQL está carregado
# ============================================================
echo "[ENTRYPOINT] Verificando extensões PHP..."
if php -m | grep -q pdo_pgsql; then
    echo "[ENTRYPOINT] ✅ PDO_PGSQL está carregado."
else
    echo "[ENTRYPOINT] ❌ PDO_PGSQL NÃO está carregado. Tentando habilitar..."
    # Tenta habilitar a extensão via php.ini (fallback)
    echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini
    if php -m | grep -q pdo_pgsql; then
        echo "[ENTRYPOINT] ✅ PDO_PGSQL habilitado com sucesso."
    else
        echo "[ENTRYPOINT] ❌ ERRO CRÍTICO: Não foi possível carregar PDO_PGSQL."
        exit 1
    fi
fi

# ============================================================
# 5. Gerar .env
# ============================================================
echo "[ENTRYPOINT] Gerando .env..."
rm -f .env

cat > .env << EOF
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY=${APP_KEY}
APP_URL=${APP_URL:-https://smcomponentes.onrender.com}
ASSET_URL=${ASSET_URL:-$APP_URL}
VITE_APP_URL=${VITE_APP_URL:-$APP_URL}

APP_STORAGE=$APP_STORAGE
VIEW_COMPILED_PATH=$VIEW_COMPILED_PATH

DATABASE_URL=$DATABASE_URL
DB_CONNECTION=pgsql

CACHE_DRIVER=$CACHE_DRIVER
SESSION_DRIVER=$SESSION_DRIVER
SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}
FORCE_HTTPS=${FORCE_HTTPS:-true}
BROADCAST_DRIVER=${BROADCAST_DRIVER:-log}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-error}
EOF

echo "[ENTRYPOINT] .env gerado com sucesso."

# ============================================================
# 6. Teste de conexão com o banco
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
    echo "[ENTRYPOINT] Último erro: $ERROR_MSG"
    exit 1
fi

# ============================================================
# 7. Limpeza de caches
# ============================================================
php artisan config:clear --no-interaction || true
php artisan cache:clear --no-interaction || true
php artisan view:clear --no-interaction || true
php artisan route:clear --no-interaction || true

# ============================================================
# 8. APP_KEY
# ============================================================
if grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --no-interaction
fi

# ============================================================
# 9. Autoload
# ============================================================
composer dump-autoload --optimize --no-interaction

# ============================================================
# 10. Package discover
# ============================================================
php artisan package:discover --no-ansi --no-interaction || true

# ============================================================
# 11. Migrations e Seeders
# ============================================================
if [ "$FORCE_MIGRATION" = "true" ]; then
    php artisan migrate --force --no-interaction || exit 1
fi

if [ "$FORCE_SEED" = "true" ]; then
    php artisan db:seed --force --no-interaction || exit 1
fi

# ============================================================
# 12. Otimizações
# ============================================================
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache --no-interaction || true
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction || true
fi

echo "[ENTRYPOINT] ✅ Inicialização concluída. Iniciando servidor..."
exec "$@"