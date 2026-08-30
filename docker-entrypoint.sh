#!/bin/sh
set -e

# Função para executar tarefas pós-instalação apenas na primeira execução
initialize_laravel() {
    # Cria .env se não existir
    if [ ! -f .env ]; then
        echo "Criando .env a partir do .env.example..."
        cp .env.example .env
        # Força algumas variáveis importantes (podem ser sobrescritas pelas do ambiente)
        echo "APP_STORAGE=/var/www/html/storage" >> .env
        echo "CACHE_DRIVER=file" >> .env
        echo "SESSION_DRIVER=file" >> .env
    fi

    # Gera APP_KEY se não definida
    if [ -z "$APP_KEY" ] && grep -q "^APP_KEY=$" .env; then
        echo "Gerando APP_KEY..."
        php artisan key:generate
    fi

    # Roda o autoload dump e package discovery (pós-instalação)
    echo "Executando post-autoload-dump..."
    composer run-script post-autoload-dump

    # (Opcional) Cria link simbólico do storage
    php artisan storage:link || true

    echo "Inicialização concluída."
}

# Verifica se o arquivo de lock de inicialização existe
if [ ! -f /var/www/html/.initialized ]; then
    initialize_laravel
    touch /var/www/html/.initialized
    echo "Inicialização concluída com sucesso."
fi

# Executa migrações se solicitado (após a inicialização)
if [ "$FORCE_MIGRATION" = "true" ]; then
    echo "Executando migrações..."
    php artisan migrate --force
fi

# Otimiza cache em produção (após todas as alterações)
if [ "$APP_ENV" = "production" ]; then
    echo "Otimizando cache para produção..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Executa o comando principal (CMD)
exec "$@"