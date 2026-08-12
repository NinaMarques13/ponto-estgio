# 📋 Testes de Estagiários - Guia de Execução

## 📌 Sobre os Testes

Este arquivo de testes (`tests/Feature/EstagiariosTest.php`) valida o fluxo completo de:
- ✅ Cadastro de estagiários
- ✅ Listagem/Exportação de registros
- ✅ Registro de ponto (entrada/saída)
- ✅ Atualização de dados
- ✅ QR Code processing
- ✅ Filtros e pesquisa

## 🚀 Executar Todos os Testes

```bash
php artisan test
```

## 🎯 Executar Testes Específicos

### Testes de Cadastro
```bash
php artisan test tests/Feature/EstagiariosTest.php --filter "cadastro"
```

### Testes de Validação
```bash
php artisan test tests/Feature/EstagiariosTest.php --filter "validacao"
```

### Testes de Listagem/Exportação
```bash
php artisan test tests/Feature/EstagiariosTest.php --filter "listar"
```

### Testes de Registro de Ponto
```bash
php artisan test tests/Feature/EstagiariosTest.php --filter "ponto"
```

### Testes de Fluxo Completo
```bash
php artisan test tests/Feature/EstagiariosTest.php --filter "fluxo"
```

### Testes de QR Code
```bash
php artisan test tests/Feature/EstagiariosTest.php --filter "qrcode"
```

## 📊 Executar com Relatório de Cobertura

```bash
php artisan test --coverage
```

## 📈 Executar com Cobertura Detalhada

```bash
php artisan test --coverage --coverage-html=coverage
```

Após executar, abra `coverage/index.html` no navegador para ver o relatório detalhado.

## 🧪 Testes Incluídos

### 1. Cadastro (8 testes)
- ✅ Criar novo estagiário
- ✅ Validação de campos obrigatórios
- ✅ Validação de email
- ✅ Validação de matrícula duplicada
- ✅ Validação de telefone duplicado
- ✅ Validação de email duplicado
- ✅ Atualizar cadastro
- ✅ Desativar estagiário

### 2. Listagem/Exportação (6 testes)
- ✅ Listar estagiários cadastrados
- ✅ Listar por data
- ✅ Listar por mês
- ✅ Listar por ano
- ✅ Filtrar por estagiário
- ✅ Filtrar por motivo

### 3. Registro de Ponto (3 testes)
- ✅ Registrar entrada
- ✅ Registrar saída após entrada
- ✅ Erro quando estagiário não encontrado

### 4. Atualização de Ponto (2 testes)
- ✅ Atualizar horário de entrada
- ✅ Atualizar motivo do registro

### 5. Pesquisa (1 teste)
- ✅ Pesquisar estagiários

### 6. QR Code (2 testes)
- ✅ Processar QR Code válido
- ✅ Processar QR Code inválido

### 7. Fluxo Completo (2 testes)
- ✅ Cadastro → Registro de ponto completo
- ✅ Cadastro → Atualização → Verificação

## 🔧 Troubleshooting

### Erro: "Database does not exist"
```bash
php artisan migrate:fresh --env=testing
```

### Erro: "Factory not found"
Certifique-se de que as factories estão em `database/factories/`:
- `EstagiarioFactory.php` ✅
- `RegistroPontoFactory.php` ✅

### Erro: "Class not found"
Execute composer autoload:
```bash
composer dump-autoload
```

## 📝 Adicionar Novos Testes

Para adicionar novos testes, siga o padrão:

```php
/** @test */
public function test_descricao_do_teste()
{
    // Arrange (Preparar dados)
    $estagiario = Estagiario::factory()->create();
    
    // Act (Executar ação)
    $response = $this->postJson('/rota', $dados);
    
    // Assert (Verificar resultado)
    $response->assertStatus(200);
    $this->assertDatabaseHas('tabela', $dados);
}
```

## 📚 Referências

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/)
- [Laravel Factories](https://laravel.com/docs/eloquent-factories)
