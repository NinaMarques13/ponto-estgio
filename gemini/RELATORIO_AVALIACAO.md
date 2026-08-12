# 📊 Relatório de Avaliação Técnica - Ponto-Estágio

Este relatório documenta uma análise minuciosa no código-fonte do projeto **Ponto-Estágio**. Abaixo estão detalhados os principais problemas identificados (incluindo bugs críticos de segurança e comportamento) e sugestões de melhorias estruturadas.

---

## 🔍 Resumo dos Problemas Encontrados

| Gravidade | Tipo | Descrição | Impacto |
| :--- | :--- | :--- | :--- |
| 🔴 **Crítico** | **Segurança** | Rotas administrativas de API públicas | Acesso e alteração de dados sem autenticação. |
| 🔴 **Crítico** | **Comportamento** | Bug de envio no cadastro de estagiários | Formulário recarrega a página via GET sem salvar. |
| 🔴 **Crítico** | **Feature** | Exportação de relatórios ausente | Controllers e views de exportação foram deletados. |
| 🟡 **Alto** | **Comportamento** | Scanner de QR Code incompleto | Apenas valida dados, mas não registra o ponto de fato. |
| 🟡 **Alto** | **Comportamento** | Bypass de validação no formulário de ponto | Submissão ocorre mesmo com validação inválida. |
| 🟢 **Médio** | **Arquitetura** | Middleware de nível de admin não aplicado | Níveis de privilégios (`level`) não são aplicados. |
| 🟢 **Médio** | **Documentação** | Arquivo `PROBLEMAS_MERGE.md` desatualizado | Referencia arquivos deletados e assume controllers vazios. |

---

## 🔴 1. Rotas Administrativas Expostas Publicamente
### Análise
No arquivo `routes/web.php`, as rotas que manipulam os dados dos estagiários e registros de ponto estão fora do middleware de autenticação `auth:admin`.

```php
// routes/web.php (Linhas 31-38)
Route::get('inicio', [EstagiariosController::class, 'index'])->name('inicio.index');
Route::any('registrar-ponto', [EstagiariosController::class, 'store'])->name('registrar-ponto');
Route::any('lista-estagiarios', [EstagiariosController::class, 'listaEstagiariosDia'])->name('lista.estagiarios');
Route::put('atualizar-estagiarios/{id}', [EstagiariosController::class, 'atualizarEstagiario'])->name('atualizar-estagiarios');
Route::get('estagiarios-cadastrados', [CadastroController::class, 'listagemCadastrados'])->name('estagiarios-cadastrados');
Route::post('cadastrar-estagiario', [CadastroController::class, 'storeEstagiario'])->name('estagiarios.store');
Route::put('atualizar-cadastro/{id}', [CadastroController::class, 'updateCadastro'])->name('estagiarios.update');
Route::put('desativar-estagiario/{id}', [CadastroController::class, 'desativarCadastro'])->name('estagiarios.desativar');
```

Qualquer usuário sem autenticação pode enviar requisições para `/cadastrar-estagiario`, `/desativar-estagiario/{id}` ou `/atualizar-estagiarios/{id}` e comprometer completamente a integridade do banco de dados.

### 💡 Solução
Agrupar essas rotas administrativas dentro do bloco de middleware `auth:admin`:

```php
Route::middleware('auth:admin')->group(function () {
    Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
    Route::get('dashboard', function () { return view('pages.principal.dashboard'); })->name('dashboard');
    Route::get('cadastro', function () { return view('pages.principal.cadastro'); })->name('cadastro');
    Route::get('admin/export', function () { return view('pages.principal.export'); })->name('export');

    // Mover rotas de escrita/consulta administrativa para cá:
    Route::any('lista-estagiarios', [EstagiariosController::class, 'listaEstagiariosDia'])->name('lista.estagiarios');
    Route::put('atualizar-estagiarios/{id}', [EstagiariosController::class, 'atualizarEstagiario'])->name('atualizar-estagiarios');
    Route::get('estagiarios-cadastrados', [CadastroController::class, 'listagemCadastrados'])->name('estagiarios-cadastrados');
    Route::post('cadastrar-estagiario', [CadastroController::class, 'storeEstagiario'])->name('estagiarios.store');
    Route::put('atualizar-cadastro/{id}', [CadastroController::class, 'updateCadastro'])->name('estagiarios.update');
    Route::put('desativar-estagiario/{id}', [CadastroController::class, 'desativarCadastro'])->name('estagiarios.desativar');
});
```

