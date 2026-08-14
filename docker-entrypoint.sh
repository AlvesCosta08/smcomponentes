#!/bin/bash
set -e

# ============================================
# CORES
# ============================================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }
log_step() { echo -e "${BLUE}[STEP]${NC} $1"; }

# ============================================
# 1. CRIAR .env A PARTIR DAS VARIÁVEIS DE AMBIENTE
# ============================================
log_step "Criando arquivo .env a partir das variáveis de ambiente..."

# Verifica se as variáveis obrigatórias existem
if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ]; then
    log_error "❌ Variáveis de banco não definidas!"
    log_error "DB_HOST: ${DB_HOST:-NÃO DEFINIDO}"
    log_error "DB_DATABASE: ${DB_DATABASE:-NÃO DEFINIDO}"
    log_error "Verifique as variáveis de ambiente no Render!"
    exit 1
fi

# Cria .env com os valores das variáveis de ambiente
cat > .env << EOF
# ============================================
# CONFIGURAÇÕES GERAIS DA APLICAÇÃO
# ============================================
APP_NAME="${APP_NAME:-Loja Virtual SM Componentes}"
APP_ENV="${APP_ENV:-production}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_TIMEZONE="${APP_TIMEZONE:-America/Sao_Paulo}"
APP_URL="${APP_URL:-https://smcomponentes.onrender.com}"
ASSET_URL="${ASSET_URL:-${APP_URL:-https://smcomponentes.onrender.com}}"
APP_KEY="${APP_KEY:-}"

# ============================================
# CONFIGURAÇÕES DE BANCO DE DADOS (PostgreSQL)
# ============================================
DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE}"
DB_USERNAME="${DB_USERNAME}"
DB_PASSWORD="${DB_PASSWORD}"
DB_SSLMODE="${DB_SSLMODE:-require}"

# ============================================
# CONFIGURAÇÕES DE CACHE E SESSÃO
# ============================================
CACHE_DRIVER="${CACHE_DRIVER:-file}"
SESSION_DRIVER="${SESSION_DRIVER:-database}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"
SESSION_SECURE_COOKIE="${SESSION_SECURE_COOKIE:-true}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

# ============================================
# CONFIGURAÇÕES DE BROADCAST E LOG
# ============================================
BROADCAST_DRIVER="${BROADCAST_DRIVER:-log}"
LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_LEVEL="${LOG_LEVEL:-error}"

# ============================================
# CONFIGURAÇÕES DE EMAIL
# ============================================
MAIL_MAILER="${MAIL_MAILER:-smtp}"
MAIL_HOST="${MAIL_HOST:-smtp.mailtrap.io}"
MAIL_PORT="${MAIL_PORT:-2525}"
MAIL_USERNAME="${MAIL_USERNAME:-null}"
MAIL_PASSWORD="${MAIL_PASSWORD:-null}"
MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-null}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-contato@smcomponentes.com}"
MAIL_FROM_NAME="${MAIL_FROM_NAME:-SM Componentes}"

# ============================================
# REDIS
# ============================================
REDIS_HOST="${REDIS_HOST:-127.0.0.1}"
REDIS_PASSWORD="${REDIS_PASSWORD:-null}"
REDIS_PORT="${REDIS_PORT:-6379}"

# ============================================
# MERCADO PAGO
# ============================================
MERCADOPAGO_PUBLIC_KEY="${MERCADOPAGO_PUBLIC_KEY:-}"
MERCADOPAGO_ACCESS_TOKEN="${MERCADOPAGO_ACCESS_TOKEN:-}"
MERCADOPAGO_WEBHOOK_URL="${MERCADOPAGO_WEBHOOK_URL:-}"
MERCADOPAGO_ENV="${MERCADOPAGO_ENV:-production}"

# ============================================
# VITE (Frontend)
# ============================================
VITE_APP_URL="${VITE_APP_URL:-${APP_URL:-https://smcomponentes.onrender.com}}"

# ============================================
# HTTPS (forçado em produção)
# ============================================
FORCE_HTTPS="${FORCE_HTTPS:-true}"

# ============================================
# CONTROLE DE SEEDERS E MIGRAÇÕES
# ============================================
RUN_SEEDERS="${RUN_SEEDERS:-false}"
REFRESH_DATABASE="${REFRESH_DATABASE:-false}"
FORCE_SEEDERS="${FORCE_SEEDERS:-false}"
EOF

