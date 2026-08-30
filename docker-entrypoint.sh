#!/bin/sh
set -e

# Se não existir .env, copia do .env.example e gera chave
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Executa o comando passado (CMD)
exec "$@"