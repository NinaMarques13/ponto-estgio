# Avaliação de Segurança e LGPD

## Escopo

Avaliação estática das rotas, views, controllers, services, models, migrations e frontend do sistema de controle de ponto. O documento identifica riscos observáveis no código atual e recomendações para tratamento. Não substitui revisão jurídica, análise de impacto à proteção de dados (RIPD) ou validação com o encarregado/DPO.

## Resumo executivo

O sistema trata dados pessoais de estagiários e administradores, incluindo CPF, nome, telefone, e-mail, registros de ponto, endereço IP e informações de eventos. CPF é **dado pessoal** para a LGPD; não é, isoladamente, dado pessoal sensível, mas exige proteção, finalidade, necessidade, transparência, controle de acesso e prestação de contas.

Os riscos mais urgentes são:

1. Rotas destrutivas e administrativas expostas sem autenticação adequada.
2. CPF usado como identificador operacional do ponto e do QR Code, inclusive em mensagens, respostas e tabelas.
3. Rotas de gestão aparentemente fora do middleware `auth:admin`.
4. Ausência de autorização por ação e por registro, além da autenticação de sessão.
5. Possível vazamento de dados e detalhes internos em respostas JSON e logs.
6. Falta de proteção contra replay, cópia e falsificação no fluxo de QR Code.

## Achados confirmados

### SEC-01 - Rotas críticas expostas publicamente - Crítico

Em `routes/web.php` existem endpoints GET que executam operações administrativas:

- `/popular-banco-secreto` executa o `AdminSeeder`.
- `/popular-banco-estagiarios` executa seeders de dados.
- `/reduzir-banco-estagiarios` executa `migrate:fresh` e apaga o banco.
- `/admin/run-migrations` executa migrações.

Essas rotas não estão protegidas por `auth:admin`, autorização de nível, ambiente ou segredo. Usar GET para alterar ou apagar dados também permite acionamento acidental, pré-carregamento por robôs e abuso via CSRF.

**Recomendação:** remover essas rotas do ambiente publicado. Migrações e seeders devem ser executados via pipeline/CLI protegido. Se uma operação operacional temporária for indispensável, usar comando autenticado fora da aplicação, ambiente restrito, método POST, autorização explícita, auditoria e confirmação adicional. Nunca disponibilizar `migrate:fresh` em produção.

### SEC-02 - Gestão de estagiários e eventos sem proteção consistente - Crítico

Após o grupo `auth:admin`, várias rotas de gestão aparecem registradas fora dele, como cadastro, atualização, desativação, listagens, eventos e exportação de dados. Isso permite que endpoints potencialmente administrativos sejam chamados sem sessão de administrador, dependendo do comportamento atual das views e do ambiente.

**Recomendação:** agrupar todas as rotas administrativas em `Route::middleware(['auth:admin', ...])`. Separar rotas públicas de registrar ponto das rotas de consulta e manutenção. Adicionar middleware de autorização por capacidade/nível, por exemplo `admin.manage-estagiarios`, `admin.manage-eventos` e `admin.export`.

### SEC-03 - CPF como dado de entrada, busca, resposta e QR Code - Alto

O CPF é recebido diretamente em `PontoController::store` e `processarQrcode`, usado para localizar o estagiário e aparece em mensagens de erro, respostas JSON, DataTables e scripts do frontend. O cadastro também persiste o CPF em claro na coluna `estagiarios.cpf`.

Consequências:

- exposição desnecessária em telas, logs do navegador, histórico, proxies e ferramentas de suporte;
- enumeração de pessoas por CPF, especialmente no endpoint de ponto;
- QR Code copiável e reutilizável, caso contenha o CPF;
- dificuldade de demonstrar minimização e necessidade perante a LGPD;
- impacto maior em caso de vazamento do banco.

**Recomendação:** separar o identificador interno do identificador do QR Code. O QR Code não deve conter CPF, nome, e-mail ou matrícula. Deve conter um identificador aleatório opaco, revogável e, preferencialmente, de uso controlado. Nas telas, mascarar CPF, por exemplo `***.***.***-42`, e retornar somente os campos necessários para cada operação.

### SEC-04 - Exportação e listagens expõem dados em massa - Alto

O controller de ponto adiciona CPF, nome, setor, horários, observação e outros dados ao DataTables. A exportação deve ser tratada como operação de alto impacto, pois concentra dados pessoais e registros de jornada.

**Recomendação:** exigir autenticação e autorização específica, limitar filtros e período, registrar quem exportou, quando, finalidade e quantidade de registros, aplicar mascaramento por padrão e limitar formatos/downloads. Avaliar se o CPF completo é realmente necessário no arquivo exportado.

### SEC-05 - Autorização de nível usa o guard padrão - Alto

`CheckAdminLevel` verifica `auth()->check()` e `auth()->user()`, mas o sistema utiliza o guard `admin`. Isso pode validar o usuário errado ou negar/permitir acesso de forma inesperada quando os guards diferirem.

