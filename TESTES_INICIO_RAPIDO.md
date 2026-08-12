# 🧪 TESTES - Referência Rápida

> ⭐ **LEIA ISTO PRIMEIRO** - Guia rápido de 2 minutos

## 🚀 Comece Agora!

### Rodar Todos os Testes
```bash
php artisan test tests/Feature/EstagiariosTest.php
```

### Ou Use o Script
```bash
chmod +x run-tests.sh
./run-tests.sh all
```

**Esperado:** Todos os 30 testes passam ✅

---

## 📊 O Que Você Tem

| Item | Arquivos | Detalhes |
|------|----------|----------|
| **Testes** | EstagiariosTest.php | 30 testes (500+ linhas) |
| **Factory** | RegistroPontoFactory.php | Gerador de dados de teste |
| **Script** | run-tests.sh | Facilita execução |
| **Docs** | 5 guias | INDEX_DOCS.md é o índice |

---

## 🎯 Testes Disponíveis

```
✅ 8 testes - Cadastro (validação, criar, atualizar, deletar)
✅ 6 testes - Exportação (filtros por data/mês/ano/motivo)
✅ 3 testes - Registro de Ponto (entrada, saída)
✅ 2 testes - Atualização (horários, motivos)
✅ 1 teste  - Pesquisa
✅ 2 testes - QR Code
✅ 8 testes - Fluxos completos + validações
────────────────────────────────────────
   30 TESTES TOTAL
```

---

## 🔥 Comandos Principais

### Rodar Tudo
```bash
./run-tests.sh all              # Todos os testes
php artisan test                # Alternativa
```

### Rodar Específico
```bash
./run-tests.sh cadastro         # Só cadastro
./run-tests.sh validacao        # Só validação
./run-tests.sh fluxo            # Só fluxos completos
```

### Ver Cobertura
```bash
./run-tests.sh coverage         # Terminal
./run-tests.sh coverage-html    # Gera HTML
```

---

## 📖 Documentação

| Doc | Propósito | Tempo |
|-----|-----------|-------|
| [INDEX_DOCS.md](INDEX_DOCS.md) | Índice completo | 2 min |
| [RESUMO_TESTES.md](RESUMO_TESTES.md) | Visão geral | 5 min |
| [GUIA_TESTES.md](GUIA_TESTES.md) | Como executar | 5 min |
| [RELATORIO_TESTES.md](RELATORIO_TESTES.md) | Detalhes cada teste | 15 min |
| [VALIDACAO_TELAS.md](VALIDACAO_TELAS.md) | Testar manualmente | 10 min |

---

## ✅ Checklist Rápido

```
☐ Executei: php artisan test
☐ Vi: 30 testes passando
☐ Li: RESUMO_TESTES.md
☐ Validei: Telas em /cadastro
☐ Gerei: Relatório coverage-html
```

---

## 🎨 Telas Testadas

### Cadastro (`/cadastro`)
- Criar novo estagiário ✅
- Validar campos ✅
- Editar ✅
- Excluir ✅

### Exportação (`/admin/export`)
- Listar registros ✅
- Filtrar por data ✅
- Filtrar por mês ✅
- Filtrar por ano ✅
- Calcular horas ✅

---

## 🔗 Rotas Cobertas

```
✅ POST   /cadastrar-estagiario
✅ PUT    /atualizar-cadastro/{id}
✅ PUT    /desativar-estagiario/{id}
✅ GET    /estagiarios-cadastrados
✅ POST   /lista-estagiarios
✅ POST   /registrar-ponto
✅ PUT    /atualizar-estagiarios/{id}
✅ GET    /pesquisarEstagiarios
✅ POST   /processar-qrcode
```

---

## 🐛 Problema?

| Erro | Solução |
|------|---------|
| "Factory not found" | `composer dump-autoload` |
| "Database error" | `php artisan migrate:fresh --env=testing` |
| Testes não rodam | Verifique se PHP está instalado: `php -v` |
| Quer mais detalhes | Leia [GUIA_TESTES.md](GUIA_TESTES.md) |

---

## 📝 Próximos Passos

1. **Execute:** `./run-tests.sh all`
2. **Valide:** Telas em navegador ([VALIDACAO_TELAS.md](VALIDACAO_TELAS.md))
3. **Explore:** Detalhes em [RELATORIO_TESTES.md](RELATORIO_TESTES.md)
4. **Mantendo:** Adicione testes para novo código

---

## 🌟 Destaques

- ✅ 30 testes prontos para rodar
- 📊 100% de cobertura nas funcionalidades principais
- 📖 5 documentos completos
- 🚀 Script para facilitar execução
- 🎨 Validação manual documentada

---

## 📞 Documentação Completa

👉 **[Índice Completo: INDEX_DOCS.md](INDEX_DOCS.md)**

Clique para ver todos os documentos e opções disponíveis.

---

**Versão:** 1.0  
**Status:** ✅ Pronto  
**Última Atualização:** 2026-06-18
