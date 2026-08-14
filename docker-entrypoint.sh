#!/bin/bash
set -e

# ============================================
# CORES
# ============================================
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

log_info() { echo -e "${GREEN}[INFO]${NC} $1"; }
log_warn() { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }
log_step() { echo -e "${BLUE}[STEP]${NC} $1"; }
log_success() { echo -e "${GREEN}✅${NC} $1"; }
log_fail() { echo -e "${RED}❌${NC} $1"; }

# ============================================
# FUNÇÃO PARA VALIDAR VARIÁVEIS OBRIGATÓRIAS
# ============================================
validate_required_vars() {
    local missing_vars=()
    
    # Lista de variáveis obrigatórias
    local required_vars=(
        "DB_HOST"
        "DB_PORT"
        "DB_DATABASE"
        "DB_USERNAME"
        "DB_PASSWORD"
    )
    
    for var in "${required_vars[@]}"; do
        if [ -z "${!var}" ]; then
            missing_vars+=("$var")
        fi
    done
    
    if [ ${#missing_vars[@]} -gt 0 ]; then
        log_error "❌ Variáveis obrigatórias não configuradas!"
        echo ""
        for var in "${missing_vars[@]}"; do
            log_error "  → $var não está definida"
        done
        echo ""
        log_error "Configure as variáveis no Render:"
        log_error "  Dashboard → Seu serviço → Environment → Add Variable"
        return 1
    fi
    
    return 0
}

# ============================================
# FUNÇÃO PARA TESTAR CONEXÃO COM O BANCO
# ============================================
test_db_connection() {
    local max_retries=30
    local retry=0
    local connected=false
    
    log_step "Testando conexão com o banco de dados..."
    log_info "  Host: $DB_HOST"
    log_info "  Port: $DB_PORT"
    log_info "  Database: $DB_DATABASE"
    log_info "  Username: $DB_USERNAME"
    
    while [ $retry -lt $max_retries ] && [ "$connected" = false ]; do
        if php -r "
            try {
                \$pdo = new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
                \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo 'OK';
            } catch (PDOException \$e) {
                echo 'ERROR: ' . \$e->getMessage();
                exit(1);
            }
        " 2>&1 | grep -q "OK"; then
            connected=true
            log_success "Conexão com banco estabelecida!"
        else
            retry=$((retry + 1))
            log_warn "Aguardando banco... ($retry/$max_retries)"
            sleep 2
        fi
    done
    
    if [ "$connected" = false ]; then
        log_error "❌ Falha ao conectar ao banco de dados!"
        echo ""
        log_error "Possíveis causas:"
        log_error "  1. Senha incorreta → Verifique DB_PASSWORD"
        log_error "  2. Host incorreto → Verifique DB_HOST"
        log_error "  3. Banco não está rodando → Aguarde o PostgreSQL iniciar"
        log_error "  4. Credenciais inválidas → Verifique DB_USERNAME e DB_DATABASE"
        echo ""
        log_error "Comando para testar manualmente:"
        log_error "  php -r \"try { \$pdo = new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', 'SUA_SENHA'); echo 'OK'; } catch (Exception \$e) { echo \$e->getMessage(); }\""
        return 1
    fi
    
    return 0
}

# ============================================
# FUNÇÃO PARA VALIDAR ARQUIVOS E DIRETÓRIOS
# ============================================
validate_files_and_directories() {
    log_step "Validando arquivos e diretórios..."
    
    local errors=0
    
    # Verifica se o artisan existe
    if [ ! -f artisan ]; then
        log_error "❌ Arquivo artisan não encontrado!"
        log_error "  Certifique-se de que está na raiz do projeto"
        errors=1
    fi
    
    # Verifica se o composer.json existe
    if [ ! -f composer.json ]; then
        log_error "❌ Arquivo composer.json não encontrado!"
        errors=1
    fi
    
    # Verifica se o vendor existe
    if [ ! -d vendor ]; then
        log_warn "⚠️ Diretório vendor não encontrado"
        log_info "  → As dependências serão instaladas automaticamente"
    fi
    
    # Verifica permissões
    if [ -d storage ] && [ ! -w storage ]; then
        log_warn "⚠️ Diretório storage sem permissão de escrita"
        log_info "  → Corrigindo permissões..."
        chmod -R 775 storage 2>/dev/null || true
    fi
    
    if [ -d bootstrap/cache ] && [ ! -w bootstrap/cache ]; then
        log_warn "⚠️ Diretório bootstrap/cache sem permissão de escrita"
        log_info "  → Corrigindo permissões..."
        chmod -R 775 bootstrap/cache 2>/dev/null || true
    fi
    
    if [ $errors -eq 1 ]; then
        return 1
    fi
    
    log_success "Validação de arquivos concluída"
    return 0
}

# ============================================
# FUNÇÃO PARA VERIFICAR EXTENSÕES PHP
# ============================================
validate_php_extensions() {
    log_step "Verificando extensões PHP..."
    
    local required_extensions=(
        "pdo"
        "pdo_pgsql"
        "pgsql"
        "mbstring"
        "exif"
        "pcntl"
        "bcmath"
        "gd"
        "zip"
        "intl"
        "opcache"
        "sockets"
        "soap"
        "sodium"
        "json"
        "xml"
        "curl"
        "fileinfo"
    )
    
    local missing_extensions=()
    
    for ext in "${required_extensions[@]}"; do
        if ! php -m | grep -qi "^$ext$"; then
            missing_extensions+=("$ext")
        fi
    done
    
    if [ ${#missing_extensions[@]} -gt 0 ]; then
        log_warn "⚠️ Extensões PHP não encontradas:"
        for ext in "${missing_extensions[@]}"; do
            log_warn "  → $ext"
        done
        log_info "  → Elas serão instaladas durante o build do Docker"
    else
        log_success "Todas as extensões PHP estão disponíveis"
    fi
    
    return 0
}

# ============================================
# FUNÇÃO PARA VALIDAR CONFIGURAÇÃO DO LARAVEL
# ============================================
validate_laravel_config() {
    log_step "Validando configuração do Laravel..."
    
    # Tenta carregar as configurações
    if php artisan config:clear 2>/dev/null; then
        log_success "Configuração do Laravel OK"
        return 0
    else
        log_error "❌ Falha ao carregar configuração do Laravel"
        return 1
    fi
}

# ============================================
# FUNÇÃO PARA EXECUTAR COMANDOS COM VALIDAÇÃO
# ============================================
run_command() {
    local cmd="$1"
    local description="${2:-$1}"
    
    log_info "  → Executando: $description"
    
    if eval "$cmd" 2>&1 | while IFS= read -r line; do
        echo "    $line"
    done; then
        log_success "  → $description concluído"
        return 0
    else
        log_error "  → Falha ao executar: $description"
        return 1
    fi
}

# ============================================
# 1. VALIDAR VARIÁVEIS DE AMBIENTE
# ============================================
echo ""
echo "╔═══════════════════════════════════════════════════════════════════╗"
echo "║                                                                   ║"
echo "║     🔍  SM COMPONENTES - Validando ambiente de execução           ║"
echo "║                                                                   ║"
echo "╚═══════════════════════════════════════════════════════════════════╝"
echo ""

log_step "Validando variáveis de ambiente..."

if ! validate_required_vars; then
    echo ""
    log_error "❌ Validação falhou! Corrija os erros e tente novamente."
    exit 1
fi

log_success "Variáveis de ambiente validadas"

# ============================================
# 2. VALIDAR EXTENSÕES PHP
# ============================================
validate_php_extensions

# ============================================
# 3. VALIDAR ARQUIVOS E DIRETÓRIOS
# ============================================
if ! validate_files_and_directories; then
    log_error "❌ Validação de arquivos falhou!"
    exit 1
fi

# ============================================
# 4. CRIAR .env
# ============================================
log_step "Criando arquivo .env..."

if [ -f .env ]; then
    log_warn "⚠️ Arquivo .env já existe, fazendo backup..."
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
fi

cat > .env << EOF
APP_NAME="${APP_NAME:-Loja Virtual SM Componentes}"
APP_ENV="${APP_ENV:-production}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_TIMEZONE="${APP_TIMEZONE:-America/Sao_Paulo}"
APP_URL="${APP_URL:-https://loja-vitual-smcomponentes.onrender.com}"
ASSET_URL="${ASSET_URL:-https://loja-vitual-smcomponentes.onrender.com}"
APP_KEY="${APP_KEY:-}"

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
DB_SSLMODE=${DB_SSLMODE:-require}

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

log_success ".env criado com sucesso"

# ============================================
# 5. GERAR APP_KEY
# ============================================
log_step "Validando APP_KEY..."

if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env || grep -q "^APP_KEY=base64:...$" .env; then
    log_warn "APP_KEY não encontrada, gerando automaticamente..."
    
    if php artisan key:generate --force; then
        APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2- | tr -d '\r' | xargs)
        log_success "APP_KEY gerada: ${APP_KEY:0:20}..."
    else
        log_error "❌ Falha ao gerar APP_KEY"
        exit 1
    fi
else
    APP_KEY=$(grep "^APP_KEY=" .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    log_success "APP_KEY já existe: ${APP_KEY:0:20}..."
fi

# ============================================
# 6. INSTALAR DEPENDÊNCIAS
# ============================================
log_step "Verificando dependências..."

if [ ! -f vendor/autoload.php ]; then
    log_warn "Vendor não encontrado, instalando dependências PHP..."
    if composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist; then
        log_success "Dependências PHP instaladas"
    else
        log_error "❌ Falha ao instalar dependências PHP"
        exit 1
    fi
else
    log_success "Dependências PHP já instaladas"
fi

if [ -f package.json ] && [ ! -d node_modules ]; then
    log_warn "Node modules não encontrados, instalando..."
    if npm ci --no-audit --no-fund --prefer-offline --legacy-peer-deps 2>/dev/null || npm install --legacy-peer-deps; then
        log_success "Dependências Node instaladas"
    else
        log_warn "⚠️ Falha ao instalar dependências Node"
    fi
fi

# ============================================
# 7. CRIAR DIRETÓRIOS
# ============================================
log_step "Criando diretórios..."

mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p public/build
mkdir -p public/storage

chmod -R 775 storage bootstrap/cache public/build public/storage 2>/dev/null || true

log_success "Diretórios criados e permissões configuradas"

# ============================================
# 8. TESTAR CONEXÃO COM O BANCO
# ============================================
if ! test_db_connection; then
    log_error "❌ Validação do banco falhou!"
    exit 1
fi

# ============================================
# 9. LIMPAR CACHE
# ============================================
log_step "Limpando cache..."

php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

log_success "Cache limpo"

# ============================================
# 10. RODAR MIGRATIONS
# ============================================
log_step "Rodando migrations..."

if php artisan migrate:status > /dev/null 2>&1; then
    MIGRATIONS_DONE=$(php artisan migrate:status 2>/dev/null | grep -c "\[X\]" || echo "0")
    MIGRATIONS_TOTAL=$(php artisan migrate:status 2>/dev/null | grep -c "\[ \]" || echo "0")
    
    log_info "  → Migrações executadas: $MIGRATIONS_DONE"
    log_info "  → Migrações pendentes: $MIGRATIONS_TOTAL"
fi

if php artisan migrate --force; then
    log_success "Migrations concluídas com sucesso"
else
    log_error "❌ Falha ao executar migrations"
    exit 1
fi

# ============================================
# 11. RODAR SEEDERS
# ============================================
if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    log_step "Rodando seeders..."
    
    if php artisan db:seed --force; then
        log_success "Seeders executados com sucesso"
    else
        log_warn "⚠️ Falha ao executar seeders (não crítico)"
    fi
fi

# ============================================
# 12. STORAGE LINK
# ============================================
log_step "Criando storage link..."

if [ ! -L public/storage ]; then
    if php artisan storage:link 2>/dev/null; then
        log_success "Storage link criado"
    else
        log_warn "⚠️ Falha ao criar storage link"
    fi
else
    log_info "  → Storage link já existe"
fi

# ============================================
# 13. OTIMIZAR PARA PRODUÇÃO
# ============================================
if [ "${APP_ENV:-production}" = "production" ]; then
    log_step "Otimizando para produção..."
    
    php artisan config:cache || log_warn "⚠️ Config cache falhou"
    php artisan route:cache || log_warn "⚠️ Route cache falhou"
    php artisan view:cache || log_warn "⚠️ View cache falhou"
    php artisan event:cache || log_warn "⚠️ Event cache falhou"
    
    log_success "Otimização concluída"
fi

# ============================================
# 14. VALIDAR CONFIGURAÇÃO FINAL
# ============================================
log_step "Validando configuração final..."

if php artisan route:list > /dev/null 2>&1; then
    log_success "Rotas carregadas com sucesso"
else
    log_warn "⚠️ Falha ao carregar rotas (verifique se há rotas definidas)"
fi

if php artisan db:show > /dev/null 2>&1; then
    log_success "Conexão com banco de dados OK"
else
    log_warn "⚠️ Falha ao verificar conexão com banco"
fi

# ============================================
# 15. RESUMO FINAL
# ============================================
echo ""
echo "╔═══════════════════════════════════════════════════════════════════╗"
echo "║                                                                   ║"
echo "║     🚀  SM COMPONENTES - Aplicação iniciada com sucesso           ║"
echo "║                                                                   ║"
echo "╠═══════════════════════════════════════════════════════════════════╣"
echo "║                                                                   ║"
echo "║  🌐 URL:          ${APP_URL:-https://loja-vitual-smcomponentes.onrender.com}"
echo "║  🔧 Ambiente:     ${APP_ENV:-production}"
echo "║  🐘 PHP:          $(php -r 'echo PHP_VERSION;')"
echo "║  📦 Laravel:      $(php artisan --version 2>/dev/null | cut -d' ' -f2 || echo 'N/A')"
echo "║  🗄️  Banco:       PostgreSQL ${DB_HOST}:${DB_PORT}/${DB_DATABASE}"
echo "║  📊 Conexão:      ✅ OK"
echo "║  📊 Migrações:    ✅ Executadas"
echo "║  📊 Seeders:      $([ "${RUN_SEEDERS:-true}" = "true" ] && echo '✅ Executados' || echo '➖ Pulados')"
echo "║  🕒  Data/Hora:   $(date '+%d/%m/%Y %H:%M:%S')"
echo "║                                                                   ║"
echo "╚═══════════════════════════════════════════════════════════════════╝"
echo ""

exec "$@"