**Recomendação:** usar `auth('admin')->check()` e `auth('admin')->user()`, ou injetar explicitamente o guard no middleware. Preferir Policies/Gates para autorização por recurso, em vez de depender apenas de números de nível.

### SEC-06 - Controle de acesso por ID precisa de Policies - Alto

Operações como atualizar, desativar, listar eventos, excluir evento e alterar ponto recebem um `{id}` e fazem busca direta. A autenticação, sozinha, não comprova que o administrador pode executar aquela ação ou que o registro pertence ao contexto autorizado.

**Recomendação:** aplicar `can`/Policies em cada operação, usar route model binding, validar existência e situação do estagiário, restringir datas e registrar alterações. Para exclusões e correções, exigir permissão elevada e motivo obrigatório.

### SEC-07 - Mensagens e respostas vazam informações internas - Médio/Alto

Há respostas contendo CPF informado, nome, detalhes de exceção e, no cadastro, o objeto criado. Em tratamento de erro aparecem `$e->getMessage()` e o objeto da exceção na resposta. Isso pode revelar estrutura de banco, consultas, caminhos ou dados pessoais.

**Recomendação:** respostas públicas devem usar mensagens genéricas e códigos de erro. Detalhes ficam em logs estruturados, sem CPF, senha, token ou payload completo. Em produção, desabilitar debug e revisar canais de log, retenção e acesso.

### SEC-08 - Validação e normalização de CPF inconsistente - Médio

O cadastro valida `unique` antes de normalizar o CPF, enquanto depois salva somente dígitos. Isso pode permitir duplicidades ou produzir regras inconsistentes entre valores formatados e não formatados. Também há validações apenas de tamanho, sem validação do dígito do CPF.

**Recomendação:** normalizar em Form Request/DTO antes da validação, validar formato e dígitos verificadores, aplicar índice único sobre a representação canônica e não retornar o valor completo sem necessidade.

### SEC-09 - Falta de rate limit e antifraude no ponto/QR Code - Alto

`registrar-ponto` e `processar-qrcode` são endpoints de alto valor operacional e não mostram rate limiting, nonce, expiração ou mecanismo de detecção de tentativas. O fluxo atual permite tentativa repetida com CPF.

**Recomendação:** aplicar `throttle` por IP, dispositivo e identificador opaco; impor janela temporal e limites; registrar tentativas anômalas; usar respostas uniformes para reduzir enumeração; e exigir um QR Code rotativo ou sessão de registro com expiração curta.

## Projeto recomendado para CPF

Há duas necessidades diferentes e elas não devem usar o mesmo mecanismo:

### CPF para login e integração

Se a aplicação precisa pesquisar pelo CPF, uma opção é manter uma representação criptográfica não reversível para busca exata:

- normalizar o CPF para 11 dígitos;
- gerar `cpf_hash = HMAC-SHA-256(chave_secreta, cpf_normalizado)`;
- armazenar o hash com índice único;
- guardar o CPF cifrado separadamente apenas se houver necessidade operacional legítima de recuperá-lo;
- manter a chave fora do banco, em secret manager/KMS;
- nunca usar hash sem chave como proteção suficiente, pois CPF tem espaço de busca pequeno;
- mascarar o CPF em logs, views, mensagens e exports.

Criptografia em repouso não elimina a necessidade de controle de acesso. O banco, backups, dumps, filas, logs e ambientes de teste também devem ser considerados.

### QR Code

O QR Code deve carregar apenas um valor aleatório, por exemplo um token de alta entropia, nunca o CPF:

1. Gerar um token aleatório forte com `random_bytes`/`Str::random` apropriado.
2. Exibir no QR Code somente o token ou uma referência opaca.
3. Armazenar no servidor apenas o hash do token, com `hash_equals` na validação quando aplicável.
4. Associar o token ao estagiário no servidor.
5. Definir expiração, revogação e rotação.
6. Usar estado de uso, nonce ou janela curta para impedir replay.
7. Não retornar nome e CPF antes de uma validação legítima.
8. Registrar tentativas inválidas, reutilização e revogação sem registrar o token em claro.
9. Considerar assinatura HMAC para detectar alteração, mas lembrar que assinatura não impede cópia: expiração e replay protection continuam necessárias.

Para registro presencial, uma alternativa mais forte é um QR Code dinâmico exibido por um terminal autorizado, com token de curta duração e associação a uma sessão. Um QR Code impresso e permanente deve ser tratado como credencial portadora: se for copiado, qualquer pessoa poderá tentar usá-lo.

## Autenticação, sessão e tokens

