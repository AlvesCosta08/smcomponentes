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
                mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'root123';" 2>/dev/null || true
                
                # Cria banco de dados
                mysql -u root -proot123 -e "CREATE DATABASE IF NOT EXISTS smcomponentes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
                
                # Cria usuário e dá permissões
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

# Função para configurar o .env
configure_env() {
    echo "📝 Configurando .env..."
    
    # Verifica se .env existe
    if [ ! -f .env ]; then
        if [ -f .env.example ]; then
            cp .env.example .env
        else
            touch .env
        fi
    fi
    
    # Configurações do banco
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
    
    # Configura APP_ENV
    sed -i 's/^APP_ENV=.*/APP_ENV=production/g' .env
    sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/g' .env
    
    echo "✅ .env configurado!"
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
    
    # Configura .env
    configure_env
    
    # Gera APP_KEY se necessário
    if ! grep -q "^APP_KEY=" .env || [ -z "$(grep "^APP_KEY=" .env | cut -d '=' -f2)" ]; then
        echo "🔑 Gerando APP_KEY..."
        php artisan key:generate --force
    fi
    
    # Verifica conexão com banco
    echo "🔍 Testando conexão com banco de dados..."
    
    # Testa a conexão
    if php -r "new PDO('mysql:host=127.0.0.1;dbname=smcomponentes', 'smuser', 'smuser123'); echo 'Conectado!';" 2>/dev/null; then
        echo "✅ Conexão com banco OK!"
    else
        echo "⚠️  Erro ao conectar com banco (pode ser normal na primeira execução)"
    fi
    
    # Roda migrations e seeds
    echo "📦 Executando migrations..."
    php artisan migrate --force || echo "⚠️  Erro nas migrations"
    
    echo "🌱 Executando seeds..."
    php artisan db:seed --force || echo "⚠️  Erro nos seeds"
    
    # Cria link simbólico do storage
    php artisan storage:link --force || true
    
    # Otimiza
    echo "⚡ Otimizando aplicação..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    
    echo "========================================="
    echo "✅ APLICAÇÃO PRONTA!"
    echo "🌐 URL: http://localhost:8080"
    echo "📊 MySQL: 127.0.0.1:3306"
    echo "   Usuário: smuser"
    echo "   Senha: smuser123"
    echo "   Root: root/root123"
    echo "========================================="
    
    # Inicia Apache em foreground
    exec apache2-foreground
}

# Comando para shell interativo
if [ "$1" = "shell" ]; then
    exec /bin/bash
elif [ "$1" = "mysql" ]; then
    exec mysql -u root -proot123
else
    start_app
fi