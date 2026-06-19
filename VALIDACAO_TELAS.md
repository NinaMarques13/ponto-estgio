# 🎨 Guia de Validação das Telas - Cadastro e Exportação

## 📋 Visão Geral

Este guia fornece instruções para validar manualmente as funcionalidades de **Cadastro** e **Exportação** no sistema de ponto de estagiários.

---

## 🏠 Tela de Cadastro (`/cadastro`)

### Localização
- **URL:** `http://ponto-estagio.pm.pr.gov.br/cadastro`
- **Autenticação:** Requer login de admin
- **Arquivo:** `resources/views/pages/principal/cadastro.blade.php`

### Componentes da Tela

#### 1. **Header com Título e Botão**
```html
<h5>Cadastro de Estagiários</h5>
<button>Cadastrar estagiário</button>
```
- ✅ Título deve estar visível
- ✅ Botão deve abrir modal de cadastro

#### 2. **Tabela de Estagiários Cadastrados**
```html
<table id="tabela-estagiarios-cadastrados">
    <thead>
        <tr>
            <th>id</th>
            <th>Nome</th>
            <th>CPF (Matrícula)</th>
            <th>Setor</th>
            <th>Telefone</th>
            <th>E-mail</th>
            <th>Ações</th>
        </tr>
    </thead>
</table>
```
- ✅ Tabela deve carregar dados via DataTables
- ✅ Deve mostrar lista de estagiários
- ✅ Deve ter paginação
- ✅ Deve ter busca

#### 3. **Modal: Cadastrar Novo Estagiário**
```
ID do Modal: #modalAdicionarEstagiario
```

**Campos do Formulário:**
```
┌─────────────────────────────────────┐
│  Cadastrar Novo Estagiário          │
├─────────────────────────────────────┤
│ Nome *                              │
│ [Texto]                             │
│                                     │
│ CPF (Matrícula) *                   │
│ [11 dígitos]                        │
│                                     │
│ Setor *                             │
│ [Texto]                             │
│                                     │
│ Telefone        │  E-mail           │
│ [11 dígitos]    │  [email@test.com] │
├─────────────────────────────────────┤
│ [Cancelar]              [Salvar]     │
└─────────────────────────────────────┘
```

### Validações Esperadas (Testes Automatizados)

| Validação | Comportamento | Status |
|-----------|---------------|--------|
| Nome vazio | Mostra erro | ✅ |
| CPF vazio | Mostra erro | ✅ |
| Setor vazio | Mostra erro | ✅ |
| Email inválido | Mostra erro | ✅ |
| CPF duplicado | Mostra erro | ✅ |
| Email duplicado | Mostra erro | ✅ |
| Telefone duplicado | Mostra erro | ✅ |
| Dados válidos | Salva no banco | ✅ |

### Ações Disponíveis na Tabela

#### 1. **Gerar QR Code** 🔷
- **Ícone:** QR Code
- **Ação:** Abre modal `#qrModalCadastro`
- **Função:** Exibe QR Code do estagiário
- **Teste:** Clicar no ícone QR Code

#### 2. **Editar Estagiário** ✏️
- **Ícone:** Lápis
- **Ação:** Abre modal de edição
- **Função:** Permite atualizar dados
- **Teste:** 
  ```
  1. Clicar em Editar
  2. Modificar Nome ou Setor
  3. Salvar
  4. Verificar atualização na tabela
  ```

#### 3. **Excluir Estagiário** 🗑️
- **Ícone:** Lixeira
- **Ação:** Desativa o estagiário
- **Função:** Remove da listagem ativa
- **Teste:**
  ```
  1. Clicar em Excluir
  2. Confirmar exclusão
  3. Verificar se desaparece da tabela
  ```

### Fluxo de Teste: Cadastro Completo

```
1. Acessar /cadastro
   ✅ Página carrega sem erros
   ✅ Tabela mostra estagiários

2. Clicar em "Cadastrar estagiário"
   ✅ Modal abre

3. Preencher formulário:
   Nome: "João Silva"
   CPF: "12345678901"
   Setor: "TI"
   Telefone: "11987654321"
   Email: "joao@example.com"

4. Clicar "Salvar"
   ✅ Modal fecha
   ✅ Novo registro aparece na tabela
   ✅ Mensagem de sucesso aparece

5. Testar validações:
   - Deixar campos em branco
   - Email inválido (ex: "abc")
   - CPF duplicado
   - Deve mostrar erro apropriado

6. Testar ações:
   - Clicar QR Code: exibe código
   - Clicar Editar: modifica e salva
   - Clicar Excluir: remove da lista
```

