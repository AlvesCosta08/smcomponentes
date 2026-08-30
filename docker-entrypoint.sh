#!/bin/sh
set -e

# ============================================
# 1. Garantir que as pastas de cache existam
# ============================================
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ============================================
# 2. Configurar variáveis de ambiente
# ============================================
export APP_STORAGE=/var/www/html/storage
export CACHE_DRIVER=file
export SESSION_DRIVER=file

# ============================================
# 3. Criar .env se não existir
# ============================================
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "AVISO: .env.example não encontrado. Criando .env vazio."
        > .env
    fi
    # Força variáveis essenciais (serão sobrescritas por variáveis de ambiente se definidas)
    echo "APP_STORAGE=$APP_STORAGE" >> .env
    echo "CACHE_DRIVER=$CACHE_DRIVER" >> .env
    echo "SESSION_DRIVER=$SESSION_DRIVER" >> .env
fi

# ============================================
# 4. Limpar cache de configuração (evita conflitos)
# ============================================
php artisan config:clear || true

# ============================================
# 5. Gerar APP_KEY se não definida
# ============================================
if grep -q "^APP_KEY=$" .env; then
    php artisan key:generate
fi

# ============================================
# 6. Executar tarefas pós-instalação (dump-autoload e package discovery)
# ============================================
echo "Executando composer dump-autoload --optimize..."
composer dump-autoload --optimize

echo "Executando php artisan package:discover..."
php artisan package:discover --ansi || {
    echo "Falha no package:discover, mas continuando..."
}

# ============================================
# 7. Otimizações para produção (se APP_ENV=production)
# ============================================
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# ============================================
# 8. Migrações (se FORCE_MIGRATION=true)
# ============================================
if [ "$FORCE_MIGRATION" = "true" ]; then
    php artisan migrate --force
fi

# ============================================
# 9. Executa o comando principal (CMD)
# ============================================
exec "$@"