---

## 🔴 2. Bug de Submissão no Cadastro de Estagiários
### Análise
No arquivo `resources/views/pages/principal/cadastro.blade.php` (linha 70), a tag form de cadastro de novos estagiários não possui os atributos `method` ou `action`:

```html
<form id="formAdicionarEstagiario">
    @csrf
    @method('put') <!-- Isso também está incorreto para um cadastro (POST) -->
```

Além disso, não existe nenhum interceptador jQuery para o evento `submit` desse formulário em `public/js/script.js`. Ao tentar salvar o cadastro, o formulário faz um GET normal e recarrega a página colocando as variáveis na URL, sem persistir nada.

Outro detalhe é que os inputs do HTML utilizam names simplificados (`nome`, `cpf`, `setor`, `telefone`, `email`), mas o método `storeEstagiario` de `CadastroController.php` espera as chaves mapeadas para as colunas do banco (`nm_estagiarios`, `nr_matricula`, etc.).

### 💡 Solução
1. Alterar a abertura do form em `cadastro.blade.php` para enviar via POST:
   ```html
   <form id="formAdicionarEstagiario" action="{{ route('estagiarios.store') }}" method="POST">
       @csrf
   ```
2. Adicionar o tratador de evento AJAX em `public/js/script.js`:
   ```javascript
   $("#formAdicionarEstagiario").on("submit", function (e) {
       e.preventDefault();

       const payload = {
           _token: $('input[name="_token"]').val(),
           nm_estagiarios: $("#nome_cadastro").val(),
           nr_matricula: $("#cpf_cadastro").val(),
           nm_setor: $("#setor_cadastro").val(),
           nr_telefone: $("#telefone_cadastro").val(),
           nm_email: $("#email_cadastro").val()
       };

       $.ajax({
           url: "/cadastrar-estagiario",
           type: "POST",
           data: payload,
           dataType: "json",
           success: function (response) {
               alert("Sucesso: Estagiário cadastrado!");
               $("#modalAdicionarEstagiario").modal("hide");
               $("#formAdicionarEstagiario")[0].reset();
               if (typeof tabelaCadastrados !== "undefined") {
                   tabelaCadastrados.ajax.reload();
               } else {
                   location.reload();
               }
           },
           error: function (xhr) {
               let erroMsg = "Erro de validação.";
               if (xhr.responseJSON && xhr.responseJSON.errors) {
                   erroMsg = Object.values(xhr.responseJSON.errors).flat().join("\n");
               }
               alert("Falha ao cadastrar: \n" + erroMsg);
           }
       });
   });
   ```

---

## 🔴 3. Ausência de Exportação de Relatórios (PDF/CSV)
### Análise
No commit `f69433c` ("refatoração 1.0"), o arquivo `RelatorioController.php` e a view de template PDF `pdf.blade.php` foram completamente deletados do projeto. Com isso, os métodos de exportação não existem mais. 

Além disso, na view `resources/views/pages/principal/export.blade.php` não existem botões para acionar a geração de PDF ou Excel.

### 💡 Solução
1. **Recriar o RelatorioController** (`app/Http/Controllers/RelatorioController.php`) corrigindo as consultas:
   - Trocar o filtro `created_at` por `hr_registro` (o campo correto do banco).
   - Ajustar o mapeamento de `$registro->estagiario->name` para `$registro->estagiario->nm_estagiarios`.
2. **Recriar o template PDF** (`resources/views/pdf.blade.php`).
3. **Mapear as rotas** no `routes/web.php`.
4. **Adicionar os botões de ação** (PDF / Excel) na view `export.blade.php` integrando com o backend.

---

## 🟡 4. Automação do Scanner de QR Code Incompleta
### Análise
No arquivo `public/js/script.js` (linha 418), quando o QR Code é lido com sucesso pela câmera:
1. Ele envia os dados para `/processar-qrcode`.
2. O backend responde informando que o estagiário existe.
3. Exibe um alerta "CPF processado com sucesso!" e **para por aí**.
4. Não há preenchimento automático do input de CPF, nem submissão automática do registro de ponto (entrada/saída).

