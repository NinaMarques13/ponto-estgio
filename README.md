# 🕐 Ponto-Estagio - Sistema de Controle de Ponto para Estagiários

Um sistema web completo para gerenciar o registro de ponto, dados de estagiários, turnos e geração de relatórios em PDF. Desenvolvido para órgãos públicos e instituições que necessitam controlar a presença de estagiários.

## ✨ Funcionalidades Principais

- ✅ **Autenticação de Administradores** - Login seguro com CPF e senha
- 📱 **Registro de Ponto com QR Code** - Captura automática de entrada/saída
- 👥 **Gestão de Estagiários** - Cadastro, edição e desativação de estagiários
- 🔄 **Controle de Turnos** - Associação de turnos aos estagiários
- 📊 **Listagem de Registros** - Visualização de presença por dia
- 📄 **Geração de Relatórios PDF** - Exportação de dados em PDF
- 📈 **Tabelas Interativas** - DataTables com filtros, busca e paginação
- 🔐 **Segurança** - Registro de IP e rastreamento de acessos

## 🛠️ Requisitos Técnicos

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** >= 9.x
- **Banco de Dados** - MySQL, PostgreSQL ou compatível
- **Servidor Web** - Apache, Nginx ou similar

## 📦 Dependências Principais

```json
{
  "laravel/framework": "^12.0",
  "barryvdh/laravel-dompdf": "^3.1",
  "yajra/laravel-datatables": "^12.0",
  "yajra/laravel-datatables-buttons": "^12.0"
}
```

## 🚀 Instalação

### 1. Clonar o Repositório

```bash
git clone https://gitlab.pm.pr.gov.br/pm-est.nicolaspaiva/ponto-estagio.git
cd ponto-estagio
```

### 2. Instalar Dependências

```bash
# Instalação completa (recomendado para primeira vez)
composer setup
```

Ou manualmente:

```bash
# Instalar dependências PHP
composer install

# Criar arquivo .env
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Executar migrations
php artisan migrate

# Instalar dependências Node.js
npm install

# Build frontend
npm run build
```

### 3. Configurar Banco de Dados

Edite o arquivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ponto_estagio
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

### 4. Popular Banco de Dados (Opcional)

```bash
php artisan migrate:fresh --seed
```

## 📚 Uso

### Iniciar o Servidor de Desenvolvimento

```bash
# Opção 1: Modo desenvolvimento com hot-reload (recomendado)
composer dev
```

Isso irá iniciar simultaneamente:
- Servidor Laravel (port 8000)
- Queue listener
- Pail logs
- Vite dev server

```bash
# Opção 2: Servidor simples
php artisan serve
```

A aplicação estará disponível em `http://localhost:8000`

### Login de Administrador

1. Acesse `http://localhost:8000/admin/login`
2. CPF: Use os dados do seeder AdminSeeder
3. Senha: Configurada durante o setup

### Registrar Ponto com QR Code

1. Acesse a página de registro
2. Escaneie o QR code do estagiário
3. O sistema capturará automaticamente o horário e IP

## 📁 Estrutura do Projeto

```
ponto-estagio/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Controladores principais
│   │   │   ├── EstagiariosController.php
│   │   │   ├── RelatorioController.php
│   │   │   └── Admin/
│   │   │       └── LoginController.php
│   │   └── Middleware/           # Middlewares de autenticação
│   └── Models/
│       ├── Estagiario.php        # Modelo de Estagiário
│       ├── RegistroPonto.php     # Modelo de Registro de Ponto
│       ├── Turno.php             # Modelo de Turno
│       ├── Admin.php             # Modelo de Admin
│       └── User.php              # Modelo padrão (não utilizado)
├── database/
│   ├── migrations/               # Schema do banco de dados
│   ├── seeders/                  # Seeds para popular dados
│   └── factories/                # Factories para testes
├── resources/
│   ├── views/                    # Templates Blade
│   │   └── pages/
│   │       ├── inicio/           # Página inicial
│   │       ├── login/            # Login admin
│   │       ├── principal/        # Dashboard e funcionalidades
│   │       └── templates/        # Layouts
│   ├── css/                      # Estilos
│   └── js/                       # Scripts frontend
├── routes/
│   └── web.php                   # Rotas da aplicação
├── public/
│   ├── js/                       # JavaScript compilado
│   ├── css/                      # CSS compilado
│   ├── img/                      # Imagens
│   └── index.php                 # Entry point
└── tests/                        # Testes unitários e feature
```

