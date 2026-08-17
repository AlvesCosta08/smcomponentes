#!/bin/bash
set -e

# Função para verificar se o MySQL está pronto
wait_for_mysql() {
    echo "Aguardando MySQL iniciar..."
    local max_attempts=30
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if php -r "try { new PDO('mysql:host=db;dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'Conectado!'; exit(0); } catch(PDOException \$e) { exit(1); }" 2>/dev/null; then
            echo "MySQL está pronto!"
            return 0
        fi
        attempt=$((attempt+1))
        echo "MySQL ainda não está pronto... Aguardando 2 segundos (tentativa $attempt/$max_attempts)"
        sleep 2
    done
    
    echo "Erro: MySQL não ficou pronto após $max_attempts tentativas"
    return 1
}

# Aguarda o banco de dados
wait_for_mysql

# Gera a chave da aplicação se não existir
if [ ! -f .env ]; then
    echo "Criando arquivo .env..."
    cp .env.example .env
fi

# Verifica se APP_KEY está definida
if ! grep -q "^APP_KEY=" .env || [ -z "$(grep "^APP_KEY=" .env | cut -d '=' -f2)" ]; then
    echo "Gerando APP_KEY..."
    php artisan key:generate --force
fi

# Roda as migrations e seeds
echo "Executando migrations..."
php artisan migrate --force || echo "Erro nas migrations, continuando..."

echo "Executando seeds..."
php artisan db:seed --force || echo "Erro nos seeds, continuando..."

# Otimiza para produção
echo "Otimizando aplicação..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
php artisan event:cache || true

echo "========================================="
echo "✅ Aplicação SMComponentes está pronta!"
echo "📊 Banco: ${DB_DATABASE}"
echo "🌐 URL: http://localhost:8080"
echo "========================================="

# Executa o comando passado (padrão: apache2-foreground)
exec "$@"