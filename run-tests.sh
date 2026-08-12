#!/bin/bash

# Script para executar testes do sistema de ponto de estagiários
# Uso: ./run-tests.sh [opcao]

set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir com cor
print_section() {
    echo -e "${BLUE}▶ $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Verificar se o comando existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Verificar se PHP está disponível
if ! command_exists php; then
    print_error "PHP não está instalado ou não está no PATH"
    exit 1
fi

print_section "Iniciando Testes do Sistema de Ponto de Estagiários"
echo ""

# Opção padrão
OPTION="${1:-all}"

case "$OPTION" in
    all)
        print_section "Executando TODOS os testes..."
        php artisan test tests/Feature/EstagiariosTest.php --verbose
        ;;
    
    cadastro)
        print_section "Executando testes de CADASTRO..."
        php artisan test tests/Feature/EstagiariosTest.php --filter "cadastro"
        ;;
    
    validacao)
        print_section "Executando testes de VALIDAÇÃO..."
        php artisan test tests/Feature/EstagiariosTest.php --filter "validacao"
        ;;
    
    listagem)
        print_section "Executando testes de LISTAGEM/EXPORTAÇÃO..."
        php artisan test tests/Feature/EstagiariosTest.php --filter "listar"
        ;;
    
    ponto)
        print_section "Executando testes de REGISTRO DE PONTO..."
        php artisan test tests/Feature/EstagiariosTest.php --filter "ponto"
        ;;
    
    atualizacao)
        print_section "Executando testes de ATUALIZAÇÃO..."
        php artisan test tests/Feature/EstagiariosTest.php --filter "atualizar"
        ;;
    
    qrcode)
        print_section "Executando testes de QR CODE..."
        php artisan test tests/Feature/EstagiariosTest.php --filter "qrcode"
        ;;
    
    fluxo)
        print_section "Executando testes de FLUXO COMPLETO..."
        php artisan test tests/Feature/EstagiariosTest.php --filter "fluxo"
        ;;
    
    coverage)
        print_section "Executando testes COM RELATÓRIO DE COBERTURA..."
        php artisan test tests/Feature/EstagiariosTest.php --coverage
        ;;
    
    coverage-html)
        print_section "Gerando RELATÓRIO HTML de cobertura..."
        php artisan test tests/Feature/EstagiariosTest.php --coverage-html=coverage
        print_success "Relatório gerado em: coverage/index.html"
        ;;
    
    refresh)
        print_section "Limpando database e remigrantando..."
        php artisan migrate:fresh --env=testing
        print_success "Database renovado!"
        ;;
    
    help|--help|-h)
        echo "Opções disponíveis:"
        echo ""
        echo "  all           - Executar todos os 30 testes (padrão)"
        echo "  cadastro      - Executar apenas testes de cadastro"
        echo "  validacao     - Executar apenas testes de validação"
        echo "  listagem      - Executar apenas testes de listagem/exportação"
        echo "  ponto         - Executar apenas testes de registro de ponto"
        echo "  atualizacao   - Executar apenas testes de atualização"
        echo "  qrcode        - Executar apenas testes de QR Code"
        echo "  fluxo         - Executar apenas testes de fluxo completo"
        echo "  coverage      - Executar com relatório de cobertura no terminal"
        echo "  coverage-html - Gerar relatório HTML de cobertura"
        echo "  refresh       - Renovar database para testes"
        echo "  help          - Mostrar esta mensagem de ajuda"
        echo ""
        echo "Exemplos:"
        echo "  ./run-tests.sh                 # Rodar todos os testes"
        echo "  ./run-tests.sh cadastro        # Rodar testes de cadastro"
        echo "  ./run-tests.sh coverage-html   # Gerar relatório HTML"
        ;;
    
    *)
        print_error "Opção desconhecida: $OPTION"
        echo "Use './run-tests.sh help' para ver opções disponíveis"
        exit 1
        ;;
esac

EXIT_CODE=$?

echo ""
if [ $EXIT_CODE -eq 0 ]; then
    print_success "Testes executados com sucesso!"
else
    print_error "Alguns testes falharam (código de saída: $EXIT_CODE)"
fi

exit $EXIT_CODE
