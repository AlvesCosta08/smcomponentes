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
# 2. Definir variáveis do MySQL (via ambiente)
# ============================================================
# Se não houver DATABASE_URL, usamos variáveis individuais
# Para MySQL, não usamos DATABASE_URL; usamos DB_* separadas

# Valores padrão (podem ser sobrescritos)
: "${DB_CONNECTION:=mysql}"
: "${DB_HOST:=db}"
: "${DB_PORT:=3306}"
: "${DB_DATABASE:=laravel}"
: "${DB_USERNAME:=root}"
: "${DB_PASSWORD:=root}"

export DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD

echo "[ENTRYPOINT] MySQL: $DB_HOST:$DB_PORT/$DB_DATABASE"

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
# 4. Substituir variáveis ${VAR} no .env (fallback manual)
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
# 5. Forçar variáveis críticas (sobrescreve)
# ============================================================
echo "[ENTRYPOINT] Forçando variáveis críticas..."

sed -i '/^APP_ENV=/d' .env; echo "APP_ENV=${APP_ENV:-production}" >> .env
sed -i '/^APP_DEBUG=/d' .env; echo "APP_DEBUG=${APP_DEBUG:-false}" >> .env
sed -i '/^APP_KEY=/d' .env; echo "APP_KEY=${APP_KEY}" >> .env
sed -i '/^APP_URL=/d' .env; echo "APP_URL=${APP_URL:-http://localhost:10000}" >> .env
sed -i '/^SESSION_DRIVER=/d' .env; echo "SESSION_DRIVER=file" >> .env
sed -i '/^CACHE_DRIVER=/d' .env; echo "CACHE_DRIVER=file" >> .env
sed -i '/^DB_CONNECTION=/d' .env; echo "DB_CONNECTION=$DB_CONNECTION" >> .env
sed -i '/^DB_HOST=/d' .env; echo "DB_HOST=$DB_HOST" >> .env
sed -i '/^DB_PORT=/d' .env; echo "DB_PORT=$DB_PORT" >> .env
sed -i '/^DB_DATABASE=/d' .env; echo "DB_DATABASE=$DB_DATABASE" >> .env
sed -i '/^DB_USERNAME=/d' .env; echo "DB_USERNAME=$DB_USERNAME" >> .env
sed -i '/^DB_PASSWORD=/d' .env; echo "DB_PASSWORD=$DB_PASSWORD" >> .env

# ============================================================
# 6. Testar conexão com MySQL (aguardar banco iniciar)
# ============================================================
echo "[ENTRYPOINT] Testando conexão com MySQL..."
MAX_RETRIES=15
COUNT=0
CONNECTED=0

while [ $COUNT -lt $MAX_RETRIES ]; do
    ERROR_MSG=$(php -r "
        try {
            new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
            echo 'ok';
        } catch (PDOException \$e) {
            echo 'erro: ' . \$e->getMessage();
        }
    " 2>&1)

    if echo "$ERROR_MSG" | grep -q '^ok$'; then
        echo "[ENTRYPOINT] ✅ Conectado ao MySQL!"
        CONNECTED=1
        break
    else
        echo "[ENTRYPOINT] Tentativa $((COUNT+1))/$MAX_RETRIES falhou: $ERROR_MSG"
        COUNT=$((COUNT + 1))
        sleep 3
    fi
done

if [ $CONNECTED -eq 0 ]; then
    echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao MySQL."
    exit 1
fi

# ============================================================
# 7. Criar tabelas de cache e sessão (se necessário)
# ============================================================
echo "[ENTRYPOINT] Criando tabelas de cache e sessão..."
php artisan cache:table --no-interaction 2>/dev/null || true
php artisan session:table --no-interaction 2>/dev/null || true

# ============================================================
# 8. Migrações e Seeders
# ============================================================
if [ "${FORCE_MIGRATION:-false}" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrações..."
    php artisan migrate --force --no-interaction || { echo "[ENTRYPOINT] ❌ Falha nas migrações."; exit 1; }
fi

if [ "${FORCE_SEED:-false}" = "true" ]; then
    echo "[ENTRYPOINT] Executando seeders..."
    php artisan db:seed --force --no-interaction || echo "[ENTRYPOINT] ⚠️ Seeders falharam."
fi

# ============================================================
# 9. Limpeza de caches, APP_KEY, package discover, otimizações
# ============================================================
php artisan optimize:clear --no-interaction || true
php artisan package:discover --ansi --no-interaction || true

if [ -z "${APP_KEY:-}" ] || grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    echo "[ENTRYPOINT] Otimizando cache para produção..."
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction
fi

# ============================================================
# 10. Porta e finalização
# ============================================================
export PORT="${PORT:-10000}"
echo "[ENTRYPOINT] ✅ Inicialização concluída. Servidor na porta $PORT."
exec "$@"