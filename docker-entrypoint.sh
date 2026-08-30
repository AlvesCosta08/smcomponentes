#!/bin/sh
set -e

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

# ============================================================
# 1. Cria pastas de cache com permissões totais
# ============================================================
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
chmod -R 777 storage bootstrap/cache

# ============================================================
# 2. Cria .env a partir do .env.example
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
# 3. Sobrescreve variáveis do .env com as do ambiente (Render)
# ============================================================
echo "[ENTRYPOINT] Aplicando variáveis de ambiente ao .env..."

# Lista de variáveis que podem vir do ambiente
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

# Força variáveis internas
echo "APP_STORAGE=/var/www/html/storage" >> .env
echo "VIEW_COMPILED_PATH=/var/www/html/storage/framework/views" >> .env

# ============================================================
# 4. TESTE DE CONEXÃO COM O BANCO (PostgreSQL)
# ============================================================
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
    echo "[ENTRYPOINT] Testando conexão com o banco em $DB_HOST:$DB_PORT..."
    MAX_RETRIES=30
    COUNT=0
    CONNECTED=0
    while [ $COUNT -lt $MAX_RETRIES ]; do
        # Teste de conexão via PDO (PHP)
        if php -r "try { new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD'); echo 'ok'; } catch (PDOException \$e) { exit(1); }" 2>/dev/null | grep -q ok; then
            echo "[ENTRYPOINT] ✅ Conexão com o banco bem-sucedida!"
            CONNECTED=1
            break
        fi
        COUNT=$((COUNT + 1))
        echo "[ENTRYPOINT] Aguardando banco... ($COUNT/$MAX_RETRIES)"
        sleep 2
    done
    if [ $CONNECTED -eq 0 ]; then
        echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao banco após $MAX_RETRIES tentativas."
        exit 1
    fi
else
    echo "[ENTRYPOINT] ⚠️  Variáveis de banco incompletas. Pulando teste de conexão."
fi

# ============================================================
# 5. Limpa caches existentes
# ============================================================
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# ============================================================
# 6. Gera APP_KEY se necessário
# ============================================================
if grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate
fi

# ============================================================
# 7. Recria autoload otimizado
# ============================================================
echo "[ENTRYPOINT] Recriando autoload otimizado..."
composer dump-autoload --optimize

# ============================================================
# 8. Executa package:discover (se necessário)
# ============================================================
php artisan package:discover --no-ansi || true

# ============================================================
# 9. Migrations (se FORCE_MIGRATION=true)
# ============================================================
if [ "$FORCE_MIGRATION" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrations..."
    php artisan migrate --force || {
        echo "[ENTRYPOINT] ❌ ERRO: Falha nas migrations."
        exit 1
    }
fi

# ============================================================
# 10. Seeders (se FORCE_SEED=true)
# ============================================================
if [ "$FORCE_SEED" = "true" ]; then
    echo "[ENTRYPOINT] Executando seeders..."
    php artisan db:seed --force || {
        echo "[ENTRYPOINT] ❌ ERRO: Falha nos seeders."
        exit 1
    }
fi

# ============================================================
# 11. Otimizações para produção
# ============================================================
if [ "$APP_ENV" = "production" ]; then
    echo "[ENTRYPOINT] Otimizando cache para produção..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# ============================================================
# 12. Inicia o servidor
# ============================================================
echo "[ENTRYPOINT] ✅ Inicialização concluída. Iniciando servidor..."
exec "$@"