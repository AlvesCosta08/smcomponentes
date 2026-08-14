#!/bin/bash
set -e

# ============================================
# 1. VERIFICAR .ENV
# ============================================
if [ ! -f .env ]; then
    cat > .env << 'EOF'
APP_NAME="Loja Virtual SM Componentes"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://loja-vitual-smcomponentes.onrender.com
RUN_SEEDERS=true
EOF
fi

# ============================================
# 2. GERAR APP_KEY SE NECESSÁRIO
# ============================================
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --force > /dev/null 2>&1
fi

# ============================================
# 3. CRIAR DIRETÓRIOS
# ============================================
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/build
chmod -R 775 storage bootstrap/cache public/build 2>/dev/null || true

# ============================================
# 4. LIMPAR CACHE
# ============================================
php artisan config:clear > /dev/null 2>&1 || true
php artisan cache:clear > /dev/null 2>&1 || true
php artisan view:clear > /dev/null 2>&1 || true
php artisan route:clear > /dev/null 2>&1 || true

# ============================================
# 5. RODAR MIGRATIONS
# ============================================
php artisan migrate --force > /dev/null 2>&1 || true

# ============================================
# 6. RODAR SEEDERS
# ============================================
if grep -q "RUN_SEEDERS=true" .env 2>/dev/null; then
    php artisan db:seed --force > /dev/null 2>&1 || true
fi

# ============================================
# 7. STORAGE LINK
# ============================================
php artisan storage:link > /dev/null 2>&1 || true

# ============================================
# 8. OTIMIZAR PRODUÇÃO
# ============================================
if grep -q "APP_ENV=production" .env 2>/dev/null; then
    php artisan config:cache > /dev/null 2>&1 || true
    php artisan route:cache > /dev/null 2>&1 || true
    php artisan view:cache > /dev/null 2>&1 || true
fi

# ============================================
# 9. EXECUTAR O COMANDO PRINCIPAL
# ============================================
exec "$@"