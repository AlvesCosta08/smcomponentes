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
# FUNÇÃO PARA SUBSTITUIR VARIÁVEIS NO .ENV
# ============================================
substitute_env_vars() {
    log_step "Substituindo variáveis de ambiente no .env..."
    
    if [ ! -f .env ]; then
        log_error ".env não encontrado!"
        return 1
    fi
    
    # Lista de variáveis para substituir
    VARS=(
        "DB_HOST"
        "DB_PORT"
        "DB_DATABASE"
        "DB_USERNAME"
        "DB_PASSWORD"
        "DB_SSLMODE"
        "APP_URL"
        "APP_ENV"
        "APP_DEBUG"
        "REDIS_HOST"
        "REDIS_PORT"
        "REDIS_PASSWORD"
        "MERCADOPAGO_PUBLIC_KEY"
        "MERCADOPAGO_ACCESS_TOKEN"
        "MERCADOPAGO_WEBHOOK_URL"
        "VITE_APP_URL"
        "FORCE_HTTPS"
    )
    
    for VAR in "${VARS[@]}"; do
        if [ -n "${!VAR}" ]; then
            # Escapa caracteres especiais para sed
            VALUE=$(echo "${!VAR}" | sed -e 's/[\/&]/\\&/g')
            # Substitui ${VAR} ou VAR_PLACEHOLDER
            sed -i "s/\${$VAR}/$VALUE/g" .env
            sed -i "s/${VAR}_PLACEHOLDER/$VALUE/g" .env
        fi
    done
    
    # Verifica se ainda há variáveis não substituídas
    if grep -q "\${" .env; then
        log_warn "⚠️ Ainda há variáveis não substituídas no .env"
        grep "\${" .env | while read -r line; do
            log_warn "  $line"
        done
        log_warn "⚠️ O aplicativo pode não funcionar corretamente!"
    else
        log_info "✅ Todas as variáveis foram substituídas"
    fi
}