- Manter `Hash::make`/cast `hashed` para senhas; nunca criar ou comparar senha manualmente.
- Aumentar a senha mínima e aplicar política contra senhas comprometidas.
- Adicionar rate limit e bloqueio progressivo ao login; não diferenciar usuário inexistente de senha incorreta.
- Manter regeneração de sessão no login e invalidar sessão/token no logout.
- Habilitar cookies `Secure`, `HttpOnly` e `SameSite=Lax` ou `Strict` conforme o fluxo.
- Usar HTTPS obrigatório, HSTS e redirecionamento seguro em produção.
- Usar CSRF em todas as requisições de mutação; remover mutações via GET.
- Para tokens de API, usar tokens revogáveis, escopos, expiração, rotação e armazenamento somente do hash. Nunca colocar token em URL ou log.
- Usar confirmação de senha/MFA para exportações, correções, desativações e ações administrativas de maior impacto.

## Entrada, saída e banco de dados

- Usar Form Requests para todas as entradas, com tipos, limites, enums e datas válidas.
- Preferir listas fechadas para `motivo`; não aceitar valores arbitrários.
- Validar datas e horários contra limites razoáveis e timezone configurado.
- Nunca usar `$request->all()` em services; passar um DTO/array explicitamente validado.
- Manter mass assignment restritivo e usar Resources/DTOs para definir o que sai em JSON.
- Escapar saída em Blade; revisar cuidadosamente qualquer `rawColumns` e HTML gerado no DataTables.
- Usar consultas parametrizadas/Eloquent e revisar filtros de ordenação e busca do DataTables.
- Aplicar privilégios mínimos ao usuário do banco e criptografia de backups.
- Definir retenção: registros de ponto, logs, exports e tokens devem ter prazos e descarte seguro documentados.
- Evitar dados reais em testes, desenvolvimento e seeders publicados.

## Auditoria e resposta a incidentes

Registrar eventos de segurança sem capturar segredos ou CPF completo:

- login bem-sucedido, falho e logout;
- criação, alteração, desativação e exclusão lógica;
- correção de ponto e geração de ocorrência em massa;
- exportação, migração e operações de manutenção;
- criação, rotação, revogação e replay de QR Code;
- alterações de permissão.

Cada evento deve ter administrador, ação, recurso, data/hora, resultado, request ID e IP tratado conforme política de retenção. Definir alertas para volume anormal, múltiplas falhas e uso de rotas de manutenção.

## Plano de implementação priorizado

### P0 - Antes de publicar

- Remover as rotas públicas de seed, `migrate:fresh` e migração.
- Colocar todas as rotas administrativas sob `auth:admin` e autorização de nível/Policy.
- Corrigir `CheckAdminLevel` para usar explicitamente o guard `admin`.
- Remover exceções e dados completos das respostas de produção.
- Aplicar rate limit a login, ponto e QR Code.
- Garantir HTTPS, cookies seguros, `APP_DEBUG=false` e secrets fora do repositório.

### P1 - Proteção de dados

- Parar de enviar CPF completo em mensagens, DataTables e respostas desnecessárias.
- Trocar CPF no QR Code por token opaco, expirável e revogável.
- Definir se o CPF precisa ser recuperável; caso não, usar HMAC para busca. Caso precise, cifrar com chave gerenciada.
- Padronizar normalização e validação com Form Requests.
- Criar Resources/DTOs para controlar campos de entrada e saída.

### P2 - Governança e resiliência

- Implementar Policies, MFA e confirmação para operações críticas.
- Criar auditoria de ações administrativas e exportações.
- Definir retenção, descarte, backup, acesso por função e procedimento de incidente.
- Produzir inventário de dados, registro de operações e aviso de privacidade.
- Executar testes automatizados de autorização, enumeração, replay de QR Code, CSRF, rate limit e vazamento de dados.

## Testes de segurança sugeridos

- Usuário não autenticado não acessa nenhuma rota administrativa.
- Admin de nível insuficiente recebe `403` em cada ação protegida.
- Um admin não consegue alterar ou excluir recurso fora de sua permissão.
- GET não altera estado nem executa operações destrutivas.
- Respostas de CPF inexistente e CPF existente não permitem enumeração relevante.
- QR Code expirado, revogado, adulterado ou reutilizado é rejeitado.
- CPF não aparece em logs, URLs, mensagens genéricas ou payloads não necessários.
- Exportação exige permissão, registra auditoria e aplica o escopo correto.
- Payloads inesperados não atravessam mass assignment nem alteram campos protegidos.
- Falhas não retornam stack trace, SQL, caminho local ou exceção.

## Referências locais avaliadas

- `routes/web.php`
- `app/Domains/ControleDePonto/Controllers/PontoController.php`
- `app/Domains/Estagiarios/Controllers/CadastroController.php`
- `app/Domains/Estagiarios/Services/EstagiarioService.php`
- `app/Domains/Admins/Controllers/LoginController.php`
- `app/Http/Middleware/CheckAdminLevel.php`
- `app/Domains/Estagiarios/Models/Estagiario.php`
- `app/Domains/ControleDePonto/Models/RegistroPonto.php`
- `resources/views/pages/inicio/inicio.blade.php`
- `resources/views/pages/principal/cadastro.blade.php`
- `public/js/script.js`
