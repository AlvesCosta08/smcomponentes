#!/bin/bash
set -e

# Função para verificar se o MySQL está pronto
wait_for_mysql() {
    echo "Aguardando MySQL iniciar..."
    while ! php -r "try { new PDO('mysql:host=db;dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'Conectado!'; } catch(PDOException \$e) { exit(1); }" 2>/dev/null; do
        echo "MySQL ainda não está pronto... Aguardando 2 segundos"
        sleep 2
    done
    echo "MySQL está pronto!"
}

# Aguarda o banco de dados
wait_for_mysql

# Gera a chave da aplicação se não existir
if [ ! -f .env ]; then
    echo "Criando arquivo .env..."
    cp .env.example .env
    php artisan key:generate
fi

# Roda as migrations e seeds
echo "Executando migrations..."
php artisan migrate --force

echo "Executando seeds..."
php artisan db:seed --force

# Otimiza para produção
echo "Otimizando aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Remove cache de desenvolvimento
php artisan optimize:clear || true

echo "Aplicação pronta!"

# Executa o comando passado (padrão: apache2-foreground)
exec "$@"