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

# Se DATABASE_URL estiver definida, use-a
if [ -n "$DATABASE_URL" ]; then
    echo "[ENTRYPOINT] DATABASE_URL encontrada. Usando string de conexão."
    # Extrai componentes
    DB_USERNAME=$(echo "$DATABASE_URL" | sed -n 's/.*:\/\/\([^:]*\):.*/\1/p')
    DB_PASSWORD=$(echo "$DATABASE_URL" | sed -n 's/.*:\/\/[^:]*:\([^@]*\)@.*/\1/p')
    DB_HOST=$(echo "$DATABASE_URL" | sed -n 's/.*@\([^:]*\):.*/\1/p')
    DB_PORT=$(echo "$DATABASE_URL" | sed -n 's/.*:\([0-9]*\)\/.*/\1/p')
    DB_DATABASE=$(echo "$DATABASE_URL" | sed -n 's/.*\/\(.*\)$/\1/p')
    DB_SSLMODE="require"

    # Atualiza .env com os valores extraídos
    sed -i "/^DB_CONNECTION=/d" .env
    sed -i "/^DB_HOST=/d" .env
    sed -i "/^DB_PORT=/d" .env
    sed -i "/^DB_DATABASE=/d" .env
    sed -i "/^DB_USERNAME=/d" .env
    sed -i "/^DB_PASSWORD=/d" .env
    sed -i "/^DB_SSLMODE=/d" .env
    echo "DB_CONNECTION=pgsql" >> .env
    echo "DB_HOST=$DB_HOST" >> .env
    echo "DB_PORT=$DB_PORT" >> .env
    echo "DB_DATABASE=$DB_DATABASE" >> .env
    echo "DB_USERNAME=$DB_USERNAME" >> .env
    echo "DB_PASSWORD=$DB_PASSWORD" >> .env
    echo "DB_SSLMODE=$DB_SSLMODE" >> .env
else
    # Usa variáveis individuais
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
fi

echo "APP_STORAGE=/var/www/html/storage" >> .env
echo "VIEW_COMPILED_PATH=/var/www/html/storage/framework/views" >> .env

# ============================================================
# 4. CORREÇÃO DO HOSTNAME E TESTE DE CONEXÃO
# ============================================================
if [ -n "$DB_HOST" ]; then
    ORIGINAL_HOST="$DB_HOST"
    echo "[ENTRYPOINT] Hostname original: $ORIGINAL_HOST"

    # Corrige "pdpg" para "dpg"
    CORRECTED_HOST=$(echo "$ORIGINAL_HOST" | sed 's/^pdpg/dpg/')
    if [ "$CORRECTED_HOST" != "$ORIGINAL_HOST" ]; then
        echo "[ENTRYPOINT] 🔄 Corrigido 'pdpg' para 'dpg': $CORRECTED_HOST"
        ORIGINAL_HOST="$CORRECTED_HOST"
    fi

    # Adiciona domínio se necessário
    if ! echo "$ORIGINAL_HOST" | grep -q '\.render\.com$'; then
        TEST_HOST="${ORIGINAL_HOST}.oregon-postgres.render.com"
        echo "[ENTRYPOINT] Tentando hostname com domínio: $TEST_HOST"
        if nslookup "$TEST_HOST" >/dev/null 2>&1; then
            echo "[ENTRYPOINT] ✅ Hostname corrigido para: $TEST_HOST"
            export DB_HOST="$TEST_HOST"
            sed -i "s/^DB_HOST=.*/DB_HOST=$TEST_HOST/" .env
        else
            TEST_HOST2="${ORIGINAL_HOST}.render.com"
            if nslookup "$TEST_HOST2" >/dev/null 2>&1; then
                echo "[ENTRYPOINT] ✅ Hostname corrigido para: $TEST_HOST2"
                export DB_HOST="$TEST_HOST2"
                sed -i "s/^DB_HOST=.*/DB_HOST=$TEST_HOST2/" .env
            else
                echo "[ENTRYPOINT] ⚠️  Nenhum domínio resolveu. Mantendo original."
                export DB_HOST="$ORIGINAL_HOST"
            fi
        fi
    else
        export DB_HOST="$ORIGINAL_HOST"
    fi
fi

# ============================================================
# 5. TESTE DE CONEXÃO (com diagnóstico)
# ============================================================
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
    echo "[ENTRYPOINT] Testando conexão SSL em $DB_HOST:$DB_PORT (sslmode=require)..."
    export PGSSLMODE=require

    MAX_RETRIES=30
    COUNT=0
    CONNECTED=0
    while [ $COUNT -lt $MAX_RETRIES ]; do
        # Usa pg_isready se disponível (mais leve)
        if command -v pg_isready >/dev/null 2>&1; then
            if pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" >/dev/null 2>&1; then
                echo "[ENTRYPOINT] ✅ pg_isready bem-sucedido."
                # Mas ainda testa o PDO com senha
            fi
        fi

        ERROR_MSG=$(php -r "
            try {
                \$dsn = 'pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE;sslmode=require';
                \$pdo = new PDO(\$dsn, '$DB_USERNAME', '$DB_PASSWORD', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
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
        echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao banco após $MAX_RETRIES tentativas."
        echo "[ENTRYPOINT] Último erro: $ERROR_MSG"
        echo "[ENTRYPOINT] Hostname testado: $DB_HOST"
        echo "[ENTRYPOINT] Usuário: $DB_USERNAME"
        echo "[ENTRYPOINT] Banco: $DB_DATABASE"
        # Não exibe senha por segurança
        exit 1
    fi
else
    echo "[ENTRYPOINT] ⚠️  Variáveis de banco incompletas. Pulando teste de conexão."
fi

# ============================================================
# 6. Limpa caches e demais etapas
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