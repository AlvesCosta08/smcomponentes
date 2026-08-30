#!/bin/sh
set -eu

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

# ============================================================

# 1. Diretório da aplicação

# ============================================================

cd /var/www/html

# ============================================================

# 2. Criar diretórios obrigatórios do Laravel

# ============================================================

echo "[ENTRYPOINT] Criando diretórios de storage e cache..."

mkdir -p 
storage/framework/sessions 
storage/framework/views 
storage/framework/cache/data 
storage/logs 
bootstrap/cache

touch storage/logs/laravel.log

# ============================================================

# 3. Permissões

# ============================================================

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache

# ============================================================

# 4. Criar .env caso não exista

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

# 5. Validar configurações do banco

# ============================================================

: "${DB_CONNECTION:=pgsql}"
: "${DB_HOST:?ERRO: DB_HOST não definida}"
: "${DB_PORT:=5432}"
: "${DB_DATABASE:?ERRO: DB_DATABASE não definida}"
: "${DB_USERNAME:?ERRO: DB_USERNAME não definida}"
: "${DB_PASSWORD:?ERRO: DB_PASSWORD não definida}"

export 
DB_CONNECTION 
DB_HOST 
DB_PORT 
DB_DATABASE 
DB_USERNAME 
DB_PASSWORD

echo "[ENTRYPOINT] Configuração do banco:"
echo "[ENTRYPOINT] Driver: $DB_CONNECTION"
echo "[ENTRYPOINT] Host: $DB_HOST"
echo "[ENTRYPOINT] Porta: $DB_PORT"
echo "[ENTRYPOINT] Banco: $DB_DATABASE"
echo "[ENTRYPOINT] Usuário: $DB_USERNAME"

# ============================================================

# 6. Testar conexão com PostgreSQL

# ============================================================

echo "[ENTRYPOINT] Testando conexão com PostgreSQL..."

MAX_RETRIES=10
COUNT=0
CONNECTED=0

while [ "$COUNT" -lt "$MAX_RETRIES" ]; do

```
if php -r '
    try {
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;sslmode=require",
            getenv("DB_HOST"),
            getenv("DB_PORT"),
            getenv("DB_DATABASE")
        );

        new PDO(
            $dsn,
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD"),
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

    echo "[ENTRYPOINT] Conexão com PostgreSQL bem-sucedida!"
    CONNECTED=1
    break
fi

COUNT=$((COUNT + 1))

echo "[ENTRYPOINT] Tentativa $COUNT/$MAX_RETRIES falhou:"
cat /tmp/db_test || true

sleep 3
```

done

if [ "$CONNECTED" -eq 0 ]; then
echo "[ENTRYPOINT] ERRO: Não foi possível conectar ao PostgreSQL."
exit 1
fi

# ============================================================

# 7. Validar diretórios antes do Artisan

# ============================================================

echo "[ENTRYPOINT] Validando estrutura de cache..."

for DIR in 
storage/framework/sessions 
storage/framework/views 
storage/framework/cache 
storage/framework/cache/data 
storage/logs 
bootstrap/cache
do
if [ ! -d "$DIR" ]; then
echo "[ENTRYPOINT] ERRO: Diretório ausente: $DIR"
exit 1
fi
done

# ============================================================

# 8. APP_KEY

# ============================================================

if [ -z "${APP_KEY:-}" ]; then
if ! grep -q "^APP_KEY=.+" .env; then
echo "[ENTRYPOINT] Gerando APP_KEY..."
php artisan key:generate --force --no-interaction
fi
fi

# ============================================================

# 9. Limpar caches

# ============================================================

echo "[ENTRYPOINT] Limpando caches..."

php artisan optimize:clear --no-interaction

# ============================================================

# 10. Package Discovery

# ============================================================

echo "[ENTRYPOINT] Executando package discovery..."

php artisan package:discover 
--ansi 
--no-interaction

# ============================================================

# 11. Migrações

# ============================================================

if [ "${FORCE_MIGRATION:-false}" = "true" ]; then
echo "[ENTRYPOINT] Executando migrations..."

```
php artisan migrate \
    --force \
    --no-interaction
```

fi

# ============================================================

# 12. Seeders

# ============================================================

if [ "${FORCE_SEED:-false}" = "true" ]; then
echo "[ENTRYPOINT] Executando seeders..."

```
php artisan db:seed \
    --force \
    --no-interaction
```

fi

# ============================================================

# 13. Otimizações para produção

# ============================================================

if [ "${APP_ENV:-production}" = "production" ]; then

```
echo "[ENTRYPOINT] Criando caches de produção..."

php artisan config:cache --no-interaction
php artisan route:cache --no-interaction || true
php artisan view:cache --no-interaction
```

fi

# ============================================================

# 14. Porta

# ============================================================

export PORT="${PORT:-10000}"

echo "[ENTRYPOINT] Inicialização concluída!"
echo "[ENTRYPOINT] Servidor será iniciado na porta $PORT."

exec "$@"
