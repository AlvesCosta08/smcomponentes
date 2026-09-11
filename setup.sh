#!/usr/bin/env bash
set -euo pipefail

# =========================================================
# CONFIGURAÇÕES
# =========================================================
MYSQL_CONTAINER="sm_mysql"
VOLUME_NAME="smcomponentes_mysql_data"
DB_HOST="127.0.0.1"
DB_PORT="3306"
DB_DATABASE="smcomponentes"
DB_USERNAME="alves"
DB_PASSWORD="alves123A"

# =========================================================
# CORES
# =========================================================
GREEN="\033[0;32m"; YELLOW="\033[1;33m"; RED="\033[0;31m"; BLUE="\033[0;34m"; NC="\033[0m"
log()  { echo -e "${BLUE}▶ $1${NC}"; }
ok()   { echo -e "${GREEN}✔ $1${NC}"; }
warn() { echo -e "${YELLOW}⚠ $1${NC}"; }
err()  { echo -e "${RED}✘ $1${NC}"; }

# =========================================================
# 1. LIMPEZA DO AMBIENTE
# =========================================================
log "Limpando containers do projeto..."
docker rm -f sm_mysql sm_phpmyadmin 2>/dev/null || true
ok "Containers removidos."

log "Removendo volume do banco (${VOLUME_NAME})..."
docker volume rm "${VOLUME_NAME}" 2>/dev/null || true
ok "Volume removido."

# =========================================================
# 2. SUBIR INFRA
# =========================================================
log "Subindo MySQL + phpMyAdmin..."
docker compose up -d --force-recreate

log "Aguardando MySQL ficar saudável (healthcheck)..."
MAX_TRIES=60; TRIES=0
until [ "$(docker inspect -f '{{.State.Health.Status}}' "${MYSQL_CONTAINER}" 2>/dev/null || echo 'starting')" = "healthy" ]; do
  TRIES=$((TRIES+1))
  if [ "$TRIES" -ge "$MAX_TRIES" ]; then
    err "MySQL não ficou saudável. Logs:"
    docker logs "${MYSQL_CONTAINER}" --tail 50
    exit 1
  fi
  printf "."; sleep 2
done
echo ""
ok "Healthcheck passou."

# =========================================================
# 3. ESPERA ATIVA: TESTAR CONEXÃO REAL VIA PHP/PDO
# =========================================================
log "Testando conexão real via PDO (pode levar alguns segundos)..."
MAX_PDO=30; COUNT=0
until php -r "
  try {
    new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');
    exit(0);
  } catch (Exception \$e) { exit(1); }
" 2>/dev/null; do
  COUNT=$((COUNT+1))
  if [ "$COUNT" -ge "$MAX_PDO" ]; then
    err "Não foi possível conectar via PDO após ${MAX_PDO} tentativas."
    docker logs "${MYSQL_CONTAINER}" --tail 30
    exit 1
  fi
  printf "·"; sleep 2
done
echo ""
ok "Conexão PDO estabelecida com sucesso!"

# =========================================================
# 4. PREPARAR LARAVEL
# =========================================================
if [ ! -f ".env" ]; then
  warn ".env não encontrado. Copiando de .env.example..."
  cp .env.example .env
fi

log "Limpando caches do Laravel..."
php artisan config:clear  >/dev/null 2>&1 || true
php artisan cache:clear   >/dev/null 2>&1 || true
php artisan route:clear   >/dev/null 2>&1 || true
php artisan view:clear    >/dev/null 2>&1 || true
ok "Caches limpos."

if ! grep -q "^APP_KEY=base64:" .env; then
  log "Gerando APP_KEY..."
  php artisan key:generate --force
  ok "APP_KEY gerada."
else
  ok "APP_KEY já existe."
fi

# =========================================================
# 5. MIGRATIONS + SEEDERS
# =========================================================
log "Rodando migrations (fresh)..."
php artisan migrate:fresh --force
ok "Migrations concluídas."

log "Rodando seeders..."
php artisan db:seed --force
ok "Seeders concluídos."

# =========================================================
# 6. RESUMO
# =========================================================
echo ""
echo -e "${GREEN}=========================================${NC}"
echo -e "${GREEN} ✅  TUDO PRONTO!${NC}"
echo -e "${GREEN}=========================================${NC}"
echo -e " 🐬  MySQL:        ${DB_HOST}:${DB_PORT}"
echo -e " 🗄   Database:     ${DB_DATABASE}"
echo -e " 👤  User:         ${DB_USERNAME}"
echo -e " 🔑  Password:     ${DB_PASSWORD}"
echo -e " 🌐  phpMyAdmin:   http://localhost:8081"
echo -e " 🚀  Laravel:      php artisan serve --port=8080"
echo -e "${GREEN}=========================================${NC}"