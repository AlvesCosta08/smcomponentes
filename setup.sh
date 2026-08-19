#!/bin/bash

echo "🚀 Configurando Laravel 13 + MySQL Docker"

# 1. Subir containers
echo "🐳 Subindo MySQL e phpMyAdmin..."
docker-compose up -d

# 2. Aguardar MySQL iniciar
echo "⏳ Aguardando MySQL iniciar..."
sleep 10

# 3. Verificar containers
echo "📊 Status dos containers:"
docker-compose ps

# 4. Verificar conexão com MySQL
echo "🔍 Testando conexão com MySQL..."
if mysql -h 127.0.0.1 -P 3307 -uroot -proot123 -e "SELECT 1" 2>/dev/null; then
    echo "✅ MySQL conectado com sucesso!"
else
    echo "⚠️  Erro ao conectar ao MySQL. Verifique as configurações."
    exit 1
fi

# 5. Verificar se o banco foi criado
echo "🔍 Verificando banco de dados..."
mysql -h 127.0.0.1 -P 3307 -uroot -proot123 -e "SHOW DATABASES;" | grep smcomponentes

if [ $? -eq 0 ]; then
    echo "✅ Banco de dados 'smcomponentes' criado!"
else
    echo "⚠️  Banco de dados não encontrado. Criando..."
    mysql -h 127.0.0.1 -P 3307 -uroot -proot123 -e "CREATE DATABASE IF NOT EXISTS smcomponentes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
fi

# 6. Configurar .env
echo "🔧 Configurando .env..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Atualizar .env com configurações do Docker
sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=3307/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=smcomponentes/' .env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=root/' .env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=root123/' .env
sed -i 's/APP_URL=.*/APP_URL=http:\/\/localhost:8000/' .env

# 7. Gerar APP_KEY
echo "🔑 Gerando APP_KEY..."
php artisan key:generate

# 8. Criar storage link
echo "🔗 Criando storage link..."
php artisan storage:link

# 9. Criar diretórios
echo "📁 Criando diretórios..."
mkdir -p storage/app/public/produtos
mkdir -p public/images

# 10. Criar placeholder
echo "🖼️  Criando placeholder..."
cat > public/images/produto-placeholder.jpg << 'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300">
  <rect width="300" height="300" fill="#f8f9fa"/>
  <rect x="50" y="50" width="200" height="200" fill="#dee2e6" rx="10"/>
  <text x="50%" y="55%" text-anchor="middle" dy=".3em" font-family="Arial" font-size="24" fill="#6c757d">📷</text>
  <text x="50%" y="70%" text-anchor="middle" dy=".3em" font-family="Arial" font-size="16" fill="#adb5bd">Sem Imagem</text>
</svg>
SVG

# 11. Executar migrações
echo "📦 Executando migrações..."
php artisan migrate --force

# 12. Limpar cache
echo "🧹 Limpando cache..."
php artisan optimize:clear

# 13. Verificar ambiente
echo "📊 Verificando ambiente..."
php artisan about

echo ""
echo "✅ CONFIGURAÇÃO CONCLUÍDA!"
echo ""
echo "🌐 Acesse a aplicação: http://localhost:8000"
echo "🗄️  phpMyAdmin: http://localhost:8081"
echo "   Servidor: mysql"
echo "   Usuário: root"
echo "   Senha: root123"
echo ""
echo "🔑 Credenciais do banco:"
echo "   Host: 127.0.0.1"
echo "   Porta: 3307"
echo "   Usuário: root"
echo "   Senha: root123"
echo "   Banco: smcomponentes"
echo ""
echo "📋 Comandos úteis:"
echo "   docker-compose ps          - Ver containers"
echo "   docker-compose logs -f     - Ver logs"
echo "   docker-compose down        - Parar containers"
echo "   php artisan serve          - Iniciar servidor Laravel"
