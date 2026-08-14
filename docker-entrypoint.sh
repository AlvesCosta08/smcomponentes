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
# FUNÇÃO PARA AGUARDAR BANCO DE DADOS
# ============================================
wait_for_db() {
    log_step "Aguardando banco de dados..."

    # Verifica variáveis de conexão
    DB_CONNECTION=$(grep ^DB_CONNECTION .env | cut -d '=' -f2 | tr -d '\r' | xargs)
    DB_HOST=$(grep ^DB_HOST .env | cut -d '=' -f2 | tr -d '\r' | xargs)
    DB_PORT=$(grep ^DB_PORT .env | cut -d '=' -f2 | tr -d '\r' | xargs)
    DB_DATABASE=$(grep ^DB_DATABASE .env | cut -d '=' -f2 | tr -d '\r' | xargs)
    DB_USERNAME=$(grep ^DB_USERNAME .env | cut -d '=' -f2 | tr -d '\r' | xargs)
    DB_PASSWORD=$(grep ^DB_PASSWORD .env | cut -d '=' -f2 | tr -d '\r' | xargs)

    if [ -z "$DB_HOST" ] || [ -z "$DB_DATABASE" ]; then
        log_warn "Variáveis de banco não configuradas. Pulando verificação..."
        return 0
    fi

    # Se for PostgreSQL no Render, usa pg_isready
    if [[ "$DB_CONNECTION" == "pgsql" ]] || [[ "$DB_CONNECTION" == "postgres" ]] || [[ "$DB_CONNECTION" == "pgsql_pdo" ]]; then
        log_info "Testando conexão PostgreSQL: $DB_HOST:$DB_PORT"
        
        # Instala pg_isready se não existir
        if ! command -v pg_isready &> /dev/null; then
            log_warn "pg_isready não encontrado, usando fallback com PHP"
            # Fallback usando PHP PDO
            MAX_RETRIES=30
            RETRY=0
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
                    return 1
                fi
                log_warn "Aguardando PostgreSQL... (${RETRY}/${MAX_RETRIES})"
                sleep 2
            done
        else
            # Usa pg_isready
            MAX_RETRIES=30
            RETRY=0
            while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" > /dev/null 2>&1; do
                RETRY=$((RETRY + 1))
                if [ $RETRY -ge $MAX_RETRIES ]; then
                    log_error "Timeout ao conectar ao PostgreSQL"
                    return 1
                fi
                log_warn "Aguardando PostgreSQL... (${RETRY}/${MAX_RETRIES})"
                sleep 2
            done
        fi
        log_info "✅ PostgreSQL conectado!"
        return 0
    fi

    # Para MySQL
    if [[ "$DB_CONNECTION" == "mysql" ]] || [[ "$DB_CONNECTION" == "mysqli" ]] || [[ "$DB_CONNECTION" == "mysql_pdo" ]]; then
        log_info "Testando conexão MySQL: $DB_HOST:$DB_PORT"
        MAX_RETRIES=30
        RETRY=0
        while ! mysqladmin ping -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" --silent > /dev/null 2>&1; do
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
# 1. VERIFICAR .ENV
# ============================================
log_step "Verificando arquivo .env..."

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        log_warn "Copiando .env.example para .env..."
        cp .env.example .env
    else
        log_warn "Criando .env vazio..."
        cat > .env << EOF
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=postgres
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
EOF
    fi
    log_info ".env criado"
else
    log_info ".env encontrado"
fi

# ============================================
# 2. GERAR APP_KEY
# ============================================
log_step "Verificando APP_KEY..."

if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
    log_warn "Gerando APP_KEY..."
    php artisan key:generate --force
    log_info "APP_KEY gerada"
else
    log_info "APP_KEY já definida"
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
wait_for_db || true  # Continua mesmo se falhar

# ============================================
# 5. LIMPAR CACHE
# ============================================
log_step "Limpando cache..."

php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# ============================================
# 6. RODAR MIGRATIONS
# ============================================
log_step "Rodando migrations..."

# Tenta conectar ao banco
if php artisan db:show > /dev/null 2>&1; then
    log_info "✅ Banco conectado. Executando migrations..."
    php artisan migrate --force || log_warn "⚠️ Migrations falharam"
    log_info "Migrations concluídas"
else
    log_warn "⚠️ Banco não conectado. Pulando migrations."
fi

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
echo "  🌐 URL: $(grep ^APP_URL .env | cut -d '=' -f2 | tr -d '\r' || echo 'Não definida')"
echo "  🔧 Ambiente: $(grep ^APP_ENV .env | cut -d '=' -f2 | tr -d '\r' || echo 'Não definido')"
echo "  🐘 PHP: $(php -r 'echo PHP_VERSION;')"
echo "  📦 Laravel: $(php artisan --version | cut -d' ' -f2 || echo 'N/A')"
echo "  📦 Node: $(node -v 2>/dev/null || echo 'N/A')"
echo "  📦 NPM: $(npm -v 2>/dev/null || echo 'N/A')"
echo "  🗄️  Banco: $(grep ^DB_CONNECTION .env | cut -d '=' -f2 | tr -d '\r' || echo 'N/A')"
echo "============================================="
echo ""

exec "$@"