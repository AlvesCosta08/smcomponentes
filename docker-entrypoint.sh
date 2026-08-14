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
# 1. DEFINIR VALORES DAS VARIÁVEIS
# ============================================
log_step "Configurando variáveis de ambiente..."

# Verifica se as variáveis estão definidas, senão usa valores fixos
# IMPORTANTE: Substitua pelos valores REAIS do seu banco no Render
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = '${DB_HOST}' ]; then
    log_warn "DB_HOST não definido, usando valor fixo..."
    DB_HOST="postgresql-smcomponentes.render.com"  # Substitua pelo seu host
fi

if [ -z "$DB_PORT" ] || [ "$DB_PORT" = '${DB_PORT}' ]; then
    log_warn "DB_PORT não definido, usando valor fixo..."
    DB_PORT="5432"
fi

if [ -z "$DB_DATABASE" ] || [ "$DB_DATABASE" = '${DB_DATABASE}' ]; then
    log_warn "DB_DATABASE não definido, usando valor fixo..."
    DB_DATABASE="smcomponentes_db"  # Substitua pelo nome do seu banco
fi

if [ -z "$DB_USERNAME" ] || [ "$DB_USERNAME" = '${DB_USERNAME}' ]; then
    log_warn "DB_USERNAME não definido, usando valor fixo..."
    DB_USERNAME="smcomponentes_user"  # Substitua pelo seu usuário
fi

if [ -z "$DB_PASSWORD" ] || [ "$DB_PASSWORD" = '${DB_PASSWORD}' ]; then
    log_warn "DB_PASSWORD não definido, usando valor fixo..."
    DB_PASSWORD="sua_senha_aqui"  # Substitua pela sua senha
fi

log_info "✅ Variáveis configuradas:"
log_info "  DB_HOST: $DB_HOST"
log_info "  DB_PORT: $DB_PORT"
log_info "  DB_DATABASE: $DB_DATABASE"
log_info "  DB_USERNAME: $DB_USERNAME"
log_info "  DB_CONNECTION: ${DB_CONNECTION:-pgsql}"

# ============================================
# 2. CRIAR .env
# ============================================
log_step "Criando arquivo .env..."

cat > .env << EOF
APP_NAME="${APP_NAME:-Loja Virtual SM Componentes}"
APP_ENV="${APP_ENV:-production}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_TIMEZONE="${APP_TIMEZONE:-America/Sao_Paulo}"
APP_URL="${APP_URL:-https://smcomponentes.onrender.com}"
ASSET_URL="${ASSET_URL:-${APP_URL:-https://smcomponentes.onrender.com}}"
APP_KEY="${APP_KEY:-}"

DB_CONNECTION="${DB_CONNECTION:-pgsql}"
DB_HOST="${DB_HOST}"
DB_PORT="${DB_PORT:-5432}"
DB_DATABASE="${DB_DATABASE}"
DB_USERNAME="${DB_USERNAME}"
DB_PASSWORD="${DB_PASSWORD}"
DB_SSLMODE="${DB_SSLMODE:-require}"

CACHE_DRIVER="${CACHE_DRIVER:-file}"
SESSION_DRIVER="${SESSION_DRIVER:-database}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"
SESSION_SECURE_COOKIE="${SESSION_SECURE_COOKIE:-true}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

BROADCAST_DRIVER="${BROADCAST_DRIVER:-log}"
LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_LEVEL="${LOG_LEVEL:-error}"

MAIL_MAILER="${MAIL_MAILER:-smtp}"
MAIL_HOST="${MAIL_HOST:-smtp.mailtrap.io}"
MAIL_PORT="${MAIL_PORT:-2525}"
MAIL_USERNAME="${MAIL_USERNAME:-null}"
MAIL_PASSWORD="${MAIL_PASSWORD:-null}"
MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-null}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-contato@smcomponentes.com}"
MAIL_FROM_NAME="${MAIL_FROM_NAME:-SM Componentes}"

REDIS_HOST="${REDIS_HOST:-127.0.0.1}"
REDIS_PASSWORD="${REDIS_PASSWORD:-null}"
REDIS_PORT="${REDIS_PORT:-6379}"

MERCADOPAGO_PUBLIC_KEY="${MERCADOPAGO_PUBLIC_KEY:-}"
MERCADOPAGO_ACCESS_TOKEN="${MERCADOPAGO_ACCESS_TOKEN:-}"
MERCADOPAGO_WEBHOOK_URL="${MERCADOPAGO_WEBHOOK_URL:-}"
MERCADOPAGO_ENV="${MERCADOPAGO_ENV:-production}"

VITE_APP_URL="${VITE_APP_URL:-${APP_URL:-https://smcomponentes.onrender.com}}"

FORCE_HTTPS="${FORCE_HTTPS:-true}"

RUN_SEEDERS="${RUN_SEEDERS:-false}"
REFRESH_DATABASE="${REFRESH_DATABASE:-false}"
FORCE_SEEDERS="${FORCE_SEEDERS:-false}"
EOF

log_info "✅ .env criado"

# Mostra configuração (sem senha)
log_info "📊 Configuração:"
grep -E "^(APP_ENV|APP_URL|DB_CONNECTION|DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME)=" .env | while read -r line; do
    log_info "  $line"
done

# ============================================
# 3. GERAR APP_KEY
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
# 4. CRIAR DIRETÓRIOS
# ============================================
log_step "Criando diretórios..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache public/build 2>/dev/null || true

# ============================================
# 5. TESTAR CONEXÃO COM O BANCO
# ============================================
log_step "Testando conexão com o banco..."

MAX_RETRIES=30
RETRY=0
DB_CONNECTED=false

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
    log_warn "⚠️ Não foi possível conectar ao banco."
fi

# ============================================
# 6. LIMPAR CACHE
# ============================================
log_step "Limpando cache..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

# ============================================
# 7. RODAR MIGRATIONS
# ============================================
if [ "$DB_CONNECTED" = true ]; then
    log_step "Rodando migrations..."
    php artisan migrate --force 2>/dev/null || log_warn "⚠️ Migrations falharam"
    log_info "✅ Migrações concluídas"
else
    log_warn "⚠️ Pulando migrations (banco não conectado)"
fi

# ============================================
# 8. LINK STORAGE
# ============================================
log_step "Criando storage link..."
if [ ! -L public/storage ]; then
    php artisan storage:link 2>/dev/null || true
fi

# ============================================
# 9. OTIMIZAR
# ============================================
if [ "${APP_ENV:-production}" = "production" ] && [ "$DB_CONNECTED" = true ]; then
    log_step "Otimizando para produção..."
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
    log_info "✅ Otimização concluída"
fi

# ============================================
# 10. RESUMO
# ============================================
echo ""
echo "============================================="
echo "  🚀 SM Componentes - Aplicação iniciada"
echo "============================================="
echo "  🌐 URL: ${APP_URL:-https://smcomponentes.onrender.com}"
echo "  🔧 Ambiente: ${APP_ENV:-production}"
echo "  🗄️  Banco: ${DB_CONNECTION:-pgsql}"
echo "  📊 Conexão: $([ "$DB_CONNECTED" = true ] && echo '✅ OK' || echo '❌ FALHOU')"
echo "============================================="
echo ""

exec "$@"