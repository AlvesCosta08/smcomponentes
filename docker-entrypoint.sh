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
# 2. Definir e exportar variáveis de caminho do storage
# ============================================================
export APP_STORAGE=/var/www/html/storage
export VIEW_COMPILED_PATH=/var/www/html/storage/framework/views
export SESSION_DRIVER=${SESSION_DRIVER:-file}
export CACHE_DRIVER=${CACHE_DRIVER:-file}

# ============================================================
# 3. Criar .env a partir do .env.example (com envsubst)
# ============================================================
if [ -f .env.example ]; then
    echo "[ENTRYPOINT] Gerando .env a partir do .env.example com envsubst..."
    # Substitui variáveis no formato ${VAR} ou $VAR pelos valores do ambiente
    envsubst < .env.example > .env
    echo "[ENTRYPOINT] .env gerado com sucesso."
else
    echo "[ENTRYPOINT] ERRO: .env.example não encontrado!"
    exit 1
fi

# ============================================================
# 4. Forçar variáveis essenciais (sobrescrevendo se necessário)
# ============================================================
echo "[ENTRYPOINT] Forçando variáveis essenciais no .env..."

# Remove linhas existentes para substituir
sed -i '/^APP_STORAGE=/d' .env
sed -i '/^VIEW_COMPILED_PATH=/d' .env
sed -i '/^CACHE_DRIVER=/d' .env
sed -i '/^SESSION_DRIVER=/d' .env

echo "APP_STORAGE=$APP_STORAGE" >> .env
echo "VIEW_COMPILED_PATH=$VIEW_COMPILED_PATH" >> .env
echo "CACHE_DRIVER=$CACHE_DRIVER" >> .env
echo "SESSION_DRIVER=$SESSION_DRIVER" >> .env

# Aplica as demais variáveis do ambiente (Render)
ENV_VARS="APP_ENV APP_DEBUG APP_KEY APP_URL ASSET_URL VITE_APP_URL \
          DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD DB_SSLMODE \
          SESSION_SECURE_COOKIE FORCE_HTTPS BROADCAST_DRIVER QUEUE_CONNECTION \
          LOG_CHANNEL LOG_LEVEL"

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
# 5. USAR DATABASE_URL se disponível (sobrescreve variáveis)
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
    # Força DB_CONNECTION=pgsql
    sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/" .env
    export DB_CONNECTION=pgsql
fi

# ============================================================
# 6. Teste de conexão com o banco (usando DATABASE_URL se possível)
# ============================================================
if [ -n "$DATABASE_URL" ]; then
    echo "[ENTRYPOINT] Testando conexão via DATABASE_URL..."
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
        exit 1
    fi
else
    # Fallback manual com correção de hostname
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
        echo "[ENTRYPOINT] Testando conexão SSL em $DB_HOST:$DB_PORT..."
        MAX_RETRIES=20
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
# 7. Limpeza de caches (com APP_STORAGE já definido)
# ============================================================
echo "[ENTRYPOINT] Limpando caches..."
php artisan config:clear --no-interaction || true
php artisan cache:clear --no-interaction || true
php artisan view:clear --no-interaction || true
php artisan route:clear --no-interaction || true

# ============================================================
# 8. Gerar APP_KEY se não existir
# ============================================================
if grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate --no-interaction
fi

# ============================================================
# 9. Recriar autoload otimizado
# ============================================================
echo "[ENTRYPOINT] Recriando autoload otimizado..."
composer dump-autoload --optimize --no-interaction

# ============================================================
# 10. Executar package:discover
# ============================================================
echo "[ENTRYPOINT] Executando package:discover..."
php artisan package:discover --no-ansi --no-interaction || true

# ============================================================
# 11. Migrações e Seeders (se habilitados)
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
# 12. Otimizações para produção (se APP_ENV=production)
# ============================================================
if [ "$APP_ENV" = "production" ]; then
    echo "[ENTRYPOINT] Otimizando cache para produção..."
    php artisan config:cache --no-interaction || true
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction || true
fi

echo "[ENTRYPOINT] ✅ Inicialização concluída. Iniciando servidor..."
exec "$@"