## 🗄️ Modelos de Dados

### Estagiario
- `nm_estagiarios` - Nome completo
- `nr_matricula` - Número de matrícula
- `nm_setor` - Setor de atuação
- `nr_telefone` - Telefone para contato
- `nm_email` - Email
- `ds_situacao` - Situação (ativo/inativo)

### RegistroPonto
- `estagiario_id` - FK para Estagiario
- `ds_motivo` - Motivo do registro
- `hr_registro` - Hora do registro
- `ip_registro` - IP do acesso
- `ds_observacao` - Observações adicionais

### Turno
- `estagiario_id` - FK para Estagiario
- Dados do turno de trabalho

### Admin
- `cpf` - CPF único
- `name` - Nome completo
- `email` - Email
- `password` - Senha (hash)
- `level` - Nível de acesso

## 🔐 Segurança

- Autenticação separada para administradores
- Registro de IP em cada ação
- Senhas armazenadas com hash (bcrypt)
- CSRF protection automático do Laravel
- SQL Injection prevention com Eloquent ORM

## 🧪 Testes

Executar testes:

```bash
# Todos os testes
php artisan test

# Apenas testes unitários
php artisan test --testsuite=Unit

# Apenas testes feature
php artisan test --testsuite=Feature

# Com cobertura de código
php artisan test --coverage
```

## 📊 Relatórios

### Gerar PDF

```bash
# Via interface web
GET /views/principal/export
```

### Exportar Dados

```bash
# Endpoint de exportação
GET /views/principal/export
```

## 🔧 Comandos Úteis

```bash
# Limpar caches
php artisan cache:clear
php artisan config:clear

# Otimizar aplicação
php artisan optimize

# Migrations
php artisan migrate           # Executar todas
php artisan migrate:rollback  # Reverter última
php artisan migrate:refresh   # Resetar e rodar todas

# Seeds
php artisan db:seed
php artisan db:seed --class=AdminSeeder

# Tinker (shell interativo)
php artisan tinker
```

## 📝 Variáveis de Ambiente

Arquivo `.env`:

```env
APP_NAME="Ponto Estagio"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ponto_estagio
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=seu_usuario
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=nao-responda@ponto-estagio.local
MAIL_FROM_NAME="Ponto Estagio"
```

## 🤝 Contribuição

Para contribuir com o projeto:

1. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
2. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
3. Push para a branch (`git push origin feature/AmazingFeature`)
4. Abra um Merge Request

### Padrões de Código

- PSR-12 via Laravel Pint (`php artisan pint`)
- Nomes em português para modelos e banco de dados
- Comentários em português
- Variáveis descritivas

## 🐛 Troubleshooting

### Erro: "Class not found"
```bash
composer dump-autoload
```

### Erro de permissão em storage/
```bash
chmod -R 775 storage/
```

### Banco de dados desatualizado
```bash
php artisan migrate:fresh --seed
```

### Cache corrompido
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 📞 Suporte

Para reportar bugs ou solicitar features:
- Abra uma issue no GitLab
- Entre em contato: pm-est.nicolaspaiva@pm.pr.gov.br

## 📄 Licença

Este projeto está licenciado sob a MIT License - veja o arquivo LICENSE para detalhes.

## 👨‍💼 Autores

- **Nicolas Paiva** - Desenvolvimento inicial
- Equipe de Estagiários - PM/PR

---

**Versão:** 1.0.0  
**Última atualização:** Junho 2026  
**Status:** Em desenvolvimento ✨