---

## 📊 Tela de Exportação (`/admin/export`)

### Localização
- **URL:** `http://ponto-estagio.pm.pr.gov.br/admin/export`
- **Autenticação:** Requer login de admin
- **Arquivo:** `resources/views/pages/principal/export.blade.php`

### Componentes da Tela

#### 1. **Painel de Filtros**
```
┌──────────────────────────────────────────────────────────┐
│                     FILTROS                              │
├──────────────────────────────────────────────────────────┤
│ Data        │ Mês        │ Ano           │ Motivo        │
│ [input]     │ [input]    │ [2000-2100]   │ [dropdown]    │
└──────────────────────────────────────────────────────────┘
```

**Campos de Filtro:**
- **Data:** Filtrar por data específica (YYYY-MM-DD)
- **Mês:** Filtrar por mês específico (YYYY-MM)
- **Ano:** Filtrar por ano (dropdown 2000-2100)
- **Motivo:** Filtrar por tipo de registro

**Opções de Motivo:**
```
- Todos
- Presente (Registros Completos)
- Em Andamento (Só Entrada)
- Falta
- Dispensa
- Folga
- Atestado
- Recesso
```

#### 2. **Cards de Resumo**
```
┌─────────┬──────────┬─────────┬──────────┬────────┬──────────┬────────┐
│Estagi.  │Registros │ Recesso │ Atestado │ Folga  │ Dispensa │ Falta  │
│         │          │         │          │        │          │        │
│   25    │    87    │   12    │    8     │  15    │    5     │   10   │
└─────────┴──────────┴─────────┴──────────┴────────┴──────────┴────────┘
```

- ✅ Números devem atualizar conforme filtros
- ✅ Cálculo deve ser em tempo real

#### 3. **Tabela de Registros (DataTables)**
```
┌──────┬───────────┬────────────┬────────┬─────────┬────────┬──────────┬─────────────────┐
│  ID  │   Nome    │  Matrícula │ Setor  │ Entrada │ Saída  │ Total    │ Observação      │
├──────┼───────────┼────────────┼────────┼─────────┼────────┼──────────┼─────────────────┤
│  1   │ João Silva│ 12345678901│  TI    │  08:00  │ 18:00  │  10h00m  │ Dia normal      │
│  2   │ Maria     │ 98765432109│  RH    │  09:00  │ 17:30  │  08h30m  │ Almoço estendido│
└──────┴───────────┴────────────┴────────┴─────────┴────────┴──────────┴─────────────────┘
```

**Colunas:**
- `Data` - Data do registro
- `Nome` - Nome do estagiário
- `Matrícula` - CPF/Matrícula
- `Setor` - Departamento
- `Entrada` - Hora de entrada
- `Saída` - Hora de saída
- `Total Horas` - Horas calculadas
- `Observação` - Observações do registro

#### 4. **Funcionalidades da Tabela**
- ✅ Paginação (10 registros por página)
- ✅ Busca em tempo real
- ✅ Ordenação por coluna
- ✅ Exportar para Excel (se implementado)

### Validações Esperadas (Testes Automatizados)

| Filtro | Comportamento | Status |
|--------|---------------|--------|
| Data válida | Mostra registros do dia | ✅ |
| Data inválida | Retorna vazio | ✅ |
| Mês válido | Mostra registros do mês | ✅ |
| Ano válido | Mostra registros do ano | ✅ |
| Filtro por estagiário | Mostra registros dele | ✅ |
| Filtro por motivo | Filtra por tipo | ✅ |
| Combinação de filtros | Aplica todos | ✅ |

### Fluxo de Teste: Exportação Completa

