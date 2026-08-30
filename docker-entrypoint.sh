#!/bin/sh
set -e

echo "[ENTRYPOINT] Iniciando configuração do Laravel..."

# ============================================
# 1. Garantir pastas de cache com permissões amplas
# ============================================
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
chmod -R 777 storage bootstrap/cache

# ============================================
# 2. Definir variáveis de ambiente essenciais
# ============================================
export APP_STORAGE=/var/www/html/storage
export CACHE_DRIVER=${CACHE_DRIVER:-file}
export SESSION_DRIVER=${SESSION_DRIVER:-file}
export VIEW_COMPILED_PATH=/var/www/html/storage/framework/views

# ============================================
# 3. Criar/atualizar .env com as variáveis forçadas
# ============================================
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        echo "[ENTRYPOINT] .env criado a partir do .env.example"
    else
        touch .env
        echo "[ENTRYPOINT] .env criado vazio"
    fi
fi

# Adiciona/atualiza as variáveis no .env (sobrescreve)
sed -i '/^APP_STORAGE=/d' .env
sed -i '/^CACHE_DRIVER=/d' .env
sed -i '/^SESSION_DRIVER=/d' .env
sed -i '/^VIEW_COMPILED_PATH=/d' .env

echo "APP_STORAGE=$APP_STORAGE" >> .env
echo "CACHE_DRIVER=$CACHE_DRIVER" >> .env
echo "SESSION_DRIVER=$SESSION_DRIVER" >> .env
echo "VIEW_COMPILED_PATH=$VIEW_COMPILED_PATH" >> .env

# ============================================
# 4. Verificar conexão com o banco de dados (se configurado)
# ============================================
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
    echo "[ENTRYPOINT] Aguardando banco de dados ficar disponível em $DB_HOST:$DB_PORT..."
    timeout=60
    while [ $timeout -gt 0 ]; do
        if php -r "try { new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD'); echo 'ok'; } catch (PDOException \$e) { exit(1); }" 2>/dev/null | grep -q ok; then
            echo "[ENTRYPOINT] Conexão com o banco bem-sucedida!"
            break
        fi
        timeout=$((timeout - 1))
        sleep 1
        echo "[ENTRYPOINT] Aguardando banco... ($timeout segundos restantes)"
    done
    if [ $timeout -eq 0 ]; then
        echo "[ENTRYPOINT] AVISO: Não foi possível conectar ao banco em $DB_HOST. Continuando mesmo assim..."
    fi
else
    echo "[ENTRYPOINT] Variáveis de banco não definidas. Pulando verificação."
fi

# ============================================
# 5. Limpar qualquer cache existente
# ============================================
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# ============================================
# 6. Gerar APP_KEY se necessário
# ============================================
if grep -q "^APP_KEY=$" .env; then
    php artisan key:generate
fi

# ============================================
# 7. Recriar autoload otimizado e rodar package discovery
# ============================================
echo "[ENTRYPOINT] Recriando autoload otimizado..."
composer dump-autoload --optimize

echo "[ENTRYPOINT] Executando package:discover..."
php artisan package:discover --no-ansi || {
    echo "[ENTRYPOINT] AVISO: package:discover falhou, mas a aplicação pode funcionar normalmente."
}

# ============================================
# 8. Otimizações para produção (se APP_ENV=production)
# ============================================
if [ "$APP_ENV" = "production" ]; then
    echo "[ENTRYPOINT] Otimizando cache para produção..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# ============================================
# 9. Migrações (se FORCE_MIGRATION=true)
# ============================================
if [ "$FORCE_MIGRATION" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrações..."
    php artisan migrate --force || true
fi

# ============================================
# 10. Executa o comando principal (CMD)
# ============================================
echo "[ENTRYPOINT] Inicialização concluída. Iniciando servidor..."
exec "$@"