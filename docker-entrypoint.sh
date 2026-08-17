#!/bin/bash
set -e

# Função para iniciar e configurar o MySQL
start_mysql() {
    echo "🔧 Iniciando MySQL..."
    
    if [ ! -d "/var/lib/mysql/mysql" ]; then
        echo "📦 Inicializando banco de dados MySQL..."
        mysqld --initialize-insecure --user=mysql --datadir=/var/lib/mysql 2>/dev/null || true
    fi
    
    mysqld --user=mysql --datadir=/var/lib/mysql &
    
    echo "⏳ Aguardando MySQL iniciar..."
    local max_attempts=60
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if mysqladmin ping -h 127.0.0.1 -u root --silent 2>/dev/null; then
            echo "✅ MySQL iniciado com sucesso!"
            
            if [ ! -f "/var/lib/mysql/.configured" ]; then
                echo "⚙️  Configurando MySQL..."
                
                mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root123';" 2>/dev/null || true
                mysql -u root -proot123 -e "CREATE DATABASE IF NOT EXISTS smcomponentes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
                mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'smuser'@'localhost' IDENTIFIED BY 'smuser123';" 2>/dev/null || true
                mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'smuser'@'127.0.0.1' IDENTIFIED BY 'smuser123';" 2>/dev/null || true
                mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'smuser'@'%' IDENTIFIED BY 'smuser123';" 2>/dev/null || true
                mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON smcomponentes.* TO 'smuser'@'localhost';" 2>/dev/null || true
                mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON smcomponentes.* TO 'smuser'@'127.0.0.1';" 2>/dev/null || true
                mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON smcomponentes.* TO 'smuser'@'%';" 2>/dev/null || true
                mysql -u root -proot123 -e "FLUSH PRIVILEGES;" 2>/dev/null || true
                
                touch /var/lib/mysql/.configured
                echo "✅ MySQL configurado!"
                
                echo "========================================="
                echo "📊 DADOS DO BANCO DE DADOS:"
                echo "   Banco: smcomponentes"
                echo "   Host: 127.0.0.1"
                echo "   Porta: 3306"
                echo "   Usuário: smuser"
                echo "   Senha: smuser123"
                echo "   Root: root"
                echo "   Senha Root: root123"
                echo "========================================="
            fi
            return 0
        fi
        attempt=$((attempt+1))
        echo "⏳ Aguardando MySQL... (tentativa $attempt/$max_attempts)"
        sleep 1
    done
    
    echo "❌ Erro: MySQL não iniciou!"
    return 1
}

# Função para configurar o .env (se não existir)
configure_env() {
    echo "📝 Verificando .env..."
    
    if [ ! -f .env ]; then
        echo "⚠️  .env não encontrado, criando..."
        cat > .env << 'EOF'
APP_NAME=SMComponentes
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8080

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smcomponentes
DB_USERNAME=smuser
DB_PASSWORD=smuser123

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
EOF
        echo "✅ .env criado!"
    fi
    
    # Configura APP_URL
    if [ -n "$RENDER_EXTERNAL_URL" ]; then
        sed -i "s|^APP_URL=.*|APP_URL=$RENDER_EXTERNAL_URL|g" .env
    fi
    
    echo "✅ .env verificado!"
}

# Função principal
start_app() {
    echo "========================================="
    echo "🚀 INICIANDO SMComponentes - All-in-One"
    echo "========================================="
    
    start_mysql
    sleep 3
    configure_env
    
    if ! grep -q "^APP_KEY=" .env || [ -z "$(grep "^APP_KEY=" .env | cut -d '=' -f2)" ]; then
        echo "🔑 Gerando APP_KEY..."
        php artisan key:generate --force
    fi
    
    echo "🔍 Testando conexão com banco de dados..."
    if php -r "new PDO('mysql:host=127.0.0.1;dbname=smcomponentes', 'smuser', 'smuser123'); echo 'Conectado!';" 2>/dev/null; then
        echo "✅ Conexão com banco OK!"
    else
        echo "⚠️  Erro ao conectar com banco"
    fi
    
    echo "📦 Executando migrations..."
    php artisan migrate --force || echo "⚠️  Erro nas migrations"
    
    echo "🌱 Executando seeds..."
    php artisan db:seed --force || echo "⚠️  Erro nos seeds"
    
    php artisan storage:link --force || true
    
    echo "⚡ Otimizando aplicação..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    
    echo "========================================="
    echo "✅ APLICAÇÃO PRONTA!"
    echo "🌐 URL: http://localhost:8080"
    echo "========================================="
    
    exec apache2-foreground
}

if [ "$1" = "shell" ]; then
    exec /bin/bash
elif [ "$1" = "mysql" ]; then
    exec mysql -u root -proot123
else
    start_app
fi