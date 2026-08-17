#!/bin/bash
# create-modern-dashboard.sh

echo "📁 CRIANDO DASHBOARD MODERNO DO CLIENTE"

# 1. Criar diretório se não existir
mkdir -p resources/views/cliente

# 2. Criar o arquivo completo
cat > resources/views/cliente/dashboard.blade.php << 'EOF'
{{-- CÓDIGO COMPLETO ACIMA --}}
EOF

# 3. Verificar se foi criado
if [ -f "resources/views/cliente/dashboard.blade.php" ]; then
    echo "✅ Dashboard criado com sucesso!"
    echo "📁 Arquivo: resources/views/cliente/dashboard.blade.php"
else
    echo "❌ Erro ao criar o arquivo!"
    exit 1
fi

# 4. Limpar cache de views
echo "🧹 Limpando cache de views..."
docker exec -it smcomponentes_app bash -c "php artisan view:clear"

echo ""
echo "✅ DASHBOARD CRIADO COM SUCESSO!"
echo "================================================"
echo "🌐 Acesse: http://localhost:8080/cliente/dashboard"