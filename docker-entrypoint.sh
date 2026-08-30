#!/bin/sh
set -e

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

cd /var/www/html

# ============================================================
# 1. Criar diretórios obrigatórios
# ============================================================
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
touch storage/logs/laravel.log
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

# ============================================================
# 2. Extrair parâmetros do banco da DATABASE_URL
# ============================================================
echo "[ENTRYPOINT] Extraindo parâmetros da DATABASE_URL..."

if [ -z "$DATABASE_URL" ]; then
    DATABASE_URL="postgresql://loja_virtual_eilu_user:FeNuDK8XRL0XoI7WwqCgvCOJzT6d0Kof@dpg-daa41s9f2nfc7395g1dg-a.oregon-postgres.render.com/loja_virtual_eilu?sslmode=require"
    echo "[ENTRYPOINT] Usando DATABASE_URL embutida."
else
    echo "[ENTRYPOINT] Usando DATABASE_URL do ambiente."
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
# 3. Criar .env a partir do .env.example
# ============================================================
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "[ENTRYPOINT] .env criado a partir do .env.example"
    else
        echo "[ENTRYPOINT] ERRO: .env.example não encontrado!"
        exit 1
    fi
fi

# ============================================================
# 4. Substituir variáveis no formato ${VAR}
# ============================================================
echo "[ENTRYPOINT] Substituindo variáveis de ambiente no .env..."

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

# ============================================================
# 5. Forçar variáveis críticas
# ============================================================
echo "[ENTRYPOINT] Forçando variáveis críticas..."

sed -i '/^APP_ENV=/d' .env
sed -i '/^APP_DEBUG=/d' .env
sed -i '/^APP_KEY=/d' .env
sed -i '/^APP_URL=/d' .env
sed -i '/^ASSET_URL=/d' .env
sed -i '/^VITE_APP_URL=/d' .env
sed -i '/^SESSION_DRIVER=/d' .env
sed -i '/^CACHE_DRIVER=/d' .env
sed -i '/^FORCE_HTTPS=/d' .env
sed -i '/^DB_HOST=/d' .env
sed -i '/^DB_PORT=/d' .env
sed -i '/^DB_DATABASE=/d' .env
sed -i '/^DB_USERNAME=/d' .env
sed -i '/^DB_PASSWORD=/d' .env
sed -i '/^DB_SSLMODE=/d' .env

echo "APP_ENV=${APP_ENV:-production}" >> .env
echo "APP_DEBUG=${APP_DEBUG:-false}" >> .env
echo "APP_KEY=${APP_KEY}" >> .env
echo "APP_URL=${APP_URL:-https://smcomponentes.onrender.com}" >> .env
echo "ASSET_URL=${APP_URL:-https://smcomponentes.onrender.com}" >> .env
echo "VITE_APP_URL=${APP_URL:-https://smcomponentes.onrender.com}" >> .env
echo "SESSION_DRIVER=file" >> .env
echo "CACHE_DRIVER=file" >> .env
echo "FORCE_HTTPS=true" >> .env
echo "DB_HOST=$DB_HOST" >> .env
echo "DB_PORT=$DB_PORT" >> .env
echo "DB_DATABASE=$DB_DATABASE" >> .env
echo "DB_USERNAME=$DB_USERNAME" >> .env
echo "DB_PASSWORD=$DB_PASSWORD" >> .env
echo "DB_SSLMODE=require" >> .env

# ============================================================
# 6. Testar conexão com o banco
# ============================================================
echo "[ENTRYPOINT] Testando conexão com PostgreSQL..."

DSN="pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE;sslmode=require"
MAX_RETRIES=10
COUNT=0
CONNECTED=0

while [ $COUNT -lt $MAX_RETRIES ]; do
    ERROR_MSG=$(php -r "
        try {
            new PDO('$DSN', '$DB_USERNAME', '$DB_PASSWORD');
            echo 'ok';
        } catch (PDOException \$e) {
            echo 'erro: ' . \$e->getMessage();
        }
    " 2>&1)

    if echo "$ERROR_MSG" | grep -q '^ok$'; then
        echo "[ENTRYPOINT] ✅ Conexão com PostgreSQL bem-sucedida!"
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
# 7. CRIAR TABELAS DE CACHE E SESSÃO (para eliminar erros)
# ============================================================
echo "[ENTRYPOINT] Criando tabelas de cache e sessão (se necessário)..."

# Gera as migrations para cache e session (se não existirem)
php artisan cache:table --no-interaction 2>/dev/null || true
php artisan session:table --no-interaction 2>/dev/null || true

# Cria as tabelas (migrations)
php artisan migrate --force --no-interaction || true

# ============================================================
# 8. Limpar caches e outras tarefas
# ============================================================
php artisan optimize:clear --no-interaction || true
php artisan package:discover --ansi --no-interaction || true

if [ -z "${APP_KEY:-}" ] || grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --force --no-interaction
fi

if [ "${FORCE_MIGRATION:-false}" = "true" ]; then
    php artisan migrate --force --no-interaction
fi

if [ "${FORCE_SEED:-false}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction
fi

# ============================================================
# 9. Porta e finalização
# ============================================================
export PORT="${PORT:-10000}"
echo "[ENTRYPOINT] ✅ Inicialização concluída. Servidor na porta $PORT."
exec "$@"