#!/bin/bash

echo "🔄 Reconstruindo ambiente do zero..."

# Parar e remover containers
echo "⏹️  Parando containers..."
docker-compose down -v

# Limpar cache Docker
echo "🧹 Limpando cache Docker..."
docker system prune -a -f
docker volume prune -f

# Subir novamente
echo "🐳 Subindo containers..."
docker-compose up -d

# Aguardar
echo "⏳ Aguardando inicialização..."
sleep 10

# Reconfigurar
echo "🚀 Executando setup..."
./setup.sh

echo "✅ RECONSTRUÇÃO CONCLUÍDA!"
