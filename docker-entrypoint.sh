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
export CACHE_DRIVER=file
export SESSION_DRIVER=file
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
        echo "[ENTRYPOINT] .env criado vazio (sem .env.example)"
    fi
fi

# Remove linhas antigas das variáveis que vamos forçar
sed -i '/^APP_STORAGE=/d' .env
sed -i '/^CACHE_DRIVER=/d' .env
sed -i '/^SESSION_DRIVER=/d' .env
sed -i '/^VIEW_COMPILED_PATH=/d' .env

# Adiciona as variáveis no .env
echo "APP_STORAGE=$APP_STORAGE" >> .env
echo "CACHE_DRIVER=$CACHE_DRIVER" >> .env
echo "SESSION_DRIVER=$SESSION_DRIVER" >> .env
echo "VIEW_COMPILED_PATH=$VIEW_COMPILED_PATH" >> .env

# ============================================
# 4. Limpar qualquer cache existente
# ============================================
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# ============================================
# 5. Gerar APP_KEY se não definida
# ============================================
if grep -q "^APP_KEY=$" .env; then
    echo "[ENTRYPOINT] Gerando APP_KEY..."
    php artisan key:generate
fi

# ============================================
# 6. Recriar autoload otimizado
# ============================================
echo "[ENTRYPOINT] Recriando autoload otimizado..."
composer dump-autoload --optimize

# ============================================
# 7. Descobrir pacotes (package:discover)
# ============================================
echo "[ENTRYPOINT] Executando package:discover..."
php artisan package:discover --no-ansi || {
    echo "[ENTRYPOINT] AVISO: package:discover falhou, mas continuando..."
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
# 9. Rodar migrações se solicitado via variável de ambiente
# ============================================
if [ "$FORCE_MIGRATION" = "true" ]; then
    echo "[ENTRYPOINT] Executando migrações..."
    php artisan migrate --force
fi

# ============================================
# 10. Executa o comando principal (CMD)
# ============================================
echo "[ENTRYPOINT] Inicialização concluída. Iniciando servidor..."
exec "$@"