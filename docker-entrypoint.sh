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
# 2. Criar .env a partir do .env.example
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
# 3. Aplicar variáveis de ambiente ao .env
# ============================================================
echo "[ENTRYPOINT] Aplicando variáveis de ambiente ao .env..."

ENV_VARS="APP_ENV APP_DEBUG APP_KEY APP_URL ASSET_URL VITE_APP_URL \
          DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DB_SSLMODE \
          CACHE_DRIVER SESSION_DRIVER SESSION_SECURE_COOKIE FORCE_HTTPS \
          BROADCAST_DRIVER QUEUE_CONNECTION LOG_CHANNEL LOG_LEVEL"

for VAR in $ENV_VARS; do
    eval VALUE=\$$VAR
    if [ -n "$VALUE" ]; then
        ESCAPED_VALUE=$(echo "$VALUE" | sed -e 's/[\/&]/\\&/g')
        sed -i "/^$VAR=/d" .env
        echo "$VAR=$VALUE" >> .env
        echo "[ENTRYPOINT] $VAR definido"
    fi
done

echo "APP_STORAGE=/var/www/html/storage" >> .env
echo "VIEW_COMPILED_PATH=/var/www/html/storage/framework/views" >> .env

# ============================================================
# 4. CORREÇÃO AUTOMÁTICA DO HOSTNAME (várias combinações)
# ============================================================
if [ -n "$DB_HOST" ]; then
    ORIGINAL_HOST="$DB_HOST"
    echo "[ENTRYPOINT] Hostname original: $ORIGINAL_HOST"

    # Lista de combinações para testar
    VARIANTS="
        $ORIGINAL_HOST
        $ORIGINAL_HOST.render.com
        $ORIGINAL_HOST.oregon-postgres.render.com
        $ORIGINAL_HOST.us-east-1.postgres.render.com
        $ORIGINAL_HOST.postgres.render.com
    "

    RESOLVED_HOST=""
    for HOST in $VARIANTS; do
        if nslookup "$HOST" >/dev/null 2>&1; then
            echo "[ENTRYPOINT] ✅ Hostname resolvido: $HOST"
            RESOLVED_HOST="$HOST"
            break
        else
            echo "[ENTRYPOINT] Tentativa falhou: $HOST"
        fi
    done

    if [ -n "$RESOLVED_HOST" ]; then
        export DB_HOST="$RESOLVED_HOST"
        sed -i "s/^DB_HOST=.*/DB_HOST=$RESOLVED_HOST/" .env
        echo "[ENTRYPOINT] Hostname atualizado para: $DB_HOST"
    else
        echo "[ENTRYPOINT] ⚠️  Nenhuma combinação resolveu. Usando original: $ORIGINAL_HOST"
        export DB_HOST="$ORIGINAL_HOST"
    fi
fi

# ============================================================
# 5. TESTE DE CONEXÃO COM O BANCO
# ============================================================
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
    echo "[ENTRYPOINT] Testando conexão com o banco em $DB_HOST:$DB_PORT..."
    MAX_RETRIES=30
    COUNT=0
    CONNECTED=0
    while [ $COUNT -lt $MAX_RETRIES ]; do
        ERROR_MSG=$(php -r "try { new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD'); echo 'ok'; } catch (PDOException \$e) { echo 'erro: ' . \$e->getMessage(); }" 2>&1)
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
        echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao banco após $MAX_RETRIES tentativas."
        echo "[ENTRYPOINT] Último erro: $ERROR_MSG"
        echo "[ENTRYPOINT] Verifique se o hostname está correto. O valor atual é: $DB_HOST"
        exit 1
    fi
else
    echo "[ENTRYPOINT] ⚠️  Variáveis de banco incompletas. Pulando teste de conexão."
fi

# ============================================================
# 6. Limpa caches e executa tarefas
# ============================================================
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

if grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate
fi

echo "[ENTRYPOINT] Recriando autoload otimizado..."
composer dump-autoload --optimize

php artisan package:discover --no-ansi || true

if [ "$FORCE_MIGRATION" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrations..."
    php artisan migrate --force || {
        echo "[ENTRYPOINT] ❌ ERRO: Falha nas migrations."
        exit 1
    }
fi

if [ "$FORCE_SEED" = "true" ]; then
    echo "[ENTRYPOINT] Executando seeders..."
    php artisan db:seed --force || {
        echo "[ENTRYPOINT] ❌ ERRO: Falha nos seeders."
        exit 1
    }
fi

if [ "$APP_ENV" = "production" ]; then
    echo "[ENTRYPOINT] Otimizando cache para produção..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "[ENTRYPOINT] ✅ Inicialização concluída. Iniciando servidor..."
exec "$@"