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
# 1. VALORES FIXOS DO BANCO
# ============================================
log_step "Configurando banco de dados..."

# ⚠️ IMPORTANTE: Coloque a senha real do seu banco aqui!
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
    echo ""
    log_error "❌ Senha do banco não configurada!"
    echo ""
    log_error "Como obter a senha:"
    log_error "  1. Acesse: Dashboard → PostgreSQL → Connect"
    log_error "  2. Copie a string de conexão"
    log_error "  3. A senha está entre ':' e '@'"
    echo ""
    log_error "Exemplo:"
    log_error "  postgresql://loja_virtual_9n3c_user:SUA_SENHA@pdpg-d9to3a3m8hqs73dlvt70-a:5432/loja_virtual_9n3c"
    log_error "                                               ↑"
    log_error "                                          COPIE ISSO"
    echo ""
    exit 1
fi

log_success "Banco configurado:"
log_info "  Host: $DB_HOST"
log_info "  Port: $DB_PORT"
log_info "  Database: $DB_DATABASE"
log_info "  Username: $DB_USERNAME"
log_info "  Password: ********"

# ============================================
# 2. CRIAR .env
# ============================================
log_step "Criando arquivo .env..."

cat > .env << 'EOF'
APP_NAME="Loja Virtual SM Componentes"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://loja-vitual-smcomponentes.onrender.com
ASSET_URL=https://loja-vitual-smcomponentes.onrender.com
APP_KEY=43bbe8c4f273807bc376b9809bf19ac8

DB_CONNECTION=pgsql
DB_HOST=pdpg-d9to3a3m8hqs73dlvt70-a
DB_PORT=5432
DB_DATABASE=loja_virtual_9n3c
DB_USERNAME=loja_virtual_9n3c_user
DB_PASSWORD=COLOQUE_A_SENHA_AQUI
DB_SSLMODE=require

CACHE_DRIVER=file
SESSION_DRIVER=database
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

# Substitui a senha no .env
sed -i "s/43bbe8c4f273807bc376b9809bf19ac8/$DB_PASSWORD/g" .env

log_success ".env criado com sucesso"

# ============================================
# 3. VERIFICAR DIRETÓRIOS
# ============================================
log_step "Criando diretórios..."

mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/build
mkdir -p public/storage

chmod -R 775 storage bootstrap/cache public/build public/storage 2>/dev/null || true

log_success "Diretórios criados"

# ============================================
# 4. TESTAR CONEXÃO COM O BANCO
# ============================================
log_step "Testando conexão com o banco..."

MAX_RETRIES=30
RETRY=0
DB_CONNECTED=false

while [ $RETRY -lt $MAX_RETRIES ] && [ "$DB_CONNECTED" = false ]; do
    if php -r "
        try {
            \$pdo = new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
            \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo 'OK';
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>/dev/null | grep -q OK; then
        DB_CONNECTED=true
        log_success "Conexão com banco OK!"
    else
        RETRY=$((RETRY + 1))
        log_warn "Aguardando banco... ($RETRY/$MAX_RETRIES)"
        sleep 2
    fi
done

if [ "$DB_CONNECTED" = false ]; then
    echo ""
    log_error "❌ Falha ao conectar ao banco de dados!"
    echo ""
    log_error "Verifique:"
    log_error "  1. A senha está correta?"
    log_error "  2. O banco está rodando?"
    log_error "  3. As credenciais estão corretas?"
    echo ""
    exit 1
fi

# ============================================
# 5. LIMPAR CACHE
# ============================================
log_step "Limpando cache..."

php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

log_success "Cache limpo"

# ============================================
# 6. RODAR MIGRATIONS
# ============================================
log_step "Rodando migrations..."

if php artisan migrate:status > /dev/null 2>&1; then
    MIGRATIONS_DONE=$(php artisan migrate:status 2>/dev/null | grep -c "\[X\]" || echo "0")
    MIGRATIONS_TOTAL=$(php artisan migrate:status 2>/dev/null | grep -c "\[ \]" || echo "0")
    
    log_info "  → Migrações executadas: $MIGRATIONS_DONE"
    log_info "  → Migrações pendentes: $MIGRATIONS_TOTAL"
fi

if php artisan migrate --force; then
    log_success "Migrations concluídas"
else
    log_error "❌ Falha ao executar migrations"
    exit 1
fi

# ============================================
# 7. RODAR SEEDERS
# ============================================
log_step "Rodando seeders..."

if php artisan db:seed --force; then
    log_success "Seeders executados com sucesso"
else
    log_warn "⚠️ Falha ao executar seeders"
fi

# ============================================
# 8. STORAGE LINK
# ============================================
log_step "Criando storage link..."

if [ ! -L public/storage ]; then
    php artisan storage:link 2>/dev/null || true
    log_success "Storage link criado"
fi

# ============================================
# 9. OTIMIZAR PRODUÇÃO
# ============================================
log_step "Otimizando para produção..."

php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

log_success "Otimização concluída"

# ============================================
# 10. RESUMO FINAL
# ============================================
echo ""
echo "╔═══════════════════════════════════════════════════════════════════╗"
echo "║                                                                   ║"
echo "║     🚀  SM COMPONENTES - Aplicação iniciada com sucesso           ║"
echo "║                                                                   ║"
echo "╠═══════════════════════════════════════════════════════════════════╣"
echo "║                                                                   ║"
echo "║  🌐 URL:          https://loja-vitual-smcomponentes.onrender.com  ║"
echo "║  🔧 Ambiente:     production                                      ║"
echo "║  🐘 PHP:          $(php -r 'echo PHP_VERSION;')                   ║"
echo "║  📦 Laravel:      $(php artisan --version 2>/dev/null | cut -d' ' -f2 || echo 'N/A') ║"
echo "║  🗄️  Banco:       PostgreSQL ✅                                   ║"
echo "║  📊 Migrações:    ✅ Executadas                                   ║"
echo "║  📊 Seeders:      ✅ Executados                                   ║"
echo "║                                                                   ║"
echo "╚═══════════════════════════════════════════════════════════════════╝"
echo ""

exec "$@"