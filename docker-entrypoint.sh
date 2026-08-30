#!/bin/sh
set -eu

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

# ============================================================
# 1. Ir para o diretório correto da aplicação
# ============================================================
cd /var/www/html

# ============================================================
# 2. Garantir diretórios necessários do Laravel
# ============================================================
echo "[ENTRYPOINT] Criando diretórios de storage e cache..."

mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache

# Criar arquivos para garantir que os diretórios existam
touch storage/logs/laravel.log

# ============================================================
# 3. Permissões
# ============================================================
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

# ============================================================
# 4. Criar .env somente se ainda não existir
# ============================================================
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "[ENTRYPOINT] .env criado a partir do .env.example"
    else
        echo "[ENTRYPOINT] ERRO: .env.example não encontrado!"
        exit 1
    fi
else
    echo "[ENTRYPOINT] .env já existe. Mantendo arquivo atual."
fi

# ============================================================
# 5. DATABASE_URL
# ============================================================
if [ -z "${DATABASE_URL:-}" ]; then
    echo "[ENTRYPOINT] AVISO: DATABASE_URL não definida no ambiente."
    echo "[ENTRYPOINT] Configure DATABASE_URL nas variáveis do Render."
    exit 1
fi

echo "[ENTRYPOINT] Usando DATABASE_URL do ambiente."

# ============================================================
# 6. Extrair parâmetros da DATABASE_URL
# ============================================================
echo "[ENTRYPOINT] Extraindo parâmetros da DATABASE_URL..."

DB_VALUES=$(php -r '
    $url = getenv("DATABASE_URL");

    if (!$url) {
        fwrite(STDERR, "DATABASE_URL não encontrada\n");
        exit(1);
    }

    $db = parse_url($url);

    if ($db === false || empty($db["host"]) || empty($db["path"])) {
        fwrite(STDERR, "DATABASE_URL inválida\n");
        exit(1);
    }

    echo "DB_HOST=" . escapeshellarg($db["host"]) . " ";
    echo "DB_PORT=" . escapeshellarg($db["port"] ?? 5432) . " ";
    echo "DB_NAME=" . escapeshellarg(ltrim($db["path"], "/")) . " ";
    echo "DB_USER=" . escapeshellarg($db["user"] ?? "") . " ";
    echo "DB_PASS=" . escapeshellarg($db["pass"] ?? "");
')

eval "$DB_VALUES"

# ============================================================
# 7. Testar conexão com PostgreSQL
# ============================================================
echo "[ENTRYPOINT] Testando conexão com o banco..."

MAX_RETRIES=10
COUNT=0
CONNECTED=0

while [ "$COUNT" -lt "$MAX_RETRIES" ]; do

    if php -r '
        $url = getenv("DATABASE_URL");

        try {
            $pdo = new PDO(
                $url,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 10,
                ]
            );

            echo "ok";
        } catch (Throwable $e) {
            fwrite(STDERR, $e->getMessage());
            exit(1);
        }
    ' >/tmp/db_test 2>&1; then

        echo "[ENTRYPOINT] Conexão com o banco bem-sucedida!"
        CONNECTED=1
        break
    fi

    COUNT=$((COUNT + 1))

    echo "[ENTRYPOINT] Tentativa $COUNT/$MAX_RETRIES falhou:"
    cat /tmp/db_test

    sleep 3
done

if [ "$CONNECTED" -eq 0 ]; then
    echo "[ENTRYPOINT] ERRO: Não foi possível conectar ao banco."
    exit 1
fi

# ============================================================
# 8. Verificação das pastas ANTES do Artisan
# ============================================================
echo "[ENTRYPOINT] Verificando estrutura do Laravel..."

for DIR in \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache
do
    if [ ! -d "$DIR" ]; then
        echo "[ENTRYPOINT] ERRO: Diretório ausente: $DIR"
        exit 1
    fi
done

echo "[ENTRYPOINT] Estrutura de cache validada."

# ============================================================
# 9. Recriar autoload sem executar scripts automaticamente
# ============================================================
echo "[ENTRYPOINT] Recriando autoload..."

composer dump-autoload \
    --optimize \
    --no-interaction \
    --no-scripts

# ============================================================
# 10. Limpar caches
# ============================================================
echo "[ENTRYPOINT] Limpando caches..."

php artisan optimize:clear --no-interaction

# ============================================================
# 11. APP_KEY
# ============================================================
if ! grep -q "^APP_KEY=.\+" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate --force --no-interaction
fi

# ============================================================
# 12. Package discovery
# ============================================================
echo "[ENTRYPOINT] Executando package discovery..."

php artisan package:discover \
    --ansi \
    --no-interaction

# ============================================================
# 13. Migrações
# ============================================================
if [ "${FORCE_MIGRATION:-false}" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrations..."

    php artisan migrate \
        --force \
        --no-interaction
fi

# ============================================================
# 14. Seeders
# ============================================================
if [ "${FORCE_SEED:-false}" = "true" ]; then
    echo "[ENTRYPOINT] Executando seeders..."

    php artisan db:seed \
        --force \
        --no-interaction
fi

# ============================================================
# 15. Cache para produção
# ============================================================
if [ "${APP_ENV:-production}" = "production" ]; then

    echo "[ENTRYPOINT] Otimizando Laravel para produção..."

    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction
fi

# ============================================================
# 16. Porta
# ============================================================
export PORT="${PORT:-10000}"

echo "[ENTRYPOINT] Inicialização concluída!"
echo "[ENTRYPOINT] Servidor será iniciado na porta $PORT."

exec "$@"