```
1. Acessar /admin/export
   ✅ Página carrega sem erros
   ✅ Tabela exibe registros
   ✅ Cards mostram resumo

2. Testar Filtro por Data:
   - Selecionar data específica
   - ✅ Tabela atualiza
   - ✅ Cards recalculam
   - ✅ Apenas registros do dia aparecem

3. Testar Filtro por Mês:
   - Selecionar mês/ano
   - ✅ Tabela mostra mês completo
   - ✅ Cards atualizam total do mês
   - ✅ 20+ registros aparecem (se houver)

4. Testar Filtro por Ano:
   - Selecionar ano diferente
   - ✅ Tabela mostra ano completo
   - ✅ Cards mostram resumo anual
   - ✅ Busca funciona

5. Testar Filtro por Motivo:
   - Selecionar "Falta"
   - ✅ Tabela mostra só faltas
   - ✅ Outros motivos desaparecem
   - Selecionar "Folga"
   - ✅ Tabela mostra só folgas

6. Testar Busca:
   - Digitar nome de estagiário
   - ✅ Tabela filtra em tempo real
   - ✅ Apenas registros relevantes aparecem

7. Testar Paginação:
   - Se > 10 registros
   - ✅ Botões "próxima" e "anterior" aparecem
   - ✅ Mudar de página funciona
   - ✅ Total de páginas correto

8. Testar Cálculo de Horas:
   - Entrada: 08:00, Saída: 18:00
   - ✅ Total deve ser 10h00m
   - Entrada: 09:30, Saída: 17:45
   - ✅ Total deve ser 08h15m

9. Testar Cards de Resumo:
   - Sem filtro: mostra totais gerais
   - Com filtro: recalcula
   - Comparar com contagem manual
   - ✅ Números devem estar corretos
```

---

## 🔄 Fluxo Integrado: Cadastro → Registro → Exportação

```
1. CADASTRO
   ├─ Criar novo estagiário "Carlos"
   └─ ✅ Salvo em estagiarios

2. REGISTRO DE PONTO
   ├─ Estagiário registra entrada
   ├─ Cria registro com hr_registro = hoje 08:00
   ├─ Estagiário registra saída
   ├─ Cria segundo registro com hr_registro = hoje 18:00
   └─ ✅ 2 registros em registro_ponto

3. EXPORTAÇÃO
   ├─ Acessar /admin/export
   ├─ Selecionar data de hoje
   ├─ ✅ Tabela exibe "Carlos" com entrada/saída
   ├─ ✅ Total Horas = 10h00m
   ├─ ✅ Card "Estagiários" = 1
   ├─ ✅ Card "Registros" = 2
   └─ ✅ Filtrar por "Presente" mostra o registro
```

---

## 🐛 Problemas Comuns e Soluções

| Problema | Causa | Solução |
|----------|-------|---------|
| Tabela não carrega | DataTables não inicializado | F5 para recarregar, verificar console |
| Filtros não funcionam | Banco vazio | Adicionar registros via POST |
| Cards com valores 0 | Sem registros no período | Criar registros de teste |
| Modal não fecha | JavaScript error | Ver console do navegador |
| Erro de autenticação | Sessão expirada | Fazer login novamente |

---

## ✅ Checklist de Validação Manual

### Tela de Cadastro
- [ ] Página carrega sem erros
- [ ] Tabela mostra lista de estagiários
- [ ] Botão "Cadastrar" abre modal
- [ ] Modal tem todos os campos requeridos
- [ ] Validação de email funciona
- [ ] Validação de CPF funciona
- [ ] Duplicação é detectada
- [ ] Salvar cria novo registro
- [ ] QR Code pode ser gerado
- [ ] Editar atualiza dados
- [ ] Excluir remove registro

### Tela de Exportação
- [ ] Página carrega sem erros
- [ ] Tabela exibe registros
- [ ] Cards mostram resumos
- [ ] Filtro por data funciona
- [ ] Filtro por mês funciona
- [ ] Filtro por ano funciona
- [ ] Filtro por motivo funciona
- [ ] Busca funciona
- [ ] Paginação funciona
- [ ] Cálculo de horas está correto
- [ ] Múltiplos filtros funcionam juntos

---

## 📞 Suporte

Para relatar problemas com os testes:

1. Executar: `./run-tests.sh all`
2. Verificar erros específicos
3. Executar: `./run-tests.sh coverage-html`
4. Revisar relatório de cobertura

---

**Última Atualização:** 2026-06-18
**Versão:** 1.0
