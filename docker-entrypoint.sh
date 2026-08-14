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
# 1. VALORES FIXOS DO BANCO (RENDER)
# ============================================
log_step "Configurando banco de dados..."

# ⚠️ COLOQUE A SENHA REAL AQUI!
# A senha está em: Dashboard → PostgreSQL → Connect
DB_HOST="pdpg-d9to3a3m8hqs73dlvt70-a"
DB_PORT="5432"
DB_DATABASE="loja_virtual_9n3c"
DB_USERNAME="loja_virtual_9n3c_user"
DB_PASSWORD="COLOQUE_A_SENHA_AQUI"  # ← SUBSTITUA PELA SENHA REAL!
DB_CONNECTION="pgsql"
DB_SSLMODE="require"

# Verifica se a senha foi preenchida
if [ "$DB_PASSWORD" = "COLOQUE_A_SENHA_AQUI" ] || [ -z "$DB_PASSWORD" ]; then
    log_error "❌ Senha do banco não configurada!"
    log_error "Edite o docker-entrypoint.sh e coloque a senha real do seu banco PostgreSQL"
    log_error "A senha está disponível no Render: Dashboard → PostgreSQL → Connect"
    exit 1
fi

log_info "✅ Configuração do banco:"
log_info "  Host: $DB_HOST"
log_info "  Port: $DB_PORT"
log_info "  Database: $DB_DATABASE"
log_info "  Username: $DB_USERNAME"
log_info "  Password: ********"

# ============================================
# 2. CRIAR .env
# ============================================
log_step "Criando arquivo .env..."

cat > .env << EOF
APP_NAME="Loja Virtual SM Componentes"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://loja-vitual-smcomponentes.onrender.com
ASSET_URL=https://loja-vitual-smcomponentes.onrender.com
APP_KEY=43bbe8c4f273807bc376b9809bf19ac8

DB_CONNECTION=$DB_CONNECTION
DB_HOST=$DB_HOST
DB_PORT=$DB_PORT
DB_DATABASE=$DB_DATABASE
DB_USERNAME=$DB_USERNAME
DB_PASSWORD=$DB_PASSWORD
DB_SSLMODE=$DB_SSLMODE

CACHE_DRIVER=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=sync

BROADCAST_DRIVER=log
LOG_CHANNEL=stack
LOG_LEVEL=error

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=contato@smcomponentes.com
MAIL_FROM_NAME="SM Componentes"

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MERCADOPAGO_PUBLIC_KEY=APP_USR-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
MERCADOPAGO_ACCESS_TOKEN=APP_USR-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
MERCADOPAGO_WEBHOOK_URL=https://loja-vitual-smcomponentes.onrender.com/api/webhooks/mercadopago
MERCADOPAGO_ENV=production

VITE_APP_URL=https://loja-vitual-smcomponentes.onrender.com
FORCE_HTTPS=true
RUN_SEEDERS=true
REFRESH_DATABASE=false
FORCE_SEEDERS=false
EOF

log_info "✅ .env criado"

# ============================================
# 3. VERIFICAR CONEXÃO COM O BANCO
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
    log_error "Verifique se a senha está correta!"
    log_error "DB_HOST: $DB_HOST"
    log_error "DB_PORT: $DB_PORT"
    log_error "DB_DATABASE: $DB_DATABASE"
    log_error "DB_USERNAME: $DB_USERNAME"
    exit 1
fi

# ============================================
# 4. DIRETÓRIOS
# ============================================
log_step "Criando diretórios..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/build
chmod -R 775 storage bootstrap/cache public/build 2>/dev/null || true

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
php artisan migrate --force 2>&1 | while read line; do
    log_info "  $line"
done
log_info "✅ Migrações concluídas"

# ============================================
# 7. RODAR SEEDERS
# ============================================
log_step "Rodando seeders..."
php artisan db:seed --force 2>&1 | while read line; do
    log_info "  $line"
done
log_info "✅ Seeders concluídos"

# ============================================
# 8. STORAGE LINK
# ============================================
log_step "Criando storage link..."
php artisan storage:link 2>/dev/null || true

# ============================================
# 9. OTIMIZAR
# ============================================
log_step "Otimizando para produção..."
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

# ============================================
# 10. VERIFICAR ROTAS
# ============================================
log_step "Verificando aplicação..."
php artisan route:list 2>/dev/null | head -5 || true

# ============================================
# 11. RESUMO
# ============================================
echo ""
echo "============================================="
echo "  🚀 SM Componentes - Aplicação iniciada"
echo "============================================="
echo "  🌐 URL: https://loja-vitual-smcomponentes.onrender.com"
echo "  🔧 Ambiente: production"
echo "  🗄️  Banco: PostgreSQL"
echo "  📊 Conexão: ✅ OK"
echo "  📊 Migrações: ✅ Executadas"
echo "  📊 Seeders: ✅ Executados"
echo "============================================="
echo ""

exec "$@"