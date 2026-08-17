#!/bin/bash
set -e

# Função para iniciar e configurar o MySQL
start_mysql() {
    echo "🔧 Iniciando MySQL..."
    
    # Inicializa o MySQL se for primeira execução
    if [ ! -d "/var/lib/mysql/mysql" ]; then
        echo "📦 Inicializando banco de dados MySQL..."
        mysqld --initialize-insecure --user=mysql --datadir=/var/lib/mysql 2>/dev/null || true
    fi
    
    # Inicia o MySQL em background
    mysqld --user=mysql --datadir=/var/lib/mysql &
    
    # Aguarda o MySQL iniciar
    echo "⏳ Aguardando MySQL iniciar..."
    local max_attempts=60
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if mysqladmin ping -h 127.0.0.1 -u root --silent 2>/dev/null; then
            echo "✅ MySQL iniciado com sucesso!"
            
            # Configura o MySQL se for primeira execução
            if [ ! -f "/var/lib/mysql/.configured" ]; then
                echo "⚙️  Configurando MySQL..."
                
                # Define senha do root
                mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root123';"
                
                # Cria banco de dados
                mysql -u root -proot123 -e "CREATE DATABASE IF NOT EXISTS smcomponentes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
                
                # Cria usuário e dá permissões
                mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'smuser'@'localhost' IDENTIFIED BY 'smuser123';"
                mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'smuser'@'127.0.0.1' IDENTIFIED BY 'smuser123';"
                mysql -u root -proot123 -e "CREATE USER IF NOT EXISTS 'smuser'@'%' IDENTIFIED BY 'smuser123';"
                mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON smcomponentes.* TO 'smuser'@'localhost';"
                mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON smcomponentes.* TO 'smuser'@'127.0.0.1';"
                mysql -u root -proot123 -e "GRANT ALL PRIVILEGES ON smcomponentes.* TO 'smuser'@'%';"
                mysql -u root -proot123 -e "FLUSH PRIVILEGES;"
                
                touch /var/lib/mysql/.configured
                echo "✅ MySQL configurado!"
                
                # Mostra informações do banco
                echo "========================================="
                echo "📊 Banco de dados configurado:"
                echo "   Banco: smcomponentes"
                echo "   Usuário: root"
                echo "   Senha: root123"
                echo "   Usuário app: smuser"
                echo "   Senha app: smuser123"
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

# Função principal
start_app() {
    echo "========================================="
    echo "🚀 INICIANDO SMComponentes - All-in-One"
    echo "========================================="
    
    # Inicia MySQL
    start_mysql
    
    # Aguarda MySQL ficar 100% pronto
    sleep 3
    
    # Configura .env para usar MySQL local
    echo "📝 Configurando .env..."
    
    # Atualiza ou cria o .env
    if [ ! -f .env ]; then
        cp .env.example .env
    fi
    
    # Configura conexão com banco
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/g' .env
    sed -i 's/^DB_HOST=.*/DB_HOST=127.0.0.1/g' .env
    sed -i 's/^DB_PORT=.*/DB_PORT=3306/g' .env
    sed -i 's/^DB_DATABASE=.*/DB_DATABASE=smcomponentes/g' .env
    sed -i 's/^DB_USERNAME=.*/DB_USERNAME=smuser/g' .env
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=smuser123/g' .env
    
    # Configura APP_URL
    if [ -n "$RENDER_EXTERNAL_URL" ]; then
        sed -i "s|^APP_URL=.*|APP_URL=$RENDER_EXTERNAL_URL|g" .env
    fi
    
    # Gera APP_KEY se necessário
    if ! grep -q "^APP_KEY=" .env || [ -z "$(grep "^APP_KEY=" .env | cut -d '=' -f2)" ]; then
        echo "🔑 Gerando APP_KEY..."
        php artisan key:generate --force
    fi
    
    # Verifica conexão com banco
    echo "🔍 Testando conexão com banco de dados..."
    if php artisan migrate:status 2>/dev/null | grep -q "Migration table"; then
        echo "✅ Conexão com banco OK!"
    else
        echo "⚠️  Tabela de migrations não encontrada, criando..."
        php artisan migrate:install --force || true
    fi
    
    # Roda migrations e seeds
    echo "📦 Executando migrations..."
    php artisan migrate --force || echo "⚠️  Erro nas migrations (pode ser normal na primeira execução)"
    
    echo "🌱 Executando seeds..."
    php artisan db:seed --force || echo "⚠️  Erro nos seeds"
    
    # Cria link simbólico do storage
    php artisan storage:link --force || true
    
    # Otimiza
    echo "⚡ Otimizando aplicação..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    php artisan event:cache || true
    
    echo "========================================="
    echo "✅ APLICAÇÃO PRONTA!"
    echo "🌐 URL: http://localhost:8080"
    echo "📊 MySQL: localhost:3306"
    echo "   Usuário: root"
    echo "   Senha: root123"
    echo "========================================="
    echo "📋 Logs:"
    echo "   - Aplicação: /var/www/storage/logs/laravel.log"
    echo "   - MySQL: /var/log/mysql/error.log"
    echo "========================================="
    
    # Inicia Apache em foreground
    exec apache2-foreground
}

# Comando para shell interativo
if [ "$1" = "shell" ]; then
    exec /bin/bash
else
    start_app
fi