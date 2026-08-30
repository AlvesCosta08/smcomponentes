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
# 3. Aplicar variáveis de ambiente (sobrescreve)
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

# ============================================================
# 4. USAR DATABASE_URL (se disponível)
# ============================================================
if [ -n "$DATABASE_URL" ]; then
    echo "[ENTRYPOINT] DATABASE_URL detectada. Usando string de conexão."
    sed -i "/^DATABASE_URL=/d" .env
    echo "DATABASE_URL=$DATABASE_URL" >> .env
    # Remove variáveis individuais para evitar conflitos
    sed -i "/^DB_HOST=/d" .env
    sed -i "/^DB_PORT=/d" .env
    sed -i "/^DB_DATABASE=/d" .env
    sed -i "/^DB_USERNAME=/d" .env
    sed -i "/^DB_PASSWORD=/d" .env
    sed -i "/^DB_SSLMODE=/d" .env
    # Força conexão pgsql
    sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
    export DB_CONNECTION=pgsql
else
    # Se DATABASE_URL não estiver definida, constrói manualmente com SSL
    if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
        # Corrige hostname (pdpg -> dpg)
        DB_HOST_CORRECTED=$(echo "$DB_HOST" | sed 's/^pdpg/dpg/')
        if [ "$DB_HOST_CORRECTED" != "$DB_HOST" ]; then
            echo "[ENTRYPOINT] 🔄 Corrigido hostname para: $DB_HOST_CORRECTED"
            export DB_HOST="$DB_HOST_CORRECTED"
            sed -i "s/^DB_HOST=.*/DB_HOST=$DB_HOST_CORRECTED/" .env
        fi

        # Se não tiver domínio, adiciona .oregon-postgres.render.com
        if ! echo "$DB_HOST" | grep -q '\.'; then
            TEST_HOST="${DB_HOST}.oregon-postgres.render.com"
            if nslookup "$TEST_HOST" >/dev/null 2>&1; then
                echo "[ENTRYPOINT] ✅ Hostname corrigido para: $TEST_HOST"
                export DB_HOST="$TEST_HOST"
                sed -i "s/^DB_HOST=.*/DB_HOST=$TEST_HOST/" .env
            fi
        fi
    fi
fi

# ============================================================
# 5. TESTE DE CONEXÃO (usa DATABASE_URL se disponível)
# ============================================================
if [ -n "$DATABASE_URL" ]; then
    echo "[ENTRYPOINT] Testando conexão via DATABASE_URL..."
    MAX_RETRIES=30
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
        exit 1
    fi
else
    # Teste manual com SSL
    if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
        echo "[ENTRYPOINT] Testando conexão SSL em $DB_HOST:$DB_PORT..."
        MAX_RETRIES=30
        COUNT=0
        CONNECTED=0
        while [ $COUNT -lt $MAX_RETRIES ]; do
            ERROR_MSG=$(php -r "
                try {
                    \$dsn = 'pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE;sslmode=require';
                    new PDO(\$dsn, '$DB_USERNAME', '$DB_PASSWORD');
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
            exit 1
        fi
    fi
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

# ============================================================
# 7. MIGRAÇÕES E SEEDERS (se habilitados)
# ============================================================
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

# ============================================================
# 8. Otimizações para produção
# ============================================================
if [ "$APP_ENV" = "production" ]; then
    echo "[ENTRYPOINT] Otimizando cache para produção..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "[ENTRYPOINT] ✅ Inicialização concluída. Iniciando servidor..."
exec "$@"