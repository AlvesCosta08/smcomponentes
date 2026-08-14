#!/bin/bash
set -e

# ============================================
# CORES
# ============================================
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }
log_step() { echo -e "${BLUE}[STEP]${NC} $1"; }
log_success() { echo -e "${GREEN}✅${NC} $1"; }

# ============================================
# 1. VERIFICAR .ENV
# ============================================
if [ ! -f .env ]; then
    log_error "❌ Arquivo .env não encontrado!"
    exit 1
fi

log_success ".env encontrado"

# ============================================
# 2. SUBSTITUIR VALORES PELAS VARIÁVEIS DE AMBIENTE
# ============================================
log_step "Substituindo valores pelas variáveis de ambiente..."

# Substitui DB_HOST se a variável existir
if [ -n "$DB_HOST" ]; then
    log_info "  DB_HOST: ${DB_HOST}"
    sed -i "s/^DB_HOST=.*/DB_HOST=${DB_HOST}/" .env
fi

# Substitui DB_PORT se a variável existir
if [ -n "$DB_PORT" ]; then
    log_info "  DB_PORT: ${DB_PORT}"
    sed -i "s/^DB_PORT=.*/DB_PORT=${DB_PORT}/" .env
fi

# Substitui DB_DATABASE se a variável existir
if [ -n "$DB_DATABASE" ]; then
    log_info "  DB_DATABASE: ${DB_DATABASE}"
    sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${DB_DATABASE}/" .env
fi

# Substitui DB_USERNAME se a variável existir
if [ -n "$DB_USERNAME" ]; then
    log_info "  DB_USERNAME: ${DB_USERNAME}"
    sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USERNAME}/" .env
fi

# Substitui DB_PASSWORD se a variável existir (mais seguro)
if [ -n "$DB_PASSWORD" ]; then
    log_info "  DB_PASSWORD: ******** (definido via variável de ambiente)"
    # Escapa caracteres especiais para o sed
    DB_PASSWORD_ESCAPED=$(echo "$DB_PASSWORD" | sed -e 's/[\/&]/\\&/g')
    sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD_ESCAPED}/" .env
else
    log_warn "  ⚠️ DB_PASSWORD não definida via variável de ambiente"
    log_warn "  Usando valor do .env (não recomendado para produção)"
fi

# Substitui DB_SSLMODE se a variável existir
if [ -n "$DB_SSLMODE" ]; then
    log_info "  DB_SSLMODE: ${DB_SSLMODE}"
    sed -i "s/^DB_SSLMODE=.*/DB_SSLMODE=${DB_SSLMODE}/" .env
fi

# Substitui DB_CONNECTION se a variável existir
if [ -n "$DB_CONNECTION" ]; then
    log_info "  DB_CONNECTION: ${DB_CONNECTION}"
    sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=${DB_CONNECTION}/" .env
fi

log_success "Valores substituídos com sucesso"

# ============================================
# 3. MOSTRA CONFIGURAÇÃO FINAL (SEM SENHA)
# ============================================
log_step "Configuração final do banco:"
grep -E "^(DB_CONNECTION|DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_SSLMODE)=" .env | while read -r line; do
    log_info "  $line"
done

# ============================================
# 4. GERAR APP_KEY SE NECESSÁRIO
# ============================================
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    log_warn "Gerando APP_KEY..."
    php artisan key:generate --force
    log_success "APP_KEY gerada"
fi

# ============================================
# 5. CRIAR DIRETÓRIOS
# ============================================
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/build

chmod -R 775 storage bootstrap/cache public/build 2>/dev/null || true

# ============================================
# 6. LIMPAR CACHE
# ============================================
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

# ============================================
# 7. RODAR MIGRATIONS
# ============================================
log_step "Rodando migrations..."
php artisan migrate --force || log_warn "⚠️ Migrations falharam"

# ============================================
# 8. RODAR SEEDERS
# ============================================
if grep -q "RUN_SEEDERS=true" .env; then
    log_step "Rodando seeders..."
    php artisan db:seed --force || log_warn "⚠️ Seeders falharam"
fi

# ============================================
# 9. STORAGE LINK
# ============================================
php artisan storage:link 2>/dev/null || true

# ============================================
# 10. OTIMIZAR PRODUÇÃO
# ============================================
if grep -q "APP_ENV=production" .env; then
    log_step "Otimizando para produção..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# ============================================
# 11. RESUMO
# ============================================
echo ""
echo "============================================="
echo "  🚀 SM Componentes - Aplicação iniciada"
echo "============================================="
echo "  🌐 URL: $(grep ^APP_URL .env | cut -d '=' -f2)"
echo "  🔧 Ambiente: $(grep ^APP_ENV .env | cut -d '=' -f2)"
echo "  🗄️  Banco: $(grep ^DB_CONNECTION .env | cut -d '=' -f2)"
echo "  🔒  Senha: $( [ -n "$DB_PASSWORD" ] && echo '✅ Via variável de ambiente' || echo '⚠️  No .env' )"
echo "============================================="
echo ""

exec "$@"