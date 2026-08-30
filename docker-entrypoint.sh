#!/bin/sh
set -e

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

cd /var/www/html

# ============================================================
# 1. Criar diretórios
# ============================================================
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
touch storage/logs/laravel.log
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

# ============================================================
# 2. Extrair DATABASE_URL (embutida ou do ambiente)
# ============================================================
if [ -z "$DATABASE_URL" ]; then
    DATABASE_URL="postgresql://loja_virtual_eilu_user:FeNuDK8XRL0XoI7WwqCgvCOJzT6d0Kof@dpg-daa41s9f2nfc7395g1dg-a.oregon-postgres.render.com/loja_virtual_eilu?sslmode=require"
    echo "[ENTRYPOINT] Usando DATABASE_URL embutida."
fi

EXTRACT=$(php -r "
    \$url = parse_url('$DATABASE_URL');
    \$host = \$url['host'] ?? '';
    \$port = \$url['port'] ?? 5432;
    \$dbname = ltrim(\$url['path'] ?? '', '/');
    \$user = \$url['user'] ?? '';
    \$pass = \$url['pass'] ?? '';
    echo \"DB_HOST=\$host DB_PORT=\$port DB_DATABASE=\$dbname DB_USERNAME=\$user DB_PASSWORD=\$pass\";
" 2>/dev/null)

eval "$EXTRACT"
export DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD

echo "[ENTRYPOINT] Banco: $DB_HOST:$DB_PORT/$DB_DATABASE"

# ============================================================
# 3. Criar .env
# ============================================================
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Substituir variáveis (fallback manual)
echo "[ENTRYPOINT] Substituindo variáveis no .env..."
if command -v envsubst >/dev/null 2>&1; then
    envsubst < .env > .env.tmp && mv .env.tmp .env
else
    VARS=$(grep -oE '\$\{[A-Za-z_][A-Za-z0-9_]*\}' .env | sed 's/\${//g' | sed 's/}//g' | sort -u)
    for VAR in $VARS; do
        eval VALUE=\$$VAR
        if [ -n "$VALUE" ]; then
            ESCAPED_VALUE=$(echo "$VALUE" | sed -e 's/[\/&]/\\&/g')
            sed -i "s/\${$VAR}/$ESCAPED_VALUE/g" .env
            sed -i "s/$$VAR/$ESCAPED_VALUE/g" .env
        fi
    done
fi

# Forçar variáveis críticas
sed -i '/^APP_ENV=/d' .env; echo "APP_ENV=${APP_ENV:-production}" >> .env
sed -i '/^APP_DEBUG=/d' .env; echo "APP_DEBUG=${APP_DEBUG:-false}" >> .env
sed -i '/^APP_KEY=/d' .env; echo "APP_KEY=${APP_KEY}" >> .env
sed -i '/^APP_URL=/d' .env; echo "APP_URL=${APP_URL:-https://smcomponentes.onrender.com}" >> .env
sed -i '/^SESSION_DRIVER=/d' .env; echo "SESSION_DRIVER=file" >> .env
sed -i '/^CACHE_DRIVER=/d' .env; echo "CACHE_DRIVER=file" >> .env
sed -i '/^DB_HOST=/d' .env; echo "DB_HOST=$DB_HOST" >> .env
sed -i '/^DB_PORT=/d' .env; echo "DB_PORT=$DB_PORT" >> .env
sed -i '/^DB_DATABASE=/d' .env; echo "DB_DATABASE=$DB_DATABASE" >> .env
sed -i '/^DB_USERNAME=/d' .env; echo "DB_USERNAME=$DB_USERNAME" >> .env
sed -i '/^DB_PASSWORD=/d' .env; echo "DB_PASSWORD=$DB_PASSWORD" >> .env
sed -i '/^DB_SSLMODE=/d' .env; echo "DB_SSLMODE=require" >> .env

# ============================================================
# 4. Testar conexão com o banco
# ============================================================
DSN="pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE;sslmode=require"
echo "[ENTRYPOINT] Testando conexão com PostgreSQL..."
MAX_RETRIES=10
COUNT=0
CONNECTED=0
while [ $COUNT -lt $MAX_RETRIES ]; do
    ERROR_MSG=$(php -r "try { new PDO('$DSN', '$DB_USERNAME', '$DB_PASSWORD'); echo 'ok'; } catch (PDOException \$e) { echo 'erro: ' . \$e->getMessage(); }" 2>&1)
    if echo "$ERROR_MSG" | grep -q '^ok$'; then
        echo "[ENTRYPOINT] ✅ Conectado!"
        CONNECTED=1
        break
    else
        echo "[ENTRYPOINT] Tentativa $((COUNT+1))/$MAX_RETRIES falhou: $ERROR_MSG"
        COUNT=$((COUNT + 1))
        sleep 3
    fi
done
if [ $CONNECTED -eq 0 ]; then
    echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao PostgreSQL."
    exit 1
fi

# ============================================================
# 5. CRIAR TABELAS (cache, sessions, etc.) E RODAR MIGRAÇÕES + SEEDERS
# ============================================================
echo "[ENTRYPOINT] Criando tabelas de cache e sessão (se necessário)..."
php artisan cache:table --no-interaction 2>/dev/null || true
php artisan session:table --no-interaction 2>/dev/null || true

echo "[ENTRYPOINT] Executando migrações e seeders..."
php artisan migrate --force --no-interaction || { echo "[ENTRYPOINT] ❌ Falha nas migrações."; exit 1; }

# Se FORCE_SEED=true (ou se quiser sempre rodar), rode seeders
if [ "${FORCE_SEED:-false}" = "true" ] || [ "${FORCE_MIGRATION:-false}" = "true" ]; then
    echo "[ENTRYPOINT] Executando seeders..."
    php artisan db:seed --force --no-interaction || echo "[ENTRYPOINT] ⚠️ Seeders falharam, continuando..."
fi

# ============================================================
# 6. Limpeza de caches, APP_KEY, package discover, etc.
# ============================================================
php artisan optimize:clear --no-interaction || true
php artisan package:discover --ansi --no-interaction || true

if [ -z "${APP_KEY:-}" ] || grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --force --no-interaction
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction
fi

# ============================================================
# 7. Porta e finalização
# ============================================================
export PORT="${PORT:-10000}"
echo "[ENTRYPOINT] ✅ Inicialização concluída. Servidor na porta $PORT."
exec "$@"