# ============================================
# FUNÇÃO PARA AGUARDAR BANCO DE DADOS
# ============================================
wait_for_db() {
    log_step "Aguardando banco de dados..."

    # Lê as variáveis do .env
    DB_CONNECTION=$(grep ^DB_CONNECTION .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    DB_HOST=$(grep ^DB_HOST .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    DB_PORT=$(grep ^DB_PORT .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    DB_DATABASE=$(grep ^DB_DATABASE .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    DB_USERNAME=$(grep ^DB_USERNAME .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    DB_PASSWORD=$(grep ^DB_PASSWORD .env | cut -d '=' -f2- | tr -d '\r' | xargs)

    log_info "DB_CONNECTION: $DB_CONNECTION"
    log_info "DB_HOST: $DB_HOST"
    log_info "DB_PORT: $DB_PORT"
    log_info "DB_DATABASE: $DB_DATABASE"

    if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ] || echo "$DB_HOST" | grep -q "PLACEHOLDER" || echo "$DB_HOST" | grep -q "\${"; then
        log_warn "Variáveis de banco não configuradas corretamente. Pulando verificação..."
        return 0
    fi

    # Para PostgreSQL
    if [ "$DB_CONNECTION" = "pgsql" ] || [ "$DB_CONNECTION" = "postgres" ]; then
        log_info "Testando conexão PostgreSQL: $DB_HOST:$DB_PORT"
        
        MAX_RETRIES=30
        RETRY=0
        
        # Usa PHP PDO para testar (mais confiável)
        while ! php -r "
            try {
                \$pdo = new PDO('pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
                echo 'OK';
            } catch (Exception \$e) {
                exit(1);
            }
        " 2>/dev/null | grep -q OK; do
            RETRY=$((RETRY + 1))
            if [ $RETRY -ge $MAX_RETRIES ]; then
                log_error "Timeout ao conectar ao PostgreSQL"
                log_error "Host: $DB_HOST, Port: $DB_PORT, Database: $DB_DATABASE"
                return 1
            fi
            log_warn "Aguardando PostgreSQL... (${RETRY}/${MAX_RETRIES})"
            sleep 2
        done
        
        log_info "✅ PostgreSQL conectado!"
        return 0
    fi

    # Para MySQL
    if [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mysqli" ]; then
        log_info "Testando conexão MySQL: $DB_HOST:$DB_PORT"
        
        MAX_RETRIES=30
        RETRY=0
        
        while ! php -r "
            try {
                \$pdo = new PDO('mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE', '$DB_USERNAME', '$DB_PASSWORD');
                echo 'OK';
            } catch (Exception \$e) {
                exit(1);
            }
        " 2>/dev/null | grep -q OK; do
            RETRY=$((RETRY + 1))
            if [ $RETRY -ge $MAX_RETRIES ]; then
                log_error "Timeout ao conectar ao MySQL"
                return 1
            fi
            log_warn "Aguardando MySQL... (${RETRY}/${MAX_RETRIES})"
            sleep 2
        done
        
        log_info "✅ MySQL conectado!"
        return 0
    fi

    log_warn "Conexão '$DB_CONNECTION' não suportada para espera automática"
    return 0
}

# ============================================
# FUNÇÃO PARA RODAR MIGRATIONS E SEEDERS
# ============================================
run_migrations_and_seeders() {
    log_step "Verificando e executando migrações..."

    # Tenta conectar ao banco
    if ! php artisan db:show > /dev/null 2>&1; then
        log_warn "⚠️ Banco não conectado. Pulando migrações e seeders."
        return 1
    fi

    log_info "✅ Banco conectado. Verificando migrações..."

    # Verifica se a tabela migrations existe
    if php artisan migrate:status > /dev/null 2>&1; then
        # Conta migrações já executadas
        MIGRATIONS_DONE=$(php artisan migrate:status 2>/dev/null | grep -c "\[X\]" || echo "0")
        MIGRATIONS_TOTAL=$(php artisan migrate:status 2>/dev/null | grep -c "\[ \]" || echo "0")
        
        log_info "📊 Migrações executadas: $MIGRATIONS_DONE"
        log_info "📊 Migrações pendentes: $MIGRATIONS_TOTAL"
        
        if [ "$MIGRATIONS_TOTAL" -gt 0 ] || [ "$MIGRATIONS_DONE" -eq 0 ]; then
            log_info "📦 Executando migrações..."
            php artisan migrate --force || log_error "❌ Falha ao executar migrações"
            log_info "✅ Migrações concluídas"
        else
            log_info "ℹ️ Nenhuma migração pendente"
        fi

        # Verifica se deve rodar seeders
        RUN_SEEDERS=${RUN_SEEDERS:-false}
        FORCE_SEEDERS=${FORCE_SEEDERS:-false}
        APP_ENV=$(grep ^APP_ENV .env | cut -d '=' -f2- | tr -d '\r' | xargs)
        
        if [ "$RUN_SEEDERS" = "true" ] || [ "$FORCE_SEEDERS" = "true" ] || [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
            log_step "Executando seeders..."
            
            # Verifica se deve forçar
            if [ "$FORCE_SEEDERS" = "true" ]; then
                log_warn "⚠️ Forçando execução de seeders..."
                php artisan db:seed --force || log_warn "⚠️ Falha ao executar seeders"
                log_info "✅ Seeders concluídos"
            else
                # Verifica se já tem dados
                HAS_DATA=false
                if php artisan tinker --execute="echo App\\Models\\User::count();" 2>/dev/null | grep -q "^[1-9]"; then
                    HAS_DATA=true
                fi
                
                if [ "$HAS_DATA" = false ]; then
                    log_info "🌱 Banco vazio. Executando seeders..."
                    php artisan db:seed --force || log_warn "⚠️ Falha ao executar seeders"
                    log_info "✅ Seeders concluídos"
                else
                    log_warn "⚠️ Banco já possui dados. Pulando seeders."
                    log_info "💡 Para forçar seeders, defina FORCE_SEEDERS=true"
                fi
            fi
        else
            log_info "ℹ️ Seeders não executados (RUN_SEEDERS=false ou ambiente de produção)"
        fi

        return 0
    else
        log_warn "⚠️ Não foi possível verificar status das migrações"
        return 1
    fi
}

# ============================================
# FUNÇÃO PARA RODAR MIGRATIONS EM FRESH
# ============================================
run_fresh_migrations() {
    REFRESH_DATABASE=${REFRESH_DATABASE:-false}
    APP_ENV=$(grep ^APP_ENV .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    
    if [ "$REFRESH_DATABASE" = "true" ]; then
        log_step "🔄 REFRESH DATABASE - Recriando todas as tabelas..."
        log_warn "⚠️ Isso irá apagar todos os dados existentes!"
        
        if [ "$APP_ENV" = "production" ]; then
            log_error "❌ REFRESH_DATABASE não permitido em produção!"
            return 1
        fi
        
        php artisan migrate:fresh --force || log_error "❌ Falha ao recriar banco"
        log_info "✅ Banco recriado com sucesso"
        
        RUN_SEEDERS=${RUN_SEEDERS:-false}
        if [ "$RUN_SEEDERS" = "true" ]; then
            log_step "Executando seeders no banco fresco..."
            php artisan db:seed --force || log_warn "⚠️ Falha ao executar seeders"
            log_info "✅ Seeders concluídos"
        fi
    fi
}

# ============================================
# 1. VERIFICAR .ENV E SUBSTITUIR VARIÁVEIS
# ============================================
log_step "Verificando arquivo .env..."

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        log_warn "Copiando .env.example para .env..."
        cp .env.example .env
    else
        log_warn "Criando .env vazio..."
        cat > .env << 'EOF'
APP_NAME="Loja Virtual SM Componentes"
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=${APP_URL}
ASSET_URL=${APP_URL}

DB_CONNECTION=pgsql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}
DB_SSLMODE=${DB_SSLMODE:-require}

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

MERCADOPAGO_PUBLIC_KEY=${MERCADOPAGO_PUBLIC_KEY}
MERCADOPAGO_ACCESS_TOKEN=${MERCADOPAGO_ACCESS_TOKEN}
MERCADOPAGO_WEBHOOK_URL=${MERCADOPAGO_WEBHOOK_URL}
MERCADOPAGO_ENV=production

VITE_APP_URL=${VITE_APP_URL}

FORCE_HTTPS=${FORCE_HTTPS:-true}

RUN_SEEDERS=${RUN_SEEDERS:-false}
REFRESH_DATABASE=${REFRESH_DATABASE:-false}
FORCE_SEEDERS=${FORCE_SEEDERS:-false}
EOF
    fi
    log_info ".env criado"
else
    log_info ".env encontrado"
fi

# Substitui variáveis de ambiente no .env
substitute_env_vars

# ============================================
# 2. GERAR APP_KEY
# ============================================
log_step "Verificando APP_KEY..."

if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env || grep -q "^APP_KEY=\${" .env; then
    log_warn "Gerando APP_KEY..."
    php artisan key:generate --force
    log_info "APP_KEY gerada"
else
    APP_KEY=$(grep ^APP_KEY .env | cut -d '=' -f2- | tr -d '\r' | xargs)
    log_info "APP_KEY já definida: ${APP_KEY:0:10}..."
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
chown -R www-data:www-data storage bootstrap/cache public/build 2>/dev/null || true

# ============================================
# 4. AGUARDAR BANCO DE DADOS
# ============================================
wait_for_db || true

# ============================================
# 5. LIMPAR CACHE
# ============================================
log_step "Limpando cache..."

php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# ============================================
# 6. RODAR MIGRATIONS E SEEDERS
# ============================================
# Executa refresh se configurado
run_fresh_migrations

# Executa migrações normais
run_migrations_and_seeders

# ============================================
# 7. LINK STORAGE
# ============================================
log_step "Criando storage link..."

if [ ! -L public/storage ]; then
    php artisan storage:link || true
    log_info "Storage link criado"
else
    log_info "Storage link já existe"
fi

# ============================================
# 8. VERIFICAR DEPENDÊNCIAS
# ============================================
log_step "Verificando dependências..."

if [ ! -f vendor/autoload.php ]; then
    log_warn "Vendor não encontrado. Instalando PHP dependencies..."
    composer install --no-interaction --optimize-autoloader
fi

if [ -f package.json ] && [ ! -d node_modules ]; then
    log_warn "Node modules não encontrados. Instalando..."
    npm ci || npm install
fi

# ============================================
# 9. OTIMIZAR (APENAS PRODUÇÃO)
# ============================================
if grep -q "APP_ENV=production" .env; then
    log_step "Ambiente PRODUÇÃO - Otimizando..."

    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan event:cache || true

    # Compilar assets
    if [ -f package.json ] && [ -d node_modules ]; then
        log_info "Compilando assets..."
        npm run build || echo "Build ignorado"
    fi

    log_info "✅ Otimização concluída"
else
    log_step "Ambiente DESENVOLVIMENTO - Pulando otimizações"
fi

# ============================================
# 10. VERIFICAR CONEXÃO FINAL
# ============================================
log_step "Verificando conexão final..."

if php artisan db:show > /dev/null 2>&1; then
    log_info "✅ Conexão com banco de dados OK"
    
    # Mostra status das migrações
    MIGRATIONS_DONE=$(php artisan migrate:status 2>/dev/null | grep -c "\[X\]" || echo "0")
    log_info "📊 Migrações executadas: $MIGRATIONS_DONE"
else
    log_warn "⚠️ Conexão com banco de dados falhou"
fi

# ============================================
# 11. RESUMO FINAL
# ============================================
echo ""
echo "============================================="
echo "  🚀 SM Componentes - Aplicação iniciada"
echo "============================================="
echo "  🌐 URL: $(grep ^APP_URL .env | cut -d '=' -f2- | tr -d '\r' || echo 'Não definida')"
echo "  🔧 Ambiente: $(grep ^APP_ENV .env | cut -d '=' -f2- | tr -d '\r' || echo 'Não definido')"
echo "  🐘 PHP: $(php -r 'echo PHP_VERSION;')"
echo "  📦 Laravel: $(php artisan --version | cut -d' ' -f2 || echo 'N/A')"
echo "  📦 Node: $(node -v 2>/dev/null || echo 'N/A')"
echo "  📦 NPM: $(npm -v 2>/dev/null || echo 'N/A')"
echo "  🗄️  Banco: $(grep ^DB_CONNECTION .env | cut -d '=' -f2- | tr -d '\r' || echo 'N/A')"
echo "  📊 Migrações: $(php artisan migrate:status 2>/dev/null | grep -c '\[X\]' || echo '0') executadas"
echo "============================================="
echo ""

exec "$@"