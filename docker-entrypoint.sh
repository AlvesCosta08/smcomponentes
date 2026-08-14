#!/bin/bash
set -e

# ============================================
# CORES
# ============================================
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# ============================================
# 1. VERIFICAR .ENV
# ============================================
if [ ! -f .env ]; then
    log_error "❌ Arquivo .env não encontrado!"
    log_error "Certifique-se de que o .env está no repositório"
    exit 1
fi

log_info "✅ .env encontrado"

# ============================================
# 2. GERAR APP_KEY SE NECESSÁRIO
# ============================================
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    log_warn "Gerando APP_KEY..."
    php artisan key:generate --force
    log_info "APP_KEY gerada"
fi

# ============================================
# 3. CRIAR DIRETÓRIOS
# ============================================
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ============================================
# 4. LIMPAR CACHE
# ============================================
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

# ============================================
# 5. RODAR MIGRATIONS
# ============================================
log_info "Executando migrations..."
php artisan migrate --force || log_warn "⚠️ Migrations falharam"

# ============================================
# 6. RODAR SEEDERS
# ============================================
if grep -q "RUN_SEEDERS=true" .env; then
    log_info "Executando seeders..."
    php artisan db:seed --force || log_warn "⚠️ Seeders falharam"
fi

# ============================================
# 7. STORAGE LINK
# ============================================
php artisan storage:link 2>/dev/null || true

# ============================================
# 8. OTIMIZAR PRODUÇÃO
# ============================================
if grep -q "APP_ENV=production" .env; then
    log_info "Otimizando para produção..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# ============================================
# 9. RESUMO
# ============================================
echo ""
echo "============================================="
echo "  🚀 SM Componentes - Aplicação iniciada"
echo "============================================="
echo "  🌐 URL: $(grep ^APP_URL .env | cut -d '=' -f2)"
echo "  🔧 Ambiente: $(grep ^APP_ENV .env | cut -d '=' -f2)"
echo "  🗄️  Banco: PostgreSQL"
echo "============================================="
echo ""

exec "$@"