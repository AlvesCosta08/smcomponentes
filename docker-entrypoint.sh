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
# 2. Copiar .env.example para .env (sem alterações)
# ============================================================
if [ -f .env.example ]; then
    cp .env.example .env
    echo "[ENTRYPOINT] .env criado a partir do .env.example (sem alterações)"
else
    echo "[ENTRYPOINT] ERRO: .env.example não encontrado!"
    exit 1
fi

# ============================================================
# 3. Garantir DATABASE_URL (se não existir no ambiente)
# ============================================================
if [ -z "$DATABASE_URL" ]; then
    export DATABASE_URL="postgresql://loja_virtual_eilu_user:FeNuDK8XRL0XoI7WwqCgvCOJzT6d0Kof@dpg-daa41s9f2nfc7395g1dg-a.oregon-postgres.render.com/loja_virtual_eilu?sslmode=require"
    echo "[ENTRYPOINT] Usando DATABASE_URL embutida."
else
    echo "[ENTRYPOINT] Usando DATABASE_URL do ambiente."
fi

# ============================================================
# 4. Extrair parâmetros do banco (para teste de conexão)
# ============================================================
echo "[ENTRYPOINT] Extraindo parâmetros da DATABASE_URL..."

EXTRACT=$(php -r "
    \$url = parse_url('$DATABASE_URL');
    \$host = \$url['host'] ?? '';
    \$port = \$url['port'] ?? 5432;
    \$dbname = ltrim(\$url['path'] ?? '', '/');
    \$user = \$url['user'] ?? '';
    \$pass = \$url['pass'] ?? '';
    echo \"DB_HOST=\$host DB_PORT=\$port DB_NAME=\$dbname DB_USER=\$user DB_PASS=\$pass\";
" 2>/dev/null)

eval "$EXTRACT"

# ============================================================
# 5. Montar DSN manual para teste
# ============================================================
DSN="pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;sslmode=require"
echo "[ENTRYPOINT] DSN para teste: $DSN"

# ============================================================
# 6. TESTE DE CONEXÃO
# ============================================================
echo "[ENTRYPOINT] Testando conexão com banco..."

MAX_RETRIES=10
COUNT=0
CONNECTED=0

while [ $COUNT -lt $MAX_RETRIES ]; do
    ERROR_MSG=$(php -r "
        try {
            new PDO('$DSN', '$DB_USER', '$DB_PASS');
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
        sleep 2
    fi
    COUNT=$((COUNT + 1))
done

if [ $CONNECTED -eq 0 ]; then
    echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao banco."
    echo "[ENTRYPOINT] DSN usado: $DSN"
    echo "[ENTRYPOINT] Último erro: $ERROR_MSG"
    exit 1
fi

# ============================================================
# 7. Limpar caches (opcional, mas ajuda)
# ============================================================
php artisan config:clear --no-interaction || true
php artisan cache:clear --no-interaction || true
php artisan view:clear --no-interaction || true
php artisan route:clear --no-interaction || true

# ============================================================
# 8. APP_KEY (se não existir no .env)
# ============================================================
if grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate --no-interaction
fi

# ============================================================
# 9. Recriar autoload
# ============================================================
echo "[ENTRYPOINT] Recriando autoload..."
composer dump-autoload --optimize --no-interaction

# ============================================================
# 10. Package discover
# ============================================================
php artisan package:discover --no-ansi --no-interaction || true

# ============================================================
# 11. Migrações (se FORCE_MIGRATION=true)
# ============================================================
if [ "$FORCE_MIGRATION" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrations..."
    php artisan migrate --force --no-interaction || exit 1
fi

# ============================================================
# 12. Seeders (se FORCE_SEED=true)
# ============================================================
if [ "$FORCE_SEED" = "true" ]; then
    echo "[ENTRYPOINT] Executando seeders..."
    php artisan db:seed --force --no-interaction || exit 1
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

# ============================================================
# 14. Garantir a porta correta
# ============================================================
export PORT=${PORT:-10000}

echo "[ENTRYPOINT] ✅ Inicialização concluída. Servidor na porta $PORT."
exec "$@"