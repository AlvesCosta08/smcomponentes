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

# ============================================
# FUNÇÕES DE LOG
# ============================================
log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }
log_step() { echo -e "${BLUE}[STEP]${NC} $1"; }

# ============================================
# 1. CRIAR .env COM VALORES DAS VARIÁVEIS DE AMBIENTE
# ============================================
log_step "Criando arquivo .env com valores das variáveis..."

# Verifica se as variáveis críticas estão definidas
if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ]; then
    log_error "❌ Variáveis de banco não definidas!"
    log_error "DB_HOST: ${DB_HOST:-NÃO DEFINIDO}"
    log_error "DB_DATABASE: ${DB_DATABASE:-NÃO DEFINIDO}"
    log_error "Verifique as variáveis de ambiente no Render!"
    exit 1
fi

log_info "✅ Variáveis encontradas:"
log_info "  DB_HOST: $DB_HOST"
log_info "  DB_PORT: ${DB_PORT:-5432}"
log_info "  DB_DATABASE: $DB_DATABASE"
log_info "  DB_USERNAME: ${DB_USERNAME:-NÃO DEFINIDO}"
log_info "  DB_CONNECTION: ${DB_CONNECTION:-pgsql}"

# Cria .env diretamente com os valores
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

log_info "✅ .env criado com sucesso"

# Mostra configuração do banco (sem a senha)
log_info "📊 Configuração do banco:"
grep "^DB_" .env | grep -v "PASSWORD" | while read -r line; do
    log_info "  $line"
done

# ============================================
# 2. GERAR APP_KEY (se não existir)
# ============================================
log_step "Verificando APP_KEY..."

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:..." ] || [ "$APP_KEY" = "43bbe8c4f273807bc376b9809bf19ac8" ]; then
    log_warn "Gerando APP_KEY..."
    php artisan key:generate --force 2>/dev/null || true
    # Pega a chave gerada do .env
    APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    if [ -n "$APP_KEY" ]; then
        log_info "APP_KEY gerada: ${APP_KEY:0:15}..."
    fi
else
    log_info "APP_KEY já definida: ${APP_KEY:0:15}..."
fi

# ============================================
# 3. CRIAR DIRETÓRIOS
# ============================================
log_step "Criando diretórios..."

mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/build

chmod -R 775 storage bootstrap/cache public/build 2>/dev/null || true

# ============================================
# 4. AGUARDAR BANCO DE DADOS
# ============================================
log_step "Aguardando banco de dados..."

MAX_RETRIES=30
RETRY=0
DB_CONNECTED=false

# Pega os valores do .env (ou das variáveis)
DB_CONNECTION=$(grep ^DB_CONNECTION .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_HOST=$(grep ^DB_HOST .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_PORT=$(grep ^DB_PORT .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_DATABASE=$(grep ^DB_DATABASE .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_USERNAME=$(grep ^DB_USERNAME .env | cut -d '=' -f2- | tr -d '\r' | xargs)
DB_PASSWORD=$(grep ^DB_PASSWORD .env | cut -d '=' -f2- | tr -d '\r' | xargs)

log_info "Testando conexão: $DB_CONNECTION://$DB_HOST:$DB_PORT/$DB_DATABASE"

while [ $RETRY -lt $MAX_RETRIES ]; do
    # Tenta conectar com PHP PDO
    if php -r "
        try {
            \$pdo = new PDO('$DB_CONNECTION:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
            echo 'OK';
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null | grep -q OK; then
        DB_CONNECTED=true
        log_info "✅ Banco conectado!"
        break
    fi
    
    RETRY=$((RETRY + 1))
    log_warn "Aguardando banco... (${RETRY}/${MAX_RETRIES})"
    sleep 2
done

if [ "$DB_CONNECTED" = false ]; then
    log_warn "⚠️ Não foi possível conectar ao banco. Continuando mesmo assim..."
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
log_step "Rodando migrations..."

if [ "$DB_CONNECTED" = true ]; then
    log_info "✅ Banco conectado. Executando migrações..."
    php artisan migrate --force 2>/dev/null || log_warn "⚠️ Migrations falharam"
    log_info "✅ Migrações concluídas"
else
    log_warn "⚠️ Banco não conectado. Pulando migrations."
fi

# ============================================
# 7. LINK STORAGE
# ============================================
log_step "Criando storage link..."

if [ ! -L public/storage ]; then
    php artisan storage:link 2>/dev/null || true
    log_info "Storage link criado"
else
    log_info "Storage link já existe"
fi

# ============================================
# 8. OTIMIZAR (PRODUÇÃO)
# ============================================
if [ "${APP_ENV:-production}" = "production" ]; then
    log_step "Ambiente PRODUÇÃO - Otimizando..."
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
    log_info "✅ Otimização concluída"
fi

# ============================================
# 9. VERIFICAR CONEXÃO FINAL
# ============================================
log_step "Verificando conexão final..."

if php artisan db:show 2>/dev/null; then
    log_info "✅ Conexão com banco de dados OK"
else
    log_warn "⚠️ Conexão com banco de dados falhou"
fi

# ============================================
# 10. RESUMO FINAL
# ============================================
echo ""
echo "============================================="
echo "  🚀 SM Componentes - Aplicação iniciada"
echo "============================================="
echo "  🌐 URL: ${APP_URL:-https://smcomponentes.onrender.com}"
echo "  🔧 Ambiente: ${APP_ENV:-production}"
echo "  🐘 PHP: $(php -r 'echo PHP_VERSION;')"
echo "  🗄️  Banco: ${DB_CONNECTION:-pgsql}"
echo "  📊 Conexão: $([ "$DB_CONNECTED" = true ] && echo '✅ OK' || echo '❌ FALHOU')"
echo "============================================="
echo ""

exec "$@"