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
# 2. Definir variáveis essenciais (com fallbacks)
# ============================================================
export APP_STORAGE=/var/www/html/storage
export VIEW_COMPILED_PATH=/var/www/html/storage/framework/views
export SESSION_DRIVER=${SESSION_DRIVER:-file}
export CACHE_DRIVER=${CACHE_DRIVER:-file}

# ============================================================
# 3. GERAR .env DIRETAMENTE DAS VARIÁVEIS DE AMBIENTE
# ============================================================
echo "[ENTRYPOINT] Gerando .env a partir das variáveis de ambiente..."

# Remove .env existente se houver
rm -f .env

# Cria .env com as variáveis essenciais
cat > .env << EOF
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY=${APP_KEY}
APP_URL=${APP_URL:-https://smcomponentes.onrender.com}
ASSET_URL=${ASSET_URL:-$APP_URL}
VITE_APP_URL=${VITE_APP_URL:-$APP_URL}

APP_STORAGE=$APP_STORAGE
VIEW_COMPILED_PATH=$VIEW_COMPILED_PATH

# Banco de dados (usa DATABASE_URL se disponível, senão usa variáveis individuais)
EOF

# ============================================================
# 4. Adiciona configuração do banco (prioriza DATABASE_URL)
# ============================================================
if [ -n "$DATABASE_URL" ]; then
    echo "DATABASE_URL=$DATABASE_URL" >> .env
    echo "DB_CONNECTION=pgsql" >> .env
    echo "[ENTRYPOINT] DATABASE_URL configurada"
else
    # Fallback para variáveis individuais
    echo "DB_CONNECTION=${DB_CONNECTION:-pgsql}" >> .env
    echo "DB_HOST=${DB_HOST}" >> .env
    echo "DB_PORT=${DB_PORT:-5432}" >> .env
    echo "DB_DATABASE=${DB_DATABASE}" >> .env
    echo "DB_USERNAME=${DB_USERNAME}" >> .env
    echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
    echo "DB_SSLMODE=${DB_SSLMODE:-require}" >> .env
fi

# ============================================================
# 5. Adiciona outras variáveis
# ============================================================
cat >> .env << EOF
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
# 6. CORREÇÃO DE HOSTNAME (se necessário)
# ============================================================
if [ -n "$DB_HOST" ] && [ -z "$DATABASE_URL" ]; then
    DB_HOST_CORRECTED=$(echo "$DB_HOST" | sed 's/^pdpg/dpg/')
    if [ "$DB_HOST_CORRECTED" != "$DB_HOST" ]; then
        echo "[ENTRYPOINT] 🔄 Corrigido hostname para: $DB_HOST_CORRECTED"
        sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST_CORRECTED/" .env
        export DB_HOST="$DB_HOST_CORRECTED"
    fi
    if ! echo "$DB_HOST" | grep -q '\.'; then
        TEST_HOST="${DB_HOST}.oregon-postgres.render.com"
        if nslookup "$TEST_HOST" >/dev/null 2>&1; then
            echo "[ENTRYPOINT] ✅ Hostname corrigido para: $TEST_HOST"
            sed -i "s/^DB_HOST=.*/DB_HOST=$TEST_HOST/" .env
            export DB_HOST="$TEST_HOST"
        fi
    fi
fi

# ============================================================
# 7. TESTE DE CONEXÃO COM O BANCO
# ============================================================
echo "[ENTRYPOINT] Testando conexão com o banco..."

if [ -n "$DATABASE_URL" ]; then
    CONNECTION_STRING="$DATABASE_URL"
else
    # Monta DSN manual
    CONNECTION_STRING="pgsql:host=${DB_HOST};port=${DB_PORT:-5432};dbname=${DB_DATABASE};sslmode=${DB_SSLMODE:-require}"
fi

MAX_RETRIES=20
COUNT=0
CONNECTED=0

while [ $COUNT -lt $MAX_RETRIES ]; do
    ERROR_MSG=$(php -r "
        try {
            \$pdo = new PDO('$CONNECTION_STRING', '${DB_USERNAME}', '${DB_PASSWORD}');
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
    echo "[ENTRYPOINT] Host testado: ${DB_HOST}"
    echo "[ENTRYPOINT] Database: ${DB_DATABASE}"
    echo "[ENTRYPOINT] Usuário: ${DB_USERNAME}"
    echo "[ENTRYPOINT] Verifique as credenciais e a conectividade."
    exit 1
fi

# ============================================================
# 8. Limpeza de caches
# ============================================================
echo "[ENTRYPOINT] Limpando caches..."
php artisan config:clear --no-interaction || true
php artisan cache:clear --no-interaction || true
php artisan view:clear --no-interaction || true
php artisan route:clear --no-interaction || true

# ============================================================
# 9. Gerar APP_KEY se não existir
# ============================================================
if grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate --no-interaction
fi

# ============================================================
# 10. Recriar autoload otimizado
# ============================================================
echo "[ENTRYPOINT] Recriando autoload otimizado..."
composer dump-autoload --optimize --no-interaction

# ============================================================
# 11. Executar package:discover
# ============================================================
echo "[ENTRYPOINT] Executando package:discover..."
php artisan package:discover --no-ansi --no-interaction || true

# ============================================================
# 12. Migrações e Seeders
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
# 13. Otimizações para produção
# ============================================================
if [ "$APP_ENV" = "production" ]; then
    echo "[ENTRYPOINT] Otimizando cache para produção..."
    php artisan config:cache --no-interaction || true
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction || true
fi

echo "[ENTRYPOINT] ✅ Inicialização concluída. Iniciando servidor..."
exec "$@"