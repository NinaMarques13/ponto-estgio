# 🛠️ Guia de Correções - Telas de Registros e Cadastro

Este documento explica os problemas reportados na tela de registros e de cadastro de estagiários, detalhando a causa raiz, os exemplos de código de antes e depois, e a solução implementada.

---

## 📅 Tela de Registros

### 1. Filtro por Mês Não Funcionava
*   **Problema:** Ao selecionar o mês (ex: "Agosto de 2026"), o filtro não retornava dados ou apresentava comportamento inesperado.
*   **Causa Raiz:** O input HTML5 do tipo `month` envia o valor formatado como `YYYY-MM` (ex: `2026-08`). No backend, a consulta esperava obter o ano e o mês separados por requisição individual.
*   **Exemplo de Código (Antes vs Depois):**
    *   **Antes (`EstagiariosController.php`):**
        ```php
        $ano = $request->filled('ano') ? $request->ano : now()->year;
        $mes = $request->filled('mes') ? $request->mes : now()->month;

        // ...
        } elseif ($request->filled('mes') && $request->filled('ano')) {
            $inicio = Carbon::createFromDate($request->ano, $request->mes, 1)->startOfMonth();
            $fim = Carbon::createFromDate($request->ano, $request->mes, 1)->endOfMonth();
        ```
    *   **Depois (`EstagiariosController.php`):**
        ```php
        // Extrai ano e mês do campo 'mes' se ele vier formatado como YYYY-MM
        $ano = $request->input('ano');
        $mes = $request->input('mes');
        if ($mes && strpos($mes, '-') !== false) {
            $partes = explode('-', $mes);
            $ano = $partes[0];
            $mes = $partes[1];
        }
        if (!$ano) { $ano = now()->year; }
        if (!$mes) { $mes = now()->month; }

        // ...
        } elseif ($request->filled('mes')) {
            $inicio = Carbon::createFromDate($ano, $mes, 1)->startOfMonth();
            $fim = Carbon::createFromDate($ano, $mes, 1)->endOfMonth();
        ```

---

### 2. Impossibilidade de Limpar Filtros ("..." / Três Pontos e Ano)
*   **Problema:** Após selecionar um dia ou ano nos dropdowns, não era possível selecionar novamente a opção vazia (`...` ou `Selecione um ano...`) para desmarcar o filtro. Era necessário recarregar a página (F5).
*   **Causa Raiz:** Os elementos `<option>` correspondentes à opção vazia/padrão estavam marcados com o atributo `disabled` no HTML.
*   **Exemplo de Código (Antes vs Depois):**
    *   **Antes (`export.blade.php`):**
        ```html
        <select name="data-ano" id="data-semana-inicio" class="form-control">
            <option value="" selected disabled>...</option>
        ```
    *   **Depois (`export.blade.php`):**
        ```html
        <select name="data-ano" id="data-semana-inicio" class="form-control">
            <option value="" selected>...</option>
        ```

---

### 3. Lógica do Filtro por Motivos ("Em Andamento" / "Presente")
*   **Problema:** A lógica do status "Em Andamento" e "Presente" causava confusão, listando registros de forma duplicada ou incoerente.
*   **Causa Raiz:** Filtrar por "Em Andamento" apenas procurava registros com `ds_motivo = 'entrada'`, retornando mesmo quem já havia registrado a saída.
*   **Exemplo de Código (Antes vs Depois):**
    *   **Antes (`EstagiariosController.php`):**
        ```php
        if ($request->motivo === 'presente') {
            $q->whereIn('ds_motivo', ['entrada', 'saida']);
        } elseif ($request->motivo === 'andamento') {
            $q->where('ds_motivo', 'entrada');
        }
        ```
    *   **Depois (`EstagiariosController.php`):**
        ```php
        if ($request->motivo === 'presente') {
            // Registro completo: tem Entrada com Saída correspondente no mesmo dia
            $q->where('ds_motivo', 'entrada')
              ->whereExists(function ($query) {
                  $query->select(DB::raw(1))
                        ->from('registro_ponto as rp_saida')
                        ->whereColumn('rp_saida.estagiario_id', 'registro_ponto.estagiario_id')
                        ->where('rp_saida.ds_motivo', 'saida')
                        ->whereRaw('DATE(rp_saida.hr_registro) = DATE(registro_ponto.hr_registro)');
              });
        } elseif ($request->motivo === 'andamento') {
            // Em andamento: tem Entrada SEM Saída correspondente no mesmo dia
            $q->where('ds_motivo', 'entrada')
              ->whereNotExists(function ($query) {
                  $query->select(DB::raw(1))
                        ->from('registro_ponto as rp_saida')
                        ->whereColumn('rp_saida.estagiario_id', 'registro_ponto.estagiario_id')
                        ->where('rp_saida.ds_motivo', 'saida')
                        ->whereRaw('DATE(rp_saida.hr_registro) = DATE(registro_ponto.hr_registro)');
              });
        }
        ```

---

## 👥 Tela de Cadastro de Usuários (Estagiários)

### 1. Estagiários Não Eram Listados
*   **Problema:** A tabela de estagiários cadastrados ficava vazia, mesmo contendo registros ativos no banco de dados.
*   **Causa Raiz:** A consulta filtrava registros pela coluna `ds_situacao` usando a string `'true'`. Como a coluna do banco de dados é um tinyint/boolean, a comparação string falhava.
*   **Exemplo de Código (Antes vs Depois):**
    *   **Antes (`CadastroController.php`):**
        ```php
        $query = Estagiario::query()->where('ds_situacao', 'true');
        ```
    *   **Depois (`CadastroController.php`):**
        ```php
        $query = Estagiario::query()->where('ds_situacao', true);
        ```
