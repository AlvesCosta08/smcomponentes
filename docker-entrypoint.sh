#!/bin/sh
set -e

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

# ============================================================
# 1. Ir para o diretório da aplicação
# ============================================================
cd /var/www/html

# ============================================================
# 2. Criar diretórios obrigatórios (redundância)
# ============================================================
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
touch storage/logs/laravel.log
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

# ============================================================
# 3. Copiar .env.example para .env (se não existir)
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
# 4. Substituir variáveis no formato ${VAR} usando envsubst
#    (fallback para sed se envsubst não estiver disponível)
# ============================================================
echo "[ENTRYPOINT] Substituindo variáveis de ambiente no .env..."

if command -v envsubst >/dev/null 2>&1; then
    # Usa envsubst para substituir todas as variáveis do ambiente
    envsubst < .env > .env.tmp && mv .env.tmp .env
    echo "[ENTRYPOINT] Variáveis substituídas com envsubst."
else
    echo "[ENTRYPOINT] envsubst não encontrado. Usando substituição manual..."
    # Extrai todas as variáveis no formato ${VAR} do .env
    VARS=$(grep -oE '\$\{[A-Za-z_][A-Za-z0-9_]*\}' .env | sed 's/\${//g' | sed 's/}//g' | sort -u)
    for VAR in $VARS; do
        eval VALUE=\$$VAR
        if [ -n "$VALUE" ]; then
            ESCAPED_VALUE=$(echo "$VALUE" | sed -e 's/[\/&]/\\&/g')
            sed -i "s/\${$VAR}/$ESCAPED_VALUE/g" .env
            sed -i "s/$$VAR/$ESCAPED_VALUE/g" .env
        fi
    done
    echo "[ENTRYPOINT] Substituição manual concluída."
fi

# ============================================================
# 5. Forçar variáveis críticas (APP_ENV, APP_DEBUG, APP_KEY, APP_URL)
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

echo "APP_ENV=${APP_ENV:-production}" >> .env
echo "APP_DEBUG=${APP_DEBUG:-false}" >> .env
echo "APP_KEY=${APP_KEY}" >> .env
echo "APP_URL=${APP_URL:-https://smcomponentes.onrender.com}" >> .env
echo "ASSET_URL=${APP_URL:-https://smcomponentes.onrender.com}" >> .env
echo "VITE_APP_URL=${APP_URL:-https://smcomponentes.onrender.com}" >> .env
echo "SESSION_DRIVER=file" >> .env
echo "CACHE_DRIVER=file" >> .env
echo "FORCE_HTTPS=true" >> .env

# ============================================================
# 6. Verificar se as variáveis do banco estão definidas
#    Se DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD não existirem,
#    tentamos extrair de DATABASE_URL (se definida)
# ============================================================
if [ -n "$DATABASE_URL" ]; then
    echo "[ENTRYPOINT] DATABASE_URL detectada. Extraindo parâmetros..."
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
fi

# Se ainda não foram definidas, verifica se estão no ambiente
: "${DB_HOST:?ERRO: DB_HOST não definida}"
: "${DB_PORT:=5432}"
: "${DB_DATABASE:?ERRO: DB_DATABASE não definida}"
: "${DB_USERNAME:?ERRO: DB_USERNAME não definida}"
: "${DB_PASSWORD:?ERRO: DB_PASSWORD não definida}"

# Atualiza o .env com os valores do banco (garantindo que estejam corretos)
sed -i '/^DB_HOST=/d' .env
sed -i '/^DB_PORT=/d' .env
sed -i '/^DB_DATABASE=/d' .env
sed -i '/^DB_USERNAME=/d' .env
sed -i '/^DB_PASSWORD=/d' .env
sed -i '/^DB_SSLMODE=/d' .env

echo "DB_HOST=$DB_HOST" >> .env
echo "DB_PORT=$DB_PORT" >> .env
echo "DB_DATABASE=$DB_DATABASE" >> .env
echo "DB_USERNAME=$DB_USERNAME" >> .env
echo "DB_PASSWORD=$DB_PASSWORD" >> .env
echo "DB_SSLMODE=require" >> .env

echo "[ENTRYPOINT] Configuração do banco:"
echo "  Host: $DB_HOST"
echo "  Porta: $DB_PORT"
echo "  Banco: $DB_DATABASE"
echo "  Usuário: $DB_USERNAME"

# ============================================================
# 7. Testar conexão com o banco (usando DSN manual)
# ============================================================
echo "[ENTRYPOINT] Testando conexão com PostgreSQL..."

MAX_RETRIES=10
COUNT=0
CONNECTED=0

DSN="pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE;sslmode=require"

while [ "$COUNT" -lt "$MAX_RETRIES" ]; do
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

if [ "$CONNECTED" -eq 0 ]; then
    echo "[ENTRYPOINT] ❌ ERRO: Não foi possível conectar ao PostgreSQL."
    echo "[ENTRYPOINT] DSN usado: $DSN"
    echo "[ENTRYPOINT] Último erro: $ERROR_MSG"
    exit 1
fi

# ============================================================
# 8. Validar diretórios antes do Artisan
# ============================================================
for DIR in storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache; do
    if [ ! -d "$DIR" ]; then
        echo "[ENTRYPOINT] ERRO: Diretório ausente: $DIR"
        exit 1
    fi
done

# ============================================================
# 9. Gerar APP_KEY se não estiver definida
# ============================================================
if [ -z "${APP_KEY:-}" ] || grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

# ============================================================
# 10. Limpar caches
# ============================================================
echo "[ENTRYPOINT] Limpando caches..."
php artisan optimize:clear --no-interaction || true

# ============================================================
# 11. Package Discovery
# ============================================================
echo "[ENTRYPOINT] Executando package discovery..."
php artisan package:discover --ansi --no-interaction || true

# ============================================================
# 12. Migrações (se FORCE_MIGRATION=true)
# ============================================================
if [ "${FORCE_MIGRATION:-false}" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrations..."
    php artisan migrate --force --no-interaction
fi

# ============================================================
# 13. Seeders (se FORCE_SEED=true)
# ============================================================
if [ "${FORCE_SEED:-false}" = "true" ]; then
    echo "[ENTRYPOINT] Executando seeders..."
    php artisan db:seed --force --no-interaction
fi

# ============================================================
# 14. Otimizações de produção
# ============================================================
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "[ENTRYPOINT] Criando caches de produção..."
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction
fi

# ============================================================
# 15. Porta e finalização
# ============================================================
export PORT="${PORT:-10000}"
echo "[ENTRYPOINT] ✅ Inicialização concluída. Servidor na porta $PORT."
exec "$@"
