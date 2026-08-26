#!/bin/bash

echo "📊 Status dos containers:"
docker-compose ps

echo ""
echo "📈 Uso de recursos:"
docker stats --no-stream

echo ""
echo "🗄️  Verificando banco de dados..."
mysql -h 127.0.0.1 -P 3307 -uroot -proot123 -e "SHOW DATABASES;" 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ MySQL está respondendo!"
else
    echo "⚠️  MySQL não está acessível"
fi
