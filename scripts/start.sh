#!/bin/bash

echo "🚀 Iniciando containers..."
docker-compose up -d

echo "📊 Status:"
docker-compose ps

echo ""
echo "🌐 Acesse:"
echo "   Aplicação: http://localhost:8000 (php artisan serve)"
echo "   phpMyAdmin: http://localhost:8081"