### 💡 Solução
Melhorar a experiência de uso. Ao escanear o QR Code, a câmera deve preencher o input do CPF e realizar a batida de ponto automaticamente:

```javascript
// public/js/script.js (Callback de leitura de QR Code)
(decodedText) => {
    html5QrCode.stop().then(() => {
        // Injeta no campo de texto e formata
        $("#cpf").val(aplicarMascaraCPF(decodedText));
        
        // Exibe feedback visual de processamento
        alert("QR Code lido! Registrando ponto...");
        
        // Submete o formulário principal automaticamente
        $("form[action*='registrar-ponto']").submit();
    });
}
```

---

## 🟡 5. Bypass de Validação no Registro de Ponto
### Análise
No evento de clique do botão `#registrarBtn` em `public/js/script.js` (linha 68), há validações para garantir que o CPF não seja em branco e tenha 11 caracteres. Caso a validação falhe, o código mostra um `alert(...)` e dá um `return`. 

No entanto, o botão é do tipo `type="submit"`, o que significa que o evento de submissão do formulário continuará ocorrendo normalmente mesmo após o `return`, enviando dados inválidos ao backend.

### 💡 Solução
Adicionar o parâmetro de evento `e` e chamar `e.preventDefault()` quando a validação falhar:

```javascript
// public/js/script.js (Linha 68)
$("#registrarBtn").on("click", function (e) {
    let cpfBruto = $("#cpf").val().trim();
    let cpf = limparCPF(cpfBruto);

    if (cpf === "") {
        e.preventDefault(); // Impede o envio do form
        alert("Por favor, digite o CPF.");
        $("#cpf").focus();
        return;
    }

    if (cpf.length !== 11) {
        e.preventDefault(); // Impede o envio do form
        alert("O CPF deve conter exatamente 11 dígitos.");
        $("#cpf").focus();
        return;
    }
});
```

---

## 🟢 6. Middleware de Permissões de Admin (CheckAdminLevel)
### Análise
O middleware `CheckAdminLevel` está corretamente cadastrado no `bootstrap/app.php` sob o alias `checklevel`, mas não está em uso em nenhuma rota do `routes/web.php`.
Além disso, no middleware:
```php
if (auth()->user()->level > $requiredLevel){
    abort(403,'Acesso Negado.');
}
```
Como o guard padrão do Laravel é o `web` (que busca de `users`), chamar `auth()->user()` fora de um guard específico pode retornar nulo ou buscar o model incorreto, já que os administradores usam o guard `admin`.

### 💡 Solução
1. Atualizar o middleware para forçar a verificação do guard `admin`:
   ```php
   // app/Http/Middleware/CheckAdminLevel.php
   if (!auth('admin')->check()) {
       abort(401);
   }
   if (auth('admin')->user()->level > $requiredLevel) {
       abort(403, 'Acesso Negado.');
   }
   ```
2. Aplicar o middleware nas rotas de escrita/cadastro, permitindo apenas para SuperAdmin (level 1):
   ```php
   Route::middleware(['auth:admin', 'checklevel:1'])->group(function () {
       Route::get('cadastro', function () { return view('pages.principal.cadastro'); })->name('cadastro');
       Route::post('cadastrar-estagiario', [CadastroController::class, 'storeEstagiario']);
       Route::put('atualizar-cadastro/{id}', [CadastroController::class, 'updateCadastro']);
       Route::put('desativar-estagiario/{id}', [CadastroController::class, 'desativarCadastro']);
   });
   ```

---

## 🚀 Próximos Passos Recomendados

Se você desejar, posso ajudar a corrigir esses problemas implementando:
1. **Proteção das Rotas de API** e ativação do middleware de nível de admin (`checklevel:1`).
2. **Correção do envio de formulário** via AJAX no cadastro de estagiários.
3. **Automação completa do QR Code** (auto-fill + auto-submit).
4. **Reconstrução completa do RelatorioController** (PDF e Excel/CSV) com os botões funcionais na view de exportação.
