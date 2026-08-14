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

# ============================================
# 1. DEFINIR VARIÁVEIS DO BANCO
# ============================================
log_step "Configurando banco de dados..."

# VALORES DO SEU BANCO - SUBSTITUA A SENHA!
DB_HOST="pdpg-d9to3a3m8hqs73dlvt70-a"
DB_PORT="5432"
DB_DATABASE="loja_virtual_9n3c"
DB_USERNAME="loja_virtual_9n3c_user"
DB_PASSWORD="COLOQUE_A_SENHA_AQUI"  # ⚠️ SUBSTITUA PELA SENHA REAL!

# Verifica se a senha foi preenchida
if [ "$DB_PASSWORD" = "COLOQUE_A_SENHA_AQUI" ]; then
    log_error "❌ Senha do banco não configurada!"
    log_error "Edite o docker-entrypoint.sh e coloque a senha real"
    log_error "A senha está em: Dashboard → PostgreSQL → Connect"
    exit 1
fi

log_info "✅ Banco configurado:"
log_info "  Host: $DB_HOST"
log_info "  Database: $DB_DATABASE"
log_info "  Username: $DB_USERNAME"

# ============================================
# 2. CRIAR .env
# ============================================
log_step "Criando arquivo .env..."

cat > .env << EOF
APP_NAME="${APP_NAME:-Loja Virtual SM Componentes}"
APP_ENV="${APP_ENV:-production}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_TIMEZONE="${APP_TIMEZONE:-America/Sao_Paulo}"
APP_URL="${APP_URL:-https://loja-vitual-smcomponentes.onrender.com}"
ASSET_URL="${ASSET_URL:-https://loja-vitual-smcomponentes.onrender.com}"
APP_KEY="${APP_KEY:-}"

DB_CONNECTION=pgsql
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD
DB_SSLMODE=require

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

VITE_APP_URL="${VITE_APP_URL:-https://loja-vitual-smcomponentes.onrender.com}"
FORCE_HTTPS="${FORCE_HTTPS:-true}"
RUN_SEEDERS="${RUN_SEEDERS:-true}"
REFRESH_DATABASE="${REFRESH_DATABASE:-false}"
FORCE_SEEDERS="${FORCE_SEEDERS:-false}"
EOF

log_info "✅ .env criado"

# ============================================
# 3. GERAR APP_KEY
# ============================================
log_step "Gerando APP_KEY..."

if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    php artisan key:generate --force
    log_info "✅ APP_KEY gerada"
else
    log_info "✅ APP_KEY já existe"
fi

# ============================================
# 4. CRIAR DIRETÓRIOS
# ============================================
log_step "Criando diretórios..."
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache public/build 2>/dev/null || true

# ============================================
# 5. TESTAR CONEXÃO
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
        log_warn "Aguardando banco... ($RETRY/$MAX_RETRIES)"
        sleep 2
    fi
done

if [ "$DB_CONNECTED" = false ]; then
    log_error "❌ Falha ao conectar ao banco!"
    exit 1
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
log_step "Rodando migrations..."
php artisan migrate --force
log_info "✅ Migrations concluídas"

# ============================================
# 8. RODAR SEEDERS
# ============================================
if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    log_step "Rodando seeders..."
    php artisan db:seed --force
    log_info "✅ Seeders concluídos"
fi

# ============================================
# 9. STORAGE LINK
# ============================================
log_step "Criando storage link..."
php artisan storage:link 2>/dev/null || true

# ============================================
# 10. OTIMIZAR
# ============================================
if [ "${APP_ENV:-production}" = "production" ]; then
    log_step "Otimizando para produção..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    log_info "✅ Otimização concluída"
fi

# ============================================
# 11. RESUMO
# ============================================
echo ""
echo "============================================="
echo "  🚀 SM Componentes - Aplicação iniciada"
echo "============================================="
echo "  🌐 URL: ${APP_URL:-https://loja-vitual-smcomponentes.onrender.com}"
echo "  🔧 Ambiente: ${APP_ENV:-production}"
echo "  🗄️  Banco: PostgreSQL ✅"
echo "  📊 Migrações: ✅ Executadas"
echo "  📊 Seeders: ✅ Executados"
echo "============================================="
echo ""

exec "$@"