#!/bin/bash

# Cores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}🚀 Executando Testes - Projeto SM Componentes${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"

# Executar testes e filtrar warnings
vendor/bin/phpunit "$@" --testdox --no-output 2>&1 | \
  grep -v "Warning" | \
  grep -v "Ambiguous" | \
  grep -v "Skipping" | \
  grep -v "Runtime:" | \
  grep -v "Configuration:" | \
  grep -v "✅ Coluna" | \
  grep -v "Deprecations" | \
  grep -E "^(✔|✘|↩|Banner|Categoria|Pedido|Produto|User|Wishlist|Tests:|OK|FAILURES|ERRORS|Time:|Memory:)"

echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}✅ Testes concluídos!${NC}"