log_info "✅ .env criado com sucesso"

# Mostra configuração (sem senha)
log_info "📊 Configuração:"
grep -E "^(APP_ENV|APP_URL|DB_CONNECTION|DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME)=" .env | while read -r line; do
    log_info "  $line"
done

# ============================================
# 2. GERAR APP_KEY (se não existir)
# ============================================
log_step "Verificando APP_KEY..."

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:..." ] || [ "$APP_KEY" = "43bbe8c4f273807bc376b9809bf19ac8" ]; then
    log_warn "Gerando APP_KEY..."
    php artisan key:generate --force 2>/dev/null || true
    APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    log_info "APP_KEY gerada: ${APP_KEY:0:15}..."
else
    log_info "APP_KEY já definida"
fi

# ============================================
# 3. CRIAR DIRETÓRIOS
# ============================================
log_step "Criando diretórios..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache public/build 2>/dev/null || true

# ============================================
# 4. TESTAR CONEXÃO COM O BANCO
# ============================================
log_step "Testando conexão com o banco..."

MAX_RETRIES=30
RETRY=0
DB_CONNECTED=false

DB_HOST=$(grep ^DB_HOST .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_PORT=$(grep ^DB_PORT .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_DATABASE=$(grep ^DB_DATABASE .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_USERNAME=$(grep ^DB_USERNAME .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_PASSWORD=$(grep ^DB_PASSWORD .env | cut -d '=' -f2- | tr -d '\r' | xargs)

while [ $RETRY -lt $MAX_RETRIES ] && [ "$DB_CONNECTED" = false ]; do
    if php -r "
        try {
            \$pdo = new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
            echo 'OK';
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null | grep -q OK; then
        DB_CONNECTED=true
        log_info "✅ Conexão com banco OK!"
    else
        RETRY=$((RETRY + 1))
        log_warn "Aguardando banco... (${RETRY}/${MAX_RETRIES})"
        sleep 2
    fi
done

if [ "$DB_CONNECTED" = false ]; then
    log_warn "⚠️ Não foi possível conectar ao banco. Verifique as credenciais!"
fi

# ============================================
# 5. LIMPAR CACHE
# ============================================
log_step "Limpando cache..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

# ============================================
# 6. RODAR MIGRATIONS
# ============================================
if [ "$DB_CONNECTED" = true ]; then
    log_step "Rodando migrations..."
    php artisan migrate --force 2>/dev/null || log_warn "⚠️ Migrations falharam"
    log_info "✅ Migrações concluídas"
    
    # Rodar seeders se configurado
    RUN_SEEDERS=$(grep ^RUN_SEEDERS .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    if [ "$RUN_SEEDERS" = "true" ]; then
        log_step "Rodando seeders..."
        php artisan db:seed --force 2>/dev/null || log_warn "⚠️ Seeders falharam"
        log_info "✅ Seeders concluídos"
    fi
else
    log_warn "⚠️ Pulando migrations (banco não conectado)"
fi

# ============================================
# 7. LINK STORAGE
# ============================================
log_step "Criando storage link..."
if [ ! -L public/storage ]; then
    php artisan storage:link 2>/dev/null || true
fi

# ============================================
# 8. OTIMIZAR
# ============================================
APP_ENV=$(grep ^APP_ENV .env | cut -d '=' -f2- | tr -d '\r' | xargs)
if [ "$APP_ENV" = "production" ] && [ "$DB_CONNECTED" = true ]; then
    log_step "Otimizando para produção..."
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
    log_info "✅ Otimização concluída"
fi

# ============================================
# 9. RESUMO
# ============================================
echo ""
echo "============================================="
echo "  🚀 SM Componentes - Aplicação iniciada"
echo "============================================="
echo "  🌐 URL: ${APP_URL:-https://smcomponentes.onrender.com}"
echo "  🔧 Ambiente: ${APP_ENV:-production}"
echo "  🗄️  Banco: PostgreSQL"
echo "  📊 Conexão: $([ "$DB_CONNECTED" = true ] && echo '✅ OK' || echo '❌ FALHOU')"
echo "============================================="
echo ""

exec "$@"